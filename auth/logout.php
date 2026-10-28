<?php
session_start();

// Cek jika user memang memiliki sesi login aktif sebelum mencatat log
if(isset($_SESSION['id_pengguna'])) {
    require_once '../config/koneksi.php';
    
    // KODE PENENANG INTELEPHENSE
    /** @var mysqli $koneksi */
    
    $id_pengguna = $_SESSION['id_pengguna'];
    $deskripsi = "Melakukan logout dari sistem";
    
    // Mencatat riwayat logout
    mysqli_query($koneksi, "INSERT INTO tabel_riwayat_aktivitas (id_pengguna, jenis_aktivitas, deskripsi) VALUES ('$id_pengguna', 'Logout', '$deskripsi')");
}

// Hancurkan semua data sesi
session_unset();
session_destroy();

// Arahkan kembali ke halaman login
header("Location: login.php");
exit;