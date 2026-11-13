<?php
// ========================================================
// API CORS & DATABASE CONNECTION HANDLER
// ========================================================
header("Content-Type: application/json; charset=UTF-8");

define('PERUNGGU_PUBLIC_API', true);

if (!defined('PERUNGGU_PRIVATE_API')) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
}

// Tangani Preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (defined('PERUNGGU_PRIVATE_API')) {
        http_response_code(403);
        exit;
    }
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../connect.php';

// Helper: Membaca input JSON atau POST standar
function get_api_input() {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $json = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }
    }
    return $_POST;
}
