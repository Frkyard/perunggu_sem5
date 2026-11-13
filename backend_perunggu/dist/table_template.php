<?php
// ========================================================
// TEMPLATE DIREKTORI TABEL ADMINLTE 4 (PERUNGGU)
// Mengikuti Desain & Fitur utspemrogramanweb/daftar_nilai.php
// ========================================================
require_once __DIR__ . '/../connect.php';

if (!function_exists('get_count')) {
    function get_count($conn, $table, $where = "1=1") {
        $res = mysqli_query($conn, "SELECT COUNT(*) FROM `$table` WHERE $where");
        if ($res && $row = mysqli_fetch_row($res)) {
            return (int)$row[0];
        }
        return 0;
    }
}

// 1. Hitung Statistik untuk Sidebar Badges
$count_albums   = get_count($conn, 'albums');
$count_tracks   = get_count($conn, 'tracks');
$count_products = get_count($conn, 'products');
$count_orders   = get_count($conn, 'orders');
$count_tour     = get_count($conn, 'tour_dates');
$count_articles = get_count($conn, 'articles');
$count_gallery  = get_count($conn, 'gallery');
$count_subs     = get_count($conn, 'subscribers');
$count_unread   = get_count($conn, 'contact_messages', "is_read = 0");

// 2. Metadata 11 Tabel & Pembagian Hak Operasi CRUD (Sesuai rancangan_basis_data.md)
$allowed_tables = [
    'orders' => [
        'title' => 'Pesanan Merchandise',
        'single_name' => 'Pesanan',
        'file' => 'daftar_pesanan.php',
        'icon' => 'bi-receipt',
        'desc' => 'Daftar transaksi dengan status pembayaran dan pengiriman yang terpisah.',
        'search_cols' => ['order_code', 'customer_name', 'whatsapp', 'address'],
        'search_placeholder' => 'Cari nama / kode order...',
        'filter_col' => 'payment_status',
        'filter_label' => 'Status Pembayaran',
        'sort_cols' => ['id', 'total_amount', 'created_at'],
        'default_sort' => 'id',
        'can_create' => false, // Dibuat pengunjung saat checkout shop.html
        'can_update' => true,  // Admin memperbarui status pembayaran/pengiriman
        'can_delete' => false,
        'crud_type' => 'R, U (Create via Checkout; delete disabled)',
        'actor' => 'Pengunjung (Checkout)'
    ],
    'products' => [
        'title' => 'Katalog Merchandise Toko',
        'single_name' => 'Produk',
        'file' => 'daftar_produk.php',
        'icon' => 'bi-bag-check',
        'desc' => 'Katalog merchandise resmi; produk terbaru dengan stok tersedia tampil sebagai sorotan beranda.',
        'search_cols' => ['name'],
        'search_placeholder' => 'Cari nama produk...',
        'filter_col' => 'category',
        'filter_label' => 'Kategori',
        'sort_cols' => ['id', 'price', 'stock'],
        'default_sort' => 'id',
        'can_create' => true,
        'can_update' => true,
        'can_delete' => true,
        'crud_type' => 'Full CRUD (C, R, U, D)',
        'actor' => 'Admin Band'
    ],
    'tour_dates' => [
        'title' => 'Jadwal Konser & Tur',
        'single_name' => 'Jadwal Tur',
        'file' => 'daftar_tur.php',
        'icon' => 'bi-calendar-event',
        'desc' => 'Agenda jadwal pertunjukan panggung, festival, kota, venue, dan status tiket.',
        'search_cols' => ['event_name', 'city', 'venue'],
        'search_placeholder' => 'Cari kota / acara...',
        'filter_col' => 'status',
        'filter_label' => 'Status Tiket',
        'sort_cols' => ['id', 'event_date'],
        'default_sort' => 'event_date',
        'can_create' => true,
        'can_update' => true,
        'can_delete' => true,
        'crud_type' => 'Full CRUD (C, R, U, D)',
        'actor' => 'Admin Band'
    ],
    'albums' => [
        'title' => 'Katalog Album Musik',
        'single_name' => 'Album',
        'file' => 'daftar_album.php',
        'icon' => 'bi-disc',
        'desc' => 'Daftar rilisan karya album resmi grup band PERUNGGU.',
        'search_cols' => ['title', 'deskripsi'],
        'search_placeholder' => 'Cari judul album...',
        'filter_col' => '',
        'filter_label' => '',
        'sort_cols' => ['id', 'release_year'],
        'default_sort' => 'release_year',
        'can_create' => true,
        'can_update' => true,
        'can_delete' => true,
        'crud_type' => 'Full CRUD (C, R, U, D)',
        'actor' => 'Admin Band'
    ],
    'tracks' => [
        'title' => 'Trek Musik & Audio',
        'single_name' => 'Trek Lagu',
        'file' => 'daftar_track.php',
        'icon' => 'bi-music-note-beamed',
        'desc' => 'Daftar trek lagu, durasi, status pemutar audio, dan album.',
        'search_cols' => ['title'],
        'search_placeholder' => 'Cari judul lagu...',
        'filter_col' => '',
        'filter_label' => '',
        'sort_cols' => ['id', 'title'],
        'default_sort' => 'id',
        'can_create' => true,
        'can_update' => true,
        'can_delete' => true,
        'crud_type' => 'Full CRUD (C, R, U, D)',
        'actor' => 'Admin Band'
    ],
    'contact_messages' => [
        'title' => 'Pesan Masuk Pengunjung',
        'single_name' => 'Pesan',
        'file' => 'daftar_pesan.php',
        'icon' => 'bi-chat-left-dots',
        'desc' => 'Kotak surat pertanyaan dan pesan kerja sama dari pengunjung.',
        'search_cols' => ['name', 'email', 'subject', 'message'],
        'search_placeholder' => 'Cari nama / subjek...',
        'filter_col' => 'is_read',
        'filter_label' => 'Status Baca',
        'sort_cols' => ['id', 'created_at'],
        'default_sort' => 'id',
        'can_create' => false, // Dikirim oleh pengunjung melalui form kontak
        'can_update' => true,  // Update penanda status sudah dibaca (is_read)
        'can_delete' => true,
        'crud_type' => 'R, U, D (Create via Form Kontak)',
        'actor' => 'Pengunjung (Kirim Pesan)'
    ],
    'gallery' => [
        'title' => 'Galeri Foto Dokumentasi',
        'single_name' => 'Foto Galeri',
        'file' => 'daftar_galeri.php',
        'icon' => 'bi-images',
        'desc' => 'Arsip dokumentasi; tiga foto terbaru menjadi slide hero dan empat foto terbaru tampil di beranda.',
        'search_cols' => ['title', 'caption'],
        'search_placeholder' => 'Cari judul foto...',
        'filter_col' => 'category',
        'filter_label' => 'Kategori Foto',
        'sort_cols' => ['id', 'created_at'],
        'default_sort' => 'id',
        'can_create' => true,
        'can_update' => true,
        'can_delete' => true,
        'crud_type' => 'Full CRUD (C, R, U, D)',
        'actor' => 'Admin Band'
    ],
    'articles' => [
        'title' => 'Berita & Artikel Band',
        'single_name' => 'Artikel',
        'file' => 'daftar_artikel.php',
        'icon' => 'bi-newspaper',
        'desc' => 'Publikasi artikel, berita resmi, dan siaran pers.',
        'search_cols' => ['title', 'content'],
        'search_placeholder' => 'Cari judul artikel...',
        'filter_col' => 'category',
        'filter_label' => 'Kategori Berita',
        'sort_cols' => ['id', 'published_at'],
        'default_sort' => 'id',
        'can_create' => true,
        'can_update' => true,
        'can_delete' => true,
        'crud_type' => 'Full CRUD (C, R, U, D)',
        'actor' => 'Admin Band'
    ],
    'subscribers' => [
        'title' => 'Pelanggan Newsletter',
        'single_name' => 'Subscriber',
        'file' => 'daftar_subscriber.php',
        'icon' => 'bi-envelope-check',
        'desc' => 'Daftar email fans pendaftar newsletter.',
        'search_cols' => ['email'],
        'search_placeholder' => 'Cari email...',
        'filter_col' => '',
        'filter_label' => '',
        'sort_cols' => ['id', 'subscribed_at'],
        'default_sort' => 'id',
        'can_create' => false, // Diisi oleh fans dari formulir newsletter
        'can_update' => false, // Email dikirim oleh pengunjung dan tidak dapat diubah admin.
        'can_delete' => true,  // Hapus / Unsubscribe
        'crud_type' => 'R, D (Create via Newsletter)',
        'actor' => 'Pengunjung (Newsletter)'
    ],
    'admins' => [
        'title' => 'Akun Administrator',
        'single_name' => 'Admin',
        'file' => 'daftar_admin.php',
        'icon' => 'bi-person-lock',
        'desc' => 'Daftar pengelola dengan hak akses dashboard admin.',
        'search_cols' => ['username', 'email'],
        'search_placeholder' => 'Cari username...',
        'filter_col' => '',
        'filter_label' => '',
        'sort_cols' => ['id', 'created_at'],
        'default_sort' => 'id',
        'can_create' => true,
        'can_update' => true,
        'can_delete' => true,
        'crud_type' => 'Full CRUD (C, R, U, D)',
        'actor' => 'Superadmin'
    ],
    'order_items' => [
        'title' => 'Rincian Item Pesanan',
        'single_name' => 'Item Pesanan',
        'file' => 'daftar_order_items.php',
        'icon' => 'bi-list-check',
        'desc' => 'Rincian produk yang dikirim pengunjung melalui checkout.',
        'search_cols' => [],
        'search_placeholder' => 'Cari item...',
        'filter_col' => '',
        'filter_label' => '',
        'sort_cols' => ['id', 'quantity'],
        'default_sort' => 'id',
        'can_create' => false, // Dibuat otomatis saat checkout merujuk keranjang
        'can_update' => false, // Item checkout tidak dapat diubah admin.
        'can_delete' => false,
        'crud_type' => 'R (Create via Checkout; edit/delete disabled)',
        'actor' => 'Pengunjung (Keranjang)'
    ]
];

// Validasi tabel aktif
if (!isset($active_table) || !array_key_exists($active_table, $allowed_tables)) {
    $active_table = 'orders';
}
$table_meta = $allowed_tables[$active_table];
$current_file = $table_meta['file'];

// 1. Pencarian
$keyword = trim((string) ($_GET['keyword'] ?? ''));

// 2. Filter Predikat / Kategori
$filterVal = trim((string) ($_GET['filter'] ?? ''));

// 3. Sorting
$sortCol = trim((string) ($_GET['sort'] ?? $table_meta['default_sort']));
if (!in_array($sortCol, $table_meta['sort_cols'], true)) {
    $sortCol = $table_meta['default_sort'];
}
$urut = strtoupper(trim((string) ($_GET['urut'] ?? 'DESC')));
if (!in_array($urut, ['ASC', 'DESC'], true)) {
    $urut = 'DESC';
}
$urutBerikutnya = ($urut === 'DESC') ? 'ASC' : 'DESC';

// 4. Pagination (Limit 5, 10, 15)
$pilihanLimit = [5, 10, 15];
$perHalaman = filter_var($_GET['limit'] ?? 5, FILTER_VALIDATE_INT);
if (!in_array($perHalaman, $pilihanLimit, true)) {
    $perHalaman = 5;
}
$halamanDiminta = filter_var($_GET['halaman'] ?? 1, FILTER_VALIDATE_INT);
$halamanDiminta = ($halamanDiminta !== false && $halamanDiminta > 0) ? $halamanDiminta : 1;

// Filter Options dari DB
$filterOptions = [];
if (!empty($table_meta['filter_col'])) {
    $fCol = $table_meta['filter_col'];
    $fRes = mysqli_query($conn, "SELECT DISTINCT `$fCol` FROM `$active_table` WHERE `$fCol` IS NOT NULL AND `$fCol` <> '' ORDER BY `$fCol` ASC");
    if ($fRes) {
        while ($fRow = mysqli_fetch_row($fRes)) {
            $filterOptions[] = $fRow[0];
        }
    }
}
if ($filterVal !== '' && !in_array($filterVal, $filterOptions, true)) {
    $filterVal = '';
}

// Bangun WHERE SQL
$where_clauses = [];
if ($keyword !== '' && !empty($table_meta['search_cols'])) {
    $kwEscaped = mysqli_real_escape_string($conn, $keyword);
    $searchParts = [];
    foreach ($table_meta['search_cols'] as $sCol) {
        $searchParts[] = "`$sCol` LIKE '%$kwEscaped%'";
    }
    $where_clauses[] = '(' . implode(' OR ', $searchParts) . ')';
}
if ($filterVal !== '' && !empty($table_meta['filter_col'])) {
    $fEscaped = mysqli_real_escape_string($conn, $filterVal);
    $where_clauses[] = "`" . $table_meta['filter_col'] . "` = '$fEscaped'";
}
$whereSql = !empty($where_clauses) ? ' WHERE ' . implode(' AND ', $where_clauses) : '';

// Hitung total
$countRes = mysqli_query($conn, "SELECT COUNT(*) FROM `$active_table` $whereSql");
$totalData = $countRes ? (int)mysqli_fetch_row($countRes)[0] : 0;

$totalHalaman = max(1, (int) ceil($totalData / $perHalaman));
$halaman = min($halamanDiminta, $totalHalaman);
$offset = ($halaman - 1) * $perHalaman;
$awalData = $totalData > 0 ? $offset + 1 : 0;
$akhirData = min($offset + $perHalaman, $totalData);

// Query sorting URL
$queryUrut = http_build_query([
    'keyword' => $keyword,
    'filter' => $filterVal,
    'sort' => $sortCol,
    'urut' => $urutBerikutnya,
    'limit' => $perHalaman,
]);

// Ambil baris
if ($active_table === 'order_items') {
    $sql = "SELECT oi.*, o.order_code, o.customer_name, p.name AS product_name, p.price AS product_price, p.image_url AS product_image
            FROM order_items oi
            LEFT JOIN orders o ON oi.order_id = o.id
            LEFT JOIN products p ON oi.product_id = p.id
            $whereSql
            ORDER BY oi.`$sortCol` $urut LIMIT $perHalaman OFFSET $offset";
} else {
    $sql = "SELECT * FROM `$active_table` $whereSql ORDER BY `$sortCol` $urut LIMIT $perHalaman OFFSET $offset";
}
$table_data = mysqli_query($conn, $sql);

function urlHalamanItem(string $file, int $nomor, string $keyword, string $filter, string $sort, string $urut, int $limit = 5): string {
    return $file . '?' . http_build_query([
        'keyword' => $keyword,
        'filter' => $filter,
        'sort' => $sort,
        'urut' => $urut,
        'limit' => $limit,
        'halaman' => $nomor,
    ]);
}
?>
<!doctype html>
<html lang="id">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?= htmlspecialchars($table_meta['title']) ?> — Admin PERUNGGU</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="all" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="./css/adminlte.css" />
    <link rel="stylesheet" href="./css/delete-confirmation.css" />

    <style>
      .perunggu-brand { letter-spacing: 2px; font-weight: 800; color: #d4a373 !important; }
      .nav-treeview .nav-link { padding-left: 2.2rem !important; }
    </style>
  </head>
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

      <!-- NAVBAR -->
      <nav class="app-header navbar navbar-expand bg-body shadow-sm">
        <div class="container-fluid">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle Sidebar">
                <i class="bi bi-list fs-5"></i>
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="../../web_perunggu/index.html" target="_blank" class="nav-link text-primary fw-medium">
                <i class="bi bi-box-arrow-up-right me-1"></i> Buka Website Publik
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="index.php" class="nav-link">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard Utama
              </a>
            </li>
          </ul>

          <ul class="navbar-nav ms-auto align-items-center">
            <!-- Messages Dropdown -->
            <li class="nav-item dropdown">
              <a class="nav-link position-relative" data-bs-toggle="dropdown" href="#" aria-label="Pesan">
                <i class="bi bi-envelope fs-5"></i>
                <?php if ($count_unread > 0): ?>
                  <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= $count_unread ?>
                  </span>
                <?php endif; ?>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <span class="dropdown-item dropdown-header font-weight-bold"><?= $count_unread ?> Pesan Belum Dibaca</span>
                <div class="dropdown-divider"></div>
                <a href="daftar_pesan.php" class="dropdown-item dropdown-footer text-primary">Buka Pesan Masuk</a>
              </div>
            </li>

            <!-- User Menu -->
            <li class="nav-item dropdown user-menu ms-2">
              <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                <img src="./assets/img/perunggu-logo.jpeg" class="user-image rounded-circle shadow-sm" alt="Admin" style="width: 32px; height: 32px; object-fit: cover;" />
                <span class="d-none d-md-inline fw-semibold"><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-dark text-center py-3">
                  <img src="./assets/img/perunggu-logo.jpeg" class="rounded-circle shadow mb-2" alt="Perunggu" style="width: 60px; height: 60px; object-fit: cover;" />
                  <p class="mb-0 fw-bold"><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></p>
                  <small class="text-white-50">Database: perunggu_db (MySQL)</small>
                </li>
                <li class="user-footer d-flex justify-content-between p-2">
                  <a href="daftar_admin.php" class="btn btn-sm btn-outline-secondary">Akun Admin</a>
                  <a href="../index.php" class="btn btn-sm btn-outline-danger">DB Manager</a>
                  <a href="../logout.php" class="btn btn-sm btn-outline-secondary">Keluar</a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>

      <!-- SIDEBAR PERSIS SEPERTI STRUKTUR DI utspemrogramanweb -->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand d-flex align-items-center justify-content-start px-3 py-2 border-bottom border-secondary border-opacity-25">
          <a href="./index.php" class="brand-link d-flex align-items-center gap-2 text-decoration-none">
            <img src="./assets/img/perunggu-logo.jpeg" alt="Logo" class="brand-image rounded shadow" style="width: 34px; height: 34px; object-fit: cover;" />
            <span class="brand-text perunggu-brand fs-5">PERUNGGU</span>
          </a>
        </div>

        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
              
              <!-- MENU DASHBOARD -->
              <li class="nav-item">
                <a href="index.php" class="nav-link">
                  <i class="nav-icon bi bi-speedometer2"></i>
                  <p>Dashboard</p>
                </a>
              </li>

              <!-- 1. KARYA MUSIK & AUDIO -->
              <li class="nav-header">KARYA MUSIK & AUDIO</li>
              <li class="nav-item">
                <a href="daftar_album.php" class="nav-link <?= $active_table === 'albums' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-disc"></i>
                  <p>Albums (<?= $count_albums ?>)</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="daftar_track.php" class="nav-link <?= $active_table === 'tracks' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-music-note-beamed"></i>
                  <p>Tracks & Audio (<?= $count_tracks ?>)</p>
                </a>
              </li>

              <!-- 2. TUR & PENAMPILAN -->
              <li class="nav-header">TUR & PENAMPILAN</li>
              <li class="nav-item">
                <a href="daftar_tur.php" class="nav-link <?= $active_table === 'tour_dates' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-calendar-event"></i>
                  <p>Jadwal Tur (<?= $count_tour ?>)</p>
                </a>
              </li>

              <!-- 3. MERCHANDISE & TRANSAKSI -->
              <li class="nav-header">MERCHANDISE & TRANSAKSI</li>
              <li class="nav-item">
                <a href="daftar_produk.php" class="nav-link <?= $active_table === 'products' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-bag-check"></i>
                  <p>Produk Toko (<?= $count_products ?>)</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="daftar_pesanan.php" class="nav-link <?= $active_table === 'orders' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-receipt"></i>
                  <p>Pesanan Masuk (<?= $count_orders ?>)</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="index.php#section-revenue" class="nav-link">
                  <i class="nav-icon bi bi-graph-up-arrow"></i>
                  <p>Rincian Pendapatan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="daftar_order_items.php" class="nav-link <?= $active_table === 'order_items' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-list-check"></i>
                  <p>Rincian Item Pesanan</p>
                </a>
              </li>

              <!-- 4. KONTEN & MEDIA -->
              <li class="nav-header">KONTEN & MEDIA</li>
              <li class="nav-item">
                <a href="daftar_artikel.php" class="nav-link <?= $active_table === 'articles' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-newspaper"></i>
                  <p>Berita & Artikel (<?= $count_articles ?>)</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="daftar_galeri.php" class="nav-link <?= $active_table === 'gallery' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-images"></i>
                  <p>Galeri Dokumentasi (<?= $count_gallery ?>)</p>
                </a>
              </li>

              <!-- 5. INTERAKSI FANS -->
              <li class="nav-header">INTERAKSI FANS</li>
              <li class="nav-item">
                <a href="daftar_pesan.php" class="nav-link <?= $active_table === 'contact_messages' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-chat-left-dots"></i>
                  <p>
                    Pesan Kontak
                    <?php if ($count_unread > 0): ?>
                      <span class="badge text-bg-danger ms-1"><?= $count_unread ?> baru</span>
                    <?php endif; ?>
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="daftar_subscriber.php" class="nav-link <?= $active_table === 'subscribers' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-envelope-check"></i>
                  <p>Subscribers (<?= $count_subs ?>)</p>
                </a>
              </li>

              <!-- 6. PENGATURAN SISTEM -->
              <li class="nav-header">PENGATURAN SISTEM</li>
              <li class="nav-item">
                <a href="daftar_admin.php" class="nav-link <?= $active_table === 'admins' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-person-lock"></i>
                  <p>Kelola Admin</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="../index.php" class="nav-link">
                  <i class="nav-icon bi bi-database-gear"></i>
                  <p>Database Manager</p>
                </a>
              </li>

            </ul>
          </nav>
        </div>
      </aside>

      <!-- APP MAIN CONTENT -->
      <main class="app-main">
        <!-- Content Header / Breadcrumb -->
        <div class="app-content-header py-3">
          <div class="container-fluid">
            <div class="row align-items-center">
              <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><?= htmlspecialchars($table_meta['title']) ?></h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0">
                  <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($table_meta['title']) ?></li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <!-- Notifikasi Status Aksi CRUD -->
            <?php 
              $msg_type = $_GET['status'] ?? '';
              $msg_text = $_GET['msg'] ?? '';
            ?>
            <?php if (!empty($msg_text)): ?>
              <div class="alert alert-<?= $msg_type === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm mb-3" role="alert">
                <i class="bi <?= $msg_type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
                <strong><?= $msg_type === 'success' ? 'Berhasil!' : 'Perhatian!' ?></strong> <?= htmlspecialchars(urldecode($msg_text)) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <!-- CARD DIREKTORI DATA TABEL (PERSIS SEPERTI DI UTS DAFTAR_NILAI.PHP) -->
            <div class="card mb-4 shadow-sm border">
              
              <!--begin::Card Header-->
              <div class="card-header bg-body py-3">
                <div class="row g-2 align-items-center">
                  <div class="col-12 col-md-5">
                    <h3 class="card-title fw-bold mb-0 d-flex align-items-center gap-2 flex-wrap">
                      <span>Direktori <?= htmlspecialchars($table_meta['title']) ?></span>
                      <span class="badge <?= $table_meta['can_create'] && $table_meta['can_update'] ? 'text-bg-success' : 'text-bg-info' ?> fs-7 fw-normal" title="Operasi CRUD">
                        <i class="bi <?= $table_meta['can_create'] && $table_meta['can_update'] ? 'bi-check-all' : 'bi-shield-check' ?> me-1"></i><?= htmlspecialchars($table_meta['crud_type']) ?>
                      </span>
                    </h3>
                  </div>
                  <div class="col-12 col-md-7">
                    <div class="d-flex flex-wrap justify-content-md-end gap-2 align-items-center">
                      <!-- Form Filter & Search (GET) -->
                      <form id="formFilterTabel" action="<?= htmlspecialchars($current_file) ?>" method="get" class="d-flex flex-wrap justify-content-md-end gap-2">
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sortCol) ?>">
                        <input type="hidden" name="urut" value="<?= htmlspecialchars($urut) ?>">
                        <input type="hidden" name="limit" value="<?= (int) $perHalaman ?>">

                        <!-- Input Cari / Search -->
                        <div class="input-group input-group-sm w-auto">
                          <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                          <input type="search" id="tabel-search" name="keyword"
                                 class="form-control" placeholder="<?= htmlspecialchars($table_meta['search_placeholder'] ?? 'Cari data...') ?>"
                                 aria-label="Cari data" style="width: 190px"
                                 value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <!-- Filter Kategori / Status Dinamis -->
                        <?php if (!empty($filterOptions)): ?>
                          <select id="tabel-filter" name="filter"
                                  class="form-select form-select-sm w-auto"
                                  aria-label="Filter"
                                  onchange="this.form.submit()">
                            <option value="">Semua <?= htmlspecialchars($table_meta['filter_label']) ?></option>
                            <?php foreach ($filterOptions as $opsi): ?>
                              <option value="<?= htmlspecialchars($opsi, ENT_QUOTES, 'UTF-8') ?>"
                                <?= $filterVal === (string)$opsi ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $opsi)), ENT_QUOTES, 'UTF-8') ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        <?php endif; ?>

                        <!-- Tombol Cari -->
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Jalankan pencarian">
                          <i class="bi bi-search"></i>
                        </button>

                        <!-- Tombol Reset -->
                        <?php if ($keyword !== '' || $filterVal !== ''): ?>
                          <a href="<?= htmlspecialchars($current_file) ?>" class="btn btn-sm btn-outline-secondary" title="Reset pencarian">Reset</a>
                        <?php endif; ?>
                      </form>

                      <!-- Tombol Tambah Data Pake Form Modal (Hanya Jika Tabel Mengizinkan Create Manual) -->
                      <?php if (!empty($table_meta['can_create'])): ?>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahData">
                          <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>
                          Tambah Data <?= htmlspecialchars($table_meta['single_name']) ?>
                        </button>
                      <?php else: ?>
                        <span class="badge text-bg-secondary bg-opacity-10 text-secondary border py-2 px-3 small d-inline-flex align-items-center" title="Data diisi otomatis oleh pengunjung">
                          <i class="bi bi-box-arrow-in-down-right text-primary me-1"></i> Data dari <?= htmlspecialchars($table_meta['actor']) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
              <!--end::Card Header-->

              <!--begin::Card Body-->
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover align-middle m-0">
                    <thead class="table-light">
                      <tr>
                        <?php if ($active_table === 'orders'): ?>
                          <th>#ID</th>
                          <th>Kode Order</th>
                          <th>Pelanggan</th>
                          <th>WhatsApp</th>
                          <th>Alamat Pengiriman</th>
                          <th>Metode Bayar</th>
                          <th>
                            <a href="<?= htmlspecialchars(urlHalamanItem($current_file, 1, $keyword, $filterVal, 'total_amount', $urutBerikutnya, $perHalaman), ENT_QUOTES, 'UTF-8') ?>"
                               class="text-reset text-decoration-none d-inline-flex align-items-center gap-1"
                               title="Urutkan total harga">
                              Total Bayar
                              <i class="bi <?= $sortCol === 'total_amount' && $urut === 'ASC' ? 'bi-sort-numeric-up' : 'bi-sort-numeric-down' ?>" aria-hidden="true"></i>
                            </a>
                          </th>
                          <th>Status Pembayaran</th>
                          <th>Status Pengiriman</th>
                          <th>
                            <a href="<?= htmlspecialchars(urlHalamanItem($current_file, 1, $keyword, $filterVal, 'created_at', $urutBerikutnya, $perHalaman), ENT_QUOTES, 'UTF-8') ?>"
                               class="text-reset text-decoration-none d-inline-flex align-items-center gap-1"
                               title="Urutkan tanggal">
                              Waktu Pesan
                              <i class="bi <?= $sortCol === 'created_at' && $urut === 'ASC' ? 'bi-sort-numeric-up' : 'bi-sort-numeric-down' ?>" aria-hidden="true"></i>
                            </a>
                          </th>
                          <th class="text-center" style="width: 100px;">Aksi</th>

                        <?php elseif ($active_table === 'products'): ?>
                          <th>Foto & Nama Produk</th>
                          <th>Kategori</th>
                          <th>
                            <a href="<?= htmlspecialchars(urlHalamanItem($current_file, 1, $keyword, $filterVal, 'price', $urutBerikutnya, $perHalaman), ENT_QUOTES, 'UTF-8') ?>"
                               class="text-reset text-decoration-none d-inline-flex align-items-center gap-1"
                               title="Urutkan harga">
                              Harga Satuan
                              <i class="bi <?= $sortCol === 'price' && $urut === 'ASC' ? 'bi-sort-numeric-up' : 'bi-sort-numeric-down' ?>" aria-hidden="true"></i>
                            </a>
                          </th>
                          <th>
                            <a href="<?= htmlspecialchars(urlHalamanItem($current_file, 1, $keyword, $filterVal, 'stock', $urutBerikutnya, $perHalaman), ENT_QUOTES, 'UTF-8') ?>"
                               class="text-reset text-decoration-none d-inline-flex align-items-center gap-1"
                               title="Urutkan stok">
                              Stok
                              <i class="bi <?= $sortCol === 'stock' && $urut === 'ASC' ? 'bi-sort-numeric-up' : 'bi-sort-numeric-down' ?>" aria-hidden="true"></i>
                            </a>
                          </th>
                          <th class="text-center" style="width: 100px;">Aksi</th>

                        <?php elseif ($active_table === 'tour_dates'): ?>
                          <th>Nama Acara / Konser</th>
                          <th>Kota & Venue</th>
                          <th>
                            <a href="<?= htmlspecialchars(urlHalamanItem($current_file, 1, $keyword, $filterVal, 'event_date', $urutBerikutnya, $perHalaman), ENT_QUOTES, 'UTF-8') ?>"
                               class="text-reset text-decoration-none d-inline-flex align-items-center gap-1"
                               title="Urutkan tanggal konser">
                              Tanggal & Waktu
                              <i class="bi <?= $sortCol === 'event_date' && $urut === 'ASC' ? 'bi-sort-numeric-up' : 'bi-sort-numeric-down' ?>" aria-hidden="true"></i>
                            </a>
                          </th>
                          <th>Status Tiket</th>
                          <th>Tautan Tiket</th>
                          <th class="text-center" style="width: 100px;">Aksi</th>

                        <?php elseif ($active_table === 'albums'): ?>
                          <th>Cover & Judul Album</th>
                          <th>
                            <a href="<?= htmlspecialchars(urlHalamanItem($current_file, 1, $keyword, $filterVal, 'release_year', $urutBerikutnya, $perHalaman), ENT_QUOTES, 'UTF-8') ?>"
                               class="text-reset text-decoration-none d-inline-flex align-items-center gap-1"
                               title="Urutkan tahun rilis">
                              Tahun Rilis
                              <i class="bi <?= $sortCol === 'release_year' && $urut === 'ASC' ? 'bi-sort-numeric-up' : 'bi-sort-numeric-down' ?>" aria-hidden="true"></i>
                            </a>
                          </th>
                          <th>Deskripsi Album</th>
                          <th class="text-center" style="width: 100px;">Aksi</th>

                        <?php elseif ($active_table === 'tracks'): ?>
                          <th>
                            <a href="<?= htmlspecialchars(urlHalamanItem($current_file, 1, $keyword, $filterVal, 'id', $urutBerikutnya, $perHalaman), ENT_QUOTES, 'UTF-8') ?>"
                               class="text-reset text-decoration-none d-inline-flex align-items-center gap-1"
                               title="Urutkan ID">
                              #ID
                              <i class="bi <?= $sortCol === 'id' && $urut === 'ASC' ? 'bi-sort-numeric-up' : 'bi-sort-numeric-down' ?>" aria-hidden="true"></i>
                            </a>
                          </th>
                          <th>Judul Trek Lagu</th>
                          <th>Album ID</th>
                          <th>Durasi</th>
                          <th>Now Playing</th>
                          <th>Audio File</th>
                          <th class="text-center" style="width: 100px;">Aksi</th>

                        <?php elseif ($active_table === 'contact_messages'): ?>
                          <th>Pengirim</th>
                          <th>Subjek Pesan</th>
                          <th>Isi Pesan</th>
                          <th>Status Baca</th>
                          <th>Waktu Masuk</th>
                          <th class="text-center" style="width: 100px;">Aksi</th>

                        <?php elseif ($active_table === 'gallery'): ?>
                          <th>Foto & Judul Dokumentasi</th>
                          <th>Kategori</th>
                          <th>Keterangan / Caption</th>
                          <th>Waktu Unggah</th>
                          <th class="text-center" style="width: 100px;">Aksi</th>

                        <?php elseif ($active_table === 'articles'): ?>
                          <th>Thumbnail & Judul Berita</th>
                          <th>Kategori</th>
                          <th>Tanggal Terbit</th>
                          <th class="text-center" style="width: 100px;">Aksi</th>

                        <?php elseif ($active_table === 'subscribers'): ?>
                          <th>#ID</th>
                          <th>Email Penggemar</th>
                          <th>Tanggal Berlangganan</th>
                          <th class="text-center" style="width: 100px;">Aksi</th>

                        <?php elseif ($active_table === 'admins'): ?>
                          <th>Username</th>
                          <th>Email Akun</th>
                          <th>Kata Sandi</th>
                          <th>Dibuat Tanggal</th>
                          <th class="text-center" style="width: 100px;">Aksi</th>

                        <?php elseif ($active_table === 'order_items'): ?>
                          <th>#ID</th>
                          <th>Pesanan Induk</th>
                          <th>Produk Merchandise</th>
                          <th>Harga Satuan</th>
                          <th>Jumlah (Qty)</th>
                          <th>Subtotal</th>
                          <th class="text-center" style="width: 100px;">Aksi</th>

                        <?php else: ?>
                          <?php
                          $fields = mysqli_fetch_fields($table_data);
                          foreach ($fields as $field):
                          ?>
                            <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $field->name))) ?></th>
                          <?php endforeach; ?>
                          <th class="text-center" style="width: 100px;">Aksi</th>
                        <?php endif; ?>
                      </tr>
                    </thead>

                    <tbody>
                      <?php if ($table_data && mysqli_num_rows($table_data) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($table_data)): ?>
                          <tr>
                            <?php if ($active_table === 'orders'): ?>
                              <td><span class="text-muted fs-7">#<?= $row['id'] ?></span></td>
                              <td><span class="badge text-bg-light border fw-bold"><?= htmlspecialchars($row['order_code']) ?></span></td>
                              <td>
                                <div class="d-flex align-items-center gap-2">
                                  <span class="rounded-circle bg-body-secondary d-inline-flex align-items-center justify-content-center border" style="width:38px;height:38px"><i class="bi bi-person-fill text-body-secondary"></i></span>
                                  <div><strong><?= htmlspecialchars($row['customer_name']) ?></strong></div>
                                </div>
                              </td>
                              <td><span class="small text-muted"><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($row['whatsapp']) ?></span></td>
                              <td><small class="text-secondary"><?= htmlspecialchars($row['address']) ?></small></td>
                              <td><span class="badge text-bg-secondary"><?= htmlspecialchars($row['payment_method']) ?></span></td>
                              <td><strong>Rp <?= number_format((float)$row['total_amount'], 0, ',', '.') ?></strong></td>
                              <?php
                                $payment_status_labels = [
                                    'pending' => 'Belum Dibayar',
                                    'paid' => 'Lunas',
                                    'refunded' => 'Refund'
                                ];
                                $payment_badge_class = $row['payment_status'] === 'paid'
                                    ? 'text-bg-success'
                                    : ($row['payment_status'] === 'refunded' ? 'text-bg-secondary' : 'text-bg-warning');
                                $shipping_status_labels = [
                                    'pending' => 'Menunggu',
                                    'shipped' => 'Dikirim',
                                    'delivered' => 'Diterima',
                                    'cancelled' => 'Dibatalkan'
                                ];
                                $shipping_badge_class = $row['shipping_status'] === 'delivered'
                                    ? 'text-bg-success'
                                    : ($row['shipping_status'] === 'shipped'
                                        ? 'text-bg-info'
                                        : ($row['shipping_status'] === 'cancelled' ? 'text-bg-danger' : 'text-bg-warning'));
                              ?>
                              <td><span class="badge <?= $payment_badge_class ?>"><?= htmlspecialchars($payment_status_labels[$row['payment_status']] ?? $row['payment_status']) ?></span></td>
                              <td><span class="badge <?= $shipping_badge_class ?>"><?= htmlspecialchars($shipping_status_labels[$row['shipping_status']] ?? $row['shipping_status']) ?></span></td>
                              <td><small class="text-muted"><?= htmlspecialchars($row['created_at']) ?></small></td>

                            <?php elseif ($active_table === 'products'): ?>
                              <td>
                                <div class="d-flex align-items-center gap-2">
                                  <img src="../../web_perunggu/<?= htmlspecialchars($row['image_url']) ?>" alt="Foto" class="rounded-circle object-fit-cover border shadow-sm" style="width:38px;height:38px;object-fit:cover" onerror="this.src='./assets/img/perunggu-logo.jpeg'">
                                  <strong><?= htmlspecialchars($row['name']) ?></strong>
                                </div>
                              </td>
                              <td><span class="badge text-bg-secondary"><?= htmlspecialchars($row['category']) ?></span></td>
                              <td><strong>Rp <?= number_format((float)$row['price'], 0, ',', '.') ?></strong></td>
                              <td>
                                <span class="badge <?= $row['stock'] <= 5 ? 'text-bg-danger' : 'text-bg-success' ?> fs-7">
                                  <?= (int)$row['stock'] ?> pcs
                                </span>
                              </td>

                            <?php elseif ($active_table === 'tour_dates'): ?>
                              <td><strong><?= htmlspecialchars($row['event_name']) ?></strong></td>
                              <td>
                                <i class="bi bi-geo-alt text-danger me-1"></i>
                                <strong><?= htmlspecialchars($row['city']) ?></strong> &bull; <small class="text-muted"><?= htmlspecialchars($row['venue']) ?></small>
                              </td>
                              <td><small><i class="bi bi-clock me-1 text-muted"></i><?= htmlspecialchars($row['event_date']) ?></small></td>
                              <td>
                                <?php
                                  $tst = $row['status'];
                                  $tBadge = ($tst === 'available') ? 'text-bg-success' : (($tst === 'presale') ? 'text-bg-info' : 'text-bg-secondary');
                                ?>
                                <span class="badge <?= $tBadge ?>"><?= htmlspecialchars($tst) ?></span>
                              </td>
                              <td>
                                <?php if (!empty($row['ticket_url'])): ?>
                                  <a href="<?= htmlspecialchars($row['ticket_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 fs-7">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Beli
                                  </a>
                                <?php else: ?>
                                  <span class="text-muted fs-7">-</span>
                                <?php endif; ?>
                              </td>

                            <?php elseif ($active_table === 'albums'): ?>
                              <td>
                                <div class="d-flex align-items-center gap-2">
                                  <img src="../../web_perunggu/<?= htmlspecialchars($row['cover_image']) ?>" alt="Cover" class="rounded-circle object-fit-cover border shadow-sm" style="width:38px;height:38px;object-fit:cover" onerror="this.src='./assets/img/perunggu-logo.jpeg'">
                                  <strong><?= htmlspecialchars($row['title']) ?></strong>
                                </div>
                              </td>
                              <td><span class="badge text-bg-light border fw-bold"><?= (int)$row['release_year'] ?></span></td>
                              <td><small class="text-secondary"><?= htmlspecialchars(substr($row['deskripsi'] ?? '', 0, 75)) ?>...</small></td>

                            <?php elseif ($active_table === 'tracks'): ?>
                              <td><span class="badge text-bg-light border">#<?= (int)$row['id'] ?></span></td>
                              <td><strong><i class="bi bi-music-note me-1 text-primary"></i> <?= htmlspecialchars($row['title']) ?></strong></td>
                              <td><span class="text-muted">Album #<?= (int)$row['album_id'] ?></span></td>
                              <td><span class="badge text-bg-light border"><?= htmlspecialchars($row['duration']) ?></span></td>
                              <td>
                                <?= $row['is_now_playing'] ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Tidak</span>' ?>
                              </td>
                              <td>
                                <a href="../../web_perunggu/<?= htmlspecialchars($row['audio_url']) ?>" target="_blank" class="small text-decoration-none">
                                  <i class="bi bi-play-circle text-primary me-1"></i> Putar Audio
                                </a>
                              </td>

                            <?php elseif ($active_table === 'contact_messages'): ?>
                              <td>
                                <div class="d-flex align-items-center gap-2">
                                  <span class="rounded-circle bg-body-secondary d-inline-flex align-items-center justify-content-center border" style="width:38px;height:38px"><i class="bi bi-person-fill text-body-secondary"></i></span>
                                  <div>
                                    <strong><?= htmlspecialchars($row['name']) ?></strong>
                                    <small class="d-block text-muted"><?= htmlspecialchars($row['email']) ?></small>
                                  </div>
                                </div>
                              </td>
                              <td><strong class="text-primary"><?= htmlspecialchars($row['subject']) ?></strong></td>
                              <td><small class="text-secondary"><?= htmlspecialchars(substr($row['message'], 0, 70)) ?>...</small></td>
                              <td>
                                <?= $row['is_read'] ? '<span class="badge text-bg-secondary">Dibaca</span>' : '<span class="badge text-bg-danger">Baru</span>' ?>
                              </td>
                              <td><small class="text-muted"><?= htmlspecialchars($row['created_at']) ?></small></td>

                            <?php elseif ($active_table === 'gallery'): ?>
                              <td>
                                <div class="d-flex align-items-center gap-2">
                                  <img src="../../web_perunggu/<?= htmlspecialchars($row['image_url'] ?? '') ?>" alt="Foto" class="rounded-circle object-fit-cover border shadow-sm" style="width:38px;height:38px;object-fit:cover" onerror="this.src='./assets/img/perunggu-logo.jpeg'">
                                  <strong><?= htmlspecialchars($row['title'] ?? '') ?></strong>
                                </div>
                              </td>
                              <td><span class="badge text-bg-secondary"><?= htmlspecialchars($row['category'] ?? '') ?></span></td>
                              <td><small class="text-secondary"><?= htmlspecialchars($row['caption'] ?? '-') ?></small></td>
                              <td><small class="text-muted"><?= htmlspecialchars($row['created_at'] ?? '-') ?></small></td>

                            <?php elseif ($active_table === 'articles'): ?>
                              <td>
                                <div class="d-flex align-items-center gap-2">
                                  <img src="../../web_perunggu/<?= htmlspecialchars($row['thumbnail_url']) ?>" alt="Thumb" class="rounded-circle object-fit-cover border shadow-sm" style="width:38px;height:38px;object-fit:cover" onerror="this.src='./assets/img/perunggu-logo.jpeg'">
                                  <strong><?= htmlspecialchars($row['title']) ?></strong>
                                </div>
                              </td>
                              <td><span class="badge text-bg-secondary"><?= htmlspecialchars($row['category']) ?></span></td>
                              <td><small class="text-muted"><?= htmlspecialchars($row['published_at']) ?></small></td>

                            <?php elseif ($active_table === 'subscribers'): ?>
                              <td><span class="text-muted">#<?= $row['id'] ?></span></td>
                              <td><strong><i class="bi bi-envelope me-1 text-primary"></i> <?= htmlspecialchars($row['email']) ?></strong></td>
                              <td><small class="text-muted"><?= htmlspecialchars($row['subscribed_at']) ?></small></td>

                            <?php elseif ($active_table === 'admins'): ?>
                              <td><strong><i class="bi bi-person-check me-1 text-primary"></i> <?= htmlspecialchars($row['username']) ?></strong></td>
                              <td><?= htmlspecialchars($row['email']) ?></td>
                              <td><span class="badge text-bg-secondary"><i class="bi bi-shield-lock me-1"></i> Terenkripsi</span></td>
                              <td><small class="text-muted"><?= htmlspecialchars($row['created_at']) ?></small></td>

                            <?php elseif ($active_table === 'order_items'): ?>
                              <td><span class="text-muted fs-7">#<?= $row['id'] ?></span></td>
                              <td>
                                <span class="badge text-bg-light border fw-bold"><?= htmlspecialchars($row['order_code'] ?? ('Order #' . $row['order_id'])) ?></span><br>
                                <small class="text-secondary"><?= htmlspecialchars($row['customer_name'] ?? '-') ?></small>
                              </td>
                              <td>
                                <div class="d-flex align-items-center gap-2">
                                  <img src="../../web_perunggu/<?= htmlspecialchars($row['product_image'] ?? 'images/merch/yogya.png') ?>" alt="Foto" class="rounded-circle object-fit-cover border shadow-sm" style="width:38px;height:38px;object-fit:cover" onerror="this.src='./assets/img/perunggu-logo.jpeg'">
                                  <strong><?= htmlspecialchars($row['product_name'] ?? ('Produk #' . $row['product_id'])) ?></strong>
                                </div>
                              </td>
                              <td>Rp <?= number_format((float)($row['product_price'] ?? 0), 0, ',', '.') ?></td>
                              <td><span class="badge text-bg-primary fs-7"><?= (int)$row['quantity'] ?> pcs</span></td>
                              <td class="fw-bold text-success">Rp <?= number_format((float)(($row['product_price'] ?? 0) * (int)$row['quantity']), 0, ',', '.') ?></td>

                            <?php else: ?>
                              <?php foreach ($row as $cName => $cVal): ?>
                                <td><?= htmlspecialchars((string)$cVal) ?></td>
                              <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- KOLOM AKSI (TOMBOL KUNING EDIT + TOMBOL MERAH HAPUS) -->
                            <td class="text-center" style="white-space: nowrap;">
                              <!-- Tombol Edit (Hanya jika tabel mengizinkan Update) -->
                              <?php if (!empty($table_meta['can_update'])): ?>
                                <a href="../update.php?table=<?= urlencode($active_table) ?>&id=<?= (int)$row['id'] ?>&redirect=dist/<?= urlencode($current_file) ?>"
                                   class="btn btn-sm btn-warning"
                                   title="Edit data">
                                  <i class="bi bi-pencil-square"></i>
                                </a>
                              <?php endif; ?>

                              <!-- Tombol Hapus (Delete) -->
                              <?php if (!empty($table_meta['can_delete'])): ?>
                                <?php
                                  $delete_label = $row['order_code'] ?? $row['username'] ?? $row['name'] ?? $row['title'] ?? $row['event_name'] ?? $row['email'] ?? $row['subject'] ?? ('ID #' . (int)$row['id']);
                                ?>
                                <form action="../delete.php" method="POST" class="d-inline">
                                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                  <input type="hidden" name="table" value="<?= htmlspecialchars($active_table) ?>">
                                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                  <input type="hidden" name="redirect" value="dist/<?= htmlspecialchars($current_file) ?>">
                                  <button type="button" class="btn btn-sm btn-danger" title="Hapus data"
                                          data-delete-confirm
                                          data-table-label="<?= htmlspecialchars($table_meta['single_name'] ?? $active_table, ENT_QUOTES, 'UTF-8') ?>"
                                          data-record-label="<?= htmlspecialchars((string)$delete_label, ENT_QUOTES, 'UTF-8') ?>"
                                          data-record-id="<?= (int)$row['id'] ?>">
                                    <i class="bi bi-trash"></i>
                                  </button>
                                </form>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endwhile; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="<?= $active_table === 'orders' ? 11 : 10 ?>" class="text-center p-4 text-muted">
                            <?= ($keyword !== '' || $filterVal !== '') ? 'Data tidak ditemukan sesuai kata kunci pencarian.' : 'Belum ada data pada direktori ini.' ?>
                          </td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <!--end::Card Body-->

              <!--begin::Card Footer (Pagination Limit & Halaman)-->
              <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2 bg-body">
                <div class="d-flex align-items-center gap-2">
                  <label for="pilihLimit" class="fs-7 text-body-secondary mb-0">Tampilkan:</label>
                  <select id="pilihLimit" class="form-select form-select-sm w-auto" onchange="window.location.href=this.value;">
                    <?php foreach ($pilihanLimit as $optLimit): ?>
                      <option value="<?= htmlspecialchars(urlHalamanItem($current_file, 1, $keyword, $filterVal, $sortCol, $urut, $optLimit), ENT_QUOTES, 'UTF-8') ?>" <?= $perHalaman === $optLimit ? 'selected' : '' ?>>
                        <?= $optLimit ?> record/page
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <span class="fs-7 text-body-secondary">
                    (Menampilkan <?= $awalData ?>–<?= $akhirData ?> dari <?= $totalData ?> data)
                  </span>
                </div>

                <?php if ($totalHalaman >= 1): ?>
                  <nav aria-label="Navigasi halaman direktori">
                    <ul class="pagination pagination-sm mb-0">
                      <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars(urlHalamanItem($current_file, 1, $keyword, $filterVal, $sortCol, $urut, $perHalaman), ENT_QUOTES, 'UTF-8') ?>" <?= $halaman <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>First</a>
                      </li>
                      <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars(urlHalamanItem($current_file, max(1, $halaman - 1), $keyword, $filterVal, $sortCol, $urut, $perHalaman), ENT_QUOTES, 'UTF-8') ?>" <?= $halaman <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Previous</a>
                      </li>
                      <?php for ($nomor = 1; $nomor <= $totalHalaman; $nomor++): ?>
                        <li class="page-item <?= $nomor === $halaman ? 'active' : '' ?>">
                          <a class="page-link" href="<?= htmlspecialchars(urlHalamanItem($current_file, $nomor, $keyword, $filterVal, $sortCol, $urut, $perHalaman), ENT_QUOTES, 'UTF-8') ?>"><?= $nomor ?></a>
                        </li>
                      <?php endfor; ?>
                      <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars(urlHalamanItem($current_file, min($totalHalaman, $halaman + 1), $keyword, $filterVal, $sortCol, $urut, $perHalaman), ENT_QUOTES, 'UTF-8') ?>" <?= $halaman >= $totalHalaman ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Next</a>
                      </li>
                      <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars(urlHalamanItem($current_file, $totalHalaman, $keyword, $filterVal, $sortCol, $urut, $perHalaman), ENT_QUOTES, 'UTF-8') ?>" <?= $halaman >= $totalHalaman ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Last</a>
                      </li>
                    </ul>
                  </nav>
                <?php endif; ?>
              </div>
              <!--end::Card Footer-->

            </div>
            <!--end::Card-->

          </div>
        </div>
      </main>

      <!-- FOOTER -->
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">
          Basis Data: <strong>perunggu_db</strong> (MySQL 8.x) &bull; Semester 4 IT
        </div>
        <strong>Copyright &copy; 2026 <a href="../../web_perunggu/index.html" class="text-decoration-none">PERUNGGU</a>.</strong> All rights reserved.
      </footer>

    </div>

    <!-- ======================================================== -->
    <!-- MODAL FORM TAMBAH DATA (HANYA DITAMPILKAN JIKA TABEL MENDUKUNG CREATE MANUAL) -->
    <!-- ======================================================== -->
    <?php if (!empty($table_meta['can_create'])): ?>
    <div class="modal fade" id="modalTambahData" tabindex="-1" aria-labelledby="modalTambahDataLabel" aria-hidden="true">
      <div class="modal-dialog <?= in_array($active_table, ['orders', 'tour_dates', 'tracks', 'articles']) ? 'modal-lg' : '' ?>">
        <div class="modal-content">
          <form action="../crud_action.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="table" value="<?= htmlspecialchars($active_table) ?>">
            <input type="hidden" name="redirect" value="dist/<?= htmlspecialchars($current_file) ?>">

            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title fw-bold" id="modalTambahDataLabel">
                <i class="bi <?= $table_meta['icon'] ?> me-2"></i> Tambah Data <?= htmlspecialchars($table_meta['single_name']) ?>
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
              <?php if ($active_table === 'orders'): ?>
                <div class="row g-2 mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Kode Order <span class="text-danger">*</span></label>
                    <input type="text" name="order_code" class="form-control" value="PRG-<?= rand(100000, 999999) ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Pelanggan <span class="text-danger">*</span></label>
                    <input type="text" name="customer_name" class="form-control" placeholder="Nama pemesan" required>
                  </div>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">WhatsApp <span class="text-danger">*</span></label>
                    <input type="tel" name="whatsapp" class="form-control" placeholder="08xxxxxxxxxx" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select name="payment_method" class="form-select" required>
                      <option value="QRIS">QRIS</option>
                      <option value="Transfer BCA">Transfer BCA</option>
                      <option value="Transfer Mandiri">Transfer Mandiri</option>
                      <option value="COD">COD</option>
                    </select>
                  </div>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Total Pembayaran (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="total_amount" class="form-control" placeholder="250000" step="any" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Status Pesanan <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                      <option value="pending" selected>Pending (Menunggu Bayar)</option>
                      <option value="paid">Paid (Sudah Dibayar)</option>
                      <option value="shipped">Shipped (Dikirim)</option>
                      <option value="cancelled">Cancelled (Batal)</option>
                    </select>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Alamat Pengiriman <span class="text-danger">*</span></label>
                  <textarea name="address" class="form-control" rows="2" placeholder="Alamat lengkap penerima" required></textarea>
                </div>

              <?php elseif ($active_table === 'products'): ?>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Nama Merchandise <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control" placeholder="Misal: Kaos Pertunjukan Jakarta" required>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                      <option value="kaos">Kaos</option>
                      <option value="hoodie">Hoodie</option>
                      <option value="aksesoris">Aksesoris</option>
                      <option value="poster">Poster</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control" placeholder="200000" step="any" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Stok (Pcs) <span class="text-danger">*</span></label>
                    <input type="number" name="stock" class="form-control" value="25" min="0" required>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold"><i class="bi bi-image text-primary me-1"></i> Foto / Gambar Produk <span class="text-danger">*</span></label>
                  <input type="file" name="image_url" class="form-control" accept="image/*" required onchange="previewUpload(this, 'preview-prod-create')">
                  <div class="form-text text-muted">Pilih file foto dari perangkat (JPG, PNG, WEBP), maksimal 2 MB.</div>
                  <div class="mt-2 text-center d-none" id="preview-prod-create-wrap">
                    <img id="preview-prod-create" src="" alt="Preview" class="img-thumbnail rounded shadow-sm" style="max-height: 120px;">
                  </div>
                </div>

              <?php elseif ($active_table === 'tour_dates'): ?>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Nama Acara / Konser <span class="text-danger">*</span></label>
                  <input type="text" name="event_name" class="form-control" placeholder="Pertunjukan Dalam Dinamika..." required>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Kota <span class="text-danger">*</span></label>
                    <input type="text" name="city" class="form-control" placeholder="Jakarta" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Venue / Lokasi <span class="text-danger">*</span></label>
                    <input type="text" name="venue" class="form-control" placeholder="GBK Arena" required>
                  </div>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Tanggal Konser <span class="text-danger">*</span></label>
                    <input type="date" name="event_date" class="form-control" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Waktu / Jam <span class="text-danger">*</span></label>
                    <input type="text" name="event_time" class="form-control" value="19:00 WIB" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Status Tiket <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                      <option value="available" selected>Available</option>
                      <option value="presale">Presale</option>
                      <option value="sold out">Sold Out</option>
                    </select>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Tautan Pembelian Tiket</label>
                  <input type="url" name="ticket_url" class="form-control" placeholder="https://tiket.com/...">
                </div>

              <?php elseif ($active_table === 'albums'): ?>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Judul Album <span class="text-danger">*</span></label>
                  <input type="text" name="title" class="form-control" placeholder="Dalam Dinamika" required>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Tahun Rilis <span class="text-danger">*</span></label>
                    <input type="number" name="release_year" class="form-control" value="<?= date('Y') ?>" required>
                  </div>
                  <div class="col-md-8">
                    <label class="form-label fw-semibold"><i class="bi bi-image text-primary me-1"></i> Foto Cover Album <span class="text-danger">*</span></label>
                    <input type="file" name="cover_image" class="form-control" accept="image/*" required onchange="previewUpload(this, 'preview-album-create')">
                    <div class="form-text text-muted">Ukuran maksimal 2 MB.</div>
                    <div class="mt-2 text-center d-none" id="preview-album-create-wrap">
                      <img id="preview-album-create" src="" alt="Preview" class="img-thumbnail rounded shadow-sm" style="max-height: 120px;">
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Deskripsi Album</label>
                  <textarea name="deskripsi" class="form-control" rows="3" placeholder="Sinopsis album..."></textarea>
                </div>

              <?php elseif ($active_table === 'tracks'): ?>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Judul Trek Lagu <span class="text-danger">*</span></label>
                  <input type="text" name="title" class="form-control" placeholder="Biarkan Bising Ini Jadi Musik" required>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Pilih Album <span class="text-danger">*</span></label>
                    <select name="album_id" class="form-select" required>
                      <?php
                      $albs = mysqli_query($conn, "SELECT id, title FROM albums ORDER BY id ASC");
                      while ($a = mysqli_fetch_assoc($albs)):
                      ?>
                        <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['title']) ?> (ID: <?= $a['id'] ?>)</option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-semibold">Durasi <span class="text-danger">*</span></label>
                    <input type="text" name="duration" class="form-control" value="04:20" placeholder="04:20" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-semibold">Now Playing <span class="text-danger">*</span></label>
                    <select name="is_now_playing" class="form-select">
                      <option value="0" selected>Tidak</option>
                      <option value="1">Ya (Aktif)</option>
                    </select>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold"><i class="bi bi-file-earmark-music text-primary me-1"></i> File Audio Lagu (MP3) <span class="text-danger">*</span></label>
                  <input type="file" name="audio_url" class="form-control" accept="audio/mp3,audio/*" required>
                  <div class="form-text text-muted">Pilih file audio dari perangkat Anda, maksimal 2 MB.</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Lirik Lagu Tersinkronisasi</label>
                  <textarea name="lyrics" class="form-control font-monospace" rows="8" placeholder="[00:02.00] Baris lirik pertama&#10;[00:06.50] Baris lirik kedua"></textarea>
                  <div class="form-text text-muted">Satu timestamp mulai per baris dengan format [menit:detik.desimal]. Contoh: [00:12.50] Lirik dimulai di sini.</div>
                </div>

              <?php elseif ($active_table === 'contact_messages'): ?>
                <div class="row g-2 mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Pengirim <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Nama lengkap" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Email Pengirim <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="nama@example.invalid" required>
                  </div>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-md-8">
                    <label class="form-label fw-semibold">Subjek Pesan <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="form-control" placeholder="Subjek pertanyaan/undangan" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Status Baca</label>
                    <select name="is_read" class="form-select">
                      <option value="0" selected>Belum Dibaca (Baru)</option>
                      <option value="1">Sudah Dibaca</option>
                    </select>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Isi Pesan <span class="text-danger">*</span></label>
                  <textarea name="message" class="form-control" rows="3" placeholder="Tuliskan isi pesan di sini..." required></textarea>
                </div>

              <?php elseif ($active_table === 'gallery'): ?>
                <div class="row g-2 mb-3">
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                      <option value="Konser">Konser</option>
                      <option value="Panggung">Panggung</option>
                      <option value="Dokumentasi">Dokumentasi</option>
                      <option value="Behind The Scene">Behind The Scene</option>
                    </select>
                  </div>
                  <div class="col-md-8">
                    <label class="form-label fw-semibold">Judul Foto <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Judul momen foto" required>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold"><i class="bi bi-image text-primary me-1"></i> File Foto Galeri <span class="text-danger">*</span></label>
                  <input type="file" name="image_url" class="form-control" accept="image/*" required onchange="previewUpload(this, 'preview-gal-create')">
                  <div class="form-text text-muted">Pilih foto dari perangkat (JPG, PNG, WEBP), maksimal 2 MB.</div>
                  <div class="mt-2 text-center d-none" id="preview-gal-create-wrap">
                    <img id="preview-gal-create" src="" alt="Preview" class="img-thumbnail rounded shadow-sm" style="max-height: 120px;">
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Caption / Keterangan</label>
                  <textarea name="caption" class="form-control" rows="2" placeholder="Keterangan singkat momen foto..."></textarea>
                </div>

              <?php elseif ($active_table === 'articles'): ?>
                <div class="row g-2 mb-3">
                  <div class="col-md-8">
                    <label class="form-label fw-semibold">Judul Artikel / Berita <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Judul siaran pers atau berita" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                      <option value="News">News</option>
                      <option value="Tour">Tour</option>
                      <option value="Music">Music</option>
                      <option value="Behind The Scene">Behind The Scene</option>
                      <option value="Merchandise">Merchandise</option>
                    </select>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold"><i class="bi bi-image text-primary me-1"></i> Foto Thumbnail Artikel <span class="text-danger">*</span></label>
                  <input type="file" name="thumbnail_url" class="form-control" accept="image/*" required onchange="previewUpload(this, 'preview-art-create')">
                  <div class="form-text text-muted">Pilih foto sampul berita (JPG, PNG, WEBP), maksimal 2 MB.</div>
                  <div class="mt-2 text-center d-none" id="preview-art-create-wrap">
                    <img id="preview-art-create" src="" alt="Preview" class="img-thumbnail rounded shadow-sm" style="max-height: 120px;">
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Isi Konten Artikel <span class="text-danger">*</span></label>
                  <textarea name="content" class="form-control" rows="4" placeholder="Tuliskan berita lengkap..." required></textarea>
                </div>

              <?php elseif ($active_table === 'subscribers'): ?>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Alamat Email Fans <span class="text-danger">*</span></label>
                  <input type="email" name="email" class="form-control" placeholder="subscriber@example.invalid" required>
                </div>

              <?php elseif ($active_table === 'admins'): ?>
                <div class="row g-2 mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" placeholder="Username baru" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Email Administrator <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="admin@perunggu.id" required>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Kata Sandi (Password) <span class="text-danger">*</span></label>
                  <input type="password" name="password" class="form-control" placeholder="Minimal 12 karakter" minlength="12" required>
                </div>

              <?php elseif ($active_table === 'order_items'): ?>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Pilih Pesanan Induk (Order) <span class="text-danger">*</span></label>
                  <select name="order_id" class="form-select" required>
                    <?php
                    $ordList = mysqli_query($conn, "SELECT id, order_code, customer_name FROM orders ORDER BY id DESC LIMIT 20");
                    while ($o = mysqli_fetch_assoc($ordList)):
                    ?>
                      <option value="<?= $o['id'] ?>">#<?= $o['id'] ?> - <?= htmlspecialchars($o['order_code']) ?> (<?= htmlspecialchars($o['customer_name']) ?>)</option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-md-8">
                    <label class="form-label fw-semibold">Pilih Merchandise Produk <span class="text-danger">*</span></label>
                    <select name="product_id" class="form-select" required>
                      <?php
                      $prdList = mysqli_query($conn, "SELECT id, name, price FROM products ORDER BY name ASC");
                      while ($p = mysqli_fetch_assoc($prdList)):
                      ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> - Rp <?= number_format($p['price'], 0, ',', '.') ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Jumlah (Qty) <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                  </div>
                </div>
              <?php endif; ?>

              <p class="small text-secondary mb-0">*Kolom wajib diisi dengan benar.</p>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-floppy me-1"></i> Simpan Data
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

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

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
    <script src="./js/adminlte.js"></script>
    <script src="./js/delete-confirmation.js"></script>
    <script>
      function previewUpload(input, targetImgId) {
        if (input.files && input.files[0]) {
          const reader = new FileReader();
          reader.onload = function(e) {
            const img = document.getElementById(targetImgId);
            const wrap = document.getElementById(targetImgId + '-wrap');
            if (img) img.src = e.target.result;
            if (wrap) wrap.classList.remove('d-none');
          };
          reader.readAsDataURL(input.files[0]);
        }
      }
    </script>
  </body>
</html>
<?php
mysqli_close($conn);
?>

