-- ========================================================
-- IMPLEMENTASI BASIS DATA RESMI BAND PERUNGGU (perunggu_db)
-- Sesuai Dokumen Perancangan Basis Data (rancangan_basis_data.md)
-- Server: MySQL / MariaDB (Laragon / phpMyAdmin)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `perunggu_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `perunggu_db`;

-- Matikan foreign key checks sementara untuk kelancaran pembuatan/reset tabel
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. Tabel: admins
-- Deskripsi: Data kredensial login administrator dashboard
-- Relasi: Entitas Mandiri (Master Autentikasi)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Tabel: albums
-- Deskripsi: Data album & rilisan musik resmi grup band PERUNGGU
-- Relasi: Parent dari tabel tracks (1 : N)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `albums`;
CREATE TABLE `albums` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `release_year` INT NOT NULL,
    `cover_image` VARCHAR(255) NOT NULL,
    `deskripsi` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Tabel: tracks
-- Deskripsi: Data pemutar musik (audio mp3, durasi, lirik, & chord)
-- Relasi: Child dari tabel albums (album_id -> albums.id)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tracks`;
CREATE TABLE `tracks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `album_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `duration` VARCHAR(10) NOT NULL,
    `audio_url` VARCHAR(255) NOT NULL,
    `lyrics` TEXT NULL,
    `chord` TEXT NULL,
    `is_now_playing` BOOLEAN DEFAULT FALSE,
    INDEX `idx_tracks_album_id` (`album_id`),
    CONSTRAINT `fk_tracks_albums` 
        FOREIGN KEY (`album_id`) 
        REFERENCES `albums` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Tabel: tour_dates
-- Deskripsi: Data jadwal pertunjukan panggung, festival, dan konser
-- Relasi: Entitas Mandiri
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tour_dates`;
CREATE TABLE `tour_dates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_name` VARCHAR(150) NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `venue` VARCHAR(150) NOT NULL,
    `event_date` DATE NOT NULL,
    `event_time` VARCHAR(50) NOT NULL,
    `ticket_url` VARCHAR(255) NULL,
    `status` ENUM('available', 'sold_out', 'presale') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Tabel: products
-- Deskripsi: Data katalog merchandise resmi band dan kuantitas stok
-- Relasi: Parent referensi untuk order_items (1 : N)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category` ENUM('kaos', 'hoodie', 'aksesoris', 'poster') NOT NULL,
    `name` VARCHAR(200) NOT NULL,
    `price` DECIMAL(12,2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `image_url` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Tabel: orders
-- Deskripsi: Data induk transaksi pemesanan merchandise oleh fans
-- Relasi: Parent dari tabel order_items (1 : N)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_code` VARCHAR(20) NOT NULL UNIQUE,
    `customer_name` VARCHAR(150) NOT NULL,
    `whatsapp` VARCHAR(25) NOT NULL,
    `address` TEXT NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL,
    `total_amount` DECIMAL(12,2) NOT NULL,
    `status` ENUM('pending', 'paid', 'shipped', 'cancelled') DEFAULT 'pending',
    `payment_status` ENUM('pending', 'paid', 'refunded') NOT NULL DEFAULT 'pending',
    `shipping_status` ENUM('pending', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    `paid_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_orders_payment_status_paid_at` (`payment_status`, `paid_at`),
    INDEX `idx_orders_shipping_status` (`shipping_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Tabel: order_items
-- Deskripsi: Rincian item barang yang dibeli per transaksi
-- Relasi: 
--   - Child dari orders (order_id -> orders.id) [ON DELETE CASCADE]
--   - Child dari products (product_id -> products.id) [ON DELETE RESTRICT]
-- --------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    INDEX `idx_order_items_order_id` (`order_id`),
    INDEX `idx_order_items_product_id` (`product_id`),
    CONSTRAINT `fk_order_items_orders` 
        FOREIGN KEY (`order_id`) 
        REFERENCES `orders` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_order_items_products` 
        FOREIGN KEY (`product_id`) 
        REFERENCES `products` (`id`) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 8. Tabel: subscribers
-- Deskripsi: Data email fans terdaftar untuk newsletter
-- Relasi: Entitas Mandiri
-- --------------------------------------------------------
DROP TABLE IF EXISTS `subscribers`;
CREATE TABLE `subscribers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 9. Tabel: contact_messages
-- Deskripsi: Pesan, pertanyaan, dan tawaran kerjasama dari formulir kontak
-- Relasi: Entitas Mandiri
-- --------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 10. Tabel: articles
-- Deskripsi: Data artikel berita resmi, siaran pers, dan dokumentasi band
-- Relasi: Entitas Mandiri
-- --------------------------------------------------------
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `category` ENUM('Music', 'Tour', 'Behind The Scene', 'Merchandise', 'News') NOT NULL DEFAULT 'News',
    `thumbnail_url` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `published_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 11. Tabel: gallery
-- Deskripsi: Dokumentasi foto konser, behind the scene, dan personel band
-- Relasi: Entitas Mandiri
-- --------------------------------------------------------
DROP TABLE IF EXISTS `gallery`;
CREATE TABLE `gallery` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category` VARCHAR(50) NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- DATA AWAL / SEED DATA (SESUAI DOKUMEN & WEBSITE)
-- ========================================================

-- Akun admin pertama dibuat melalui halaman login setelah database diinisialisasi.

-- 2. Data albums
INSERT INTO `albums` (`id`, `title`, `release_year`, `cover_image`, `deskripsi`) VALUES
(1, 'Memorandum', 2022, 'images/albums/memorandum.jpeg', 'Album perdana penuh narasi tentang dinamika kehidupan pekerja perkotaan, keluarga, dan pencarian makna hidup sehari-hari.'),
(2, 'Dalam Dinamika', 2025, 'images/albums/dalam-dinamika.jpeg', 'Rilisan eksploratif yang membawa warna rock kontemporer yang sarat riff bertenaga serta lirik mendalam.');

-- 3. Data tracks
INSERT INTO `tracks` (`id`, `album_id`, `title`, `duration`, `audio_url`, `lyrics`, `chord`, `is_now_playing`) VALUES
(1, 1, '33x', '6:42', 'music/33x.mp3', '', '', 1),
(2, 1, 'Pikiran Yang Matang', '4:15', 'music/pikiran-yang-matang.mp3', '', '', 0),
(3, 1, 'Pastikan Riuh Akhiri Malammu', '5:20', 'music/pastikan-riuh-akhiri-malammu.mp3', '', '', 0),
(4, 2, 'Kalibata , 2012', '4:45', 'music/kalibata-2012.mp3', '', '', 0);

-- 4. Data tour_dates
INSERT INTO `tour_dates` (`id`, `event_name`, `city`, `venue`, `event_date`, `event_time`, `ticket_url`, `status`) VALUES
(1, 'Pertunjukan Dalam Dinamika Jakarta', 'Jakarta', 'GBK Arena', '2026-05-12', '19:00 WIB', 'https://tiket.com/perunggu-jkt', 'available'),
(2, 'Pertunjukan Dalam Dinamika Bandung', 'Bandung', 'Sasana Budaya', '2026-05-18', '20:00 WIB', 'https://tiket.com/perunggu-bdg', 'sold_out'),
(3, 'Pertunjukan Dalam Dinamika Yogyakarta', 'Yogyakarta', 'Jogja Expo Center', '2026-05-25', '19:30 WIB', 'https://tiket.com/perunggu-jogja', 'presale'),
(4, 'Pertunjukan Dalam Dinamika Surabaya', 'Surabaya', 'Convention Hall', '2026-05-30', '19:30 WIB', 'https://tiket.com/perunggu-sby', 'available'),
(5, 'Pertunjukan Dalam Dinamika Semarang', 'Semarang', 'TBRS Semarang', '2026-06-05', '19:30 WIB', 'https://tiket.com/perunggu-smg', 'available'),
(6, 'Pertunjukan Dalam Dinamika Malang', 'Malang', 'Graha Cakrawala', '2026-06-12', '20:00 WIB', 'https://tiket.com/perunggu-mlg', 'available'),
(7, 'Pertunjukan Dalam Dinamika Bali', 'Bali', 'Art Center Denpasar', '2026-06-20', '19:00 WIB', 'https://tiket.com/perunggu-bali', 'available'),
(8, 'Pertunjukan Dalam Dinamika Medan', 'Medan', 'Tiara Convention Hall', '2026-06-27', '19:30 WIB', 'https://tiket.com/perunggu-medan', 'available'),
(9, 'Pertunjukan Dalam Dinamika Makassar', 'Makassar', 'Celebes Convention Center', '2026-07-04', '20:00 WIB', 'https://tiket.com/perunggu-mks', 'available'),
(10, 'Pertunjukan Dalam Dinamika Palembang', 'Palembang', 'PSCC Palembang', '2026-07-11', '19:30 WIB', 'https://tiket.com/perunggu-plb', 'available'),
(11, 'Pertunjukan Dalam Dinamika Solo', 'Solo', 'De Tjolomadoe', '2026-07-18', '20:00 WIB', 'https://tiket.com/perunggu-solo', 'available');

-- 5. Data products
INSERT INTO `products` (`id`, `category`, `name`, `price`, `stock`, `image_url`) VALUES
(1, 'kaos', 'Kaos Pertunjukan dalam Dinamika Yogyakarta', 200000.00, 25, 'images/merch/yogya.png'),
(2, 'kaos', 'Kaos Pertunjukan dalam Dinamika Surabaya', 200000.00, 18, 'images/merch/surabaya.png'),
(3, 'kaos', 'Kaos Pertunjukan dalam Dinamika Bandung', 200000.00, 0, 'images/merch/bandung.png'),
(4, 'hoodie', 'Hoodie Semua Nama Ayah', 450000.00, 12, 'images/merch/semua-nama-ayah.png'),
(5, 'aksesoris', 'Topi Perunggu', 75000.00, 30, 'images/merch/topi.png'),
(6, 'aksesoris', 'Gantungan Kunci', 50000.00, 50, 'images/merch/gantungan-kunci.png'),
(7, 'aksesoris', 'CD Album Dalam Dinamika', 120000.00, 40, 'images/merch/cd.png'),
(8, 'aksesoris', 'Tote Bag Perunggu', 85000.00, 20, 'images/merch/TOTEBAG.png');

-- 6. Data orders
INSERT INTO `orders` (`id`, `order_code`, `customer_name`, `whatsapp`, `address`, `payment_method`, `total_amount`, `status`, `payment_status`, `shipping_status`, `paid_at`) VALUES
(1, 'PRG-102934', 'Demo Customer 1', '0800000001', 'Sample address 1, Indonesia', 'QRIS', 275000.00, 'paid', 'paid', 'pending', CURRENT_TIMESTAMP),
(2, 'PRG-102935', 'Demo Customer 2', '0800000002', 'Sample address 2, Indonesia', 'Transfer BCA', 450000.00, 'pending', 'pending', 'pending', NULL);

-- 7. Data order_items
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`) VALUES
(1, 1, 1, 1),
(2, 1, 5, 1),
(3, 2, 4, 1);

-- 8. Data subscribers
INSERT INTO `subscribers` (`id`, `email`) VALUES
(1, 'subscriber1@example.invalid'),
(2, 'subscriber2@example.invalid'),
(3, 'subscriber3@example.invalid');

-- 9. Data contact_messages
INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `is_read`) VALUES
(1, 'Demo Visitor 1', 'visitor1@example.invalid', 'Sample inquiry', 'Sample contact message for local development.', 1),
(2, 'Demo Visitor 2', 'visitor2@example.invalid', 'Sample product question', 'Sample contact message for local development.', 0);

-- 10. Data articles
INSERT INTO `articles` (`id`, `title`, `category`, `thumbnail_url`, `content`, `published_at`) VALUES
(1, 'Perunggu Releases New Single', 'Music', 'images/news1.jpeg', 'Perunggu officially releases their newest single titled "Dalam Bayang" and receives overwhelmingly positive responses from listeners across all platforms. The single showcases a new musical direction while maintaining their signature sound.', '2026-05-15 10:00:00'),
(2, 'National Tour Starts Soon', 'Tour', 'images/news2.jpeg', 'Perunggu announces their biggest national tour yet, visiting several major cities across Indonesia. The tour will kick off in Jakarta this May and continue through July, bringing their latest hits to fans nationwide.', '2026-05-10 14:30:00'),
(3, 'Behind The Scenes Recording', 'Behind The Scene', 'images/news3.jpeg', 'Fans can now watch exclusive behind-the-scenes footage from the latest recording sessions. Get an intimate look at the creative process and see how Perunggu brings their music to life in the studio.', '2026-05-05 09:15:00');

-- 11. Data gallery
INSERT INTO `gallery` (`id`, `category`, `title`, `image_url`, `caption`) VALUES
(1, 'Behind The Scene', 'Soundcheck & Briefing', 'images/concert/bts1.png', 'Persiapan teknis sebelum panggung dibuka'),
(2, 'Behind The Scene', 'Backstage Warmup', 'images/concert/bts2.png', 'Fokus dan pemanasan jelang pertunjukan'),
(3, 'Behind The Scene', 'Peralatan & Gear', 'images/concert/bts3.png', 'Instrumen siap melantunkan nada'),
(4, 'Behind The Scene', 'Diskusi Personel', 'images/concert/bts4.png', 'Menyelaraskan energi antar personil'),
(5, 'Behind The Scene', 'Detik Jelang Panggung', 'images/concert/bts5.png', 'Sensasi riuh sebelum gemuruh lagu pertama'),
(6, 'Members', 'Maulana Ibrahim', 'images/members/maul.png', 'Vokal & Gitar PERUNGGU'),
(7, 'Members', 'Adam Adenan', 'images/members/adam.png', 'Bass & Vokal Latar PERUNGGU'),
(8, 'Members', 'Ildo Reynardian Hasman', 'images/members/ildo.png', 'Drum PERUNGGU'),
(9, 'Concert', 'Riuh Pertunjukan 1', 'images/gallery/gallery1.png', 'Sorak penonton memenuhi venue pertunjukan'),
(10, 'Concert', 'Riuh Pertunjukan 2', 'images/gallery/gallery2.png', 'Hentakan distorsi menggelegar di panggung'),
(11, 'Concert', 'Riuh Pertunjukan 3', 'images/gallery/gallery3.png', 'Energi panggung bersama para kawan perunggu'),
(12, 'Concert', 'Riuh Pertunjukan 4', 'images/gallery/gallery4.png', 'Momen puncak penutupan konser');

-- Nyalakan kembali foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
