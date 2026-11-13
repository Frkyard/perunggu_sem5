<?php
// ========================================================
// API: JADWAL TUR & KONSER PERUNGGU
// ========================================================
require_once __DIR__ . '/connect_cors.php';

$sql = "SELECT id, event_name, city, venue, event_date, event_time, ticket_url, status
        FROM tour_dates
        WHERE event_date >= CURDATE()
        ORDER BY event_date ASC";
$res = mysqli_query($conn, $sql);

if (!$res) {
    error_log('Query jadwal tur gagal: ' . mysqli_error($conn));
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Jadwal tur tidak dapat diambil.']);
    exit;
}

$tours = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['id'] = (int)$row['id'];
    $tours[] = $row;
}

echo json_encode([
    'status' => 'success',
    'total' => count($tours),
    'data' => $tours
]);
