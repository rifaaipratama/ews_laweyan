<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Ambil ID Pengguna dari URL
$id_edit = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query 1: Ambil data pengguna yang akan diedit
$query_user = mysqli_query($koneksi, "SELECT * FROM tabel_pengguna WHERE id_pengguna = '$id_edit'");
$user = mysqli_fetch_assoc($query_user);

if (!$user) {
    $_SESSION['alert_gagal'] = "Data pengguna tidak ditemukan.";
    header("Location: index.php");
    exit;
}

// Query 2: Ambil seluruh data master Role untuk Dropdown Pilihan
$query_role = mysqli_query($koneksi, "SELECT * FROM tabel_role ORDER BY id_role ASC");

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
        max-width: 800px;
        padding: 3rem;
        transition: background-color 0.3s, border-color 0.3s;
    }

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
    
    .form-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .select-locked {
        background-color: rgba(0,0,0,0.03) !important;
        pointer-events: none;
        color: var(--text-muted);
    }
</style>

<div class="form-container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dynamic">
                <i class="bi bi-person-gear text-warning me-2"></i> Edit Hak Akses Pengguna
            </h4>
            <p class="text-muted small mb-0">Kelola kredensial login, status aktif, dan role akun ini.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-medium shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

    <form action="proses_edit_pengguna.php" method="POST">
        <!-- Hidden ID -->
        <input type="hidden" name="id_pengguna" value="<?php echo $user['id_pengguna']; ?>">

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control fw-semibold" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Username (Login)</label>
                <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label text-primary"><i class="bi bi-shield-lock-fill me-1"></i> Role (Hak Akses)</label>
                <!-- Proteksi: Jika ID 2 (Super Admin Pertama), rolenya tidak boleh diubah untuk mencegah admin terhapus -->
                <select name="id_role" class="form-select fw-bold <?php echo ($user['id_pengguna'] == 2) ? 'select-locked' : ''; ?>" required>
                    <option value="">-- Pilih Role --</option>
                    <?php while($role = mysqli_fetch_assoc($query_role)): ?>
                        <option value="<?php echo $role['id_role']; ?>" <?php echo ($user['id_role'] == $role['id_role']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['nama_role']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php if($user['id_pengguna'] == 2): ?>
                    <small class="text-danger mt-1 d-block" style="font-size: 0.75rem;">*Role Super Admin Utama tidak dapat diubah.</small>
                <?php endif; ?>
            </div>
            
            <div class="col-md-6">
                <label class="form-label"><i class="bi bi-toggle-on me-1"></i> Status Akun</label>
                <select name="status_aktif" class="form-select <?php echo ($user['id_pengguna'] == 2) ? 'select-locked' : ''; ?>" required>
                    <option value="aktif" <?php echo ($user['status_aktif'] == 'aktif') ? 'selected' : ''; ?>>🟢 Aktif (Bisa Login)</option>
                    <option value="nonaktif" <?php echo ($user['status_aktif'] == 'nonaktif') ? 'selected' : ''; ?>>🔴 Nonaktif (Blokir Login)</option>
                </select>
            </div>
        </div>

        <div class="mb-5 p-3 rounded" style="background-color: rgba(220, 53, 69, 0.05); border: 1px dashed #dc3545;">
            <label class="form-label text-danger">Reset Password (Opsional)</label>
            <input type="password" class="form-control" name="password_baru" placeholder="Kosongkan jika password tidak ingin direset">
            <small class="text-muted mt-2 d-block">Isi kolom ini hanya jika staf lupa password dan meminta Anda meresetkannya.</small>
        </div>

        <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

        <div class="d-flex justify-content-end gap-3">
            <a href="index.php" class="btn btn-outline-secondary px-4 py-2 fw-medium">Batal</a>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-floppy-fill me-2"></i> Simpan Pembaruan Data
            </button>
        </div>
    </form>
</div>

<?php include '../../layout/footer.php'; ?>