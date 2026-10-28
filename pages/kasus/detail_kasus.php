<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_kasus = intval($_GET['id']);
$id_role  = isset($_SESSION['id_role']) ? $_SESSION['id_role'] : 0;
$is_admin = ($id_role == 1); // Cek apakah pengguna adalah Administrator

// Query kompleks untuk mengambil semua data relasi
$query = "
    SELECT 
        k.*, 
        p.nama_populer, 
        kel.nama_kelurahan, 
        u.nama_lengkap AS nama_petugas
    FROM tabel_kasus k
    JOIN tabel_penyakit p ON k.id_penyakit = p.id_penyakit
    JOIN tabel_kelurahan kel ON k.id_kelurahan = kel.id_kelurahan
    JOIN tabel_pengguna u ON k.id_pengguna = u.id_pengguna
    WHERE k.id_kasus = $id_kasus
";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Data kasus tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

$data = mysqli_fetch_assoc($result);

// Kalkulasi Usia
$usia = "-";
if (!empty($data['tanggal_lahir'])) {
    $tgl_lahir = new DateTime($data['tanggal_lahir']);
    $hari_ini = new DateTime('today');
    $usia = $hari_ini->diff($tgl_lahir)->y . " Tahun";
}

// Logika Keamanan Data: Masking NIK
$nik_asli = htmlspecialchars($data['nik_pasien']);
if (strlen($nik_asli) == 16) {
    $nik_masking = substr($nik_asli, 0, 6) . 'xxxxxx' . substr($nik_asli, 12, 4);
} else {
    $nik_masking = $nik_asli; // Fallback jika format salah
}

// Format Tanggal
$tgl_lapor     = date('d F Y', strtotime($data['tanggal_lapor']));
$tgl_lahir_fmt = !empty($data['tanggal_lahir']) ? date('d F Y', strtotime($data['tanggal_lahir'])) : '<i class="text-muted small">Belum diinput</i>';
$tgl_onset     = !empty($data['tanggal_onset']) ? date('d F Y', strtotime($data['tanggal_onset'])) : '<i class="text-muted small">Belum direkam</i>';
$tgl_diagnosis = !empty($data['tanggal_diagnosis']) ? date('d F Y', strtotime($data['tanggal_diagnosis'])) : '<i class="text-muted small">Belum direkam</i>';
$tgl_selesai   = !empty($data['tanggal_selesai']) ? date('d F Y', strtotime($data['tanggal_selesai'])) : '<i class="text-muted small">Kasus masih aktif / Belum selesai</i>';

$created_at = date('Y-m-d H:i:s', strtotime($data['created_at']));
$updated_at = !empty($data['updated_at']) ? date('Y-m-d H:i:s', strtotime($data['updated_at'])) : $created_at;

// Hanya panggil header untuk CSS/Tema
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
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 950px;
        padding: 3rem;
        position: relative;
        overflow: hidden;
    }
    
    /* Dekorasi Latar Belakang Sudut Atas */
    .detail-container::before {
        content: '';
        position: absolute;
        top: 0; right: 0; left: 0;
        height: 6px;
        background: linear-gradient(90deg, #0dcaf0, #0d6efd);
    }

    .data-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-color);
        opacity: 0.6;
        margin-bottom: 0.3rem;
        font-weight: 700;
    }
    .data-value {
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 1.5rem;
    }
    
    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #0d6efd;
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px dashed var(--border-color);
    }

    /* Box Kustom untuk highlight status */
    .status-box {
        background-color: var(--bg-body);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        height: 100%;
    }

    /* Animasi Toggle NIK */
    #btnToggleNik {
        transition: transform 0.2s, color 0.2s;
        cursor: pointer;
    }
    #btnToggleNik:hover {
        transform: scale(1.15);
        color: #0a58ca !important;
    }
</style>

<div class="detail-container my-4">
    
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h3 class="fw-bold mb-1" style="color: var(--text-color);">
                <i class="bi bi-eye text-info me-2"></i> Detail Lengkap Kasus
            </h3>
            <p class="text-muted small mb-0">Informasi menyeluruh rekam data dari database sistem EWS.</p>
        </div>
        <div class="d-flex gap-2">
            <!-- Tombol Cetak Dokumen (Opsional) -->
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-printer me-1"></i> Cetak
            </button>
            <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT GRID -->
    <div class="row g-4 mb-2">
        
        <!-- KOLOM KIRI (Demografi & Sistem) -->
        <div class="col-lg-7">
            
            <!-- BLOK IDENTITAS PASIEN -->
            <div class="section-title mt-2"><i class="bi bi-person-vcard"></i> Identitas Demografi Pasien</div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="data-label">NIK Pasien</div>
                    <div class="data-value d-flex align-items-center" style="font-family: monospace; font-size: 1.1rem; color: #0d6efd;">
                        <span id="nikText" data-asli="<?= $nik_asli; ?>" data-mask="<?= $nik_masking; ?>">
                            <?= $nik_masking; ?>
                        </span>
                        
                        <?php if ($is_admin): ?>
                            <button type="button" id="btnToggleNik" class="btn btn-sm text-primary ms-2 p-0" style="background: none; border: none; box-shadow: none;" title="Tampilkan/Sembunyikan NIK">
                                <i id="iconToggleNik" class="bi bi-eye-slash-fill fs-5"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="data-label">Nama Lengkap Pasien</div>
                    <div class="data-value"><?= htmlspecialchars($data['nama_pasien']); ?></div>
                </div>
                <div class="col-sm-6">
                    <div class="data-label">Jenis Kelamin</div>
                    <div class="data-value">
                        <?= ($data['jenis_kelamin'] == 'L') ? '<i class="bi bi-gender-male text-primary me-1"></i> Laki-Laki (L)' : '<i class="bi bi-gender-female text-danger me-1"></i> Perempuan (P)'; ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="data-label">Tanggal Lahir & Usia</div>
                    <div class="data-value"><?= $tgl_lahir_fmt; ?> <span class="badge bg-secondary ms-2"><?= $usia; ?></span></div>
                </div>
            </div>

            <!-- BLOK LOKASI & PENCATATAN -->
            <div class="section-title mt-4"><i class="bi bi-geo-alt"></i> Lokasi & Admin Pencatat</div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="data-label">Lokasi Kejadian (Kelurahan)</div>
                    <div class="data-value"><?= htmlspecialchars($data['nama_kelurahan']); ?></div>
                </div>
                <div class="col-sm-6">
                    <div class="data-label">ID Kasus (Primary Key)</div>
                    <div class="data-value">#<?= $data['id_kasus']; ?></div>
                </div>
                <div class="col-12">
                    <div class="data-label">Petugas / Admin Perekam</div>
                    <div class="data-value d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge-fill text-muted"></i> 
                        <?= htmlspecialchars($data['nama_petugas']); ?> 
                        <span class="badge border text-muted ms-2">ID: <?= $data['id_pengguna']; ?></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (Klinis & Status Box) -->
        <div class="col-lg-5">
            <div class="status-box shadow-sm">
                
                <div class="section-title border-0 pb-0 mb-4" style="color: #dc3545;">
                    <i class="bi bi-heart-pulse"></i> Rekam Klinis
                </div>

                <div class="data-label">Klasifikasi Penyakit</div>
                <div class="data-value fs-5 text-primary">
                    <?= htmlspecialchars($data['nama_populer']); ?>
                </div>

                <div class="data-label">Status Kasus Saat Ini</div>
                <div class="data-value mb-4">
                    <?php
                        $bg = 'bg-secondary';
                        if ($data['status_kasus'] == 'terkonfirmasi') $bg = 'bg-danger';
                        if ($data['status_kasus'] == 'suspek') $bg = 'bg-warning text-dark';
                        if ($data['status_kasus'] == 'sembuh') $bg = 'bg-success';
                        if ($data['status_kasus'] == 'meninggal') $bg = 'bg-dark';
                    ?>
                    <span class="badge <?= $bg; ?> px-4 py-2 fs-6 shadow-sm"><?= strtoupper($data['status_kasus']); ?></span>
                </div>

                <hr class="my-4" style="border-color: var(--border-color);">

                <div class="data-label"><i class="bi bi-calendar-check me-1"></i> Tanggal Pelaporan</div>
                <div class="data-value fs-6 mb-3"><?= $tgl_lapor; ?></div>

                <div class="data-label"><i class="bi bi-thermometer-half me-1"></i> Tanggal Onset (Gejala)</div>
                <div class="data-value fs-6 mb-3"><?= $tgl_onset; ?></div>

                <div class="data-label"><i class="bi bi-clipboard2-pulse me-1"></i> Tanggal Diagnosis Medis</div>
                <div class="data-value fs-6 mb-3"><?= $tgl_diagnosis; ?></div>

                <div class="data-label"><i class="bi bi-check2-circle me-1"></i> Tanggal Selesai (Sembuh/Meninggal)</div>
                <div class="data-value fs-6 mb-0"><?= $tgl_selesai; ?></div>

            </div>
        </div>
    </div>

    <!-- FOOTER TIMESTAMP -->
    <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top" style="border-color: var(--border-color) !important;">
        <div class="text-muted small">
            <i class="bi bi-clock me-1"></i> Dibuat pada: <?= $created_at; ?>
        </div>
        <div class="text-muted small">
            <i class="bi bi-arrow-repeat me-1"></i> Terakhir diperbarui: <?= $updated_at; ?>
        </div>
    </div>

</div>

<?php if ($is_admin): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const btnToggle = document.getElementById("btnToggleNik");
        const iconToggle = document.getElementById("iconToggleNik");
        const nikText = document.getElementById("nikText");

        let isMasked = true;

        btnToggle.addEventListener("click", function() {
            if (isMasked) {
                // Tampilkan NIK Asli
                nikText.textContent = nikText.getAttribute("data-asli");
                iconToggle.classList.remove("bi-eye-slash-fill");
                iconToggle.classList.add("bi-eye-fill");
            } else {
                // Samarkan NIK
                nikText.textContent = nikText.getAttribute("data-mask");
                iconToggle.classList.remove("bi-eye-fill");
                iconToggle.classList.add("bi-eye-slash-fill");
            }
            isMasked = !isMasked;
        });
    });
</script>
<?php endif; ?>

<?php include '../../layout/footer.php'; ?>