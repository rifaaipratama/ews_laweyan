<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Ambil ID Kasus dari parameter URL
$id_kasus = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data lama berdasarkan ID Kasus
$query_old = "SELECT * FROM tabel_kasus WHERE id_kasus = $id_kasus";
$result_old = mysqli_query($koneksi, $query_old);
$data = mysqli_fetch_assoc($result_old);

if (!$data) {
    $_SESSION['alert_gagal'] = "Data kasus tidak ditemukan.";
    header("Location: index.php");
    exit;
}

// Logika Keamanan Data: Masking NIK
$nik_asli = htmlspecialchars($data['nik_pasien']);
if (strlen($nik_asli) == 16) {
    $nik_masking = substr($nik_asli, 0, 6) . 'xxxxxx' . substr($nik_asli, 12, 4);
} else {
    $nik_masking = $nik_asli; // Fallback jika format salah
}

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

    .form-container {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        width: 100%;
        max-width: 900px;
        padding: 3rem;
        position: relative;
        overflow: hidden;
    }

    /* Dekorasi Sudut Atas */
    .form-container::before {
        content: '';
        position: absolute;
        top: 0; right: 0; left: 0;
        height: 6px;
        background: linear-gradient(90deg, #ffc107, #fd7e14);
    }

    .form-label {
        font-weight: 600;
        color: var(--text-color);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        background-color: var(--bg-body);
        color: var(--text-color);
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        border-radius: 8px;
    }

    .form-control:focus, .form-select:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
    }
    
    /* Readonly styling */
    .form-control[readonly] {
        background-color: rgba(0,0,0,0.03);
        opacity: 0.8;
        cursor: not-allowed;
    }
    [data-theme="dark"] .form-control[readonly] { background-color: rgba(255,255,255,0.03); }

    .section-title {
        color: #fd7e14;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 1px;
        margin-bottom: 1.5rem;
        border-bottom: 1px dashed var(--border-color);
        padding-bottom: 0.5rem;
    }
</style>

<div class="form-container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1" style="color: var(--text-color);">
                <i class="bi bi-pencil-square text-warning me-2"></i> Edit Data Kasus #<?= $data['id_kasus']; ?>
            </h3>
            <p class="text-muted small mb-0">Perbarui informasi klinis atau perbaiki kesalahan input rekam medis.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <form action="proses_edit_kasus.php" method="POST">
        <!-- Hidden Input -->
        <input type="hidden" name="id_kasus" value="<?= $data['id_kasus']; ?>">
        <input type="hidden" name="status_lama" value="<?= $data['status_kasus']; ?>">
        
        <!-- NIK Asli dikirim via hidden input agar tidak rusak saat di-update -->
        <input type="hidden" name="nik_pasien" value="<?= $nik_asli; ?>">

        <!-- BAGIAN 1: IDENTITAS DEMOGRAFI -->
        <div class="section-title mt-2"><i class="bi bi-person-vcard me-1"></i> Data Identitas Diri (Demografi)</div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label">NIK Pasien (Readonly)</label>
                <!-- Input ini hanya untuk tampilan (tanpa atribut 'name') -->
                <input type="text" class="form-control" value="<?= $nik_masking; ?>" readonly>
                <small class="text-muted" style="font-size: 0.75rem;">NIK disamarkan dan terkunci untuk menjaga privasi serta relasi historis.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nama Lengkap Pasien <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nama_pasien" value="<?= htmlspecialchars($data['nama_pasien']); ?>" required>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                <select class="form-select" name="jenis_kelamin" required>
                    <option value="L" <?= ($data['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-Laki (L)</option>
                    <option value="P" <?= ($data['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan (P)</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control" name="tanggal_lahir" value="<?= $data['tanggal_lahir']; ?>" max="<?= date('Y-m-d'); ?>">
            </div>
        </div>

        <!-- BAGIAN 2: DATA KLINIS KASUS -->
        <div class="section-title"><i class="bi bi-clipboard2-pulse me-1"></i> Data Klinis Kunjungan</div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label">Diagnosis Penyakit (Pantauan) <span class="text-danger">*</span></label>
                <select class="form-select" name="id_penyakit" required>
                    <?php
                    $q_penyakit = mysqli_query($koneksi, "SELECT id_penyakit, nama_populer, kode_icd FROM tabel_penyakit WHERE status_pantau = 'aktif'");
                    while($p = mysqli_fetch_assoc($q_penyakit)) {
                        $selected = ($p['id_penyakit'] == $data['id_penyakit']) ? 'selected' : '';
                        echo "<option value='{$p['id_penyakit']}' {$selected}>{$p['nama_populer']} ({$p['kode_icd']})</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kelurahan (Lokasi Kejadian) <span class="text-danger">*</span></label>
                <select class="form-select" name="id_kelurahan" required>
                    <?php
                    $q_kelurahan = mysqli_query($koneksi, "SELECT id_kelurahan, nama_kelurahan FROM tabel_kelurahan ORDER BY nama_kelurahan ASC");
                    while($k = mysqli_fetch_assoc($q_kelurahan)) {
                        $selected = ($k['id_kelurahan'] == $data['id_kelurahan']) ? 'selected' : '';
                        echo "<option value='{$k['id_kelurahan']}' {$selected}>{$k['nama_kelurahan']}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <label class="form-label">Tanggal Lapor Awal <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="tanggal_lapor" value="<?= $data['tanggal_lapor']; ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Onset (Gejala)</label>
                <input type="date" class="form-control" name="tanggal_onset" value="<?= $data['tanggal_onset']; ?>" max="<?= date('Y-m-d'); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status Pasien Saat Ini <span class="text-danger">*</span></label>
                <select class="form-select" name="status_kasus" required>
                    <option value="suspek" <?= ($data['status_kasus'] == 'suspek') ? 'selected' : ''; ?>>Suspek (Menunggu Lab)</option>
                    <option value="terkonfirmasi" <?= ($data['status_kasus'] == 'terkonfirmasi') ? 'selected' : ''; ?>>Terkonfirmasi (Positif)</option>
                    <option value="sembuh" <?= ($data['status_kasus'] == 'sembuh') ? 'selected' : ''; ?>>Sembuh</option>
                    <option value="meninggal" <?= ($data['status_kasus'] == 'meninggal') ? 'selected' : ''; ?>>Meninggal</option>
                </select>
            </div>
        </div>

        <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

        <div class="d-flex justify-content-end gap-3">
            <a href="index.php" class="btn btn-outline-secondary px-4 py-2">Batal</a>
            <button type="submit" class="btn btn-warning px-4 py-2 fw-semibold text-dark shadow-sm">
                <i class="bi bi-arrow-repeat me-2"></i> Perbarui Data Kasus
            </button>
        </div>
    </form>
</div>

<?php include '../../layout/footer.php'; ?>