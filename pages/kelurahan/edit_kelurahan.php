<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

$id_kelurahan = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query_old = "SELECT * FROM tabel_kelurahan WHERE id_kelurahan = $id_kelurahan";
$result_old = mysqli_query($koneksi, $query_old);
$data = mysqli_fetch_assoc($result_old);

if (!$data) {
    $_SESSION['alert_gagal'] = "Data kelurahan tidak ditemukan.";
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

    .form-container {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        width: 100%;
        max-width: 850px;
        padding: 3rem;
        transition: background-color 0.3s, border-color 0.3s;
    }

    .form-container p { color: var(--text-color) !important; opacity: 0.75; }

    .form-label {
        font-weight: 700;
        color: var(--text-main);
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control {
        background-color: var(--bg-body);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        border-radius: 10px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    /* Styling Khusus Input Terkunci */
    .input-locked {
        background-color: rgba(0,0,0,0.03) !important;
        border-color: rgba(0,0,0,0.05) !important;
        color: var(--text-muted) !important;
        cursor: not-allowed;
    }
    [data-theme="dark"] .input-locked {
        background-color: rgba(255,255,255,0.02) !important;
        border-color: rgba(255,255,255,0.05) !important;
    }

    /* Styling Khusus Input yang Bisa Diedit */
    .input-editable {
        border: 2px solid #0d6efd;
        background-color: rgba(13, 110, 253, 0.02);
    }
    .input-editable:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        background-color: var(--bg-body);
    }
</style>

<div class="form-container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-main);">
                <i class="bi bi-pencil-square text-warning me-2"></i> Edit Data Kelurahan #<?php echo $data['id_kelurahan']; ?>
            </h4>
            <p class="small mb-0 mt-1">Hanya data kependudukan yang dapat diperbarui secara manual.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-medium shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="alert alert-info border-0 shadow-sm mb-4" style="background-color: rgba(13, 110, 253, 0.1); color: #084298; border-radius: 12px;">
        <i class="bi bi-info-circle-fill me-2"></i> <b>Informasi:</b> Data master wilayah dan koordinat dikunci untuk menjaga integritas pemetaan EWS.
    </div>

    <form action="proses_edit_kelurahan.php" method="POST">
        <input type="hidden" name="id_kelurahan" value="<?php echo $data['id_kelurahan']; ?>">

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label text-muted"><i class="bi bi-lock-fill me-1"></i> Kode Wilayah (BPS)</label>
                <input type="text" class="form-control input-locked" name="kode_wilayah" value="<?php echo htmlspecialchars($data['kode_wilayah']); ?>" readonly tabindex="-1">
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted"><i class="bi bi-lock-fill me-1"></i> Nama Kelurahan</label>
                <input type="text" class="form-control input-locked" name="nama_kelurahan" value="<?php echo htmlspecialchars($data['nama_kelurahan']); ?>" readonly tabindex="-1">
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <label class="form-label text-muted"><i class="bi bi-lock-fill me-1"></i> Kode Pos</label>
                <input type="text" class="form-control input-locked" name="kodepos" value="<?php echo htmlspecialchars($data['kodepos']); ?>" readonly tabindex="-1">
            </div>
            
            <!-- INI ADALAH SATU-SATUNYA INPUT YANG BISA DIEDIT -->
            <div class="col-md-4">
                <label class="form-label text-primary"><i class="bi bi-pencil-fill me-1"></i> Populasi Penduduk</label>
                <div class="input-group">
                    <input type="number" min="0" class="form-control input-editable fw-bold" name="jumlah_penduduk" value="<?php echo $data['jumlah_penduduk']; ?>" required autofocus>
                    <span class="input-group-text bg-primary text-white border-primary fw-bold">Jiwa</span>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label text-muted"><i class="bi bi-lock-fill me-1"></i> Luas Area</label>
                <div class="input-group">
                    <input type="number" step="0.01" class="form-control input-locked" name="luas_wilayah_km2" value="<?php echo $data['luas_wilayah_km2']; ?>" readonly tabindex="-1">
                    <span class="input-group-text input-locked">km&sup2;</span>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12">
                <label class="form-label text-muted"><i class="bi bi-lock-fill me-1"></i> Koordinat Geografis (Geo Polygon)</label>
                <textarea class="form-control input-locked" name="geo_polygon" rows="3" readonly tabindex="-1"><?php echo htmlspecialchars($data['geo_polygon'] ?? ''); ?></textarea>
            </div>
        </div>

        <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

        <div class="d-flex justify-content-end gap-3">
            <a href="index.php" class="btn btn-outline-secondary px-4 fw-medium">Batal</a>
            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                <i class="bi bi-floppy-fill me-2"></i> Simpan Pembaruan
            </button>
        </div>
    </form>
</div>

<?php include '../../layout/footer.php'; ?>