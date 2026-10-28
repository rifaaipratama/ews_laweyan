<?php
session_start();

if (!isset($_SESSION['id_pengguna']) || $_SESSION['id_role'] != 1) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Tangkap Kredensial Inti (Wajib)
    $nama_lengkap = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
    $username     = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password_raw = mysqli_real_escape_string($koneksi, $_POST['password']);
    $id_role      = intval($_POST['id_role']);
    $status_aktif = mysqli_real_escape_string($koneksi, $_POST['status_aktif']);

    // 2. Tangkap Data Demografi (Opsional)
    $nip          = mysqli_real_escape_string($koneksi, trim($_POST['nip']));
    $jabatan      = mysqli_real_escape_string($koneksi, trim($_POST['jabatan']));
    $email        = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $no_telepon   = mysqli_real_escape_string($koneksi, trim($_POST['no_telepon']));
    $alamat       = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));
    
    // Penanganan khusus NULL untuk ENUM kosong
    $status_kepegawaian = !empty($_POST['status_kepegawaian']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['status_kepegawaian']) . "'" : "NULL";
    $jenis_kelamin      = !empty($_POST['jenis_kelamin']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']) . "'" : "NULL";

    // 3. Hash Password
    $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

    // 4. Cek Duplikasi Username & Email
    $cek_duplikat = mysqli_query($koneksi, "SELECT username, email FROM tabel_pengguna WHERE username = '$username' OR (email = '$email' AND email != '')");
    if (mysqli_num_rows($cek_duplikat) > 0) {
        $_SESSION['alert_gagal'] = "Username <b>@$username</b> atau Email tersebut sudah terdaftar! Silakan gunakan yang lain.";
        header("Location: index.php");
        exit;
    }

    // 5. Eksekusi Insert
    $query_insert = "INSERT INTO tabel_pengguna (
                        nama_lengkap, username, password_hash, id_role, status_aktif, 
                        nip, jabatan, email, no_telepon, alamat, status_kepegawaian, jenis_kelamin
                    ) VALUES (
                        '$nama_lengkap', '$username', '$password_hash', '$id_role', '$status_aktif',
                        '$nip', '$jabatan', '$email', '$no_telepon', '$alamat', $status_kepegawaian, $jenis_kelamin
                    )";

    if (mysqli_query($koneksi, $query_insert)) {
        $_SESSION['alert_sukses'] = "Akun pengguna baru atas nama <b>$nama_lengkap</b> berhasil diregistrasi.";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['alert_gagal'] = "Terjadi kesalahan sistem: " . mysqli_error($koneksi);
        header("Location: index.php");
        exit;
    }

} else {
    header("Location: index.php");
    exit;
}
?>