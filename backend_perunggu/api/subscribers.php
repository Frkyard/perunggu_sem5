<?php
// ========================================================
// API: NEWSLETTER SUBSCRIBERS
// ========================================================
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET'
    || (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS'
        && strtoupper($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ?? '') === 'GET')) {
    define('PERUNGGU_PRIVATE_API', true);
}
require_once __DIR__ . '/connect_cors.php';
require_once __DIR__ . '/../auth.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = get_api_input();
    $email = trim($data['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Format alamat email tidak valid!'
        ]);
        exit;
    }

    $esc_email = mysqli_real_escape_string($conn, $email);

    // Cek apakah email sudah terdaftar
    $check = mysqli_query($conn, "SELECT id FROM subscribers WHERE email = '$esc_email' LIMIT 1");
    if ($check && mysqli_num_rows($check) > 0) {
        echo json_encode([
            'status' => 'success',
            'already_registered' => true,
            'message' => 'Email kamu sudah terdaftar di Perunggu Fanbase Mail!'
        ]);
        exit;
    }

    $sql = "INSERT INTO subscribers (email) VALUES ('$esc_email')";
    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            'status' => 'success',
            'already_registered' => false,
            'message' => 'Terima kasih! Kamu telah terdaftar di Perunggu Fanbase Mail.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mendaftar: ' . mysqli_error($conn)
        ]);
    }
    exit;
}

if ($method === 'GET') {
    require_admin_api();
    $res = mysqli_query($conn, "SELECT id, email, subscribed_at FROM subscribers ORDER BY id DESC");
    $list = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $list[] = $r;
    }
    echo json_encode(['status' => 'success', 'data' => $list]);
    exit;
}
