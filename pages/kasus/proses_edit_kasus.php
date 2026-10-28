<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil ID User yang sedang melakukan edit dari session
    $id_pengguna    = $_SESSION['id_pengguna']; 
    
    // 1. Ambil dan bersihkan data dari form edit
    $id_kasus       = intval($_POST['id_kasus']);
    $status_lama    = mysqli_real_escape_string($koneksi, $_POST['status_lama']); 
    $nik_pasien     = mysqli_real_escape_string($koneksi, $_POST['nik_pasien']);
    $nama_pasien    = mysqli_real_escape_string($koneksi, $_POST['nama_pasien']);
    $jenis_kelamin  = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $id_penyakit    = mysqli_real_escape_string($koneksi, $_POST['id_penyakit']);
    $id_kelurahan   = mysqli_real_escape_string($koneksi, $_POST['id_kelurahan']);
    $tanggal_lapor  = mysqli_real_escape_string($koneksi, $_POST['tanggal_lapor']);
    $status_kasus   = mysqli_real_escape_string($koneksi, $_POST['status_kasus']); 

    // Penanganan NULL untuk kolom tanggal opsional
    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']) . "'" : "NULL";
    $tanggal_onset = !empty($_POST['tanggal_onset']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['tanggal_onset']) . "'" : "NULL";

    // 2. LOGIKA CERDAS: Auto-isi Tanggal Diagnosis & Tanggal Selesai
    $hari_ini = date('Y-m-d');
    $query_tambahan = "";
    
    // Auto-isi Tanggal Diagnosis (Suspek -> Terkonfirmasi/Meninggal)
    if ($status_lama == 'suspek' && ($status_kasus == 'terkonfirmasi' || $status_kasus == 'meninggal')) {
        $query_tambahan .= ", tanggal_diagnosis = '$hari_ini'";
    }

    // Auto-isi & Reset Tanggal Selesai
    if (($status_kasus == 'sembuh' || $status_kasus == 'meninggal') && ($status_lama != 'sembuh' && $status_lama != 'meninggal')) {
        // Jika berubah menjadi sembuh/meninggal hari ini
        $query_tambahan .= ", tanggal_selesai = '$hari_ini'";
    } elseif ($status_kasus == 'suspek' || $status_kasus == 'terkonfirmasi') {
        // Jika kasus kembali aktif (mungkin petugas salah input sebelumnya), kosongkan tanggal selesai
        $query_tambahan .= ", tanggal_selesai = NULL";
    }

    // 3. Query Update Data Kasus
    $query_update = "UPDATE tabel_kasus SET 
                        nik_pasien = '$nik_pasien',
                        nama_pasien = '$nama_pasien',
                        jenis_kelamin = '$jenis_kelamin',
                        tanggal_lahir = $tanggal_lahir,
                        id_penyakit = '$id_penyakit',
                        id_kelurahan = '$id_kelurahan',
                        tanggal_lapor = '$tanggal_lapor',
                        tanggal_onset = $tanggal_onset,
                        status_kasus = '$status_kasus'
                        $query_tambahan
                     WHERE id_kasus = $id_kasus";

    // 4. Eksekusi Query
    if (mysqli_query($koneksi, $query_update)) {
        
        // ================================================================
        // BLOK PENCATATAN LOG AKTIVITAS (AUDIT TRAIL)
        // ================================================================
        $q_info = mysqli_query($koneksi, "SELECT nama_kelurahan FROM tabel_kelurahan WHERE id_kelurahan = '$id_kelurahan'");
        
        if ($info = mysqli_fetch_assoc($q_info)) {
            $nama_kelurahan = $info['nama_kelurahan'];
            $status_upper = strtoupper($status_kasus);
            
            // Format teks murni
            $deskripsi_log = "Memperbarui data medis pasien atas nama $nama_pasien (Kel. $nama_kelurahan). Status saat ini: $status_upper.";
            
            $query_log = "INSERT INTO tabel_riwayat_aktivitas (id_pengguna, jenis_aktivitas, deskripsi) 
                          VALUES ('$id_pengguna', 'Edit Kasus', '$deskripsi_log')";
            mysqli_query($koneksi, $query_log);
        }
        // ================================================================
        
        // -------------------------------------------------------------
        // TRIGGER OTOMATIS EWS: Kalkulasi ulang saat data diperbarui
        // -------------------------------------------------------------
        require_once '../../helpers/ews_trigger.php';
        
        if ($status_kasus == 'sembuh' || $status_kasus == 'meninggal') {
            jalankan_ews_penurunan($koneksi, $id_kelurahan, $id_penyakit);
        } else {
            jalankan_ews_otomatis($koneksi, $id_kelurahan, $id_penyakit);
        }
        // -------------------------------------------------------------

        $_SESSION['alert_sukses'] = "Data rekam medis atas nama <b>$nama_pasien</b> berhasil diperbarui.";
        header("Location: index.php");
        exit;
    } else {
        die("Error Database: " . mysqli_error($koneksi));
    }

} else {
    header("Location: index.php");
    exit;
}
?>