<?php
/** 
 * KODE PENENANG INTELEPHENSE
 * @var string $nama_lengkap
 * @var string $nama_role
 */

$current = $_SERVER['REQUEST_URI'];

function isActive(string $keyword, string $current): string {
    return strpos($current, $keyword) !== false ? 'active' : '';
}

// Logika penentuan warna badge role dan icon
$role_badge_class = '';
$role_icon = '';
if (isset($_SESSION['id_role']) && $_SESSION['id_role'] == 1) {
    // Admin: Merah Soft (bg-danger bg-opacity-10 text-danger border border-danger)
    $role_badge_class = 'bg-danger bg-opacity-10 text-danger border border-danger';
    $role_icon = 'bi-shield-lock-fill';
} else {
    // Petugas/User: Biru Soft (bg-info bg-opacity-10 text-info border border-info)
    $role_badge_class = 'bg-info bg-opacity-10 text-info border border-info';
    $role_icon = 'bi-person-badge';
}
?>

<!-- Tambahan CSS Khusus Sidebar agar tampil rapi, modern, dan presisi -->
<style>
    /* Styling Logo Brand bergaya App Icon */
    .brand-app-icon {
        background: linear-gradient(135deg, #0d6efd 0%, #0aa2c0 100%);
        border-radius: 12px;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
    }

    /* Memastikan teks menu yang panjang turun sejajar, tidak ke bawah icon */
    .nav-link.menu-item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .nav-link.menu-item .menu-icon {
        width: 30px;
        text-align: center;
        font-size: 1.25rem;
        margin-right: 12px;
        flex-shrink: 0; /* Mencegah icon mengecil saat teks panjang */
    }

    .nav-link.menu-item .menu-text {
        flex: 1;
        line-height: 1.3;
        white-space: normal; /* Mengizinkan teks turun ke baris baru dengan rapi */
        font-size: 0.95rem;
    }

    /* Perbaikan Kotak Profil Bawah untuk Mode Gelap/Terang */
    .bottom-profile-card {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        transition: background-color 0.3s, border-color 0.3s;
    }
    
    /* Styling khusus tombol Keluar Sistem agar soft dan elegan */
    .btn-logout-soft {
        background-color: rgba(220, 53, 69, 0.08); /* Transparansi merah */
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.2);
        transition: all 0.3s ease;
    }
    .btn-logout-soft:hover {
        background-color: #dc3545;
        color: white;
        border-color: #dc3545;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
    }
    [data-theme="dark"] .btn-logout-soft {
        color: #ff6b81;
        border-color: rgba(255, 107, 129, 0.3);
    }
    [data-theme="dark"] .btn-logout-soft:hover {
        background-color: #ff6b81;
        color: #fff;
    }
</style>

<div id="sidebar" class="shadow-sm">
    
    <!-- ================= BRAND LOGO ================= -->
    <div class="sidebar-brand mb-3 pb-3 border-bottom d-flex align-items-center" style="border-color: var(--border-color) !important;">
        <div class="brand-app-icon me-3">
            <i class="bi bi-shield-plus text-white" style="font-size: 1.6rem;"></i>
        </div>
        <div style="flex: 1;">
            <h5 class="mb-0 fw-bold text-dynamic" style="letter-spacing: 1px; font-size: 1.15rem;">SMART EWS</h5>
            <small class="fw-bold" style="color: #0aa2c0; font-size: 0.65rem; line-height: 1.2; display: block; margin-top: 3px;">
                Sistem Early Warning System Dini Kecamatan Laweyan
            </small>
        </div>
    </div>

    <!-- ================= AREA MENU ================= -->
    <div class="sidebar-scroll pb-3">
        <ul class="nav flex-column mt-2 mb-auto gap-1">
            
            <li class="menu-title mt-2">Menu Utama</li>
            <li class="nav-item">
                <a href="/ews_laweyan/pages/dashboard/index.php" class="nav-link menu-item <?= isActive('/dashboard/', $current); ?>">
                    <i class="bi bi-grid-1x2-fill menu-icon" style="color: #0d6efd;"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <li class="menu-title mt-4">Manajemen Kasus</li>
            <li class="nav-item">
                <a href="/ews_laweyan/pages/kasus/index.php" class="nav-link menu-item <?= isActive('/kasus/', $current); ?>">
                    <i class="bi bi-clipboard-pulse menu-icon" style="color: #fd7e14;"></i> 
                    <span class="menu-text">Data Kasus</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/ews_laweyan/pages/kelurahan/index.php" class="nav-link menu-item <?= isActive('/kelurahan/', $current); ?>">
                    <i class="bi bi-pin-map-fill menu-icon" style="color: #198754;"></i> 
                    <span class="menu-text">Data Kelurahan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/ews_laweyan/pages/peta/index.php" class="nav-link menu-item <?= isActive('/peta/', $current); ?>">
                    <i class="bi bi-map-fill menu-icon" style="color: #0dcaf0;"></i> 
                    <span class="menu-text">Peta Sebaran</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/ews_laweyan/pages/notifikasi/riwayat_aktivitas.php" class="nav-link menu-item <?= isActive('/riwayat_aktivitas', $current); ?>">
                    <i class="bi bi-activity menu-icon" style="color: #e83e8c;"></i> 
                    <span class="menu-text">Riwayat Aktivitas</span>
                </a>
            </li>

            <!-- SECTION COMMAND CENTER -->
            <li class="menu-title mt-4">Command Center</li>
            <li class="nav-item">
                <a href="/ews_laweyan/pages/notifikasi/index.php" class="nav-link menu-item <?= isActive('/notifikasi/index', $current); ?>">
                    <i class="bi bi-bell-fill menu-icon" style="color: #dc3545;"></i> 
                    <span class="menu-text">Pusat Notifikasi</span>
                    <?php
                    if (isset($koneksi)) {
                        $q_badge = mysqli_query($koneksi, "SELECT COUNT(id_alert) AS unread FROM tabel_alert WHERE status_baca = 'belum'");
                        if ($q_badge) {
                            $badge_data = mysqli_fetch_assoc($q_badge);
                            if ($badge_data['unread'] > 0) {
                                echo '<span class="badge bg-danger rounded-pill shadow-sm ms-2" style="font-size: 0.7rem; padding: 4px 8px;">' . $badge_data['unread'] . ' Baru</span>';
                            }
                        }
                    }
                    ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="/ews_laweyan/pages/notifikasi/arsip_alert.php" class="nav-link menu-item <?= isActive('/arsip_alert', $current); ?>">
                    <i class="bi bi-journal-medical menu-icon" style="color: #6c757d;"></i> 
                    <span class="menu-text">Buku Arsip Alert</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/ews_laweyan/pages/statistik_laporan/index.php" class="nav-link menu-item <?= isActive('/statistik_laporan/', $current); ?>">
                    <i class="bi bi-bar-chart-fill menu-icon" style="color: #20c997;"></i> 
                    <span class="menu-text">Statistik & Pelaporan</span>
                </a>
            </li>

            <!-- SECTION PENGATURAN AKUN -->
            <li class="menu-title mt-4">Pengaturan Akun</li>
            <li class="nav-item">
                <a href="/ews_laweyan/pages/profil/index.php" class="nav-link menu-item <?= isActive('/profil/', $current); ?>">
                    <i class="bi bi-person-lines-fill menu-icon" style="color: #6f42c1;"></i> 
                    <span class="menu-text">Profil Saya</span>
                </a>
            </li>

            <!-- SECTION KHUSUS ADMINISTRATOR -->
            <?php if (isset($_SESSION['id_role']) && $_SESSION['id_role'] == 1): ?>
                <li class="menu-title mt-4 text-danger"><i class="bi bi-shield-lock-fill me-1"></i> Administrator</li>
                
                <li class="nav-item">
                    <a href="/ews_laweyan/pages/pengguna/index.php" class="nav-link menu-item <?= isActive('/pengguna/', $current); ?>">
                        <i class="bi bi-people-fill menu-icon" style="color: #0d6efd;"></i> 
                        <span class="menu-text">Manajemen Pengguna</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/ews_laweyan/pages/manajemen_role/index.php" class="nav-link menu-item <?= isActive('/manajemen_role/', $current); ?>">
                        <i class="bi bi-person-badge-fill menu-icon" style="color: #ffc107;"></i> 
                        <span class="menu-text">Manajemen Role</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/ews_laweyan/pages/data_riwayat_pasien/index.php" class="nav-link menu-item <?= isActive('/data_riwayat_pasien/', $current); ?>">
                        <i class="bi bi-folder2-open menu-icon" style="color: #0dcaf0;"></i> 
                        <span class="menu-text">Data Riwayat Pasien</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/ews_laweyan/pages/data_penyakit/index.php" class="nav-link menu-item <?= isActive('/data_penyakit/', $current); ?>">
                        <i class="bi bi-virus menu-icon" style="color: #dc3545;"></i> 
                        <span class="menu-text">Master Penyakit</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/ews_laweyan/pages/pengaturan_threshold/index.php" class="nav-link menu-item <?= isActive('/pengaturan_threshold/', $current); ?>">
                        <i class="bi bi-sliders menu-icon" style="color: #6c757d;"></i> 
                        <span class="menu-text">Pengaturan Threshold</span>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </div>

    <!-- ================= BAGIAN BAWAH SIDEBAR ================= -->
    <div class="mt-auto p-3 border-top" style="border-color: var(--border-color) !important;">
        <!-- Kotak profil kini menggunakan variabel tema agar aman di mode gelap -->
        <div class="bottom-profile-card shadow-sm mb-3 text-center rounded-3 p-2 pb-3">
            <h6 class="text-dynamic mb-2 fw-bold mt-2"><?= htmlspecialchars($nama_lengkap); ?></h6>
            
            <!-- Badge Role Dinamis (Warna disesuaikan dengan Role) -->
            <span class="badge <?= $role_badge_class; ?> px-3 py-1 rounded-pill" style="font-weight: 600;">
                <i class="bi <?= $role_icon; ?> me-1"></i> <?= htmlspecialchars($nama_role); ?>
            </span>
        </div>
        
        <!-- Tombol Keluar Sistem yang lebih soft dan elegan -->
        <a href="/ews_laweyan/auth/logout.php" class="btn btn-logout-soft w-100 fw-semibold rounded-3 d-flex align-items-center justify-content-center">
            <i class="bi bi-power me-2"></i> Keluar Sistem
        </a>
    </div>
</div>