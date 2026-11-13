<?php
require_once __DIR__ . '/auth.php';
require_admin();

$servername = getenv('PERUNGGU_DB_HOST') ?: 'localhost';
$username = getenv('PERUNGGU_DB_USER') ?: 'root';
$password = getenv('PERUNGGU_DB_PASSWORD') ?: '';
$dbname = getenv('PERUNGGU_DB_NAME') ?: 'perunggu_db';

$db_status = false;
$tables_count = 0;

// Cek koneksi server MySQL
$test_conn = @mysqli_connect($servername, $username, $password);
$server_online = ($test_conn !== false);

if ($server_online) {
    // Cek keberadaan database
    $db_selected = @mysqli_select_db($test_conn, $dbname);
    if ($db_selected) {
        $db_status = true;
        $res = mysqli_query($test_conn, "SHOW TABLES");
        if ($res) {
            $tables_count = mysqli_num_rows($res);
        }
    }
    mysqli_close($test_conn);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backend & Database PERUNGGU</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f5f8;
            color: #2c3e50;
            margin: 0;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        h1 {
            color: #a06836;
            margin-top: 0;
            border-bottom: 2px solid #f0e6dd;
            padding-bottom: 12px;
        }
        .status-box {
            background: #fdfaf7;
            border: 1px solid #e8dbcf;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .status-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #e8dbcf;
        }
        .status-item:last-child {
            border-bottom: none;
        }
        .badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: bold;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        
        .script-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        .script-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafafa;
            border: 1px solid #eee;
            margin-bottom: 12px;
            padding: 15px 20px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        .script-item:hover {
            border-color: #a06836;
            background: #fffcf9;
        }
        .script-info h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        .script-info p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }
        .btn {
            display: inline-block;
            background: #a06836;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 13px;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #865327;
        }
        .btn-outline {
            background: transparent;
            color: #a06836;
            border: 1px solid #a06836;
        }
        .btn-outline:hover {
            background: #a06836;
            color: white;
        }
        .tables-summary {
            margin-top: 25px;
            background: #fbfbfb;
            padding: 15px;
            border-radius: 6px;
            font-size: 13px;
            color: #555;
        }
        code {
            background: #f1f1f1;
            padding: 2px 5px;
            border-radius: 4px;
            color: #c7254e;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Database Manager & Backend PERUNGGU</h1>
    <p>Struktur manajemen database PHP berbasis procedural (mirip modul <code>webpro5a/php_db</code>) untuk website resmi grup musik <strong>PERUNGGU</strong>.</p>

    <div class="status-box">
        <div class="status-item">
            <span>MySQL Server (localhost:3306):</span>
            <span class="badge <?= $server_online ? 'badge-success' : 'badge-danger' ?>">
                <?= $server_online ? 'Online' : 'Offline / Tidak Terhubung' ?>
            </span>
        </div>
        <div class="status-item">
            <span>Database <code><?= $dbname ?></code>:</span>
            <span class="badge <?= $db_status ? 'badge-success' : 'badge-warning' ?>">
                <?= $db_status ? 'Ditemukan' : 'Belum Dibuat' ?>
            </span>
        </div>
        <div class="status-item">
            <span>Jumlah Tabel Terdaftar:</span>
            <strong><?= $tables_count ?> / 11 Tabel</strong>
        </div>
        <div class="status-item" style="background: #eef7ee; padding: 10px; border-radius: 6px; margin-top: 10px;">
            <span><strong>AdminLTE Dashboard (dist/):</strong></span>
            <a href="dist/index.php" class="btn" style="background: #28a745; padding: 5px 15px;">Buka Dashboard AdminLTE &raquo;</a>
        </div>
    </div>

    <h2>Daftar Script PHP Database:</h2>
    <ul class="script-list">
        <li class="script-item">
            <div class="script-info">
                <h3>1. create_db.php</h3>
                <p>Membuat database <code>perunggu_db</code> baru di MySQL.</p>
            </div>
            <a href="create_db.php" class="btn">Jalankan &raquo;</a>
        </li>
        <li class="script-item">
            <div class="script-info">
                <h3>2. create_table.php</h3>
                <p>Membuat seluruh 11 tabel sesuai dokumen <code>rancangan_basis_data.md</code>.</p>
            </div>
            <a href="create_table.php" class="btn">Jalankan &raquo;</a>
        </li>
        <li class="script-item">
            <div class="script-info">
                <h3>3. insertdata.php</h3>
                <p>Mengisi data awal (seed data lagu, album, merchandise, tour, dsb).</p>
            </div>
            <a href="insertdata.php" class="btn">Jalankan &raquo;</a>
        </li>
        <li class="script-item">
            <div class="script-info">
                <h3>4. connect.php</h3>
                <p>File konfigurasi dan fungsi koneksi <code>mysqli_connect</code>.</p>
            </div>
            <a href="connect.php" class="btn btn-outline">Test Koneksi</a>
        </li>
    </ul>

    <h2 style="color: #28a745; margin-top: 30px;">Operasi CRUD Lengkap (Tahap 1 &amp; Tahap 2):</h2>
    <ul class="script-list">
        <li class="script-item" style="border-left: 4px solid #28a745;">
            <div class="script-info">
                <h3>Tahap 1: Create (Insert) &mdash; <code>create.php</code></h3>
                <p>Formulir penambahan data baru untuk seluruh 11 tabel basis data.</p>
            </div>
            <a href="create.php" class="btn" style="background: #28a745;">+ Tambah Data (C) &raquo;</a>
        </li>
        <li class="script-item" style="border-left: 4px solid #17a2b8;">
            <div class="script-info">
                <h3>Tahap 1: Read (Select) &mdash; <code>read.php</code></h3>
                <p>Membaca dan menampilkan seluruh rekaman tabel secara dinamis dengan aksi CRUD.</p>
            </div>
            <a href="read.php" class="btn" style="background: #17a2b8;">Buka Data (R) &raquo;</a>
        </li>
        <li class="script-item" style="border-left: 4px solid #ffc107;">
            <div class="script-info">
                <h3>Tahap 2: Update (Edit) &mdash; <code>update.php</code></h3>
                <p>Formulir pengeditan data yang ada pada 11 tabel berdasarkan ID record.</p>
            </div>
            <a href="read.php" class="btn" style="background: #e0a800; color: #212529;">Pilih Data di Read &raquo;</a>
        </li>
        <li class="script-item" style="border-left: 4px solid #dc3545;">
            <div class="script-info">
                <h3>Tahap 2: Delete (Hapus) &mdash; <code>delete.php</code> &amp; <code>crud_action.php</code></h3>
                <p>Pemrosesan penghapusan record secara aman dengan proteksi akun admin terakhir.</p>
            </div>
            <span class="badge badge-success" style="padding: 6px 12px; background: #e2e3e5; color: #383d41;">Aktif di Read &amp; Dashboard</span>
        </li>
    </ul>

    <div class="tables-summary">
        <strong>11 Tabel Terintegrasi Sesuai <code>rancangan_basis_data.md</code>:</strong><br>
        <code>admins</code>, <code>albums</code>, <code>tracks</code>, <code>tour_dates</code>, 
        <code>products</code>, <code>orders</code>, <code>order_items</code>, <code>subscribers</code>, 
        <code>contact_messages</code>, <code>articles</code>, <code>gallery</code>.
    </div>
</div>

</body>
</html>

