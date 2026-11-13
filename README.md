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
