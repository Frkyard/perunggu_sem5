<?php

require_once __DIR__ . "/connect.php";

$table = isset($_GET['table']) ? $_GET['table'] : 'albums';

$allowed_tables = [
    'admins', 'albums', 'tracks', 'tour_dates', 
    'products', 'orders', 'order_items', 'subscribers', 
    'contact_messages', 'articles', 'gallery'
];

if (!in_array($table, $allowed_tables)) {
    $table = 'albums';
}

$sql = "SELECT * FROM `$table`";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baca Data - Database Perunggu (perunggu_db)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 25px; background: #f9f9fb; color: #333; }
        h2 { color: #8c5a2b; margin-bottom: 5px; }
        .nav-tables { margin: 15px 0 25px; display: flex; flex-wrap: wrap; gap: 8px; }
        .nav-tables a { 
            text-decoration: none; padding: 6px 12px; background: #e0e0e0; 
            color: #333; border-radius: 4px; font-size: 13px; font-weight: bold;
        }
        .nav-tables a.active { background: #8c5a2b; color: #fff; }
        .nav-tables a:hover:not(.active) { background: #ccc; }
        table { border-collapse: collapse; width: 100%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 10px 12px; text-align: left; font-size: 14px; }
        th { background: #f2ede7; color: #4a3420; }
        tr:nth-child(even) { background-color: #fafafa; }
        tr:hover { background-color: #f1f1f1; }
        .actions { margin-top: 20px; display: flex; gap: 15px; }
        .actions a { color: #8c5a2b; text-decoration: none; font-weight: bold; }
        .actions a:hover { text-decoration: underline; }
        caption { font-size: 16px; font-weight: bold; text-align: left; padding: 10px 0; color: #555; }
    </style>
</head>
<body>

    <h2>Data Tabel: <code><?= htmlspecialchars($table) ?></code> (Database: <code><?= htmlspecialchars($dbname) ?></code>)</h2>

    <div class="nav-tables">
        <?php foreach ($allowed_tables as $t): ?>
            <a href="?table=<?= $t ?>" class="<?= $t === $table ? 'active' : '' ?>">
                <?= $t ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <table>
            <caption>Menampilkan <?= mysqli_num_rows($result) ?> baris data</caption>
            <thead>
                <tr>
                    <?php
                    $fields = mysqli_fetch_fields($result);
                    foreach ($fields as $field):
                    ?>
                        <th><?= htmlspecialchars($field->name) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <?php foreach ($row as $colName => $val): ?>
                            <td>
                                <?php 
                                if ($colName === 'password') {
                                    echo "<em>(terenkripsi)</em>";
                                } elseif (strlen($val ?? '') > 100) {
                                    echo htmlspecialchars(substr($val, 0, 100)) . "...";
                                } else {
                                    echo htmlspecialchars($val ?? 'NULL');
                                }
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p><em>0 results - Belum ada data di tabel <code><?= htmlspecialchars($table) ?></code>.</em></p>
    <?php endif; ?>

    <div class="actions">
        <a href="index.php">&laquo; Kembali ke Menu Utama</a>
        <a href="create_table.php">Struktur Tabel (create_table.php)</a>
        <a href="insertdata.php">Isi / Tambah Data (insertdata.php)</a>
    </div>

</body>
</html>
<?php
mysqli_close($conn);
?>

