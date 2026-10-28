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
    
    // 1. Ambil dan bersihkan data dari form
    $nama_role = mysqli_real_escape_string($koneksi, trim($_POST['nama_role']));
    $deskripsi = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));

    // 2. Cek Duplikasi (Pastikan tidak ada nama role yang sama persis)
    $cek_query = mysqli_query($koneksi, "SELECT id_role FROM tabel_role WHERE nama_role = '$nama_role'");
    if (mysqli_num_rows($cek_query) > 0) {
        // Jika duplikat, kembalikan dengan pesan gagal
        $_SESSION['alert_gagal'] = "Gagal! Hak Akses dengan nama <b>$nama_role</b> sudah terdaftar di sistem.";
        header("Location: index.php");
        exit;
    }

    // 3. Siapkan Query Insert
    // Catatan: created_at tidak perlu dimasukkan karena di database sudah diset CURRENT_TIMESTAMP
    $query_insert = "INSERT INTO tabel_role (nama_role, deskripsi) VALUES ('$nama_role', '$deskripsi')";

    // 4. Eksekusi Query
    if (mysqli_query($koneksi, $query_insert)) {
        $_SESSION['alert_sukses'] = "Hak akses <b>$nama_role</b> berhasil ditambahkan ke sistem.";
        header("Location: index.php");
        exit;
    } else {
        die("Error Database: " . mysqli_error($koneksi));
    }

} else {
    // Jika diakses langsung via URL
    header("Location: index.php");
    exit;
}
?>