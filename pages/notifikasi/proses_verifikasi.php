<?php
session_start();

// 1. Proteksi Akses (Keamanan Ekstra: HANYA Admin yang boleh memvalidasi)
if (!isset($_SESSION['id_pengguna']) || $_SESSION['id_role'] != 1) {
    $_SESSION['alert_gagal'] = "Akses Ditolak! Hanya Administrator / Kepala Puskesmas yang memiliki wewenang untuk memvalidasi peringatan sistem.";
    header("Location: index.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// 2. Tangkap Parameter dari URL
if (isset($_GET['id']) && isset($_GET['aksi'])) {
    
    $id_alert = (int)$_GET['id'];
    $aksi = $_GET['aksi']; // Isinya pasti 'valid' atau 'abaikan'
    
    // Ambil data verifikator dari session
    $id_verifikator = $_SESSION['id_pengguna'];
    $waktu_sekarang = date('Y-m-d H:i:s');

    // Filter Keamanan: Pastikan variabel aksi tidak disusupi kata lain (SQL Injection / XSS)
    if (!in_array($aksi, ['valid', 'abaikan'])) {
        $_SESSION['alert_gagal'] = "Aksi tidak dikenal sistem.";
        header("Location: index.php");
        exit;
    }

    // 3. Eksekusi Update ke Database
    $query_update = "UPDATE tabel_alert 
                     SET status_verifikasi = '$aksi', 
                         id_verifikator = '$id_verifikator', 
                         waktu_verifikasi = '$waktu_sekarang' 
                     WHERE id_alert = '$id_alert'";

    if (mysqli_query($koneksi, $query_update)) {
        
        // 4. Siapkan Pesan Sukses Berbeda Berdasarkan Aksi
        if ($aksi == 'valid') {
            $_SESSION['alert_sukses'] = "<i class='bi bi-shield-check me-1'></i> <b>Berhasil!</b> Peringatan dini telah divalidasi dan resmi disiarkan ke seluruh sistem EWS.";
        } else {
            $_SESSION['alert_sukses'] = "<i class='bi bi-x-circle me-1'></i> <b>Berhasil!</b> Peringatan ditandai sebagai <i>False Alarm</i> (Diabaikan) dan disembunyikan dari pantauan.";
        }

    } else {
        $_SESSION['alert_gagal'] = "Error Sistem: Gagal menyimpan data verifikasi ke database.";
    }
}

// 5. Kembalikan ke halaman notifikasi
header("Location: index.php");
exit;
?>