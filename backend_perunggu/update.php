<?php
// ========================================================
// FORM UPDATE (EDIT DATA) - TAMPILAN ADMINLTE 4 RESMI
// Sesuai Standar Template AdminLTE & Formulir Modern
// ========================================================
require_once __DIR__ . '/connect.php';

$valid_tables = [
    'albums' => [
        'title' => 'Katalog Album Musik',
        'single_name' => 'Album Musik',
        'icon' => 'bi-disc',
        'file' => 'dist/daftar_album.php'
    ],
    'tracks' => [
        'title' => 'Trek Musik & Chord',
        'single_name' => 'Trek Lagu & Audio',
        'icon' => 'bi-music-note-beamed',
        'file' => 'dist/daftar_track.php'
    ],
    'tour_dates' => [
        'title' => 'Jadwal Konser & Tur',
        'single_name' => 'Jadwal Tur',
        'icon' => 'bi-calendar-event',
        'file' => 'dist/daftar_tur.php'
    ],
    'products' => [
        'title' => 'Katalog Merchandise Toko',
        'single_name' => 'Produk Merchandise',
        'icon' => 'bi-bag-check',
        'file' => 'dist/daftar_produk.php'
    ],
    'orders' => [
        'title' => 'Pesanan Merchandise',
        'single_name' => 'Transaksi Pesanan',
        'icon' => 'bi-receipt',
        'file' => 'dist/daftar_pesanan.php'
    ],
    'articles' => [
        'title' => 'Berita & Artikel',
        'single_name' => 'Artikel Berita',
        'icon' => 'bi-newspaper',
        'file' => 'dist/daftar_artikel.php'
    ],
    'gallery' => [
        'title' => 'Galeri Foto Dokumentasi',
        'single_name' => 'Foto Dokumentasi',
        'icon' => 'bi-images',
        'file' => 'dist/daftar_galeri.php'
    ],
    'contact_messages' => [
        'title' => 'Pesan Masuk Pengunjung',
        'single_name' => 'Pesan Masuk',
        'icon' => 'bi-chat-left-dots',
        'file' => 'dist/daftar_pesan.php'
    ],
    'admins' => [
        'title' => 'Akun Administrator',
        'single_name' => 'Admin Dashboard',
        'icon' => 'bi-shield-lock',
        'file' => 'dist/daftar_admin.php'
    ]
];

$table = isset($_GET['table']) && array_key_exists($_GET['table'], $valid_tables) ? $_GET['table'] : '';
$id    = (int)($_GET['id'] ?? 0);

if (!$table || $id <= 0) {
    die("Error: Parameter table dan id wajib diisi secara valid.");
}

// Ambil record yang akan diedit
$res = mysqli_query($conn, "SELECT * FROM `$table` WHERE id = $id LIMIT 1");
$row = ($res && mysqli_num_rows($res) > 0) ? mysqli_fetch_assoc($res) : null;

if (!$row) {
    die("Error: Data dengan ID #$id tidak ditemukan di tabel $table.");
}

$table_info = $valid_tables[$table];

// Tentukan URL tujuan kembali (redirect)
$redirect_param = $_GET['redirect'] ?? $table_info['file'];
$redirect_url   = htmlspecialchars($redirect_param);

// Helper Query Dropdown
$albums_list   = mysqli_query($conn, "SELECT id, title FROM albums ORDER BY title ASC");
?>
<!doctype html>
<html lang="id">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Edit <?= htmlspecialchars($table_info['single_name']) ?> #<?= $id ?> — Admin PERUNGGU</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />

    <!-- Google Fonts & AdminLTE 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="all" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="dist/css/adminlte.css" />

    <style>
      .perunggu-brand { letter-spacing: 2px; font-weight: 800; color: #d4a373 !important; }
      .form-label { font-size: 0.92rem; margin-bottom: 0.35rem; }
      .form-control, .form-select { padding: 0.55rem 0.85rem; font-size: 0.93rem; }
      .form-control:focus, .form-select:focus { border-color: #d4a373; box-shadow: 0 0 0 0.25rem rgba(212, 163, 115, 0.2); }
      .preview-box { background: #fdfdfd; border: 1px dashed #d1d5db; border-radius: 8px; padding: 12px; }
      .card-header-edit { background: linear-gradient(to right, #ffffff, #fafafa); }
    </style>
  </head>

  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

      <!-- NAVBAR ATAS (ADMINLTE 4) -->
      <nav class="app-header navbar navbar-expand bg-body shadow-sm">
        <div class="container-fluid">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle Sidebar">
                <i class="bi bi-list fs-5"></i>
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="../web_perunggu/index.html" target="_blank" class="nav-link text-primary fw-medium">
                <i class="bi bi-box-arrow-up-right me-1"></i> Buka Website Publik
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="dist/index.php" class="nav-link">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard Utama
              </a>
            </li>
          </ul>

          <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item">
              <span class="badge text-bg-warning px-3 py-2 fw-semibold">
                <i class="bi bi-pencil-square me-1"></i> Mode Edit Record #<?= $id ?>
              </span>
            </li>
          </ul>
        </div>
      </nav>

      <!-- SIDEBAR ADMINLTE -->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
          <a href="dist/index.php" class="brand-link text-decoration-none">
            <img src="dist/assets/img/perunggu-logo.jpeg" alt="Logo" class="brand-image rounded-circle opacity-75 shadow" onerror="this.style.display='none'" />
            <span class="brand-text perunggu-brand">PERUNGGU</span>
          </a>
        </div>

        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
              <li class="nav-item">
                <a href="dist/index.php" class="nav-link">
                  <i class="nav-icon bi bi-speedometer2"></i>
                  <p>Dashboard</p>
                </a>
              </li>

              <li class="nav-header">KARYA MUSIK & AUDIO</li>
              <li class="nav-item">
                <a href="dist/daftar_album.php" class="nav-link <?= $table === 'albums' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-disc"></i>
                  <p>Albums</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="dist/daftar_track.php" class="nav-link <?= $table === 'tracks' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-music-note-beamed"></i>
                  <p>Tracks & Audio</p>
                </a>
              </li>

              <li class="nav-header">TUR & PENAMPILAN</li>
              <li class="nav-item">
                <a href="dist/daftar_tur.php" class="nav-link <?= $table === 'tour_dates' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-calendar-event"></i>
                  <p>Jadwal Tur</p>
                </a>
              </li>

              <li class="nav-header">MERCHANDISE & TRANSAKSI</li>
              <li class="nav-item">
                <a href="dist/daftar_produk.php" class="nav-link <?= $table === 'products' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-bag-check"></i>
                  <p>Produk Toko</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="dist/daftar_pesanan.php" class="nav-link <?= $table === 'orders' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-receipt"></i>
                  <p>Pesanan Masuk</p>
                </a>
              </li>

              <li class="nav-header">KONTEN & MEDIA</li>
              <li class="nav-item">
                <a href="dist/daftar_artikel.php" class="nav-link <?= $table === 'articles' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-newspaper"></i>
                  <p>Berita & Artikel</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="dist/daftar_galeri.php" class="nav-link <?= $table === 'gallery' ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-images"></i>
                  <p>Galeri Dokumentasi</p>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </aside>

      <!-- MAIN CONTENT WRAPPER -->
      <main class="app-main">

        <!-- HEADER HALAMAN -->
        <div class="app-content-header py-3">
          <div class="container-fluid">
            <div class="row align-items-center">
              <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                  Edit <?= htmlspecialchars($table_info['single_name']) ?>
                </h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent p-0">
                  <li class="breadcrumb-item"><a href="dist/index.php" class="text-decoration-none">Dashboard</a></li>
                  <li class="breadcrumb-item"><a href="<?= $redirect_url ?>" class="text-decoration-none"><?= htmlspecialchars($table_info['title']) ?></a></li>
                  <li class="breadcrumb-item active" aria-current="page">Edit #<?= $id ?></li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <!-- FORM CARD UTAMA (SESUAI GAMBAR 2) -->
        <div class="app-content pb-5">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12 col-lg-8 mx-auto">

                <div class="card card-outline card-primary shadow-sm mb-4 border-0">
                  <div class="card-header card-header-edit border-bottom py-3">
                    <h5 class="card-title fw-bold mb-0 text-dark">
                      <i class="bi bi-pencil-square text-primary me-2"></i> Ubah Data <?= htmlspecialchars($table_info['single_name']) ?>
                    </h5>
                  </div>

                  <form action="crud_action.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect_param) ?>">

                    <div class="card-body p-4">

                      <!-- 1. TABEL: ALBUMS -->
                      <?php if ($table === 'albums'): ?>
                        <div class="mb-3">
                          <label class="form-label fw-semibold">Judul Album <span class="text-danger">*</span></label>
                          <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($row['title']) ?>">
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Tahun Rilis <span class="text-danger">*</span></label>
                          <input type="number" name="release_year" class="form-control" required value="<?= (int)$row['release_year'] ?>">
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Foto Cover Album (opsional)</label>
                          <div class="d-flex align-items-center gap-3 mb-2 preview-box">
                            <img src="../web_perunggu/<?= htmlspecialchars($row['cover_image']) ?>" alt="Cover Saat Ini" class="rounded border shadow-sm object-fit-cover" style="width: 70px; height: 70px;" id="preview-cover" onerror="this.src='dist/assets/img/perunggu-logo.jpeg'">
                            <div>
                              <span class="badge text-bg-secondary mb-1">Cover Saat Ini</span>
                            </div>
                          </div>
                          <input type="file" name="cover_image" class="form-control" accept="image/*" onchange="previewUpload(this, 'preview-cover')">
                          <input type="hidden" name="existing_cover_image" value="<?= htmlspecialchars($row['cover_image']) ?>">
                          <div class="form-text text-muted">Kosongkan untuk mempertahankan foto lama. JPG, PNG, atau WEBP maksimal 2 MB.</div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Deskripsi Album</label>
                          <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($row['deskripsi']) ?></textarea>
                          <div class="form-text text-muted">Deskripsi ini akan muncul saat kursor mengambang (hover) di atas cover album di frontend.</div>
                        </div>

                      <!-- 2. TABEL: TRACKS (AUDIO MENGGUNAKAN CHOOSE FILE) -->
                      <?php elseif ($table === 'tracks'): ?>
                        <div class="mb-3">
                          <label class="form-label fw-semibold">Pilih Album <span class="text-danger">*</span></label>
                          <select name="album_id" class="form-select" required>
                            <?php while ($alb = mysqli_fetch_assoc($albums_list)): ?>
                              <option value="<?= $alb['id'] ?>" <?= $alb['id'] == $row['album_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($alb['title']) ?> (ID: <?= $alb['id'] ?>)
                              </option>
                            <?php endwhile; ?>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Judul Lagu <span class="text-danger">*</span></label>
                          <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($row['title']) ?>">
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Durasi (Format MM:SS) <span class="text-danger">*</span></label>
                          <input type="text" name="duration" class="form-control" required value="<?= htmlspecialchars($row['duration']) ?>" placeholder="04:20">
                        </div>

                        <!-- CHOOSE FILE AUDIO MP3 -->
                        <div class="mb-3">
                          <label class="form-label fw-semibold"><i class="bi bi-file-earmark-music text-primary me-1"></i> File Audio MP3 (opsional)</label>
                          <?php if (!empty($row['audio_url'])): ?>
                            <div class="mb-2 p-2 bg-light border rounded d-flex align-items-center justify-content-between flex-wrap gap-2 preview-box">
                              <div>
                                <span class="badge text-bg-primary mb-1"><i class="bi bi-music-note me-1"></i> Audio Saat Ini</span>
                              </div>
                              <audio controls src="../web_perunggu/<?= htmlspecialchars($row['audio_url']) ?>" style="height: 35px; max-width: 350px;"></audio>
                            </div>
                          <?php endif; ?>
                          <input type="file" name="audio_url" class="form-control" accept="audio/mp3,audio/*">
                          <input type="hidden" name="existing_audio_url" value="<?= htmlspecialchars($row['audio_url']) ?>">
                          <div class="form-text text-muted">Pilih file audio baru dari komputer (MP3, WAV, atau FLAC), maksimal 2 MB. Kosongkan untuk mempertahankan audio yang sekarang.</div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Lirik Lagu</label>
                          <textarea name="lyrics" class="form-control" rows="8" placeholder="[00:02.00] Baris lirik pertama&#10;[00:06.50] Baris lirik kedua"><?= htmlspecialchars($row['lyrics']) ?></textarea>
                          <div class="form-text text-muted">Untuk sinkronisasi, tulis satu baris lirik per baris dengan timestamp mulai, misalnya [00:12.50] Lirik dimulai di sini.</div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Chord Gitar</label>
                          <textarea name="chord" class="form-control font-monospace" rows="5"><?= htmlspecialchars($row['chord']) ?></textarea>
                        </div>

                        <div class="form-check form-switch mt-3">
                          <input class="form-check-input" type="checkbox" role="switch" id="switchNowPlaying" name="is_now_playing" value="1" <?= $row['is_now_playing'] == 1 ? 'checked' : '' ?>>
                          <label class="form-check-label fw-semibold" for="switchNowPlaying">
                            Jadikan Lagu yang Sedang Diputar (Now Playing) di Halaman Beranda
                          </label>
                        </div>

                      <!-- 3. TABEL: TOUR_DATES -->
                      <?php elseif ($table === 'tour_dates'): ?>
                        <div class="mb-3">
                          <label class="form-label fw-semibold">Nama Acara / Konser <span class="text-danger">*</span></label>
                          <input type="text" name="event_name" class="form-control" required value="<?= htmlspecialchars($row['event_name']) ?>">
                        </div>

                        <div class="row g-2 mb-3">
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Kota <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" required value="<?= htmlspecialchars($row['city']) ?>">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Venue / Tempat <span class="text-danger">*</span></label>
                            <input type="text" name="venue" class="form-control" required value="<?= htmlspecialchars($row['venue']) ?>">
                          </div>
                        </div>

                        <div class="row g-2 mb-3">
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Pertunjukan <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" class="form-control" required value="<?= htmlspecialchars($row['event_date']) ?>">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="text" name="event_time" class="form-control" required value="<?= htmlspecialchars($row['event_time']) ?>" placeholder="19:30 WIB">
                          </div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Status Ketersediaan Tiket <span class="text-danger">*</span></label>
                          <select name="status" class="form-select" required>
                            <option value="available" <?= $row['status'] === 'available' ? 'selected' : '' ?>>Available (Tersedia)</option>
                            <option value="sold_out" <?= $row['status'] === 'sold_out' ? 'selected' : '' ?>>Sold Out (Habis Terjual)</option>
                            <option value="presale" <?= $row['status'] === 'presale' ? 'selected' : '' ?>>Presale</option>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Link Pembelian Tiket</label>
                          <input type="url" name="ticket_url" class="form-control" value="<?= htmlspecialchars($row['ticket_url']) ?>" placeholder="https://tiket.com/...">
                        </div>

                      <!-- 4. TABEL: PRODUCTS -->
                      <?php elseif ($table === 'products'): ?>
                        <div class="mb-3">
                          <label class="form-label fw-semibold">Kategori Merchandise <span class="text-danger">*</span></label>
                          <select name="category" class="form-select" required>
                            <?php foreach (['kaos', 'hoodie', 'aksesoris', 'poster'] as $cat): ?>
                              <option value="<?= $cat ?>" <?= $row['category'] === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Nama Produk Merchandise <span class="text-danger">*</span></label>
                          <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($row['name']) ?>">
                        </div>

                        <div class="row g-2 mb-3">
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" required value="<?= (float)$row['price'] ?>" step="any">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Kuantitas Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control" required value="<?= (int)$row['stock'] ?>" min="0">
                          </div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Foto Produk (opsional)</label>
                          <div class="d-flex align-items-center gap-3 mb-2 preview-box">
                            <img src="../web_perunggu/<?= htmlspecialchars($row['image_url']) ?>" alt="Foto Produk" class="rounded-circle border shadow-sm object-fit-cover" style="width: 70px; height: 70px;" id="preview-prod" onerror="this.src='dist/assets/img/perunggu-logo.jpeg'">
                            <div>
                              <span class="badge text-bg-secondary mb-1">Foto Saat Ini</span>
                            </div>
                          </div>
                          <input type="file" name="image_url" class="form-control" accept="image/*" onchange="previewUpload(this, 'preview-prod')">
                          <input type="hidden" name="existing_image_url" value="<?= htmlspecialchars($row['image_url']) ?>">
                          <div class="form-text text-muted">Kosongkan untuk mempertahankan foto lama. JPG, PNG, atau WEBP maksimal 2 MB.</div>
                        </div>

                      <!-- 5. TABEL: ORDERS -->
                      <?php elseif ($table === 'orders'): ?>
                        <div class="alert alert-info py-2">
                          Data checkout dari pengunjung hanya dapat dilihat. Admin hanya dapat mengubah status pesanan.
                        </div>
                        <div class="row g-2 mb-3">
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Kode Pesanan</label>
                            <input type="text" class="form-control" readonly value="<?= htmlspecialchars($row['order_code']) ?>">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Pemesan</label>
                            <input type="text" class="form-control" readonly value="<?= htmlspecialchars($row['customer_name']) ?>">
                          </div>
                        </div>

                        <div class="row g-2 mb-3">
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">No. WhatsApp</label>
                            <input type="text" class="form-control" readonly value="<?= htmlspecialchars($row['whatsapp']) ?>">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Metode Pembayaran</label>
                            <input type="text" class="form-control" readonly value="<?= htmlspecialchars($row['payment_method']) ?>">
                          </div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Alamat Pengiriman</label>
                          <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($row['address']) ?></textarea>
                        </div>

                        <div class="row g-2 mb-3">
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Total Tagihan (Rp)</label>
                            <input type="number" class="form-control" readonly value="<?= (float)$row['total_amount'] ?>">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Status Pembayaran <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select" required>
                              <?php
                                $payment_status_transitions = [
                                    'pending' => $row['shipping_status'] === 'cancelled' ? ['pending'] : ['pending', 'paid'],
                                    'paid' => ['paid', 'refunded'],
                                    'refunded' => ['refunded']
                                ];
                                $payment_status_labels = [
                                    'pending' => 'Belum Dibayar',
                                    'paid' => 'Lunas',
                                    'refunded' => 'Dikembalikan'
                                ];
                                foreach ($payment_status_transitions[$row['payment_status']] ?? [$row['payment_status']] as $payment_status):
                              ?>
                                <option value="<?= $payment_status ?>" <?= $row['payment_status'] === $payment_status ? 'selected' : '' ?>>
                                  <?= htmlspecialchars($payment_status_labels[$payment_status] ?? $payment_status) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted">Tandai lunas setelah pembayaran diterima. Refund pesanan yang belum dikirim harus disertai pembatalan pengiriman.</div>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Status Pengiriman <span class="text-danger">*</span></label>
                            <select name="shipping_status" class="form-select" required>
                              <?php
                                $shipping_status_transitions = [
                                    'pending' => ['pending', 'shipped', 'cancelled'],
                                    'shipped' => ['shipped', 'delivered'],
                                    'delivered' => ['delivered'],
                                    'cancelled' => ['cancelled']
                                ];
                                $shipping_status_labels = [
                                    'pending' => 'Menunggu Diproses',
                                    'shipped' => 'Dikirim',
                                    'delivered' => 'Diterima',
                                    'cancelled' => 'Dibatalkan'
                                ];
                                foreach ($shipping_status_transitions[$row['shipping_status']] ?? [$row['shipping_status']] as $shipping_status):
                              ?>
                                <option value="<?= $shipping_status ?>" <?= $row['shipping_status'] === $shipping_status ? 'selected' : '' ?>>
                                  <?= htmlspecialchars($shipping_status_labels[$shipping_status] ?? $shipping_status) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted">Pilih Diterima setelah ada konfirmasi pelanggan atau kurir. COD dapat dikirim sebelum lunas; pembatalan sebelum pengiriman memulihkan stok.</div>
                          </div>
                        </div>

                      <!-- 7. TABEL: ARTICLES -->
                      <?php elseif ($table === 'articles'): ?>
                        <div class="mb-3">
                          <label class="form-label fw-semibold">Judul Artikel / Berita <span class="text-danger">*</span></label>
                          <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($row['title']) ?>">
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                          <select name="category" class="form-select" required>
                            <?php foreach (['Music', 'Tour', 'Behind The Scene', 'Merchandise', 'News'] as $cat): ?>
                              <option value="<?= $cat ?>" <?= $row['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Foto Thumbnail (opsional)</label>
                          <div class="d-flex align-items-center gap-3 mb-2 preview-box">
                            <img src="../web_perunggu/<?= htmlspecialchars($row['thumbnail_url']) ?>" alt="Thumb Saat Ini" class="rounded border shadow-sm object-fit-cover" style="width: 70px; height: 70px;" id="preview-art" onerror="this.src='dist/assets/img/perunggu-logo.jpeg'">
                            <div>
                              <span class="badge text-bg-secondary mb-1">Thumbnail Saat Ini</span>
                            </div>
                          </div>
                          <input type="file" name="thumbnail_url" class="form-control" accept="image/*" onchange="previewUpload(this, 'preview-art')">
                          <input type="hidden" name="existing_thumbnail_url" value="<?= htmlspecialchars($row['thumbnail_url']) ?>">
                          <div class="form-text text-muted">Kosongkan untuk mempertahankan foto lama. JPG, PNG, atau WEBP maksimal 2 MB.</div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Konten Lengkap Berita <span class="text-danger">*</span></label>
                          <textarea name="content" class="form-control" required rows="5"><?= htmlspecialchars($row['content']) ?></textarea>
                        </div>

                      <!-- 8. TABEL: GALLERY -->
                      <?php elseif ($table === 'gallery'): ?>
                        <div class="row g-2 mb-3">
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori Galeri <span class="text-danger">*</span></label>
                            <input type="text" name="category" class="form-control" required value="<?= htmlspecialchars($row['category']) ?>" placeholder="Behind The Scene / Members / Concert">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Judul Momen Foto <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($row['title']) ?>">
                          </div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Foto Galeri (opsional)</label>
                          <div class="d-flex align-items-center gap-3 mb-2 preview-box">
                            <img src="../web_perunggu/<?= htmlspecialchars($row['image_url']) ?>" alt="Foto Galeri" class="rounded-circle border shadow-sm object-fit-cover" style="width: 70px; height: 70px;" id="preview-gal" onerror="this.src='dist/assets/img/perunggu-logo.jpeg'">
                            <div>
                              <span class="badge text-bg-secondary mb-1">Foto Saat Ini</span>
                            </div>
                          </div>
                          <input type="file" name="image_url" class="form-control" accept="image/*" onchange="previewUpload(this, 'preview-gal')">
                          <input type="hidden" name="existing_image_url" value="<?= htmlspecialchars($row['image_url']) ?>">
                          <div class="form-text text-muted">Kosongkan untuk mempertahankan foto lama. JPG, PNG, atau WEBP maksimal 2 MB.</div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Keterangan / Caption</label>
                          <input type="text" name="caption" class="form-control" value="<?= htmlspecialchars($row['caption']) ?>" placeholder="Keterangan singkat saat hover...">
                        </div>

                      <!-- 9. TABEL: CONTACT_MESSAGES -->
                      <?php elseif ($table === 'contact_messages'): ?>
                        <div class="alert alert-info py-2">
                          Isi pesan dari pengunjung tidak dapat diubah. Admin hanya dapat memperbarui status dibaca.
                        </div>
                        <div class="mb-3">
                          <label class="form-label fw-semibold">Nama Pengirim</label>
                          <input type="text" class="form-control" readonly value="<?= htmlspecialchars($row['name']) ?>">
                        </div>
                        <div class="mb-3">
                          <label class="form-label fw-semibold">Alamat Email</label>
                          <input type="email" class="form-control" readonly value="<?= htmlspecialchars($row['email']) ?>">
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Subjek Pesan</label>
                          <input type="text" class="form-control" readonly value="<?= htmlspecialchars($row['subject']) ?>">
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Isi Pesan</label>
                          <textarea class="form-control" rows="4" readonly><?= htmlspecialchars($row['message']) ?></textarea>
                        </div>

                        <div class="form-check form-switch mt-3">
                          <input class="form-check-input" type="checkbox" role="switch" id="switchRead" name="is_read" value="1" <?= $row['is_read'] == 1 ? 'checked' : '' ?>>
                          <label class="form-check-label fw-semibold" for="switchRead">
                            Tandai sebagai Pesan Sudah Dibaca (Read)
                          </label>
                        </div>

                      <!-- 11. TABEL: ADMINS -->
                      <?php elseif ($table === 'admins'): ?>
                        <div class="row g-2 mb-3">
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($row['username']) ?>">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Administrator <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($row['email']) ?>">
                          </div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Password Baru (opsional)</label>
                          <input type="password" name="password" class="form-control" minlength="12" placeholder="Kosongkan jika password tidak ingin diubah (minimal 12 karakter)">
                          <div class="form-text text-muted">Password akan otomatis dienkripsi dengan standar keamanan BCRYPT.</div>
                        </div>
                      <?php endif; ?>

                    </div>

                    <!-- FOOTER TOMBOL AKSI -->
                    <div class="card-footer bg-light border-top py-3 d-flex justify-content-between align-items-center">
                      <a href="<?= $redirect_url ?>" class="btn btn-outline-secondary px-3">
                        <i class="bi bi-arrow-left me-1"></i> Batal / Kembali
                      </a>
                      <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                        <i class="bi bi-floppy me-1"></i> Simpan Perubahan
                      </button>
                    </div>

                  </form>
                </div>

              </div>
            </div>
          </div>
        </div>

      </main>

      <!-- FOOTER ADMINLTE -->
      <footer class="app-footer text-muted small py-3 border-top bg-white">
        <div class="container-fluid d-flex justify-content-between">
          <div>Basis Data: <strong>perunggu_db</strong> (MySQL) &bull; Semester 4 IT</div>
          <div>Copyright &copy; 2026 <strong>PERUNGGU</strong>. All rights reserved.</div>
        </div>
      </footer>

    </div>

    <!-- JS SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
    <script src="dist/js/adminlte.js"></script>
    <script>
      function previewUpload(input, targetImgId) {
        if (input.files && input.files[0]) {
          const reader = new FileReader();
          reader.onload = function(e) {
            const img = document.getElementById(targetImgId);
            if (img) img.src = e.target.result;
          };
          reader.readAsDataURL(input.files[0]);
        }
      }
    </script>
  </body>
</html>
