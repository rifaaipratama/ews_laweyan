<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Tangkap NIK dari URL hasil pencarian
if (!isset($_GET['nik']) || empty($_GET['nik'])) {
    header("Location: tambah_kasus.php");
    exit;
}

$nik_pasien = mysqli_real_escape_string($koneksi, $_GET['nik']);

// Ambil data historis pasien untuk Auto-fill
$q_pasien = mysqli_query($koneksi, "SELECT nik_pasien, nama_pasien, jenis_kelamin, tanggal_lahir FROM tabel_kasus WHERE nik_pasien = '$nik_pasien' ORDER BY tanggal_lapor DESC LIMIT 1");

if(mysqli_num_rows($q_pasien) == 0){
    echo "<script>alert('Data pasien tidak ditemukan!'); window.location='tambah_kasus.php';</script>";
    exit;
}
$pasien = mysqli_fetch_assoc($q_pasien);

// Panggil header untuk CSS/Tema saja
include '../../layout/header.php';
?>

<!-- ============================================== -->
<!-- TAMBAHAN LIBRARY SELECT2 (CSS)                 -->
<!-- ============================================== -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

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
    .form-container {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        width: 100%;
        max-width: 900px;
        padding: 3rem;
    }
    .form-label { font-weight: 600; color: var(--text-color); font-size: 0.9rem; margin-bottom: 0.5rem; }
    
    /* Input & Select standar (non-Select2) */
    .form-control, .form-select:not(.select2-hidden-accessible) {
        background-color: var(--bg-body);
        color: var(--text-color);
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        border-radius: 8px;
    }
    
    .form-control:focus, .form-select:not(.select2-hidden-accessible):focus { 
        border-color: #0d6efd; 
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); 
    }
    .form-control[readonly] { 
        background-color: rgba(0,0,0,0.05); opacity: 0.8; cursor: not-allowed; 
    }
    
    [data-theme="dark"] .form-control[readonly] { background-color: rgba(255,255,255,0.05); }
    .section-title { 
        color: #0d6efd; font-weight: 700; text-transform: uppercase; 
        font-size: 0.85rem; letter-spacing: 1px; margin-bottom: 1.5rem; 
        border-bottom: 1px dashed var(--border-color); padding-bottom: 0.5rem; 
    }

    /* Kustomisasi khusus untuk Select2 agar serasi dengan tinggi form-control */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 48px;
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background-color: var(--bg-body);
        color: var(--text-color);
        display: flex;
        align-items: center;
    }
    .select2-container--bootstrap-5.select2-container--focus .select2-selection {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .select2-dropdown {
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: var(--text-color);
    }
</style>

<div class="form-container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-color);">
                <i class="bi bi-file-earmark-medical-fill text-primary me-2"></i> Entri Kasus Lanjutan
            </h4>
            <p class="text-muted small mb-0">Tambahkan rekam medis kasus baru untuk pasien terdaftar.</p>
        </div>
        <a href="tambah_kasus.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Batal
        </a>
    </div>

    <form action="proses_insert_kasus.php" method="POST">
        <input type="hidden" name="id_pengguna" value="<?php echo $_SESSION['id_pengguna']; ?>">

        <!-- BAGIAN 1: AUTO-FILL -->
        <div class="section-title mt-2"><i class="bi bi-person-check me-1"></i> Identitas Pasien (Otomatis Terisi)</div>
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label">NIK Pasien</label>
                <input type="text" class="form-control" name="nik_pasien" value="<?= htmlspecialchars($pasien['nik_pasien']); ?>" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nama Lengkap Pasien</label>
                <input type="text" class="form-control" name="nama_pasien" value="<?= htmlspecialchars($pasien['nama_pasien']); ?>" readonly>
            </div>
        </div>
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <label class="form-label">Jenis Kelamin</label>
                <input type="hidden" name="jenis_kelamin" value="<?= htmlspecialchars($pasien['jenis_kelamin']); ?>">
                <select class="form-select" disabled>
                    <option <?= ($pasien['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-Laki (L)</option>
                    <option <?= ($pasien['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan (P)</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control" name="tanggal_lahir" value="<?= htmlspecialchars($pasien['tanggal_lahir'] ?? ''); ?>" readonly>
            </div>
        </div>

        <!-- BAGIAN 2: DATA KASUS BARU -->
        <div class="section-title"><i class="bi bi-clipboard2-pulse me-1"></i> Data Kunjungan / Kasus Baru</div>
        <div class="row g-4 mb-4">
            
           <!-- DROPDOWN PENYAKIT (Dengan Class select2-pencarian) -->
            <div class="col-md-6">
                <label class="form-label">Diagnosis Penyakit Baru <span class="text-danger">*</span></label>
                <select class="form-select select2-pencarian" name="id_penyakit" required data-placeholder="-- Ketik Nama Penyakit --">
                    <option value="" selected disabled></option>
                    <?php
                    // Kembali menggunakan query asli yang dijamin aman
                    $q_penyakit = mysqli_query($koneksi, "SELECT id_penyakit, nama_populer FROM tabel_penyakit WHERE status_pantau = 'aktif'");
                    while($p = mysqli_fetch_assoc($q_penyakit)) {
                        echo "<option value='{$p['id_penyakit']}'>{$p['nama_populer']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- DROPDOWN KELURAHAN (Dengan Class select2-pencarian) -->
            <div class="col-md-6">
                <label class="form-label">Kelurahan (Lokasi Kejadian Baru) <span class="text-danger">*</span></label>
                <select class="form-select select2-pencarian" name="id_kelurahan" required data-placeholder="-- Ketik Nama Kelurahan --">
                    <option value="" selected disabled></option>
                    <?php
                    $q_kelurahan = mysqli_query($koneksi, "SELECT id_kelurahan, nama_kelurahan FROM tabel_kelurahan ORDER BY nama_kelurahan ASC");
                    while($k = mysqli_fetch_assoc($q_kelurahan)) {
                        echo "<option value='{$k['id_kelurahan']}'>{$k['nama_kelurahan']}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <label class="form-label">Tanggal Pelaporan <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="tanggal_lapor" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Onset (Mulai Gejala)</label>
                <input type="date" class="form-control" name="tanggal_onset" max="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status Pasien <span class="text-danger">*</span></label>
                <select class="form-select" name="status_kasus" required>
                    <option value="suspek">Suspek (Menunggu Lab)</option>
                    <option value="terkonfirmasi" selected>Terkonfirmasi (Positif)</option>
                </select>
            </div>
        </div>

        <hr style="border-color: var(--border-color); margin-bottom: 2rem;">
        <div class="d-flex justify-content-end gap-3">
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm">
                <i class="bi bi-save2-fill me-2"></i> Simpan Kasus Lanjutan
            </button>
        </div>
    </form>
</div>

<!-- ============================================== -->
<!-- TAMBAHAN LIBRARY SELECT2 (JAVASCRIPT)          -->
<!-- ============================================== -->
<!-- Wajib load jQuery dulu sebelum Select2 -->

<?php include '../../layout/footer.php'; ?>

<!-- Script diletakkan di bawah footer agar tidak tertimpa -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-pencarian').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true
        });
    });
</script>