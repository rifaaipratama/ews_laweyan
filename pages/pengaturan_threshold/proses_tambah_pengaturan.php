<?php
session_start();

if (!isset($_SESSION['id_pengguna']) || $_SESSION['id_role'] != 1) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Sanitasi input
    $id_pengguna   = mysqli_real_escape_string($koneksi, $_POST['id_pengguna']);
    $id_penyakit   = mysqli_real_escape_string($koneksi, $_POST['id_penyakit']);
    $batas_waspada = (int)$_POST['batas_waspada'];
    $batas_wabah   = (int)$_POST['batas_wabah'];
    $periode_hari  = (int)$_POST['periode_hari'];

    // LOGIKA BARU: Cek HANYA berdasarkan id_penyakit (Threshold Global)
    $cek_query = mysqli_query($koneksi, "SELECT id_threshold FROM tabel_pengaturan_threshold WHERE id_penyakit = '$id_penyakit'");

    if (mysqli_num_rows($cek_query) > 0) {
        // Logika UPDATE jika penyakit ini sudah pernah diatur oleh siapapun sebelumnya
        $query_aksi = "UPDATE tabel_pengaturan_threshold SET 
                        id_pengguna = '$id_pengguna', /* Record siapa admin terakhir yang mengubah */
                        batas_waspada = '$batas_waspada', 
                        batas_wabah = '$batas_wabah', 
                        periode_hari = '$periode_hari',
                        updated_at = NOW() 
                       WHERE id_penyakit = '$id_penyakit'";
        $pesan_aksi = "diperbarui";
    } else {
        // Logika INSERT jika penyakit ini benar-benar belum pernah diatur
        $query_aksi = "INSERT INTO tabel_pengaturan_threshold (id_penyakit, id_pengguna, batas_waspada, batas_wabah, periode_hari) 
                       VALUES ('$id_penyakit', '$id_pengguna', '$batas_waspada', '$batas_wabah', '$periode_hari')";
        $pesan_aksi = "ditambahkan";
    }

    if (mysqli_query($koneksi, $query_aksi)) {
        $_SESSION['alert_sukses'] = "Pengaturan threshold global berhasil <b>$pesan_aksi</b> dalam sistem.";
        header("Location: index.php");
        exit;
    } else {
        die("Error Database: " . mysqli_error($koneksi));
    }

} else {
    header("Location: index.php");
    exit;
}
?>