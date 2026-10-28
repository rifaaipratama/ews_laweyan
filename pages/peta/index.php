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

// =========================================================================
// 1. LOGIKA STATUS WARNA KELURAHAN REAL-TIME (MURNI DARI TABEL_KASUS)
// =========================================================================
$status_wilayah = [];
$total_merah = 0;
$total_kuning = 0;

// Menghitung jumlah kasus AKTIF (Suspek & Terkonfirmasi) per Kelurahan murni dari tabel_kasus
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
    // >= 3 kasus aktif -> MERAH (Potensi Wabah)
    // 1 - 2 kasus aktif -> KUNING (Waspada)
    // 0 kasus aktif -> HIJAU (Aman)
    if ($jml_aktif >= 3) {
        $status_wilayah[$nama] = 'merah';
    } elseif ($jml_aktif >= 1) {
        $status_wilayah[$nama] = 'kuning';
    } else {
        $status_wilayah[$nama] = 'hijau';
    }
}

// Menghitung total wilayah untuk legenda hidup
foreach ($status_wilayah as $status) {
    if ($status == 'merah') $total_merah++;
    if ($status == 'kuning') $total_kuning++;
}
$total_hijau = 11 - ($total_merah + $total_kuning);

// =========================================================================
// 2. LOGIKA TOP 3 PENYAKIT PER KELURAHAN & TOP 3 WILAYAH RENTAN
// =========================================================================
$top_penyakit = [];
$wilayah_rentan = [];

$q_kasus = mysqli_query($koneksi, "
    SELECT k.nama_kelurahan, p.nama_populer, COUNT(kas.id_kasus) as jml
    FROM tabel_kasus kas
    JOIN tabel_kelurahan k ON kas.id_kelurahan = k.id_kelurahan
    JOIN tabel_penyakit p ON kas.id_penyakit = p.id_penyakit
    WHERE kas.is_deleted = 0 
      AND kas.status_kasus IN ('suspek', 'terkonfirmasi') 
    GROUP BY kas.id_kelurahan, kas.id_penyakit
    ORDER BY k.nama_kelurahan ASC, jml DESC
");

while ($row = mysqli_fetch_assoc($q_kasus)) {
    $nama = $row['nama_kelurahan'];
    if (!isset($top_penyakit[$nama])) $top_penyakit[$nama] = [];
    
    if (count($top_penyakit[$nama]) < 3) {
        $top_penyakit[$nama][] = [
            'penyakit' => $row['nama_populer'], 
            'jumlah' => $row['jml']
        ];
    }
}

// Query khusus untuk TOP 3 Kelurahan dengan Kasus Terbanyak Global
$q_top_wilayah = mysqli_query($koneksi, "
    SELECT k.nama_kelurahan, COUNT(kas.id_kasus) as total_kasus
    FROM tabel_kasus kas
    JOIN tabel_kelurahan k ON kas.id_kelurahan = k.id_kelurahan
    WHERE kas.is_deleted = 0 
      AND kas.status_kasus IN ('suspek', 'terkonfirmasi') 
    GROUP BY kas.id_kelurahan
    ORDER BY total_kasus DESC
    LIMIT 3
");
while ($tw = mysqli_fetch_assoc($q_top_wilayah)) {
    $wilayah_rentan[] = $tw;
}

// =========================================================================
// 3. INJEKSI DATA DINAMIS KE GEOJSON (KOORDINAT LAWEYAN SUDAH DIPERBAIKI)
// =========================================================================
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
    $feature['properties']['detail_penyakit'] = isset($top_penyakit[$nama_kel]) ? $top_penyakit[$nama_kel] : [];
    
    $total_kasus = 0;
    if (isset($top_penyakit[$nama_kel])) {
        foreach ($top_penyakit[$nama_kel] as $tp) $total_kasus += $tp['jumlah'];
    }
    $feature['properties']['kasus'] = $total_kasus;
}
$geojson_ready = json_encode($geo_data);

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    .map-container {
        height: calc(100vh - 275px);
        min-height: 400px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background-color: var(--bg-panel);
    }
    
    [data-theme="dark"] .leaflet-layer,
    [data-theme="dark"] .leaflet-control-zoom-in,
    [data-theme="dark"] .leaflet-control-zoom-out {
        filter: brightness(0.8) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3) brightness(0.7);
    }

    .panel-card {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 4px 20px var(--shadow-color);
        transition: background-color 0.3s, border-color 0.3s;
    }

    .text-sub { color: var(--text-muted) !important; }

    .leaflet-popup-content-wrapper { background-color: var(--bg-panel); color: var(--text-main); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .leaflet-popup-tip { background-color: var(--bg-panel); }

    .legend-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed var(--border-color); }
    .legend-item:last-child { border-bottom: none; padding-bottom: 0; }
    
    .status-dot { width: 16px; height: 16px; border-radius: 50%; box-shadow: 0 0 10px currentColor; flex-shrink: 0; }
    .dot-red { color: #dc3545; background-color: #dc3545; }
    .dot-yellow { color: #ffc107; background-color: #ffc107; }
    .dot-green { color: #198754; background-color: #198754; }

    .leaderboard-item { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        background: rgba(13, 110, 253, 0.04); 
        padding: 12px 15px; 
        border-radius: 8px; 
        margin-bottom: 8px; 
        transition: transform 0.2s; 
        border: 1px solid rgba(13, 110, 253, 0.1); 
    }
    .leaderboard-item:hover { transform: translateX(4px); background: rgba(13, 110, 253, 0.08); }
    [data-theme="dark"] .leaderboard-item { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.05); }
    [data-theme="dark"] .leaderboard-item:hover { background: rgba(255,255,255,0.06); }

    .alert-info-map {
        background-color: rgba(13, 110, 253, 0.05);
        border: 1px solid rgba(13, 110, 253, 0.15);
        transition: all 0.3s ease;
    }
    [data-theme="dark"] .alert-info-map {
        background-color: rgba(13, 110, 253, 0.1);
        border-color: rgba(13, 110, 253, 0.2);
    }
</style>

<div id="main-content">
    <?php include '../../layout/topbar.php'; ?>

    <div class="row g-3">
        <!-- Kolom Peta Utama -->
        <div class="col-lg-8 col-xl-9">
            <div class="panel-card p-3 h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-dynamic mb-0 fw-bold"><i class="bi bi-geo-alt-fill text-danger me-2"></i> Peta Interaktif Sebaran Kasus Laweyan</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-pill shadow-sm d-none d-md-inline-block"><i class="bi bi-clock-history me-1"></i> Data Real-Time Active Cases</span>
                </div>
                
                <!-- Kontainer Peta -->
                <div id="map" class="map-container shadow-sm mb-3"></div>

                <!-- Informasi Tambahan Bawah Peta -->
                <div class="alert-info-map p-3 rounded-3 d-flex gap-3 align-items-start mt-auto">
                    <i class="bi bi-info-circle-fill text-primary fs-4 mt-1"></i>
                    <div>
                        <h6 class="fw-bold text-dynamic mb-1" style="font-size: 0.9rem;">Informasi Peta Sebaran</h6>
                        <p class="text-sub mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Peta ini terintegrasi langsung dengan <b>Data Kasus Aktif</b>. Perubahan warna zona kerawanan (Hijau, Kuning, Merah) dikalkulasi secara otomatis berdasarkan total jumlah kasus aktif (Suspek & Terkonfirmasi) di setiap kelurahan.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Panel Kontrol & Insight Kanan -->
        <div class="col-lg-4 col-xl-3 d-flex flex-column gap-3">
            
            <!-- Card Legenda (Live Data) -->
            <div class="panel-card p-4">
                <h6 class="text-dynamic fw-bold mb-3 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-broadcast me-2 text-primary"></i> Status Wilayah</span>
                </h6>
                
                <div class="legend-item">
                    <div class="d-flex align-items-center">
                        <div class="status-dot dot-red me-3"></div>
                        <div>
                            <div class="text-dynamic fw-bold" style="font-size: 0.85rem;">Zona Merah</div>
                            <div class="text-sub" style="font-size: 0.7rem;">Potensi Wabah</div>
                        </div>
                    </div>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3"><?= $total_merah ?> Wilayah</span>
                </div>
                
                <div class="legend-item">
                    <div class="d-flex align-items-center">
                        <div class="status-dot dot-yellow me-3"></div>
                        <div>
                            <div class="text-dynamic fw-bold" style="font-size: 0.85rem;">Waspada</div>
                            <div class="text-sub" style="font-size: 0.7rem;">Lonjakan Kasus</div>
                        </div>
                    </div>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill px-3"><?= $total_kuning ?> Wilayah</span>
                </div>
                
                <div class="legend-item">
                    <div class="d-flex align-items-center">
                        <div class="status-dot dot-green me-3"></div>
                        <div>
                            <div class="text-dynamic fw-bold" style="font-size: 0.85rem;">Aman</div>
                            <div class="text-sub" style="font-size: 0.7rem;">Terkendali</div>
                        </div>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3"><?= $total_hijau ?> Wilayah</span>
                </div>
            </div>

            <!-- Card Top Wilayah Rentan -->
            <div class="panel-card p-4 flex-grow-1">
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-1">
                        <i class="bi bi-graph-up-arrow text-danger me-2 fs-5"></i>
                        <h6 class="text-dynamic fw-bold mb-0" style="font-size: 0.95rem;">Wilayah Teratas</h6>
                    </div>
                    <small class="text-sub ms-4 d-block" style="font-size: 0.75rem;">Top 3 Kasus Aktif Terbanyak</small>
                </div>
                
                <?php if (count($wilayah_rentan) > 0): ?>
                    <div class="d-flex flex-column">
                        <?php 
                        $medals = ['bi-1-circle-fill text-warning', 'bi-2-circle-fill text-secondary', 'bi-3-circle-fill text-primary'];
                        foreach ($wilayah_rentan as $index => $w): 
                        ?>
                            <div class="leaderboard-item">
                                <div class="d-flex align-items-center gap-2" style="min-width: 0;">
                                    <i class="bi <?= $medals[$index] ?> fs-5 flex-shrink-0"></i>
                                    <div class="text-truncate">
                                        <div class="fw-bold text-dynamic text-truncate" style="font-size: 0.85rem;">Kel. <?= $w['nama_kelurahan'] ?></div>
                                    </div>
                                </div>
                                <div class="fw-bold text-danger flex-shrink-0 ms-2" style="font-size: 0.85rem;"><?= $w['total_kasus'] ?> Kasus</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center mt-4 opacity-50">
                        <i class="bi bi-shield-check text-success fs-1"></i>
                        <p class="text-sub mt-2 small">Seluruh wilayah Laweyan<br>saat ini bebas kasus.</p>
                    </div>
                <?php endif; ?>
                
                <div class="mt-3 pt-3 border-top text-center" style="border-color: var(--border-color) !important;">
                    <small class="text-sub" style="font-size: 0.7rem;">*Klik area pada peta untuk melihat penyakit spesifik.</small>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    var map = L.map('map', {
        minZoom: 13,
        zoomControl: true
    }).setView([-7.567, 110.792], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const geojsonLaweyan = <?php echo $geojson_ready; ?>;

    function getColor(status) {
        return status === 'merah'  ? '#ef4444' :
               status === 'kuning' ? '#f59e0b' :
               status === 'hijau'  ? '#10b981' : '#334155';  
    }

    function stylePolygon(feature) {
        return {
            fillColor: getColor(feature.properties.status),
            weight: 2, 
            opacity: 0.8,
            color: 'rgba(255,255,255,0.8)',
            dashArray: '5',
            fillOpacity: 0.45 
        };
    }

    function onEachFeature(feature, layer) {
        if (feature.properties) {
            let labelStatus = feature.properties.status === 'merah' ? 'POTENSI WABAH' : 
                              feature.properties.status === 'kuning' ? 'WASPADA' : 'AMAN';
            let colorStatus = getColor(feature.properties.status);

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
                geojsonLayer.resetStyle(e.target);
            }
        });
    }

    var geojsonLayer = L.geoJSON(geojsonLaweyan, {
        style: stylePolygon,
        onEachFeature: onEachFeature
    }).addTo(map);

    map.fitBounds(geojsonLayer.getBounds());
    map.setMaxBounds(geojsonLayer.getBounds().pad(0.15));
</script>

<?php include '../../layout/footer.php'; ?>