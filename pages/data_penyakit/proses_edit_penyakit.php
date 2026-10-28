<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id_penyakit   = (int)$_POST['id_penyakit'];
    $kode_icd      = mysqli_real_escape_string($koneksi, trim($_POST['kode_icd'])); 
    
    $deskripsi_id  = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi_id']));
    $deskripsi_en  = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi_en']));
    $kategori      = mysqli_real_escape_string($koneksi, trim($_POST['kategori']));
    
    $nama_populer  = mysqli_real_escape_string($koneksi, trim($_POST['nama_populer']));
    $status_pantau = mysqli_real_escape_string($koneksi, trim($_POST['status_pantau']));
    
    $id_pengguna   = (int)$_SESSION['id_pengguna'];

    // --- MULAI DATABASE TRANSACTION ---
    mysqli_begin_transaction($koneksi);

    try {
        $query_update_icd = "UPDATE tabel_master_icd10 
                             SET deskripsi_id = '$deskripsi_id', deskripsi_en = '$deskripsi_en', kategori = '$kategori' 
                             WHERE kode_icd = '$kode_icd'";
                             
        if (!mysqli_query($koneksi, $query_update_icd)) throw new Exception("Gagal memperbarui data standar Master ICD-10.");

        $query_update_penyakit = "UPDATE tabel_penyakit 
                                  SET nama_populer = '$nama_populer', status_pantau = '$status_pantau', id_pengguna = '$id_pengguna' 
                                  WHERE id_penyakit = '$id_penyakit'";
        
        if (!mysqli_query($koneksi, $query_update_penyakit)) throw new Exception("Gagal memperbarui pengaturan radar penyakit EWS.");

        // ================================================================
        // BLOK PENCATATAN LOG AKTIVITAS
        // ================================================================
        $status_text = strtoupper($status_pantau);
        $deskripsi_log = "Memperbarui master data penyakit: <b>$nama_populer</b> (ICD: $kode_icd). Status EWS: $status_text.";
        $query_log = "INSERT INTO tabel_riwayat_aktivitas (id_pengguna, jenis_aktivitas, deskripsi) 
                      VALUES ('$id_pengguna', 'Edit Penyakit', '$deskripsi_log')";
        mysqli_query($koneksi, $query_log);
        // ================================================================

        mysqli_commit($koneksi);
        
        $_SESSION['alert_sukses'] = "Perubahan pada data penyakit <b>$nama_populer</b> berhasil disimpan.";
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $_SESSION['alert_gagal'] = $e->getMessage();
        header("Location: edit_penyakit.php?id=" . $id_penyakit);
        exit;
    }

} else {
    header("Location: index.php");
    exit;
}
?>