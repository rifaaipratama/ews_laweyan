<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Tangkap data dari form
    $id_pengguna  = mysqli_real_escape_string($koneksi, $_POST['id_pengguna']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
    $username     = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $id_role      = mysqli_real_escape_string($koneksi, $_POST['id_role']);
    $status_aktif = mysqli_real_escape_string($koneksi, $_POST['status_aktif']);
    $password_baru = trim($_POST['password_baru']);

    // Cek apakah username baru yang diinput bertabrakan dengan user lain (duplikat)
    $cek_user = mysqli_query($koneksi, "SELECT id_pengguna FROM tabel_pengguna WHERE username = '$username' AND id_pengguna != '$id_pengguna'");
    if(mysqli_num_rows($cek_user) > 0) {
        $_SESSION['alert_gagal'] = "Gagal! Username <b>@$username</b> sudah dipakai oleh staf lain.";
        header("Location: index.php");
        exit;
    }

    // Racik Query Update untuk TABEL PENGGUNA saja
    $query_update = "UPDATE tabel_pengguna SET 
                        nama_lengkap = '$nama_lengkap', 
                        username = '$username', 
                        id_role = '$id_role',
                        status_aktif = '$status_aktif' ";

    // Jika admin mereset password staf
    if (!empty($password_baru)) {
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $query_update .= ", password_hash = '$password_hash' ";
    }

    $query_update .= " WHERE id_pengguna = '$id_pengguna'";

    // Eksekusi Update
    if (mysqli_query($koneksi, $query_update)) {
        
        // Jika admin mengedit dirinya sendiri, otomatis perbarui session agar nama di pojok atas ikut berubah
        if($_SESSION['id_pengguna'] == $id_pengguna) {
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            // Jika role juga ke-update, perbarui session role
            $_SESSION['id_role'] = $id_role; 
        }

        $_SESSION['alert_sukses'] = "Data akun <b>@$username</b> berhasil diperbarui.";
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