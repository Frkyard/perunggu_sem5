# PERUNGGU

Website PERUNGGU untuk memperkenalkan band dan karya musiknya, membagikan berita serta jadwal tur, dan menyediakan katalog merchandise dengan fitur keranjang dan checkout.

## Isi repository

- [`web_perunggu/`](web_perunggu/): halaman dan aset frontend website.
- [`backend_perunggu/`](backend_perunggu/): API PHP, database, dan dashboard administrasi.

## Fitur website

- Profil band dan informasi album.
- Pemutar musik, lirik, dan chord.
- Berita, galeri, dan jadwal tur.
- Katalog merchandise, keranjang belanja, dan checkout.
- Halaman kontak dan newsletter.
- Dashboard admin untuk mengelola konten serta pesanan.

## Menjalankan secara lokal

Gunakan Apache (misalnya Laragon), PHP 8 atau lebih baru, dan MySQL/MariaDB. Letakkan folder repository ini di document root web server agar `backend_perunggu` dan `web_perunggu` tetap berdampingan.

1. Ikuti petunjuk konfigurasi database dan backend di [`backend_perunggu/README.md`](backend_perunggu/README.md).
2. Jalankan Apache dan MySQL.
3. Buka frontend melalui `http://localhost/perunggu/web_perunggu/` (sesuaikan `perunggu` dengan nama folder proyek). Untuk mengakses dashboard admin, gunakan petunjuk URL pada README backend.

Frontend memakai endpoint PHP di folder `web_perunggu/api/` yang meneruskan permintaan ke backend. Karena itu, jalankan melalui web server dan siapkan backend agar fitur yang menggunakan data dinamis, seperti katalog dan checkout, berfungsi. Beberapa ikon dan peta dimuat dari CDN sehingga memerlukan koneksi internet.

Lihat [`web_perunggu/README.md`](web_perunggu/README.md) untuk informasi khusus frontend.

## Deploy full-stack melalui GitHub dan Railway

Repository ini menyertakan `Dockerfile` dan `railway.json` untuk menjalankan frontend serta backend PHP dalam satu layanan Railway. GitHub menjadi sumber deploy otomatis; GitHub Pages tidak dapat menjalankan PHP atau MySQL.

1. Di Railway, buat project melalui **Deploy from GitHub Repo** dan pilih repository ini. Railway akan membangun `Dockerfile` dari root repository.
2. Tambahkan layanan **MySQL** ke project yang sama.
3. Pada layanan aplikasi, tambahkan variabel berikut sebagai *reference variables* dari layanan MySQL (sesuaikan nama layanan jika bukan `MySQL`):
   - `PERUNGGU_DB_HOST` = `${{MySQL.MYSQLHOST}}`
   - `PERUNGGU_DB_USER` = `${{MySQL.MYSQLUSER}}`
   - `PERUNGGU_DB_PASSWORD` = `${{MySQL.MYSQLPASSWORD}}`
   - `PERUNGGU_DB_NAME` = `${{MySQL.MYSQLDATABASE}}`
4. Impor `backend_perunggu/database.sql` satu kali ke database MySQL Railway yang dipilih oleh `MYSQLDATABASE`. **Jangan jalankan ulang pada database berisi data penting:** skrip ini menghapus lalu membuat ulang tabel dan mengisi data contoh, termasuk contoh pesanan serta pesan kontak. Cadangkan data dan hapus data contoh sebelum menerima transaksi nyata.
5. Setelah deploy sehat, buat domain publik pada layanan aplikasi. Domain root akan mengarahkan ke `/web_perunggu/`; dashboard admin ada di `/backend_perunggu/login.php`. Buat admin pertama dengan password kuat.
6. Untuk mempertahankan foto unggahan admin setelah container diganti, pasang Railway Volume pada `/var/www/html/web_perunggu/images/uploads`. Unggahan audio saat ini disimpan langsung di `web_perunggu/music`; filesystem container biasa bersifat sementara, dan memasang volume di folder itu akan menutupi lagu bawaan. Jangan mengandalkan audio unggahan tetap ada setelah container diganti.

Deploy dari Railway memerlukan akses pemilik akun GitHub/Railway dan konfigurasi layanan tersebut. Jangan memasukkan kredensial database ke repository atau file Docker.
