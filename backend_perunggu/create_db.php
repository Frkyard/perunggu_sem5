<?php
require_once __DIR__ . '/auth.php';
require_admin();

$servername = getenv('PERUNGGU_DB_HOST') ?: 'localhost';
$username = getenv('PERUNGGU_DB_USER') ?: 'root';
$password = getenv('PERUNGGU_DB_PASSWORD') ?: '';
$dbname = getenv('PERUNGGU_DB_NAME') ?: 'perunggu_db';

// MySQLi Procedural
// create connection
$conn = mysqli_connect($servername, $username, $password);

// cek connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// create database
$quoted_dbname = '`' . str_replace('`', '``', $dbname) . '`';
$sql = "CREATE DATABASE IF NOT EXISTS $quoted_dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($conn, $sql)) {
    echo "<h3>Database '$dbname' created successfully</h3>";
    echo "<p><a href='create_table.php'>Lanjut: Buat Tabel (create_table.php) &raquo;</a></p>";
} else {
    echo "Error creating database: " . mysqli_error($conn);
}

// close connection
mysqli_close($conn);
?>

