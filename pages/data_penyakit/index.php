<?php
session_start();

// Akses dibuka untuk semua user yang sudah login
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
$keyword_cari  = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($koneksi, $_GET['status']) : '';

$where_clauses = [];
if (!empty($keyword_cari)) {
    // Cari berdasarkan Nama Populer ATAU Kode ICD ATAU Deskripsi ICD
    $where_clauses[] = "(p.nama_populer LIKE '%$keyword_cari%' OR p.kode_icd LIKE '%$keyword_cari%' OR i.deskripsi_id LIKE '%$keyword_cari%')";
}
if (!empty($filter_status)) {
    $where_clauses[] = "p.status_pantau = '$filter_status'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}
// ======================================

$title = "Master Data Penyakit";
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
            <h4 class="text-dynamic fw-bold mb-1">
                <i class="bi bi-virus me-2 text-danger"></i> Master Data Penyakit
            </h4>
            <p class="text-dynamic mb-0" style="opacity: 0.75; font-size: 0.9rem;">
                Manajemen radar penyakit EWS berdasarkan standar kode diagnosis ICD-10.
            </p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <a href="tambah_penyakit.php" class="btn btn-primary shadow-sm fw-medium rounded-pill px-4">
                <i class="bi bi-plus-lg me-1"></i> Tambah Penyakit Baru
            </a>
        </div>
    </div>

    <!-- PANEL PENCARIAN & FILTER -->
    <div class="panel-card p-3 mb-4">
        <form action="index.php" method="GET" class="row g-3 align-items-center">
            <!-- Cari Penyakit / Kode ICD -->
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text filter-bg border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control filter-control border-start-0 ps-0" name="cari" placeholder="Cari Nama Penyakit atau Kode ICD..." value="<?= htmlspecialchars($keyword_cari); ?>">
                </div>
            </div>
            
            <!-- Filter Status -->
            <div class="col-md-4">
                <select class="form-select filter-control" name="status">
                    <option value="">Semua Status Pantauan</option>
                    <option value="aktif" <?= ($filter_status == 'aktif') ? 'selected' : ''; ?>>🟢 Aktif (Dipantau)</option>
                    <option value="nonaktif" <?= ($filter_status == 'nonaktif') ? 'selected' : ''; ?>>🔴 Nonaktif</option>
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-medium shadow-sm"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                
                <?php if(!empty($keyword_cari) || !empty($filter_status)): ?>
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
                                <th width="10%">Kode ICD</th>
                                <th width="20%">Nama Populer</th>
                                <th width="35%">Deskripsi Medis (ICD-10)</th>
                                <th width="15%">Status Pantau</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query JOIN dengan klausa Filter dinamis
                            $query_penyakit = "
                                SELECT 
                                    p.id_penyakit, 
                                    p.kode_icd, 
                                    p.nama_populer, 
                                    p.status_pantau,
                                    i.deskripsi_id,
                                    u.nama_lengkap AS pembuat
                                FROM tabel_penyakit p
                                JOIN tabel_master_icd10 i ON p.kode_icd = i.kode_icd
                                LEFT JOIN tabel_pengguna u ON p.id_pengguna = u.id_pengguna
                                $where_sql
                                ORDER BY p.status_pantau ASC, p.nama_populer ASC
                            ";
                            
                            $result_penyakit = mysqli_query($koneksi, $query_penyakit);
                            $no = 1;

                            if ($result_penyakit && mysqli_num_rows($result_penyakit) > 0) {
                                while ($row = mysqli_fetch_assoc($result_penyakit)) {
                                    
                                    // Desain Badge Status Modern
                                    if ($row['status_pantau'] == 'aktif') {
                                        $status_badge = '<span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-1"><i class="bi bi-check-circle-fill me-1"></i> AKTIF</span>';
                                    } else {
                                        $status_badge = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-3 py-1"><i class="bi bi-x-circle-fill me-1"></i> NONAKTIF</span>';
                                    }
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?php echo $no++; ?></td>
                                    
                                    <td>
                                        <div class="fw-bold" style="color: #0d6efd; font-family: monospace; font-size: 1.05rem;">
                                            <?php echo htmlspecialchars($row['kode_icd']); ?>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="fw-bold text-dynamic" style="font-size: 1.05rem;">
                                            <?php echo htmlspecialchars($row['nama_populer']); ?>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <span class="text-dynamic" style="font-size: 0.9rem; opacity: 0.85;">
                                            <?php echo htmlspecialchars($row['deskripsi_id']); ?>
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <?php echo $status_badge; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="btn-group shadow-sm">
                                            <a href="detail_penyakit.php?id=<?php echo $row['id_penyakit']; ?>" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <a href="edit_penyakit.php?id=<?php echo $row['id_penyakit']; ?>" class="btn btn-sm btn-outline-warning" title="Edit Penyakit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="proses_hapus_penyakit.php?id=<?php echo $row['id_penyakit']; ?>" class="btn btn-sm btn-outline-danger" title="Hapus Penyakit" onclick="return confirm('Apakah Anda yakin ingin menghapus penyakit <?php echo htmlspecialchars($row['nama_populer']); ?> dari daftar pemantauan?');">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-5 text-dynamic' style='opacity: 0.6;'><i class='bi bi-virus display-6 d-block mb-3 opacity-50'></i>Data penyakit tidak ditemukan atau belum dicatat.</td></tr>";
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