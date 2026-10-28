<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. GUNAKAN SESSION ID AGAR 100% AKURAT DAN AMAN DARI FORM KOSONG
    $id_pengguna   = $_SESSION['id_pengguna'];
    
    // Ambil dan bersihkan data
    $nik_pasien    = mysqli_real_escape_string($koneksi, $_POST['nik_pasien']);
    $nama_pasien   = mysqli_real_escape_string($koneksi, $_POST['nama_pasien']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $id_kelurahan  = mysqli_real_escape_string($koneksi, $_POST['id_kelurahan']);
    $id_penyakit   = mysqli_real_escape_string($koneksi, $_POST['id_penyakit']);
    $status_kasus  = mysqli_real_escape_string($koneksi, $_POST['status_kasus']);
    $tanggal_lapor = mysqli_real_escape_string($koneksi, $_POST['tanggal_lapor']);

    // Tanggal opsional butuh penanganan NULL
    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']) . "'" : "NULL";
    $tanggal_onset = !empty($_POST['tanggal_onset']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['tanggal_onset']) . "'" : "NULL";

    // 2. Query Insert Data Kasus
    $query_insert = "INSERT INTO tabel_kasus (
                        id_pengguna, id_penyakit, id_kelurahan, nik_pasien, nama_pasien, 
                        jenis_kelamin, tanggal_lahir, tanggal_lapor, tanggal_onset, status_kasus
                    ) VALUES (
                        '$id_pengguna', '$id_penyakit', '$id_kelurahan', '$nik_pasien', '$nama_pasien', 
                        '$jenis_kelamin', $tanggal_lahir, '$tanggal_lapor', $tanggal_onset, '$status_kasus'
                    )";

    // 3. Eksekusi
    if (mysqli_query($koneksi, $query_insert)) {
        
        // ================================================================
        // BLOK PENCATATAN LOG AKTIVITAS (AUDIT TRAIL) SUPER KETAT
        // ================================================================
        $q_info = mysqli_query($koneksi, "
            SELECT p.nama_populer, k.nama_kelurahan 
            FROM tabel_penyakit p, tabel_kelurahan k 
            WHERE p.id_penyakit = '$id_penyakit' AND k.id_kelurahan = '$id_kelurahan'
        ");
        
        if ($info = mysqli_fetch_assoc($q_info)) {
            $nama_penyakit = $info['nama_populer'];
            $nama_kelurahan = $info['nama_kelurahan'];
            
            $deskripsi_log = "Menambahkan data kasus baru atas nama $nama_pasien (Kel. $nama_kelurahan) dengan diagnosis $nama_penyakit.";
            
            $query_log = "INSERT INTO tabel_riwayat_aktivitas (id_pengguna, jenis_aktivitas, deskripsi) 
                          VALUES ('$id_pengguna', 'Tambah Kasus', '$deskripsi_log')";
            
            // JIKA GAGAL MENCATAT LOG, SISTEM AKAN BERHENTI DAN MENAMPILKAN PESAN ERROR
            mysqli_query($koneksi, $query_log) or die("GAGAL MENCATAT LOG: " . mysqli_error($koneksi));
        } else {
            die("GAGAL MENGAMBIL DATA PENYAKIT/KELURAHAN UNTUK LOG!");
        }
        // ================================================================

        // Trigger EWS
        require_once '../../helpers/ews_trigger.php';
        jalankan_ews_otomatis($koneksi, $id_kelurahan, $id_penyakit);

        $_SESSION['alert_sukses'] = "Data kasus lanjutan atas nama <b>$nama_pasien</b> berhasil dicatat.";
        header("Location: index.php");
        exit;
    } else {
        die("Error Database Kasus: " . mysqli_error($koneksi));
    }

} else {
    header("Location: index.php");
    exit;
}
?>