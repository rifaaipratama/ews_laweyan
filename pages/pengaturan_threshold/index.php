<?php
session_start();

// PROTEKSI GANDA: Harus login DAN harus Administrator (id_role = 1)
if (!isset($_SESSION['id_pengguna']) || $_SESSION['id_role'] != 1) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Ambil data untuk Header/Sidebar
$id_role      = $_SESSION['id_role'];
$nama_lengkap = $_SESSION['nama_lengkap'];

$query_role_login = mysqli_query($koneksi, "SELECT nama_role FROM tabel_role WHERE id_role = '$id_role'");
$nama_role  = ($row = mysqli_fetch_assoc($query_role_login)) ? $row['nama_role'] : "User";

// --- LOGIKA FILTER & PENCARIAN ---
$search_query   = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$filter_waspada = isset($_GET['waspada']) ? mysqli_real_escape_string($koneksi, $_GET['waspada']) : '';
$filter_wabah   = isset($_GET['wabah']) ? mysqli_real_escape_string($koneksi, $_GET['wabah']) : '';
$filter_periode = isset($_GET['periode']) ? mysqli_real_escape_string($koneksi, $_GET['periode']) : '';

$where_clause = "1=1";
if (!empty($search_query)) {
    $where_clause .= " AND (u.nama_lengkap LIKE '%$search_query%' OR p.nama_populer LIKE '%$search_query%')";
}
if (!empty($filter_waspada)) {
    $where_clause .= " AND pt.batas_waspada = '$filter_waspada'";
}
if (!empty($filter_wabah)) {
    $where_clause .= " AND pt.batas_wabah = '$filter_wabah'";
}
if (!empty($filter_periode)) {
    $where_clause .= " AND pt.periode_hari = '$filter_periode'";
}

// Query Utama Threshold (Ditambahkan updated_at & Diurutkan berdasarkan nama penyakit)
$query_threshold = "
    SELECT 
        pt.id_threshold, 
        pt.batas_waspada, 
        pt.batas_wabah, 
        pt.periode_hari,
        pt.updated_at,
        p.nama_populer,
        u.nama_lengkap,
        u.status_aktif
    FROM tabel_pengaturan_threshold pt
    JOIN tabel_penyakit p ON pt.id_penyakit = p.id_penyakit
    JOIN tabel_pengguna u ON pt.id_pengguna = u.id_pengguna
    WHERE $where_clause
    ORDER BY p.nama_populer ASC
";
$result_threshold = mysqli_query($koneksi, $query_threshold);

// Ambil data unik untuk dropdown filter
$q_filter_waspada = mysqli_query($koneksi, "SELECT DISTINCT batas_waspada FROM tabel_pengaturan_threshold ORDER BY batas_waspada ASC");
$q_filter_wabah   = mysqli_query($koneksi, "SELECT DISTINCT batas_wabah FROM tabel_pengaturan_threshold ORDER BY batas_wabah ASC");
$q_filter_periode = mysqli_query($koneksi, "SELECT DISTINCT periode_hari FROM tabel_pengaturan_threshold ORDER BY periode_hari ASC");

$title = "Pengaturan Threshold";
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

    /* Custom Input Search & Filter */
    .filter-control {
        background-color: var(--bg-body);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
    .filter-control:focus {
        background-color: var(--bg-body);
        color: var(--text-color);
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    .input-group-text.filter-bg {
        background-color: var(--bg-body);
        border-color: var(--border-color);
        color: var(--text-muted);
    }
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

    <!-- HEADER HALAMAN -->
    <div class="row mb-4 align-items-center mt-2">
        <div class="col-md-7">
            <h4 class="text-dynamic fw-bold mb-1"><i class="bi bi-sliders me-2 text-primary"></i> Pengaturan Threshold EWS</h4>
            <p class="text-dynamic mb-0" style="opacity: 0.75; font-size: 0.9rem;">Kelola ambang batas algoritma deteksi wabah secara global berdasarkan penyakit.</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <a href="tambah_pengaturan.php" class="btn btn-primary shadow-sm fw-medium rounded-pill px-4">
                <i class="bi bi-gear-fill me-1"></i> Atur Threshold
            </a>
        </div>
    </div>

    <!-- PANEL PENCARIAN & FILTER -->
    <div class="panel-card p-3 mb-4">
        <form method="GET" action="index.php" class="row g-3 align-items-center">
            
            <!-- Cari Nama Pengguna / Penyakit -->
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text filter-bg border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control filter-control border-start-0 ps-0" name="search" placeholder="Cari Penyakit / Admin..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
            </div>
            
            <!-- Filter Batas Waspada -->
            <div class="col-md-2">
                <select class="form-select filter-control" name="waspada">
                    <option value="">🟡 Semua Waspada</option>
                    <?php while($rw = mysqli_fetch_assoc($q_filter_waspada)): ?>
                        <option value="<?php echo $rw['batas_waspada']; ?>" <?php echo ($filter_waspada == $rw['batas_waspada']) ? 'selected' : ''; ?>>
                            <?php echo $rw['batas_waspada']; ?> Kasus
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Filter Batas Wabah -->
            <div class="col-md-2">
                <select class="form-select filter-control" name="wabah">
                    <option value="">🔴 Semua Wabah</option>
                    <?php while($rb = mysqli_fetch_assoc($q_filter_wabah)): ?>
                        <option value="<?php echo $rb['batas_wabah']; ?>" <?php echo ($filter_wabah == $rb['batas_wabah']) ? 'selected' : ''; ?>>
                            <?php echo $rb['batas_wabah']; ?> Kasus
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Filter Periode -->
            <div class="col-md-2">
                <select class="form-select filter-control" name="periode">
                    <option value="">📅 Semua Periode</option>
                    <?php while($rp = mysqli_fetch_assoc($q_filter_periode)): ?>
                        <option value="<?php echo $rp['periode_hari']; ?>" <?php echo ($filter_periode == $rp['periode_hari']) ? 'selected' : ''; ?>>
                            <?php echo $rp['periode_hari']; ?> Hari
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <!-- Tombol Aksi -->
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-medium shadow-sm"><i class="bi bi-funnel-fill me-1"></i> Terapkan</button>
                
                <?php if(!empty($search_query) || !empty($filter_waspada) || !empty($filter_wabah) || !empty($filter_periode)): ?>
                    <a href="index.php" class="btn btn-outline-danger shadow-sm" title="Reset Filter"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- TABEL DATA -->
    <div class="row">
        <div class="col-12">
            <div class="panel-card p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="25%">Penyakit Pantauan</th>
                                <th width="25%">Terakhir Diperbarui Oleh</th>
                                <th width="15%" class="text-center">Batas Waspada</th>
                                <th width="15%" class="text-center">Batas Wabah</th>
                                <th width="15%" class="text-center">Periode Hitung</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if ($result_threshold && mysqli_num_rows($result_threshold) > 0) {
                                while ($row = mysqli_fetch_assoc($result_threshold)) {
                                    $status_user = ($row['status_aktif'] == 'nonaktif') ? ' <span class="badge bg-danger rounded-pill" style="font-size:0.65rem;">Nonaktif</span>' : '';
                                    
                                    // Format tanggal dari database
                                    $tanggal_update = !empty($row['updated_at']) ? date('d M Y, H:i', strtotime($row['updated_at'])) : '-';
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?php echo $no++; ?></td>
                                    
                                    <td>
                                        <div class="fw-bold" style="color: #0d6efd; font-size: 1.05rem;">
                                            <i class="bi bi-virus me-2"></i><?php echo htmlspecialchars($row['nama_populer']); ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-semibold text-dynamic" style="font-size: 0.95rem;">
                                            <i class="bi bi-person-fill text-muted me-1"></i> <?php echo htmlspecialchars($row['nama_lengkap']) . $status_user; ?>
                                        </div>
                                        <div class="small text-muted mt-1" style="font-size: 0.8rem;">
                                            <i class="bi bi-clock me-1"></i> Diperbarui: <?php echo $tanggal_update; ?>
                                        </div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning rounded-pill px-3 py-2 fw-bold shadow-sm">
                                            <?php echo $row['batas_waspada']; ?> Kasus
                                        </span>
                                    </td>
                                    
                                    <td class="text-center">
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3 py-2 fw-bold shadow-sm">
                                            <?php echo $row['batas_wabah']; ?> Kasus
                                        </span>
                                    </td>
                                    
                                    <td class="text-center">
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3 py-2">
                                            <i class="bi bi-calendar-day me-1"></i> <?php echo $row['periode_hari']; ?> Hari
                                        </span>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-5 text-dynamic' style='opacity: 0.6;'><i class='bi bi-sliders display-6 d-block mb-3 opacity-50'></i>Belum ada data pengaturan threshold yang sesuai.</td></tr>";
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