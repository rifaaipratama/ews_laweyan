<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Cek apakah ada lemparan id_target spesifik dari form (khusus Admin)
if ($_SESSION['id_role'] == 1 && isset($_POST['id_target'])) {
    $id_pengguna = intval($_POST['id_target']);
} else {
    $id_pengguna = $_SESSION['id_pengguna'];
}
    
    $nama_lengkap  = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
    $username      = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $email         = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $nip           = mysqli_real_escape_string($koneksi, trim($_POST['nip']));
    $jabatan       = mysqli_real_escape_string($koneksi, trim($_POST['jabatan']));
    $no_telepon    = mysqli_real_escape_string($koneksi, trim($_POST['no_telepon']));
    $alamat        = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));
    $password_baru = trim($_POST['password_baru']);

    // Penanganan khusus tipe ENUM: Ubah string kosong menjadi format NULL
    $jenis_kelamin      = !empty($_POST['jenis_kelamin']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']) . "'" : "NULL";
    $status_kepegawaian = !empty($_POST['status_kepegawaian']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['status_kepegawaian']) . "'" : "NULL";

    $query_update = "UPDATE tabel_pengguna SET 
                        nama_lengkap = '$nama_lengkap', 
                        username = '$username', 
                        email = '$email',
                        nip = '$nip',
                        jabatan = '$jabatan',
                        no_telepon = '$no_telepon',
                        jenis_kelamin = $jenis_kelamin,
                        status_kepegawaian = $status_kepegawaian,
                        alamat = '$alamat' ";

    if (!empty($password_baru)) {
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $query_update .= ", password_hash = '$password_hash' ";
    }

    $query_update .= " WHERE id_pengguna = '$id_pengguna'";

    if (mysqli_query($koneksi, $query_update)) {
        $_SESSION['nama_lengkap'] = $nama_lengkap;
        $_SESSION['alert_sukses'] = "Profil dan kelengkapan data Anda berhasil diperbarui!";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['alert_gagal'] = "Gagal memperbarui profil: " . mysqli_error($koneksi);
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>