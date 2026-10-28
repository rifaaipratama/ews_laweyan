<?php
session_start();
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Header agar browser mengenali file sebagai cetak PDF/HTML
header("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Ringkasan EWS Laweyan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h2 { text-align: center; margin-bottom: 5px; }
        p.subtitle { text-align: center; margin-top: 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { margin-top: 30px; text-align: right; font-style: italic; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <h2>LAPORAN SURVEILANS KESEHATAN (SMART EWS)</h2>
    <p class="subtitle">Kecamatan Laweyan - Tanggal Cetak: <?= date('d-m-Y H:i') ?></p>
    <hr>

    <h4>Ringkasan Data Kasus Aktif</h4>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Lapor</th>
                <th>Nama Pasien</th>
                <th>Penyakit</th>
                <th>Kelurahan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q = mysqli_query($koneksi, "
                SELECT k.tanggal_lapor, k.nama_pasien, p.nama_populer, kel.nama_kelurahan, k.status_kasus
                FROM tabel_kasus k
                JOIN tabel_penyakit p ON k.id_penyakit = p.id_penyakit
                JOIN tabel_kelurahan kel ON k.id_kelurahan = kel.id_kelurahan
                WHERE k.is_deleted = 0 AND k.status_kasus IN ('suspek', 'terkonfirmasi')
                ORDER BY k.id_kasus DESC
            ");
            
            if (mysqli_num_rows($q) > 0) {
                $no = 1;
                while ($r = mysqli_fetch_assoc($q)) {
                    echo "<tr>
                        <td>{$no}</td>
                        <td>{$r['tanggal_lapor']}</td>
                        <td>{$r['nama_pasien']}</td>
                        <td>{$r['nama_populer']}</td>
                        <td>{$r['nama_kelurahan']}</td>
                        <td>{$r['status_kasus']}</td>
                    </tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>Tidak ada data kasus aktif saat ini.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak secara otomatis oleh Sistem SMART EWS Laweyan</p>
    </div>
</body>
</html>