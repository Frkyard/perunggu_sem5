<?php
require_once __DIR__ . '/auth.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $cookie_params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $cookie_params['path'],
        'domain' => $cookie_params['domain'],
        'secure' => $cookie_params['secure'],
        'httponly' => $cookie_params['httponly'],
        'samesite' => 'Strict'
    ]);
}
session_destroy();

header('Location: login.php');
exit;
