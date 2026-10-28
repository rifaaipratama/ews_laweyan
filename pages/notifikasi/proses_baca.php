<?php
session_start();

// 1. Pastikan pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// 2. Tangkap ID dari URL (Contoh: proses_baca.php?id=1)
if (isset($_GET['id'])) {
    $id_alert = intval($_GET['id']);

    // 3. Update status menjadi 'sudah'
    // Cukup update data yang id-nya sesuai dan pastikan hanya yang statusnya 'belum' yang diubah
    $query_update = "UPDATE tabel_alert SET status_baca = 'sudah' WHERE id_alert = $id_alert";
    
    mysqli_query($koneksi, $query_update);
    // Kita abaikan pengecekan error di sini agar user experience tetap mulus (seamless redirect)
}

// 4. Langsung kembalikan ke halaman notifikasi
header("Location: index.php");
exit;
?>