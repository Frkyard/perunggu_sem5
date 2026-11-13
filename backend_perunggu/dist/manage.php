<?php
require_once __DIR__ . '/../connect.php';

$allowed_tables = [
    'albums'           => ['title' => 'Katalog Album', 'icon' => 'bi-disc', 'desc' => 'Daftar album dan rilisan musik resmi band PERUNGGU.'],
    'tracks'           => ['title' => 'Trek Musik & Audio Player', 'icon' => 'bi-music-note-beamed', 'desc' => 'Daftar lagu, file mp3, lirik, chord, dan status lagu yang sedang aktif diputar.'],
    'tour_dates'       => ['title' => 'Jadwal Tur & Konser', 'icon' => 'bi-calendar-event', 'desc' => 'Agenda jadwal pertunjukan panggung, festival, venue gedung, dan ketersediaan tiket.'],
    'products'         => ['title' => 'Katalog Merchandise Toko', 'icon' => 'bi-bag-check', 'desc' => 'Katalog merchandise resmi band, kategori, harga satuan, dan kuantitas stok.'],
    'orders'           => ['title' => 'Pesanan Merchandise Masuk', 'icon' => 'bi-receipt', 'desc' => 'Data transaksi pesanan fans dengan status pembayaran dan pengiriman terpisah.'],
    'order_items'      => ['title' => 'Rincian Item Pesanan', 'icon' => 'bi-list-check', 'desc' => 'Rincian produk yang dibeli per transaksi pesanan fans (relasi orders & products).'],
    'articles'         => ['title' => 'Berita & Artikel Band', 'icon' => 'bi-newspaper', 'desc' => 'Siaran pers resmi, kabar peluncuran single/album, dan catatan dokumentasi.'],
    'gallery'          => ['title' => 'Galeri Foto Dokumentasi', 'icon' => 'bi-images', 'desc' => 'Arsip dokumentasi foto panggung, personel, dan suasana behind the scene.'],
    'contact_messages' => ['title' => 'Pesan Kontak Masuk', 'icon' => 'bi-chat-left-dots', 'desc' => 'Pesan, tawaran kerja sama konser, atau pertanyaan dari fans.'],
    'subscribers'      => ['title' => 'Pelanggan Newsletter', 'icon' => 'bi-envelope-check', 'desc' => 'Daftar email fans terdaftar untuk menerima info terbaru.'],
    'admins'           => ['title' => 'Akun Administrator', 'icon' => 'bi-person-lock', 'desc' => 'Data kredensial pengguna terdaftar pengelola dashboard admin.']
];

$current_table = isset($_GET['table']) && array_key_exists($_GET['table'], $allowed_tables) ? $_GET['table'] : 'albums';
$table_meta    = $allowed_tables[$current_table];

// Query data
$sql = "SELECT * FROM `$current_table` ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
$total_rows = $result ? mysqli_num_rows($result) : 0;
?>
<!doctype html>
<html lang="id">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?= htmlspecialchars($table_meta['title']) ?> — PERUNGGU CMS</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="all" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="./css/adminlte.css" />

    <style>
      .perunggu-brand { letter-spacing: 2px; font-weight: 800; color: #d4a373 !important; }
    </style>
  </head>
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
      
      <!-- HEADER -->
      <nav class="app-header navbar navbar-expand bg-body shadow-sm">
        <div class="container-fluid">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="bi bi-list fs-5"></i></a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="./index.php" class="nav-link"><i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard</a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="../../web_perunggu/index.html" target="_blank" class="nav-link text-primary"><i class="bi bi-box-arrow-up-right me-1"></i> Buka Website Publik</a>
            </li>
          </ul>

          <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                <img src="./assets/img/perunggu-logo.jpeg" class="user-image rounded-circle shadow-sm" alt="Admin" style="width: 32px; height: 32px; object-fit: cover;" />
                <span class="d-none d-md-inline fw-semibold">Admin Perunggu</span>
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <!-- SIDEBAR -->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand d-flex align-items-center px-3 py-2 border-bottom border-secondary border-opacity-25">
          <a href="./index.php" class="brand-link d-flex align-items-center gap-2 text-decoration-none">
            <img src="./assets/img/perunggu-logo.jpeg" alt="Logo" class="brand-image rounded shadow" style="width: 34px; height: 34px; object-fit: cover;" />
            <span class="brand-text perunggu-brand fs-5">PERUNGGU</span>
          </a>
        </div>

        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
              <li class="nav-item">
                <a href="./index.php" class="nav-link">
                  <i class="nav-icon bi bi-speedometer2"></i>
                  <p>Dashboard</p>
                </a>
              </li>

              <li class="nav-header text-uppercase text-secondary fs-7 fw-bold mt-2">11 TABEL BASIS DATA</li>
              <?php foreach ($allowed_tables as $tbl_name => $tbl_info): ?>
                <li class="nav-item">
                  <a href="manage.php?table=<?= $tbl_name ?>" class="nav-link <?= $tbl_name === $current_table ? 'active' : '' ?>">
                    <i class="nav-icon bi <?= $tbl_info['icon'] ?>"></i>
                    <p><?= htmlspecialchars($tbl_info['title']) ?></p>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </nav>
        </div>
      </aside>

      <!-- MAIN APP CONTENT -->
      <main class="app-main">
        <div class="app-content-header py-3 border-bottom mb-3">
          <div class="container-fluid">
            <div class="row align-items-center">
              <div class="col-sm-8">
                <h2 class="mb-1 fs-3 fw-bold">
                  <i class="bi <?= $table_meta['icon'] ?> text-primary me-2"></i> <?= htmlspecialchars($table_meta['title']) ?>
                </h2>
                <p class="text-secondary small mb-0">
                  Tabel: <code class="fw-bold">perunggu_db.<?= htmlspecialchars($current_table) ?></code> &bull; <?= htmlspecialchars($table_meta['desc']) ?>
                </p>
              </div>
              <div class="col-sm-4 text-sm-end mt-2 mt-sm-0">
                <span class="badge text-bg-primary fs-7 px-3 py-2">
                  <i class="bi bi-layers me-1"></i> Total: <?= $total_rows ?> Baris Data
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <!-- DATA TABLE CARD -->
            <div class="card shadow-sm border-0 mb-4">
              <div class="card-header bg-body d-flex flex-wrap align-items-center justify-content-between py-3 gap-2">
                <div class="d-flex align-items-center gap-2">
                  <span class="fw-semibold">Tabel:</span>
                  <select class="form-select form-select-sm" onchange="location = this.value;" style="width: 220px;">
                    <?php foreach ($allowed_tables as $k => $v): ?>
                      <option value="manage.php?table=<?= $k ?>" <?= $k === $current_table ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['title']) ?> (<?= $k ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="d-flex align-items-center gap-2">
                  <div class="input-group input-group-sm" style="width: 250px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="tableSearch" class="form-control" placeholder="Cari data di tabel..." onkeyup="filterTable()" />
                  </div>
                  <a href="../bacadata.php?table=<?= $current_table ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-code-slash me-1"></i> Raw PHP View
                  </a>
                </div>
              </div>

              <div class="card-body p-0">
                <?php if ($result && $total_rows > 0): ?>
                  <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0" id="dataTable">
                      <thead class="table-dark">
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
                            <?php foreach ($row as $col => $val): ?>
                              <td>
                                <?php
                                if ($col === 'password') {
                                    echo '<span class="badge text-bg-secondary"><i class="bi bi-shield-lock me-1"></i> Terenkripsi</span>';
                                } elseif ($col === 'status') {
                                    if ($val === 'paid' || $val === 'available') {
                                        echo '<span class="badge text-bg-success">' . htmlspecialchars($val) . '</span>';
                                    } elseif ($val === 'pending' || $val === 'presale') {
                                        echo '<span class="badge text-bg-warning">' . htmlspecialchars($val) . '</span>';
                                    } else {
                                        echo '<span class="badge text-bg-danger">' . htmlspecialchars($val) . '</span>';
                                    }
                                } elseif ($col === 'is_now_playing') {
                                    echo $val ? '<span class="badge text-bg-success"><i class="bi bi-play-fill"></i> Ya</span>' : '<span class="badge text-bg-light border text-muted">Tidak</span>';
                                } elseif ($col === 'is_read') {
                                    echo $val ? '<span class="badge text-bg-secondary">Sudah Dibaca</span>' : '<span class="badge text-bg-danger">Belum Dibaca</span>';
                                } elseif ($col === 'price' || $col === 'total_amount') {
                                    echo '<strong>Rp ' . number_format((float)$val, 0, ',', '.') . '</strong>';
                                } elseif ($col === 'image_url' || $col === 'cover_image' || $col === 'thumbnail_url') {
                                    echo '<a href="../../web_perunggu/' . htmlspecialchars($val) . '" target="_blank" class="small text-truncate d-inline-block" style="max-width: 150px;"><i class="bi bi-image me-1"></i>' . htmlspecialchars($val) . '</a>';
                                } elseif ($col === 'audio_url') {
                                    echo '<a href="../../web_perunggu/' . htmlspecialchars($val) . '" target="_blank" class="small"><i class="bi bi-file-earmark-music me-1"></i>' . htmlspecialchars($val) . '</a>';
                                } elseif (strlen($val ?? '') > 80) {
                                    echo '<span title="' . htmlspecialchars($val) . '">' . htmlspecialchars(substr($val, 0, 80)) . '...</span>';
                                } else {
                                    echo htmlspecialchars($val ?? '-');
                                }
                                ?>
                              </td>
                            <?php endforeach; ?>
                          </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                    Belum ada baris data di tabel <code><?= htmlspecialchars($current_table) ?></code>.
                  </div>
                <?php endif; ?>
              </div>

              <div class="card-footer bg-body border-top py-2 d-flex justify-content-between align-items-center">
                <span class="small text-secondary">Menampilkan <?= $total_rows ?> record dari tabel <code><?= htmlspecialchars($current_table) ?></code></span>
                <a href="./index.php" class="btn btn-sm btn-outline-primary">&laquo; Kembali ke Dashboard</a>
              </div>
            </div>

          </div>
        </div>
      </main>

      <!-- FOOTER -->
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">
          Tabel Terintegrasi: <strong>perunggu_db</strong> &bull; Semester 4 IT
        </div>
        <strong>Copyright &copy; 2026 <a href="../../web_perunggu/index.html" class="text-decoration-none">PERUNGGU</a>.</strong> All rights reserved.
      </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
    <script src="./js/adminlte.js"></script>
    <script>
      function filterTable() {
        const input = document.getElementById("tableSearch");
        const filter = input.value.toLowerCase();
        const table = document.getElementById("dataTable");
        if (!table) return;
        const trs = table.getElementsByTagName("tr");
        for (let i = 1; i < trs.length; i++) {
          const text = trs[i].textContent.toLowerCase();
          trs[i].style.display = text.includes(filter) ? "" : "none";
        }
      }
    </script>
  </body>
</html>
<?php
mysqli_close($conn);
?>

