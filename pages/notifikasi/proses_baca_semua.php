<?php
session_start();

// 1. Keamanan: Pastikan pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// 2. Query Update Semua (Bulk Update)
// Ubah semua yang statusnya 'belum' menjadi 'sudah' tanpa pandang bulu
$query_update_semua = "UPDATE tabel_alert SET status_baca = 'sudah' WHERE status_baca = 'belum'";

mysqli_query($koneksi, $query_update_semua);

// 3. Kembali ke halaman notifikasi
header("Location: index.php");
exit;
?>