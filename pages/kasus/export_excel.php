<?php
session_start();
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

$filename = "Laporan_EWS_Laweyan_" . date('Y-m-d') . ".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
?>
<table border="1">
    <thead>
        <tr>
            <th colspan="6" style="background-color: #0d6efd; color: white; font-size: 14px;">LAPORAN DATA KASUS SURVEILANS SMART EWS LAWEYAN</th>
        </tr>
        <tr>
            <th>No</th>
            <th>Tanggal Lapor</th>
            <th>Nama Pasien</th>
            <th>Penyakit</th>
            <th>Kelurahan</th>
            <th>Status Kasus</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $q = mysqli_query($koneksi, "
            SELECT k.tanggal_lapor, k.nama_pasien, p.nama_populer, kel.nama_kelurahan, k.status_kasus
            FROM tabel_kasus k
            JOIN tabel_penyakit p ON k.id_penyakit = p.id_penyakit
            JOIN tabel_kelurahan kel ON k.id_kelurahan = kel.id_kelurahan
            WHERE k.is_deleted = 0
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
            echo "<tr><td colspan='6' style='text-align:center;'>Tidak ada data kasus.</td></tr>";
        }
        ?>
    </tbody>
</table>