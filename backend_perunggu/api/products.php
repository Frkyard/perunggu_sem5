<?php
// ========================================================
// API: KATALOG PRODUK MERCHANDISE
// ========================================================
require_once __DIR__ . '/connect_cors.php';

$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, trim($_GET['category'])) : '';
$where = !empty($category) && $category !== 'all' ? "WHERE category = '$category'" : "";

$is_best_seller = ($_GET['featured'] ?? '') === 'best_seller';
if ($is_best_seller) {
    $category_filter = !empty($category) && $category !== 'all'
        ? "AND p.category = '$category'"
        : '';
    $sql = "SELECT p.id, p.category, p.name, p.price, p.stock, p.image_url
            FROM products p
            LEFT JOIN (
                SELECT oi.product_id, SUM(oi.quantity) AS units_sold
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                WHERE o.payment_status = 'paid'
                  AND o.shipping_status <> 'cancelled'
                GROUP BY oi.product_id
            ) sales ON sales.product_id = p.id
            WHERE p.stock > 0 $category_filter
            ORDER BY COALESCE(sales.units_sold, 0) DESC, p.id DESC
            LIMIT 1";
} else {
    $sql = "SELECT id, category, name, price, stock, image_url FROM products $where ORDER BY id ASC";
}

$res = mysqli_query($conn, $sql);

if (!$res) {
    error_log('Query katalog produk gagal: ' . mysqli_error($conn));
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Katalog produk tidak dapat diambil.']);
    exit;
}

$products = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['id'] = (int)$row['id'];
    $row['price'] = (float)$row['price'];
    $row['stock'] = (int)$row['stock'];
    $products[] = $row;
}

echo json_encode([
    'status' => 'success',
    'total' => count($products),
    'data' => $products
]);
