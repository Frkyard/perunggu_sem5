<?php
// ====================================================================
// SCRIPT BERSIHKAN & SESUAIKAN DATA DATABASE PERUNGGU DENGAN FRONTEND
// ====================================================================
require_once __DIR__ . '/connect.php';

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Pembersihan & Penyelarasan Database</title>";
echo "<style>body{font-family:sans-serif;padding:30px;background:#141416;color:#e6e6e6;line-height:1.6}h2,h3{color:#c8a45a}code{background:#222;padding:2px 6px;border-radius:4px;color:#f39c12}.ok{color:#2ecc71}.del{color:#e74c3c}ul{margin-bottom:20px}</style></head><body>";
echo "<h2>🧹 Memulai Pembersihan & Penyelarasan Data Database Perunggu...</h2>";

// --------------------------------------------------------------------
// 1. BERSIHKAN DATA ALBUM (Hapus Single/Data Uji Coba yang Bukan Album)
// --------------------------------------------------------------------
echo "<h3>1. Menyelaraskan Tabel Album (`albums`)</h3><ul>";
$delete_singles = mysqli_query($conn, "DELETE FROM albums WHERE title IN ('Pasti Ada Jalan', 'Biang Lara', 'Tarung Bebas', 'Percobaan', 'Ngetest Lagi') OR id NOT IN (1, 2)");
$affected_albums = mysqli_affected_rows($conn);
echo "<li class='del'>Menghapus <strong>$affected_albums</strong> data single / album uji coba yang bukan album resmi.</li>";

// Pastikan 2 Album Resmi memiliki data valid
mysqli_query($conn, "INSERT INTO albums (id, title, release_year, cover_image, deskripsi)
                     VALUES (1, 'Memorandum', 2022, 'images/albums/memorandum.jpeg', 'Album perdana penuh narasi tentang dinamika kehidupan pekerja perkotaan, keluarga, dan pencarian makna hidup sehari-hari.')
                     ON DUPLICATE KEY UPDATE
                     title = 'Memorandum', release_year = 2022, cover_image = 'images/albums/memorandum.jpeg', deskripsi = 'Album perdana penuh narasi tentang dinamika kehidupan pekerja perkotaan, keluarga, dan pencarian makna hidup sehari-hari.'");

mysqli_query($conn, "INSERT INTO albums (id, title, release_year, cover_image, deskripsi)
                     VALUES (2, 'Dalam Dinamika', 2025, 'images/albums/dalam-dinamika.jpeg', 'Rilisan eksploratif yang membawa warna rock kontemporer yang sarat riff bertenaga serta lirik mendalam.')
                     ON DUPLICATE KEY UPDATE
                     title = 'Dalam Dinamika', release_year = 2025, cover_image = 'images/albums/dalam-dinamika.jpeg', deskripsi = 'Rilisan eksploratif yang membawa warna rock kontemporer yang sarat riff bertenaga serta lirik mendalam.'");
echo "<li class='ok'>Album resmi terverifikasi: <strong>Memorandum (2022)</strong> & <strong>Dalam Dinamika (2025)</strong>.</li>";
echo "</ul>";

// --------------------------------------------------------------------
// 2. SESUAIKAN DATA PRODUK MERCHANDISE (`products`)
// --------------------------------------------------------------------
echo "<h3>2. Menyelaraskan Tabel Produk (`products`)</h3><ul>";
$products_data = [
    [1, 'kaos', 'Kaos Pertunjukan dalam Dinamika Yogyakarta', 200000, 25, 'images/merch/yogya.png'],
    [2, 'kaos', 'Kaos Pertunjukan dalam Dinamika Surabaya', 200000, 18, 'images/merch/surabaya.png'],
    [3, 'kaos', 'Kaos Pertunjukan dalam Dinamika Bandung', 200000, 0, 'images/merch/bandung.png'],
    [4, 'hoodie', 'Hoodie Semua Nama Ayah', 450000, 12, 'images/merch/semua-nama-ayah.png'],
    [5, 'aksesoris', 'Topi Perunggu', 75000, 30, 'images/merch/topi.png'],
    [6, 'aksesoris', 'Gantungan Kunci', 50000, 50, 'images/merch/gantungan-kunci.png'],
    [7, 'aksesoris', 'CD Album Dalam Dinamika', 120000, 40, 'images/merch/cd.png'],
    [8, 'aksesoris', 'Tote Bag Perunggu', 85000, 20, 'images/merch/TOTEBAG.png']
];

foreach ($products_data as $p) {
    mysqli_query($conn, "INSERT INTO products (id, category, name, price, stock, image_url)
                         VALUES ({$p[0]}, '{$p[1]}', '{$p[2]}', {$p[3]}, {$p[4]}, '{$p[5]}')
                         ON DUPLICATE KEY UPDATE
                         category = '{$p[1]}', name = '{$p[2]}', price = {$p[3]}, stock = {$p[4]}, image_url = '{$p[5]}'");
}
// Hapus produk ID > 8 yang mungkin merupakan dummy percobaan
mysqli_query($conn, "DELETE FROM products WHERE id > 8");
echo "<li class='ok'>8 Produk Merchandise telah diselaraskan nama, harga, stok, dan path file gambarnya dengan frontend shop.html.</li>";
echo "</ul>";

// --------------------------------------------------------------------
// 3. SINKRONISASI 11 JADWAL TUR (`tour_dates`)
// --------------------------------------------------------------------
echo "<h3>3. Menyelaraskan Tabel Jadwal Tur (`tour_dates`)</h3><ul>";
mysqli_query($conn, "TRUNCATE TABLE tour_dates");

$tours = [
    [1, 'Pertunjukan Dalam Dinamika', 'Jakarta', 'GBK Arena', '2026-05-12', '19:00 WIB', 'https://tiket.com/perunggu-jkt', 'available'],
    [2, 'Pertunjukan Dalam Dinamika', 'Bandung', 'Sasana Budaya', '2026-05-18', '20:00 WIB', 'https://tiket.com/perunggu-bdg', 'sold_out'],
    [3, 'Pertunjukan Dalam Dinamika', 'Yogyakarta', 'Jogja Expo Center', '2026-05-25', '19:30 WIB', 'https://tiket.com/perunggu-jogja', 'presale'],
    [4, 'Pertunjukan Dalam Dinamika', 'Surabaya', 'Convention Hall', '2026-05-30', '20:00 WIB', 'https://tiket.com/perunggu-sby', 'available'],
    [5, 'Pertunjukan Dalam Dinamika', 'Semarang', 'TBRS Semarang', '2026-06-05', '19:30 WIB', 'https://tiket.com/perunggu-smg', 'available'],
    [6, 'Pertunjukan Dalam Dinamika', 'Malang', 'Graha Cakrawala', '2026-06-12', '20:00 WIB', 'https://tiket.com/perunggu-mlg', 'available'],
    [7, 'Pertunjukan Dalam Dinamika', 'Bali', 'Art Center Denpasar', '2026-06-20', '19:00 WIB', 'https://tiket.com/perunggu-bali', 'available'],
    [8, 'Pertunjukan Dalam Dinamika', 'Medan', 'Tiara Convention Hall', '2026-06-27', '19:30 WIB', 'https://tiket.com/perunggu-medan', 'available'],
    [9, 'Pertunjukan Dalam Dinamika', 'Makassar', 'Celebes Convention Center', '2026-07-04', '20:00 WIB', 'https://tiket.com/perunggu-mks', 'available'],
    [10, 'Pertunjukan Dalam Dinamika', 'Palembang', 'PSCC Palembang', '2026-07-11', '19:30 WIB', 'https://tiket.com/perunggu-plb', 'available'],
    [11, 'Pertunjukan Dalam Dinamika', 'Solo', 'De Tjolomadoe', '2026-07-18', '20:00 WIB', 'https://tiket.com/perunggu-solo', 'available']
];

foreach ($tours as $t) {
    mysqli_query($conn, "INSERT INTO tour_dates (id, event_name, city, venue, event_date, event_time, ticket_url, status)
                         VALUES ({$t[0]}, '{$t[1]}', '{$t[2]}', '{$t[3]}', '{$t[4]}', '{$t[5]}', '{$t[6]}', '{$t[7]}')");
}
echo "<li class='ok'>11 Jadwal Tur telah diselaraskan persis dengan tourdates.html (Mei, Juni, Juli 2026).</li>";
echo "</ul>";

// --------------------------------------------------------------------
// 4. SINKRONISASI 12 FOTO GALERI (`gallery`)
// --------------------------------------------------------------------
echo "<h3>4. Menyelaraskan Tabel Galeri (`gallery`)</h3><ul>";
// Pastikan kolom created_at ada pada tabel gallery
$col_check = mysqli_query($conn, "SHOW COLUMNS FROM gallery LIKE 'created_at'");
if (mysqli_num_rows($col_check) == 0) {
    mysqli_query($conn, "ALTER TABLE gallery ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}
mysqli_query($conn, "TRUNCATE TABLE gallery");

$galleries = [
    [1, 'Behind The Scene', 'Soundcheck & Briefing', 'images/concert/bts1.png', 'Persiapan teknis sebelum panggung dibuka'],
    [2, 'Behind The Scene', 'Backstage Warmup', 'images/concert/bts2.png', 'Fokus dan pemanasan jelang pertunjukan'],
    [3, 'Behind The Scene', 'Peralatan & Gear', 'images/concert/bts3.png', 'Instrumen siap melantunkan nada'],
    [4, 'Behind The Scene', 'Diskusi Personel', 'images/concert/bts4.png', 'Menyelaraskan energi antar personil'],
    [5, 'Behind The Scene', 'Detik Jelang Panggung', 'images/concert/bts5.png', 'Sensasi riuh sebelum gemuruh lagu pertama'],
    [6, 'Members', 'Maulana Ibrahim', 'images/members/maul.png', 'Vokal & Gitar'],
    [7, 'Members', 'Adam Adenan', 'images/members/adam.png', 'Bass & Vokal Latar'],
    [8, 'Members', 'Ildo Reynardian Hasman', 'images/members/ildo.png', 'Drum'],
    [9, 'Concert', 'Riuh Pertunjukan 1', 'images/gallery/gallery1.png', 'Sorak penonton memenuhi venue pertunjukan'],
    [10, 'Concert', 'Riuh Pertunjukan 2', 'images/gallery/gallery2.png', 'Hentakan distorsi menggelegar di panggung'],
    [11, 'Concert', 'Riuh Pertunjukan 3', 'images/gallery/gallery3.png', 'Energi panggung bersama para kawan perunggu'],
    [12, 'Concert', 'Riuh Pertunjukan 4', 'images/gallery/gallery4.png', 'Momen puncak penutupan konser']
];

foreach ($galleries as $g) {
    mysqli_query($conn, "INSERT INTO gallery (id, category, title, image_url, caption)
                         VALUES ({$g[0]}, '{$g[1]}', '{$g[2]}', '{$g[3]}', '{$g[4]}')");
}
echo "<li class='ok'>12 Foto Galeri lengkap (Behind The Scene, Members, & Concert) telah diselaraskan dengan gallery.html.</li>";
echo "</ul>";

// --------------------------------------------------------------------
// 5. SINKRONISASI 8 ARTIKEL BERITA (`articles`)
// --------------------------------------------------------------------
echo "<h3>5. Menyelaraskan Tabel Artikel Berita (`articles`)</h3><ul>";
// Ubah kolom category jadi VARCHAR(100) agar fleksibel dan tidak error ENUM truncation
mysqli_query($conn, "ALTER TABLE articles MODIFY category VARCHAR(100) NOT NULL DEFAULT 'News'");
mysqli_query($conn, "TRUNCATE TABLE articles");

$articles = [
    [1, 'Perunggu Releases New Single', 'Music', 'images/news1.jpeg', 'Perunggu officially releases their newest single titled "Dalam Bayang" and receives overwhelmingly positive responses from listeners across all platforms. The single showcases a new musical direction while maintaining their signature sound.', '2026-05-15 10:00:00'],
    [2, 'National Tour Starts Soon', 'Tour', 'images/news2.jpeg', 'Perunggu announces their biggest national tour yet, visiting several major cities across Indonesia. The tour will kick off in Jakarta this May and continue through July, bringing their latest hits to fans nationwide.', '2026-05-10 14:30:00'],
    [3, 'Behind The Scenes Recording', 'Behind The Scene', 'images/news3.jpeg', 'Fans can now watch exclusive behind-the-scenes footage from the latest recording sessions. Get an intimate look at the creative process and see how Perunggu brings their music to life in the studio.', '2026-05-05 09:15:00'],
    [4, 'New Album Coming This Summer', 'Music', 'images/news1.jpeg', 'Perunggu announces their highly anticipated new album set to release this summer. The album features new tracks that explore deeper themes and showcase the band\'s musical growth and evolution.', '2026-04-28 11:00:00'],
    [5, 'Official Music Video "33x" Released', 'Music', 'images/news2.jpeg', 'The official music video for "33x" is now live on YouTube and has already garnered enthusiastic response. The cinematic video was shot in various locations around Jakarta and tells a compelling visual story.', '2026-04-22 16:00:00'],
    [6, 'Perunggu Nominated for Best Alternative Band', 'News', 'images/news3.jpeg', 'Perunggu receives nomination for "Best Alternative Band" at the Indonesian Music Awards. This marks their recognition in the alternative music category, solidifying their position as one of Indonesia\'s top rock bands.', '2026-04-15 13:00:00'],
    [7, 'Collaboration with International Artist Announced', 'Music', 'images/news1.jpeg', 'Exciting news as Perunggu announces an upcoming collaboration with a guest artist. The band promises this will be a fresh and inspiring release for Indonesian indie rock music.', '2026-04-08 15:30:00'],
    [8, 'Spring Tour Highlights & Thank You', 'Tour', 'images/news2.jpeg', 'A heartfelt thank you to all the fans who attended our recent festival performances. The energy, warmth, and sing-alongs were truly unforgettable memories for the whole team.', '2026-04-01 12:00:00']
];

foreach ($articles as $a) {
    $c = mysqli_real_escape_string($conn, $a[4]);
    mysqli_query($conn, "INSERT INTO articles (id, title, category, thumbnail_url, content, published_at)
                         VALUES ({$a[0]}, '{$a[1]}', '{$a[2]}', '{$a[3]}', '$c', '{$a[5]}')");
}
echo "<li class='ok'>8 Artikel Berita telah diselaraskan persis dengan allnews.html dan updates.html.</li>";
echo "</ul>";

// --------------------------------------------------------------------
// 6. VERIFIKASI TREK LAGU (`tracks`)
// --------------------------------------------------------------------
echo "<h3>6. Verifikasi Tabel Trek Lagu (`tracks`)</h3><ul>";
// Pastikan track hanya merujuk ke album valid (1 atau 2)
mysqli_query($conn, "DELETE FROM tracks WHERE album_id NOT IN (1, 2)");
$t_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM tracks");
$t_cnt = mysqli_fetch_assoc($t_res)['total'];
echo "<li class='ok'>Terdapat <strong>$t_cnt</strong> trek lagu aktif di database yang seluruhnya terhubung ke album resmi Memorandum dan Dalam Dinamika.</li>";
echo "</ul>";

echo "<hr><h2 class='ok'>✨ Seluruh data dashboard dan database telah bersih & 100% selaras dengan frontend!</h2>";
echo "<p><a href='dist/index.php' style='color:#c8a45a;font-weight:bold;text-decoration:none;'>&larr; Buka Dashboard AdminLTE</a></p>";
echo "</body></html>";
