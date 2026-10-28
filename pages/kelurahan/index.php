<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
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

// === LOGIKA FILTER PENCARIAN PINTAR ===
$keyword_cari = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';

$where_sql = "";
if (!empty($keyword_cari)) {
    $where_sql = "WHERE (nama_kelurahan LIKE '%$keyword_cari%' OR kodepos LIKE '%$keyword_cari%' OR kode_wilayah LIKE '%$keyword_cari%')";
}

// === LOGIKA TARIK STATUS ZONA (EWS REAL-TIME) ===
$status_wilayah = [];
$q_alert = mysqli_query($koneksi, "
    SELECT id_kelurahan, tingkat_alert 
    FROM tabel_alert 
    WHERE status_verifikasi != 'abaikan' AND status_baca = 'belum'
    ORDER BY created_at ASC
");
while ($row = mysqli_fetch_assoc($q_alert)) {
    $id_kel = $row['id_kelurahan'];
    $lvl = $row['tingkat_alert'];
    if (!isset($status_wilayah[$id_kel])) {
        $status_wilayah[$id_kel] = ($lvl == 'potensi_wabah') ? 'merah' : 'kuning';
    } else {
        if ($lvl == 'potensi_wabah') $status_wilayah[$id_kel] = 'merah';
    }
}
// ======================================

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
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1rem;
    }
    
    .table-custom tbody td {
        background-color: var(--bg-panel);
        color: var(--text-color) !important;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
        padding: 1rem;
    }

    [data-theme="light"] .table-custom.table-hover tbody tr:hover td { background-color: rgba(13, 110, 253, 0.02); }
    [data-theme="dark"] .table-custom.table-hover tbody tr:hover td { background-color: rgba(255, 255, 255, 0.02); }

    .text-dynamic { color: var(--text-color) !important; }

    /* Gaya Kotak Filter Modern */
    .filter-box {
        background-color: rgba(13, 110, 253, 0.03);
        border: 1px solid rgba(13, 110, 253, 0.15);
        border-radius: 12px;
    }
    [data-theme="dark"] .filter-box {
        background-color: rgba(255, 255, 255, 0.02);
        border-color: rgba(255, 255, 255, 0.05);
    }

    /* Badge Status Zona EWS */
    .badge-zona { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px; padding: 6px 12px; }
    .zona-merah { background: rgba(220,53,69,0.1); color: #dc3545; border: 1px solid rgba(220,53,69,0.3); }
    .zona-kuning { background: rgba(255,193,7,0.1); color: #e0a800; border: 1px solid rgba(255,193,7,0.3); }
    .zona-hijau { background: rgba(25,135,84,0.1); color: #198754; border: 1px solid rgba(25,135,84,0.3); }
</style>

<div id="main-content">

    <?php if (isset($_SESSION['alert_sukses'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['alert_sukses']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['alert_sukses']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['alert_gagal'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $_SESSION['alert_gagal']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['alert_gagal']); ?>
    <?php endif; ?>

    <?php include '../../layout/topbar.php'; ?>

    <div class="row mb-4 align-items-center mt-2">
        <div class="col-md-8">
            <h4 class="text-dynamic fw-bold mb-1"><i class="bi bi-pin-map-fill me-2 text-primary"></i> Data Kelurahan & Status Zona</h4>
            <p class="text-dynamic mb-0" style="opacity: 0.75; font-size: 0.9rem;">Kelola master data kependudukan dan pantau status zona EWS wilayah administratif Kecamatan Laweyan.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <!-- Tombol Tambah DIHAPUS. Diganti dengan Indikator Status -->
            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-3 py-2 rounded-pill shadow-sm">
                <i class="bi bi-lock-fill me-1"></i> Data Master Terkunci
            </span>
        </div>
    </div>

    <!-- PANEL FILTER PINTAR -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-box p-3 shadow-sm">
                <form action="index.php" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label small fw-semibold text-dynamic mb-1">Pencarian Wilayah (Kelurahan / Kode Pos / Kode Wilayah)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" name="cari" placeholder="Ketik kata kunci pencarian wilayah..." value="<?= htmlspecialchars($keyword_cari); ?>" autofocus>
                        </div>
                    </div>
                    
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 fw-semibold shadow-sm"><i class="bi bi-funnel-fill me-1"></i> Cari</button>
                        <?php if(!empty($keyword_cari)): ?>
                            <a href="index.php" class="btn btn-outline-secondary w-100 fw-semibold shadow-sm" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="panel-card p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="15%">Kode Wilayah</th>
                                <th width="25%">Nama Kelurahan</th>
                                <th width="15%">Kode Pos</th>
                                <th width="15%">Populasi</th>
                                <th width="15%" class="text-center">Status Zona (EWS)</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = mysqli_query($koneksi, "SELECT * FROM tabel_kelurahan $where_sql ORDER BY nama_kelurahan ASC");
                            
                            if (mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    $id_kel = $row['id_kelurahan'];
                                    $populasi = number_format($row['jumlah_penduduk'], 0, ',', '.');
                                    
                                    // Logika Penentuan Badge Status Zona
                                    $zona_badge = '<span class="badge rounded-pill badge-zona zona-hijau"><i class="bi bi-shield-check me-1"></i> AMAN</span>';
                                    if (isset($status_wilayah[$id_kel])) {
                                        if ($status_wilayah[$id_kel] == 'merah') {
                                            $zona_badge = '<span class="badge rounded-pill badge-zona zona-merah"><i class="bi bi-exclamation-octagon-fill me-1"></i> BAHAYA</span>';
                                        } elseif ($status_wilayah[$id_kel] == 'kuning') {
                                            $zona_badge = '<span class="badge rounded-pill badge-zona zona-kuning"><i class="bi bi-exclamation-triangle-fill me-1"></i> WASPADA</span>';
                                        }
                                    }

                                    echo "<tr>
                                            <td class='text-center text-muted'>{$no}</td>
                                            <td class='fw-bold' style='color: #0aa2c0;'>{$row['kode_wilayah']}</td>
                                            <td class='fw-bold text-dynamic' style='font-size: 1.05rem;'>{$row['nama_kelurahan']}</td>
                                            <td><span class='badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-2'>{$row['kodepos']}</span></td>
                                            <td class='fw-medium'><i class='bi bi-people-fill text-muted me-1'></i> {$populasi} <small class='text-muted'>Jiwa</small></td>
                                            <td class='text-center'>{$zona_badge}</td>
                                            <td class='text-center'>
                                                <div class='btn-group shadow-sm'>
                                                    <a href='detail_kelurahan.php?id={$id_kel}' class='btn btn-sm btn-outline-info' title='Detail Wilayah'><i class='bi bi-eye-fill'></i></a>
                                                    <a href='edit_kelurahan.php?id={$id_kel}' class='btn btn-sm btn-outline-warning' title='Update Populasi'><i class='bi bi-pencil-square'></i></a>
                                                    <!-- Tombol Hapus Dihilangkan -->
                                                </div>
                                            </td>
                                          </tr>";
                                    $no++;
                                }
                            } else {
                                echo "<tr>
                                        <td colspan='7' class='text-center py-5 text-dynamic' style='opacity: 0.6;'>
                                            <i class='bi bi-map display-6 d-block mb-3 opacity-50'></i>
                                            <h6 class='fw-bold mb-1'>Data Tidak Ditemukan</h6>
                                            <span class='small'>Tidak ada kelurahan yang cocok dengan pencarian Anda.</span>
                                        </td>
                                      </tr>";
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