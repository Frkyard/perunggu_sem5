<?php
// ========================================================
// TAHAP 1: FORM CREATE (INSERT) - SEMUA 11 TABEL
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

$selected_table = isset($_GET['table']) && array_key_exists($_GET['table'], $valid_tables) ? $_GET['table'] : 'albums';

if (in_array($selected_table, ['orders', 'order_items'], true)) {
    $msg = urlencode('Pesanan dan rincian item hanya dapat dibuat melalui checkout.');
    header("Location: read.php?table=$selected_table&status=error&msg=$msg");
    exit;
}

// Ambil list albums untuk dropdown tracks
$albums_list = mysqli_query($conn, "SELECT id, title FROM albums ORDER BY title ASC");

// Ambil list orders & products untuk order_items
$orders_list = mysqli_query($conn, "SELECT id, order_code, customer_name FROM orders ORDER BY id DESC LIMIT 20");
$products_list = mysqli_query($conn, "SELECT id, name, price FROM products ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create (Insert) - Basis Data Perunggu</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f5f8; margin: 0; padding: 30px 15px; color: #333; }
        .card { max-width: 700px; margin: 0 auto; background: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
        h2 { color: #8c5a2b; margin-top: 0; border-bottom: 2px solid #f0e6dd; padding-bottom: 10px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-weight: bold; margin-bottom: 6px; font-size: 14px; }
        input[type="text"], input[type="number"], input[type="email"], input[type="password"], input[type="date"], select, textarea {
            width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; box-sizing: border-box;
        }
        input:focus, select:focus, textarea:focus { border-color: #8c5a2b; outline: none; }
        .btn-submit { background: #8c5a2b; color: white; border: none; padding: 10px 20px; font-size: 15px; font-weight: bold; border-radius: 5px; cursor: pointer; }
        .btn-submit:hover { background: #71461f; }
        .btn-cancel { color: #666; text-decoration: none; margin-left: 15px; font-size: 14px; }
        .nav-tabs { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 20px; }
        .nav-tabs a { text-decoration: none; padding: 6px 12px; background: #eee; color: #333; border-radius: 4px; font-size: 13px; font-weight: bold; }
        .nav-tabs a.active { background: #8c5a2b; color: white; }
    </style>
</head>
<body>

<div class="card">
    <h2>Operasi Create (Insert) Data Baru</h2>
    <p style="font-size: 13px; color: #666; margin-top: -5px;">Pilih tabel di bawah untuk menambahkan record baru ke database <code>perunggu_db</code>:</p>

    <!-- PILIHAN TABEL -->
    <div class="nav-tabs">
        <?php foreach ($valid_tables as $tbl => $lbl): ?>
            <a href="?table=<?= $tbl ?>" class="<?= $tbl === $selected_table ? 'active' : '' ?>">
                <?= $lbl ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- FORM INSERT SESUAI TABEL YANG DIPILIH -->
    <form action="crud_action.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="table" value="<?= $selected_table ?>">
        <input type="hidden" name="redirect" value="read.php">

        <?php if ($selected_table === 'albums'): ?>
            <div class="form-group">
                <label>Judul Album (*):</label>
                <input type="text" name="title" required placeholder="Contoh: Memorandum">
            </div>
            <div class="form-group">
                <label>Tahun Rilis (*):</label>
                <input type="number" name="release_year" required value="2026">
            </div>
            <div class="form-group">
                <label>Foto Cover Album (*):</label>
                <input type="file" name="cover_image" accept="image/*" required>
                <small>Ukuran maksimal 2 MB.</small>
            </div>
            <div class="form-group">
                <label>Deskripsi Album:</label>
                <textarea name="deskripsi" rows="3" placeholder="Narasi cerita dan latar belakang album..."></textarea>
            </div>

        <?php elseif ($selected_table === 'tracks'): ?>
            <div class="form-group">
                <label>Pilih Album (*):</label>
                <select name="album_id" required>
                    <?php while ($alb = mysqli_fetch_assoc($albums_list)): ?>
                        <option value="<?= $alb['id'] ?>"><?= htmlspecialchars($alb['title']) ?> (ID: <?= $alb['id'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Judul Lagu (*):</label>
                <input type="text" name="title" required placeholder="Contoh: 33x">
            </div>
            <div class="form-group">
                <label>Durasi (Format MM:SS) (*):</label>
                <input type="text" name="duration" required value="4:30">
            </div>
            <div class="form-group">
                <label>File Audio MP3 (*):</label>
                <input type="file" name="audio_url" accept="audio/mp3,audio/*" required>
                <small>Ukuran maksimal 2 MB.</small>
            </div>
            <div class="form-group">
                <label>Lirik Lagu:</label>
                <textarea name="lyrics" rows="6" placeholder="[00:02.00] Baris lirik pertama&#10;[00:06.50] Baris lirik kedua"></textarea>
                <small>Gunakan satu timestamp mulai per baris, format [menit:detik.desimal]. Contoh: [00:12.50] Lirik dimulai di sini.</small>
            </div>
            <div class="form-group">
                <label>Chord Gitar:</label>
                <textarea name="chord" rows="2" placeholder="Notasi chord..."></textarea>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="is_now_playing" value="1"> Jadikan Lagu yang Sedang Diputar (Now Playing)</label>
            </div>

        <?php elseif ($selected_table === 'tour_dates'): ?>
            <div class="form-group">
                <label>Nama Acara / Festival (*):</label>
                <input type="text" name="event_name" required placeholder="Contoh: Pertunjukan Dalam Dinamika">
            </div>
            <div class="form-group">
                <label>Kota (*):</label>
                <input type="text" name="city" required placeholder="Contoh: Jakarta">
            </div>
            <div class="form-group">
                <label>Venue / Lokasi Gedung (*):</label>
                <input type="text" name="venue" required placeholder="Contoh: GBK Arena">
            </div>
            <div class="form-group">
                <label>Tanggal Pelaksanaan (*):</label>
                <input type="date" name="event_date" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label>Waktu (*):</label>
                <input type="text" name="event_time" required value="19:00 WIB">
            </div>
            <div class="form-group">
                <label>Link Tiket:</label>
                <input type="text" name="ticket_url" value="https://tiket.com/perunggu">
            </div>
            <div class="form-group">
                <label>Status Tiket:</label>
                <select name="status">
                    <option value="available">Available</option>
                    <option value="presale">Presale</option>
                    <option value="sold_out">Sold Out</option>
                </select>
            </div>

        <?php elseif ($selected_table === 'products'): ?>
            <div class="form-group">
                <label>Kategori Produk (*):</label>
                <select name="category" required>
                    <option value="kaos">Kaos</option>
                    <option value="hoodie">Hoodie</option>
                    <option value="aksesoris">Aksesoris</option>
                    <option value="poster">Poster</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nama Merchandise (*):</label>
                <input type="text" name="name" required placeholder="Contoh: T-Shirt Perunggu">
            </div>
            <div class="form-group">
                <label>Harga Satuan (Rp) (*):</label>
                <input type="number" name="price" required value="150000">
            </div>
            <div class="form-group">
                <label>Kuantitas Stok (*):</label>
                <input type="number" name="stock" required value="20">
            </div>
            <div class="form-group">
                <label>Foto Produk Merchandise (*):</label>
                <input type="file" name="image_url" accept="image/*" required>
                <small>Ukuran maksimal 2 MB.</small>
            </div>

        <?php elseif ($selected_table === 'orders'): ?>
            <div class="form-group">
                <label>Kode Pesanan (Biarkan kosong untuk auto-generate):</label>
                <input type="text" name="order_code" placeholder="PRG-XXXXXX">
            </div>
            <div class="form-group">
                <label>Nama Pemesan (*):</label>
                <input type="text" name="customer_name" required placeholder="Nama lengkap">
            </div>
            <div class="form-group">
                <label>Nomor WhatsApp (*):</label>
                <input type="text" name="whatsapp" required placeholder="08xxxxxxxxxx">
            </div>
            <div class="form-group">
                <label>Alamat Pengiriman (*):</label>
                <textarea name="address" required rows="2" placeholder="Alamat lengkap tujuan"></textarea>
            </div>
            <div class="form-group">
                <label>Metode Pembayaran (*):</label>
                <select name="payment_method">
                    <option value="QRIS">QRIS</option>
                    <option value="Transfer BCA">Transfer BCA</option>
                    <option value="COD">COD</option>
                </select>
            </div>
            <div class="form-group">
                <label>Total Biaya (Rp) (*):</label>
                <input type="number" name="total_amount" required value="200000">
            </div>
            <div class="form-group">
                <label>Status Transaksi:</label>
                <select name="status">
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="shipped">Shipped</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

        <?php elseif ($selected_table === 'order_items'): ?>
            <div class="form-group">
                <label>Pilih Transaksi Pesanan (*):</label>
                <select name="order_id" required>
                    <?php while ($ord = mysqli_fetch_assoc($orders_list)): ?>
                        <option value="<?= $ord['id'] ?>"><?= htmlspecialchars($ord['order_code']) ?> - <?= htmlspecialchars($ord['customer_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Pilih Produk Merchandise (*):</label>
                <select name="product_id" required>
                    <?php while ($prod = mysqli_fetch_assoc($products_list)): ?>
                        <option value="<?= $prod['id'] ?>"><?= htmlspecialchars($prod['name']) ?> (Rp <?= number_format($prod['price'], 0, ',', '.') ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Jumlah Kuantitas (*):</label>
                <input type="number" name="quantity" required value="1" min="1">
            </div>

        <?php elseif ($selected_table === 'subscribers'): ?>
            <div class="form-group">
                <label>Email Fans (*):</label>
                <input type="email" name="email" required placeholder="nama@example.invalid">
            </div>

        <?php elseif ($selected_table === 'contact_messages'): ?>
            <div class="form-group">
                <label>Nama Pengirim (*):</label>
                <input type="text" name="name" required placeholder="Nama pengirim">
            </div>
            <div class="form-group">
                <label>Email Pengirim (*):</label>
                <input type="email" name="email" required placeholder="email@domain.com">
            </div>
            <div class="form-group">
                <label>Subjek Pesan (*):</label>
                <input type="text" name="subject" required placeholder="Perihal pesan">
            </div>
            <div class="form-group">
                <label>Isi Pesan (*):</label>
                <textarea name="message" required rows="3" placeholder="Isi pesan..."></textarea>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="is_read" value="1"> Tandai sudah dibaca</label>
            </div>

        <?php elseif ($selected_table === 'articles'): ?>
            <div class="form-group">
                <label>Judul Artikel / Berita (*):</label>
                <input type="text" name="title" required placeholder="Judul siaran pers...">
            </div>
            <div class="form-group">
                <label>Kategori (*):</label>
                <select name="category">
                    <option value="Music">Music</option>
                    <option value="Tour">Tour</option>
                    <option value="Behind The Scene">Behind The Scene</option>
                    <option value="Merchandise">Merchandise</option>
                    <option value="News">News</option>
                </select>
            </div>
            <div class="form-group">
                <label>Foto Thumbnail Berita (*):</label>
                <input type="file" name="thumbnail_url" accept="image/*" required>
                <small>Ukuran maksimal 2 MB.</small>
            </div>
            <div class="form-group">
                <label>Konten Lengkap (*):</label>
                <textarea name="content" required rows="4" placeholder="Naskah artikel..."></textarea>
            </div>

        <?php elseif ($selected_table === 'gallery'): ?>
            <div class="form-group">
                <label>Kategori Galeri (*):</label>
                <input type="text" name="category" required value="Behind The Scene">
            </div>
            <div class="form-group">
                <label>Judul Foto (*):</label>
                <input type="text" name="title" required placeholder="Momen dokumentasi...">
            </div>
            <div class="form-group">
                <label>File Foto Galeri (*):</label>
                <input type="file" name="image_url" accept="image/*" required>
                <small>Ukuran maksimal 2 MB.</small>
            </div>
            <div class="form-group">
                <label>Keterangan / Caption:</label>
                <input type="text" name="caption" placeholder="Keterangan singkat saat hover...">
            </div>

        <?php elseif ($selected_table === 'admins'): ?>
            <div class="form-group">
                <label>Username (*):</label>
                <input type="text" name="username" required placeholder="Username admin">
            </div>
            <div class="form-group">
                <label>Email Admin (*):</label>
                <input type="email" name="email" required placeholder="admin@perunggu.id">
            </div>
            <div class="form-group">
                <label>Password Akun (*):</label>
                <input type="password" name="password" required minlength="12" placeholder="Password baru (minimal 12 karakter)">
            </div>
        <?php endif; ?>

        <div style="margin-top: 25px;">
            <button type="submit" class="btn-submit">Simpan Record Baru &raquo;</button>
            <a href="read.php?table=<?= $selected_table ?>" class="btn-cancel">Batal / Kembali ke Data</a>
        </div>
    </form>
</div>

</body>
</html>
<?php mysqli_close($conn); ?>
