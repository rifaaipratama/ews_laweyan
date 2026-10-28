<?php
session_start();
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nik_pasien     = mysqli_real_escape_string($koneksi, $_POST['nik_pasien']);
    $no_telepon     = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);
    $alamat_lengkap = mysqli_real_escape_string($koneksi, $_POST['alamat_lengkap']);

    // Update kontak dan alamat untuk semua riwayat kasus dengan NIK yang sama
    $query_update = "UPDATE tabel_kasus 
                     SET no_telepon = NULLIF('$no_telepon', ''), 
                         alamat_lengkap = NULLIF('$alamat_lengkap', '') 
                     WHERE nik_pasien = '$nik_pasien'";

    if (mysqli_query($koneksi, $query_update)) {
        $_SESSION['alert_sukses'] = "Data kontak dan alamat pasien berhasil diperbarui.";
    } else {
        $_SESSION['alert_gagal'] = "Gagal memperbarui data pasien.";
    }
    
    // Kembalikan ke halaman detail pasien tersebut
    header("Location: detail_riwayat.php?nik=" . urlencode($nik_pasien));
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>