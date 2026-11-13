<?php
// Koneksi ke basis data perunggu_db
require_once __DIR__ . '/../connect.php';

// Helper query hitung jumlah data
if (!function_exists('get_count')) {
    function get_count($conn, $table, $where = "1=1") {
        $res = mysqli_query($conn, "SELECT COUNT(*) FROM `$table` WHERE $where");
        if ($res && $row = mysqli_fetch_row($res)) {
            return (int)$row[0];
        }
        return 0;
    }
}

// 1. Statistik Utama dari Basis Data
$count_albums   = get_count($conn, 'albums');
$count_tracks   = get_count($conn, 'tracks');
$count_products = get_count($conn, 'products');
$count_orders   = get_count($conn, 'orders');
$count_revenue_orders = get_count($conn, 'orders', "payment_status = 'paid'");
$count_pending_orders = get_count($conn, 'orders', "payment_status = 'pending' AND shipping_status <> 'cancelled'");
$count_tour     = get_count($conn, 'tour_dates', 'event_date >= CURDATE()');
$count_articles = get_count($conn, 'articles');
$count_gallery  = get_count($conn, 'gallery');
$count_subs     = get_count($conn, 'subscribers');
$count_unread   = get_count($conn, 'contact_messages', "is_read = 0");

// Total pendapatan dari pesanan yang pembayarannya sudah dikonfirmasi.
$rev_res = mysqli_query($conn, "SELECT COALESCE(SUM(total_amount), 0) FROM `orders` WHERE payment_status = 'paid'");
$total_revenue = ($rev_res && $row = mysqli_fetch_row($rev_res)) ? (float)$row[0] : 0;

// Total Stok Merchandise Tersedia
$stock_res = mysqli_query($conn, "SELECT COALESCE(SUM(stock), 0) FROM `products`");
$total_stock = ($stock_res && $row = mysqli_fetch_row($stock_res)) ? (int)$row[0] : 0;

// Data Lagu Now Playing
$now_playing_res = mysqli_query($conn, "SELECT t.title, a.title AS album_title FROM tracks t JOIN albums a ON t.album_id = a.id WHERE t.is_now_playing = 1 LIMIT 1");
$now_playing = ($now_playing_res && $row = mysqli_fetch_assoc($now_playing_res)) ? $row : ['title' => 'Belum dipilih', 'album_title' => '-'];

// 5 Pesanan Terbaru
$recent_orders = mysqli_query($conn, "SELECT * FROM `orders` ORDER BY id DESC LIMIT 5");

// Pendapatan bulanan 12 bulan terakhir berdasarkan tanggal pembayaran.
$revenue_period_start = (new DateTimeImmutable('first day of this month'))->setTime(0, 0)->modify('-11 months');
$revenue_period_end = (new DateTimeImmutable('tomorrow'))->setTime(0, 0);
$revenue_start_sql = mysqli_real_escape_string($conn, $revenue_period_start->format('Y-m-d'));
$revenue_end_sql = mysqli_real_escape_string($conn, $revenue_period_end->format('Y-m-d'));
$monthly_revenue_result = mysqli_query(
    $conn,
    "SELECT YEAR(paid_at) AS revenue_year, MONTH(paid_at) AS revenue_month,
            SUM(total_amount) AS revenue, COUNT(*) AS order_count
     FROM `orders`
     WHERE payment_status = 'paid'
       AND paid_at IS NOT NULL
       AND paid_at >= '$revenue_start_sql'
       AND paid_at < '$revenue_end_sql'
     GROUP BY YEAR(paid_at), MONTH(paid_at)
     ORDER BY revenue_year, revenue_month"
);
$monthly_revenue_error = $monthly_revenue_result ? '' : mysqli_error($conn);
if (!$monthly_revenue_result) {
    error_log('Query rincian pendapatan gagal: ' . $monthly_revenue_error);
}

$monthly_revenue_by_key = [];
if ($monthly_revenue_result) {
    while ($revenue_row = mysqli_fetch_assoc($monthly_revenue_result)) {
        $month_key = sprintf('%04d-%02d', $revenue_row['revenue_year'], $revenue_row['revenue_month']);
        $monthly_revenue_by_key[$month_key] = [
            'revenue' => (float)$revenue_row['revenue'],
            'order_count' => (int)$revenue_row['order_count']
        ];
    }
}

$month_names = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$monthly_revenue_details = [];
$monthly_revenue_total = 0;
$monthly_paid_orders_total = 0;
for ($month_index = 0; $month_index < 12; $month_index++) {
    $month_date = $revenue_period_start->modify("+$month_index months");
    $month_key = $month_date->format('Y-m');
    $month_data = $monthly_revenue_by_key[$month_key] ?? ['revenue' => 0, 'order_count' => 0];
    $monthly_revenue_details[] = [
        'label' => $month_names[(int)$month_date->format('n') - 1] . ' ' . $month_date->format('Y'),
        'revenue' => $month_data['revenue'],
        'order_count' => $month_data['order_count']
    ];
    $monthly_revenue_total += $month_data['revenue'];
    $monthly_paid_orders_total += $month_data['order_count'];
}

// 4 Jadwal Konser Terdekat
$upcoming_tours = mysqli_query($conn, "SELECT * FROM `tour_dates` WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 4");

// 5 Pesan Kontak Terbaru
$recent_messages = mysqli_query($conn, "SELECT * FROM `contact_messages` ORDER BY id DESC LIMIT 5");

// 4 Produk dengan Stok Paling Menipis
$low_stock_threshold = 5;
$low_stock_products = mysqli_query($conn, "SELECT * FROM `products` WHERE stock <= $low_stock_threshold ORDER BY stock ASC, id DESC LIMIT 4");
if (!$low_stock_products) {
    error_log('Query stok rendah gagal: ' . mysqli_error($conn));
}

// 4 Lagu Terbaru
$recent_tracks = mysqli_query($conn, "SELECT t.*, a.title AS album_title FROM `tracks` t JOIN `albums` a ON t.album_id = a.id ORDER BY t.id DESC LIMIT 4");

// 5 Merchandise Terlaris dari pesanan yang sudah dibayar.
$top_selling_products = mysqli_query(
    $conn,
    "SELECT p.id, p.name, p.stock, SUM(oi.quantity) AS units_sold
     FROM order_items oi
     JOIN orders o ON o.id = oi.order_id
     JOIN products p ON p.id = oi.product_id
     WHERE o.payment_status = 'paid'
       AND o.shipping_status <> 'cancelled'
     GROUP BY p.id, p.name, p.stock
     ORDER BY units_sold DESC, p.id DESC
     LIMIT 5"
);
if (!$top_selling_products) {
    error_log('Query merchandise terlaris gagal: ' . mysqli_error($conn));
}

// (Kode tabel viewer sudah dipindah ke masing-masing daftar_*.php + table_template.php)
?>
<!doctype html>
<html lang="id">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Dashboard Admin Perunggu</title>

    <!--begin::Theme Init-->
    <script>
      (() => {
        'use strict';
        const root = document.documentElement;
        if (root.getAttribute('data-lte-color-mode') === 'off') return;
        const STORAGE_KEY = 'lte-theme';
        let stored = null;
        try { stored = localStorage.getItem(STORAGE_KEY); } catch {}
        const authored = root.getAttribute('data-bs-theme');
        let resolved = 'light';
        if (stored === 'dark' || stored === 'light') resolved = stored;
        else if (authored === 'dark' || authored === 'light') resolved = authored;
        else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) resolved = 'dark';
        root.setAttribute('data-bs-theme', resolved);
        root.style.colorScheme = resolved;
      })();
    </script>
    <!--end::Theme Init-->

    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="title" content="PERUNGGU â€” Dashboard Admin" />
    <meta name="description" content="Dashboard Pengelolaan Basis Data Website Resmi Band PERUNGGU (perunggu_db)" />

    <link rel="preload" href="./css/adminlte.css" as="style" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="all" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="./css/adminlte.css" />

    <style>
      .perunggu-brand {
        letter-spacing: 2px;
        font-weight: 800;
        color: #d4a373 !important;
      }
      html {
        scroll-behavior: smooth;
      }
      .nav-treeview .nav-link {
        padding-left: 2.2rem !important;
      }
    </style>
  </head>
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
      
      <!-- HEADER NAVBAR -->
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
              <a href="../bacadata.php" class="nav-link">
                <i class="bi bi-table me-1"></i> Viewer 11 Tabel
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="../index.php" class="nav-link">
                <i class="bi bi-database me-1"></i> DB Manager
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
                <a href="index.php?table=contact_messages#table-viewer" class="dropdown-item dropdown-footer text-primary">Buka Pesan Masuk di Dashboard</a>
              </div>
            </li>

            <!-- Theme Toggle -->
            <li class="nav-item dropdown">
              <a class="nav-link" href="#" id="bd-theme" data-bs-toggle="dropdown">
                <i class="bi bi-sun-fill" data-lte-theme-icon="light"></i>
                <i class="bi bi-moon-fill d-none" data-lte-theme-icon="dark"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme">
                <li><button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light"><i class="bi bi-sun-fill me-2"></i> Light</button></li>
                <li><button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark"><i class="bi bi-moon-fill me-2"></i> Dark</button></li>
              </ul>
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
                  <a href="../bacadata.php" class="btn btn-sm btn-outline-danger">Cek DB</a>
                  <a href="../logout.php" class="btn btn-sm btn-outline-secondary">Keluar</a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>

      <!-- SIDEBAR DENGAN ACCORDION DROPDOWN (TREEVIEW) -->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand d-flex align-items-center justify-content-start px-3 py-2 border-bottom border-secondary border-opacity-25">
          <a href="./index.php" class="brand-link d-flex align-items-center gap-2 text-decoration-none">
            <img src="./assets/img/perunggu-logo.jpeg" alt="Logo" class="brand-image rounded shadow" style="width: 34px; height: 34px; object-fit: cover;" />
            <span class="brand-text perunggu-brand fs-5">PERUNGGU</span>
          </a>
        </div>

        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!-- Sidebar Menu dengan Treeview Accordion -->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
              
              <!-- MENU DASHBOARD -->
              <li class="nav-item">
                <a href="index.php" class="nav-link active">
                  <i class="nav-icon bi bi-speedometer2"></i>
                  <p>Dashboard</p>
                </a>
              </li>

              <!-- 1. KARYA MUSIK & AUDIO -->
              <li class="nav-header">KARYA MUSIK & AUDIO</li>
              <li class="nav-item">
                <a href="daftar_album.php" class="nav-link">
                  <i class="nav-icon bi bi-disc"></i>
                  <p>Albums (<?= $count_albums ?>)</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="daftar_track.php" class="nav-link">
                  <i class="nav-icon bi bi-music-note-beamed"></i>
                  <p>Tracks & Audio (<?= $count_tracks ?>)</p>
                </a>
              </li>

              <!-- 2. TUR & PENAMPILAN -->
              <li class="nav-header">TUR & PENAMPILAN</li>
              <li class="nav-item">
                <a href="daftar_tur.php" class="nav-link">
                  <i class="nav-icon bi bi-calendar-event"></i>
                  <p>Jadwal Tur (<?= $count_tour ?>)</p>
                </a>
              </li>

              <!-- 3. MERCHANDISE & TRANSAKSI -->
              <li class="nav-header">MERCHANDISE & TRANSAKSI</li>
              <li class="nav-item">
                <a href="daftar_produk.php" class="nav-link">
                  <i class="nav-icon bi bi-bag-check"></i>
                  <p>Produk Toko (<?= $count_products ?>)</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="daftar_pesanan.php" class="nav-link">
                  <i class="nav-icon bi bi-receipt"></i>
                  <p>Pesanan Masuk (<?= $count_orders ?>)
                    <?php if ($count_pending_orders > 0): ?>
                      <span class="badge text-bg-warning ms-1"><?= $count_pending_orders ?></span>
                    <?php endif; ?>
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="index.php#section-revenue" class="nav-link">
                  <i class="nav-icon bi bi-graph-up-arrow"></i>
                  <p>Rincian Pendapatan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="daftar_order_items.php" class="nav-link">
                  <i class="nav-icon bi bi-list-check"></i>
                  <p>Rincian Item Pesanan</p>
                </a>
              </li>

              <!-- 4. KONTEN & MEDIA -->
              <li class="nav-header">KONTEN & MEDIA</li>
              <li class="nav-item">
                <a href="daftar_artikel.php" class="nav-link">
                  <i class="nav-icon bi bi-newspaper"></i>
                  <p>Berita & Artikel (<?= $count_articles ?>)</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="daftar_galeri.php" class="nav-link">
                  <i class="nav-icon bi bi-images"></i>
                  <p>Galeri Dokumentasi (<?= $count_gallery ?>)</p>
                </a>
              </li>

              <!-- 5. INTERAKSI FANS -->
              <li class="nav-header">INTERAKSI FANS</li>
              <li class="nav-item">
                <a href="daftar_pesan.php" class="nav-link">
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
                <a href="daftar_subscriber.php" class="nav-link">
                  <i class="nav-icon bi bi-envelope-check"></i>
                  <p>Subscribers (<?= $count_subs ?>)</p>
                </a>
              </li>

              <!-- 6. PENGATURAN SISTEM -->
              <li class="nav-header">PENGATURAN SISTEM</li>
              <li class="nav-item">
                <a href="daftar_admin.php" class="nav-link">
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

      <!-- MAIN APP CONTENT -->
      <main class="app-main" id="section-summary">
        <div class="app-content-header py-3 border-bottom mb-3">
          <div class="container-fluid">
            <div class="row align-items-center">
              <div class="col-sm-6">
                <h2 class="mb-0 fs-3 fw-bold">Dashboard Band PERUNGGU</h2>
                <span class="text-secondary small">Status Database: <code class="text-success">perunggu_db</code> &bull; Terkoneksi</span>
              </div>
              <div class="col-sm-6">
                <div class="float-sm-end d-flex gap-2 align-items-center mt-2 mt-sm-0">
                  <span class="badge text-bg-dark p-2">
                    <i class="bi bi-play-circle-fill text-warning me-1"></i> Now Playing: <strong><?= htmlspecialchars($now_playing['title']) ?></strong>
                  </span>
                  <a href="../../web_perunggu/index.html" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i> Pratinjau Web
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <!-- STATISTIC BOXES ROW -->
            <div class="row g-3 mb-4">
              <!-- Box 1: Omset Penjualan Merchandise -->
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="small-box text-bg-success shadow-sm rounded-3">
                  <div class="inner p-3">
                    <h3 class="fw-bold mb-1">Rp <?= number_format($total_revenue, 0, ',', '.') ?></h3>
                    <p class="mb-0">Omzet Merchandise (<?= $count_revenue_orders ?> Pesanan Lunas)</p>
                  </div>
                  <div class="small-box-icon">
                    <i class="bi bi-currency-dollar fs-1 opacity-25"></i>
                  </div>
                  <a href="daftar_pesanan.php" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover d-block p-2 text-center bg-black bg-opacity-10">
                    Lihat Transaksi Pesanan <i class="bi bi-arrow-right-circle ms-1"></i>
                  </a>
                </div>
              </div>

              <!-- Box 2: Total Musik & Album -->
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="small-box text-bg-primary shadow-sm rounded-3">
                  <div class="inner p-3">
                    <h3 class="fw-bold mb-1"><?= $count_tracks ?> <span class="fs-6 fw-normal">Lagu (<?= $count_albums ?> Album)</span></h3>
                    <p class="mb-0">Katalog Musik & Audio Player</p>
                  </div>
                  <div class="small-box-icon">
                    <i class="bi bi-disc fs-1 opacity-25"></i>
                  </div>
                  <a href="daftar_track.php" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover d-block p-2 text-center bg-black bg-opacity-10">
                    Kelola Katalog Musik <i class="bi bi-arrow-right-circle ms-1"></i>
                  </a>
                </div>
              </div>

              <!-- Box 3: Stok Merchandise -->
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="small-box text-bg-warning shadow-sm rounded-3 text-dark">
                  <div class="inner p-3">
                    <h3 class="fw-bold mb-1"><?= $total_stock ?> <span class="fs-6 fw-normal">Pcs (<?= $count_products ?> Item)</span></h3>
                    <p class="mb-0">Stok Merchandise Aktif</p>
                  </div>
                  <div class="small-box-icon">
                    <i class="bi bi-box-seam fs-1 opacity-25"></i>
                  </div>
                  <a href="daftar_produk.php" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover d-block p-2 text-center bg-black bg-opacity-10">
                    Kelola Stok Produk <i class="bi bi-arrow-right-circle ms-1"></i>
                  </a>
                </div>
              </div>

              <!-- Box 4: Jadwal Tur & Pesan Masuk -->
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="small-box text-bg-danger shadow-sm rounded-3">
                  <div class="inner p-3">
                    <h3 class="fw-bold mb-1"><?= $count_tour ?> <span class="fs-6 fw-normal">Kota (<?= $count_unread ?> Pesan)</span></h3>
                    <p class="mb-0">Jadwal Tur & Pesan Fans</p>
                  </div>
                  <div class="small-box-icon">
                    <i class="bi bi-geo-alt fs-1 opacity-25"></i>
                  </div>
                  <a href="daftar_tur.php" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover d-block p-2 text-center bg-black bg-opacity-10">
                    Lihat Jadwal Tur <i class="bi bi-arrow-right-circle ms-1"></i>
                  </a>
                </div>
              </div>
            </div>

            <!-- MAIN DATA TABLES GRID -->
            <div class="row g-3">
              
              <!-- LEFT COLUMN -->
              <div class="col-lg-8">
                
                <!-- Card: Pesanan Merchandise Terbaru -->
                <div class="card mb-4 shadow-sm" id="section-orders">
                  <div class="card-header bg-body border-bottom d-flex align-items-center justify-content-between py-3">
                    <h5 class="card-title mb-0 fw-bold">
                      <i class="bi bi-cart3 text-primary me-2"></i> Transaksi Pesanan Merchandise Terbaru (Tabel <code>orders</code>)
                    </h5>
                    <div class="d-flex gap-2">
                      <a href="daftar_order_items.php" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-list-check me-1"></i> Rincian Item (order_items)
                      </a>
                      <a href="daftar_pesanan.php" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-table me-1"></i> Lihat Semua Pesanan
                      </a>
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                          <tr>
                            <th>Kode Order</th>
                            <th>Pelanggan</th>
                            <th>WhatsApp</th>
                            <th>Metode Bayar</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Pengiriman</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if ($recent_orders && mysqli_num_rows($recent_orders) > 0): ?>
                            <?php while ($ord = mysqli_fetch_assoc($recent_orders)): ?>
                              <tr>
                                <td><span class="badge text-bg-light border fw-bold"><?= htmlspecialchars($ord['order_code']) ?></span></td>
                                <td>
                                  <strong><?= htmlspecialchars($ord['customer_name']) ?></strong><br>
                                  <small class="text-secondary"><?= htmlspecialchars(substr($ord['address'], 0, 30)) ?>...</small>
                                </td>
                                <td>
                                  <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $ord['whatsapp']) ?>" target="_blank" class="text-decoration-none text-success">
                                    <i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($ord['whatsapp']) ?>
                                  </a>
                                </td>
                                <td><?= htmlspecialchars($ord['payment_method']) ?></td>
                                <td class="fw-bold">Rp <?= number_format($ord['total_amount'], 0, ',', '.') ?></td>
                                <?php
                                  $payment_labels = [
                                      'pending' => 'Belum Dibayar',
                                      'paid' => 'Lunas',
                                      'refunded' => 'Refund'
                                  ];
                                  $payment_badge = $ord['payment_status'] === 'paid'
                                      ? 'text-bg-success'
                                      : ($ord['payment_status'] === 'refunded' ? 'text-bg-secondary' : 'text-bg-warning');
                                  $shipping_labels = [
                                      'pending' => 'Menunggu',
                                      'shipped' => 'Dikirim',
                                      'delivered' => 'Diterima',
                                      'cancelled' => 'Dibatalkan'
                                  ];
                                  $shipping_badge = $ord['shipping_status'] === 'delivered'
                                      ? 'text-bg-success'
                                      : ($ord['shipping_status'] === 'shipped'
                                          ? 'text-bg-info'
                                          : ($ord['shipping_status'] === 'cancelled' ? 'text-bg-danger' : 'text-bg-warning'));
                                ?>
                                <td><span class="badge <?= $payment_badge ?>"><?= htmlspecialchars($payment_labels[$ord['payment_status']] ?? $ord['payment_status']) ?></span></td>
                                <td><span class="badge <?= $shipping_badge ?>"><?= htmlspecialchars($shipping_labels[$ord['shipping_status']] ?? $ord['shipping_status']) ?></span></td>
                              </tr>
                            <?php endwhile; ?>
                          <?php else: ?>
                            <tr>
                              <td colspan="7" class="text-center py-3 text-muted">Belum ada data pesanan di basis data.</td>
                            </tr>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Card: Rincian Pendapatan Bulanan -->
                <div class="card mb-4 shadow-sm" id="section-revenue">
                  <div class="card-header bg-body border-bottom d-flex align-items-center justify-content-between py-3">
                    <div>
                      <h5 class="card-title mb-1 fw-bold">
                        <i class="bi bi-graph-up-arrow text-success me-2"></i> Rincian Pendapatan per Bulan
                      </h5>
                      <small class="text-secondary">12 bulan terakhir, hanya pembayaran yang dikonfirmasi lunas.</small>
                    </div>
                    <span class="badge text-bg-success fs-6">Rp <?= number_format($monthly_revenue_total, 0, ',', '.') ?></span>
                  </div>
                  <?php if ($monthly_revenue_result): ?>
                    <div class="card-body">
                      <div style="height: 320px;">
                        <canvas id="monthly-revenue-chart" aria-label="Grafik pendapatan per bulan" role="img"></canvas>
                      </div>
                    </div>
                    <div class="card-body border-top pt-3">
                      <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                          <thead class="table-light">
                            <tr>
                              <th>Bulan</th>
                              <th class="text-end">Pesanan Terbayar</th>
                              <th class="text-end">Pendapatan</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($monthly_revenue_details as $month_detail): ?>
                              <tr>
                                <td><?= htmlspecialchars($month_detail['label']) ?></td>
                                <td class="text-end"><?= number_format($month_detail['order_count'], 0, ',', '.') ?></td>
                                <td class="text-end fw-semibold">Rp <?= number_format($month_detail['revenue'], 0, ',', '.') ?></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                          <tfoot class="table-light fw-bold">
                            <tr>
                              <td>Total 12 Bulan</td>
                              <td class="text-end"><?= number_format($monthly_paid_orders_total, 0, ',', '.') ?></td>
                              <td class="text-end">Rp <?= number_format($monthly_revenue_total, 0, ',', '.') ?></td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                    </div>
                  <?php else: ?>
                    <div class="card-body">
                      <div class="alert alert-danger mb-0">Data rincian pendapatan gagal dimuat. Periksa koneksi database atau log server.</div>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Card: Jadwal Tur & Pertunjukan Musik -->
                <div class="card mb-4 shadow-sm" id="section-tours">
                  <div class="card-header bg-body border-bottom d-flex align-items-center justify-content-between py-3">
                    <h5 class="card-title mb-0 fw-bold">
                      <i class="bi bi-calendar-check text-danger me-2"></i> Agenda Tur & Konser (Tabel <code>tour_dates</code>)
                    </h5>
                    <a href="daftar_tur.php" class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-table me-1"></i> Semua Jadwal
                    </a>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                          <tr>
                            <th>Nama Acara / Festival</th>
                            <th>Kota & Venue</th>
                            <th>Tanggal & Waktu</th>
                            <th>Status Tiket</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if ($upcoming_tours && mysqli_num_rows($upcoming_tours) > 0): ?>
                            <?php while ($tour = mysqli_fetch_assoc($upcoming_tours)): ?>
                              <tr>
                                <td class="fw-bold"><?= htmlspecialchars($tour['event_name']) ?></td>
                                <td>
                                  <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= htmlspecialchars($tour['city']) ?><br>
                                  <small class="text-secondary"><?= htmlspecialchars($tour['venue']) ?></small>
                                </td>
                                <td>
                                  <i class="bi bi-clock me-1"></i> <?= date('d M Y', strtotime($tour['event_date'])) ?><br>
                                  <small class="text-secondary"><?= htmlspecialchars($tour['event_time']) ?></small>
                                </td>
                                <td>
                                  <?php if ($tour['status'] === 'available'): ?>
                                    <span class="badge text-bg-success">Available</span>
                                  <?php elseif ($tour['status'] === 'presale'): ?>
                                    <span class="badge text-bg-primary">Presale</span>
                                  <?php else: ?>
                                    <span class="badge text-bg-secondary">Sold Out</span>
                                  <?php endif; ?>
                                </td>
                              </tr>
                            <?php endwhile; ?>
                          <?php else: ?>
                            <tr>
                              <td colspan="4" class="text-center py-3 text-muted">Belum ada agenda tur.</td>
                            </tr>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

              </div>

              <!-- RIGHT COLUMN -->
              <div class="col-lg-4">
                
                <!-- Card: Pesan Masuk Pengunjung -->
                <div class="card mb-4 shadow-sm" id="section-messages">
                  <div class="card-header bg-body border-bottom d-flex align-items-center justify-content-between py-3">
                    <h5 class="card-title mb-0 fw-bold">
                      <i class="bi bi-chat-dots text-warning me-2"></i> Pesan Kontak (Tabel <code>contact_messages</code>)
                    </h5>
                    <a href="daftar_pesan.php" class="btn btn-sm btn-outline-warning">
                      <i class="bi bi-table me-1"></i> Buka Semua
                    </a>
                  </div>
                  <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                      <?php if ($recent_messages && mysqli_num_rows($recent_messages) > 0): ?>
                        <?php while ($msg = mysqli_fetch_assoc($recent_messages)): ?>
                          <li class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                              <span class="fw-bold text-dark"><?= htmlspecialchars($msg['name']) ?></span>
                              <?php if ($msg['is_read'] == 0): ?>
                                <span class="badge text-bg-danger">Baru</span>
                              <?php else: ?>
                                <span class="badge text-bg-secondary">Dibaca</span>
                              <?php endif; ?>
                            </div>
                            <div class="small fw-semibold text-primary mb-1"><?= htmlspecialchars($msg['subject']) ?></div>
                            <p class="small text-secondary mb-0"><?= htmlspecialchars(substr($msg['message'], 0, 75)) ?>...</p>
                          </li>
                        <?php endwhile; ?>
                      <?php else: ?>
                        <li class="list-group-item text-center py-3 text-muted">Belum ada pesan masuk.</li>
                      <?php endif; ?>
                    </ul>
                  </div>
                </div>

                <!-- Card: Peringatan Stok Merchandise -->
                <div class="card mb-4 shadow-sm">
                  <div class="card-header bg-body border-bottom d-flex align-items-center justify-content-between py-3">
                    <h5 class="card-title mb-0 fw-bold">
                      <i class="bi bi-exclamation-triangle text-danger me-2"></i> Stok Rendah (maks. <?= $low_stock_threshold ?> pcs)
                    </h5>
                    <a href="daftar_produk.php" class="btn btn-sm btn-outline-secondary">
                      <i class="bi bi-table me-1"></i> Katalog
                    </a>
                  </div>
                  <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                      <?php if (!$low_stock_products): ?>
                        <li class="list-group-item text-center py-3 text-danger">Data stok rendah gagal dimuat.</li>
                      <?php elseif (mysqli_num_rows($low_stock_products) > 0): ?>
                        <?php while ($p = mysqli_fetch_assoc($low_stock_products)): ?>
                          <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                              <strong class="d-block text-truncate" style="max-width: 200px;"><?= htmlspecialchars($p['name']) ?></strong>
                              <small class="text-muted">Kategori: <?= htmlspecialchars($p['category']) ?> &bull; Rp <?= number_format($p['price'], 0, ',', '.') ?></small>
                            </div>
                            <span class="badge <?= $p['stock'] <= $low_stock_threshold ? 'text-bg-danger' : 'text-bg-warning' ?> fs-7">
                              <?= $p['stock'] ?> pcs
                            </span>
                          </li>
                        <?php endwhile; ?>
                      <?php else: ?>
                        <li class="list-group-item text-center py-3 text-muted">Tidak ada produk dengan stok rendah.</li>
                      <?php endif; ?>
                    </ul>
                  </div>
                </div>

                <!-- Card: Merchandise Terlaris -->
                <div class="card mb-4 shadow-sm">
                  <div class="card-header bg-body border-bottom d-flex align-items-center justify-content-between py-3">
                    <h5 class="card-title mb-0 fw-bold">
                      <i class="bi bi-trophy text-warning me-2"></i> Merchandise Terlaris
                    </h5>
                    <a href="daftar_produk.php" class="btn btn-sm btn-outline-secondary">
                      <i class="bi bi-bag me-1"></i> Katalog
                    </a>
                  </div>
                  <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                      <?php if (!$top_selling_products): ?>
                        <li class="list-group-item text-center py-3 text-danger">Data merchandise terlaris gagal dimuat.</li>
                      <?php elseif (mysqli_num_rows($top_selling_products) > 0): ?>
                        <?php while ($product = mysqli_fetch_assoc($top_selling_products)): ?>
                          <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                              <strong class="d-block"><?= htmlspecialchars($product['name']) ?></strong>
                              <small class="text-muted">Stok tersedia: <?= (int)$product['stock'] ?> pcs</small>
                            </div>
                            <span class="badge text-bg-primary"><?= (int)$product['units_sold'] ?> terjual</span>
                          </li>
                        <?php endwhile; ?>
                      <?php else: ?>
                        <li class="list-group-item text-center py-3 text-muted">Belum ada penjualan terbayar.</li>
                      <?php endif; ?>
                    </ul>
                  </div>
                </div>

                <!-- Card: Daftar Lagu & Player -->
                <div class="card mb-4 shadow-sm">
                  <div class="card-header bg-body border-bottom d-flex align-items-center justify-content-between py-3">
                    <h5 class="card-title mb-0 fw-bold">
                      <i class="bi bi-music-player text-info me-2"></i> Track Lagu (Tabel <code>tracks</code>)
                    </h5>
                    <a href="daftar_track.php" class="btn btn-sm btn-outline-info">
                      <i class="bi bi-table me-1"></i> Buka Lagu
                    </a>
                  </div>
                  <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                      <?php if ($recent_tracks && mysqli_num_rows($recent_tracks) > 0): ?>
                        <?php while ($t = mysqli_fetch_assoc($recent_tracks)): ?>
                          <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                              <strong><?= htmlspecialchars($t['title']) ?></strong>
                              <?php if ($t['is_now_playing']): ?>
                                <span class="badge text-bg-success ms-1">Active</span>
                              <?php endif; ?>
                              <small class="d-block text-muted">Album: <?= htmlspecialchars($t['album_title']) ?></small>
                            </div>
                            <span class="badge text-bg-light border text-secondary"><?= htmlspecialchars($t['duration']) ?></span>
                          </li>
                        <?php endwhile; ?>
                      <?php endif; ?>
                    </ul>
                  </div>
                </div>

              </div>

            </div>
            <!-- /MAIN DATA TABLES GRID -->

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

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
    <script src="./js/adminlte.js"></script>
    <script
      src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"
      integrity="sha256-SERKgtTty1vsDxll+qzd4Y2cF9swY9BCq62i9wXJ9Uo="
      crossorigin="anonymous"
    ></script>
    <?php if ($monthly_revenue_result): ?>
      <script>
        const revenueCurrencyFormatter = new Intl.NumberFormat('id-ID', {
          style: 'currency',
          currency: 'IDR',
          maximumFractionDigits: 0
        });

        new Chart(document.getElementById('monthly-revenue-chart'), {
          type: 'bar',
          data: {
            labels: <?= json_encode(array_column($monthly_revenue_details, 'label'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            datasets: [{
              label: 'Pendapatan',
              data: <?= json_encode(array_column($monthly_revenue_details, 'revenue')) ?>,
              backgroundColor: 'rgba(25, 135, 84, 0.72)',
              borderColor: 'rgb(25, 135, 84)',
              borderWidth: 1,
              borderRadius: 5
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: context => revenueCurrencyFormatter.format(Number(context.raw || 0))
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: value => revenueCurrencyFormatter.format(Number(value))
                }
              }
            }
          }
        });
      </script>
    <?php endif; ?>
  </body>
</html>
<?php
mysqli_close($conn);
?>
