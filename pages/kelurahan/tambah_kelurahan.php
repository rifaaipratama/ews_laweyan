<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// HANYA panggil header untuk memuat CSS & Tema. Sidebar dan Topbar dilewati.
include '../../layout/header.php';
?>

<style>
    /* Mengubah body menjadi canvas kosong yang terpusat di tengah (Distraction-Free) */
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
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15); /* Shadow lembut */
        width: 100%;
        max-width: 850px;
        padding: 3rem;
        transition: background-color 0.3s, border-color 0.3s;
    }

    .form-container p {
        color: var(--text-color) !important;
        opacity: 0.75;
    }

    .form-label {
        font-weight: 600;
        color: var(--text-color);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    /* Kustomisasi Input dan Textarea agar responsif terhadap tema terang/gelap */
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
        border-color: #3b82f6; /* Warna biru fokus */
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
    }
</style>

<div class="form-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-color);">
                <i class="bi bi-map-fill text-primary me-2"></i> Entri Data Kelurahan Baru
            </h4>
            <p class="small mb-0">Tambahkan wilayah administratif baru untuk pemetaan EWS.</p>
        </div>
        <!-- Tombol kembali ke halaman tabel -->
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

    <!-- Form akan mengirim data ke proses_insert_kelurahan.php -->
    <form action="proses_insert_kelurahan.php" method="POST">
        
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label">Kode Wilayah (Kemendagri/BPS)</label>
                <input type="text" class="form-control" name="kode_wilayah" placeholder="Misal: 33.72.01.1001" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nama Kelurahan</label>
                <input type="text" class="form-control" name="nama_kelurahan" placeholder="Nama resmi kelurahan" required>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <label class="form-label">Kode Pos</label>
                <input type="text" class="form-control" name="kodepos" maxlength="10" placeholder="Misal: 57141" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Populasi Penduduk (Jiwa)</label>
                <input type="number" min="0" class="form-control" name="jumlah_penduduk" placeholder="Jumlah riil" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Luas Area (km&sup2;)</label>
                <input type="number" step="0.01" min="0" class="form-control" name="luas_wilayah_km2" placeholder="Misal: 0.55" required>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12">
                <label class="form-label">Koordinat Geografis (Geo Polygon)</label>
                <textarea class="form-control" name="geo_polygon" rows="4" placeholder='Contoh: {"type": "Polygon", "coordinates": [[[110.8123, -7.5678], [110.815, -7.5678], ...]]]}'></textarea>
                <small style="color: var(--text-color); opacity: 0.6; font-size: 0.8rem; margin-top: 5px; display: block;">
                    *Opsional: Masukkan format JSON yang valid untuk diintegrasikan dengan peta Leaflet.js nantinya.
                </small>
            </div>
        </div>

        <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

        <div class="d-flex justify-content-end gap-3">
            <button type="reset" class="btn btn-outline-secondary px-4 py-2">Batal / Reset</button>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                <i class="bi bi-cloud-arrow-up-fill me-2"></i> Simpan Data Kelurahan
            </button>
        </div>
    </form>
</div>

<?php include '../../layout/footer.php'; ?>