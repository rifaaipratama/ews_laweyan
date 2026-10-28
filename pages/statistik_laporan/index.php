<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

$id_role      = $_SESSION['id_role'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$query_role   = mysqli_query($koneksi, "SELECT nama_role FROM tabel_role WHERE id_role = '$id_role'");
$nama_role    = ($row = mysqli_fetch_assoc($query_role)) ? $row['nama_role'] : "User";

// --- 1. QUERY SUMMARY CARDS ---
// Total Pasien Unik (Yang belum dihapus/soft delete)
$q_pasien = mysqli_query($koneksi, "SELECT COUNT(DISTINCT nik_pasien) as total FROM tabel_kasus WHERE is_deleted = 0");
$tot_pasien = mysqli_fetch_assoc($q_pasien)['total'] ?? 0;

// Total Kelurahan
$q_kelurahan = mysqli_query($koneksi, "SELECT COUNT(id_kelurahan) as total FROM tabel_kelurahan");
$tot_kelurahan = mysqli_fetch_assoc($q_kelurahan)['total'] ?? 0;

// Total Penyakit
$q_penyakit = mysqli_query($koneksi, "SELECT COUNT(id_penyakit) as total FROM tabel_penyakit");
$tot_penyakit = mysqli_fetch_assoc($q_penyakit)['total'] ?? 0;

// Total Pengguna
$q_pengguna = mysqli_query($koneksi, "SELECT COUNT(id_pengguna) as total FROM tabel_pengguna");
$tot_pengguna = mysqli_fetch_assoc($q_pengguna)['total'] ?? 0;


// --- 2. QUERY DONUT CHART (Rasio Status Kasus) ---
$q_status = mysqli_query($koneksi, "
    SELECT status_kasus, COUNT(id_kasus) as jumlah 
    FROM tabel_kasus 
    WHERE is_deleted = 0 
    GROUP BY status_kasus
");
$data_status = ['suspek' => 0, 'terkonfirmasi' => 0, 'sembuh' => 0, 'meninggal' => 0];
while($s = mysqli_fetch_assoc($q_status)){
    $data_status[$s['status_kasus']] = (int)$s['jumlah'];
}


// --- 3. QUERY LINE CHART (Data Interaktif Tooltip) ---
// Mengambil semua data kasus, lalu dikelompokkan berdasarkan tanggal di JavaScript
$q_chart = mysqli_query($koneksi, "
    SELECT 
        k.tanggal_lapor, 
        k.nama_pasien, 
        p.nama_populer as penyakit, 
        kel.nama_kelurahan, 
        k.status_kasus
    FROM tabel_kasus k
    JOIN tabel_penyakit p ON k.id_penyakit = p.id_penyakit
    JOIN tabel_kelurahan kel ON k.id_kelurahan = kel.id_kelurahan
    WHERE k.is_deleted = 0
    ORDER BY k.tanggal_lapor ASC
");

$raw_chart_data = [];
while ($r = mysqli_fetch_assoc($q_chart)) {
    $tgl = $r['tanggal_lapor'];
    if (!isset($raw_chart_data[$tgl])) {
        $raw_chart_data[$tgl] = [];
    }
    // Masukkan detail ke dalam array tanggal tersebut untuk ditampilkan di Tooltip Pop-up
    $raw_chart_data[$tgl][] = [
        'nama' => $r['nama_pasien'],
        'penyakit' => $r['penyakit'],
        'lokasi' => $r['nama_kelurahan'],
        'status' => ucfirst($r['status_kasus'])
    ];
}


$title = "Statistik & Pelaporan";
include '../../layout/header.php';
include '../../layout/sidebar.php';
?>

<!-- Include CDN ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    .panel-card {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }
    
    /* Hover Effect untuk 4 Card Atas + h-100 untuk menyamakan ukuran */
    .stat-card {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        cursor: pointer;
        text-decoration: none;
        color: inherit;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(13, 110, 253, 0.15);
        border-color: #0d6efd;
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        flex-shrink: 0;
    }

    /* Toggle Button Group (Kapsul) */
    .btn-group-toggle .btn {
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 6px 18px;
        border: 1px solid var(--border-color);
        color: var(--text-color);
        background-color: var(--bg-body);
        transition: all 0.2s ease;
    }
    .btn-group-toggle .btn.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
    }
    .btn-group-toggle .btn:hover:not(.active) {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    /* Kustomisasi Tooltip ApexCharts untuk Tema Gelap/Terang */
    .apexcharts-tooltip {
        background: var(--bg-panel) !important;
        border: 1px solid var(--border-color) !important;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
        color: var(--text-color) !important;
    }
    .apexcharts-tooltip-title {
        background: var(--bg-body) !important;
        border-bottom: 1px solid var(--border-color) !important;
        font-weight: bold;
    }
</style>

<div id="main-content">
    <?php include '../../layout/topbar.php'; ?>

    <!-- HEADER & EXPORT BUTTON -->
    <div class="row mb-4 align-items-center mt-2">
        <div class="col-md-7">
            <h4 class="text-dynamic fw-bold mb-1">
                <i class="bi bi-bar-chart-fill me-2 text-primary"></i> Statistik & Pelaporan EWS
            </h4>
            <p class="text-dynamic mb-0" style="opacity: 0.75; font-size: 0.9rem;">
                Analisis tren lonjakan kasus wabah dan sebaran zona merah di Kecamatan Laweyan.
            </p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <button class="btn btn-outline-danger rounded-pill shadow-sm px-4 fw-medium me-2" onclick="alert('Fitur Cetak PDF akan segera hadir!')">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Cetak PDF
            </button>
            <button class="btn btn-outline-success rounded-pill shadow-sm px-4 fw-medium" onclick="alert('Fitur Export Excel akan segera hadir!')">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
            </button>
        </div>
    </div>

    <!-- 4 SUMMARY CARDS (Ukurannya kini dijamin sama dengan h-100) -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <a href="../data_riwayat_pasien/index.php" class="panel-card stat-card p-3 h-100 d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 fw-semibold text-uppercase" style="font-size:0.75rem; letter-spacing: 0.5px;">Total Pasien</p>
                    <h3 class="fw-bold mb-0 text-dynamic"><?= number_format($tot_pasien); ?></h3>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="../kelurahan/index.php" class="panel-card stat-card p-3 h-100 d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-pin-map-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 fw-semibold text-uppercase" style="font-size:0.75rem; letter-spacing: 0.5px;">Wilayah Pantauan</p>
                    <h3 class="fw-bold mb-0 text-dynamic"><?= number_format($tot_kelurahan); ?></h3>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="../data_penyakit/index.php" class="panel-card stat-card p-3 h-100 d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-virus"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 fw-semibold text-uppercase" style="font-size:0.75rem; letter-spacing: 0.5px;">Master Penyakit</p>
                    <h3 class="fw-bold mb-0 text-dynamic"><?= number_format($tot_penyakit); ?></h3>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="../pengguna/index.php" class="panel-card stat-card p-3 h-100 d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 fw-semibold text-uppercase" style="font-size:0.75rem; letter-spacing: 0.5px;">Total Petugas</p>
                    <h3 class="fw-bold mb-0 text-dynamic"><?= number_format($tot_pengguna); ?></h3>
                </div>
            </a>
        </div>
    </div>

    <!-- MAIN CHART (DIAGRAM GARIS HIDUP) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="panel-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <h5 class="fw-bold mb-0 text-dynamic"><i class="bi bi-graph-up-arrow text-primary me-2"></i> Tren Lonjakan Kasus EWS</h5>
                    
                    <!-- Toggle Kapsul Periode -->
                    <div class="btn-group btn-group-toggle" role="group">
                        <button type="button" class="btn" id="btn7Hari">7 Hari</button>
                        <button type="button" class="btn active" id="btn1Bulan">1 Bulan</button>
                        <button type="button" class="btn" id="btn1Tahun">1 Tahun</button>
                    </div>
                </div>
                
                <!-- Tempat Grafik Muncul -->
                <div id="mainLineChart" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>

    <!-- BOTTOM SECTION (DONUT & ZONA MERAH) -->
    <div class="row g-4 mb-4">
        <!-- DONUT CHART -->
        <div class="col-md-5">
            <div class="panel-card p-4 h-100">
                <h6 class="fw-bold text-dynamic mb-4"><i class="bi bi-pie-chart-fill text-primary me-2"></i> Rasio Status Kasus</h6>
                <div id="donutChartStatus" class="d-flex justify-content-center"></div>
            </div>
        </div>
        
        <!-- TABEL TOP 5 KELURAHAN (ZONA MERAH) -->
        <div class="col-md-7">
            <div class="panel-card p-4 h-100">
                <h6 class="fw-bold text-dynamic mb-3"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Top 5 Zona Merah (Kasus Terbanyak)</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-color);">
                        <thead class="text-muted" style="font-size: 0.85rem; border-bottom: 2px solid var(--border-color);">
                            <tr>
                                <th width="15%">Ranking</th>
                                <th width="40%">Kelurahan</th>
                                <th width="25%" class="text-center">Total Kasus</th>
                                <th width="20%">Kondisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_top_zona = mysqli_query($koneksi, "
                                SELECT kel.nama_kelurahan, COUNT(k.id_kasus) as total_kasus 
                                FROM tabel_kasus k
                                JOIN tabel_kelurahan kel ON k.id_kelurahan = kel.id_kelurahan
                                WHERE k.is_deleted = 0
                                GROUP BY k.id_kelurahan
                                ORDER BY total_kasus DESC
                                LIMIT 5
                            ");
                            
                            $rank = 1;
                            if(mysqli_num_rows($q_top_zona) > 0) {
                                while($z = mysqli_fetch_assoc($q_top_zona)) {
                                    $badge_color = ($rank <= 2) ? 'bg-danger' : 'bg-warning text-dark';
                                    $kondisi = ($rank <= 2) ? 'Kritis' : 'Waspada';
                                    echo "<tr>";
                                    echo "<td><span class='badge bg-secondary rounded-circle px-2 py-1 shadow-sm'>#$rank</span></td>";
                                    echo "<td class='fw-bold' style='color: #0d6efd;'>{$z['nama_kelurahan']}</td>";
                                    echo "<td class='text-center fw-bold fs-5'>{$z['total_kasus']}</td>";
                                    echo "<td><span class='badge {$badge_color} rounded-pill px-3 py-2 shadow-sm'>{$kondisi}</span></td>";
                                    echo "</tr>";
                                    $rank++;
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center text-muted py-4'>Belum ada data kasus di wilayah manapun.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // === 1. DATA DATABASE UNTUK GRAFIK GARIS (Di-parsing dari PHP) ===
    const rawDbData = <?= json_encode($raw_chart_data); ?>;
    
    // Konversi objek JSON menjadi Array sesuai format grafik X (Waktu) dan Y (Jumlah)
    let allChartData = [];
    for (const [dateStr, cases] of Object.entries(rawDbData)) {
        allChartData.push({
            x: new Date(dateStr).getTime(), // Mengubah tanggal ke format timestamp millisecond
            y: cases.length,                // Jumlah kasus pada hari itu
            details: cases                  // Array detail untuk Tooltip Pop-up
        });
    }
    
    // Urutkan array berdasarkan tanggal dari paling lama ke terbaru
    allChartData.sort((a, b) => a.x - b.x);

    let lineChart; // Variabel penampung instance grafik garis
    
    // Fungsi Utama Render Grafik Berdasarkan Hari
    function renderLineChart(days) {
        let filteredData = allChartData;
        
        // Logika Filter Rentang Waktu
        if (days !== 'all') {
            const today = new Date().getTime();
            const cutoff = today - (days * 24 * 60 * 60 * 1000); // Batas waktu mundur
            filteredData = allChartData.filter(d => d.x >= cutoff);
        }

        var lineOptions = {
            series: [{
                name: "Total Kasus Baru",
                data: filteredData
            }],
            chart: {
                height: 350,
                type: 'area', // Area memberikan gradasi elegan di bawah garis
                toolbar: { show: false },
                fontFamily: 'inherit',
                zoom: { enabled: false },
                background: 'transparent'
            },
            colors: ['#0d6efd'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 }, // Garis melengkung
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.0,
                    stops: [0, 100]
                }
            },
            xaxis: {
                type: 'datetime', // Otomatis mengatur jarak titik berdasarkan tanggal
                labels: {
                    datetimeUTC: false,
                    format: 'dd MMM yyyy',
                    style: { colors: '#888' }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { colors: '#888' },
                    formatter: function(val) { return Math.round(val); } // Bulatkan angka kasus
                }
            },
            // KUSTOMISASI TOOLTIP (POP-UP)
            tooltip: {
                custom: function({series, seriesIndex, dataPointIndex, w}) {
                    let data = w.globals.initialSeries[seriesIndex].data[dataPointIndex];
                    let dateStr = new Date(data.x).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});
                    
                    let detailsHtml = '';
                    data.details.forEach(d => {
                        let badgeColor = '#6c757d';
                        if (d.status === 'Terkonfirmasi') badgeColor = '#dc3545';
                        if (d.status === 'Suspek') badgeColor = '#ffc107';
                        if (d.status === 'Sembuh') badgeColor = '#198754';
                        if (d.status === 'Meninggal') badgeColor = '#212529';

                        detailsHtml += `
                            <div style="font-size: 0.8rem; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px; margin-bottom: 8px;">
                                <div style="color: #0d6efd; font-weight: bold; font-size: 0.9rem;">${d.penyakit}</div>
                                <div style="color: var(--text-color); margin-top: 2px;">
                                    <i class="bi bi-geo-alt-fill text-muted me-1"></i> Kelurahan ${d.lokasi}
                                </div>
                                <div style="color: var(--text-color); margin-top: 2px;">
                                    <i class="bi bi-person-fill text-muted me-1"></i> ${d.nama} 
                                    <span style="color: ${badgeColor}; font-weight: bold; font-size: 0.7rem; margin-left: 5px;">(${d.status})</span>
                                </div>
                            </div>
                        `;
                    });
                    
                    return `
                        <div class="p-3 shadow-lg" style="min-width: 260px; max-height: 300px; overflow-y: auto; background-color: var(--bg-panel); border: 1px solid var(--border-color); border-radius: 12px;">
                            <div class="fw-bold mb-3 border-bottom pb-2 d-flex justify-content-between align-items-center" style="font-size: 0.9rem; color: var(--text-color);">
                                <span><i class="bi bi-calendar2-event me-2 text-primary"></i>${dateStr}</span>
                                <span class="badge bg-primary rounded-pill">${data.y} Kasus</span>
                            </div>
                            ${detailsHtml}
                        </div>
                    `;
                }
            }
        };

        // Hancurkan grafik lama (jika ada) lalu render yang baru untuk efek mulus
        if (lineChart) lineChart.destroy();
        lineChart = new ApexCharts(document.querySelector("#mainLineChart"), lineOptions);
        lineChart.render();
    }

    // Default Load: Grafik menampilkan data 1 Bulan terakhir (30 Hari)
    renderLineChart(30);

    // Event Listener untuk Tombol Filter (Kapsul)
    document.getElementById('btn7Hari').addEventListener('click', function() {
        document.querySelectorAll('.btn-group-toggle .btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        renderLineChart(7);
    });
    
    document.getElementById('btn1Bulan').addEventListener('click', function() {
        document.querySelectorAll('.btn-group-toggle .btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        renderLineChart(30);
    });
    
    document.getElementById('btn1Tahun').addEventListener('click', function() {
        document.querySelectorAll('.btn-group-toggle .btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        renderLineChart(365);
    });


    // === 2. KONFIGURASI GRAFIK DONUT (Status Kasus) ===
    var donutOptions = {
        series: [
            <?= $data_status['suspek']; ?>, 
            <?= $data_status['terkonfirmasi']; ?>, 
            <?= $data_status['sembuh']; ?>, 
            <?= $data_status['meninggal']; ?>
        ],
        chart: { type: 'donut', height: 320, fontFamily: 'inherit', background: 'transparent' },
        labels: ['Suspek', 'Terkonfirmasi', 'Sembuh', 'Meninggal'],
        colors: ['#ffc107', '#dc3545', '#198754', '#212529'],
        plotOptions: {
            pie: { donut: { size: '75%' } }
        },
        stroke: { show: false },
        dataLabels: { enabled: false },
        legend: { 
            position: 'bottom',
            labels: { colors: 'var(--text-color)' }
        }
    };
    var donutChart = new ApexCharts(document.querySelector("#donutChartStatus"), donutOptions);
    donutChart.render();
</script>

<?php include '../../layout/footer.php'; ?>