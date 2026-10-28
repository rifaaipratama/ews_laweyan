<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Pastikan ada parameter ID yang dikirim melalui URL
if (isset($_GET['id'])) {
    $id_pengguna = $_SESSION['id_pengguna']; // Ambil ID Admin/Petugas yang sedang aktif
    $id_kasus    = intval($_GET['id']);

    // Ambil data detail kasus terlebih dahulu untuk keperluan teks notifikasi dan trigger
    $q_cek = mysqli_query($koneksi, "SELECT nama_pasien, id_kelurahan, id_penyakit, status_kasus FROM tabel_kasus WHERE id_kasus = $id_kasus");
    $data = mysqli_fetch_assoc($q_cek);

    if ($data) {
        $nama_pasien  = $data['nama_pasien'];
        $id_kelurahan = $data['id_kelurahan'];
        $id_penyakit  = $data['id_penyakit'];

        // Query Soft Delete: Hanya merubah status is_deleted menjadi 1, BUKAN menghapus permanen
        $query_soft_delete = "UPDATE tabel_kasus SET is_deleted = 1 WHERE id_kasus = $id_kasus";

        if (mysqli_query($koneksi, $query_soft_delete)) {
            
            // ================================================================
            // BLOK PENCATATAN LOG AKTIVITAS (AUDIT TRAIL) HAPUS KASUS
            // ================================================================
            $q_info = mysqli_query($koneksi, "
                SELECT p.nama_populer, k.nama_kelurahan 
                FROM tabel_penyakit p, tabel_kelurahan k 
                WHERE p.id_penyakit = '$id_penyakit' AND k.id_kelurahan = '$id_kelurahan'
            ");
            
            if ($info = mysqli_fetch_assoc($q_info)) {
                $nama_penyakit = $info['nama_populer'];
                $nama_kelurahan = $info['nama_kelurahan'];
                
                // Menggunakan kata "Arsip Kasus" atau "Hapus Kasus" agar sesuai dengan icon warna merah di frontend
                $deskripsi_log = "Mengarsipkan (Soft Delete) data kasus atas nama $nama_pasien (Kel. $nama_kelurahan) dengan diagnosis $nama_penyakit.";
                
                $query_log = "INSERT INTO tabel_riwayat_aktivitas (id_pengguna, jenis_aktivitas, deskripsi) 
                              VALUES ('$id_pengguna', 'Hapus Kasus', '$deskripsi_log')";
                
                mysqli_query($koneksi, $query_log) or die("GAGAL MENCATAT LOG ARSIP: " . mysqli_error($koneksi));
            }
            // ================================================================

            // -------------------------------------------------------------
            // TRIGGER EWS: Karena data "dihapus", kita harus kalkulasi ulang
            // untuk memastikan threshold wilayah tersebut terupdate (turun).
            // -------------------------------------------------------------
            require_once '../../helpers/ews_trigger.php';
            jalankan_ews_penurunan($koneksi, $id_kelurahan, $id_penyakit);
            // -------------------------------------------------------------

            $_SESSION['alert_sukses'] = "Data kasus atas nama <b>$nama_pasien</b> berhasil diarsipkan (Soft Delete). Data tetap aman di rekam medis.";
            header("Location: index.php");
            exit;
        } else {
            die("Error Database: " . mysqli_error($koneksi));
        }
    } else {
        $_SESSION['alert_gagal'] = "Data kasus tidak ditemukan.";
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>