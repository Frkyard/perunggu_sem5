# PERUNGGU Backend

Backend PHP dan MySQL untuk API publik serta dashboard administrasi PERUNGGU.

## Fitur

- Autentikasi administrator dan perlindungan aksi dashboard dengan CSRF.
- API katalog produk, musik, berita, galeri, jadwal tur, kontak, newsletter, dan pesanan.
- Checkout memvalidasi harga dan stok di server serta menyimpan transaksi secara atomik.
- Dashboard untuk mengelola konten, pesanan, status pembayaran, dan pengiriman.

## Persyaratan

- PHP 8.0 atau lebih baru dengan ekstensi `mysqli` dan `session`.
- MySQL 8 atau MariaDB.
- Apache (misalnya Laragon).

## Menjalankan secara lokal

1. Letakkan folder backend di document root Apache.
2. Buat database `perunggu_db`, lalu impor `database.sql` untuk instalasi baru. File ini menjatuhkan dan membuat ulang tabel; jangan impor ke database yang berisi data yang ingin dipertahankan.
3. Secara default koneksi lokal memakai `localhost`, user `root`, password kosong, dan database `perunggu_db`. Untuk konfigurasi lain, atur variabel lingkungan:
   - `PERUNGGU_DB_HOST`
   - `PERUNGGU_DB_USER`
   - `PERUNGGU_DB_PASSWORD`
   - `PERUNGGU_DB_NAME`
   Jika nama database diubah, samakan nama pada perintah `CREATE DATABASE` dan `USE` di `database.sql`.
4. Buka `http://localhost/perunggu/backend_perunggu/login.php` (sesuaikan `perunggu` dengan nama folder proyek di document root). Jika tabel `admins` kosong, halaman login menampilkan pembuatan akun admin pertama. Jika sudah ada akun, halaman hanya menampilkan login; untuk menambah admin, masuk dengan akun yang ada lalu pilih menu **Akun Admin**. Pendaftaran admin publik dinonaktifkan.
6. Untuk database lama yang belum memiliki kolom terkait, jalankan migrasi SQL di folder `migrations` satu kali dan sesuai urutan berikut. Lewati migrasi yang sudah diterapkan:
   1. `add_paid_at_to_orders.sql`
   2. `split_order_payment_shipping_status.sql`

`insertdata.php` dapat dijalankan untuk mengisi contoh data pada tabel yang masih kosong. Contoh email menggunakan domain `.invalid`, dan contoh data tidak berisi informasi pelanggan nyata.

## API

Endpoint API tersedia di folder `api`. Endpoint pembacaan data pribadi pesanan, pesan kontak, dan subscriber memerlukan sesi administrator; endpoint publik lainnya digunakan oleh frontend.

## Catatan keamanan

- Nilai koneksi bawaan hanya untuk lingkungan Laragon lokal. Gunakan akun database khusus dan password kuat di lingkungan selain lokal.
- Jangan menaruh file `.env`, kredensial, data pelanggan, atau dump database produksi di repository.
- `database.sql` dan `insertdata.php` berisi data contoh untuk pengembangan, bukan data produksi.
