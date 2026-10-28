<?php
session_start();

// Proteksi ganda: Harus login & harus Administrator (Role 1)
if (!isset($_SESSION['id_pengguna']) || $_SESSION['id_role'] != 1) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

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
</style>

<div class="form-container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dynamic">
                <i class="bi bi-person-plus-fill text-primary me-2"></i> Registrasi Pengguna Baru
            </h4>
            <p class="text-muted small mb-0">Buat akun kredensial dan lengkapi data profil staf Command Center.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-medium shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

    <form action="proses_insert_pengguna.php" method="POST">
        
        <h6 class="fw-bold mb-3 text-dynamic" style="border-bottom: 2px dashed var(--border-color); padding-bottom: 10px;">Informasi Kredensial (Wajib)</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label text-primary">Nama Lengkap</label>
                <input type="text" class="form-control fw-bold" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="col-md-6">
                <label class="form-label text-primary">Username (Login)</label>
                <input type="text" class="form-control fw-bold" name="username" placeholder="Contoh: admin_laweyan" required>
            </div>
            <div class="col-md-6">
                <label class="form-label text-primary">Password Awal</label>
                <input type="password" class="form-control" name="password" placeholder="Buat password yang kuat" required>
            </div>
            <div class="col-md-3">
                <label class="form-label text-primary">Hak Akses</label>
                <select class="form-select fw-bold text-primary" name="id_role" required>
                    <option value="" selected disabled>-- Pilih Role --</option>
                    <?php
                    $q_role = mysqli_query($koneksi, "SELECT id_role, nama_role FROM tabel_role ORDER BY id_role ASC");
                    while($r = mysqli_fetch_assoc($q_role)) {
                        echo "<option value='{$r['id_role']}'>".strtoupper($r['nama_role'])."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-primary">Status</label>
                <select class="form-select text-success fw-bold" name="status_aktif" required>
                    <option value="aktif" selected>Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
        </div>

        <h6 class="fw-bold mb-3 text-dynamic" style="border-bottom: 2px dashed var(--border-color); padding-bottom: 10px;">Data Kepegawaian & Kontak (Opsional)</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">NIP (Nomor Induk Pegawai)</label>
                <input type="text" class="form-control" name="nip" placeholder="Masukkan NIP (jika ada)">
            </div>
            <div class="col-md-6">
                <label class="form-label">Jabatan</label>
                <input type="text" class="form-control" name="jabatan" placeholder="Contoh: Staf Lapangan">
            </div>
            <div class="col-md-6">
                <label class="form-label">Status Kepegawaian</label>
                <select class="form-select" name="status_kepegawaian">
                    <option value="" selected>-- Pilih Status --</option>
                    <option value="tetap">Pegawai Tetap</option>
                    <option value="kontrak">Pegawai Kontrak</option>
                    <option value="magang">Magang</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Jenis Kelamin</label>
                <select class="form-select" name="jenis_kelamin">
                    <option value="" selected>-- Pilih Gender --</option>
                    <option value="L">Laki-Laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Alamat Email</label>
                <input type="email" class="form-control" name="email" placeholder="nama@email.com">
            </div>
            <div class="col-md-6">
                <label class="form-label">No. Telepon / WhatsApp</label>
                <input type="text" class="form-control" name="no_telepon" placeholder="08xxxxxx">
            </div>
            <div class="col-12">
                <label class="form-label">Alamat Domisili</label>
                <textarea class="form-control" name="alamat" rows="2" placeholder="Masukkan alamat lengkap..."></textarea>
            </div>
        </div>

        <hr style="border-color: var(--border-color); margin-bottom: 2rem;">

        <div class="d-flex justify-content-end gap-3">
            <button type="reset" class="btn btn-outline-secondary px-4 py-2 fw-medium">Batal / Reset</button>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-person-check-fill me-2"></i> Simpan Pengguna Baru
            </button>
        </div>
    </form>
</div>

<?php include '../../layout/footer.php'; ?>