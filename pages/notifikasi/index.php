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
        height: 4px;
        background: linear-gradient(90deg, #0dcaf0, #0d6efd);
        opacity: 0.8;
    }

    .panel-header {
        padding: 1.5rem;
        border-bottom: 1px dashed var(--border-color);
        background-color: transparent;
    }

    /* List Item Alert Modern */
    .list-group-item-custom {
        background-color: transparent;
        border: none;
        border-bottom: 1px solid var(--border-color);
        padding: 1.5rem;
        transition: all 0.2s ease;
        color: var(--text-color);
    }
    .list-group-item-custom:last-child { border-bottom: none; }
    
    /* Efek Hover Seamless Mode Gelap & Terang */
    [data-theme="light"] .list-group-item-custom:hover { background-color: rgba(0, 0, 0, 0.015); transform: translateX(2px); }
    [data-theme="dark"] .list-group-item-custom:hover { background-color: rgba(255, 255, 255, 0.02); transform: translateX(2px); }

    .border-left-danger { border-left: 4px solid #dc3545 !important; }
    .border-left-warning { border-left: 4px solid #ffc107 !important; }
    .border-left-success { border-left: 4px solid #198754 !important; }
    
    /* Box Info Konteks Alert */
    .alert-context-box {
        background-color: rgba(13, 202, 240, 0.05);
        border-radius: 8px;
        padding: 10px 15px;
        margin-top: 12px;
        font-size: 0.85rem;
        border-left: 3px solid #0dcaf0;
        color: var(--text-color);
    }
</style>

<div id="main-content">
    <?php include '../../layout/topbar.php'; ?>

    <div class="row mb-4 align-items-center mt-2">
        <div class="col-md-8">
            <h4 class="fw-bold mb-1" style="color: var(--text-color);">
                <i class="bi bi-bell-fill text-danger me-2"></i> Pusat Notifikasi (Inbox)
            </h4>
            <p class="mb-0" style="color: var(--text-color); opacity: 0.75; font-size: 0.95rem;">
                Pantau peringatan dini <b>terbaru</b> yang belum ditindaklanjuti secara real-time.
            </p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- KOLOM PENUH: PERINGATAN DINI (ZERO INBOX) -->
        <div class="col-12">
            <div class="panel-card h-100 d-flex flex-column">
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0" style="color: var(--text-color);"><i class="bi bi-broadcast me-2 text-primary"></i>Sistem Peringatan Baru</h5>
                    <a href="proses_baca_semua.php" class="btn btn-sm btn-outline-secondary rounded-pill shadow-sm px-3">
                        <i class="bi bi-check2-all me-1"></i> Tandai Semua Dibaca
                    </a>
                </div>
                
                <div class="list-group list-group-flush flex-grow-1">
                    <?php
                    $q_alert = mysqli_query($koneksi, "
                        SELECT a.*, k.nama_kelurahan, p.nama_populer 
                        FROM tabel_alert a
                        JOIN tabel_kelurahan k ON a.id_kelurahan = k.id_kelurahan
                        JOIN tabel_penyakit p ON a.id_penyakit = p.id_penyakit
                        WHERE a.status_verifikasi != 'abaikan' 
                        AND a.status_baca = 'belum'
                        ORDER BY a.created_at DESC
                    ");

                    if (mysqli_num_rows($q_alert) > 0) {
                        while ($alert = mysqli_fetch_assoc($q_alert)) {
                            
                            $id_kelurahan = $alert['id_kelurahan'];
                            $id_penyakit = $alert['id_penyakit'];
                            
                            // Tentukan Warna & Ikon
                            if ($alert['tingkat_alert'] == 'potensi_wabah') {
                                $border_class = 'border-left-danger';
                                $icon = '<div class="bg-danger bg-opacity-10 rounded-circle p-2 d-inline-block"><i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i></div>';
                                $badge = '<span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm">POTENSI WABAH</span>';
                            } elseif ($alert['tingkat_alert'] == 'waspada') {
                                $border_class = 'border-left-warning';
                                $icon = '<div class="bg-warning bg-opacity-10 rounded-circle p-2 d-inline-block"><i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i></div>';
                                $badge = '<span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm">WASPADA</span>';
                            } else {
                                $border_class = 'border-left-success';
                                $icon = '<div class="bg-success bg-opacity-10 rounded-circle p-2 d-inline-block"><i class="bi bi-shield-check text-success fs-5"></i></div>';
                                $badge = '<span class="badge bg-success px-3 py-2 rounded-pill shadow-sm">KONDISI MEMBAIK</span>';
                            }

                            // LOGIKA EXPLAINABILITY DINAMIS
                            if ($alert['tingkat_alert'] == 'penurunan') {
                                $pesan_dinamis = "Kabar baik. Kasus aktif <b>{$alert['nama_populer']}</b> di <b>Kelurahan {$alert['nama_kelurahan']}</b> turun menjadi <b>{$alert['jumlah_kasus_aktual']} kasus</b> (di bawah ambang batas waspada).";
                            } elseif (!empty($alert['jumlah_kasus_aktual'])) {
                                $pesan_dinamis = "Terdapat <b>{$alert['jumlah_kasus_aktual']} kasus</b> {$alert['nama_populer']} dalam {$alert['periode_hari_digunakan']} hari terakhir di <b>Kelurahan {$alert['nama_kelurahan']}</b>. Melampaui batas waspada ({$alert['ambang_batas_digunakan']} kasus) dan meningkat <b>{$alert['persentase_kenaikan']}%</b>.";
                            } else {
                                $pesan_dinamis = "Terdeteksi lonjakan kasus <b>{$alert['nama_populer']}</b> di wilayah <b>Kelurahan {$alert['nama_kelurahan']}</b>.";
                            }

                            // LOGIKA REKAM JEJAK PEMICU
                            $waktu_alert = $alert['created_at'];
                            $q_trigger = mysqli_query($koneksi, "
                                SELECT k.nama_pasien, u.nama_lengkap AS nama_petugas
                                FROM tabel_kasus k
                                JOIN tabel_pengguna u ON k.id_pengguna = u.id_pengguna
                                WHERE k.id_kelurahan = '$id_kelurahan' AND k.id_penyakit = '$id_penyakit' 
                                AND k.created_at <= '$waktu_alert'
                                ORDER BY k.created_at DESC LIMIT 1
                            ");
                            
                            $info_pemicu = "";
                            if(mysqli_num_rows($q_trigger) > 0) {
                                $trigger = mysqli_fetch_assoc($q_trigger);
                                $info_pemicu = "
                                    <div class='alert-context-box'>
                                        <i class='bi bi-info-circle me-1 text-info'></i> 
                                        <b>Konteks Pemicu:</b> Update data pasien <b>{$trigger['nama_pasien']}</b> oleh petugas <b>{$trigger['nama_petugas']}</b>.
                                    </div>
                                ";
                            }

                            // LOGIKA STATUS VERIFIKASI
                            if ($alert['status_verifikasi'] == 'menunggu') {
                                $badge .= ' <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-3 py-2 rounded-pill ms-1"><i class="bi bi-hourglass-split me-1"></i>Menunggu Verifikasi</span>';
                            } else {
                                $badge .= ' <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill ms-1"><i class="bi bi-shield-check me-1"></i>Terverifikasi</span>';
                            }

                            $waktu = date('d M Y, H:i', strtotime($alert['created_at']));
                            
                            echo "
                            <div class='list-group-item-custom $border_class d-flex gap-3 align-items-start'>
                                <div class='mt-1'>$icon</div>
                                <div class='flex-grow-1'>
                                    <div class='d-flex justify-content-between align-items-center mb-2'>
                                        <div class='fw-bold fs-6'>{$alert['pesan_alert']}</div>
                                        <span class='badge bg-secondary bg-opacity-10 text-secondary'><i class='bi bi-clock me-1'></i> $waktu</span>
                                    </div>
                                    <p class='mb-2 small' style='opacity: 0.9;'>$pesan_dinamis</p>
                                    
                                    $info_pemicu
                                    
                                    <div class='d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2'>
                                        <div>$badge</div>
                                        <div class='d-flex gap-2'>
                                            <a href='proses_baca.php?id={$alert['id_alert']}' class='btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm' title='Tandai Selesai (Arsipkan)'>
                                                <i class='bi bi-check-lg me-1'></i> Arsipkan
                                            </a>
                                            <a href='../kasus/index.php?filter_kelurahan={$alert['id_kelurahan']}' class='btn btn-sm btn-primary rounded-pill px-3 shadow-sm'>Lihat Kasus</a>
                                        </div>
                                    </div>";

                            // Tombol Verifikasi KHUSUS ADMIN
                            if ($alert['status_verifikasi'] == 'menunggu' && $_SESSION['id_role'] == 1) {
                                echo "
                                    <div class='mt-3 pt-3 border-top d-flex gap-2' style='border-color: var(--border-color) !important;'>
                                        <a href='proses_verifikasi.php?id={$alert['id_alert']}&aksi=valid' class='btn btn-sm btn-success rounded-pill px-3 shadow-sm'><i class='bi bi-shield-check me-1'></i> Validasi Alert</a>
                                        <a href='proses_verifikasi.php?id={$alert['id_alert']}&aksi=abaikan' class='btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm' onclick=\"return confirm('Yakin ingin mengabaikan peringatan ini?');\"><i class='bi bi-x-circle me-1'></i> False Alarm</a>
                                    </div>
                                ";
                            }

                            echo "      </div>
                            </div>";
                        }
                    } else {
                        // TAMPILAN ZERO INBOX Modern
                        echo "<div class='p-5 text-center d-flex flex-column align-items-center justify-content-center h-100' style='opacity: 0.7; color: var(--text-color); min-height: 300px;'>
                                <i class='bi bi-envelope-check-fill text-success mb-3' style='font-size: 4rem; opacity: 0.5;'></i>
                                <h5 class='fw-bold mb-1'>Kotak Masuk Bersih</h5>
                                <p class='small mb-4'>Tidak ada peringatan baru yang memerlukan perhatian Anda saat ini.</p>
                                <a href='arsip_alert.php' class='btn btn-outline-secondary rounded-pill px-4 shadow-sm'>
                                    <i class='bi bi-journal-text me-2'></i>Buka Lemari Arsip Alert
                                </a>
                              </div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../layout/footer.php'; ?>