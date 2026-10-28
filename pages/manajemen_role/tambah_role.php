<?php
session_start();

// Proteksi Ganda: Harus login DAN harus Administrator (id_role = 1)
if (!isset($_SESSION['id_pengguna']) || $_SESSION['id_role'] != 1) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/koneksi.php';

// HANYA panggil header untuk memuat CSS & Tema. Sidebar dan Topbar DILOMPATI.
include '../../layout/header.php';
?>

<style>
    /* Mengubah body menjadi canvas kosong yang terpusat di tengah */
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
        max-width: 650px; /* Dipersempit karena inputnya sedikit */
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

    .form-control {
        background-color: var(--bg-body);
        color: var(--text-color);
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        border-radius: 8px;
    }

    .form-control:focus {
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
                <i class="bi bi-shield-plus text-primary me-2"></i> Entri Role Baru
            </h4>
            <p class="text-muted small mb-0">Tambahkan hak akses (role) baru untuk pengguna.</p>
        </div>
        <!-- Tombol kembali ke halaman tabel -->
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

    <form action="proses_tambah_role.php" method="POST">
        
        <div class="mb-4">
            <label class="form-label">Nama Role <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nama_role" placeholder="Contoh: Petugas Lapangan, Kepala Dinkes..." required autocomplete="off">
        </div>

        <div class="mb-5">
            <label class="form-label">Deskripsi Wewenang (Opsional)</label>
            <textarea class="form-control" name="deskripsi" rows="4" placeholder="Jelaskan secara singkat apa saja yang bisa dilakukan oleh role ini di dalam sistem..."></textarea>
            <small class="text-muted mt-2 d-block" style="font-size: 0.75rem;">
                <i class="bi bi-info-circle me-1"></i> Opsional, namun sangat disarankan untuk diisi agar memudahkan manajemen.
            </small>
        </div>

        <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

        <div class="d-flex justify-content-end gap-3">
            <button type="reset" class="btn btn-outline-secondary px-4 py-2">Batal / Reset</button>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                <i class="bi bi-save me-2"></i> Simpan Role
            </button>
        </div>
    </form>
</div>

<?php include '../../layout/footer.php'; ?>