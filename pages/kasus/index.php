<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Ambil data untuk Header/Sidebar
$id_role      = $_SESSION['id_role'];
$nama_lengkap = $_SESSION['nama_lengkap'];

$query_role = mysqli_query($koneksi, "SELECT nama_role FROM tabel_role WHERE id_role = '$id_role'");
$nama_role  = ($row = mysqli_fetch_assoc($query_role)) ? $row['nama_role'] : "User";

// === LOGIKA FILTER PENCARIAN ===
$keyword_cari     = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';
$filter_penyakit  = isset($_GET['penyakit']) ? mysqli_real_escape_string($koneksi, $_GET['penyakit']) : '';
$filter_kelurahan = isset($_GET['kelurahan']) ? mysqli_real_escape_string($koneksi, $_GET['kelurahan']) : '';
$filter_status    = isset($_GET['status']) ? mysqli_real_escape_string($koneksi, $_GET['status']) : '';

$where_clauses = [];
$where_clauses[] = "k.is_deleted = 0"; // Sembunyikan data soft delete

if (!empty($keyword_cari)) {
    $where_clauses[] = "(k.nama_pasien LIKE '%$keyword_cari%' OR k.nik_pasien LIKE '%$keyword_cari%')";
}
if (!empty($filter_penyakit)) {
    $where_clauses[] = "k.id_penyakit = '$filter_penyakit'";
}
if (!empty($filter_kelurahan)) {
    $where_clauses[] = "k.id_kelurahan = '$filter_kelurahan'";
}
if (!empty($filter_status)) {
    $where_clauses[] = "k.status_kasus = '$filter_status'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Master Data untuk Dropdown Filter
$q_penyakit = mysqli_query($koneksi, "SELECT * FROM tabel_penyakit WHERE status_pantau = 'aktif' ORDER BY nama_populer ASC");
$q_kelurahan = mysqli_query($koneksi, "SELECT * FROM tabel_kelurahan ORDER BY nama_kelurahan ASC");

$title = "Data Manajemen Kasus";
include '../../layout/header.php';
include '../../layout/sidebar.php';
?>

<style>
    .panel-card {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 4px 20px var(--shadow-color);
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
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        white-space: nowrap;
    }
    
    .table-custom tbody td {
        background-color: var(--bg-panel);
        color: var(--text-color) !important;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
        padding: 1rem;
    }

    [data-theme="light"] .table-custom.table-hover tbody tr:hover td { background-color: rgba(13, 110, 253, 0.02); }
    [data-theme="dark"] .table-custom.table-hover tbody tr:hover td { background-color: rgba(255, 255, 255, 0.02); }

    .text-dynamic { color: var(--text-color) !important; }

    /* Custom Input Search & Filter */
    .filter-control {
        background-color: var(--bg-body);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
    .filter-control:focus {
        background-color: var(--bg-body);
        color: var(--text-color);
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    .input-group-text.filter-bg {
        background-color: var(--bg-body);
        border-color: var(--border-color);
        color: var(--text-muted);
    }
    
    /* Animasi Toast */
    @keyframes slideInDown { from { transform: translateY(-100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<div id="main-content">

    <?php include '../../layout/topbar.php'; ?>

    <!-- TOAST NOTIFIKASI -->
    <?php if (isset($_SESSION['alert_sukses'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-lg" role="alert" style="position: fixed; top: 80px; right: 20px; z-index: 9999; border-radius: 10px; border-left: 5px solid #198754; min-width: 350px; animation: slideInDown 0.4s ease-out;">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div><?php echo $_SESSION['alert_sukses']; ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="top: 50%; transform: translateY(-50%);"></button>
        </div>
        <?php unset($_SESSION['alert_sukses']); ?>
    <?php endif; ?>

    <!-- HEADER & TOMBOL AKSI -->
    <div class="row mb-4 align-items-center mt-2">
        <div class="col-md-7">
            <h4 class="text-dynamic fw-bold mb-1">
                <i class="bi bi-file-earmark-medical me-2 text-primary"></i> Data Manajemen Kasus
            </h4>
            <p class="mb-0" style="color: var(--text-color); opacity: 0.75; font-size: 0.9rem;">
                Buku register kasus harian dan pemantauan status pasien EWS.
            </p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <a href="tambah_kasus.php" class="btn btn-primary rounded-pill shadow-sm px-4 py-2 fw-medium">
                <i class="bi bi-plus-circle-fill me-1"></i> Tambah Kasus
            </a>
        </div>
    </div>

    <!-- PANEL FILTER & PENCARIAN -->
    <div class="panel-card p-3 mb-4">
        <form action="index.php" method="GET" class="row g-3 align-items-center">
            
            <!-- Cari NIK / Nama -->
            <div class="col-lg-3 col-md-6">
                <div class="input-group">
                    <span class="input-group-text filter-bg border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control filter-control border-start-0 ps-0" name="cari" placeholder="Cari Nama / NIK..." value="<?= htmlspecialchars($keyword_cari); ?>">
                </div>
            </div>
            
            <!-- Filter Penyakit -->
            <div class="col-lg-2 col-md-6">
                <select class="form-select filter-control" name="penyakit">
                    <option value="">Semua Penyakit</option>
                    <?php while ($peny = mysqli_fetch_assoc($q_penyakit)): ?>
                        <option value="<?= $peny['id_penyakit']; ?>" <?= ($filter_penyakit == $peny['id_penyakit']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($peny['nama_populer']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Filter Kelurahan -->
            <div class="col-lg-2 col-md-4">
                <select class="form-select filter-control" name="kelurahan">
                    <option value="">Semua Kelurahan</option>
                    <?php while ($kel = mysqli_fetch_assoc($q_kelurahan)): ?>
                        <option value="<?= $kel['id_kelurahan']; ?>" <?= ($filter_kelurahan == $kel['id_kelurahan']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kel['nama_kelurahan']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Filter Status -->
            <div class="col-lg-3 col-md-4">
                <select class="form-select filter-control" name="status">
                    <option value="">Semua Status</option>
                    <option value="suspek" <?= ($filter_status == 'suspek') ? 'selected' : ''; ?>>🟡 Suspek</option>
                    <option value="terkonfirmasi" <?= ($filter_status == 'terkonfirmasi') ? 'selected' : ''; ?>>🔴 Terkonfirmasi</option>
                    <option value="sembuh" <?= ($filter_status == 'sembuh') ? 'selected' : ''; ?>>🟢 Sembuh</option>
                    <option value="meninggal" <?= ($filter_status == 'meninggal') ? 'selected' : ''; ?>>⚫ Meninggal</option>
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="col-lg-2 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-medium shadow-sm"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                <?php if(!empty($keyword_cari) || !empty($filter_penyakit) || !empty($filter_kelurahan) || !empty($filter_status)): ?>
                    <a href="index.php" class="btn btn-outline-danger shadow-sm" title="Reset Filter"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- TABEL DATA KASUS -->
    <div class="row">
        <div class="col-12">
            <div class="panel-card p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="15%">Tanggal Lapor</th>
                                <th width="25%">Identitas Pasien</th>
                                <th width="20%">Diagnosis</th>
                                <th width="15%">Kelurahan</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query_kasus = "
                                SELECT 
                                    k.id_kasus, 
                                    k.nama_pasien, 
                                    k.nik_pasien,
                                    k.tanggal_lapor, 
                                    k.status_kasus,
                                    kel.nama_kelurahan,
                                    p.nama_populer 
                                FROM tabel_kasus k
                                JOIN tabel_kelurahan kel ON k.id_kelurahan = kel.id_kelurahan
                                JOIN tabel_penyakit p ON k.id_penyakit = p.id_penyakit
                                $where_sql
                                ORDER BY k.tanggal_lapor DESC, k.id_kasus DESC
                            ";
                            
                            $result_kasus = mysqli_query($koneksi, $query_kasus);
                            $no = 1;

                            if ($result_kasus && mysqli_num_rows($result_kasus) > 0) {
                                while ($row = mysqli_fetch_assoc($result_kasus)) {
                                    
                                    // Logika Keamanan Data: Masking NIK
                                    $nik_asli = htmlspecialchars($row['nik_pasien']);
                                    if (strlen($nik_asli) == 16) {
                                        $nik_masking = substr($nik_asli, 0, 6) . 'xxxxxx' . substr($nik_asli, 12, 4);
                                    } else {
                                        $nik_masking = $nik_asli; // Fallback jika format salah
                                    }
                                    
                                    // Pewarnaan Badge Status (Soft Gradasi / Opacity)
                                    switch ($row['status_kasus']) {
                                        case 'terkonfirmasi':
                                            $badge_ui = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3 py-1 fw-bold">Terkonfirmasi</span>';
                                            break;
                                        case 'suspek':
                                            $badge_ui = '<span class="badge bg-warning bg-opacity-10 text-dark border border-warning rounded-pill px-3 py-1 fw-bold">Suspek</span>';
                                            break;
                                        case 'sembuh':
                                            $badge_ui = '<span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-1 fw-bold">Sembuh</span>';
                                            break;
                                        case 'meninggal':
                                            $badge_ui = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-3 py-1 fw-bold">Meninggal</span>';
                                            break;
                                        default:
                                            $badge_ui = '<span class="badge bg-light text-dark border rounded-pill px-3 py-1">Unknown</span>';
                                    }
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?php echo $no++; ?></td>
                                    
                                    <td>
                                        <div class="fw-medium text-dynamic" style="font-size: 0.95rem;">
                                            <i class="bi bi-calendar3 me-1 text-muted"></i> <?php echo date('d M Y', strtotime($row['tanggal_lapor'])); ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Identitas Ditumpuk (Nama Atas, NIK Bawah) -->
                                    <td>
                                        <div class="fw-bold text-dynamic" style="font-size: 1.05rem;"><?php echo htmlspecialchars($row['nama_pasien']); ?></div>
                                        <div style="font-family: monospace; font-size: 0.85rem; color: #0d6efd;">NIK: <?php echo $nik_masking; ?></div>
                                    </td>
                                    
                                    <td>
                                        <div class="fw-semibold text-dynamic" style="font-size: 0.95rem;">
                                            <i class="bi bi-virus me-1 text-primary"></i> <?php echo htmlspecialchars($row['nama_populer']); ?>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="text-dynamic" style="font-size: 0.95rem;">
                                            <i class="bi bi-geo-alt-fill me-1 text-danger"></i> <?php echo htmlspecialchars($row['nama_kelurahan']); ?>
                                        </div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php echo $badge_ui; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="btn-group shadow-sm">
                                            <!-- TOMBOL BARU: SYNC SATUSEHAT -->
                                            <button type="button" onclick="simulasiSyncSatuSehat('<?php echo $row['id_kasus']; ?>')" class="btn btn-sm btn-outline-success" title="Sync ke SATUSEHAT Kemenkes">
                                                <i class="bi bi-cloud-upload-fill"></i>
                                            </button>

                                            <a href="detail_kasus.php?id=<?php echo $row['id_kasus']; ?>" class="btn btn-sm btn-outline-info" title="Detail Kasus">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <a href="edit_kasus.php?id=<?php echo $row['id_kasus']; ?>" class="btn btn-sm btn-outline-warning" title="Edit Data">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="proses_hapus_kasus.php?id=<?php echo $row['id_kasus']; ?>" class="btn btn-sm btn-outline-danger" title="Hapus Data" onclick="return confirm('Apakah Anda yakin ingin menghapus catatan kasus <?php echo htmlspecialchars($row['nama_pasien']); ?>?');">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center py-5 text-dynamic' style='opacity: 0.6;'><i class='bi bi-folder-x display-6 d-block mb-3 opacity-50'></i>Belum ada data kasus yang terdaftar atau cocok dengan filter.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL POP-UP PREVIEW FHIR JSON SATUSEHAT KEMENKES       -->
<!-- ======================================================= -->
<div class="modal fade" id="modalSatuSehat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content panel-card p-0 border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-success text-white p-3">
                <h6 class="modal-title fw-bold" id="modalSatuSehatLabel"><i class="bi bi-shield-check me-2"></i> Integrasi SATUSEHAT Kemenkes (FHIR R4)</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" style="background: var(--bg-body);">
                <div class="d-flex align-items-center justify-content-between p-2.5 px-3 bg-success bg-opacity-10 border border-success border-opacity-20 rounded mb-3">
                    <div>
                        <small class="text-muted d-block" style="font-size:0.7rem;">STATUS RESPON SERVER</small>
                        <span class="badge bg-success" style="font-size:0.75rem;"><i class="bi bi-check-circle-fill me-1"></i> HTTP 201 Created</span>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block" style="font-size:0.7rem;">ENCOUNTER ID</small>
                        <code id="satusehat-enc-id" class="fw-bold text-success" style="font-size:0.75rem;">-</code>
                    </div>
                </div>

                <label class="form-label fw-bold text-dynamic mb-1" style="font-size: 0.8rem;"><i class="bi bi-code-slash me-1"></i> Payload JSON Standards (FHIR Bundle Resource):</label>
                <pre id="json-fhir-output" class="p-3 bg-dark text-success rounded" style="max-height: 280px; overflow-y: auto; font-size: 0.75rem; font-family: monospace; border: 1px solid var(--border-color);"></pre>
            </div>
            <div class="modal-footer p-2.5 border-top" style="border-color: var(--border-color) !important;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function simulasiSyncSatuSehat(idKasus) {
    const outputPre = document.getElementById('json-fhir-output');
    const encIdText = document.getElementById('satusehat-enc-id');
    
    // 1. Set Status Loading awal
    outputPre.textContent = "Sedang menghubungkan ke Server SATUSEHAT Kemenkes Staging...";
    encIdText.textContent = "Syncing...";

    // Buka Modal
    const modal = new bootstrap.Modal(document.getElementById('modalSatuSehat'));
    modal.show();

    // 2. Generate Data Simulasi FHIR R4 secara Instan
    setTimeout(() => {
        // Generate UUID Standar FHIR
        const uuidEnc = 'ENC-' + crypto.randomUUID().substring(0, 18);
        const uuidCond = 'COND-' + crypto.randomUUID().substring(0, 18);
        const nowIso = new Date().toISOString();

        // Buat Structure Payload FHIR Bundle Standard Kemenkes
        const fhirPayload = {
            "resourceType": "Bundle",
            "type": "transaction",
            "entry": [
                {
                    "fullUrl": "urn:uuid:" + uuidEnc,
                    "resource": {
                        "resourceType": "Encounter",
                        "id": uuidEnc,
                        "status": "finished",
                        "class": {
                            "system": "http://terminology.hl7.org/CodeSystem/v3-ActCode",
                            "code": "AMB",
                            "display": "ambulatory"
                        },
                        "subject": {
                            "reference": "Patient/100000012345",
                            "display": "Pasien Terdaftar EWS"
                        },
                        "period": {
                            "start": nowIso,
                            "end": nowIso
                        },
                        "location": [
                            {
                                "location": {
                                    "display": "Puskesmas Laweyan - Wilayah Surakarta"
                                }
                            }
                        ]
                    }
                },
                {
                    "fullUrl": "urn:uuid:" + uuidCond,
                    "resource": {
                        "resourceType": "Condition",
                        "id": uuidCond,
                        "clinicalStatus": {
                            "coding": [
                                {
                                    "system": "http://terminology.hl7.org/CodeSystem/condition-clinical",
                                    "code": "active"
                                }
                            ]
                        },
                        "code": {
                            "coding": [
                                {
                                    "system": "http://hl7.org/fhir/sid/icd-10",
                                    "code": "A90",
                                    "display": "Dengue Fever / DBD"
                                }
                            ]
                        },
                        "subject": {
                            "reference": "Patient/100000012345"
                        },
                        "encounter": {
                            "reference": "urn:uuid:" + uuidEnc
                        }
                    }
                }
            ]
        };

        // Render ke Layar Modal
        encIdText.textContent = uuidEnc;
        outputPre.textContent = JSON.stringify(fhirPayload, null, 2);

    }, 400); // Delay efek loading 0.4 detik agar terkesan melakukan request API
}
</script>

<?php include '../../layout/footer.php'; ?>