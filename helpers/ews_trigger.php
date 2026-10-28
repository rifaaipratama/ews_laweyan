<?php
// helpers/ews_trigger.php

/**
 * Fungsi untuk mengecek dan memicu Alert EWS secara otomatis
 * Dilengkapi dengan logika perbandingan periode (Time-Series) dan Explainability
 * 
 * @param mysqli $koneksi Objek koneksi database aktif
 * @param int|string $id_kelurahan ID lokasi kelurahan dari kasus
 * @param int|string $id_penyakit ID jenis penyakit dari kasus
 * @return bool Mengembalikan true jika alert baru ditembakkan, false jika aman
 */
function jalankan_ews_otomatis(mysqli $koneksi, $id_kelurahan, $id_penyakit) {
    
    // 1. Cek apakah penyakit ini sedang dipantau (status_pantau = 'aktif')
    $q_penyakit = mysqli_query($koneksi, "SELECT nama_populer FROM tabel_penyakit WHERE id_penyakit = '$id_penyakit' AND status_pantau = 'aktif'");
    if(mysqli_num_rows($q_penyakit) == 0) return false;
    
    $penyakit = mysqli_fetch_assoc($q_penyakit);
    $nama_penyakit = $penyakit['nama_populer'];

    // 2. Ambil parameter batas threshold dari database
    $q_threshold = mysqli_query($koneksi, "SELECT batas_waspada, batas_wabah, periode_hari FROM tabel_pengaturan_threshold WHERE id_penyakit = '$id_penyakit'");
    if(mysqli_num_rows($q_threshold) == 0) return false;
    
    $threshold = mysqli_fetch_assoc($q_threshold);
    $batas_waspada = (int)$threshold['batas_waspada'];
    $batas_wabah   = (int)$threshold['batas_wabah'];
    $periode_hari  = (int)$threshold['periode_hari'];

    // 3. Hitung jumlah kasus periode SAAT INI (misal: 7 hari terakhir)
    $q_kasus_aktual = mysqli_query($koneksi, "
        SELECT COUNT(id_kasus) as total_kasus 
        FROM tabel_kasus 
        WHERE id_kelurahan = '$id_kelurahan' 
        AND id_penyakit = '$id_penyakit' 
        AND status_kasus IN ('terkonfirmasi', 'suspek')
        AND tanggal_lapor >= DATE_SUB(CURDATE(), INTERVAL $periode_hari DAY)
    ");
    $data_aktual = mysqli_fetch_assoc($q_kasus_aktual);
    $jumlah_kasus_aktual = (int)$data_aktual['total_kasus'];

    // 4. Hitung jumlah kasus periode SEBELUMNYA (misal: hari ke-14 s/d hari ke-8 yang lalu)
    $q_kasus_sebelumnya = mysqli_query($koneksi, "
        SELECT COUNT(id_kasus) as total_kasus 
        FROM tabel_kasus 
        WHERE id_kelurahan = '$id_kelurahan' 
        AND id_penyakit = '$id_penyakit' 
        AND status_kasus IN ('terkonfirmasi', 'suspek')
        AND tanggal_lapor >= DATE_SUB(CURDATE(), INTERVAL ($periode_hari * 2) DAY)
        AND tanggal_lapor < DATE_SUB(CURDATE(), INTERVAL $periode_hari DAY)
    ");
    $data_sebelumnya = mysqli_fetch_assoc($q_kasus_sebelumnya);
    $kasus_periode_sebelumnya = (int)$data_sebelumnya['total_kasus'];

    // 5. Tentukan tingkat bahaya dan ambang batas yang tertembus
    $tingkat_alert = '';
    $pesan_alert = '';
    $ambang_batas_digunakan = 0;
    
    if ($jumlah_kasus_aktual >= $batas_wabah) {
        $tingkat_alert = 'potensi_wabah';
        $pesan_alert = "POTENSI WABAH $nama_penyakit";
        $ambang_batas_digunakan = $batas_wabah;
    } elseif ($jumlah_kasus_aktual >= $batas_waspada) {
        $tingkat_alert = 'waspada';
        $pesan_alert = "WASPADA $nama_penyakit";
        $ambang_batas_digunakan = $batas_waspada;
    } else {
        return false; // Aman, belum menyentuh batas apapun
    }

    // 6. Hitung Persentase Kenaikan
    if ($kasus_periode_sebelumnya == 0) {
        // Jika sebelumnya 0 dan sekarang ada kasus (menembus threshold), anggap lonjakan 100%
        $persentase_kenaikan = 100.00; 
    } else {
        $kalkulasi = (($jumlah_kasus_aktual - $kasus_periode_sebelumnya) / $kasus_periode_sebelumnya) * 100;
        $persentase_kenaikan = round($kalkulasi, 2);
    }

    // 7. Anti-Spam: Pastikan tidak mengirim alert yang sama berkali-kali di hari yang sama
    $q_cek_alert = mysqli_query($koneksi, "
        SELECT id_alert FROM tabel_alert 
        WHERE id_kelurahan = '$id_kelurahan' 
        AND id_penyakit = '$id_penyakit' 
        AND tingkat_alert = '$tingkat_alert' 
        AND DATE(created_at) = CURDATE()
    ");

    if(mysqli_num_rows($q_cek_alert) == 0) {
        // 8. TEMBAKKAN PERINGATAN! (Insert dengan status 'menunggu' verifikasi Admin)
        $query_insert_alert = "
            INSERT INTO tabel_alert (
                id_kelurahan, 
                id_penyakit, 
                tingkat_alert, 
                pesan_alert, 
                status_baca,
                jumlah_kasus_aktual,
                ambang_batas_digunakan,
                periode_hari_digunakan,
                kasus_periode_sebelumnya,
                persentase_kenaikan,
                status_verifikasi
            ) VALUES (
                '$id_kelurahan', 
                '$id_penyakit', 
                '$tingkat_alert', 
                '$pesan_alert', 
                'belum',
                '$jumlah_kasus_aktual',
                '$ambang_batas_digunakan',
                '$periode_hari',
                '$kasus_periode_sebelumnya',
                '$persentase_kenaikan',
                'menunggu'
            )
        ";
        mysqli_query($koneksi, $query_insert_alert);
        return true; 
    }
    
    return false;
}

/**
 * Fungsi untuk mendeteksi penurunan kasus (Kondisi Membaik)
 * Dipanggil saat ada perubahan status pasien menjadi 'sembuh' atau 'meninggal'
 */
function jalankan_ews_penurunan(mysqli $koneksi, int|string $id_kelurahan, int|string $id_penyakit) {
    
    // 1. Ambil nama penyakit dan ambang batas (seperti biasa)
    $q_penyakit = mysqli_query($koneksi, "SELECT nama_populer FROM tabel_penyakit WHERE id_penyakit = '$id_penyakit' AND status_pantau = 'aktif'");
    if(mysqli_num_rows($q_penyakit) == 0) return false;
    $nama_penyakit = mysqli_fetch_assoc($q_penyakit)['nama_populer'];

    $q_threshold = mysqli_query($koneksi, "SELECT batas_waspada, periode_hari FROM tabel_pengaturan_threshold WHERE id_penyakit = '$id_penyakit'");
    if(mysqli_num_rows($q_threshold) == 0) return false;
    
    $threshold = mysqli_fetch_assoc($q_threshold);
    $batas_waspada = (int)$threshold['batas_waspada'];
    $periode_hari  = (int)$threshold['periode_hari'];

    // 2. Hitung jumlah kasus AKTIF (hanya Suspek & Terkonfirmasi) saat ini
    $q_kasus_aktif = mysqli_query($koneksi, "
        SELECT COUNT(id_kasus) as total_kasus 
        FROM tabel_kasus 
        WHERE id_kelurahan = '$id_kelurahan' 
        AND id_penyakit = '$id_penyakit' 
        AND status_kasus IN ('terkonfirmasi', 'suspek')
        AND tanggal_lapor >= DATE_SUB(CURDATE(), INTERVAL $periode_hari DAY)
    ");
    $jumlah_kasus_aktual = (int)mysqli_fetch_assoc($q_kasus_aktif)['total_kasus'];

    // 3. Logika Penurunan: Jika kasus aktif SEKARANG berada DI BAWAH batas waspada
    if ($jumlah_kasus_aktual < $batas_waspada) {
        
        $tingkat_alert = 'penurunan';
        $pesan_alert = "KONDISI MEMBAIK $nama_penyakit";

        // Anti-Spam: Cek apakah hari ini sudah ada notif "Kondisi Membaik" untuk wilayah ini
        $q_cek = mysqli_query($koneksi, "
            SELECT id_alert FROM tabel_alert 
            WHERE id_kelurahan = '$id_kelurahan' 
            AND id_penyakit = '$id_penyakit' 
            AND tingkat_alert = '$tingkat_alert' 
            AND DATE(created_at) = CURDATE()
        ");

        if(mysqli_num_rows($q_cek) == 0) {
            // Tembakkan Notifikasi Hijau! (Otomatis valid, tidak butuh verifikasi)
            $query_insert = "
                INSERT INTO tabel_alert (
                    id_kelurahan, id_penyakit, tingkat_alert, pesan_alert, status_baca,
                    jumlah_kasus_aktual, ambang_batas_digunakan, periode_hari_digunakan,
                    kasus_periode_sebelumnya, persentase_kenaikan, status_verifikasi
                ) VALUES (
                    '$id_kelurahan', '$id_penyakit', '$tingkat_alert', '$pesan_alert', 'belum',
                    '$jumlah_kasus_aktual', '$batas_waspada', '$periode_hari',
                    '0', '0', 'valid' 
                )
            ";
            mysqli_query($koneksi, $query_insert);
            return true;
        }
    }
    return false;
}
?>