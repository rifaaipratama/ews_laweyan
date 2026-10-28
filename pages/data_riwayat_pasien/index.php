<?php
session_start();

if (!isset($_SESSION['id_pengguna']) || $_SESSION['id_role'] != 1) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Ambil data untuk Header/Sidebar
$id_role      = $_SESSION['id_role'];
$nama_lengkap = $_SESSION['nama_lengkap'];

$query_role = mysqli_query($koneksi, "SELECT nama_role FROM tabel_role WHERE id_role = '$id_role'");
$nama_role  = ($row = mysqli_fetch_assoc($query_role)) ? $row['nama_role'] : "User";

// === LOGIKA FILTER PENCARIAN ===
$keyword_cari  = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';
$filter_gender = isset($_GET['gender']) ? mysqli_real_escape_string($koneksi, $_GET['gender']) : '';
$filter_lokasi = isset($_GET['lokasi']) ? mysqli_real_escape_string($koneksi, $_GET['lokasi']) : '';

// Susun string klausa WHERE secara dinamis
$where_clauses = [];
if (!empty($keyword_cari)) {
    $where_clauses[] = "(nik_pasien LIKE '%$keyword_cari%' OR nama_pasien LIKE '%$keyword_cari%')";
}
if (!empty($filter_gender)) {
    $where_clauses[] = "jenis_kelamin = '$filter_gender'";
}
if (!empty($filter_lokasi)) {
    $where_clauses[] = "id_kelurahan = '$filter_lokasi'"; // Asumsi tabel_kasus mencatat id_kelurahan domisili saat pendaftaran
}

// Gabungkan klausa WHERE jika ada filter yang aktif
$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}
// ===============================

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>

<style>
    .panel-card {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 4px 20px var(--shadow-color);
    }
    
    .table-custom {
        --bs-table-color: var(--text-color);
        --bs-table-bg: transparent;
        --bs-table-border-color: var(--border-color);
        --bs-table-hover-color: var(--text-color);
    }
    
    .table-custom thead th {
        background-color: var(--bg-body);
        color: var(--text-color);
        border-bottom: 2px solid var(--border-color);
        font-weight: 600;
        white-space: nowrap;
    }
    
    .table-custom tbody td {
        background-color: var(--bg-panel);
        color: var(--text-color) !important;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }

    [data-theme="light"] .table-custom.table-hover tbody tr:hover td { background-color: rgba(0, 0, 0, 0.03); }
    [data-theme="dark"] .table-custom.table-hover tbody tr:hover td { background-color: rgba(255, 255, 255, 0.05); }

    /* Styling khusus untuk form filter */
    .filter-box {
        background-color: rgba(13, 202, 240, 0.05); /* Soft Cyan background */
        border: 1px solid rgba(13, 202, 240, 0.2);
        border-radius: 10px;
    }
</style>

<div id="main-content">

    <?php include '../../layout/topbar.php'; ?>

    <!-- HEADER & TOMBOL TAMBAH PASIEN -->
    <div class="row mb-4 align-items-center mt-2">
        <div class="col-md-8">
            <h4 class="text-dynamic fw-bold mb-1">
                <i class="bi bi-person-lines-fill me-2 text-info"></i> Data Riwayat Pasien
            </h4>
            <p class="mb-0" style="color: var(--text-color); opacity: 0.75; font-size: 0.9rem;">
                Buku Induk rekam jejak historis pasien yang pernah terdaftar dalam sistem EWS.
            </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <!-- Tombol Tambah Pasien Baru diarahkan ke form pendaftaran -->
            <a href="tambah_pasien.php" class="btn btn-info text-white shadow-sm fw-medium">
                <i class="bi bi-person-plus-fill me-1"></i> Daftarkan Pasien Baru
            </a>
        </div>
    </div>

    <!-- PANEL FILTER & PENCARIAN -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-box p-3">
                <form action="index.php" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-dynamic mb-1">Cari NIK / Nama Pasien</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" name="cari" placeholder="Ketik NIK atau Nama..." value="<?= htmlspecialchars($keyword_cari); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-dynamic mb-1">Filter Gender</label>
                        <select class="form-select" name="gender">
                            <option value="">Semua Jenis Kelamin</option>
                            <option value="L" <?= ($filter_gender == 'L') ? 'selected' : ''; ?>>Laki-laki (L)</option>
                            <option value="P" <?= ($filter_gender == 'P') ? 'selected' : ''; ?>>Perempuan (P)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-dynamic mb-1">Filter Kelurahan</label>
                        <select class="form-select" name="lokasi">
                            <option value="">Semua Wilayah</option>
                            <?php
                            $q_kel = mysqli_query($koneksi, "SELECT * FROM tabel_kelurahan ORDER BY nama_kelurahan ASC");
                            while ($kel = mysqli_fetch_assoc($q_kel)) {
                                $selected = ($filter_lokasi == $kel['id_kelurahan']) ? 'selected' : '';
                                echo "<option value='{$kel['id_kelurahan']}' $selected>{$kel['nama_kelurahan']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                        <?php if(!empty($keyword_cari) || !empty($filter_gender) || !empty($filter_lokasi)): ?>
                            <!-- Tombol Reset hanya muncul jika ada filter yang aktif -->
                            <a href="index.php" class="btn btn-outline-secondary w-100" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TABEL DATA PASIEN -->
    <div class="row">
        <div class="col-12">
            <div class="panel-card p-4">
                <div class="table-responsive">
                    <table class="table table-custom table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIK Pasien</th>
                                <th>Nama Lengkap</th>
                                <th>L/P</th>
                                <th>Usia</th>
                                <th>Total Kunjungan</th>
                                <th>Kunjungan Terakhir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query Cerdas: Mengelompokkan data berdasarkan NIK (GROUP BY) + Klausa WHERE dari Filter
                            $query_pasien = "
                                SELECT 
                                    nik_pasien, 
                                    nama_pasien, 
                                    jenis_kelamin, 
                                    tanggal_lahir, 
                                    COUNT(id_kasus) as total_riwayat, 
                                    MAX(tanggal_lapor) as kunjungan_terakhir
                                FROM tabel_kasus 
                                $where_sql
                                GROUP BY nik_pasien, nama_pasien, jenis_kelamin, tanggal_lahir
                                ORDER BY kunjungan_terakhir DESC
                            ";
                            
                            $result_pasien = mysqli_query($koneksi, $query_pasien);
                            $no = 1;

                            if ($result_pasien && mysqli_num_rows($result_pasien) > 0) {
                                while ($row = mysqli_fetch_assoc($result_pasien)) {
                                    
                                    // Logika Keamanan Data: Masking NIK
                                    $nik_asli = htmlspecialchars($row['nik_pasien']);
                                    if (strlen($nik_asli) == 16) {
                                        $nik_masking = substr($nik_asli, 0, 6) . 'xxxxxx' . substr($nik_asli, 12, 4);
                                    } else {
                                        $nik_masking = $nik_asli; // Fallback jika format salah
                                    }

                                    // Hitung Usia Pasien
                                    $usia = "-";
                                    if (!empty($row['tanggal_lahir'])) {
                                        $tgl_lahir = new DateTime($row['tanggal_lahir']);
                                        $hari_ini = new DateTime('today');
                                        $usia = $hari_ini->diff($tgl_lahir)->y . " Thn";
                                    }

                                    // Badge Jenis Kelamin
                                    $jk_badge = ($row['jenis_kelamin'] == 'L') ? 
                                                '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2">L</span>' : 
                                                '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2">P</span>';

                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td class='text-muted' style='font-family: monospace;'>" . $nik_masking . "</td>";
                                    echo "<td class='fw-semibold'>" . htmlspecialchars($row['nama_pasien']) . "</td>";
                                    echo "<td>" . $jk_badge . "</td>";
                                    echo "<td>" . $usia . "</td>";
                                    echo "<td><span class='badge bg-secondary rounded-pill px-3'>" . $row['total_riwayat'] . " Kali</span></td>";
                                    echo "<td>" . date('d M Y', strtotime($row['kunjungan_terakhir'])) . "</td>";
                                    
                                    echo "<td>
                                            <a href='detail_riwayat.php?nik=" . urlencode($row['nik_pasien']) . "' class='btn btn-sm btn-info text-white shadow-sm' style='font-size: 0.85rem;'>
                                                <i class='bi bi-folder2-open me-1'></i> Buka Rekam Medis
                                            </a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center text-muted py-5'>
                                        <i class='bi bi-search display-6 d-block mb-3 opacity-50'></i>
                                        Tidak ada data pasien yang cocok dengan pencarian Anda.
                                      </td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../layout/footer.php'; ?>

<!-- TOAST NOTIFIKASI SUKSES (MELAYANG DI POJOK KANAN ATAS) -->
    <?php if (isset($_SESSION['alert_sukses'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-lg" role="alert" style="position: fixed; top: 80px; right: 20px; z-index: 9999; border-radius: 10px; border-left: 5px solid #198754; min-width: 350px; max-width: 500px; animation: slideInDown 0.4s ease-out;">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div><?php echo $_SESSION['alert_sukses']; ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="top: 50%; transform: translateY(-50%);"></button>
        </div>
        <style>
            @keyframes slideInDown {
                from { transform: translateY(-100%); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        </style>
        <?php unset($_SESSION['alert_sukses']); ?>
    <?php endif; ?>
    <!-- AKHIR TOAST NOTIFIKASI -->