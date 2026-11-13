<?php
// ========================================================
// API: PESAN KONTAK (CONTACT MESSAGES)
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

    $name    = trim($data['name'] ?? $data['nama'] ?? '');
    $email   = trim($data['email'] ?? '');
    $subject = trim($data['subject'] ?? '');
    if (empty($subject)) {
        $subject = 'Pesan dari Website';
    }
    $message = trim($data['message'] ?? $data['pesan'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Nama, email, dan isi pesan wajib diisi!'
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Format email tidak valid (contoh yang benar: nama@email.com)!'
        ]);
        exit;
    }

    $esc_name    = mysqli_real_escape_string($conn, $name);
    $esc_email   = mysqli_real_escape_string($conn, $email);
    $esc_subject = mysqli_real_escape_string($conn, $subject);
    $esc_message = mysqli_real_escape_string($conn, $message);

    $sql = "INSERT INTO contact_messages (name, email, subject, message, is_read)
            VALUES ('$esc_name', '$esc_email', '$esc_subject', '$esc_message', 0)";

    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            'status' => 'success',
            'id' => mysqli_insert_id($conn),
            'message' => 'Pesan kamu berhasil dikirim ke manajemen PERUNGGU!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengirim pesan: ' . mysqli_error($conn)
        ]);
    }
    exit;
}

if ($method === 'GET') {
    require_admin_api();
    $res = mysqli_query($conn, "SELECT id, name, email, subject, message, is_read, created_at FROM contact_messages ORDER BY id DESC");
    $list = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $list[] = $r;
    }
    echo json_encode(['status' => 'success', 'data' => $list]);
    exit;
}
