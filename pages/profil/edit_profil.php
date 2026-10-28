<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Jika Admin klik tombol edit dari detail pengguna, pakai ID target dari URL. Jika tidak, pakai sesi sendiri.
if ($_SESSION['id_role'] == 1 && isset($_GET['id'])) {
    $id_pengguna = intval($_GET['id']);
} else {
    $id_pengguna = $_SESSION['id_pengguna'];
}

// --- LOGIKA CEK ROLE ADMIN ---
// Asumsi ID Role 1 adalah Administrator sesuai rancangan database tabel_role
$is_admin = ($_SESSION['id_role'] == 1) ? true : false;

$query = mysqli_query($koneksi, "SELECT * FROM tabel_pengguna WHERE id_pengguna = '$id_pengguna'");
$user = mysqli_fetch_assoc($query);

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
        max-width: 850px;
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

    /* KELAS KHUSUS UNTUK INPUT YANG DIKUNCI */
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
    
    /* Untuk Select/Dropdown agar tidak bisa diklik sama sekali tapi tetap terkirim */
    .select-locked {
        pointer-events: none;
    }
</style>

<div class="form-container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dynamic">
                <i class="bi bi-pencil-square text-primary me-2"></i> Edit Profil Saya
            </h4>
            <p class="text-muted small mb-0">Lengkapi informasi pribadi, kontak, dan data kepegawaian Anda.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-medium shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <?php if (!$is_admin): ?>
    <div class="alert alert-warning border-0 shadow-sm mb-4" style="background-color: rgba(255, 193, 7, 0.1); color: #856404; border-radius: 12px;">
        <i class="bi bi-shield-lock-fill me-2"></i> <b>Akses Terbatas:</b> Beberapa data identitas dan kepegawaian telah dikunci. Hubungi Administrator Utama jika ada perubahan data.
    </div>
    <?php endif; ?>

    <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

    <form action="proses_edit_profil.php" method="POST">

    <input type="hidden" name="id_target" value="<?php echo $id_pengguna; ?>">
        
        <h6 class="fw-bold mb-3 text-dynamic" style="border-bottom: 2px dashed var(--border-color); padding-bottom: 10px;">Informasi Dasar</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">
                    <?php if(!$is_admin) echo '<i class="bi bi-lock-fill text-muted me-1"></i>'; ?> Nama Lengkap
                </label>
                <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required 
                    <?php echo !$is_admin ? 'class="form-control input-locked" readonly tabindex="-1"' : 'class="form-control"'; ?>>
            </div>
            <div class="col-md-6">
                <label class="form-label">
                    <?php if(!$is_admin) echo '<i class="bi bi-lock-fill text-muted me-1"></i>'; ?> Username (Login)
                </label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required 
                    <?php echo !$is_admin ? 'class="form-control input-locked" readonly tabindex="-1"' : 'class="form-control"'; ?>>
            </div>
        </div>

        <h6 class="fw-bold mb-3 text-dynamic" style="border-bottom: 2px dashed var(--border-color); padding-bottom: 10px;">Data Kepegawaian & Demografi</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">
                    <?php if(!$is_admin) echo '<i class="bi bi-lock-fill text-muted me-1"></i>'; ?> NIP (Nomor Induk Pegawai)
                </label>
                <input type="text" name="nip" value="<?php echo htmlspecialchars($user['nip'] ?? ''); ?>" placeholder="Masukkan NIP"
                    <?php echo !$is_admin ? 'class="form-control input-locked" readonly tabindex="-1"' : 'class="form-control"'; ?>>
            </div>
            <div class="col-md-6">
                <label class="form-label">
                    <?php if(!$is_admin) echo '<i class="bi bi-lock-fill text-muted me-1"></i>'; ?> Jabatan
                </label>
                <input type="text" name="jabatan" value="<?php echo htmlspecialchars($user['jabatan'] ?? ''); ?>" placeholder="Contoh: Staff Surveilans"
                    <?php echo !$is_admin ? 'class="form-control input-locked" readonly tabindex="-1"' : 'class="form-control"'; ?>>
            </div>
            <div class="col-md-6">
                <label class="form-label">
                    <?php if(!$is_admin) echo '<i class="bi bi-lock-fill text-muted me-1"></i>'; ?> Status Kepegawaian
                </label>
                <select name="status_kepegawaian" <?php echo !$is_admin ? 'class="form-select input-locked select-locked" tabindex="-1"' : 'class="form-select"'; ?>>
                    <option value="">-- Pilih Status --</option>
                    <option value="tetap" <?php echo (isset($user['status_kepegawaian']) && $user['status_kepegawaian'] == 'tetap') ? 'selected' : ''; ?>>Pegawai Tetap</option>
                    <option value="kontrak" <?php echo (isset($user['status_kepegawaian']) && $user['status_kepegawaian'] == 'kontrak') ? 'selected' : ''; ?>>Pegawai Kontrak</option>
                    <option value="magang" <?php echo (isset($user['status_kepegawaian']) && $user['status_kepegawaian'] == 'magang') ? 'selected' : ''; ?>>Magang</option>
                </select>
            </div>
            <div class="col-md-6">
                <!-- Jenis Kelamin tetap bisa diedit oleh semua user -->
                <label class="form-label">Jenis Kelamin</label>
                <select class="form-select" name="jenis_kelamin">
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="L" <?php echo (isset($user['jenis_kelamin']) && $user['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-Laki</option>
                    <option value="P" <?php echo (isset($user['jenis_kelamin']) && $user['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>
        </div>

        <h6 class="fw-bold mb-3 text-dynamic" style="border-bottom: 2px dashed var(--border-color); padding-bottom: 10px;">Informasi Kontak & Keamanan</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Alamat Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="nama@email.com">
            </div>
            <div class="col-md-6">
                <label class="form-label">Nomor WhatsApp / HP</label>
                <input type="text" class="form-control" name="no_telepon" value="<?php echo htmlspecialchars($user['no_telepon'] ?? ''); ?>" placeholder="08xxxxxx">
            </div>
            <div class="col-12">
                <label class="form-label">Alamat Domisili Lengkap</label>
                <textarea class="form-control" name="alamat" rows="2" placeholder="Masukkan alamat lengkap..."><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
            </div>
        </div>

        <!-- Kolom Password Baru (Dikunci jika bukan admin) -->
        <div class="mb-4 p-3 rounded" style="background-color: rgba(59, 130, 246, 0.05); border: 1px dashed #3b82f6;">
            <label class="form-label" style="color: #0d6efd;">
                <?php if(!$is_admin) echo '<i class="bi bi-lock-fill me-1"></i>'; ?> Password Baru (Opsional)
            </label>
            <input type="password" name="password_baru" 
                <?php echo !$is_admin ? 'class="form-control input-locked" readonly tabindex="-1" placeholder="Hubungi Administrator untuk mengubah password"' : 'class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password"'; ?>>
            <?php if ($is_admin): ?>
                <small class="text-muted mt-2 d-block"><i class="bi bi-info-circle me-1"></i> Hanya isi jika Anda ingin mengganti kata sandi login Anda.</small>
            <?php endif; ?>
        </div>

        <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

        <div class="d-flex justify-content-end gap-3">
            <a href="index.php" class="btn btn-outline-secondary px-4 py-2 fw-medium">Batal</a>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-save me-2"></i> Simpan Pembaruan
            </button>
        </div>
    </form>
</div>

<?php include '../../layout/footer.php'; ?>