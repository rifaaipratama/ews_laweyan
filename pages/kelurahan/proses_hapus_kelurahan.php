<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION['id_role'] != 1) {
    $_SESSION['alert_gagal'] = "Akses Ditolak! Anda tidak memiliki izin untuk menghapus Master Data Wilayah.";
    header("Location: index.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if (isset($_GET['id'])) {
    $id_pengguna  = (int)$_SESSION['id_pengguna'];
    $id_kelurahan = intval($_GET['id']);

    // Cek Relasi: Apakah kelurahan ini sudah dipakai di tabel kasus?
    $cek_kasus = mysqli_query($koneksi, "SELECT id_kasus FROM tabel_kasus WHERE id_kelurahan = '$id_kelurahan'");
    
    if (mysqli_num_rows($cek_kasus) > 0) {
        $jumlah = mysqli_num_rows($cek_kasus);
        $_SESSION['alert_gagal'] = "<strong>Penghapusan Ditolak!</strong> Kelurahan ini tidak dapat dihapus karena menampung <b>$jumlah data kasus</b> EWS. Silakan sesuaikan data kasus terlebih dahulu.";
        header("Location: index.php");
        exit;
    }

    $q_cek = mysqli_query($koneksi, "SELECT nama_kelurahan, kode_wilayah FROM tabel_kelurahan WHERE id_kelurahan = $id_kelurahan");
    $data = mysqli_fetch_assoc($q_cek);

    if ($data) {
        $nama_kelurahan = $data['nama_kelurahan'];
        $kode_wilayah   = $data['kode_wilayah'];

        $query_delete = "DELETE FROM tabel_kelurahan WHERE id_kelurahan = $id_kelurahan";

        if (mysqli_query($koneksi, $query_delete)) {
            
            // ================================================================
            // BLOK PENCATATAN LOG AKTIVITAS
            // ================================================================
            $deskripsi_log = "Menghapus permanen master data wilayah <b>Kel. {$nama_kelurahan}</b> (Kode: {$kode_wilayah}) dari sistem EWS.";
            $query_log = "INSERT INTO tabel_riwayat_aktivitas (id_pengguna, jenis_aktivitas, deskripsi) 
                          VALUES ('$id_pengguna', 'Hapus Kelurahan', '$deskripsi_log')";
            mysqli_query($koneksi, $query_log);
            // ================================================================

            $_SESSION['alert_sukses'] = "Data wilayah Kelurahan <b>$nama_kelurahan</b> berhasil dihapus dari sistem.";
        } else {
            $_SESSION['alert_gagal'] = "Gagal menghapus data! Error: " . mysqli_error($koneksi);
        }
    } else {
        $_SESSION['alert_gagal'] = "Data kelurahan tidak ditemukan.";
    }
}

header("Location: index.php");
exit;
?>