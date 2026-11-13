
<?php

// koneksi ke database
require_once __DIR__ . "/connect.php";

// Matikan foreign key checks sementara untuk kemudahan proses pembuatan tabel
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0;");

$tables = [
    // 1. admins
    "admins" => "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // 2. albums
    "albums" => "CREATE TABLE IF NOT EXISTS albums (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        release_year INT NOT NULL,
        cover_image VARCHAR(255) NOT NULL,
        deskripsi TEXT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // 3. tracks
    "tracks" => "CREATE TABLE IF NOT EXISTS tracks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        album_id INT NOT NULL,
        title VARCHAR(150) NOT NULL,
        duration VARCHAR(10) NOT NULL,
        audio_url VARCHAR(255) NOT NULL,
        lyrics TEXT NULL,
        chord TEXT NULL,
        is_now_playing BOOLEAN DEFAULT FALSE,
        CONSTRAINT fk_tracks_albums FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // 4. tour_dates
    "tour_dates" => "CREATE TABLE IF NOT EXISTS tour_dates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_name VARCHAR(150) NOT NULL,
        city VARCHAR(100) NOT NULL,
        venue VARCHAR(150) NOT NULL,
        event_date DATE NOT NULL,
        event_time VARCHAR(50) NOT NULL,
        ticket_url VARCHAR(255) NULL,
        status ENUM('available','sold_out','presale') DEFAULT 'available'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // 5. products
    "products" => "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category ENUM('kaos','hoodie','aksesoris','poster') NOT NULL,
        name VARCHAR(200) NOT NULL,
        price DECIMAL(12,2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        image_url VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // 6. orders
    "orders" => "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_code VARCHAR(20) NOT NULL UNIQUE,
        customer_name VARCHAR(150) NOT NULL,
        whatsapp VARCHAR(25) NOT NULL,
        address TEXT NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        total_amount DECIMAL(12,2) NOT NULL,
        status ENUM('pending','paid','shipped','cancelled') DEFAULT 'pending',
        payment_status ENUM('pending','paid','refunded') NOT NULL DEFAULT 'pending',
        shipping_status ENUM('pending','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
        paid_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_orders_payment_status_paid_at (payment_status, paid_at),
        INDEX idx_orders_shipping_status (shipping_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // 7. order_items
    "order_items" => "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        CONSTRAINT fk_order_items_orders FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_order_items_products FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // 8. subscribers
    "subscribers" => "CREATE TABLE IF NOT EXISTS subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(150) NOT NULL UNIQUE,
        subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // 9. contact_messages
    "contact_messages" => "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // 10. articles
    "articles" => "CREATE TABLE IF NOT EXISTS articles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL DEFAULT 'News',
        thumbnail_url VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // 11. gallery
    "gallery" => "CREATE TABLE IF NOT EXISTS gallery (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category VARCHAR(50) NOT NULL,
        title VARCHAR(150) NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        caption VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

echo "<h2>Status Pembuatan Tabel Database: <code>$dbname</code></h2>";
echo "<ol>";

$successCount = 0;
foreach ($tables as $name => $query) {
    if (mysqli_query($conn, $query)) {
        echo "<li style='color: green;'>Tabel <strong>$name</strong> berhasil dibuat / sudah ada.</li>";
        $successCount++;
    } else {
        echo "<li style='color: red;'>Gagal membuat tabel <strong>$name</strong>: " . mysqli_error($conn) . "</li>";
    }
}

echo "</ol>";

// Nyalakan kembali foreign key checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1;");

if ($successCount === count($tables)) {
    echo "<p style='color: green; font-weight: bold;'>Seluruh 11 tabel berhasil dibuat dengan sukses!</p>";
    echo "<p><a href='insertdata.php'>Lanjut: Masukkan Data Awal (insertdata.php) &raquo;</a></p>";
}

// close connection
mysqli_close($conn);
?>
