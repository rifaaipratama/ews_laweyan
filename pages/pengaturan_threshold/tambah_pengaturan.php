<?php
session_start();

// Proteksi ganda: Harus Administrator
if (!isset($_SESSION['id_pengguna']) || $_SESSION['id_role'] != 1) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Ambil ID pengguna yang sedang login (untuk direkam secara diam-diam)
$id_pengguna_login = $_SESSION['id_pengguna'];

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

    .form-container {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        width: 100%;
        max-width: 850px;
        padding: 3rem;
        transition: background-color 0.3s, border-color 0.3s;
    }

    .form-container p { color: var(--text-color) !important; opacity: 0.75; }
    .form-label { font-weight: 600; color: var(--text-color); font-size: 0.9rem; margin-bottom: 0.5rem; }
    
    .form-control, .form-select {
        background-color: var(--bg-body);
        color: var(--text-color);
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        border-radius: 8px;
    }

    .form-control:focus, .form-select:focus {
        background-color: var(--bg-body);
        color: var(--text-color);
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
    }
</style>

<div class="form-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-color);">
                <i class="bi bi-sliders text-primary me-2"></i> Konfigurasi Threshold
            </h4>
            <p class="text-muted small mb-0">Atur batas pemicu Early Warning System (EWS) secara Global.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

    <!-- Alert Info (Memberitahu pengguna sistem cara kerjanya) -->
    <div class="alert alert-info border-info bg-opacity-10 d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-info-circle-fill me-3 fs-4 text-info"></i>
        <div>
            <strong>Informasi:</strong> Pengaturan ini bersifat <b>Global</b>. Jika penyakit yang Anda pilih sudah pernah diatur sebelumnya, sistem akan otomatis memperbarui angkanya, bukan membuat data baru.
        </div>
    </div>

    <form action="proses_tambah_pengaturan.php" method="POST">
        
        <!-- HIDDEN INPUT: Menyimpan ID admin yang login secara otomatis -->
        <input type="hidden" name="id_pengguna" value="<?= htmlspecialchars($id_pengguna_login); ?>">

        <div class="row g-4 mb-4">
            <div class="col-md-12">
                <label class="form-label">Pilih Jenis Penyakit (Pantauan) <span class="text-danger">*</span></label>
                <select class="form-select" name="id_penyakit" required>
                    <option value="" selected disabled>-- Silakan Pilih Penyakit --</option>
                    <?php
                    $q_penyakit = mysqli_query($koneksi, "SELECT id_penyakit, nama_populer FROM tabel_penyakit WHERE status_pantau = 'aktif' ORDER BY nama_populer ASC");
                    while($p = mysqli_fetch_assoc($q_penyakit)) {
                        echo "<option value='{$p['id_penyakit']}'>{$p['nama_populer']}</option>";
                    }
                    ?>
                </select>
                <small class="text-muted mt-2 d-block" style="font-size: 0.8rem;">Hanya menampilkan penyakit yang berstatus "Aktif" di Master Data.</small>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <label class="form-label">Batas Waspada (Kuning) <span class="text-danger">*</span></label>
                <input type="number" min="1" class="form-control" name="batas_waspada" placeholder="Contoh: 2" required>
                <small class="text-muted" style="font-size: 0.75rem;">Jumlah kasus minimal peringatan dini.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Batas Wabah (Merah) <span class="text-danger">*</span></label>
                <input type="number" min="1" class="form-control" name="batas_wabah" placeholder="Contoh: 5" required>
                <small class="text-muted" style="font-size: 0.75rem;">Status Kejadian Luar Biasa (KLB).</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Periode Waktu <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" min="1" class="form-control" name="periode_hari" value="7" required>
                    <span class="input-group-text" style="background-color: var(--bg-body); color: var(--text-color); border-color: var(--border-color);">Hari</span>
                </div>
                <small class="text-muted" style="font-size: 0.75rem;">Jendela analisis sistem EWS.</small>
            </div>
        </div>

        <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

        <div class="d-flex justify-content-end gap-3">
            <button type="reset" class="btn btn-outline-secondary px-4 py-2">Batal / Reset</button>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                <i class="bi bi-save me-2"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

<?php include '../../layout/footer.php'; ?>