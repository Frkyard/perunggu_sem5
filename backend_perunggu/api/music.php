<?php
// ========================================================
// API: DISKOGRAFI, TRACKS, & NOW PLAYING
// ========================================================
require_once __DIR__ . '/connect_cors.php';

// 1. Ambil data album dan hitung jumlah track secara dinamis
$albums_res = mysqli_query($conn, "SELECT id, title, release_year, cover_image, deskripsi FROM albums ORDER BY release_year DESC, id ASC");
$albums = [];
if ($albums_res) {
    while ($a = mysqli_fetch_assoc($albums_res)) {
        $a['id'] = (int)$a['id'];
        $a['release_year'] = (int)$a['release_year'];

        // Hitung jumlah track aktual dari tabel tracks
        $tc_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tracks WHERE album_id = " . $a['id']);
        $tc_row = $tc_res ? mysqli_fetch_assoc($tc_res) : null;
        $a['track_count'] = $tc_row ? (int)$tc_row['total'] : 0;

        $albums[] = $a;
    }
}

// 2. Ambil data track lengkap dengan judul album
$tracks_sql = "SELECT t.id, t.album_id, a.title AS album_title, t.title, t.duration,
                      t.audio_url, t.lyrics, t.chord, t.is_now_playing
               FROM tracks t
               LEFT JOIN albums a ON t.album_id = a.id
               ORDER BY t.id ASC";
$tracks_res = mysqli_query($conn, $tracks_sql);
$tracks = [];
$now_playing = null;

if ($tracks_res) {
    while ($t = mysqli_fetch_assoc($tracks_res)) {
        $t['id'] = (int)$t['id'];
        $t['album_id'] = (int)$t['album_id'];
        $t['is_now_playing'] = (int)$t['is_now_playing'];
        if ($t['is_now_playing'] === 1 && $now_playing === null) {
            $now_playing = $t;
        }
        $tracks[] = $t;
    }
}

// Jika belum ada yang aktif, fallback ke track pertama
if ($now_playing === null && count($tracks) > 0) {
    $now_playing = $tracks[0];
}

echo json_encode([
    'status' => 'success',
    'albums' => $albums,
    'tracks' => $tracks,
    'now_playing' => $now_playing
]);
