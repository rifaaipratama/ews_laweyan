<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Ambil data user untuk Header/Sidebar
$id_role           = $_SESSION['id_role'];
$id_pengguna_login = $_SESSION['id_pengguna'];
$nama_lengkap      = $_SESSION['nama_lengkap'];

// PERBAIKAN: Ambil $nama_role agar file sidebar.php tidak error biru
$query_role = mysqli_query($koneksi, "SELECT nama_role FROM tabel_role WHERE id_role = '$id_role'");
$nama_role  = ($row = mysqli_fetch_assoc($query_role)) ? $row['nama_role'] : "User";

// --- LOGIKA FILTER PENCARIAN & BATASAN DATA ---
$show_all       = isset($_GET['show_all']) && $_GET['show_all'] == '1';
$filter_user    = isset($_GET['user']) ? mysqli_real_escape_string($koneksi, $_GET['user']) : '';
$filter_tanggal = isset($_GET['tanggal']) ? mysqli_real_escape_string($koneksi, $_GET['tanggal']) : '';

// Jika Admin (1), bisa lihat semua. Jika Petugas (2), hanya lihat miliknya sendiri.
$where_sql = "WHERE 1=1";

if ($id_role != 1) {
    // Paksa hanya menampilkan log miliknya sendiri
    $where_sql .= " AND r.id_pengguna = '$id_pengguna_login'";
} else {
    // Admin bisa filter by user
    if (!empty($filter_user)) {
        $where_sql .= " AND r.id_pengguna = '$filter_user'";
    }
}

if (!empty($filter_tanggal)) {
    $where_sql .= " AND DATE(r.waktu_aktivitas) = '$filter_tanggal'";
}

// Batasi 10 data jika tidak sedang "Show All"
$limit_sql = $show_all ? "" : "LIMIT 10";

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>

<style>
    /* Upgrade Layout & Panel Modern */
    .panel-card {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
    }
    
    .panel-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 5px;
        background: linear-gradient(90deg, #6f42c1, #0dcaf0);
    }

    /* Kustomisasi Form Filter */
    .filter-box {
        background-color: rgba(0,0,0,0.015);
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid var(--border-color);
    }
    [data-theme="dark"] .filter-box { background-color: rgba(255,255,255,0.02); }

    .form-control, .form-select {
        background-color: var(--bg-body);
        color: var(--text-color);
        border: 1px solid var(--border-color);
        border-radius: 8px;
    }
    .form-control:focus, .form-select:focus {
        border-color: #6f42c1;
        box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
        background-color: var(--bg-body);
        color: var(--text-color);
    }

    /* MODERN TIMELINE UI */
    .timeline-modern {
        position: relative;
        padding-left: 2rem;
        list-style: none;
        margin: 0;
    }
    
    .timeline-modern::before {
        content: '';
        position: absolute;
        top: 0; bottom: 0; left: 0.75rem;
        width: 3px;
        background: var(--border-color);
        border-radius: 3px;
        opacity: 0.7;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }
    .timeline-item:last-child { margin-bottom: 0; }
    
    .timeline-icon {
        position: absolute;
        top: 0; left: -2.35rem;
        width: 35px; height: 35px;
        border-radius: 50%;
        background-color: var(--bg-panel);
        border: 3px solid var(--log-color, #0d6efd);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--log-color, #0d6efd);
        font-size: 1.1rem;
        z-index: 1;
        box-shadow: 0 0 0 4px var(--bg-body);
    }

    .timeline-content {
        background-color: rgba(0,0,0,0.015);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.25rem;
        transition: all 0.2s ease;
    }
    [data-theme="dark"] .timeline-content { background-color: rgba(255,255,255,0.02); }
    
    .timeline-content:hover {
        background-color: rgba(0,0,0,0.03);
        border-color: var(--log-color, #0d6efd);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    [data-theme="dark"] .timeline-content:hover { background-color: rgba(255,255,255,0.04); }

    .user-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
        background-color: var(--bg-body);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        color: var(--text-color);
    }
</style>

<div id="main-content">
    <?php include '../../layout/topbar.php'; ?>

    <!-- Header Section -->
    <div class="row mb-4 align-items-center mt-2">
        <div class="col-12">
            <h4 class="fw-bold mb-1 text-dynamic">
                <i class="bi bi-activity text-info me-2"></i> Log Riwayat Aktivitas Sistem
            </h4>
            <p class="mb-0 text-muted" style="font-size: 0.95rem;">
                Rekam jejak seluruh pergerakan, input data, dan aktivitas yang dilakukan oleh pengguna.
            </p>
        </div>
    </div>

    <!-- Panel Filter -->
    <div class="panel-card mb-4">
        <div class="d-flex justify-content-between align-items-center px-4 pt-4 pb-0">
            <div class="fw-bold text-muted small text-uppercase" style="letter-spacing: 1px;">
                <i class="bi bi-search me-1"></i> Telusuri Jejak Pengguna
            </div>
            <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill shadow-sm px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Inbox
            </a>
        </div>

        <div class="filter-box m-3 mt-3">
            <form action="" method="GET" class="d-flex flex-column flex-md-row gap-3 align-items-end">
                
                <?php if ($id_role == 1): ?>
                <div class="flex-grow-1 w-100">
                    <label class="form-label small fw-semibold text-muted mb-1">Pilih Pengguna / Petugas</label>
                    <select name="user" class="form-select">
                        <option value="">-- Semua Pengguna --</option>
                        <?php
                        $q_users = mysqli_query($koneksi, "SELECT id_pengguna, nama_lengkap, id_role FROM tabel_pengguna ORDER BY nama_lengkap ASC");
                        while($u = mysqli_fetch_assoc($q_users)){
                            $role_label = ($u['id_role'] == 1) ? '(Admin)' : '(Petugas)';
                            $selected = ($filter_user == $u['id_pengguna']) ? 'selected' : '';
                            echo "<option value='{$u['id_pengguna']}' $selected>{$u['nama_lengkap']} {$role_label}</option>";
                        }
                        ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="flex-grow-1 w-100">
                    <label class="form-label small fw-semibold text-muted mb-1">Tanggal Aktivitas</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= $filter_tanggal ?>">
                </div>

                <?php if($show_all): ?>
                    <input type="hidden" name="show_all" value="1">
                <?php endif; ?>

                <div>
                    <button type="submit" class="btn btn-info text-white px-4 shadow-sm fw-semibold">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                    <?php if(!empty($filter_user) || !empty($filter_tanggal)): ?>
                        <a href="riwayat_aktivitas.php<?= $show_all ? '?show_all=1' : '' ?>" class="btn btn-outline-danger px-3 shadow-sm" title="Reset Filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Panel Timeline Riwayat -->
    <div class="panel-card mb-5 p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom" style="border-color: var(--border-color) !important;">
            <h5 class="fw-bold mb-0 text-dynamic">
                <?php if($show_all): ?>
                    <i class="bi bi-database text-primary me-2"></i> Menampilkan Seluruh Riwayat
                <?php else: ?>
                    <i class="bi bi-clock-history text-warning me-2"></i> Menampilkan 10 Aktivitas Terbaru
                <?php endif; ?>
            </h5>
            
            <div>
                <?php
                $qs = [];
                if(!empty($filter_user)) $qs[] = "user=$filter_user";
                if(!empty($filter_tanggal)) $qs[] = "tanggal=$filter_tanggal";
                $base_url = "riwayat_aktivitas.php" . (!empty($qs) ? "?" . implode("&", $qs) : "");
                
                if($show_all): ?>
                    <a href="<?= $base_url ?>" class="btn btn-sm btn-primary shadow-sm rounded-pill px-3">
                        <i class="bi bi-eye-slash me-1"></i> Sembunyikan & Batasi 10
                    </a>
                <?php else: 
                    $joiner = empty($qs) ? "?" : "&";
                ?>
                    <a href="<?= $base_url . $joiner . "show_all=1" ?>" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3 fw-semibold">
                        <i class="bi bi-database me-1"></i> Tampilkan Seluruh Histori
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <ul class="timeline-modern">
            <?php
            $query_log = "
                SELECT r.*, u.nama_lengkap, u.id_role 
                FROM tabel_riwayat_aktivitas r
                JOIN tabel_pengguna u ON r.id_pengguna = u.id_pengguna
                $where_sql
                ORDER BY r.waktu_aktivitas DESC
                $limit_sql
            ";
            
            $result = mysqli_query($koneksi, $query_log);

            if (mysqli_num_rows($result) > 0) {
                while ($log = mysqli_fetch_assoc($result)) {
                    
                    $waktu_log = date('d M Y - H:i:s', strtotime($log['waktu_aktivitas']));
                    $jenis = strtolower($log['jenis_aktivitas']);
                    
                    $color_hex = '#0d6efd';
                    $icon_class = 'bi-record-circle';

                    if (strpos($jenis, 'login') !== false || strpos($jenis, 'masuk') !== false) {
                        $color_hex = '#198754';
                        $icon_class = 'bi-box-arrow-in-right';
                    } elseif (strpos($jenis, 'logout') !== false || strpos($jenis, 'keluar') !== false) {
                        $color_hex = '#6c757d';
                        $icon_class = 'bi-box-arrow-left';
                    } elseif (strpos($jenis, 'tambah') !== false || strpos($jenis, 'buat') !== false) {
                        $color_hex = '#0dcaf0';
                        $icon_class = 'bi-plus-circle';
                    } elseif (strpos($jenis, 'edit') !== false || strpos($jenis, 'ubah') !== false) {
                        $color_hex = '#fd7e14';
                        $icon_class = 'bi-pencil-square';
                    } elseif (strpos($jenis, 'hapus') !== false || strpos($jenis, 'arsip') !== false) {
                        $color_hex = '#dc3545';
                        $icon_class = 'bi-trash';
                    } elseif (strpos($jenis, 'validasi') !== false || strpos($jenis, 'verifikasi') !== false) {
                        $color_hex = '#6f42c1';
                        $icon_class = 'bi-shield-check';
                    }

                    $role_badge = ($log['id_role'] == 1) ? "<i class='bi bi-star-fill text-warning me-1' style='font-size:0.6rem;'></i> Admin" : "<i class='bi bi-person-badge text-info me-1' style='font-size:0.6rem;'></i> Petugas";

                    echo "
                    <li class='timeline-item' style='--log-color: {$color_hex};'>
                        <div class='timeline-icon'><i class='bi {$icon_class}'></i></div>
                        
                        <div class='timeline-content'>
                            <div class='d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-2 gap-2'>
                                <div class='fw-bold fs-6' style='color: {$color_hex}; text-transform: uppercase; letter-spacing: 0.5px;'>
                                    {$log['jenis_aktivitas']}
                                </div>
                                <div class='text-muted small fw-semibold'>
                                    <i class='bi bi-clock me-1'></i> {$waktu_log}
                                </div>
                            </div>
                            
                            <p class='mb-3 text-dynamic' style='font-size: 0.95rem; opacity: 0.9; line-height: 1.5;'>
                                {$log['deskripsi']}
                            </p>
                            
                            <div class='d-flex justify-content-between align-items-center mt-3 pt-3 border-top' style='border-color: rgba(128,128,128,0.2) !important;'>
                                <div class='d-flex align-items-center gap-2'>
                                    <span class='user-badge shadow-sm'>
                                        <i class='bi bi-person-fill me-1'></i> <b>" . htmlspecialchars($log['nama_lengkap']) . "</b>
                                    </span>
                                    <span class='user-badge bg-transparent border-0 opacity-75'>
                                        {$role_badge}
                                    </span>
                                </div>
                                <!-- TOMBOL DETAIL MENGARAH KE HALAMAN KHUSUS -->
                                <a href='detail_riwayat_user.php?id={$log['id_pengguna']}' class='btn btn-sm btn-outline-secondary rounded-pill px-3' style='font-size:0.8rem;'>
                                    Detail User <i class='bi bi-arrow-right ms-1'></i>
                                </a>
                            </div>
                        </div>
                    </li>
                    ";
                }
            } else {
                echo "<div class='text-center py-5' style='color: var(--text-color); opacity: 0.6;'>
                        <i class='bi bi-folder-x fs-1 d-block mb-3'></i>
                        <h5 class='fw-bold'>Tidak Ada Rekam Jejak</h5>
                        <p class='small'>Tidak ditemukan aktivitas pengguna yang sesuai dengan filter yang dipilih.</p>
                      </div>";
            }
            ?>
        </ul>
    </div>
</div>

<?php include '../../layout/footer.php'; ?>