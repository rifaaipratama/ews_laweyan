<?php
session_start();

// Proteksi: Hanya bisa diakses yang sudah login dan Role Admin
if (!isset($_SESSION['id_pengguna']) || $_SESSION['id_role'] != 1) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Tangkap NIK dari URL
if (!isset($_GET['nik']) || empty($_GET['nik'])) {
    header("Location: index.php");
    exit;
}

$nik_pasien = mysqli_real_escape_string($koneksi, $_GET['nik']);

// Fitur Baru: Toggle Show All (Soft Deleted Data)
$show_all = isset($_GET['show_all']) && $_GET['show_all'] == '1';
$filter_delete = $show_all ? "" : "AND k.is_deleted = 0";

// 1. Ambil Data Demografi Pasien
$q_pasien = mysqli_query($koneksi, "
    SELECT nik_pasien, nama_pasien, jenis_kelamin, tanggal_lahir, no_telepon, alamat_lengkap 
    FROM tabel_kasus 
    WHERE nik_pasien = '$nik_pasien' 
    LIMIT 1
");

if (mysqli_num_rows($q_pasien) == 0) {
    echo "<script>alert('Data pasien tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}
$pasien = mysqli_fetch_assoc($q_pasien);

// Penanganan Data Kosong
$usia = '<span class="text-muted fst-italic">Belum diinput</span>';
if (!empty($pasien['tanggal_lahir'])) {
    $tgl_lahir = new DateTime($pasien['tanggal_lahir']);
    $hari_ini = new DateTime('today');
    $usia = $hari_ini->diff($tgl_lahir)->y . " Tahun";
}

$no_telepon = !empty($pasien['no_telepon']) ? htmlspecialchars($pasien['no_telepon']) : '<span class="text-muted fst-italic">Belum diinput</span>';
$alamat     = !empty($pasien['alamat_lengkap']) ? htmlspecialchars($pasien['alamat_lengkap']) : '<span class="text-muted fst-italic">Belum diinput</span>';

// Data untuk Header/Sidebar
$id_role      = $_SESSION['id_role'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$query_role = mysqli_query($koneksi, "SELECT nama_role FROM tabel_role WHERE id_role = '$id_role'");
$nama_role  = ($row = mysqli_fetch_assoc($query_role)) ? $row['nama_role'] : "User";

// Logika Keamanan Data: Masking NIK
$nik_asli = htmlspecialchars($pasien['nik_pasien']);
if (strlen($nik_asli) == 16) {
    $nik_masking = substr($nik_asli, 0, 6) . 'xxxxxx' . substr($nik_asli, 12, 4);
} else {
    $nik_masking = $nik_asli; // Fallback jika format salah
}
$is_admin = ($id_role == 1); // Cek apakah pengguna adalah Administrator

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>

<style>
    .panel-card {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 4px 25px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .table-custom {
        --bs-table-color: var(--text-color);
        --bs-table-bg: transparent;
        --bs-table-border-color: var(--border-color);
        --bs-table-hover-color: var(--text-color);
    }
    .table-custom thead th {
        background-color: var(--bg-body);
        color: var(--text-color);
        border-bottom: 2px solid var(--border-color);
        font-weight: 600;
        white-space: nowrap;
    }
    .table-custom tbody td {
        background-color: var(--bg-panel);
        color: var(--text-color) !important;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }
    [data-theme="light"] .table-custom.table-hover tbody tr:hover td { background-color: rgba(0, 0, 0, 0.02); }
    [data-theme="dark"] .table-custom.table-hover tbody tr:hover td { background-color: rgba(255, 255, 255, 0.03); }

    /* Profil Card Modern (Medical ID Style) */
    .profile-header {
        position: relative;
        padding: 2rem;
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 202, 240, 0.05) 100%);
        border-bottom: 1px dashed var(--border-color);
    }
    .profile-icon {
        width: 85px;
        height: 85px;
        background: linear-gradient(135deg, #0dcaf0, #0d6efd);
        border: 4px solid var(--bg-panel);
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
    }
    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-color);
        opacity: 0.6;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }
    .info-value {
        font-size: 0.95rem;
        font-weight: 500;
        color: var(--text-color);
    }

    /* Style untuk data arsip */
    .row-archived {
        background-color: rgba(0,0,0,0.02) !important;
        opacity: 0.75;
    }
    [data-theme="dark"] .row-archived { background-color: rgba(255,255,255,0.02) !important; }

    /* Animasi Toggle NIK */
    #btnToggleNik {
        transition: transform 0.2s, color 0.2s;
        cursor: pointer;
    }
    #btnToggleNik:hover {
        transform: scale(1.15);
        color: #0a58ca !important;
    }
</style>

<div id="main-content">

    <?php include '../../layout/topbar.php'; ?>

    <!-- Header & Tombol Kembali -->
    <div class="row mb-4 align-items-center mt-2">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="text-dynamic fw-bold mb-1">
                    <i class="bi bi-folder2-open me-2 text-primary"></i> Detail Rekam Medis Pasien
                </h4>
                <p class="mb-0" style="color: var(--text-color); opacity: 0.75; font-size: 0.9rem;">
                    Rincian identitas demografi dan riwayat historis kasus yang tercatat di sistem.
                </p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill shadow-sm px-4 btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    <?php if (isset($_SESSION['alert_sukses'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert" style="border-radius: 10px;">
            <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['alert_sukses']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['alert_sukses']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['alert_gagal'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert" style="border-radius: 10px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $_SESSION['alert_gagal']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['alert_gagal']); ?>
    <?php endif; ?>

    <div class="row g-4">

        <!-- CARD IDENTITAS PASIEN -->
        <div class="col-12">
            <div class="panel-card">
                <div class="profile-header d-flex flex-column flex-md-row align-items-center gap-4">
                    <div class="profile-icon">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="text-center text-md-start">
                        <h3 class="fw-bold mb-1 text-dynamic"><?= htmlspecialchars($pasien['nama_pasien']); ?></h3>
                        
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mt-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-1 rounded-pill">
                                <i class="bi bi-check-circle-fill me-1"></i> Terdaftar di SIMRS EWS
                            </span>
                            
                            <!-- Tombol Edit Modal -->
                            <?php if ($is_admin): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditPasien">
                                    <i class="bi bi-pencil-square me-1"></i> Edit Data
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <div class="p-4">
                    <div class="row g-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="info-label"><i class="bi bi-credit-card me-1"></i> NIK / No. Identitas</div>
                            <div class="info-value d-flex align-items-center" style="font-family: monospace; color: #0d6efd; font-size: 1.1rem;">
                                <span id="nikText" data-asli="<?= $nik_asli; ?>" data-mask="<?= $nik_masking; ?>">
                                    <?= $nik_masking; ?>
                                </span>

                                <?php if ($is_admin): ?>
                                    <button type="button" id="btnToggleNik" class="btn btn-sm text-primary ms-2 p-0" style="background: none; border: none; box-shadow: none;" title="Tampilkan/Sembunyikan NIK">
                                        <i id="iconToggleNik" class="bi bi-eye-slash-fill fs-5"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-label"><i class="bi bi-gender-ambiguous me-1"></i> Jenis Kelamin</div>
                            <div class="info-value">
                                <?= ($pasien['jenis_kelamin'] == 'L') ? '<i class="bi bi-gender-male text-primary"></i> Laki-laki' : '<i class="bi bi-gender-female text-danger"></i> Perempuan'; ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-label"><i class="bi bi-calendar-event me-1"></i> Usia Saat Ini</div>
                            <div class="info-value"><?= $usia; ?></div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-label"><i class="bi bi-telephone me-1"></i> Kontak / No. Telepon</div>
                            <div class="info-value"><?= $no_telepon; ?></div>
                        </div>
                        <div class="col-12">
                            <div class="info-label"><i class="bi bi-house-door me-1"></i> Alamat Domisili Lengkap</div>
                            <div class="info-value"><?= $alamat; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL RIWAYAT PENYAKIT -->
        <div class="col-12">
            <div class="panel-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0 text-dynamic"><i class="bi bi-clock-history me-2 text-primary"></i> Historis Kasus & Kunjungan</h5>

                    <!-- TOGGLE BUTTON UNTUK ARSIP -->
                    <div>
                        <?php if($show_all): ?>
                            <a href="?nik=<?= urlencode($nik_pasien) ?>" class="btn btn-sm btn-primary shadow-sm rounded-pill px-3">
                                <i class="bi bi-eye-slash me-1"></i> Sembunyikan Arsip
                            </a>
                        <?php else: ?>
                            <a href="?nik=<?= urlencode($nik_pasien) ?>&show_all=1" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3">
                                <i class="bi bi-archive me-1"></i> Tampilkan Semua Riwayat
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 25%;">Timeline Kasus</th>
                                <th style="width: 25%;">Diagnosis & Lokasi</th>
                                <th style="width: 25%;">Petugas Pencatat</th>
                                <th style="width: 20%;">Status Kasus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query dengan Logika Filter Arsip Dinamis
                            $q_riwayat = mysqli_query($koneksi, "
                                SELECT k.*, p.nama_populer, kel.nama_kelurahan, u.nama_lengkap AS nama_petugas
                                FROM tabel_kasus k
                                JOIN tabel_penyakit p ON k.id_penyakit = p.id_penyakit
                                JOIN tabel_kelurahan kel ON k.id_kelurahan = kel.id_kelurahan
                                JOIN tabel_pengguna u ON k.id_pengguna = u.id_pengguna
                                WHERE k.nik_pasien = '$nik_pasien' $filter_delete
                                ORDER BY k.tanggal_lapor DESC
                            ");

                            $no = 1;
                            if(mysqli_num_rows($q_riwayat) > 0) {
                                while ($riwayat = mysqli_fetch_assoc($q_riwayat)) {

                                    // Deteksi jika ini adalah data soft delete
                                    $is_archived = ($riwayat['is_deleted'] == 1);
                                    $row_class = $is_archived ? "row-archived" : "";

                                    // Pewarnaan Badge Status
                                    $badge_class = 'bg-secondary';
                                    if ($riwayat['status_kasus'] == 'terkonfirmasi') $badge_class = 'bg-danger';
                                    if ($riwayat['status_kasus'] == 'suspek') $badge_class = 'bg-warning text-dark';
                                    if ($riwayat['status_kasus'] == 'sembuh') $badge_class = 'bg-success';
                                    if ($riwayat['status_kasus'] == 'meninggal') $badge_class = 'bg-dark';

                                    // Format Tanggal Terstruktur
                                    $tgl_lapor = date('d M Y', strtotime($riwayat['tanggal_lapor']));
                                    $tgl_onset = !empty($riwayat['tanggal_onset']) ? date('d M Y', strtotime($riwayat['tanggal_onset'])) : "Belum direkam";
                                    $tgl_selesai_ui = !empty($riwayat['tanggal_selesai']) ? "<div class='small text-success mt-1'><i class='bi bi-check2-circle me-1'></i> Selesai: " . date('d M Y', strtotime($riwayat['tanggal_selesai'])) . "</div>" : "";

                                    echo "<tr class='{$row_class}'>";
                                    echo "<td class='text-center'>" . $no++ . "</td>";

                                    echo "<td>
                                            <div class='fw-semibold text-dynamic mb-1'><i class='bi bi-calendar-check me-1 text-primary'></i> $tgl_lapor</div>
                                            <div class='small text-muted'><i class='bi bi-thermometer-half me-1'></i> Onset: $tgl_onset</div>
                                            $tgl_selesai_ui
                                          </td>";

                                    echo "<td>
                                            <div class='fw-bold text-info mb-1'>{$riwayat['nama_populer']}</div>
                                            <div class='small text-muted'><i class='bi bi-geo-alt me-1'></i> Kel. {$riwayat['nama_kelurahan']}</div>
                                          </td>";

                                    echo "<td>
                                            <div class='d-flex align-items-center gap-2'>
                                                <div class='bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center' style='width: 30px; height: 30px;'>
                                                    <i class='bi bi-person-badge text-secondary small'></i>
                                                </div>
                                                <span class='small fw-medium text-dynamic'>" . htmlspecialchars($riwayat['nama_petugas']) . "</span>
                                            </div>
                                          </td>";

                                    // Menampilkan Status + Tanda Arsip jika data soft delete
                                    echo "<td>
                                            <span class='badge {$badge_class} px-3 py-2'>" . strtoupper($riwayat['status_kasus']) . "</span>";
                                            if($is_archived) {
                                                echo "<br><span class='badge bg-secondary mt-2' style='font-size:0.65rem;'><i class='bi bi-archive-fill'></i> Diarsipkan</span>";
                                            }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center text-muted py-5'>
                                        <i class='bi bi-inbox display-6 d-block mb-3 opacity-50'></i>
                                        Tidak ada riwayat kasus yang sesuai kriteria pencarian.
                                      </td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Data Pasien -->
<div class="modal fade" id="modalEditPasien" tabindex="-1" aria-labelledby="modalEditPasienLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--bg-panel); border-color: var(--border-color); border-radius: 12px;">
            <div class="modal-header border-bottom" style="border-color: var(--border-color) !important;">
                <h5 class="modal-title fw-bold text-dynamic" id="modalEditPasienLabel">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Edit Data Demografi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="proses_edit_pasien.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="nik_pasien" value="<?= htmlspecialchars($nik_pasien); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label text-dynamic fw-semibold" style="font-size: 0.9rem;">Nomor Telepon / Kontak</label>
                        <input type="text" class="form-control" name="no_telepon" value="<?= htmlspecialchars($pasien['no_telepon'] ?? ''); ?>" placeholder="Contoh: 081234567890" style="background-color: var(--bg-body); color: var(--text-color); border-color: var(--border-color);">
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label text-dynamic fw-semibold" style="font-size: 0.9rem;">Alamat Domisili Lengkap</label>
                        <textarea class="form-control" name="alamat_lengkap" rows="3" placeholder="Nama Jalan, RT/RW, Kelurahan..." style="background-color: var(--bg-body); color: var(--text-color); border-color: var(--border-color);"><?= htmlspecialchars($pasien['alamat_lengkap'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-3" style="border-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill fw-medium" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-medium">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($is_admin): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const btnToggle = document.getElementById("btnToggleNik");
        if (btnToggle) {
            const iconToggle = document.getElementById("iconToggleNik");
            const nikText = document.getElementById("nikText");

            let isMasked = true;

            btnToggle.addEventListener("click", function() {
                if (isMasked) {
                    // Tampilkan NIK Asli
                    nikText.textContent = nikText.getAttribute("data-asli");
                    iconToggle.classList.remove("bi-eye-slash-fill");
                    iconToggle.classList.add("bi-eye-fill");
                } else {
                    // Samarkan NIK
                    nikText.textContent = nikText.getAttribute("data-mask");
                    iconToggle.classList.remove("bi-eye-fill");
                    iconToggle.classList.add("bi-eye-slash-fill");
                }
                isMasked = !isMasked;
            });
        }
    });
</script>
<?php endif; ?>

<?php include '../../layout/footer.php'; ?>