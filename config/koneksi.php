<?php
// Konfigurasi Database (Sesuai default Laragon)
$host     = "localhost";
$username = "root";       // Username default Laragon
$password = "";           // Password default Laragon biasanya kosong
$database = "ews_laweyan"; // Nama database yang sudah kita buat sebelumnya

// Membuka koneksi ke database
$koneksi = mysqli_connect($host, $username, $password, $database);

// Mengecek apakah koneksi berhasil
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Opsional: Set timezone agar pencatatan waktu aktivitas sesuai dengan Waktu Indonesia Barat
date_default_timezone_set('Asia/Jakarta');
?>