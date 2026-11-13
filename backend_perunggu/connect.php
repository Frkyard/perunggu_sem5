<?php

$servername = getenv('PERUNGGU_DB_HOST') ?: 'localhost';
$username = getenv('PERUNGGU_DB_USER') ?: 'root';
$password = getenv('PERUNGGU_DB_PASSWORD') ?: '';
$dbname = getenv('PERUNGGU_DB_NAME') ?: 'perunggu_db';

// create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// cek connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!defined('PERUNGGU_PUBLIC_API') && !defined('PERUNGGU_AUTH_PAGE')) {
    require_once __DIR__ . '/auth.php';
    require_admin();
}
?>

