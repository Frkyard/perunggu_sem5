<?php
// ========================================================
// API: GALERI DOKUMENTASI & BEHIND THE SCENE
// ========================================================
require_once __DIR__ . '/connect_cors.php';

$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, trim($_GET['category'])) : '';
$where = !empty($category) && $category !== 'all' ? "WHERE category = '$category'" : "";

$sql = "SELECT id, category, title, image_url, caption FROM gallery $where ORDER BY id ASC";
$res = mysqli_query($conn, $sql);

if (!$res) {
    error_log('Query galeri gagal: ' . mysqli_error($conn));
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Galeri tidak dapat diambil.']);
    exit;
}

$gallery = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['id'] = (int)$row['id'];
    $gallery[] = $row;
}

echo json_encode([
    'status' => 'success',
    'total' => count($gallery),
    'data' => $gallery
]);
