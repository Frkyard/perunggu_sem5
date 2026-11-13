# PERUNGGU Frontend

Frontend website PERUNGGU berisi halaman profil band, musik, berita, jadwal tur, galeri, kontak, dan toko merchandise.

## Halaman dan aset

- Halaman utama dan profil: `index.html`, `about.html`, `albums.html`.
- Musik, lirik, dan chord: `music.html`, `lyrics/`, `chord/`.
- Berita dan acara: `updates.html`, `allnews.html`, `tourdates.html`, `gallery.html`.
- Toko dan kontak: `shop.html`, `contact.html`.
- Gambar dan audio: `images/`, `music/`.
- `api/`: endpoint PHP frontend yang meneruskan permintaan ke backend.

## Menjalankan secara lokal

Frontend ini terhubung ke backend pada folder `backend_perunggu` yang sejajar dengan `web_perunggu`. Untuk fitur dinamis seperti katalog, jadwal, dan checkout, jalankan melalui Apache dengan PHP serta konfigurasi database/backend terlebih dahulu.

1. Ikuti petunjuk setup di [`../backend_perunggu/README.md`](../backend_perunggu/README.md).
2. Pastikan struktur folder `backend_perunggu/` dan `web_perunggu/` tetap sejajar di document root Apache.
3. Buka `http://localhost/perunggu/web_perunggu/` (sesuaikan `perunggu` dengan nama folder proyek).

Tidak diperlukan proses build frontend khusus; halaman menggunakan HTML, CSS, JavaScript, dan endpoint PHP. Ikon dan peta menggunakan library dari CDN, jadi koneksi internet diperlukan untuk memuatnya.
