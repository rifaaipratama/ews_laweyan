<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Ambil data untuk Header/Sidebar
$id_user_login = $_SESSION['id_pengguna'];
$query_user_login = mysqli_query($koneksi, "
    SELECT p.nama_lengkap, r.nama_role, p.id_role 
    FROM tabel_pengguna p 
    JOIN tabel_role r ON p.id_role = r.id_role 
    WHERE p.id_pengguna = '$id_user_login'
");
$data_login = mysqli_fetch_assoc($query_user_login);
$nama_lengkap = $data_login['nama_lengkap'];
$nama_role    = $data_login['nama_role'];
$id_role      = $data_login['id_role'];

// --- LOGIKA FILTER & PENCARIAN ---
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$filter_role  = isset($_GET['role']) ? mysqli_real_escape_string($koneksi, $_GET['role']) : '';
$filter_status= isset($_GET['status']) ? mysqli_real_escape_string($koneksi, $_GET['status']) : '';

// Base Query
$query = "SELECT p.*, r.nama_role AS nama_role_akun 
          FROM tabel_pengguna p 
          JOIN tabel_role r ON p.id_role = r.id_role 
          WHERE 1=1"; // 1=1 trik agar mudah menambahkan kondisi AND selanjutnya

// Terapkan kondisi jika filter diisi
if (!empty($search_query)) {
    $query .= " AND (p.nama_lengkap LIKE '%$search_query%' OR p.username LIKE '%$search_query%')";
}
if (!empty($filter_role)) {
    $query .= " AND p.id_role = '$filter_role'";
}
if (!empty($filter_status)) {
    $query .= " AND p.status_aktif = '$filter_status'";
}

$query .= " ORDER BY p.id_role ASC, p.id_pengguna ASC";
$result = mysqli_query($koneksi, $query);

// Ambil data role untuk mengisi dropdown filter
$query_master_role = mysqli_query($koneksi, "SELECT * FROM tabel_role ORDER BY id_role ASC");

$title = "Manajemen Pengguna";
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

    .role-badge { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px; padding: 5px 10px; }

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

    <div class="row mb-4 align-items-center mt-2">
        <div class="col-md-7">
            <h4 class="text-dynamic fw-bold mb-1"><i class="bi bi-people-fill me-2 text-primary"></i> Manajemen Pengguna</h4>
            <p class="text-dynamic mb-0" style="opacity: 0.75; font-size: 0.9rem;">Kelola hak akses, kredensial, dan daftar akun staf EWS.</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <a href="tambah_pengguna.php" class="btn btn-primary shadow-sm fw-medium rounded-pill px-4">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah Pengguna
            </a>
        </div>
    </div>

    <!-- PANEL PENCARIAN & FILTER -->
    <div class="panel-card p-3 mb-4">
        <form method="GET" action="index.php" class="row g-3 align-items-center">
            <!-- Cari Nama/Username -->
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text filter-bg border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control filter-control border-start-0 ps-0" name="search" placeholder="Cari Nama / @Username..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
            </div>
            
            <!-- Filter Role -->
            <div class="col-md-3">
                <select class="form-select filter-control" name="role">
                    <option value="">Semua Role</option>
                    <?php while($r = mysqli_fetch_assoc($query_master_role)): ?>
                        <option value="<?php echo $r['id_role']; ?>" <?php echo ($filter_role == $r['id_role']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($r['nama_role']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <!-- Filter Status -->
            <div class="col-md-3">
                <select class="form-select filter-control" name="status">
                    <option value="">Semua Status Akun</option>
                    <option value="aktif" <?php echo ($filter_status == 'aktif') ? 'selected' : ''; ?>>🟢 Aktif</option>
                    <option value="nonaktif" <?php echo ($filter_status == 'nonaktif') ? 'selected' : ''; ?>>🔴 Nonaktif</option>
                </select>
            </div>
            
            <!-- Tombol Aksi -->
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-medium shadow-sm"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                
                <!-- Munculkan tombol reset jika ada filter yang aktif -->
                <?php if(!empty($search_query) || !empty($filter_role) || !empty($filter_status)): ?>
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
                                <th width="25%">Nama & Kredensial</th>
                                <th width="15%">Role Akses</th>
                                <th width="15%">Status Akun</th>
                                <th width="20%">Terakhir Login</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if(mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)): 
                                    
                                    // Pewarnaan Badge Berdasarkan Role
                                    $badge_class = ($row['id_role'] == 1) ? 'bg-danger bg-opacity-10 text-danger border border-danger' : 'bg-info bg-opacity-10 text-info border border-info';
                                    
                                    // Penentuan Ikon Status
                                    $status_icon = ($row['status_aktif'] == 'aktif') ? '<i class="bi bi-check-circle-fill text-success me-1"></i> Aktif' : '<i class="bi bi-x-circle-fill text-danger me-1"></i> Nonaktif';
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?php echo $no++; ?></td>
                                    <td>
                                        <div class="fw-bold text-dynamic" style="font-size: 1.05rem;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                        <div style="font-family: monospace; font-size: 0.85rem; color: #0d6efd;">@<?php echo htmlspecialchars($row['username']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill role-badge <?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($row['nama_role_akun']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold" style="font-size: 0.85rem; color: var(--text-color);">
                                            <?php echo $status_icon; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted" style="font-size: 0.85rem;">
                                            <i class="bi bi-clock-history me-1"></i> 
                                            <?php echo $row['last_login'] ? date('d M Y, H:i', strtotime($row['last_login'])) : '<span class="fst-italic">Belum pernah login</span>'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group shadow-sm">
                                            <!-- Tombol Lihat Detail (Target File Baru) -->
                                            <a href="detail_pengguna.php?id=<?php echo $row['id_pengguna']; ?>" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <!-- Tombol Edit -->
                                            <a href="edit_pengguna.php?id=<?php echo $row['id_pengguna']; ?>" class="btn btn-sm btn-outline-warning" title="Edit Hak Akses">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <!-- Tombol Hapus (Dilindungi jika Super Admin Utama ID 2) -->
                                            <?php if($row['id_pengguna'] != 2): ?>
                                            <a href="proses_hapus_pengguna.php?id=<?php echo $row['id_pengguna']; ?>" class="btn btn-sm btn-outline-danger" title="Hapus Akun" onclick="return confirm('Peringatan: Menghapus akun @<?php echo htmlspecialchars($row['username']); ?> bersifat permanen. Lanjutkan?');">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-5 text-dynamic' style='opacity: 0.6;'><i class='bi bi-search display-6 d-block mb-3 opacity-50'></i>Tidak ada pengguna yang cocok dengan filter tersebut.</td></tr>";
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