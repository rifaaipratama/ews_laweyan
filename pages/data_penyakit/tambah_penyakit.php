<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Tarik semua data dari tabel_master_icd10 untuk keperluan Auto-fill
$query_master = mysqli_query($koneksi, "SELECT * FROM tabel_master_icd10 ORDER BY kode_icd ASC");
$data_icd = [];
while ($row = mysqli_fetch_assoc($query_master)) {
    $data_icd[$row['kode_icd']] = $row;
}
// Konversi array PHP ke format JSON agar bisa dibaca oleh JavaScript
$json_icd = json_encode($data_icd);

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
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>

<div class="form-container">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-virus text-primary me-2"></i> Entri Penyakit Baru</h4>
            <p class="text-muted small mb-0">Pilih dari master data yang ada, atau tambahkan kode ICD-10 baru.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <?php if (isset($_SESSION['alert_gagal'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $_SESSION['alert_gagal']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['alert_gagal']); ?>
    <?php endif; ?>

    <form action="proses_tambah_penyakit.php" method="POST">
        <div class="row g-4">
            <!-- Kolom Kiri: Data Master ICD-10 -->
            <div class="col-md-6 border-end pe-md-4">
                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-journal-medical me-2"></i>Data Standar ICD-10</h6>
                
                <div class="mb-3">
                    <label class="form-label text-danger">Kode ICD-10 *</label>
                    <!-- Menggunakan list="daftar_icd" untuk menghubungkan input dengan datalist -->
                    <input type="text" class="form-control text-uppercase fw-bold text-primary" name="kode_icd" id="input_kode_icd" list="daftar_icd" placeholder="Ketik/Pilih Kode (Contoh: A91)" required maxlength="10" autocomplete="off">
                    
                    <datalist id="daftar_icd">
                        <?php foreach($data_icd as $kode => $detail): ?>
                            <option value="<?php echo $kode; ?>"><?php echo htmlspecialchars($detail['deskripsi_id']); ?></option>
                        <?php endforeach; ?>
                    </datalist>
                    <div class="form-text small opacity-75">Sistem akan otomatis mengisi kolom di bawah jika kode sudah ada di Master Data.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-danger">Deskripsi Bahasa Indonesia *</label>
                    <textarea class="form-control" name="deskripsi_id" id="input_deskripsi_id" rows="2" placeholder="Demam berdarah dengue..." required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label text-danger">Deskripsi English *</label>
                    <textarea class="form-control" name="deskripsi_en" id="input_deskripsi_en" rows="2" placeholder="Dengue haemorrhagic fever..." required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori (Bab ICD-10)</label>
                    <input type="text" class="form-control" name="kategori" id="input_kategori" placeholder="Contoh: Penyakit infeksi dan parasit tertentu">
                </div>
            </div>

            <!-- Kolom Kanan: Pengaturan EWS Dashboard -->
            <div class="col-md-6 ps-md-4">
                <h6 class="fw-bold text-success mb-3"><i class="bi bi-speedometer2 me-2"></i>Pengaturan Radar EWS</h6>
                
                <div class="mb-3">
                    <label class="form-label text-danger">Nama Populer (Dashboard) *</label>
                    <input type="text" class="form-control" name="nama_populer" placeholder="Contoh: DBD, TBC, Diare..." required>
                    <div class="form-text small opacity-75">Nama yang akan tampil di grafik dan tabel kasus.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status Pantauan</label>
                    <select class="form-select" name="status_pantau">
                        <option value="aktif">Aktif (Dipantau EWS)</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
            <button type="reset" class="btn btn-light me-2 px-4 border">Reset</button>
            <button type="submit" class="btn btn-primary px-4 fw-semibold"><i class="bi bi-save me-2"></i>Simpan Data Terpadu</button>
        </div>
    </form>
</div>

<!-- Script Auto-fill Master Data ICD-10 -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Parsing data JSON dari PHP ke Variabel JavaScript
        const masterIcdData = <?php echo $json_icd; ?>;
        
        // Ambil elemen input
        const inputKodeICD = document.getElementById('input_kode_icd');
        const inputDescId = document.getElementById('input_deskripsi_id');
        const inputDescEn = document.getElementById('input_deskripsi_en');
        const inputKategori = document.getElementById('input_kategori');

        // Dengarkan event ketika pengguna mengetik atau memilih kode
        inputKodeICD.addEventListener('input', function() {
            const kodeDiketik = this.value.trim().toUpperCase();
            
            // Cek apakah kode yang diketik/dipilih ada di dalam array Master Data
            if (masterIcdData.hasOwnProperty(kodeDiketik)) {
                // Jika ADA: Auto-fill kolom lainnya
                inputDescId.value = masterIcdData[kodeDiketik].deskripsi_id;
                inputDescEn.value = masterIcdData[kodeDiketik].deskripsi_en;
                inputKategori.value = masterIcdData[kodeDiketik].kategori;
            } else {
                // Jika TIDAK ADA (Bisa jadi mengetik kode baru): Biarkan form apa adanya atau kosongkan
                // Catatan: kita tidak mengosongkan otomatis agar ketikan user tidak hilang saat merevisi ketikan
            }
        });
    });
</script>

<?php include '../../layout/footer.php'; ?>