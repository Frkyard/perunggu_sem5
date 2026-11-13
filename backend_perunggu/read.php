<?php
// ========================================================
// TAHAP 1: READ (SELECT) & INTEGRASI CRUD LENGKAP
// ========================================================
require_once __DIR__ . '/connect.php';

$valid_tables = [
    'albums'           => 'Katalog Album',
    'tracks'           => 'Trek Lagu & Chord',
    'tour_dates'       => 'Jadwal Tur & Konser',
    'products'         => 'Produk Merchandise',
    'orders'           => 'Pesanan Merchandise',
    'order_items'      => 'Rincian Item Pesanan',
    'articles'         => 'Berita & Artikel',
    'gallery'          => 'Galeri Foto',
    'contact_messages' => 'Pesan Kontak',
    'subscribers'      => 'Newsletter Subscriber',
    'admins'           => 'Akun Admin'
];

$table = isset($_GET['table']) && array_key_exists($_GET['table'], $valid_tables) ? $_GET['table'] : 'albums';

// Hak Operasi CRUD (Sesuai rancangan_basis_data.md)
$full_create_tables    = ['albums', 'tracks', 'tour_dates', 'products', 'articles', 'gallery', 'admins'];
$update_allowed_tables = ['albums', 'tracks', 'tour_dates', 'products', 'articles', 'gallery', 'admins', 'orders', 'contact_messages'];

$can_create = in_array($table, $full_create_tables, true);
$can_update = in_array($table, $update_allowed_tables, true);
$can_delete = !in_array($table, ['orders', 'order_items'], true);

// Notifikasi pesan status
$status = $_GET['status'] ?? '';
$msg    = $_GET['msg'] ?? '';

// Query Select Data
$sql = "SELECT * FROM `$table` ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
$total_rows = $result ? mysqli_num_rows($result) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read (Select) - Basis Data Perunggu</title>
    <link rel="stylesheet" href="dist/css/delete-confirmation.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f5f8; margin: 0; padding: 25px; color: #333; }
        .container { max-width: 1100px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
        h2 { color: #8c5a2b; margin-top: 0; display: flex; justify-content: space-between; align-items: center; }
        .alert { padding: 12px 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; font-size: 14px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .nav-tabs { display: flex; flex-wrap: wrap; gap: 6px; margin: 15px 0 20px; }
        .nav-tabs a { text-decoration: none; padding: 6px 12px; background: #eee; color: #333; border-radius: 4px; font-size: 13px; font-weight: bold; }
        .nav-tabs a.active { background: #8c5a2b; color: white; }
        .btn-create { background: #28a745; color: white; text-decoration: none; padding: 8px 15px; border-radius: 5px; font-weight: bold; font-size: 13px; }
        .btn-create:hover { background: #218838; }
        .btn-action { text-decoration: none; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; display: inline-block; }
        .btn-edit { background: #ffc107; color: #212529; }
        .btn-delete { background: #dc3545; color: white; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 13px; }
        th, td { border: 1px solid #ddd; padding: 10px 12px; text-align: left; }
        th { background: #f2ede7; color: #4a3420; }
        tr:nth-child(even) { background-color: #fafafa; }
        tr:hover { background-color: #f5f5f5; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: #212529; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-info { background: #17a2b8; color: white; }
    </style>
</head>
<body>

<div class="container">
    <?php if (!empty($msg)): ?>
        <div class="alert <?= $status === 'success' ? 'alert-success' : 'alert-error' ?>">
            <?= htmlspecialchars(urldecode($msg)) ?>
        </div>
    <?php endif; ?>

    <div class="top-bar">
        <div>
            <h2 style="margin: 0;">Operasi Read (Select): <code><?= $table ?></code></h2>
            <small style="color: #666;">Database: <code>perunggu_db</code> &bull; Total: <?= $total_rows ?> record</small>
        </div>
        <div>
            <?php if ($can_create): ?>
                <a href="create.php?table=<?= $table ?>" class="btn-create">+ Tambah Data Baru (Create) &raquo;</a>
            <?php else: ?>
                <span style="display: inline-block; padding: 6px 12px; background: #e9ecef; color: #6c757d; border-radius: 5px; font-size: 12px; font-weight: bold;">
                    ℹ Data dibuat via Frontend (Pengunjung)
                </span>
            <?php endif; ?>
            <a href="dist/index.php" style="margin-left: 10px; font-size: 13px; color: #8c5a2b; font-weight: bold; text-decoration: none;">Buka AdminLTE Dashboard &raquo;</a>
        </div>
    </div>

    <!-- TABS TABEL -->
    <div class="nav-tabs">
        <?php foreach ($valid_tables as $tbl => $lbl): ?>
            <a href="?table=<?= $tbl ?>" class="<?= $tbl === $table ? 'active' : '' ?>">
                <?= $lbl ?> (<?= $tbl ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($result && $total_rows > 0): ?>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <?php
                        $fields = mysqli_fetch_fields($result);
                        foreach ($fields as $f):
                            if ($table === 'orders' && $f->name === 'status') continue;
                        ?>
                            <th><?= htmlspecialchars($f->name) ?></th>
                        <?php endforeach; ?>
                        <th style="text-align: center; width: 130px;">Aksi CRUD</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <?php foreach ($row as $col => $val): ?>
                                <?php if ($table === 'orders' && $col === 'status') continue; ?>
                                <td>
                                    <?php
                                    if ($col === 'password') {
                                        echo "<em>(terenkripsi)</em>";
                                    } elseif ($col === 'status') {
                                        if ($val === 'paid' || $val === 'available') {
                                            echo "<span class='badge badge-success'>$val</span>";
                                        } elseif ($val === 'pending' || $val === 'presale') {
                                            echo "<span class='badge badge-warning'>$val</span>";
                                        } else {
                                            echo "<span class='badge badge-danger'>$val</span>";
                                        }
                                    } elseif ($col === 'price' || $col === 'total_amount') {
                                        echo "Rp " . number_format((float)$val, 0, ',', '.');
                                    } elseif (strlen($val ?? '') > 50) {
                                        echo htmlspecialchars(substr($val, 0, 50)) . "...";
                                    } else {
                                        echo htmlspecialchars($val ?? '-');
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            <td style="text-align: center; white-space: nowrap;">
                                <?php if ($can_update): ?>
                                    <a href="update.php?table=<?= $table ?>&id=<?= $row['id'] ?>" class="btn-action btn-edit">Edit (U)</a>
                                <?php endif; ?>
                                <?php if ($can_delete): ?>
                                    <?php
                                        $delete_label = $row['order_code'] ?? $row['username'] ?? $row['name'] ?? $row['title'] ?? $row['event_name'] ?? $row['email'] ?? $row['subject'] ?? ('ID #' . (int)$row['id']);
                                    ?>
                                    <form action="delete.php" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                        <input type="hidden" name="redirect" value="read.php">
                                        <button type="button" class="btn-action btn-delete"
                                                data-delete-confirm
                                                data-table-label="<?= htmlspecialchars($valid_tables[$table], ENT_QUOTES, 'UTF-8') ?>"
                                                data-record-label="<?= htmlspecialchars((string)$delete_label, ENT_QUOTES, 'UTF-8') ?>"
                                                data-record-id="<?= (int)$row['id'] ?>">Hapus (D)</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="text-align: center; padding: 40px 0; color: #888;">
            Belum ada record di tabel <code><?= $table ?></code>. Silakan klik tombol <strong>+ Tambah Data Baru (Create)</strong> di atas.
        </p>
    <?php endif; ?>
</div>

<div class="delete-confirm-overlay" id="delete-confirm-modal" hidden>
    <section class="delete-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="delete-confirm-title" aria-describedby="delete-confirm-description" tabindex="-1">
        <header class="delete-confirm-header">
            <h2 class="delete-confirm-heading" id="delete-confirm-title">
                <span class="delete-confirm-warning-icon" aria-hidden="true">!</span>
                Konfirmasi Hapus Data
            </h2>
            <button type="button" class="delete-confirm-close" data-delete-cancel aria-label="Tutup konfirmasi">&times;</button>
        </header>
        <div class="delete-confirm-content" id="delete-confirm-description">
            <p class="delete-confirm-prompt">Yakin ingin menghapus data <strong id="delete-confirm-table"></strong> berikut?</p>
            <strong class="delete-confirm-record" id="delete-confirm-record"></strong>
            <span class="delete-confirm-id" id="delete-confirm-id"></span>
            <p class="delete-confirm-caution">Data yang dihapus tidak dapat dikembalikan.</p>
        </div>
        <footer class="delete-confirm-footer">
            <button type="button" class="delete-confirm-cancel" data-delete-cancel>Batal</button>
            <button type="button" class="delete-confirm-button" id="delete-confirm-submit">Ya, Hapus</button>
        </footer>
    </section>
</div>
<script src="dist/js/delete-confirmation.js"></script>

</body>
</html>
<?php mysqli_close($conn); ?>
