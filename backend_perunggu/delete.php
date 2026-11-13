<?php
// ========================================================
// TAHAP 2: DELETE (HAPUS DATA) - SEMUA 11 TABEL
// ========================================================
require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode permintaan tidak diizinkan.');
}
verify_admin_csrf();

$valid_tables = [
    'admins', 'albums', 'tracks', 'tour_dates',
    'products', 'orders', 'order_items', 'subscribers',
    'contact_messages', 'articles', 'gallery'
];

$table = $_POST['table'] ?? '';
$id    = (int)($_POST['id'] ?? 0);
$redirect_to = $_POST['redirect'] ?? 'read.php';

if (!in_array($table, $valid_tables) || $id <= 0) {
    die("Error: Parameter table atau id tidak valid.");
}

if (in_array($table, ['orders', 'order_items'], true)) {
    $err = urlencode('Pesanan dan rincian transaksi tidak dapat dihapus agar riwayat serta stok tetap akurat.');
    header("Location: $redirect_to?table=$table&status=error&msg=$err");
    exit;
}

// Pencegahan hapus jika admin tersisa 1 akun
if ($table === 'admins') {
    $countAdminRes = mysqli_query($conn, "SELECT COUNT(*) FROM admins");
    $countAdmin = $countAdminRes ? (int)mysqli_fetch_row($countAdminRes)[0] : 0;
    if ($countAdmin <= 1) {
        $err = urlencode("Tidak dapat menghapus admin terakhir untuk menjaga akses sistem.");
        header("Location: $redirect_to?table=$table&status=error&msg=$err#table-viewer");
        exit;
    }
}

// Eksekusi DELETE
$sql = "DELETE FROM `$table` WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    $msg = urlencode("Data #$id dari tabel $table berhasil dihapus secara permanen.");
    header("Location: $redirect_to?table=$table&status=success&msg=$msg#table-viewer");
    exit;
} else {
    $err = urlencode("Gagal menghapus data: " . mysqli_error($conn));
    header("Location: $redirect_to?table=$table&status=error&msg=$err#table-viewer");
    exit;
}

mysqli_close($conn);
?>
