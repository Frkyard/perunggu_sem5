<?php
// ========================================================
// API: TRANSAKSI PEMESANAN (ORDERS & ORDER_ITEMS)
// ========================================================
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET'
    || (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS'
        && strtoupper($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ?? '') === 'GET')) {
    define('PERUNGGU_PRIVATE_API', true);
}
require_once __DIR__ . '/connect_cors.php';
require_once __DIR__ . '/../auth.php';

function send_order_error(int $status_code, string $message): void
{
    http_response_code($status_code);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? '';

if ($method === 'POST') {
    $data = get_api_input();
    $customer_name = trim((string)($data['customer_name'] ?? $data['name'] ?? ''));
    $whatsapp = trim((string)($data['whatsapp'] ?? $data['wa'] ?? ''));
    $address = trim((string)($data['address'] ?? ''));
    $payment_method = trim((string)($data['payment_method'] ?? ''));
    $items = $data['items'] ?? null;
    $valid_payment_methods = ['QRIS', 'Transfer Bank (BCA)', 'COD (Bayar di Tempat)'];

    if ($customer_name === '' || strlen($customer_name) > 150
        || $address === '' || strlen($address) > 2000
        || !preg_match('/^[0-9+()\\s-]{6,25}$/', $whatsapp)
        || !in_array($payment_method, $valid_payment_methods, true)) {
        send_order_error(400, 'Nama, nomor WhatsApp, alamat, atau metode pembayaran tidak valid.');
    }

    if (!is_array($items) || count($items) < 1 || count($items) > 50) {
        send_order_error(400, 'Keranjang pesanan tidak valid.');
    }

    $quantities = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            send_order_error(400, 'Rincian produk pesanan tidak valid.');
        }

        $product_id = filter_var($item['product_id'] ?? $item['id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($item['quantity'] ?? $item['qty'] ?? null, FILTER_VALIDATE_INT);
        if ($product_id === false || $product_id === null || $product_id < 1
            || $quantity === false || $quantity === null || $quantity < 1 || $quantity > 100) {
            send_order_error(400, 'Produk atau jumlah pesanan tidak valid.');
        }

        $quantities[$product_id] = ($quantities[$product_id] ?? 0) + $quantity;
        if ($quantities[$product_id] > 100) {
            send_order_error(400, 'Jumlah produk melebihi batas pesanan.');
        }
    }
    ksort($quantities, SORT_NUMERIC);

    try {
        mysqli_begin_transaction($conn);

        $select_product = mysqli_prepare($conn, 'SELECT id, name, price, stock FROM products WHERE id = ? FOR UPDATE');
        if (!$select_product) {
            throw new mysqli_sql_exception('Unable to prepare product lookup.');
        }
        $product_id_for_query = 0;
        $locked_product_id = 0;
        $product_name = '';
        $product_price = 0;
        $product_stock = 0;
        mysqli_stmt_bind_param($select_product, 'i', $product_id_for_query);
        mysqli_stmt_bind_result($select_product, $locked_product_id, $product_name, $product_price, $product_stock);

        $order_items = [];
        $subtotal_cents = 0;
        foreach ($quantities as $product_id => $quantity) {
            $product_id_for_query = (int)$product_id;
            if (!mysqli_stmt_execute($select_product) || !mysqli_stmt_fetch($select_product)) {
                mysqli_stmt_close($select_product);
                mysqli_rollback($conn);
                send_order_error(422, 'Salah satu produk tidak tersedia.');
            }

            $stock = (int)$product_stock;
            if ($stock < $quantity || (float)$product_price < 0) {
                $available_product_name = (string)$product_name;
                mysqli_stmt_close($select_product);
                mysqli_rollback($conn);
                send_order_error(409, (float)$product_price < 0
                    ? 'Harga produk ' . $available_product_name . ' tidak valid.'
                    : 'Stok ' . $available_product_name . ' tidak mencukupi.');
            }

            $unit_price_cents = (int)round((float)$product_price * 100);
            $subtotal_cents += $unit_price_cents * $quantity;
            $order_items[] = [
                'product_id' => (int)$locked_product_id,
                'quantity' => $quantity
            ];
        }
        mysqli_stmt_close($select_product);

        $total_amount = number_format(($subtotal_cents + 1500000) / 100, 2, '.', '');
        $order_code = 'PRG-' . strtoupper(bin2hex(random_bytes(4)));
        $insert_order = mysqli_prepare(
            $conn,
            "INSERT INTO orders (
                order_code, customer_name, whatsapp, address, payment_method, total_amount,
                status, payment_status, shipping_status
             ) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'pending', 'pending')"
        );
        if (!$insert_order) {
            throw new mysqli_sql_exception('Unable to prepare order insertion.');
        }
        mysqli_stmt_bind_param(
            $insert_order,
            'ssssss',
            $order_code,
            $customer_name,
            $whatsapp,
            $address,
            $payment_method,
            $total_amount
        );
        if (!mysqli_stmt_execute($insert_order)) {
            mysqli_stmt_close($insert_order);
            throw new mysqli_sql_exception('Order insertion failed.');
        }
        $order_id = mysqli_insert_id($conn);
        mysqli_stmt_close($insert_order);

        $insert_item = mysqli_prepare($conn, 'INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)');
        $update_stock = mysqli_prepare($conn, 'UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
        if (!$insert_item || !$update_stock) {
            throw new mysqli_sql_exception('Unable to prepare order item operations.');
        }

        foreach ($order_items as $order_item) {
            $item_product_id = $order_item['product_id'];
            $item_quantity = $order_item['quantity'];
            mysqli_stmt_bind_param($insert_item, 'iii', $order_id, $item_product_id, $item_quantity);
            if (!mysqli_stmt_execute($insert_item)) {
                throw new mysqli_sql_exception('Order item insertion failed.');
            }

            mysqli_stmt_bind_param($update_stock, 'iii', $item_quantity, $item_product_id, $item_quantity);
            if (!mysqli_stmt_execute($update_stock) || mysqli_stmt_affected_rows($update_stock) !== 1) {
                throw new mysqli_sql_exception('Product stock update failed.');
            }
        }

        mysqli_stmt_close($insert_item);
        mysqli_stmt_close($update_stock);
        if (!mysqli_commit($conn)) {
            throw new mysqli_sql_exception('Order transaction could not be committed.');
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Pesanan berhasil dicatat.',
            'order_code' => $order_code,
            'order_id' => $order_id,
            'customer_name' => $customer_name,
            'total_amount' => (float)$total_amount,
            'items_count' => count($order_items)
        ]);
        exit;
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($conn);
        error_log('Checkout gagal: ' . $exception->getMessage());
        send_order_error(500, 'Pesanan gagal disimpan. Silakan coba kembali.');
    }
}

if ($method === 'GET') {
    require_admin_api();
    $code = trim((string)($_GET['code'] ?? ''));

    if ($code !== '') {
        $stmt = mysqli_prepare($conn, 'SELECT * FROM orders WHERE order_code = ? LIMIT 1');
        if (!$stmt) {
            send_order_error(500, 'Data pesanan tidak dapat diambil.');
        }
        mysqli_stmt_bind_param($stmt, 's', $code);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        if (!$order) {
            send_order_error(404, 'Pesanan tidak ditemukan.');
        }

        $order_id = (int)$order['id'];
        $items_stmt = mysqli_prepare(
            $conn,
            'SELECT oi.*, p.name AS product_name, p.price, p.image_url
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?'
        );
        if (!$items_stmt) {
            send_order_error(500, 'Rincian pesanan tidak dapat diambil.');
        }
        mysqli_stmt_bind_param($items_stmt, 'i', $order_id);
        mysqli_stmt_execute($items_stmt);
        $items_result = mysqli_stmt_get_result($items_stmt);
        $order['items'] = $items_result ? mysqli_fetch_all($items_result, MYSQLI_ASSOC) : [];
        mysqli_stmt_close($items_stmt);

        echo json_encode(['status' => 'success', 'data' => $order]);
        exit;
    }

    $limit = filter_var($_GET['limit'] ?? 20, FILTER_VALIDATE_INT);
    $limit = ($limit !== false && $limit > 0) ? min($limit, 100) : 20;
    $result = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC LIMIT $limit");
    if (!$result) {
        send_order_error(500, 'Daftar pesanan tidak dapat diambil.');
    }

    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $orders]);
    exit;
}

http_response_code(405);
header('Allow: GET, POST, OPTIONS');
echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak diizinkan.']);
