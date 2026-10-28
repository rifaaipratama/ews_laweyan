<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Ambil data user untuk Header/Sidebar
$id_role      = $_SESSION['id_role'];
$nama_lengkap = $_SESSION['nama_lengkap'];

$query_role = mysqli_query($koneksi, "SELECT nama_role FROM tabel_role WHERE id_role = '$id_role'");
$nama_role  = ($row = mysqli_fetch_assoc($query_role)) ? $row['nama_role'] : "User";

// --- LOGIKA FILTER PENCARIAN ---
$filter_bulan   = isset($_GET['bulan']) ? mysqli_real_escape_string($koneksi, $_GET['bulan']) : '';
$filter_penyakit= isset($_GET['penyakit']) ? mysqli_real_escape_string($koneksi, $_GET['penyakit']) : '';
$filter_status  = isset($_GET['status']) ? mysqli_real_escape_string($koneksi, $_GET['status']) : '';

$where_sql = "WHERE 1=1"; // Base clause

if (!empty($filter_bulan)) {
    // Format YYYY-MM
    $where_sql .= " AND DATE_FORMAT(a.created_at, '%Y-%m') = '$filter_bulan'";
}
if (!empty($filter_penyakit)) {
    $where_sql .= " AND a.id_penyakit = '$filter_penyakit'";
}
if (!empty($filter_status)) {
    $where_sql .= " AND a.status_verifikasi = '$filter_status'";
}

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>

<style>
    /* Upgrade Layout & Panel Modern */
    .panel-card {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
        transition: background-color 0.3s, border-color 0.3s;
    }
    
    /* Aksen Garis Gradien di atas Card */
    .panel-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 5px;
        background: linear-gradient(90deg, #6c757d, #adb5bd);
    }

    /* Kustomisasi Form Filter */
    .filter-box {
        background-color: rgba(0,0,0,0.015);
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid var(--border-color);
    }
    [data-theme="dark"] .filter-box { background-color: rgba(255,255,255,0.02); }

    .form-control, .form-select {
        background-color: var(--bg-body);
        color: var(--text-color);
        border: 1px solid var(--border-color);
        border-radius: 8px;
    }
    .form-control:focus, .form-select:focus {
        border-color: #6c757d;
        box-shadow: 0 0 0 0.25rem rgba(108, 117, 125, 0.25);
        background-color: var(--bg-body);
        color: var(--text-color);
    }

    /* Table Modern UI */
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
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        white-space: nowrap;
        padding: 1rem;
    }
    .table-custom tbody td {
        background-color: var(--bg-panel);
        color: var(--text-color) !important;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
        padding: 1rem;
    }
    [data-theme="light"] .table-custom.table-hover tbody tr:hover td { background-color: rgba(0, 0, 0, 0.02); }
    [data-theme="dark"] .table-custom.table-hover tbody tr:hover td { background-color: rgba(255, 255, 255, 0.03); }

    /* Info Box Dinamis */
    .context-box {
        background-color: rgba(0,0,0,0.03);
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 0.8rem;
        margin-top: 8px;
    }
    [data-theme="dark"] .context-box { background-color: rgba(255,255,255,0.05); }
</style>

<div id="main-content">
    <?php include '../../layout/topbar.php'; ?>

    <!-- Header Section (Bersih dari tombol) -->
    <div class="row mb-4 align-items-center mt-2">
        <div class="col-12">
            <h4 class="fw-bold mb-1 text-dynamic">
                <i class="bi bi-journal-medical text-secondary me-2"></i> Buku Arsip Peringatan EWS
            </h4>
            <p class="mb-0 text-muted" style="font-size: 0.95rem;">
                Rekam jejak seluruh peringatan sistem, termasuk yang telah divalidasi maupun false alarm.
            </p>
        </div>
    </div>

    <!-- Panel Filter (Tombol Kembali ada di kanan atas panel ini) -->
    <div class="panel-card mb-4">
        <!-- Header Panel Filter -->
        <div class="d-flex justify-content-between align-items-center px-4 pt-4 pb-0">
            <div class="fw-bold text-muted small text-uppercase" style="letter-spacing: 1px;">
                <i class="bi bi-search me-1"></i> Filter Data Arsip
            </div>
            <!-- TOMBOL KEMBALI DI-UPGRADE DAN DIPINDAH KESINI -->
            <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill shadow-sm px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Inbox
            </a>
        </div>

        <div class="filter-box m-3 mt-3 d-flex flex-column flex-md-row gap-3 align-items-end">
            <form action="" method="GET" class="w-100 d-flex flex-column flex-md-row gap-3 align-items-end">
                <div class="flex-grow-1">
                    <label class="form-label small fw-semibold text-muted mb-1">Rentang Bulan</label>
                    <input type="month" name="bulan" class="form-control" value="<?= $filter_bulan ?>">
                </div>
                <div class="flex-grow-1">
                    <label class="form-label small fw-semibold text-muted mb-1">Klasifikasi Penyakit</label>
                    <select name="penyakit" class="form-select">
                        <option value="">-- Semua Penyakit --</option>
                        <?php
                        $q_penyakit = mysqli_query($koneksi, "SELECT id_penyakit, nama_populer FROM tabel_penyakit");
                        while($p = mysqli_fetch_assoc($q_penyakit)){
                            $selected = ($filter_penyakit == $p['id_penyakit']) ? 'selected' : '';
                            echo "<option value='{$p['id_penyakit']}' $selected>{$p['nama_populer']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="flex-grow-1">
                    <label class="form-label small fw-semibold text-muted mb-1">Status Verifikasi</label>
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="menunggu" <?= ($filter_status == 'menunggu') ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                        <option value="valid" <?= ($filter_status == 'valid') ? 'selected' : '' ?>>Valid (Terverifikasi)</option>
                        <option value="abaikan" <?= ($filter_status == 'abaikan') ? 'selected' : '' ?>>Abaikan (False Alarm)</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-secondary px-4 shadow-sm">
                        <i class="bi bi-funnel-fill me-1"></i> Filter Data
                    </button>
                    <?php if(!empty($filter_bulan) || !empty($filter_penyakit) || !empty($filter_status)): ?>
                        <a href="arsip_alert.php" class="btn btn-outline-danger px-3 shadow-sm" title="Reset Filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Panel Tabel Arsip -->
    <div class="panel-card mb-5">
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">No</th>
                        <th style="width: 20%;">Waktu & Lokasi</th>
                        <th style="width: 20%;">Klasifikasi & Tingkat Alert</th>
                        <th style="width: 35%;">Detail Peringatan EWS</th>
                        <th style="width: 20%;">Status & Verifikator</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query Arsip dengan Filter
                    $query_arsip = "
                        SELECT a.*, k.nama_kelurahan, p.nama_populer, u.nama_lengkap AS nama_verifikator 
                        FROM tabel_alert a
                        JOIN tabel_kelurahan k ON a.id_kelurahan = k.id_kelurahan
                        JOIN tabel_penyakit p ON a.id_penyakit = p.id_penyakit
                        LEFT JOIN tabel_pengguna u ON a.id_verifikator = u.id_pengguna
                        $where_sql
                        ORDER BY a.created_at DESC
                    ";
                    
                    $result = mysqli_query($koneksi, $query_arsip);
                    $no = 1;

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            
                            // Format Waktu
                            $waktu_alert = date('d M Y, H:i', strtotime($row['created_at']));
                            
                            // Pewarnaan Tingkat Alert
                            $tingkat_badge = '';
                            if ($row['tingkat_alert'] == 'potensi_wabah') {
                                $tingkat_badge = '<span class="badge bg-danger px-2 py-1"><i class="bi bi-exclamation-octagon me-1"></i>Potensi Wabah</span>';
                            } elseif ($row['tingkat_alert'] == 'waspada') {
                                $tingkat_badge = '<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-exclamation-triangle me-1"></i>Waspada</span>';
                            } else {
                                $tingkat_badge = '<span class="badge bg-success px-2 py-1"><i class="bi bi-arrow-down-circle me-1"></i>Kondisi Membaik</span>';
                            }

                            // --- PERBAIKAN HTMLSPECIALCHARS NULL ERROR ---
                            $verif_badge = '';
                            $verifikator = '<span class="text-muted fst-italic small">Belum divalidasi</span>';
                            $nama_v = !empty($row['nama_verifikator']) ? $row['nama_verifikator'] : 'Sistem / Tidak Tercatat';
                            
                            if ($row['status_verifikasi'] == 'menunggu') {
                                $verif_badge = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-2 py-1">Menunggu Verifikasi</span>';
                            } elseif ($row['status_verifikasi'] == 'valid') {
                                $verif_badge = '<span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1"><i class="bi bi-check-lg me-1"></i>Valid (Disiarkan)</span>';
                                $verifikator = "<i class='bi bi-person-check me-1'></i> " . htmlspecialchars($nama_v);
                            } elseif ($row['status_verifikasi'] == 'abaikan') {
                                $verif_badge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1"><i class="bi bi-x-lg me-1"></i>False Alarm</span>';
                                $verifikator = "<i class='bi bi-person-x me-1'></i> " . htmlspecialchars($nama_v);
                            }

                            // Pesan Dinamis (Explainability)
                            if ($row['tingkat_alert'] == 'penurunan') {
                                $pesan_dinamis = "Kasus aktif <b>{$row['nama_populer']}</b> turun menjadi {$row['jumlah_kasus_aktual']} kasus (Batas: {$row['ambang_batas_digunakan']}).";
                            } elseif (!empty($row['jumlah_kasus_aktual'])) {
                                $pesan_dinamis = "Terdapat {$row['jumlah_kasus_aktual']} kasus dalam {$row['periode_hari_digunakan']} hari terakhir. Melampaui batas ({$row['ambang_batas_digunakan']} kasus) dengan kenaikan {$row['persentase_kenaikan']}%.";
                            } else {
                                $pesan_dinamis = "Terdeteksi anomali/lonjakan kasus dari pantauan sistem EWS.";
                            }

                            echo "<tr>";
                            echo "<td class='text-center fw-semibold'>{$no}</td>";
                            
                            echo "<td>
                                    <div class='fw-bold text-dynamic mb-1'>{$waktu_alert}</div>
                                    <div class='small text-muted'><i class='bi bi-geo-alt me-1'></i> Kel. {$row['nama_kelurahan']}</div>
                                  </td>";
                            
                            echo "<td>
                                    <div class='fw-bold text-info mb-2'>{$row['nama_populer']}</div>
                                    <div>{$tingkat_badge}</div>
                                  </td>";
                                  
                            echo "<td>
                                    <div class='fw-semibold text-dynamic mb-1'>{$row['pesan_alert']}</div>
                                    <div class='context-box text-muted'>{$pesan_dinamis}</div>
                                  </td>";
                                  
                            echo "<td>
                                    <div class='mb-2'>{$verif_badge}</div>
                                    <div class='small text-dynamic'>{$verifikator}</div>
                                  </td>";
                            echo "</tr>";
                            
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center text-muted py-5'>
                                <i class='bi bi-inbox-fill fs-1 d-block mb-3 opacity-25'></i>
                                Belum ada riwayat peringatan yang tersimpan atau sesuai dengan filter.
                              </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../layout/footer.php'; ?>