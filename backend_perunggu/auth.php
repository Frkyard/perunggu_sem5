<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    $is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $is_https,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

function admin_is_authenticated(): bool
{
    return isset($_SESSION['admin_id']) && (int)$_SESSION['admin_id'] > 0;
}

function admin_login_url(): string
{
    $script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $directory = dirname($script_path);
    if (in_array(basename($directory), ['dist', 'api'], true)) {
        $directory = dirname($directory);
    }

    return rtrim($directory, '/') . '/login.php';
}

function require_admin(): void
{
    if (!admin_session_is_valid()) {
        unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_csrf_token']);
        header('Location: ' . admin_login_url());
        exit;
    }
}

function require_admin_api(): void
{
    if (!admin_session_is_valid()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['status' => 'error', 'message' => 'Autentikasi admin diperlukan.']);
        exit;
    }
}

function admin_session_is_valid(): bool
{
    if (!admin_is_authenticated()) {
        return false;
    }

    global $conn;
    if (!isset($conn) || !($conn instanceof mysqli)) {
        return true;
    }

    $stmt = mysqli_prepare($conn, 'SELECT id FROM admins WHERE id = ? LIMIT 1');
    if (!$stmt) {
        http_response_code(500);
        exit('Tidak dapat memverifikasi akun administrator.');
    }
    $admin_id = (int)$_SESSION['admin_id'];
    mysqli_stmt_bind_param($stmt, 'i', $admin_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $is_valid = mysqli_stmt_num_rows($stmt) === 1;
    mysqli_stmt_close($stmt);

    return $is_valid;
}

function admin_csrf_token(): string
{
    if (empty($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['admin_csrf_token'];
}

function verify_admin_csrf(): void
{
    $submitted_token = (string)($_POST['csrf_token'] ?? '');
    $session_token = (string)($_SESSION['admin_csrf_token'] ?? '');

    if ($session_token === '' || !hash_equals($session_token, $submitted_token)) {
        http_response_code(403);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
    }
}
