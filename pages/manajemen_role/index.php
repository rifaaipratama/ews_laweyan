<?php
session_start();

// Proteksi Ganda: Harus login DAN harus Administrator (id_role = 1)
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
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$filter_sort  = isset($_GET['sort']) ? mysqli_real_escape_string($koneksi, $_GET['sort']) : '';

$where_clause = "1=1";
if (!empty($search_query)) {
    $where_clause .= " AND (r.nama_role LIKE '%$search_query%' OR r.deskripsi LIKE '%$search_query%')";
}

$order_clause = "ORDER BY r.id_role ASC"; // Default
if ($filter_sort == 'abjad') {
    $order_clause = "ORDER BY r.nama_role ASC";
} elseif ($filter_sort == 'terbaru') {
    $order_clause = "ORDER BY r.created_at DESC";
}

// Query menggunakan LEFT JOIN untuk menghitung jumlah pengguna per role
$query_master_role = "
    SELECT 
        r.id_role, 
        r.nama_role, 
        r.deskripsi, 
        r.created_at,
        COUNT(p.id_pengguna) AS total_user 
    FROM tabel_role r
    LEFT JOIN tabel_pengguna p ON r.id_role = p.id_role
    WHERE $where_clause
    GROUP BY r.id_role
    $order_clause
";
$result_role = mysqli_query($koneksi, $query_master_role);

$title = "Manajemen Role";
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
            <h4 class="text-dynamic fw-bold mb-1"><i class="bi bi-shield-lock me-2 text-primary"></i> Manajemen Hak Akses (Role)</h4>
            <p class="text-dynamic mb-0" style="opacity: 0.75; font-size: 0.9rem;">Kelola tingkat kewenangan dan master data role sistem EWS.</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <a href="tambah_role.php" class="btn btn-primary shadow-sm fw-medium rounded-pill px-4">
                <i class="bi bi-plus-lg me-1"></i> Tambah Role Baru
            </a>
        </div>
    </div>

    <!-- PANEL PENCARIAN & FILTER -->
    <div class="panel-card p-3 mb-4">
        <form method="GET" action="index.php" class="row g-3 align-items-center">
            <!-- Cari Nama Role/Deskripsi -->
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text filter-bg border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control filter-control border-start-0 ps-0" name="search" placeholder="Cari Nama Role atau Deskripsi..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
            </div>
            
            <!-- Filter Urutan -->
            <div class="col-md-4">
                <select class="form-select filter-control" name="sort">
                    <option value="">Default (Berdasarkan ID)</option>
                    <option value="abjad" <?php echo ($filter_sort == 'abjad') ? 'selected' : ''; ?>>Urutkan Abjad (A-Z)</option>
                    <option value="terbaru" <?php echo ($filter_sort == 'terbaru') ? 'selected' : ''; ?>>Ditambahkan Terbaru</option>
                </select>
            </div>
            
            <!-- Tombol Aksi -->
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-medium shadow-sm"><i class="bi bi-funnel-fill me-1"></i> Terapkan</button>
                
                <?php if(!empty($search_query) || !empty($filter_sort)): ?>
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
                                <th width="20%">Nama Role</th>
                                <th width="40%">Deskripsi Kewenangan</th>
                                <th width="20%" class="text-center">Total Pengguna</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if ($result_role && mysqli_num_rows($result_role) > 0) {
                                while ($row = mysqli_fetch_assoc($result_role)) {
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?php echo $no++; ?></td>
                                    
                                    <!-- Nama Role -->
                                    <td>
                                        <?php if ($row['id_role'] == 1): ?>
                                            <div class="fw-bold text-primary" style="font-size: 1.05rem;"><i class="bi bi-star-fill me-1 small"></i> <?php echo htmlspecialchars($row['nama_role']); ?></div>
                                        <?php else: ?>
                                            <div class="fw-bold text-dynamic" style="font-size: 1.05rem;"><?php echo htmlspecialchars($row['nama_role']); ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Deskripsi -->
                                    <td>
                                        <?php if (!empty($row['deskripsi'])): ?>
                                            <span style="font-size: 0.9rem; color: var(--text-color);"><?php echo htmlspecialchars($row['deskripsi']); ?></span>
                                        <?php else: ?>
                                            <em class="text-muted" style="font-size: 0.85rem;">Belum ada deskripsi</em>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Total User -->
                                    <td class="text-center">
                                        <?php if ($row['total_user'] > 0): ?>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3 py-1 fw-semibold">
                                                <i class="bi bi-people-fill me-1"></i> <?php echo $row['total_user']; ?> Akun
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-3 py-1 fw-normal">
                                                Kosong
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Tombol Aksi -->
                                    <td class="text-center">
                                        <div class="btn-group shadow-sm">
                                            <a href="edit_role.php?id=<?php echo $row['id_role']; ?>" class="btn btn-sm btn-outline-warning" title="Edit Role">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            
                                            <?php if ($row['id_role'] == 1): ?>
                                                <button class="btn btn-sm btn-outline-secondary" title="Role Administrator Utama Tidak Bisa Dihapus" disabled>
                                                    <i class="bi bi-lock-fill"></i>
                                                </button>
                                            <?php else: 
                                                $alert_msg = ($row['total_user'] > 0) 
                                                    ? "PERINGATAN KRITIS: Ada " . $row['total_user'] . " pengguna yang terikat dengan role ini! Menghapus role akan merusak hak akses mereka. Yakin ingin melanjutkan?"
                                                    : "Apakah Anda yakin ingin menghapus Master Role: " . htmlspecialchars($row['nama_role']) . "?";
                                            ?>
                                                <a href="proses_hapus_role.php?id=<?php echo $row['id_role']; ?>" class="btn btn-sm btn-outline-danger" title="Hapus Role" onclick="return confirm('<?php echo $alert_msg; ?>');">
                                                    <i class="bi bi-trash-fill"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-5 text-dynamic' style='opacity: 0.6;'><i class='bi bi-shield-x display-6 d-block mb-3 opacity-50'></i>Tidak ada data master role yang ditemukan.</td></tr>";
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