<?php
session_start();

// PERBAIKAN: Hapus pembatasan $_SESSION['id_role'] != 1 agar Petugas bisa masuk
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// HANYA panggil header untuk memuat CSS & Tema. Sidebar dan Topbar KITA LEWATI.
include '../../layout/header.php';
?>

<!-- Tambahan Library Select2 (CSS) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    /* Canvas Kosong (Distraction-Free) */
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
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 950px;
        padding: 3rem;
        position: relative;
        overflow: hidden;
        transition: background-color 0.3s, border-color 0.3s;
    }

    /* Aksen Gradien Estetik di Atas Form */
    .form-container::before {
        content: '';
        position: absolute;
        top: 0; right: 0; left: 0;
        height: 6px;
        background: linear-gradient(90deg, #0dcaf0, #0d6efd);
    }

    .form-container p { color: var(--text-color) !important; opacity: 0.75; }
    
    .form-label {
        font-weight: 600;
        color: var(--text-color);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        opacity: 0.8;
    }

    /* Kustomisasi Input Modern */
    .form-control, .form-select:not(.select2-hidden-accessible) {
        background-color: var(--bg-body);
        color: var(--text-color);
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        border-radius: 8px;
        transition: all 0.2s ease-in-out;
    }
    .form-control:focus, .form-select:not(.select2-hidden-accessible):focus {
        background-color: var(--bg-body);
        color: var(--text-color);
        border-color: #0dcaf0;
        box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.2);
        transform: translateY(-1px);
    }
    
    /* Kustomisasi Select2 agar serasi */
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
        border-color: #0dcaf0;
        box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.2);
    }

    /* Section Divider Modern */
    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #0dcaf0;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 1px;
        margin-bottom: 1.5rem;
        border-bottom: 2px dashed var(--border-color);
        padding-bottom: 0.75rem;
    }
</style>

<div class="form-container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1" style="color: var(--text-color);">
                <i class="bi bi-person-vcard-fill text-info me-2"></i> Pendaftaran Pasien Baru
            </h3>
            <p class="text-muted small mb-0">Lengkapi data demografi dan klinis awal pasien untuk Buku Induk.</p>
        </div>
        <a href="<?php echo ($_SESSION['id_role'] == 1) ? 'index.php' : '../kasus/tambah_kasus.php'; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <form action="proses_tambah_pasien.php" method="POST">
        <!-- Hidden input: ID Petugas yang merekam -->
        <input type="hidden" name="id_pengguna" value="<?php echo $_SESSION['id_pengguna']; ?>">

        <!-- BAGIAN 1: IDENTITAS DEMOGRAFI -->
        <div class="section-title mt-2"><i class="bi bi-person-badge"></i> Data Identitas Diri (Demografi)</div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label">NIK Pasien <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-muted"><i class="bi bi-credit-card"></i></span>
                    <input type="text" class="form-control" name="nik_pasien" maxlength="16" pattern="[0-9]{16}" placeholder="16 digit angka identitas" required autofocus>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nama Lengkap Pasien <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nama_pasien" placeholder="Sesuai kartu identitas" required>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                <select class="form-select" name="jenis_kelamin" required>
                    <option value="" selected disabled>-- Pilih Gender --</option>
                    <option value="L">Laki-Laki (L)</option>
                    <option value="P">Perempuan (P)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control" name="tanggal_lahir" max="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Nomor Telepon / HP</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-muted"><i class="bi bi-telephone"></i></span>
                    <input type="tel" class="form-control" name="no_telepon" placeholder="Contoh: 08123...">
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <label class="form-label">Kelurahan (Domisili) <span class="text-danger">*</span></label>
                <select class="form-select select2-pencarian" name="id_kelurahan" required data-placeholder="-- Pilih Wilayah --">
                    <option value="" selected disabled></option>
                    <?php
                    $q_kelurahan = mysqli_query($koneksi, "SELECT id_kelurahan, nama_kelurahan FROM tabel_kelurahan ORDER BY nama_kelurahan ASC");
                    while($k = mysqli_fetch_assoc($q_kelurahan)) {
                        echo "<option value='{$k['id_kelurahan']}'>{$k['nama_kelurahan']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">Alamat Lengkap</label>
                <textarea class="form-control" name="alamat_lengkap" rows="1" placeholder="Nama jalan, RT/RW, pedukuhan..."></textarea>
            </div>
        </div>

        <!-- BAGIAN 2: DATA KLINIS AWAL -->
        <div class="section-title"><i class="bi bi-heart-pulse"></i> Rekam Medis (Kunjungan Pertama)</div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label">Diagnosis Penyakit <span class="text-danger">*</span></label>
                <select class="form-select select2-pencarian" name="id_penyakit" required data-placeholder="-- Pilih Penyakit --">
                    <option value="" selected disabled></option>
                    <?php
                    $q_penyakit = mysqli_query($koneksi, "SELECT id_penyakit, nama_populer FROM tabel_penyakit WHERE status_pantau = 'aktif'");
                    while($p = mysqli_fetch_assoc($q_penyakit)) {
                        echo "<option value='{$p['id_penyakit']}'>{$p['nama_populer']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status Pasien Saat Ini <span class="text-danger">*</span></label>
                <select class="form-select" name="status_kasus" required>
                    <option value="suspek">Suspek (Menunggu Uji Lab)</option>
                    <option value="terkonfirmasi" selected>Terkonfirmasi (Positif)</option>
                </select>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <label class="form-label">Tanggal Pelaporan <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="tanggal_lapor" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tanggal Onset (Mulai Gejala)</label>
                <input type="date" class="form-control" name="tanggal_onset" max="<?php echo date('Y-m-d'); ?>">
                <small class="text-muted" style="font-size: 0.75rem;">Opsional. Tanggal pasien pertama kali merasa sakit.</small>
            </div>
        </div>

        <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

        <div class="d-flex justify-content-end gap-3">
            <button type="reset" class="btn btn-outline-secondary rounded-pill px-4 py-2">Batal / Reset</button>
            <button type="submit" class="btn btn-info text-white rounded-pill px-4 py-2 fw-semibold shadow-sm">
                <i class="bi bi-save2-fill me-2"></i> Simpan Pendaftaran Pasien
            </button>
        </div>
    </form>
</div>

<?php include '../../layout/footer.php'; ?>

<!-- ========================================== -->
    <!-- BLOK NOTIFIKASI SUKSES (METODE URL GET)    -->
    <!-- ========================================== -->
    <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert" style="border-left: 5px solid #198754 !important;">
                <i class="bi bi-check-circle-fill fs-4 text-success me-3"></i>
                <div>
                    Pasien atas nama <b><?= htmlspecialchars($_GET['nama'] ?? ''); ?></b> berhasil didaftarkan dan data SIMRS awal telah dibuat.
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- ========================================== -->

<!-- Tambahan Library Select2 (JS) diletakkan di bawah footer -->
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