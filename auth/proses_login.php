<?php
session_start();
// Naik satu folder untuk memanggil file koneksi
require_once '../config/koneksi.php';

// KODE PENENANG INTELEPHENSE: Memberi tahu editor bahwa $koneksi adalah objek mysqli
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Menangkap inputan dan membersihkannya (mencegah serangan SQL Injection)
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password = $_POST['password'];

    // Mencari user berdasarkan username dan memastikan akunnya 'aktif'
    $query = mysqli_query($koneksi, "SELECT * FROM tabel_pengguna WHERE username = '$username' AND status_aktif = 'aktif'");
    
    // Jika username ditemukan di database
    if (mysqli_num_rows($query) === 1) {
        $user = mysqli_fetch_assoc($query);
        
        // Memverifikasi kecocokan password yang diketik dengan hash di database
        if (password_verify($password, $user['password_hash'])) {
            
            // Password BENAR! Daftarkan data ke Session
            $_SESSION['id_pengguna']  = $user['id_pengguna'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['id_role']      = $user['id_role'];
            
            // 1. Update waktu terakhir login (last_login)
            $id_pengguna = $user['id_pengguna'];
            mysqli_query($koneksi, "UPDATE tabel_pengguna SET last_login = NOW() WHERE id_pengguna = '$id_pengguna'");
            
            // 2. Catat ke tabel riwayat aktivitas (Audit Trail)
            $deskripsi = "Melakukan login ke dalam sistem";
            mysqli_query($koneksi, "INSERT INTO tabel_riwayat_aktivitas (id_pengguna, jenis_aktivitas, deskripsi) VALUES ('$id_pengguna', 'Login', '$deskripsi')");

            // ==============================================================
            // PERBAIKAN: Arahkan ke folder dashboard yang baru
            // ==============================================================
            header("Location: ../pages/dashboard/index.php");
            exit;
        } else {
            // Password SALAH
            $_SESSION['error_msg'] = "Password yang Anda masukkan salah!";
            header("Location: login.php");
            exit;
        }
    } else {
        // Username tidak ada
        $_SESSION['error_msg'] = "Username tidak ditemukan atau akun nonaktif!";
        header("Location: login.php");
        exit;
    }
} else {
    // Jika file diakses langsung dari URL tanpa menekan tombol form
    header("Location: login.php");
    exit;
}
?>