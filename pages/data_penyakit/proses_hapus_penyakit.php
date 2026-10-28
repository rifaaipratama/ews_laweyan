<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION['id_role'] != 1) {
    $_SESSION['alert_gagal'] = "Akses Ditolak! Anda tidak memiliki izin untuk menghapus Master Data.";
    header("Location: index.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_penyakit = (int)$_GET['id'];
    $id_pengguna = (int)$_SESSION['id_pengguna'];

    // Ambil nama penyakit dan kode icd sebelum dihapus untuk log aktivitas
    $q_info = mysqli_query($koneksi, "SELECT nama_populer, kode_icd FROM tabel_penyakit WHERE id_penyakit = '$id_penyakit'");
    $info_penyakit = mysqli_fetch_assoc($q_info);

    // 1. CEK RELASI: Apakah penyakit ini sudah dipakai oleh pasien di tabel_kasus?
    $cek_kasus = mysqli_query($koneksi, "SELECT id_kasus FROM tabel_kasus WHERE id_penyakit = '$id_penyakit'");

    if (mysqli_num_rows($cek_kasus) > 0) {
        $jumlah = mysqli_num_rows($cek_kasus);
        $_SESSION['alert_gagal'] = "<strong>Penghapusan Ditolak!</strong> Penyakit ini tidak dapat dihapus karena sudah digunakan pada <b>$jumlah data kasus pasien</b>. Silakan gunakan fitur Edit untuk menonaktifkan status pantauannya.";
        header("Location: index.php");
        exit;
        
    } else {
        // 2. JIKA AMAN: Hapus dari tabel_penyakit
        $query_hapus = "DELETE FROM tabel_penyakit WHERE id_penyakit = '$id_penyakit'";

        if (mysqli_query($koneksi, $query_hapus)) {
            
            // ================================================================
            // BLOK PENCATATAN LOG AKTIVITAS (HANYA JIKA BERHASIL DIHAPUS)
            // ================================================================
            if ($info_penyakit) {
                $nama_populer = $info_penyakit['nama_populer'];
                $kode_icd = $info_penyakit['kode_icd'];
                
                $deskripsi_log = "Menghapus Soft Delete penyakit <b>$nama_populer</b> (ICD: $kode_icd) dari sistem pantauan EWS Data masih Tersimpan di DB Master Data ICD.";
                $query_log = "INSERT INTO tabel_riwayat_aktivitas (id_pengguna, jenis_aktivitas, deskripsi) 
                              VALUES ('$id_pengguna', 'Hapus Penyakit', '$deskripsi_log')";
                mysqli_query($koneksi, $query_log);
            }
            // ================================================================

            $_SESSION['alert_sukses'] = "Penyakit berhasil dihapus dari radar EWS. (Data kamus standar ICD-10 tetap dipertahankan).";
        } else {
            $_SESSION['alert_gagal'] = "Error Sistem: Gagal menghapus data dari database.";
        }
    }
}

header("Location: index.php");
exit;
?>