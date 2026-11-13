<?php
// ========================================================
// API: ARTIKEL BERITA RESMI PERUNGGU
// ========================================================
require_once __DIR__ . '/connect_cors.php';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

$sql = "SELECT id, title, category, thumbnail_url, content, published_at
        FROM articles
        ORDER BY published_at DESC, id DESC
        LIMIT $limit";
$res = mysqli_query($conn, $sql);

$articles = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $row['id'] = (int)$row['id'];
        $articles[] = $row;
    }
}

echo json_encode([
    'status' => 'success',
    'total' => count($articles),
    'data' => $articles
]);
