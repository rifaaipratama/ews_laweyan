<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

$id_kelurahan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil Data Profil Kelurahan
$query_detail = "
    SELECT k.*, u.nama_lengkap, r.nama_role 
    FROM tabel_kelurahan k
    LEFT JOIN tabel_pengguna u ON k.id_pengguna = u.id_pengguna
    LEFT JOIN tabel_role r ON u.id_role = r.id_role
    WHERE k.id_kelurahan = $id_kelurahan
";
$result = mysqli_query($koneksi, $query_detail);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    $_SESSION['alert_gagal'] = "Data kelurahan tidak ditemukan.";
    header("Location: index.php");
    exit;
}

// Ambil Data Statistik Kasus Spesifik Kelurahan Ini
$q_kasus = mysqli_query($koneksi, "
    SELECT p.nama_populer, COUNT(kas.id_kasus) as total
    FROM tabel_kasus kas
    JOIN tabel_penyakit p ON kas.id_penyakit = p.id_penyakit
    WHERE kas.id_kelurahan = $id_kelurahan 
      AND kas.is_deleted = 0 
      AND kas.status_kasus IN ('suspek', 'terkonfirmasi')
    GROUP BY kas.id_penyakit
    ORDER BY total DESC
");

$data_kasus = [];
$total_semua_kasus = 0;
while ($row = mysqli_fetch_assoc($q_kasus)) {
    $data_kasus[] = $row;
    $total_semua_kasus += $row['total'];
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
        background-color: rgba(13, 110, 253, 0.03);
        border: 1px solid rgba(13, 110, 253, 0.1);
        border-radius: 12px;
        padding: 15px 20px;
        height: 100%;
        transition: all 0.3s ease;
    }
    [data-theme="dark"] .info-card { background-color: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.05); }

    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .info-value {
        font-size: 1.15rem;
        color: var(--text-main);
        font-weight: 700;
    }

    /* Kotak Khusus Statistik Kasus */
    .case-card {
        background: linear-gradient(135deg, rgba(220,53,69,0.05) 0%, rgba(220,53,69,0.15) 100%);
        border: 1px solid rgba(220,53,69,0.2);
        border-radius: 16px;
        padding: 20px;
        height: 100%;
    }
    .case-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 8px 12px;
        background: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        margin-bottom: 8px;
    }

    /* Kotak Geo Polygon */
    .polygon-box {
        background-color: var(--bg-body);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1rem;
        max-height: 120px;
        overflow-y: auto;
        font-size: 0.8rem;
        color: var(--text-muted);
        white-space: pre-wrap;
    }
    .polygon-box::-webkit-scrollbar { width: 4px; }
    .polygon-box::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }

    .btn-quick-action {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        font-weight: 600; padding: 12px 20px; border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-quick-action:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.1); }
</style>

<div class="detail-container">
    
    <!-- HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                <i class="bi bi-pin-map-fill fs-3"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="color: var(--text-main);">Kelurahan <?= htmlspecialchars($data['nama_kelurahan']); ?></h3>
                <p class="text-muted small mb-0 mt-1">Profil Wilayah & Data Statistik Real-Time EWS</p>
            </div>
        </div>
        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 fw-medium shadow-sm">
            <i class="bi bi-arrow-left me-2"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- ROW 1: MINI CARDS INFO -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="info-card">
                <div class="info-label">Kode Wilayah (BPS)</div>
                <div class="info-value text-primary"><?= htmlspecialchars($data['kode_wilayah']); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-card">
                <div class="info-label">Kode Pos</div>
                <div class="info-value"><?= htmlspecialchars($data['kodepos']); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-card">
                <div class="info-label">Populasi Penduduk</div>
                <div class="info-value"><?= number_format($data['jumlah_penduduk'], 0, ',', '.'); ?> <span style="font-size:0.8rem; font-weight:normal;">Jiwa</span></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-card">
                <div class="info-label">Luas Wilayah</div>
                <div class="info-value"><?= number_format($data['luas_wilayah_km2'], 2, ',', '.'); ?> <span style="font-size:0.8rem; font-weight:normal;">km&sup2;</span></div>
            </div>
        </div>
    </div>

    <!-- ROW 2: STATISTIK KASUS & METADATA -->
    <div class="row g-4 mb-4">
        
        <!-- Kiri: Statistik Penyakit -->
        <div class="col-lg-6">
            <div class="case-card">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-danger border-opacity-25 pb-2">
                    <h6 class="fw-bold text-danger mb-0"><i class="bi bi-clipboard2-pulse-fill me-2"></i>Status Kasus Aktif</h6>
                    <span class="badge bg-danger rounded-pill px-3"><?= $total_semua_kasus ?> Total Kasus</span>
                </div>
                
                <div style="max-height: 180px; overflow-y: auto; padding-right: 5px;">
                    <?php if (count($data_kasus) > 0): ?>
                        <?php foreach ($data_kasus as $kasus): ?>
                            <div class="case-item shadow-sm">
                                <span class="fw-bold" style="color: var(--text-main); font-size: 0.9rem;"><?= htmlspecialchars($kasus['nama_populer']) ?></span>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><?= $kasus['total'] ?> Kasus</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 opacity-50">
                            <i class="bi bi-shield-check text-success fs-1 mb-2 d-block"></i>
                            <span class="text-muted fw-medium">Alhamdulillah, tidak ada kasus aktif terdeteksi.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Kanan: Meta Data & Polygon -->
        <div class="col-lg-6 d-flex flex-column gap-3">
            <div class="info-card flex-grow-0" style="padding: 12px 20px;">
                <div class="info-label mb-1">Dikelola Terakhir Oleh</div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-badge-fill fs-5" style="color: #0aa2c0;"></i>
                    <span class="fw-bold" style="color: var(--text-main);"><?= htmlspecialchars($data['nama_lengkap'] ?? 'Sistem'); ?></span>
                    <?php if(!empty($data['nama_role'])): ?>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary" style="font-size:0.65rem;"><?= htmlspecialchars($data['nama_role']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="flex-grow-1">
                <div class="info-label mb-2"><i class="bi bi-braces me-1"></i> Data Koordinat GeoJSON</div>
                <?php if(!empty($data['geo_polygon'])): ?>
                    <pre class="polygon-box m-0 shadow-sm"><code><?= htmlspecialchars($data['geo_polygon']); ?></code></pre>
                <?php else: ?>
                    <div class="polygon-box d-flex align-items-center justify-content-center fst-italic shadow-sm">Data koordinat pemetaan belum direkam.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ROW 3: QUICK ACTIONS -->
    <h6 class="fw-bold mb-3" style="color: var(--text-main); border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;"><i class="bi bi-lightning-charge-fill text-warning me-2"></i> Tindakan Cepat (Quick Actions)</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <!-- Asumsi Anda memiliki filter_kelurahan di halaman kasus -->
            <a href="../kasus/index.php?filter_kelurahan=<?= $id_kelurahan ?>" class="btn btn-primary w-100 btn-quick-action">
                <i class="bi bi-folder-plus fs-5"></i> Buka Data Kasus
            </a>
        </div>
        <div class="col-md-4">
            <a href="../notifikasi/index.php" class="btn btn-warning text-dark w-100 btn-quick-action">
                <i class="bi bi-bell-fill fs-5"></i> Cek Pusat Notifikasi
            </a>
        </div>
        <div class="col-md-4">
            <a href="../peta/index.php" class="btn btn-info text-white w-100 btn-quick-action">
                <i class="bi bi-map-fill fs-5"></i> Lihat di Peta Sebaran
            </a>
        </div>
    </div>

    <div class="text-center mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
        <small class="text-muted d-block"><i class="bi bi-clock-history me-1"></i> Data Dibuat: <?= date('d M Y, H:i', strtotime($data['created_at'])); ?> &bull; Terakhir Diperbarui: <?= date('d M Y, H:i', strtotime($data['updated_at'])); ?></small>
    </div>

</div>

<?php include '../../layout/footer.php'; ?>