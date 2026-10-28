<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

$nama_lengkap = $_SESSION['nama_lengkap'];
$id_role      = $_SESSION['id_role'];

$query_role = mysqli_query($koneksi, "SELECT nama_role FROM tabel_role WHERE id_role = '$id_role'");
$nama_role  = ($row = mysqli_fetch_assoc($query_role)) ? $row['nama_role'] : "User";

// ==========================================
// 1. PENGAMBILAN DATA KARTU METRIK REAL-TIME
// ==========================================
$tanggal_hari_ini = date('Y-m-d');
$tanggal_kemarin  = date('Y-m-d', strtotime('-1 day'));

$q_kasus_ini = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tabel_kasus WHERE is_deleted = 0 AND DATE(tanggal_lapor) = '$tanggal_hari_ini'");
$d_kasus_ini = mysqli_fetch_assoc($q_kasus_ini)['total'];

$q_kasus_kemarin = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tabel_kasus WHERE is_deleted = 0 AND DATE(tanggal_lapor) = '$tanggal_kemarin'");
$d_kasus_kemarin = mysqli_fetch_assoc($q_kasus_kemarin)['total'];

$selisih_kasus = $d_kasus_ini - $d_kasus_kemarin;
if ($d_kasus_kemarin > 0) {
    $persen_kasus = round(($selisih_kasus / $d_kasus_kemarin) * 100, 1);
} else {
    $persen_kasus = $d_kasus_ini > 0 ? 100 : 0;
}

// Total Kasus Aktif (Hanya Suspek & Terkonfirmasi, is_deleted = 0)
$q_total_kasus = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tabel_kasus WHERE is_deleted = 0 AND status_kasus IN ('suspek', 'terkonfirmasi')");
$total_kasus_aktif = mysqli_fetch_assoc($q_total_kasus)['total'];

$q_kelurahan = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tabel_kelurahan");
$total_kelurahan = mysqli_fetch_assoc($q_kelurahan)['total'];

$q_penyakit = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tabel_penyakit WHERE status_pantau = 'aktif'");
$total_penyakit = mysqli_fetch_assoc($q_penyakit)['total'];


// ==========================================
// 2. DATA GRAFIK TOP 5 PENYAKIT & INSIGHT WILAYAH
// ==========================================
$labels_penyakit = [];
$data_penyakit = [];
$top_penyakit_raw = [];

$q_top = mysqli_query($koneksi, "
    SELECT p.id_penyakit, p.nama_populer, COUNT(k.id_kasus) AS jumlah
    FROM tabel_kasus k
    JOIN tabel_penyakit p ON k.id_penyakit = p.id_penyakit
    WHERE k.is_deleted = 0 
      AND k.status_kasus IN ('suspek', 'terkonfirmasi')
      AND k.tanggal_lapor >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY k.id_penyakit
    ORDER BY jumlah DESC
    LIMIT 5
");

while($row = mysqli_fetch_assoc($q_top)) {
    $labels_penyakit[] = $row['nama_populer'];
    $data_penyakit[] = $row['jumlah'];
    $top_penyakit_raw[] = $row;
}

$insight_list = [];
$limit_insight = 0;
foreach($top_penyakit_raw as $tp) {
    if ($limit_insight >= 4) break;
    $id_p = $tp['id_penyakit'];
    $nama_p = $tp['nama_populer'];
    $total_p = $tp['jumlah'];
    
    $q_kel = mysqli_query($koneksi, "
        SELECT k.nama_kelurahan, COUNT(kas.id_kasus) as jml_kasus
        FROM tabel_kasus kas
        JOIN tabel_kelurahan k ON kas.id_kelurahan = k.id_kelurahan
        WHERE kas.id_penyakit = '$id_p'
          AND kas.is_deleted = 0 
          AND kas.status_kasus IN ('suspek', 'terkonfirmasi')
          AND kas.tanggal_lapor >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY kas.id_kelurahan
        ORDER BY jml_kasus DESC
        LIMIT 2
    ");
    
    $kel_text = [];
    while($kel = mysqli_fetch_assoc($q_kel)) {
        $kel_text[] = "<b style='color: var(--text-main);'>Kel. " . $kel['nama_kelurahan'] . "</b> (" . $kel['jml_kasus'] . " kasus)";
    }
    
    if (count($kel_text) > 1) {
        $gabungan_kel = implode(" dan ", $kel_text);
        $narasi = "Terdeteksi <b class='text-danger'>$total_p kasus $nama_p</b>, sebaran tertinggi berada di $gabungan_kel.";
    } elseif (count($kel_text) == 1) {
        $narasi = "Terdeteksi <b class='text-danger'>$total_p kasus $nama_p</b>, seluruhnya terpusat di {$kel_text[0]}.";
    } else {
        $narasi = "Terdeteksi <b class='text-danger'>$total_p kasus $nama_p</b> di wilayah Laweyan.";
    }
    
    $insight_list[] = $narasi;
    $limit_insight++;
}

// ==========================================
// 3. LOGIKA PETA REAL-TIME (LANGSUNG DARI TABEL_KASUS)
// ==========================================
$status_wilayah = [];

// Menghitung jumlah kasus AKTIF (Suspek & Terkonfirmasi) per Kelurahan secara MURNI dari tabel_kasus
$q_kasus_wilayah = mysqli_query($koneksi, "
    SELECT kel.nama_kelurahan, COUNT(k.id_kasus) as total_aktif
    FROM tabel_kelurahan kel
    LEFT JOIN tabel_kasus k ON kel.id_kelurahan = k.id_kelurahan 
        AND k.is_deleted = 0 
        AND k.status_kasus IN ('suspek', 'terkonfirmasi')
    GROUP BY kel.id_kelurahan, kel.nama_kelurahan
");

while ($row = mysqli_fetch_assoc($q_kasus_wilayah)) {
    $nama = $row['nama_kelurahan'];
    $jml_aktif = intval($row['total_aktif']);
    
    // ATURAN ZONA BERDASARKAN JUMLAH KASUS AKTIF RIIL:
    // 3 atau lebih kasus aktif  -> MERAH (Potensi Wabah / Episentrum)
    // 1 - 2 kasus aktif          -> KUNING (Waspada)
    // 0 kasus aktif              -> HIJAU (Aman)
    if ($jml_aktif >= 3) {
        $status_wilayah[$nama] = 'merah';
    } elseif ($jml_aktif >= 1) {
        $status_wilayah[$nama] = 'kuning';
    } else {
        $status_wilayah[$nama] = 'hijau';
    }
}

$top_penyakit_peta = [];
$q_kasus_peta = mysqli_query($koneksi, "
    SELECT k.nama_kelurahan, p.nama_populer, COUNT(kas.id_kasus) as jml
    FROM tabel_kasus kas
    JOIN tabel_kelurahan k ON kas.id_kelurahan = k.id_kelurahan
    JOIN tabel_penyakit p ON kas.id_penyakit = p.id_penyakit
    WHERE kas.is_deleted = 0 
      AND kas.status_kasus IN ('suspek', 'terkonfirmasi') 
    GROUP BY kas.id_kelurahan, kas.id_penyakit
    ORDER BY k.nama_kelurahan ASC, jml DESC
");

while ($row = mysqli_fetch_assoc($q_kasus_peta)) {
    $nama = $row['nama_kelurahan'];
    if (!isset($top_penyakit_peta[$nama])) $top_penyakit_peta[$nama] = [];
    if (count($top_penyakit_peta[$nama]) < 3) {
        $top_penyakit_peta[$nama][] = ['penyakit' => $row['nama_populer'], 'jumlah' => $row['jml']];
    }
}

$geojson_raw = '{
    "type": "FeatureCollection",
    "features": [
        {"type": "Feature", "properties": {"nama": "Karangasem"}, "geometry": {"type": "Polygon", "coordinates": [[[110.775, -7.558], [110.782, -7.556], [110.785, -7.562], [110.776, -7.564], [110.775, -7.558]]]}},
        {"type": "Feature", "properties": {"nama": "Jajar"}, "geometry": {"type": "Polygon", "coordinates": [[[110.782, -7.556], [110.789, -7.555], [110.791, -7.561], [110.785, -7.562], [110.782, -7.556]]]}},
        {"type": "Feature", "properties": {"nama": "Kerten"}, "geometry": {"type": "Polygon", "coordinates": [[[110.789, -7.555], [110.798, -7.557], [110.796, -7.563], [110.791, -7.561], [110.789, -7.555]]]}},
        {"type": "Feature", "properties": {"nama": "Pajang"}, "geometry": {"type": "Polygon", "coordinates": [[[110.776, -7.564], [110.785, -7.562], [110.783, -7.569], [110.773, -7.571], [110.776, -7.564]]]}},
        {"type": "Feature", "properties": {"nama": "Sondakan"}, "geometry": {"type": "Polygon", "coordinates": [[[110.785, -7.562], [110.791, -7.561], [110.792, -7.567], [110.783, -7.569], [110.785, -7.562]]]}},
        {"type": "Feature", "properties": {"nama": "Purwosari"}, "geometry": {"type": "Polygon", "coordinates": [[[110.791, -7.561], [110.796, -7.563], [110.801, -7.561], [110.803, -7.566], [110.792, -7.567], [110.791, -7.561]]]}},
        {"type": "Feature", "properties": {"nama": "Penumping"}, "geometry": {"type": "Polygon", "coordinates": [[[110.801, -7.561], [110.808, -7.562], [110.807, -7.568], [110.803, -7.566], [110.801, -7.561]]]}},
        {"type": "Feature", "properties": {"nama": "Sriwedari"}, "geometry": {"type": "Polygon", "coordinates": [[[110.808, -7.562], [110.814, -7.564], [110.812, -7.570], [110.807, -7.568], [110.808, -7.562]]]}},
        {"type": "Feature", "properties": {"nama": "Laweyan"}, "geometry": {"type": "Polygon", "coordinates": [[[110.773, -7.571], [110.783, -7.569], [110.786, -7.576], [110.778, -7.578], [110.773, -7.571]]]}},
        {"type": "Feature", "properties": {"nama": "Bumi"}, "geometry": {"type": "Polygon", "coordinates": [[[110.783, -7.569], [110.792, -7.567], [110.794, -7.574], [110.786, -7.576], [110.783, -7.569]]]}},
        {"type": "Feature", "properties": {"nama": "Panularan"}, "geometry": {"type": "Polygon", "coordinates": [[[110.792, -7.567], [110.803, -7.566], [110.807, -7.568], [110.812, -7.570], [110.809, -7.576], [110.794, -7.574], [110.792, -7.567]]]}}
    ]
}';

$geo_data = json_decode($geojson_raw, true);
foreach ($geo_data['features'] as &$feature) {
    $nama_kel = $feature['properties']['nama'];
    $feature['properties']['status'] = isset($status_wilayah[$nama_kel]) ? $status_wilayah[$nama_kel] : 'hijau';
    $feature['properties']['detail_penyakit'] = isset($top_penyakit_peta[$nama_kel]) ? $top_penyakit_peta[$nama_kel] : [];
    $total_kasus = 0;
    if (isset($top_penyakit_peta[$nama_kel])) {
        foreach ($top_penyakit_peta[$nama_kel] as $tp) $total_kasus += $tp['jumlah'];
    }
    $feature['properties']['kasus'] = $total_kasus;
}
$geojson_ready = json_encode($geo_data);

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>

<!-- Leaflet CSS untuk Peta -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    .panel-card {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 4px 20px var(--shadow-color);
        padding: 1.25rem;
        height: 100%;
        transition: background-color 0.3s, border-color 0.3s, box-shadow 0.3s;
    }

    .icon-box {
        width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;
    }

    /* CSS Khusus untuk Animasi Hover Card Dashboard */
    .card-metric {
        transition: all 0.3s ease;
    }
    .card-metric:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(13, 110, 253, 0.12) !important;
    }
    
    a.panel-card {
        text-decoration: none;
        color: inherit;
        display: flex; 
        cursor: pointer;
    }

    .map-area-dash { height: 380px; position: relative; overflow: hidden; padding: 0 !important; }
    #mapDashboard { height: 100%; width: 100%; z-index: 1; border-radius: 12px; }
    
    .btn-floating-map {
        position: absolute; bottom: 20px; right: 20px; z-index: 1000;
        border-radius: 50px; font-weight: 600; padding: 8px 24px;
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4); transition: transform 0.2s;
    }
    .btn-floating-map:hover { transform: scale(1.05); }

    [data-theme="dark"] .leaflet-layer,
    [data-theme="dark"] .leaflet-control-zoom-in,
    [data-theme="dark"] .leaflet-control-zoom-out { filter: brightness(0.8) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3) brightness(0.7); }
    
    .leaflet-popup-content-wrapper { background-color: var(--bg-panel); color: var(--text-main); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .leaflet-popup-tip { background-color: var(--bg-panel); }

    .side-list-area { height: 380px; display: flex; flex-direction: column; overflow: hidden; padding: 0 !important; }
    .side-list-scroll { overflow-y: auto; flex-grow: 1; padding: 0; }
    .side-list-scroll::-webkit-scrollbar { width: 4px; }
    .side-list-scroll::-webkit-scrollbar-track { background: transparent; }
    .side-list-scroll::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }

    .bottom-area { height: 260px; display: flex; flex-direction: column; }
    .chart-container { position: relative; height: 100%; width: 100%; flex-grow: 1; }

    .text-sub { color: var(--text-muted) !important; }
    .alert-list-item:hover { background-color: rgba(13,110,253,0.03) !important; }
    [data-theme="dark"] .alert-list-item:hover { background-color: rgba(255,255,255,0.03) !important; }
    
    .insight-item { background: rgba(13, 110, 253, 0.02); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 15px; display: flex; align-items: center; gap: 12px; transition: transform 0.2s; }
    .insight-item:hover { transform: translateX(3px); background: rgba(13, 110, 253, 0.05); }
    [data-theme="dark"] .insight-item { background: rgba(255,255,255,0.02); }
    [data-theme="dark"] .insight-item:hover { background: rgba(255,255,255,0.05); }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div id="main-content">
    <?php include '../../layout/topbar.php'; ?>

    <!-- BARIS 1: Kartu Metrik -->
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="panel-card d-flex align-items-center card-metric">
                <div class="icon-box bg-primary me-3 shadow-sm"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="text-sub" style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;">KASUS BARU HARI INI</div>
                    <h3 class="mb-0 fw-bold text-dynamic"><?php echo number_format($d_kasus_ini); ?></h3>
                    <small class="<?php echo $persen_kasus >= 0 ? 'text-danger' : 'text-success'; ?>" style="font-size: 0.7rem;">
                        <i class="bi bi-arrow-<?php echo $persen_kasus >= 0 ? 'up' : 'down'; ?>-short"></i> <?php echo abs($persen_kasus); ?>% dari kemarin
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="../kasus/index.php" class="panel-card d-flex align-items-center card-metric">
                <div class="icon-box bg-danger me-3 shadow-sm"><i class="bi bi-clipboard2-pulse-fill"></i></div>
                <div>
                    <div class="text-sub" style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;">TOTAL KASUS AKTIF</div>
                    <h3 class="mb-0 fw-bold text-dynamic"><?php echo number_format($total_kasus_aktif); ?></h3>
                    <small class="text-sub" style="font-size: 0.7rem;">Suspek & Terkonfirmasi</small>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="../kelurahan/index.php" class="panel-card d-flex align-items-center card-metric">
                <div class="icon-box bg-warning text-dark me-3 shadow-sm"><i class="bi bi-pin-map-fill"></i></div>
                <div>
                    <div class="text-sub" style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;">TOTAL KELURAHAN</div>
                    <h3 class="mb-0 fw-bold text-dynamic"><?php echo number_format($total_kelurahan); ?></h3>
                    <small class="text-sub" style="font-size: 0.7rem;">Wilayah Laweyan</small>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="../data_penyakit/index.php" class="panel-card d-flex align-items-center card-metric">
                <div class="icon-box bg-success me-3 shadow-sm"><i class="bi bi-virus"></i></div>
                <div>
                    <div class="text-sub" style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;">MASTER PENYAKIT</div>
                    <h3 class="mb-0 fw-bold text-dynamic"><?php echo number_format($total_penyakit); ?></h3>
                    <small class="text-sub" style="font-size: 0.7rem;">Aktif dipantau EWS</small>
                </div>
            </a>
        </div>
    </div>

    <!-- BARIS 2: Peta Sebaran & Alert -->
    <div class="row g-3 mb-3">
        <div class="col-xl-8 col-lg-7">
            <div class="panel-card map-area-dash">
                <div id="mapDashboard"></div>
                <a href="../peta/index.php" class="btn btn-primary btn-floating-map">
                    <i class="bi bi-arrows-fullscreen me-1"></i> Lihat Peta Penuh
                </a>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="panel-card side-list-area">
                <div class="p-3 border-bottom m-0 d-flex justify-content-between align-items-center" style="border-color: var(--border-color) !important;">
                    <span class="text-dynamic fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;"><i class="bi bi-bell-fill me-2" style="color: #dc3545;"></i> ALERT TERBARU</span>
                </div>
                
                <div class="side-list-scroll">
                    <?php
                    $q_alert_dash = mysqli_query($koneksi, "
                        SELECT a.id_alert, a.tingkat_alert, a.created_at, p.nama_populer, k.nama_kelurahan 
                        FROM tabel_alert a
                        JOIN tabel_penyakit p ON a.id_penyakit = p.id_penyakit
                        JOIN tabel_kelurahan k ON a.id_kelurahan = k.id_kelurahan
                        WHERE a.status_verifikasi != 'abaikan'
                        ORDER BY a.created_at DESC LIMIT 5
                    ");

                    if (mysqli_num_rows($q_alert_dash) > 0) {
                        echo '<div class="list-group list-group-flush">';
                        while ($alrt = mysqli_fetch_assoc($q_alert_dash)) {
                            $warna_icon = 'text-success'; $bg_icon = 'bg-success';
                            if ($alrt['tingkat_alert'] == 'potensi_wabah') {
                                $warna_icon = 'text-danger'; $bg_icon = 'bg-danger';
                            } elseif ($alrt['tingkat_alert'] == 'waspada') {
                                $warna_icon = 'text-warning'; $bg_icon = 'bg-warning';
                            }
                            $waktu_lalu = date('d M, H:i', strtotime($alrt['created_at']));

                            echo "
                            <a href='../notifikasi/index.php' class='list-group-item list-group-item-action alert-list-item d-flex gap-3 py-3' style='background: transparent; border-bottom: 1px solid var(--border-color);'>
                                <div class='{$bg_icon} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center' style='width: 38px; height: 38px; flex-shrink: 0;'>
                                    <i class='bi bi-exclamation-circle-fill {$warna_icon}'></i>
                                </div>
                                <div class='d-flex flex-column w-100'>
                                    <div class='d-flex justify-content-between align-items-center mb-1'>
                                        <h6 class='mb-0 text-dynamic fw-bold' style='font-size: 0.85rem;'>{$alrt['nama_populer']}</h6>
                                        <small class='text-sub' style='font-size: 0.7rem;'>{$waktu_lalu}</small>
                                    </div>
                                    <p class='mb-0 text-sub' style='font-size: 0.75rem;'><i class='bi bi-geo-alt-fill me-1 opacity-50'></i>Kel. {$alrt['nama_kelurahan']}</p>
                                </div>
                            </a>";
                        }
                        echo '</div>';
                    } else {
                        echo "
                        <div class='text-center mt-5 p-4'>
                            <i class='bi bi-shield-check text-success fs-1 opacity-50 d-block mb-3'></i>
                            <h6 class='text-dynamic fw-bold mb-1'>Sistem Aman</h6>
                            <p class='text-sub mb-0' style='font-size: 0.8rem;'>Tidak ada peringatan EWS<br>yang aktif saat ini.</p>
                        </div>";
                    }
                    ?>
                </div>
                
                <div class="p-3 border-top text-center mt-auto" style="border-color: var(--border-color) !important; background: rgba(0,0,0,0.02);">
                    <a href="../notifikasi/index.php" class="text-decoration-none fw-bold" style="font-size: 0.8rem; color: #0d6efd;">
                        Buka Pusat Notifikasi <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- BARIS 3: Insight & Chart -->
    <div class="row g-3 mb-4">
        <div class="col-xl-7 col-lg-6">
            <div class="panel-card bottom-area p-0 overflow-hidden">
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom" style="border-color: var(--border-color) !important; background: rgba(0,0,0,0.01);">
                    <h6 class="text-dynamic fw-bold mb-0" style="font-size: 0.85rem;"><i class="bi bi-radar text-info me-2"></i>Fokus Sebaran Wilayah (7 Hari)</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary" style="font-size: 0.65rem;">Top 4 Kasus</span>
                </div>
                
                <div class="side-list-scroll p-3">
                    <?php if(count($insight_list) > 0): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php 
                            $icon_colors = ['text-danger bg-danger', 'text-warning bg-warning', 'text-primary bg-primary', 'text-info bg-info'];
                            foreach($insight_list as $index => $narasi): 
                                $c_text = explode(' ', $icon_colors[$index % 4])[0];
                                $c_bg = explode(' ', $icon_colors[$index % 4])[1];
                            ?>
                                <div class="insight-item">
                                    <div class="<?= $c_bg ?> bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 35px; height: 35px;">
                                        <i class="bi bi-geo-alt-fill <?= $c_text ?>"></i>
                                    </div>
                                    <div class="text-sub" style="font-size: 0.8rem; line-height: 1.5;">
                                        <?= $narasi ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column justify-content-center align-items-center h-100 text-sub">
                            <i class="bi bi-shield-check fs-2 opacity-50 mb-2 text-success"></i>
                            <span style="font-size: 0.8rem;">Wilayah aman, tidak ada sebaran kasus signifikan.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-5 col-lg-6">
            <div class="panel-card bottom-area">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-color: var(--border-color) !important;">
                    <h6 class="text-dynamic fw-bold mb-0" style="font-size: 0.85rem;"><i class="bi bi-bar-chart-steps text-primary me-2"></i>Top 5 Penyakit (7 Hari)</h6>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary" style="font-size: 0.65rem;">Suspek & Konfirmasi</span>
                </div>
                
                <div class="chart-container">
                    <?php if(count($data_penyakit) > 0): ?>
                        <canvas id="topPenyakitChart"></canvas>
                    <?php else: ?>
                        <div class="d-flex flex-column justify-content-center align-items-center h-100 text-sub">
                            <i class="bi bi-inbox fs-2 opacity-50 mb-2"></i>
                            <span style="font-size: 0.8rem;">Belum ada kasus dalam 7 hari terakhir</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT INISIALISASI PETA DASHBOARD -->
<script>
    var mapDash = L.map('mapDashboard', {
        minZoom: 13,
        zoomControl: true,
        scrollWheelZoom: false 
    }).setView([-7.567, 110.792], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(mapDash);

    const geojsonLaweyanDash = <?php echo $geojson_ready; ?>;

    function getColorDash(status) {
        return status === 'merah'  ? '#ef4444' :
               status === 'kuning' ? '#f59e0b' :
               status === 'hijau'  ? '#10b981' : '#334155';  
    }

    function stylePolygonDash(feature) {
        return {
            fillColor: getColorDash(feature.properties.status),
            weight: 2, 
            opacity: 0.8,
            color: 'rgba(255,255,255,0.8)',
            dashArray: '5',
            fillOpacity: 0.45 
        };
    }

    function onEachFeatureDash(feature, layer) {
        if (feature.properties) {
            let labelStatus = feature.properties.status === 'merah' ? 'POTENSI WABAH' : 
                              feature.properties.status === 'kuning' ? 'WASPADA' : 'AMAN';
            let colorStatus = getColorDash(feature.properties.status);

            let listPenyakitHTML = '';
            let dataPenyakit = feature.properties.detail_penyakit;

            if (dataPenyakit.length > 0) {
                dataPenyakit.forEach(function(item) {
                    listPenyakitHTML += `
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:12px; border-bottom:1px dashed #475569; padding-bottom:2px;">
                            <span style="color:var(--text-main); font-weight:600;">&bull; ${item.penyakit}</span>
                            <span style="color:var(--text-main); font-weight:800;">${item.jumlah}</span>
                        </div>
                    `;
                });
            } else {
                listPenyakitHTML = `<div style="text-align:center; color:var(--text-muted); font-size:11px; margin-top:10px;"><i>Belum ada kasus baru<br>dalam 7 hari terakhir.</i></div>`;
            }

            let popupContent = `
                <div style="min-width: 190px; font-family: 'Segoe UI', sans-serif;">
                    <h6 style="margin:0; font-weight:800; color:var(--text-main); font-size:1rem;">Kel. ${feature.properties.nama}</h6>
                    <hr style="margin:8px 0; border-color:var(--border-color);">
                    
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; align-items:center;">
                        <span style="color:var(--text-muted); font-size:11px; font-weight:600; text-transform:uppercase;">Status Zona</span>
                        <span style="background:${colorStatus}; color:white; padding:3px 8px; border-radius:6px; font-size:9px; font-weight:bold; letter-spacing:0.5px;">${labelStatus}</span>
                    </div>
                    
                    <div style="background:var(--bg-body); border-radius:6px; padding:8px; border: 1px solid var(--border-color);">
                        <div style="color:var(--text-muted); font-size:10px; font-weight:bold; margin-bottom:6px; text-transform:uppercase;">Top Penyakit Aktif</div>
                        ${listPenyakitHTML}
                    </div>
                    
                    <div style="display:flex; justify-content:space-between; margin-top:8px; padding:0 4px;">
                        <span style="color:var(--text-muted); font-size:11px; font-weight:600;">Total Kasus Aktif</span>
                        <span style="color:var(--text-main); font-size:12px; font-weight:900;">${feature.properties.kasus}</span>
                    </div>
                </div>
            `;
            layer.bindPopup(popupContent);
        }

        layer.on({
            mouseover: function(e) {
                var layer = e.target;
                layer.setStyle({ weight: 3, color: '#ffffff', fillOpacity: 0.8 });
                layer.bringToFront();
            },
            mouseout: function(e) {
                geojsonLayerDash.resetStyle(e.target);
            }
        });
    }

    var geojsonLayerDash = L.geoJSON(geojsonLaweyanDash, {
        style: stylePolygonDash,
        onEachFeature: onEachFeatureDash
    }).addTo(mapDash);

    mapDash.fitBounds(geojsonLayerDash.getBounds());
    mapDash.setMaxBounds(geojsonLayerDash.getBounds().pad(0.15));
</script>

<!-- SCRIPT INISIALISASI CHART.JS -->
<?php if(count($data_penyakit) > 0): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('topPenyakitChart').getContext('2d');
    const chartLabels = <?php echo json_encode($labels_penyakit); ?>;
    const chartData = <?php echo json_encode($data_penyakit); ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Jumlah Kasus',
                data: chartData,
                backgroundColor: 'rgba(13, 110, 253, 0.8)',
                borderColor: '#0d6efd',
                borderWidth: 1,
                borderRadius: 4,
                barPercentage: 0.6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: { size: 13 },
                    bodyFont: { size: 13, weight: 'bold' }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: '#888' },
                    grid: { color: 'rgba(150, 150, 150, 0.1)' }
                },
                y: {
                    ticks: { color: '#888', font: { size: 11, weight: '500' } },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<!-- ======================================================= -->
<!-- WIDGET CHATBOT EWS AI CO-PILOT                           -->
<!-- ======================================================= -->
<button id="ai-chat-toggle" onclick="toggleAiChat()" class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="position: fixed; bottom: 25px; right: 25px; width: 60px; height: 60px; z-index: 9999; border: 2px solid rgba(255,255,255,0.4); background: linear-gradient(135deg, #0d6efd, #0a58ca); transition: transform 0.2s ease;">
    <i class="bi bi-robot fs-2 text-white"></i>
</button>

<div id="ai-chat-box-wrapper" class="shadow-lg border-0" style="display: none; position: fixed; bottom: 95px; right: 25px; width: 370px; height: 500px; z-index: 9999; border-radius: 16px; overflow: hidden; flex-direction: column; background: var(--bg-panel); backdrop-filter: blur(10px); transition: all 0.3s ease;">
    
    <div class="p-3 text-white d-flex justify-content-between align-items-center flex-shrink-0" style="background: linear-gradient(135deg, #0d6efd 0%, #084298 100%);">
        <div class="d-flex align-items-center gap-2">
            <div class="position-relative">
                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                    <i class="bi bi-robot fs-5 text-primary"></i>
                </div>
                <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle" style="width: 10px; height: 10px;"></span>
            </div>
            <div>
                <h6 class="mb-0 fw-bold" style="font-size: 0.95rem; letter-spacing: 0.3px;">EWS AI Co-Pilot</h6>
                <small style="font-size: 0.68rem; opacity: 0.85;" class="d-flex align-items-center gap-1">
                    <span class="spinner-grow spinner-grow-sm" style="width: 6px; height: 6px;" role="status"></span> Active Assistant
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-1">
            <button type="button" class="btn btn-sm text-white opacity-75 p-0 me-1" onclick="clearAiChatHistory()" title="Hapus Riwayat Chat">
                <i class="bi bi-trash fs-6"></i>
            </button>
            <button type="button" class="btn-close btn-close-white opacity-75" onclick="toggleAiChat()"></button>
        </div>
    </div>

    <div class="p-2 border-bottom d-flex gap-1 overflow-auto text-nowrap flex-shrink-0" style="border-color: var(--border-color) !important; background: rgba(0,0,0,0.02); scrollbar-width: none;">
    <button type="button" onclick="sendQuickPrompt('Bagaimana ringkasan kasus hari ini?')" class="btn btn-outline-primary btn-sm rounded-pill py-1 px-2 style-chip" style="font-size: 0.7rem; flex-shrink: 0;">
        <i class="bi bi-activity me-1"></i> Kasus Hari Ini
    </button>
    <button type="button" onclick="sendQuickPrompt('Cetak laporan surveilans minggu ini')" class="btn btn-outline-primary btn-sm rounded-pill py-1 px-2 style-chip" style="font-size: 0.7rem; flex-shrink: 0;">
        <i class="bi bi-file-earmark-arrow-down me-1"></i> Cetak Laporan
    </button>
    <button type="button" onclick="sendQuickPrompt('Apakah ada klaster penularan lokal?')" class="btn btn-outline-primary btn-sm rounded-pill py-1 px-2 style-chip" style="font-size: 0.7rem; flex-shrink: 0;">
        <i class="bi bi-diagram-3 me-1"></i> Pelacak Klaster
    </button>
    <button type="button" onclick="sendQuickPrompt('Bikin draf surat laporan ke Dinkes')" class="btn btn-outline-primary btn-sm rounded-pill py-1 px-2 style-chip" style="font-size: 0.7rem; flex-shrink: 0;">
        <i class="bi bi-envelope-paper me-1"></i> Draf Surat
    </button>
    <button type="button" onclick="sendQuickPrompt('Berapa estimasi penurunan kasus jika dilakukan fogging?')" class="btn btn-outline-primary btn-sm rounded-pill py-1 px-2 style-chip" style="font-size: 0.7rem; flex-shrink: 0;">
        <i class="bi bi-sliders me-1"></i> Simulasi Fogging
    </button>
</div>

    <div id="ai-chat-messages" class="p-3 flex-grow-1 overflow-auto" style="font-size: 0.82rem; background: var(--bg-body); display: flex; flex-direction: column; gap: 10px;">
    </div>

    <div class="p-2 border-top d-flex gap-2 align-items-center flex-shrink-0" style="border-color: var(--border-color) !important; background: var(--bg-panel);">
        <input type="text" id="ai-chat-input" class="form-control form-control-sm rounded-pill border-0 bg-body px-3 py-2" placeholder="Tanyakan analisis data surveilans..." onkeypress="if(event.key==='Enter') sendAiMessage()" style="font-size: 0.8rem; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);">
        <button type="button" onclick="sendAiMessage()" class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
            <i class="bi bi-send-fill" style="font-size: 0.8rem;"></i>
        </button>
    </div>
</div>

<script>
const USER_NAME = "<?php echo htmlspecialchars($nama_lengkap); ?>";

document.addEventListener('DOMContentLoaded', function() {
    loadAiChatHistory();
});

function toggleAiChat() {
    const chatWrapper = document.getElementById('ai-chat-box-wrapper');
    const toggleBtn = document.getElementById('ai-chat-toggle');
    
    if (window.getComputedStyle(chatWrapper).display === 'none') {
        chatWrapper.style.display = 'flex';
        toggleBtn.style.transform = 'scale(0.9)';
    } else {
        chatWrapper.style.display = 'none';
        toggleBtn.style.transform = 'scale(1)';
    }
}

function saveAiChatHistory(sender, message, isError = false) {
    let history = JSON.parse(localStorage.getItem('ews_ai_chat_history')) || [];
    history.push({ sender: sender, message: message, isError: isError });
    localStorage.setItem('ews_ai_chat_history', JSON.stringify(history));
}

function loadAiChatHistory() {
    const msgBox = document.getElementById('ai-chat-messages');
    msgBox.innerHTML = '';

    msgBox.innerHTML += `
        <div class="d-flex gap-2 align-items-start">
            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                <i class="bi bi-robot text-primary" style="font-size: 0.85rem;"></i>
            </div>
            <div class="p-2.5 px-3 rounded-3 bg-panel border text-dynamic" style="max-width: 85%; border-color: var(--border-color) !important; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
                Halo <b>${USER_NAME}</b>! 👋<br>
                Saya siap membantu menganalisis data surveilans EWS Laweyan. Pilih menu cepat di atas atau ketik pertanyaan Anda!
            </div>
        </div>
    `;

    let history = JSON.parse(localStorage.getItem('ews_ai_chat_history')) || [];
    history.forEach(item => {
        if (item.sender === 'user') {
            msgBox.innerHTML += `
                <div class="d-flex gap-2 align-items-start justify-content-end">
                    <div class="p-2.5 px-3 rounded-3 bg-primary text-white" style="max-width: 82%; box-shadow: 0 2px 5px rgba(13,110,253,0.2);">
                        ${item.message}
                    </div>
                </div>
            `;
        } else if (item.sender === 'ai') {
            if (item.isError) {
                msgBox.innerHTML += `
                    <div class="d-flex gap-2 align-items-start">
                        <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="p-2.5 px-3 rounded-3 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20" style="max-width: 85%;">
                            ${item.message}
                        </div>
                    </div>
                `;
            } else {
                msgBox.innerHTML += `
                    <div class="d-flex gap-2 align-items-start">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                            <i class="bi bi-robot text-primary" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="p-2.5 px-3 rounded-3 bg-panel border text-dynamic" style="max-width: 85%; border-color: var(--border-color) !important; box-shadow: 0 2px 5px rgba(0,0,0,0.03); line-height: 1.45;">
                            ${item.message.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                `;
            }
        }
    });
    msgBox.scrollTop = msgBox.scrollHeight;
}

function clearAiChatHistory() {
    if (confirm("Apakah Anda yakin ingin menghapus seluruh riwayat percakapan AI?")) {
        localStorage.removeItem('ews_ai_chat_history');
        loadAiChatHistory();
    }
}

function sendQuickPrompt(promptText) {
    document.getElementById('ai-chat-input').value = promptText;
    sendAiMessage();
}

function sendAiMessage() {
    const input = document.getElementById('ai-chat-input');
    const msgBox = document.getElementById('ai-chat-messages');
    const text = input.value.trim();

    if (!text) return;

    msgBox.innerHTML += `
        <div class="d-flex gap-2 align-items-start justify-content-end">
            <div class="p-2.5 px-3 rounded-3 bg-primary text-white" style="max-width: 82%; box-shadow: 0 2px 5px rgba(13,110,253,0.2);">
                ${text}
            </div>
        </div>
    `;
    saveAiChatHistory('user', text);
    input.value = '';
    msgBox.scrollTop = msgBox.scrollHeight;

    const loadId = 'loading-' + Date.now();
    msgBox.innerHTML += `
        <div id="${loadId}" class="d-flex gap-2 align-items-start">
            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                <i class="bi bi-robot text-primary" style="font-size: 0.85rem;"></i>
            </div>
            <div class="p-2 px-3 rounded-3 bg-panel border text-muted fst-italic" style="font-size: 0.78rem; border-color: var(--border-color) !important;">
                <span class="spinner-border spinner-border-sm me-1 text-primary" style="width:0.7rem; height:0.7rem;"></span> Menganalisis data...
            </div>
        </div>
    `;
    msgBox.scrollTop = msgBox.scrollHeight;

    fetch('ai_assistant.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById(loadId).remove();
        if (data.status === 'success') {
            msgBox.innerHTML += `
                <div class="d-flex gap-2 align-items-start">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                        <i class="bi bi-robot text-primary" style="font-size: 0.85rem;"></i>
                    </div>
                    <div class="p-2.5 px-3 rounded-3 bg-panel border text-dynamic" style="max-width: 85%; border-color: var(--border-color) !important; box-shadow: 0 2px 5px rgba(0,0,0,0.03); line-height: 1.45;">
                        ${data.reply.replace(/\n/g, '<br>')}
                    </div>
                </div>
            `;
            saveAiChatHistory('ai', data.reply, false);
        } else {
            msgBox.innerHTML += `
                <div class="d-flex gap-2 align-items-start">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 0.85rem;"></i>
                    </div>
                    <div class="p-2.5 px-3 rounded-3 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20" style="max-width: 85%;">
                        ${data.reply}
                    </div>
                </div>
            `;
            saveAiChatHistory('ai', data.reply, true);
        }
        msgBox.scrollTop = msgBox.scrollHeight;
    })
    .catch(() => {
        document.getElementById(loadId).remove();
        const errText = "Gagal terhubung ke server backend AI.";
        msgBox.innerHTML += `
            <div class="d-flex gap-2 align-items-start">
                <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                    <i class="bi bi-wifi-off text-danger" style="font-size: 0.85rem;"></i>
                </div>
                <div class="p-2.5 px-3 rounded-3 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20" style="max-width: 85%;">
                    ${errText}
                </div>
            </div>
        `;
        saveAiChatHistory('ai', errText, true);
        msgBox.scrollTop = msgBox.scrollHeight;
    });
}
</script>

<?php include '../../layout/footer.php'; ?>