<?php
session_start();

// PERBAIKAN: Hapus pembatasan $_SESSION['id_role'] != 1 agar Petugas bisa memproses data
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Ambil ID pengguna langsung dari session demi keamanan
    $id_pengguna   = $_SESSION['id_pengguna'];
    
    // Ambil dan Bersihkan Data Wajib
    $nik_pasien    = mysqli_real_escape_string($koneksi, $_POST['nik_pasien']);
    $nama_pasien   = mysqli_real_escape_string($koneksi, $_POST['nama_pasien']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $id_kelurahan  = mysqli_real_escape_string($koneksi, $_POST['id_kelurahan']);
    $id_penyakit   = mysqli_real_escape_string($koneksi, $_POST['id_penyakit']);
    $status_kasus  = mysqli_real_escape_string($koneksi, $_POST['status_kasus']);
    $tanggal_lapor = mysqli_real_escape_string($koneksi, $_POST['tanggal_lapor']);

    // 2. Penanganan Khusus untuk Data Opsional
    $tanggal_lahir  = !empty($_POST['tanggal_lahir']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']) . "'" : "NULL";
    $tanggal_onset  = !empty($_POST['tanggal_onset']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['tanggal_onset']) . "'" : "NULL";
    $no_telepon     = !empty($_POST['no_telepon']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['no_telepon']) . "'" : "NULL";
    $alamat_lengkap = !empty($_POST['alamat_lengkap']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['alamat_lengkap']) . "'" : "NULL";

    // 3. Susun Query Insert 
    $query_insert = "INSERT INTO tabel_kasus (
                        id_pengguna, 
                        id_penyakit, 
                        id_kelurahan, 
                        nik_pasien, 
                        nama_pasien, 
                        jenis_kelamin, 
                        tanggal_lahir,
                        no_telepon,
                        alamat_lengkap,
                        tanggal_lapor, 
                        tanggal_onset, 
                        status_kasus
                    ) VALUES (
                        '$id_pengguna', 
                        '$id_penyakit', 
                        '$id_kelurahan', 
                        '$nik_pasien', 
                        '$nama_pasien', 
                        '$jenis_kelamin', 
                        $tanggal_lahir, 
                        $no_telepon,
                        $alamat_lengkap,
                        '$tanggal_lapor', 
                        $tanggal_onset, 
                        '$status_kasus'
                    )";

    // 4. Eksekusi Query
    if (mysqli_query($koneksi, $query_insert)) {
        
        // ================================================================
        // BLOK PENCATATAN LOG AKTIVITAS (AUDIT TRAIL) PASIEN BARU
        // ================================================================
        $q_info = mysqli_query($koneksi, "
            SELECT p.nama_populer, k.nama_kelurahan 
            FROM tabel_penyakit p, tabel_kelurahan k 
            WHERE p.id_penyakit = '$id_penyakit' AND k.id_kelurahan = '$id_kelurahan'
        ");
        
        if ($info = mysqli_fetch_assoc($q_info)) {
            $nama_penyakit = $info['nama_populer'];
            $nama_kelurahan = $info['nama_kelurahan'];
            
            $deskripsi_log = "Mendaftarkan pasien baru atas nama $nama_pasien (Kel. $nama_kelurahan) dengan diagnosis awal $nama_penyakit.";
            
            $query_log = "INSERT INTO tabel_riwayat_aktivitas (id_pengguna, jenis_aktivitas, deskripsi) 
                          VALUES ('$id_pengguna', 'Tambah Pasien', '$deskripsi_log')";
            
            mysqli_query($koneksi, $query_log) or die("GAGAL MENCATAT LOG PASIEN BARU: " . mysqli_error($koneksi));
        }
        // ================================================================

// -------------------------------------------------------------
        // TRIGGER OTOMATIS EWS
        // -------------------------------------------------------------
        require_once '../../helpers/ews_trigger.php';
        jalankan_ews_otomatis($koneksi, $id_kelurahan, $id_penyakit);
        // -------------------------------------------------------------

        $_SESSION['alert_sukses'] = "Pasien atas nama <b>$nama_pasien</b> berhasil didaftarkan dan data SIMRS awal telah dibuat.";
        
       // ... (kode ews_trigger tetap sama)

        // LOGIKA REDIRECT CERDAS BERDASARKAN ROLE (METODE URL PARAMETER)
        if ($_SESSION['id_role'] == 1) {
            header("Location: index.php?status=sukses&nama=" . urlencode($nama_pasien));
        } else {
            header("Location: ../kasus/tambah_kasus.php?status=sukses&nama=" . urlencode($nama_pasien));
        }
        exit;
        
    } else {
        die("Error Database Pendaftaran Pasien: " . mysqli_error($koneksi));
    }
// ...

} else {
    // Jika diakses tanpa POST
    if ($_SESSION['id_role'] == 1) {
        header("Location: index.php");
    } else {
        header("Location: ../kasus/tambah_kasus.php");
    }
    exit;
}
?>