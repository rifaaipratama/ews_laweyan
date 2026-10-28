<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $kode_icd     = strtoupper(mysqli_real_escape_string($koneksi, trim($_POST['kode_icd'])));
    $deskripsi_id = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi_id']));
    $deskripsi_en = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi_en']));
    $kategori     = mysqli_real_escape_string($koneksi, trim($_POST['kategori']));
    
    $nama_populer  = mysqli_real_escape_string($koneksi, trim($_POST['nama_populer']));
    $status_pantau = mysqli_real_escape_string($koneksi, trim($_POST['status_pantau']));
    
    $id_pengguna   = (int)$_SESSION['id_pengguna'];

    // --- MULAI DATABASE TRANSACTION ---
    mysqli_begin_transaction($koneksi);

    try {
        // CEK 1: Apakah Kode ICD ini sudah ada?
        $cek_icd = mysqli_query($koneksi, "SELECT kode_icd FROM tabel_master_icd10 WHERE kode_icd = '$kode_icd'");
        
        if (mysqli_num_rows($cek_icd) == 0) {
            $query_icd = "INSERT INTO tabel_master_icd10 (kode_icd, deskripsi_en, deskripsi_id, kategori) 
                          VALUES ('$kode_icd', '$deskripsi_en', '$deskripsi_id', '$kategori')";
            if (!mysqli_query($koneksi, $query_icd)) throw new Exception("Gagal menyimpan data ke Master ICD-10.");
        } else {
            $query_update_icd = "UPDATE tabel_master_icd10 
                                 SET deskripsi_id = '$deskripsi_id', deskripsi_en = '$deskripsi_en', kategori = '$kategori' 
                                 WHERE kode_icd = '$kode_icd'";
            mysqli_query($koneksi, $query_update_icd);
        }

        // CEK 2: Radar EWS
        $cek_penyakit = mysqli_query($koneksi, "SELECT id_penyakit FROM tabel_penyakit WHERE kode_icd = '$kode_icd'");
        if (mysqli_num_rows($cek_penyakit) > 0) throw new Exception("Penyakit dengan kode ICD <b>$kode_icd</b> sudah terdaftar dalam radar.");

        // AKSI FINAL
        $query_penyakit = "INSERT INTO tabel_penyakit (kode_icd, nama_populer, status_pantau, id_pengguna) 
                           VALUES ('$kode_icd', '$nama_populer', '$status_pantau', '$id_pengguna')";
        
        if (!mysqli_query($koneksi, $query_penyakit)) throw new Exception("Gagal mendaftarkan penyakit ke sistem EWS.");

        // ================================================================
        // BLOK PENCATATAN LOG AKTIVITAS
        // ================================================================
        $deskripsi_log = "Mendaftarkan master data penyakit baru: <b>$nama_populer</b> (Kode ICD: $kode_icd).";
        $query_log = "INSERT INTO tabel_riwayat_aktivitas (id_pengguna, jenis_aktivitas, deskripsi) 
                      VALUES ('$id_pengguna', 'Tambah Penyakit', '$deskripsi_log')";
        mysqli_query($koneksi, $query_log);
        // ================================================================

        // COMMIT Jika semua aman
        mysqli_commit($koneksi);
        
        $_SESSION['alert_sukses'] = "Data penyakit <b>$nama_populer</b> ($kode_icd) berhasil didaftarkan ke Master dan EWS.";
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $_SESSION['alert_gagal'] = $e->getMessage();
        header("Location: tambah_penyakit.php");
        exit;
    }

} else {
    header("Location: index.php");
    exit;
}
?>