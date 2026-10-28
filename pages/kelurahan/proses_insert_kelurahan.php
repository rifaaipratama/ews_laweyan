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
    $kode_wilayah     = mysqli_real_escape_string($koneksi, $_POST['kode_wilayah']);
    $nama_kelurahan   = mysqli_real_escape_string($koneksi, $_POST['nama_kelurahan']);
    $kodepos          = mysqli_real_escape_string($koneksi, $_POST['kodepos']);
    $jumlah_penduduk  = mysqli_real_escape_string($koneksi, $_POST['jumlah_penduduk']);
    $luas_wilayah_km2 = mysqli_real_escape_string($koneksi, $_POST['luas_wilayah_km2']);
    
    // PERBAIKAN: Jika kosong, jadikan NULL (tanpa tanda kutip saat di query)
    $geo_polygon      = !empty($_POST['geo_polygon']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['geo_polygon']) . "'" : "NULL";

    // PERBAIKAN: Hapus tanda kutip (' ') yang mengapit $geo_polygon di VALUE
    $query_insert = "INSERT INTO tabel_kelurahan (
                        id_pengguna, kode_wilayah, nama_kelurahan, kodepos, jumlah_penduduk, luas_wilayah_km2, geo_polygon
                    ) VALUES (
                        '$id_pengguna', '$kode_wilayah', '$nama_kelurahan', '$kodepos', '$jumlah_penduduk', '$luas_wilayah_km2', $geo_polygon
                    )";

    if (mysqli_query($koneksi, $query_insert)) {
        $populasi = number_format($jumlah_penduduk, 0, ',', '.');
        $deskripsi_log = "Mendaftarkan wilayah pantauan baru: <b>Kel. {$nama_kelurahan}</b> (Kode: {$kode_wilayah}) dengan populasi {$populasi} jiwa.";
        
        $query_log = "INSERT INTO tabel_riwayat_aktivitas (id_pengguna, jenis_aktivitas, deskripsi) 
                      VALUES ('$id_pengguna', 'Tambah Kelurahan', '$deskripsi_log')";
        mysqli_query($koneksi, $query_log);

        $_SESSION['alert_sukses'] = "Data wilayah Kelurahan <b>$nama_kelurahan</b> berhasil ditambahkan ke sistem.";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['alert_gagal'] = "Gagal menyimpan data! Error: " . mysqli_error($koneksi);
        header("Location: index.php");
        exit;
    }

} else {
    header("Location: index.php");
    exit;
}
?>