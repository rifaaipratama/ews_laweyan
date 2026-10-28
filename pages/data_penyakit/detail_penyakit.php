<?php
session_start();

// Akses untuk semua user yang login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Validasi keberadaan ID di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['alert_gagal'] = "Akses Ditolak! ID penyakit tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$id_penyakit = (int)$_GET['id'];

// Query JOIN 4 Tabel untuk mengambil seluruh rincian informasi
$query_detail = "
    SELECT 
        p.*, 
        i.deskripsi_id, 
        i.deskripsi_en, 
        i.kategori,
        u.nama_lengkap,
        r.nama_role
    FROM tabel_penyakit p
    JOIN tabel_master_icd10 i ON p.kode_icd = i.kode_icd
    LEFT JOIN tabel_pengguna u ON p.id_pengguna = u.id_pengguna
    LEFT JOIN tabel_role r ON u.id_role = r.id_role
    WHERE p.id_penyakit = '$id_penyakit'
";

$result_detail = mysqli_query($koneksi, $query_detail);

if (mysqli_num_rows($result_detail) == 0) {
    $_SESSION['alert_gagal'] = "Data penyakit tidak ditemukan di sistem.";
    header("Location: index.php");
    exit;
}

$data = mysqli_fetch_assoc($result_detail);

// Panggil header HANYA untuk styling (mode canvas distraction-free)
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
        padding: 20px;
    }
    
    .detail-container {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15); 
        width: 100%;
        max-width: 850px;
        padding: 3rem;
        color: var(--text-color);
    }

    .detail-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-color);
        opacity: 0.7;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.2rem;
    }

    .detail-value {
        font-size: 1.05rem;
        font-weight: 500;
        margin-bottom: 1.5rem;
    }

    .layer-box {
        background-color: var(--bg-body);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        height: 100%;
    }
</style>

<div class="detail-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-file-earmark-medical text-primary me-2"></i> Detail Penyakit
            </h4>
            <p class="text-muted small mb-0">Rincian master data dan riwayat pelacakan (tracking).</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Tabel
        </a>
    </div>

    <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

    <div class="row g-4">
        <!-- Layer 1: Informasi Medis -->
        <div class="col-md-6">
            <div class="layer-box shadow-sm">
                <h6 class="fw-bold mb-4 border-bottom pb-2" style="color: var(--text-color);">
                    <i class="bi bi-clipboard2-pulse text-danger me-2"></i> Informasi Medis
                </h6>
                
                <div class="detail-label">Nama Populer (Dashboard)</div>
                <div class="detail-value fw-bold text-primary fs-5"><?php echo htmlspecialchars($data['nama_populer']); ?></div>

                <div class="detail-label">Kode ICD-10</div>
                <div class="detail-value"><span class="badge bg-dark text-white px-3 py-2"><?php echo htmlspecialchars($data['kode_icd']); ?></span></div>

                <div class="detail-label">Deskripsi Medis (ID)</div>
                <div class="detail-value"><?php echo htmlspecialchars($data['deskripsi_id']); ?></div>

                <div class="detail-label">Kategori Penyakit</div>
                <div class="detail-value"><?php echo htmlspecialchars($data['kategori'] ?? '-'); ?></div>
            </div>
        </div>

        <!-- Layer 2: Informasi Sistem & Pelacakan -->
        <div class="col-md-6">
            <div class="layer-box shadow-sm">
                <h6 class="fw-bold mb-4 border-bottom pb-2" style="color: var(--text-color);">
                    <i class="bi bi-cpu text-info me-2"></i> Informasi Sistem
                </h6>
                
                <div class="detail-label">Status Pantauan EWS</div>
                <div class="detail-value">
                    <?php if($data['status_pantau'] == 'aktif'): ?>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2"><i class="bi bi-check-circle me-1"></i> Aktif Memantau</span>
                    <?php else: ?>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-3 py-2"><i class="bi bi-dash-circle me-1"></i> Nonaktif</span>
                    <?php endif; ?>
                </div>

                <div class="detail-label">Diinput / Diedit Oleh</div>
                <div class="detail-value">
                    <div class="fw-bold"><?php echo htmlspecialchars($data['nama_lengkap'] ?? 'Sistem / Tidak Diketahui'); ?></div>
                    <?php if(!empty($data['nama_role'])): ?>
                        <small class="text-muted"><i class="bi bi-shield-check me-1"></i> <?php echo htmlspecialchars($data['nama_role']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="detail-label">Didaftarkan Pada</div>
                        <div class="detail-value small">
                            <?php echo date('d M Y', strtotime($data['created_at'])); ?><br>
                            <span class="text-muted"><?php echo date('H:i:s', strtotime($data['created_at'])); ?> WIB</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="detail-label">Update Terakhir</div>
                        <div class="detail-value small">
                            <?php echo date('d M Y', strtotime($data['updated_at'])); ?><br>
                            <span class="text-muted"><?php echo date('H:i:s', strtotime($data['updated_at'])); ?> WIB</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Tombol Aksi Bawah -->
    <?php if ($_SESSION['id_role'] == 1): // Hanya admin yang boleh mengedit dari halaman detail ?>
    <div class="d-flex justify-content-end mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
        <a href="edit_penyakit.php?id=<?php echo $data['id_penyakit']; ?>" class="btn btn-warning px-4 fw-semibold text-dark shadow-sm">
            <i class="bi bi-pencil-square me-2"></i> Edit Data Penyakit
        </a>
    </div>
    <?php endif; ?>

</div>

<?php include '../../layout/footer.php'; ?>