<?php
session_start();

// Proteksi Keamanan
if (!isset($_SESSION['id_pengguna']) || $_SESSION['id_role'] != 1) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Ambil dan bersihkan data
    $id_role   = (int)$_POST['id_role'];
    $nama_role = mysqli_real_escape_string($koneksi, trim($_POST['nama_role']));
    $deskripsi = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));

    // 2. Cek Duplikasi Nama Role (KECUALI nama role ini sendiri)
    $cek_query = mysqli_query($koneksi, "SELECT id_role FROM tabel_role WHERE nama_role = '$nama_role' AND id_role != '$id_role'");
    
    if (mysqli_num_rows($cek_query) > 0) {
        // Jika duplikat, kembalikan ke form edit beserta alert gagal
        $_SESSION['alert_gagal'] = "Pembaruan Gagal! Nama role <b>$nama_role</b> sudah dipakai oleh role lain.";
        header("Location: edit_role.php?id=$id_role");
        exit;
    }

    // 3. Eksekusi Update
    $query_update = "UPDATE tabel_role SET 
                        nama_role = '$nama_role', 
                        deskripsi = '$deskripsi' 
                     WHERE id_role = '$id_role'";

    if (mysqli_query($koneksi, $query_update)) {
        $_SESSION['alert_sukses'] = "Data role <b>$nama_role</b> berhasil diperbarui.";
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