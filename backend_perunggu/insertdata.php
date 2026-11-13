<?php

// koneksi ke database
require_once __DIR__ . "/connect.php";

mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0;");

echo "<h2>Inisialisasi Data Awal (Seed Data) Database: <code>$dbname</code></h2>";
echo "<ul>";

// Akun admin pertama dibuat satu kali melalui halaman login.
$checkAdmin = mysqli_query($conn, "SELECT id FROM admins LIMIT 1");
echo mysqli_num_rows($checkAdmin) > 0
    ? "<li style='color: #888;'>Akun administrator sudah tersedia.</li>"
    : "<li style='color: #888;'>Buat akun administrator pertama melalui halaman login.</li>";

// 2. albums
$checkAlbums = mysqli_query($conn, "SELECT id FROM albums LIMIT 1");
if (mysqli_num_rows($checkAlbums) == 0) {
    $sqlAlbums = "INSERT INTO albums (id, title, release_year, cover_image, deskripsi) VALUES
    (1, 'Memorandum', 2022, 'images/albums/memorandum.jpeg', 'Album perdana penuh narasi tentang dinamika kehidupan pekerja perkotaan, keluarga, dan pencarian makna hidup sehari-hari.'),
    (2, 'Dalam Dinamika', 2025, 'images/albums/dalam-dinamika.jpeg', 'Rilisan eksploratif yang membawa warna rock kontemporer yang sarat riff bertenaga serta lirik mendalam.'),
    (3, 'Pasti Ada Jalan', 2025, 'images/albums/pasti-ada-jalan.jpeg', 'Single pengantar penuh optimisme dan pesan keteguhan bagi siapa pun yang sedang berjuang.'),
    (4, 'Biang Lara', 2021, 'images/albums/biang-lara.jpeg', 'Lagu emosional tentang penerimaan rasa kecewa dan merelakan apa yang berada di luar kendali diri.'),
    (5, 'Tarung Bebas', 2021, 'images/albums/tarung-bebas.jpeg', 'Langkah awal perjalanan musik Perunggu yang lugas, energik, dan penuh hentakan distorsi.');";
    if (mysqli_query($conn, $sqlAlbums)) {
        echo "<li style='color: green;'>Data 5 album berhasil ditambahkan.</li>";
    }
} else {
    echo "<li style='color: #888;'>Data albums sudah ada.</li>";
}

// 3. tracks
$checkTracks = mysqli_query($conn, "SELECT id FROM tracks LIMIT 1");
if (mysqli_num_rows($checkTracks) == 0) {
    $sqlTracks = "INSERT INTO tracks (id, album_id, title, duration, audio_url, lyrics, chord, is_now_playing) VALUES
    (1, 1, '33x', '6:42', 'music/33x.mp3', '', '', 1),
    (2, 1, 'Pikiran Yang Matang', '4:15', 'music/pikiran-yang-matang.mp3', '', '', 0),
    (3, 1, 'Pastikan Riuh Akhiri Malammu', '5:20', 'music/pastikan-riuh-akhiri-malammu.mp3', '', '', 0),
    (4, 2, 'Kalibata , 2012', '4:45', 'music/kalibata-2012.mp3', '', '', 0);";
    if (mysqli_query($conn, $sqlTracks)) {
        echo "<li style='color: green;'>Data 4 lagu / tracks berhasil ditambahkan.</li>";
    }
} else {
    echo "<li style='color: #888;'>Data tracks sudah ada.</li>";
}

// 4. tour_dates
$checkTours = mysqli_query($conn, "SELECT id FROM tour_dates LIMIT 1");
if (mysqli_num_rows($checkTours) == 0) {
    $sqlTours = "INSERT INTO tour_dates (id, event_name, city, venue, event_date, event_time, ticket_url, status) VALUES
    (1, 'Pertunjukan Dalam Dinamika Jakarta', 'Jakarta', 'GBK Arena', '2026-05-12', '19:00 WIB', 'https://tiket.com/perunggu-jkt', 'available'),
    (2, 'Pertunjukan Dalam Dinamika Bandung', 'Bandung', 'Sasana Budaya', '2026-05-18', '20:00 WIB', 'https://tiket.com/perunggu-bdg', 'sold_out'),
    (3, 'Pertunjukan Dalam Dinamika Yogyakarta', 'Yogyakarta', 'Jogja Expo Center', '2026-05-25', '19:30 WIB', 'https://tiket.com/perunggu-jogja', 'presale'),
    (4, 'Pertunjukan Dalam Dinamika Surabaya', 'Surabaya', 'Convention Hall', '2026-05-30', '19:30 WIB', 'https://tiket.com/perunggu-sby', 'available');";
    if (mysqli_query($conn, $sqlTours)) {
        echo "<li style='color: green;'>Data jadwal konser / tour_dates berhasil ditambahkan.</li>";
    }
} else {
    echo "<li style='color: #888;'>Data tour_dates sudah ada.</li>";
}

// 5. products
$checkProducts = mysqli_query($conn, "SELECT id FROM products LIMIT 1");
if (mysqli_num_rows($checkProducts) == 0) {
    $sqlProducts = "INSERT INTO products (id, category, name, price, stock, image_url) VALUES
    (1, 'kaos', 'Kaos Pertunjukan dalam Dinamika Yogyakarta', 200000.00, 25, 'images/merch/kaos-jogja.png'),
    (2, 'kaos', 'Kaos Pertunjukan dalam Dinamika Surabaya', 200000.00, 18, 'images/merch/kaos-surabaya.png'),
    (3, 'kaos', 'Kaos Pertunjukan dalam Dinamika Bandung', 200000.00, 0, 'images/merch/kaos-bandung.png'),
    (4, 'hoodie', 'Hoodie Semua Nama Ayah', 450000.00, 12, 'images/merch/hoodie.png'),
    (5, 'aksesoris', 'Topi Perunggu', 75000.00, 30, 'images/merch/topi.png'),
    (6, 'aksesoris', 'Gantungan Kunci', 50000.00, 50, 'images/merch/keychain.png'),
    (7, 'aksesoris', 'CD Album Dalam Dinamika', 120000.00, 40, 'images/merch/cd.png'),
    (8, 'aksesoris', 'Tote Bag Perunggu', 85000.00, 20, 'images/merch/totebag.png');";
    if (mysqli_query($conn, $sqlProducts)) {
        echo "<li style='color: green;'>Data produk merchandise berhasil ditambahkan.</li>";
    }
} else {
    echo "<li style='color: #888;'>Data products sudah ada.</li>";
}

// 6. orders & 7. order_items
$checkOrders = mysqli_query($conn, "SELECT id FROM orders LIMIT 1");
if (mysqli_num_rows($checkOrders) == 0) {
    $sqlOrders = "INSERT INTO orders (id, order_code, customer_name, whatsapp, address, payment_method, total_amount, status, payment_status, shipping_status, paid_at) VALUES
    (1, 'PRG-102934', 'Demo Customer 1', '0800000001', 'Sample address 1, Indonesia', 'QRIS', 275000.00, 'paid', 'paid', 'pending', CURRENT_TIMESTAMP),
    (2, 'PRG-102935', 'Demo Customer 2', '0800000002', 'Sample address 2, Indonesia', 'Transfer BCA', 450000.00, 'pending', 'pending', 'pending', NULL);";
    mysqli_query($conn, $sqlOrders);

    $sqlOrderItems = "INSERT INTO order_items (order_id, product_id, quantity) VALUES
    (1, 1, 1),
    (1, 5, 1),
    (2, 4, 1);";
    mysqli_query($conn, $sqlOrderItems);
    echo "<li style='color: green;'>Data pesanan (orders & order_items) berhasil ditambahkan.</li>";
} else {
    echo "<li style='color: #888;'>Data orders & order_items sudah ada.</li>";
}

// 8. subscribers
$checkSubscribers = mysqli_query($conn, "SELECT id FROM subscribers LIMIT 1");
if (mysqli_num_rows($checkSubscribers) == 0) {
    $sqlSubs = "INSERT INTO subscribers (email) VALUES
    ('subscriber1@example.invalid'),
    ('subscriber2@example.invalid'),
    ('subscriber3@example.invalid');";
    if (mysqli_query($conn, $sqlSubs)) {
        echo "<li style='color: green;'>Data pelanggan newsletter (subscribers) berhasil ditambahkan.</li>";
    }
} else {
    echo "<li style='color: #888;'>Data subscribers sudah ada.</li>";
}

// 9. contact_messages
$checkMsg = mysqli_query($conn, "SELECT id FROM contact_messages LIMIT 1");
if (mysqli_num_rows($checkMsg) == 0) {
    $sqlMsg = "INSERT INTO contact_messages (name, email, subject, message, is_read) VALUES
    ('Demo Visitor 1', 'visitor1@example.invalid', 'Sample inquiry', 'Sample contact message for local development.', 1),
    ('Demo Visitor 2', 'visitor2@example.invalid', 'Sample product question', 'Sample contact message for local development.', 0);";
    if (mysqli_query($conn, $sqlMsg)) {
        echo "<li style='color: green;'>Data pesan kontak (contact_messages) berhasil ditambahkan.</li>";
    }
} else {
    echo "<li style='color: #888;'>Data contact_messages sudah ada.</li>";
}

// 10. articles
$checkArticles = mysqli_query($conn, "SELECT id FROM articles LIMIT 1");
if (mysqli_num_rows($checkArticles) == 0) {
    $sqlArticles = "INSERT INTO articles (title, category, thumbnail_url, content, published_at) VALUES
    ('Perunggu Releases New Single', 'Music', 'images/news1.jpeg', 'Perunggu officially releases their newest single titled \"Dalam Bayang\" and receives overwhelmingly positive responses from listeners across all platforms.', '2026-05-15 10:00:00'),
    ('National Tour Starts Soon', 'Tour', 'images/news2.jpeg', 'Perunggu announces their biggest national tour yet, visiting several major cities across Indonesia.', '2026-05-10 14:30:00'),
    ('Behind The Scenes Recording', 'Behind The Scene', 'images/news3.jpeg', 'Fans can now watch exclusive behind-the-scenes footage from the latest recording sessions.', '2026-05-05 09:15:00');";
    if (mysqli_query($conn, $sqlArticles)) {
        echo "<li style='color: green;'>Data artikel / berita (articles) berhasil ditambahkan.</li>";
    }
} else {
    echo "<li style='color: #888;'>Data articles sudah ada.</li>";
}

// 11. gallery
$checkGallery = mysqli_query($conn, "SELECT id FROM gallery LIMIT 1");
if (mysqli_num_rows($checkGallery) == 0) {
    $sqlGallery = "INSERT INTO gallery (category, title, image_url, caption) VALUES
    ('Behind The Scene', 'Soundcheck & Briefing', 'images/concert/bts1.png', 'Persiapan teknis sebelum panggung dibuka'),
    ('Behind The Scene', 'Backstage Warmup', 'images/concert/bts2.png', 'Fokus dan pemanasan jelang pertunjukan'),
    ('Behind The Scene', 'Peralatan & Gear', 'images/concert/bts3.png', 'Instrumen siap melantunkan nada'),
    ('Behind The Scene', 'Diskusi Personel', 'images/concert/bts4.png', 'Menyelaraskan energi antar personil'),
    ('Behind The Scene', 'Detik Jelang Panggung', 'images/concert/bts5.png', 'Sensasi riuh sebelum gemuruh lagu pertama');";
    if (mysqli_query($conn, $sqlGallery)) {
        echo "<li style='color: green;'>Data galeri foto (gallery) berhasil ditambahkan.</li>";
    }
} else {
    echo "<li style='color: #888;'>Data gallery sudah ada.</li>";
}

echo "</ul>";

mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1;");

echo "<p style='color: green; font-weight: bold;'>Data awal selesai diproses!</p>";
echo "<p><a href='bacadata.php'>Lihat Data di Tabel (bacadata.php) &raquo;</a></p>";

mysqli_close($conn);
?>

