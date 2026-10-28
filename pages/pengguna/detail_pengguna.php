<?php
session_start();

if (!isset($_SESSION['id_pengguna']) || $_SESSION['id_role'] != 1) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

$id_pengguna_detail = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query_detail = mysqli_query($koneksi, "
    SELECT p.*, r.nama_role 
    FROM tabel_pengguna p 
    JOIN tabel_role r ON p.id_role = r.id_role 
    WHERE p.id_pengguna = '$id_pengguna_detail'
");
$detail = mysqli_fetch_assoc($query_detail);

if (!$detail) {
    $_SESSION['alert_gagal'] = "Data pengguna tidak ditemukan.";
    header("Location: index.php");
    exit;
}

include '../../layout/header.php';
?>

<style>
    body {
        background-color: var(--bg-body) !important;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        margin: 0;
        padding: 40px 20px;
    }

    .detail-container {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        width: 100%;
        max-width: 950px;
        padding: 2.5rem;
        transition: background-color 0.3s, border-color 0.3s;
    }

    .info-card {
        background-color: rgba(13, 110, 253, 0.02);
        border: 1px solid rgba(13, 110, 253, 0.1);
        border-radius: 16px;
        padding: 2rem;
        height: 100%;
    }
    [data-theme="dark"] .info-card { 
        background-color: rgba(255,255,255,0.02); 
        border-color: rgba(255,255,255,0.05); 
    }

    .profile-icon-wrapper {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.15) 0%, rgba(111, 66, 193, 0.15) 100%);
        border: 3px solid var(--bg-body);
        box-shadow: 0 0 0 2px var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem auto;
    }
    .profile-icon-wrapper i {
        font-size: 3rem;
        background: -webkit-linear-gradient(#0d6efd, #6f42c1);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .data-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        font-weight: 700;
        margin-bottom: 4px;
    }

    .data-value {
        font-size: 1.05rem;
        color: var(--text-main);
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .divider-dashed {
        border-bottom: 2px dashed var(--border-color);
        margin: 1.5rem 0;
        opacity: 0.5;
    }
</style>

<div class="detail-container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 border-bottom pb-3" style="border-color: var(--border-color) !important;">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-main);">
                <i class="bi bi-person-vcard text-info me-2"></i> Detail Pengguna
            </h4>
            <p class="text-muted small mb-0 mt-1">Rincian identitas, kontak, dan hak akses sistem.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 fw-medium shadow-sm">
            <i class="bi bi-arrow-left me-2"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="row g-4">
        <!-- KOLOM KIRI -->
        <div class="col-lg-4 col-md-5">
            <div class="info-card text-center d-flex flex-column justify-content-center">
                <div class="profile-icon-wrapper">
                    <i class="bi bi-person-fill"></i>
                </div>
                
                <h5 class="fw-bold text-dynamic mb-1"><?php echo htmlspecialchars($detail['nama_lengkap']); ?></h5>
                <div class="text-muted small mb-3">
                    <?php echo !empty($detail['jabatan']) ? htmlspecialchars($detail['jabatan']) : '<em>Pegawai EWS</em>'; ?>
                </div>
                
                <div class="mb-4">
                    <?php if ($detail['id_role'] == 1): ?>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3 py-2">
                            <i class="bi bi-shield-lock-fill me-1"></i> <?php echo htmlspecialchars($detail['nama_role']); ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3 py-2">
                            <i class="bi bi-shield-check me-1"></i> <?php echo htmlspecialchars($detail['nama_role']); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="text-start border-top pt-3" style="border-color: var(--border-color) !important;">
                    <div class="data-label" style="font-size: 0.7rem;">Status Akun</div>
                    <div class="d-flex align-items-center mb-3 mt-1">
                        <?php if($detail['status_aktif'] == 'aktif'): ?>
                            <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i> <span class="fw-semibold text-dynamic">Aktif</span>
                        <?php else: ?>
                            <i class="bi bi-x-circle-fill text-danger fs-5 me-2"></i> <span class="fw-semibold text-dynamic">Nonaktif</span>
                        <?php endif; ?>
                    </div>

                    <div class="data-label" style="font-size: 0.7rem;">Terakhir Login</div>
                    <div class="d-flex align-items-center mt-1">
                        <i class="bi bi-clock-history text-muted fs-5 me-2"></i>
                        <span class="small fw-semibold text-dynamic">
                            <?php echo ($detail['last_login'] != null) ? date('d M Y, H:i', strtotime($detail['last_login'])) : '<span class="fst-italic text-muted">Belum pernah</span>'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN -->
        <div class="col-lg-8 col-md-7">
            <div class="info-card p-4 p-md-5">
                
                <!-- HEADER KARTU KANAN + TOMBOL EDIT -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px dashed var(--border-color);">
                    <h6 class="fw-bold text-dynamic mb-0"><i class="bi bi-card-list text-primary me-2"></i> Data Demografi & Kontak</h6>
                    <!-- Arahkan ke file profil dengan membawa ID target -->
                    <a href="../profil/edit_profil.php?id=<?php echo $detail['id_pengguna']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-medium">
                        <i class="bi bi-pencil-fill me-1"></i> Edit Biodata
                    </a>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="data-label">Nomor Induk Pegawai (NIP)</div>
                        <div class="data-value" style="font-family: monospace; color: #0d6efd;">
                            <?php echo !empty($detail['nip']) ? htmlspecialchars($detail['nip']) : '<span class="text-muted fst-italic fw-normal" style="font-size: 0.9rem;">Belum diinput</span>'; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="data-label">Username (Login)</div>
                        <div class="data-value">@<?php echo htmlspecialchars($detail['username']); ?></div>
                    </div>

                    <div class="col-md-6">
                        <div class="data-label">Alamat Email</div>
                        <div class="data-value">
                            <?php echo !empty($detail['email']) ? htmlspecialchars($detail['email']) : '<span class="text-muted fst-italic fw-normal" style="font-size: 0.9rem;">Belum diinput</span>'; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="data-label">Nomor WhatsApp / HP</div>
                        <div class="data-value">
                            <?php echo !empty($detail['no_telepon']) ? htmlspecialchars($detail['no_telepon']) : '<span class="text-muted fst-italic fw-normal" style="font-size: 0.9rem;">Belum diinput</span>'; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="data-label">Jenis Kelamin</div>
                        <div class="data-value">
                            <?php 
                                if ($detail['jenis_kelamin'] == 'L') echo '<i class="bi bi-gender-male text-primary me-1"></i> Laki-Laki';
                                elseif ($detail['jenis_kelamin'] == 'P') echo '<i class="bi bi-gender-female text-danger me-1"></i> Perempuan';
                                else echo '<span class="text-muted fst-italic fw-normal" style="font-size: 0.9rem;">Belum diinput</span>';
                            ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="data-label">Status Kepegawaian</div>
                        <div class="data-value text-uppercase">
                            <?php echo !empty($detail['status_kepegawaian']) ? htmlspecialchars($detail['status_kepegawaian']) : '<span class="text-muted fst-italic text-capitalize fw-normal" style="font-size: 0.9rem; letter-spacing: 0;">Belum diinput</span>'; ?>
                        </div>
                    </div>

                    <div class="col-12"><div class="divider-dashed"></div></div>

                    <div class="col-12">
                        <div class="data-label">Alamat Domisili Lengkap</div>
                        <div class="data-value mb-0" style="line-height: 1.6; font-weight: 500;">
                            <?php echo !empty($detail['alamat']) ? nl2br(htmlspecialchars($detail['alamat'])) : '<span class="text-muted fst-italic fw-normal" style="font-size: 0.9rem;">Belum diinput</span>'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
        <small class="text-muted d-block"><i class="bi bi-calendar-check me-1"></i> Akun Terdaftar Pada: <?= date('d M Y, H:i', strtotime($detail['created_at'])); ?></small>
    </div>
</div>

<?php include '../../layout/footer.php'; ?>