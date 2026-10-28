<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

// Hanya admin (opsional, tapi sesuai standar master data, edit biasa dibatasi)
// Jika ingin semua bisa edit, Anda bisa hapus blok IF ini.
if ($_SESSION['id_role'] != 1) {
    $_SESSION['alert_gagal'] = "Akses Ditolak! Hanya Administrator yang dapat mengedit Master Data.";
    header("Location: index.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_penyakit = (int)$_GET['id'];

// Ambil data gabungan dari EWS dan Master ICD-10
$query = "
    SELECT p.*, i.deskripsi_id, i.deskripsi_en, i.kategori 
    FROM tabel_penyakit p
    JOIN tabel_master_icd10 i ON p.kode_icd = i.kode_icd
    WHERE p.id_penyakit = '$id_penyakit'
";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['alert_gagal'] = "Data penyakit tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$data = mysqli_fetch_assoc($result);

include '../../layout/header.php';
?>

<style>
    body {
        background-color: var(--bg-body);
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        margin: 0;
        padding: 20px;
    }
    .form-container {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 900px;
        padding: 3rem;
        color: var(--text-color);
    }
    .form-label { font-weight: 600; font-size: 0.9rem; }
    .form-control, .form-select {
        background-color: var(--bg-body);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
    .form-control:focus, .form-select:focus {
        background-color: var(--bg-body);
        color: var(--text-color);
        border-color: #ffc107;
        box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
    }
    .form-control[readonly] {
        background-color: rgba(0,0,0,0.05);
        opacity: 0.7;
        cursor: not-allowed;
    }
    [data-theme="dark"] .form-control[readonly] {
        background-color: rgba(255,255,255,0.05);
    }
</style>

<div class="form-container">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i> Edit Data Penyakit</h4>
            <p class="text-muted small mb-0">Perbarui informasi master data dan pengaturan radar EWS.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Batal
        </a>
    </div>

    <form action="proses_edit_penyakit.php" method="POST">
        <!-- Hidden input untuk ID Penyakit -->
        <input type="hidden" name="id_penyakit" value="<?php echo $data['id_penyakit']; ?>">
        
        <div class="row g-4">
            <!-- Kolom Kiri: Data Master ICD-10 -->
            <div class="col-md-6 border-end pe-md-4">
                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-journal-medical me-2"></i>Data Standar ICD-10</h6>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Kode ICD-10 (Tidak dapat diubah)</label>
                    <input type="text" class="form-control text-uppercase fw-bold" name="kode_icd" value="<?php echo htmlspecialchars($data['kode_icd']); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label text-danger">Deskripsi Bahasa Indonesia *</label>
                    <textarea class="form-control" name="deskripsi_id" rows="2" required><?php echo htmlspecialchars($data['deskripsi_id']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label text-danger">Deskripsi English *</label>
                    <textarea class="form-control" name="deskripsi_en" rows="2" required><?php echo htmlspecialchars($data['deskripsi_en']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori (Bab ICD-10)</label>
                    <input type="text" class="form-control" name="kategori" value="<?php echo htmlspecialchars($data['kategori']); ?>">
                </div>
            </div>

            <!-- Kolom Kanan: Pengaturan EWS Dashboard -->
            <div class="col-md-6 ps-md-4">
                <h6 class="fw-bold text-success mb-3"><i class="bi bi-speedometer2 me-2"></i>Pengaturan Radar EWS</h6>
                
                <div class="mb-3">
                    <label class="form-label text-danger">Nama Populer (Dashboard) *</label>
                    <input type="text" class="form-control" name="nama_populer" value="<?php echo htmlspecialchars($data['nama_populer']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status Pantauan</label>
                    <select class="form-select" name="status_pantau">
                        <option value="aktif" <?php echo ($data['status_pantau'] == 'aktif') ? 'selected' : ''; ?>>Aktif (Dipantau EWS)</option>
                        <option value="nonaktif" <?php echo ($data['status_pantau'] == 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
                
                <div class="alert alert-info mt-4 small border-0 bg-info bg-opacity-10 text-info">
                    <i class="bi bi-info-circle-fill me-1"></i> Data yang Anda ubah akan dicatat atas nama Anda (<strong><?php echo $_SESSION['nama_lengkap']; ?></strong>) untuk keperluan audit trail.
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-warning px-4 fw-semibold text-dark shadow-sm"><i class="bi bi-save me-2"></i>Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php include '../../layout/footer.php'; ?>