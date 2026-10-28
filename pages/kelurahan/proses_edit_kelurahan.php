<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id_pengguna      = (int)$_SESSION['id_pengguna'];
    $id_kelurahan     = mysqli_real_escape_string($koneksi, $_POST['id_kelurahan']);
    
    // Semua data tetap ditangkap dari POST. Jika ke depan ada API yang by-pass form UI
    // dan mengirim data perubahan kode wilayah/luas, sistem tetap bisa memprosesnya.
    $kode_wilayah     = mysqli_real_escape_string($koneksi, $_POST['kode_wilayah']);
    $nama_kelurahan   = mysqli_real_escape_string($koneksi, $_POST['nama_kelurahan']);
    $kodepos          = mysqli_real_escape_string($koneksi, $_POST['kodepos']);
    $jumlah_penduduk  = mysqli_real_escape_string($koneksi, $_POST['jumlah_penduduk']);
    $luas_wilayah_km2 = mysqli_real_escape_string($koneksi, $_POST['luas_wilayah_km2']);
    
    $geo_polygon      = !empty($_POST['geo_polygon']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['geo_polygon']) . "'" : "NULL";

    $query_update = "UPDATE tabel_kelurahan SET 
                        id_pengguna = '$id_pengguna',
                        kode_wilayah = '$kode_wilayah', 
                        nama_kelurahan = '$nama_kelurahan', 
                        kodepos = '$kodepos', 
                        jumlah_penduduk = '$jumlah_penduduk', 
                        luas_wilayah_km2 = '$luas_wilayah_km2', 
                        geo_polygon = $geo_polygon
                    WHERE id_kelurahan = '$id_kelurahan'";

    if (mysqli_query($koneksi, $query_update)) {
        $deskripsi_log = "Memperbarui master data profil wilayah <b>Kel. {$nama_kelurahan}</b> (Kode: {$kode_wilayah}).";
        $query_log = "INSERT INTO tabel_riwayat_aktivitas (id_pengguna, jenis_aktivitas, deskripsi) 
                      VALUES ('$id_pengguna', 'Edit Kelurahan', '$deskripsi_log')";
        mysqli_query($koneksi, $query_log);

        $_SESSION['alert_sukses'] = "Data wilayah Kelurahan <b>$nama_kelurahan</b> berhasil diperbarui.";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['alert_gagal'] = "Gagal memperbarui data! Error: " . mysqli_error($koneksi);
        header("Location: index.php");
        exit;
    }

} else {
    header("Location: index.php");
    exit;
}
?>