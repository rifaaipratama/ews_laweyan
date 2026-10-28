<?php
session_start();

// Proteksi Ganda: Harus login DAN harus Administrator (id_role = 1)
if (!isset($_SESSION['id_pengguna']) || $_SESSION['id_role'] != 1) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Validasi ID dari URL
$id_role_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data role yang akan diedit
$query_role = mysqli_query($koneksi, "SELECT * FROM tabel_role WHERE id_role = '$id_role_edit'");
if (mysqli_num_rows($query_role) == 0) {
    $_SESSION['alert_gagal'] = "Data role tidak ditemukan di sistem!";
    header("Location: index.php");
    exit;
}
$data_role = mysqli_fetch_assoc($query_role);

// Panggil header untuk CSS & Tema
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
        max-width: 650px;
        padding: 3rem;
        transition: background-color 0.3s, border-color 0.3s;
    }

    .form-container p { color: var(--text-color) !important; opacity: 0.75; }
    .form-label { font-weight: 600; color: var(--text-color); font-size: 0.9rem; margin-bottom: 0.5rem; }

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
    
    /* Styling khusus untuk input yang readonly (dikunci) */
    .form-control[readonly] {
        background-color: var(--border-color);
        opacity: 0.7;
        cursor: not-allowed;
    }
</style>

<div class="form-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-color);">
                <i class="bi bi-pencil-square text-primary me-2"></i> Edit Data Role
            </h4>
            <p class="text-muted small mb-0">Perbarui informasi hak akses sistem.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

    <form action="proses_edit_role.php" method="POST">
        
        <!-- HIDDEN INPUT untuk melempar ID ke file proses -->
        <input type="hidden" name="id_role" value="<?php echo $data_role['id_role']; ?>">

        <div class="mb-4">
            <label class="form-label">Nama Role <span class="text-danger">*</span></label>
            <?php if ($data_role['id_role'] == 1): ?>
                <!-- Jika Admin, kunci namanya -->
                <input type="text" class="form-control" name="nama_role" value="<?php echo htmlspecialchars($data_role['nama_role']); ?>" readonly>
                <small class="text-danger mt-1 d-block" style="font-size: 0.75rem;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Nama role Administrator tidak dapat diubah demi keamanan sistem.
                </small>
            <?php else: ?>
                <!-- Jika bukan Admin, bebas diedit -->
                <input type="text" class="form-control" name="nama_role" value="<?php echo htmlspecialchars($data_role['nama_role']); ?>" required autocomplete="off">
            <?php endif; ?>
        </div>

        <div class="mb-5">
            <label class="form-label">Deskripsi Wewenang (Opsional)</label>
            <textarea class="form-control" name="deskripsi" rows="4" placeholder="Jelaskan secara singkat apa saja yang bisa dilakukan oleh role ini..."><?php echo htmlspecialchars($data_role['deskripsi'] ?? ''); ?></textarea>
        </div>

        <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

        <div class="d-flex justify-content-end gap-3">
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                <i class="bi bi-save me-2"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php include '../../layout/footer.php'; ?>