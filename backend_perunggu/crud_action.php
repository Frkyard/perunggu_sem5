<?php
// ========================================================
// HANDLER UTAMA OPERASI CRUD (perunggu_db)
// Mendukung 11 Tabel:
// admins, albums, tracks, tour_dates, products, orders,
// order_items, subscribers, contact_messages, articles, gallery
// ========================================================

require_once __DIR__ . '/connect.php';

// Daftar tabel valid
$valid_tables = [
    'admins', 'albums', 'tracks', 'tour_dates',
    'products', 'orders', 'order_items', 'subscribers',
    'contact_messages', 'articles', 'gallery'
];

$action = $_REQUEST['action'] ?? '';
$table  = $_REQUEST['table'] ?? '';
$redirect_to = $_REQUEST['redirect'] ?? 'dist/index.php';
$max_upload_size = 2 * 1024 * 1024;

if (in_array($action, ['create', 'update', 'delete'], true)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Metode permintaan tidak diizinkan.');
    }
    verify_admin_csrf();
}

// Validasi tabel
if (!in_array($table, $valid_tables)) {
    die("Error: Tabel tidak valid.");
}

// ========================================================
// HELPER: UPLOAD FILE FOTO (CHOOSE FILE)
// ========================================================
function reject_upload($message) {
    global $redirect_to, $table;
    $msg = urlencode($message);
    header("Location: $redirect_to?table=$table&status=error&msg=$msg");
    exit;
}

function reject_action($message) {
    global $redirect_to, $table;
    $msg = urlencode($message);
    header("Location: $redirect_to?table=$table&status=error&msg=$msg");
    exit;
}

function update_order_statuses_transactionally($conn, $id, $new_payment_status, $new_shipping_status) {
    try {
        if (!mysqli_begin_transaction($conn)) {
            throw new mysqli_sql_exception('Could not start order status transaction.');
        }

        $order_stmt = mysqli_prepare(
            $conn,
            'SELECT payment_status, shipping_status, payment_method FROM orders WHERE id = ? FOR UPDATE'
        );
        if (!$order_stmt) {
            throw new mysqli_sql_exception('Could not prepare order lookup.');
        }
        mysqli_stmt_bind_param($order_stmt, 'i', $id);
        if (!mysqli_stmt_execute($order_stmt)) {
            throw new mysqli_sql_exception('Could not execute order lookup.');
        }
        $order_result = mysqli_stmt_get_result($order_stmt);
        $order = $order_result ? mysqli_fetch_assoc($order_result) : null;
        mysqli_stmt_close($order_stmt);

        if (!$order) {
            mysqli_rollback($conn);
            return 'Pesanan tidak ditemukan.';
        }

        $payment_transitions = [
            'pending' => ['pending', 'paid'],
            'paid' => ['paid', 'refunded'],
            'refunded' => ['refunded']
        ];
        $shipping_transitions = [
            'pending' => ['pending', 'shipped', 'cancelled'],
            'shipped' => ['shipped', 'delivered'],
            'delivered' => ['delivered'],
            'cancelled' => ['cancelled']
        ];
        if (!in_array($new_payment_status, $payment_transitions[$order['payment_status']] ?? [], true)
            || !in_array($new_shipping_status, $shipping_transitions[$order['shipping_status']] ?? [], true)) {
            mysqli_rollback($conn);
            return 'Perubahan status pembayaran atau pengiriman tidak diizinkan.';
        }

        if ($order['shipping_status'] === 'cancelled'
            && $new_payment_status !== $order['payment_status']
            && $new_payment_status !== 'refunded') {
            mysqli_rollback($conn);
            return 'Pembayaran pesanan yang dibatalkan hanya dapat diubah untuk menandai refund.';
        }

        if ($new_payment_status === 'refunded'
            && $order['shipping_status'] === 'pending'
            && $new_shipping_status !== 'cancelled') {
            mysqli_rollback($conn);
            return 'Batalkan pengiriman yang belum diproses saat mencatat refund agar stok dipulihkan.';
        }

        $is_cod = stripos($order['payment_method'], 'COD') !== false;
        if ($order['shipping_status'] === 'pending'
            && $new_shipping_status === 'shipped'
            && $new_payment_status !== 'paid'
            && (!$is_cod || $new_payment_status === 'refunded')) {
            mysqli_rollback($conn);
            return 'Pembayaran harus lunas sebelum pesanan non-COD dikirim.';
        }

        if ($new_shipping_status === 'cancelled' && $order['shipping_status'] === 'pending') {
            $items_stmt = mysqli_prepare($conn, 'SELECT product_id, quantity FROM order_items WHERE order_id = ? FOR UPDATE');
            if (!$items_stmt) {
                throw new mysqli_sql_exception('Could not prepare order item lookup.');
            }
            mysqli_stmt_bind_param($items_stmt, 'i', $id);
            if (!mysqli_stmt_execute($items_stmt)) {
                mysqli_stmt_close($items_stmt);
                throw new mysqli_sql_exception('Could not execute order item lookup.');
            }
            $items_result = mysqli_stmt_get_result($items_stmt);

            if (!$items_result) {
                mysqli_stmt_close($items_stmt);
                throw new mysqli_sql_exception('Could not read order items.');
            }

            $stock_stmt = mysqli_prepare($conn, 'UPDATE products SET stock = stock + ? WHERE id = ?');
            if (!$stock_stmt) {
                mysqli_stmt_close($items_stmt);
                throw new mysqli_sql_exception('Could not prepare stock restoration.');
            }

            while ($item = mysqli_fetch_assoc($items_result)) {
                $quantity = (int)$item['quantity'];
                $product_id = (int)$item['product_id'];
                mysqli_stmt_bind_param($stock_stmt, 'ii', $quantity, $product_id);
                if (!mysqli_stmt_execute($stock_stmt)) {
                    mysqli_stmt_close($stock_stmt);
                    mysqli_stmt_close($items_stmt);
                    throw new mysqli_sql_exception('Could not restore product stock.');
                }
                if (mysqli_stmt_affected_rows($stock_stmt) !== 1) {
                    mysqli_stmt_close($stock_stmt);
                    mysqli_stmt_close($items_stmt);
                    throw new mysqli_sql_exception('Stock restoration did not update one product.');
                }
            }

            mysqli_stmt_close($stock_stmt);
            mysqli_stmt_close($items_stmt);
        }

        $legacy_status = 'pending';
        if ($new_shipping_status === 'cancelled' || $new_payment_status === 'refunded') {
            $legacy_status = 'cancelled';
        } elseif (in_array($new_shipping_status, ['shipped', 'delivered'], true)) {
            $legacy_status = 'shipped';
        } elseif ($new_payment_status === 'paid') {
            $legacy_status = 'paid';
        }

        $update_stmt = mysqli_prepare(
            $conn,
            "UPDATE orders
             SET payment_status = ?,
                 shipping_status = ?,
                 status = ?,
                 paid_at = CASE
                     WHEN ? = 'paid' THEN COALESCE(paid_at, CURRENT_TIMESTAMP)
                     ELSE paid_at
                 END
             WHERE id = ?"
        );
        if (!$update_stmt) {
            throw new mysqli_sql_exception('Could not prepare order status update.');
        }
        mysqli_stmt_bind_param(
            $update_stmt,
            'ssssi',
            $new_payment_status,
            $new_shipping_status,
            $legacy_status,
            $new_payment_status,
            $id
        );
        if (!mysqli_stmt_execute($update_stmt)) {
            mysqli_stmt_close($update_stmt);
            throw new mysqli_sql_exception('Could not execute order status update.');
        }
        mysqli_stmt_close($update_stmt);

        if (!mysqli_commit($conn)) {
            throw new mysqli_sql_exception('Could not commit order status transaction.');
        }
        return null;
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($conn);
        error_log('Order status update failed: ' . $exception->getMessage());
        return 'Gagal memperbarui status pesanan dan stok. Silakan coba lagi.';
    }
}

function validate_upload($file, $allowed_extensions) {
    global $max_upload_size;

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > $max_upload_size) {
        reject_upload('Ukuran file maksimal 2 MB.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        reject_upload('Unggahan file gagal. Silakan coba pilih file kembali.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_extensions, true)) {
        reject_upload('Format file tidak didukung.');
    }

    return $file;
}

function handle_image_upload($field_name, $fallback_val = '') {
    // 1. Cek upload file dari $_FILES (bisa dari input file dengan nama field atau field_file)
    $file_keys = [$field_name, $field_name . '_file', 'image_file', 'file'];
    foreach ($file_keys as $key) {
        if (!isset($_FILES[$key])) {
            continue;
        }

        $file = validate_upload($_FILES[$key], ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
        if ($file === null) {
            continue;
        }

        $clean_base   = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
        $new_filename = time() . '_' . $clean_base . '.' . strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $target_dir   = __DIR__ . '/../web_perunggu/images/uploads/';

        if (!is_dir($target_dir) && !mkdir($target_dir, 0777, true) && !is_dir($target_dir)) {
            reject_upload('Folder unggahan foto tidak dapat dibuat.');
        }

        if (!move_uploaded_file($file['tmp_name'], $target_dir . $new_filename)) {
            reject_upload('Foto gagal disimpan. Silakan coba lagi.');
        }

        return 'images/uploads/' . $new_filename;
    }

    // Jika tidak ada file baru diunggah, gunakan nilai lama / default.
    return $fallback_val;
}

// ========================================================
// HELPER: UPLOAD FILE AUDIO MP3 (CHOOSE FILE FOR MUSIC)
// ========================================================
function handle_audio_upload($field_name, $fallback_val = '') {
    $file_keys = [$field_name, $field_name . '_file', 'audio_file', 'audio'];
    foreach ($file_keys as $key) {
        if (!isset($_FILES[$key])) {
            continue;
        }

        $file = validate_upload($_FILES[$key], ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac']);
        if ($file === null) {
            continue;
        }

        $clean_base   = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
        $new_filename = time() . '_' . $clean_base . '.' . strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $target_dir   = __DIR__ . '/../web_perunggu/music/';

        if (!is_dir($target_dir) && !mkdir($target_dir, 0777, true) && !is_dir($target_dir)) {
            reject_upload('Folder unggahan audio tidak dapat dibuat.');
        }

        if (!move_uploaded_file($file['tmp_name'], $target_dir . $new_filename)) {
            reject_upload('Audio gagal disimpan. Silakan coba lagi.');
        }

        return 'music/' . $new_filename;
    }

    return $fallback_val;
}

// ========================================================
// TAHAP 1: CREATE (INSERT)
// ========================================================
if ($action === 'create') {
    if (in_array($table, ['orders', 'order_items'], true)) {
        reject_action('Pesanan dan rincian item hanya dapat dibuat melalui checkout.');
    }

    switch ($table) {
        case 'admins':
            $username = mysqli_real_escape_string($conn, trim($_POST['username']));
            $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
            $pwd_raw  = trim($_POST['password']);
            if (strlen($pwd_raw) < 12) {
                reject_action('Password administrator minimal 12 karakter.');
            }
            $password = password_hash($pwd_raw, PASSWORD_DEFAULT);

            $sql = "INSERT INTO admins (username, email, password) VALUES ('$username', '$email', '$password')";
            break;

        case 'albums':
            $title        = mysqli_real_escape_string($conn, trim($_POST['title']));
            $release_year = (int)$_POST['release_year'];
            $cover_image  = mysqli_real_escape_string($conn, handle_image_upload('cover_image', 'images/albums/memorandum.jpeg'));
            $deskripsi    = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));

            $sql = "INSERT INTO albums (title, release_year, cover_image, deskripsi) 
                    VALUES ('$title', $release_year, '$cover_image', '$deskripsi')";
            break;

        case 'tracks':
            $album_id       = (int)$_POST['album_id'];
            $title          = mysqli_real_escape_string($conn, trim($_POST['title']));
            $duration       = mysqli_real_escape_string($conn, trim($_POST['duration']));
            $audio_url      = mysqli_real_escape_string($conn, handle_audio_upload('audio_url', 'music/33x.mp3'));
            $lyrics         = mysqli_real_escape_string($conn, trim($_POST['lyrics']));
            $chord          = mysqli_real_escape_string($conn, trim($_POST['chord']));
            $is_now_playing = isset($_POST['is_now_playing']) ? (int)$_POST['is_now_playing'] : 0;

            if ($is_now_playing === 1) {
                mysqli_query($conn, "UPDATE tracks SET is_now_playing = 0");
            }

            $sql = "INSERT INTO tracks (album_id, title, duration, audio_url, lyrics, chord, is_now_playing) 
                    VALUES ($album_id, '$title', '$duration', '$audio_url', '$lyrics', '$chord', $is_now_playing)";
            break;

        case 'tour_dates':
            $event_name = mysqli_real_escape_string($conn, trim($_POST['event_name']));
            $city       = mysqli_real_escape_string($conn, trim($_POST['city']));
            $venue      = mysqli_real_escape_string($conn, trim($_POST['venue']));
            $event_date = mysqli_real_escape_string($conn, trim($_POST['event_date']));
            $event_time = mysqli_real_escape_string($conn, trim($_POST['event_time']));
            $ticket_url = mysqli_real_escape_string($conn, trim($_POST['ticket_url']));
            $status     = mysqli_real_escape_string($conn, trim($_POST['status']));

            $sql = "INSERT INTO tour_dates (event_name, city, venue, event_date, event_time, ticket_url, status) 
                    VALUES ('$event_name', '$city', '$venue', '$event_date', '$event_time', '$ticket_url', '$status')";
            break;

        case 'products':
            $category  = mysqli_real_escape_string($conn, trim($_POST['category']));
            $name      = mysqli_real_escape_string($conn, trim($_POST['name']));
            $price     = (float)$_POST['price'];
            $stock     = (int)$_POST['stock'];
            $image_url = mysqli_real_escape_string($conn, handle_image_upload('image_url', 'images/merch/yogya.png'));

            $sql = "INSERT INTO products (category, name, price, stock, image_url) 
                    VALUES ('$category', '$name', $price, $stock, '$image_url')";
            break;

        case 'orders':
            reject_action('Pesanan hanya dapat dibuat melalui checkout.');

        case 'order_items':
            $order_id   = (int)$_POST['order_id'];
            $product_id = (int)$_POST['product_id'];
            $quantity   = (int)$_POST['quantity'];

            $sql = "INSERT INTO order_items (order_id, product_id, quantity) 
                    VALUES ($order_id, $product_id, $quantity)";
            break;

        case 'subscribers':
            $email = mysqli_real_escape_string($conn, trim($_POST['email']));
            $sql   = "INSERT INTO subscribers (email) VALUES ('$email')";
            break;

        case 'contact_messages':
            $name    = mysqli_real_escape_string($conn, trim($_POST['name']));
            $email   = mysqli_real_escape_string($conn, trim($_POST['email']));
            $subject = mysqli_real_escape_string($conn, trim($_POST['subject']));
            $message = mysqli_real_escape_string($conn, trim($_POST['message']));
            $is_read = isset($_POST['is_read']) ? (int)$_POST['is_read'] : 0;

            $sql = "INSERT INTO contact_messages (name, email, subject, message, is_read) 
                    VALUES ('$name', '$email', '$subject', '$message', $is_read)";
            break;

        case 'articles':
            $title         = mysqli_real_escape_string($conn, trim($_POST['title']));
            $category      = mysqli_real_escape_string($conn, trim($_POST['category']));
            $thumbnail_url = mysqli_real_escape_string($conn, handle_image_upload('thumbnail_url', 'images/news1.jpeg'));
            $content       = mysqli_real_escape_string($conn, trim($_POST['content']));

            $sql = "INSERT INTO articles (title, category, thumbnail_url, content) 
                    VALUES ('$title', '$category', '$thumbnail_url', '$content')";
            break;

        case 'gallery':
            $category  = mysqli_real_escape_string($conn, trim($_POST['category']));
            $title     = mysqli_real_escape_string($conn, trim($_POST['title']));
            $image_url = mysqli_real_escape_string($conn, handle_image_upload('image_url', 'images/concert/bts1.png'));
            $caption   = mysqli_real_escape_string($conn, trim($_POST['caption']));

            $sql = "INSERT INTO gallery (category, title, image_url, caption) 
                    VALUES ('$category', '$title', '$image_url', '$caption')";
            break;
    }

    if (mysqli_query($conn, $sql)) {
        $msg = urlencode("Data tabel $table berhasil ditambahkan!");
        header("Location: $redirect_to?table=$table&status=success&msg=$msg");
        exit;
    } else {
        $err = urlencode("Gagal menambah data: " . mysqli_error($conn));
        header("Location: $redirect_to?table=$table&status=error&msg=$err");
        exit;
    }
}

// ========================================================
// TAHAP 2: UPDATE (EDIT)
// ========================================================
if ($action === 'update') {
    if (in_array($table, ['order_items', 'subscribers'], true)) {
        $blocked_redirect = $table === 'order_items'
            ? 'dist/daftar_order_items.php'
            : 'dist/daftar_subscriber.php';
        $msg = urlencode('Data yang dikirim pengunjung tidak dapat diubah oleh admin.');
        header("Location: $blocked_redirect?status=error&msg=$msg");
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) die("Error: ID tidak valid.");

    switch ($table) {
        case 'admins':
            $username = mysqli_real_escape_string($conn, trim($_POST['username']));
            $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
            $pwd_raw  = trim($_POST['password'] ?? '');

            if (!empty($pwd_raw)) {
                if (strlen($pwd_raw) < 12) {
                    reject_action('Password administrator minimal 12 karakter.');
                }
                $password = password_hash($pwd_raw, PASSWORD_DEFAULT);
                $sql = "UPDATE admins SET username='$username', email='$email', password='$password' WHERE id=$id";
            } else {
                $sql = "UPDATE admins SET username='$username', email='$email' WHERE id=$id";
            }
            break;

        case 'albums':
            $title        = mysqli_real_escape_string($conn, trim($_POST['title']));
            $release_year = (int)$_POST['release_year'];
            $cover_image  = mysqli_real_escape_string($conn, handle_image_upload('cover_image', $_POST['existing_cover_image'] ?? ''));
            $deskripsi    = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));

            $sql = "UPDATE albums SET title='$title', release_year=$release_year, cover_image='$cover_image', deskripsi='$deskripsi' WHERE id=$id";
            break;

        case 'tracks':
            $album_id       = (int)$_POST['album_id'];
            $title          = mysqli_real_escape_string($conn, trim($_POST['title']));
            $duration       = mysqli_real_escape_string($conn, trim($_POST['duration']));
            $audio_url      = mysqli_real_escape_string($conn, handle_audio_upload('audio_url', $_POST['existing_audio_url'] ?? ''));
            $lyrics         = mysqli_real_escape_string($conn, trim($_POST['lyrics']));
            $chord          = mysqli_real_escape_string($conn, trim($_POST['chord']));
            $is_now_playing = isset($_POST['is_now_playing']) ? (int)$_POST['is_now_playing'] : 0;

            if ($is_now_playing === 1) {
                mysqli_query($conn, "UPDATE tracks SET is_now_playing = 0");
            }

            $sql = "UPDATE tracks SET album_id=$album_id, title='$title', duration='$duration', audio_url='$audio_url', lyrics='$lyrics', chord='$chord', is_now_playing=$is_now_playing WHERE id=$id";
            break;

        case 'tour_dates':
            $event_name = mysqli_real_escape_string($conn, trim($_POST['event_name']));
            $city       = mysqli_real_escape_string($conn, trim($_POST['city']));
            $venue      = mysqli_real_escape_string($conn, trim($_POST['venue']));
            $event_date = mysqli_real_escape_string($conn, trim($_POST['event_date']));
            $event_time = mysqli_real_escape_string($conn, trim($_POST['event_time']));
            $ticket_url = mysqli_real_escape_string($conn, trim($_POST['ticket_url']));
            $status     = mysqli_real_escape_string($conn, trim($_POST['status']));

            $sql = "UPDATE tour_dates SET event_name='$event_name', city='$city', venue='$venue', event_date='$event_date', event_time='$event_time', ticket_url='$ticket_url', status='$status' WHERE id=$id";
            break;

        case 'products':
            $category  = mysqli_real_escape_string($conn, trim($_POST['category']));
            $name      = mysqli_real_escape_string($conn, trim($_POST['name']));
            $price     = (float)$_POST['price'];
            $stock     = (int)$_POST['stock'];
            $image_url = mysqli_real_escape_string($conn, handle_image_upload('image_url', $_POST['existing_image_url'] ?? ''));

            $sql = "UPDATE products SET category='$category', name='$name', price=$price, stock=$stock, image_url='$image_url' WHERE id=$id";
            break;

        case 'orders':
            $payment_status = trim($_POST['payment_status'] ?? '');
            $shipping_status = trim($_POST['shipping_status'] ?? '');
            if (!in_array($payment_status, ['pending', 'paid', 'refunded'], true)
                || !in_array($shipping_status, ['pending', 'shipped', 'delivered', 'cancelled'], true)) {
                reject_action('Status pembayaran atau pengiriman tidak valid.');
            }

            $status_error = update_order_statuses_transactionally($conn, $id, $payment_status, $shipping_status);
            if ($status_error !== null) {
                reject_action($status_error);
            }

            $msg = urlencode("Status pesanan #$id berhasil diperbarui.");
            header("Location: $redirect_to?table=$table&status=success&msg=$msg");
            exit;

        case 'contact_messages':
            $is_read = isset($_POST['is_read']) ? (int)$_POST['is_read'] : 0;
            $sql = "UPDATE contact_messages SET is_read=$is_read WHERE id=$id";
            break;

        case 'articles':
            $title         = mysqli_real_escape_string($conn, trim($_POST['title']));
            $category      = mysqli_real_escape_string($conn, trim($_POST['category']));
            $thumbnail_url = mysqli_real_escape_string($conn, handle_image_upload('thumbnail_url', $_POST['existing_thumbnail_url'] ?? ''));
            $content       = mysqli_real_escape_string($conn, trim($_POST['content']));

            $sql = "UPDATE articles SET title='$title', category='$category', thumbnail_url='$thumbnail_url', content='$content' WHERE id=$id";
            break;

        case 'gallery':
            $category  = mysqli_real_escape_string($conn, trim($_POST['category']));
            $title     = mysqli_real_escape_string($conn, trim($_POST['title']));
            $image_url = mysqli_real_escape_string($conn, handle_image_upload('image_url', $_POST['existing_image_url'] ?? ''));
            $caption   = mysqli_real_escape_string($conn, trim($_POST['caption']));

            $sql = "UPDATE gallery SET category='$category', title='$title', image_url='$image_url', caption='$caption' WHERE id=$id";
            break;
    }

    if (mysqli_query($conn, $sql)) {
        $msg = urlencode("Data #$id di tabel $table berhasil diperbarui!");
        header("Location: $redirect_to?table=$table&status=success&msg=$msg");
        exit;
    } else {
        $err = urlencode("Gagal memperbarui data: " . mysqli_error($conn));
        header("Location: $redirect_to?table=$table&status=error&msg=$err");
        exit;
    }
}

// ========================================================
// TAHAP 2: DELETE (HAPUS)
// ========================================================
if ($action === 'delete') {
    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) die("Error: ID tidak valid.");

    if (in_array($table, ['orders', 'order_items'], true)) {
        reject_action('Pesanan dan rincian transaksi tidak dapat dihapus agar riwayat serta stok tetap akurat.');
    }

    $sql = "DELETE FROM `$table` WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        $msg = urlencode("Data #$id dari tabel $table berhasil dihapus!");
        header("Location: $redirect_to?table=$table&status=success&msg=$msg");
        exit;
    } else {
        $err = urlencode("Gagal menghapus data: " . mysqli_error($conn));
        header("Location: $redirect_to?table=$table&status=error&msg=$err");
        exit;
    }
}

// ========================================================
// GET DATA ROW BY ID (JSON UNTUK AJAX MODAL EDIT)
// ========================================================
if ($action === 'get_row') {
    $id = (int)($_GET['id'] ?? 0);
    header('Content-Type: application/json');
    if ($id <= 0) {
        echo json_encode(['error' => 'ID tidak valid']);
        exit;
    }
    $res = mysqli_query($conn, "SELECT * FROM `$table` WHERE id = $id LIMIT 1");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        if (isset($row['password'])) unset($row['password']);
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Data tidak ditemukan']);
    }
    exit;
}

mysqli_close($conn);
?>
