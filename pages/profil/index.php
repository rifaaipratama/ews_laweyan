<?php
session_start();

// Proteksi standar: Hanya memastikan pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// --- INI BAGIAN YANG KEMARIN TERHAPUS (Dibutuhkan oleh Sidebar & Topbar) ---
$id_role      = $_SESSION['id_role'];
$nama_lengkap = $_SESSION['nama_lengkap'];

$query_role = mysqli_query($koneksi, "SELECT nama_role FROM tabel_role WHERE id_role = '$id_role'");
$nama_role  = ($row = mysqli_fetch_assoc($query_role)) ? $row['nama_role'] : "User";
// ---------------------------------------------------------------------------

// Ambil data profil spesifik milik user yang sedang login
$id_pengguna_aktif = $_SESSION['id_pengguna'];
$query_profil = mysqli_query($koneksi, "
    SELECT p.*, r.nama_role, r.deskripsi 
    FROM tabel_pengguna p 
    JOIN tabel_role r ON p.id_role = r.id_role 
    WHERE p.id_pengguna = '$id_pengguna_aktif'
");
$profil = mysqli_fetch_assoc($query_profil);

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>

<style>
    /* Styling Panel Dasar */
    .panel-card {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .panel-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    }
    
    /* Styling Tipografi Data Profil */
    .profile-label {
        font-weight: 700;
        color: var(--text-color);
        opacity: 0.6;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        margin-bottom: 6px;
    }
    .profile-value {
        color: var(--text-color);
        font-size: 1.05rem;
        font-weight: 600;
        margin-bottom: 1.75rem;
    }
    
    /* Ikon Avatar Keren */
    .profile-icon-wrapper {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.15) 0%, rgba(111, 66, 193, 0.15) 100%);
        border: 4px solid var(--bg-body);
        box-shadow: 0 0 0 2px var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem auto;
    }
    .profile-icon-wrapper i {
        font-size: 3.5rem;
        background: -webkit-linear-gradient(#0d6efd, #6f42c1);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Utilitas Pemisah */
    .custom-divider {
        border-bottom: 2px dashed var(--border-color);
        margin: 1.5rem 0;
        opacity: 0.5;
    }
</style>

<div id="main-content">

    <?php if (isset($_SESSION['alert_sukses'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 12px;">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['alert_sukses']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['alert_sukses']); ?>
    <?php endif; ?>

    <?php include '../../layout/topbar.php'; ?>

    <!-- HEADER HALAMAN -->
    <div class="row mb-4 align-items-center mt-2">
        <div class="col-md-7">
            <h4 class="text-dynamic fw-bold mb-1"><i class="bi bi-person-lines-fill text-primary me-2"></i> Profil & Pengaturan Akun</h4>
            <p class="text-dynamic mb-0" style="opacity: 0.75; font-size: 0.9rem;">Kelola identitas, kredensial, dan informasi kepegawaian Anda.</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <a href="edit_profil.php" class="btn btn-primary shadow-sm rounded-pill px-4 fw-medium">
                <i class="bi bi-pencil-square me-1"></i> Edit Data Diri
            </a>
        </div>
    </div>

    <!-- GRID LAYOUT -->
    <div class="row g-4">
        
        <!-- KOLOM KIRI: Identitas Utama (Avatar) & Hak Akses -->
        <div class="col-lg-4 col-md-5">
            <div class="panel-card p-4 text-center d-flex flex-column justify-content-center">
                <div class="profile-icon-wrapper">
                    <!-- Icon statis karena tidak pakai fitur upload foto -->
                    <i class="bi bi-person-fill"></i>
                </div>
                
                <h4 class="fw-bold text-dynamic mb-1"><?php echo htmlspecialchars($profil['nama_lengkap']); ?></h4>
                <div class="text-muted small mb-3">
                    <?php echo !empty($profil['jabatan']) ? htmlspecialchars($profil['jabatan']) : '<em>Pegawai EWS</em>'; ?>
                </div>
                
                <div>
                    <?php if ($profil['id_role'] == 1): ?>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3 py-2">
                            <i class="bi bi-shield-lock-fill me-1"></i> <?php echo htmlspecialchars($profil['nama_role']); ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3 py-2">
                            <i class="bi bi-shield-check me-1"></i> <?php echo htmlspecialchars($profil['nama_role']); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
                    <div class="profile-label" style="font-size: 0.7rem;">Status Akun & Log</div>
                    <div class="d-flex justify-content-between align-items-center px-2 mt-2">
                        <span class="small text-muted"><i class="bi bi-person-check me-1"></i> Status</span>
                        <span class="badge bg-success">Aktif</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center px-2 mt-2">
                        <span class="small text-muted"><i class="bi bi-clock-history me-1"></i> Login Terakhir</span>
                        <span class="small fw-semibold text-dynamic">
                            <?php echo ($profil['last_login'] != null) ? date('d M Y', strtotime($profil['last_login'])) : '-'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- KOLOM KANAN: Data Spesifik HRIS/Kepegawaian -->
        <div class="col-lg-8 col-md-7">
            <div class="panel-card p-4 p-md-5">
                <h5 class="fw-bold text-dynamic mb-4 pb-2" style="border-bottom: 2px solid var(--border-color);"><i class="bi bi-card-heading me-2 text-primary"></i> Data Demografi & Kepegawaian</h5>
                
                <div class="row">
                    <!-- NIP -->
                    <div class="col-md-6">
                        <div class="profile-label">Nomor Induk Pegawai (NIP)</div>
                        <div class="profile-value" style="font-family: monospace; font-size: 1.15rem; color: #0d6efd;">
                            <?php echo !empty($profil['nip']) ? htmlspecialchars($profil['nip']) : '<span class="text-muted fst-italic" style="font-size: 0.9rem;">Belum diinput</span>'; ?>
                        </div>
                    </div>
                    
                    <!-- Username -->
                    <div class="col-md-6">
                        <div class="profile-label">Username (Kredensial Login)</div>
                        <div class="profile-value">@<?php echo htmlspecialchars($profil['username']); ?></div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <div class="profile-label">Alamat Email</div>
                        <div class="profile-value">
                            <?php echo !empty($profil['email']) ? htmlspecialchars($profil['email']) : '<span class="text-muted fst-italic" style="font-size: 0.9rem;">Belum diinput</span>'; ?>
                        </div>
                    </div>

                    <!-- No HP -->
                    <div class="col-md-6">
                        <div class="profile-label">Nomor Telepon / WhatsApp</div>
                        <div class="profile-value">
                            <?php echo !empty($profil['no_telepon']) ? htmlspecialchars($profil['no_telepon']) : '<span class="text-muted fst-italic" style="font-size: 0.9rem;">Belum diinput</span>'; ?>
                        </div>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div class="col-md-6">
                        <div class="profile-label">Jenis Kelamin</div>
                        <div class="profile-value">
                            <?php 
                                if ($profil['jenis_kelamin'] == 'L') echo '<i class="bi bi-gender-male text-primary me-1"></i> Laki-Laki';
                                elseif ($profil['jenis_kelamin'] == 'P') echo '<i class="bi bi-gender-female text-danger me-1"></i> Perempuan';
                                else echo '<span class="text-muted fst-italic" style="font-size: 0.9rem;">Belum diinput</span>';
                            ?>
                        </div>
                    </div>

                    <!-- Status Kepegawaian -->
                    <div class="col-md-6">
                        <div class="profile-label">Status Kepegawaian</div>
                        <div class="profile-value text-uppercase" style="letter-spacing: 0.5px;">
                            <?php echo !empty($profil['status_kepegawaian']) ? htmlspecialchars($profil['status_kepegawaian']) : '<span class="text-muted fst-italic text-capitalize" style="font-size: 0.9rem; letter-spacing: 0;">Belum diinput</span>'; ?>
                        </div>
                    </div>

                    <div class="col-12"><div class="custom-divider"></div></div>

                    <!-- Alamat -->
                    <div class="col-12">
                        <div class="profile-label">Alamat Domisili Lengkap</div>
                        <div class="profile-value mb-0" style="line-height: 1.6;">
                            <?php echo !empty($profil['alamat']) ? nl2br(htmlspecialchars($profil['alamat'])) : '<span class="text-muted fst-italic" style="font-size: 0.9rem;">Belum diinput</span>'; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        
    </div>
</div>

<?php include '../../layout/footer.php'; ?>