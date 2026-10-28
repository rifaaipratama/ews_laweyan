<?php
session_start();

// 1. Proteksi Keamanan Standar
if (!isset($_SESSION['id_pengguna']) || $_SESSION['id_role'] != 1) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Pastikan ada parameter ID yang dikirim melalui URL
if (isset($_GET['id'])) {
    
    $id_role_hapus = (int)$_GET['id'];

    // KUNCI 1: Cegah penghapusan Role Administrator (ID 1)
    if ($id_role_hapus == 1) {
        $_SESSION['alert_gagal'] = "Akses Ditolak! Role <b>Administrator</b> adalah hak akses inti sistem dan tidak dapat dihapus.";
        header("Location: index.php");
        exit;
    }

    // KUNCI 2: Cegah penghapusan jika role masih dipakai oleh pengguna
    $cek_pengguna = mysqli_query($koneksi, "SELECT id_pengguna FROM tabel_pengguna WHERE id_role = '$id_role_hapus'");
    
    if (mysqli_num_rows($cek_pengguna) > 0) {
        $jumlah_user = mysqli_num_rows($cek_pengguna);
        $_SESSION['alert_gagal'] = "Penghapusan Dibatalkan! Role ini masih digunakan oleh <b>$jumlah_user pengguna</b>. Silakan ubah role pengguna tersebut terlebih dahulu.";
        header("Location: index.php");
        exit;
    }

    // Jika lolos kedua kunci di atas, ambil nama role untuk notifikasi, lalu hapus
    $query_nama = mysqli_query($koneksi, "SELECT nama_role FROM tabel_role WHERE id_role = '$id_role_hapus'");
    $data_role = mysqli_fetch_assoc($query_nama);
    $nama_role_dihapus = $data_role['nama_role'];

    // Eksekusi Query Hapus
    $query_hapus = "DELETE FROM tabel_role WHERE id_role = '$id_role_hapus'";

    if (mysqli_query($koneksi, $query_hapus)) {
        $_SESSION['alert_sukses'] = "Hak akses <b>$nama_role_dihapus</b> berhasil dihapus secara permanen dari sistem.";
        header("Location: index.php");
        exit;
    } else {
        die("Error Database: " . mysqli_error($koneksi));
    }

} else {
    // Jika tidak ada ID di URL, kembalikan ke index
    header("Location: index.php");
    exit;
}
?>