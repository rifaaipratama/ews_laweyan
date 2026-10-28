<?php
// FUNGSI UNTUK MEMANGGIL ARTIKEL BERITA KESEHATAN REAL-TIME DARI ANTARA / MEDIA RESMI VIA RSS (TANPA PERLU EDIT MANUAL)
function get_realtime_health_news() {
    $rss_url = "https://www.antaranews.com/rss/warta-bumi.xml"; 
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $rss_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    $response = curl_exec($ch);
    curl_close($ch);

    $articles = [];
    if ($response) {
        $xml = @simplexml_load_string($response);
        if ($xml && isset($xml->channel->item)) {
            $count = 0;
            foreach ($xml->channel->item as $item) {
                if ($count >= 3) break;
                
                $image = "https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=500&q=80";
                if (isset($item->enclosure['url'])) {
                    $image = (string)$item->enclosure['url'];
                }

                $articles[] = [
                    'title'       => (string)$item->title,
                    'description' => strip_tags((string)$item->description),
                    'link'        => (string)$item->link,
                    'pubDate'     => date('d M Y', strtotime((string)$item->pubDate)),
                    'source'      => 'Antara News',
                    'image'       => $image
                ];
                $count++;
            }
        }
    }

    if (empty($articles)) {
        $articles = [
            [
                'title' => 'Kewaspadaan Dini Demam Berdarah dan Penyakit Menular Musim Pancaroba',
                'description' => 'Dinas Kesehatan mengimbau masyarakat menggalakkan Gerakan 3M Plus guna menekan pengembangbiakan jentik nyamuk Aedes aegypti.',
                'link' => 'https://www.kompas.com',
                'pubDate' => date('d M Y'),
                'source' => 'Kompas Health',
                'image' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?w=500&q=80'
            ],
            [
                'title' => 'Pencegahan Penyakit Menular Berbasis Lingkungan Pemukiman Perkotaan',
                'description' => 'Sanitasi lingkungan dan pemantauan drainase menjadi faktor kunci pencegahan penularan penyakit berbasis lingkungan.',
                'link' => 'https://health.detik.com',
                'pubDate' => date('d M Y'),
                'source' => 'detikHealth',
                'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=500&q=80'
            ],
            [
                'title' => 'Transformasi Digital Integrasi Data Surveilans Kesehatan Daerah',
                'description' => 'Pemanfaatan sistem informasi berbasis spasial mempercepat deteksi dini dan intervensi respon cepat di tingkat kecamatan.',
                'link' => 'https://kemkes.go.id',
                'pubDate' => date('d M Y'),
                'source' => 'Kemenkes RI',
                'image' => 'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?w=500&q=80'
            ]
        ];
    }
    return $articles;
}

$berita_list = get_realtime_health_news();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMART EWS — Sistem Early Warning System Kecamatan Laweyan</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary: #0d6efd;
            --primary-dark: #0a58ca;
            --dark: #0f172a;
            --dark-surface: #1e293b;
            --text-main: #334155;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-main);
            background-color: #ffffff;
            overflow-x: hidden;
        }

        .navbar-custom {
            background: rgba(15, 23, 42, 0.94);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.85rem 0;
        }

        .navbar-brand span {
            font-weight: 800;
            letter-spacing: -0.5px;
            font-size: 1.25rem;
        }

        .nav-link {
            font-weight: 500;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.75) !important;
            transition: color 0.2s;
        }
        .nav-link:hover { color: #ffffff !important; }

        .hero-section {
            background: radial-gradient(circle at 10% 20%, rgba(13, 110, 253, 0.18) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(15, 23, 42, 0.98) 0%, #0f172a 100%);
            color: #ffffff;
            padding: 130px 0 90px;
            position: relative;
        }

        /* Container Logo Institusi & Lomba */
        .brand-logos-wrapper {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            padding: 0.4rem 1.2rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            margin-bottom: 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        .brand-logos-wrapper img {
            height: 38px;
            width: auto;
            object-fit: contain;
        }

        .hero-title {
            font-weight: 800;
            font-size: 2.35rem;
            line-height: 1.25;
            letter-spacing: -0.5px;
            color: #ffffff;
        }

        .hero-subtitle {
            font-size: 0.98rem;
            line-height: 1.65;
            color: #94a3b8;
            font-weight: 400;
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .metric-pill {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 12px 16px;
        }

        .icon-square-box {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .card-modern {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }
        .card-modern:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            border-color: rgba(13, 110, 253, 0.3);
        }

        .news-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s;
        }
        .news-card:hover { transform: translateY(-4px); }
        .news-img { height: 185px; object-fit: cover; width: 100%; }

        .btn-glow {
            box-shadow: 0 0 20px rgba(13, 110, 253, 0.4);
            transition: all 0.2s ease;
        }
        .btn-glow:hover { box-shadow: 0 0 30px rgba(13, 110, 253, 0.6); }

        @media (max-width: 768px) {
            .hero-title { font-size: 1.85rem; }
            .brand-logos-wrapper img { height: 30px; }
        }
    </style>
</head>
<body>

    <!-- NAVBAR TOP -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand text-white d-flex align-items-center gap-2" href="#">
                <div class="bg-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-shield-fill-check text-white fs-5"></i>
                </div>
                <span>SMART EWS</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3 gap-2 mt-3 mt-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#beranda">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#fitur">Teknologi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#manfaat">Manfaat</a></li>
                    <li class="nav-item"><a class="nav-link" href="#berita">Berita Kesehatan</a></li>
                    <li class="nav-item ms-lg-2">
                        <a href="auth/login.php" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold btn-glow text-white">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sesi Petugas
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section id="beranda" class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    
                    <!-- BRAND LOGOS (Politeknik Indonusa, UIN Jogja, & UINIC 08) -->
                    <div class="brand-logos-wrapper">
                        <img src="assets/img/logo-indonusa.png" alt="Politeknik Indonusa Surakarta">
                        <span class="mx-2 text-secondary opacity-50">|</span>
                        <img src="assets/img/uin jogja.png" alt="UIN Jogja">
                        <span class="mx-2 text-secondary opacity-50">|</span>
                        <img src="assets/img/LOGO HITAM.png" alt="UINIC 08">
                    </div>

                    <div class="text-info fw-bold mb-2 small" style="letter-spacing: 1px;">
                        POLITEKNIK INDONUSA SURAKARTA
                    </div>

                    <h1 class="hero-title mb-3">Pusat Surveilans Epidemiologi & Deteksi Dini Spasial</h1>
                    <p class="hero-subtitle mb-4">
                        Mengintegrasikan data kasus kesehatan harian dengan peta kerawanan spasial adaptif dan asisten analitis kecerdasan buatan untuk merespon potensi Kejadian Luar Biasa (KLB) di Kecamatan Laweyan.
                    </p>
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <a href="auth/login.php" class="btn btn-primary btn-lg rounded-pill px-4 py-2.5 fw-bold btn-glow" style="font-size: 0.95rem;">
                            Akses Portal Petugas <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                        <a href="#fitur" class="btn btn-outline-light btn-lg rounded-pill px-4 py-2.5 fw-semibold" style="font-size: 0.95rem;">
                            Eksplorasi Fitur
                        </a>
                    </div>
                </div>

                <!-- Visual Interactive Card Kanan -->
                <div class="col-lg-5">
                    <div class="glass-card">
                        <div class="mb-4">
                            <small class="text-primary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 1px;">Status Wilayah Monitoring</small>
                            <h5 class="text-white fw-bold mb-0 mt-1">Kecamatan Laweyan</h5>
                        </div>

                        <div class="d-flex flex-column gap-3 mb-4">
                            <!-- Metric 1: Icon Gedung Kelurahan Pas di Tengah Kotak Biru -->
                            <div class="metric-pill d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-square-box bg-primary text-white shadow-sm">
                                        <i class="bi bi-building-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-white fw-bold" style="font-size: 0.9rem;">11 Kelurahan</div>
                                        <small class="text-white-50" style="font-size: 0.75rem;">Pemantauan Spasial Presisi</small>
                                    </div>
                                </div>
                                <span class="text-primary fw-bold" style="font-size: 0.85rem;">100% Data</span>
                            </div>

                            <!-- Metric 2: Icon Robot AI Pas di Tengah Kotak Kuning -->
                            <div class="metric-pill d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-square-box bg-warning text-dark shadow-sm">
                                        <i class="bi bi-robot fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="text-white fw-bold" style="font-size: 0.9rem;">EWS AI Co-Pilot</div>
                                        <small class="text-white-50" style="font-size: 0.75rem;">Analisis LLM Groq Engine</small>
                                    </div>
                                </div>
                                <span class="text-warning fw-bold" style="font-size: 0.85rem;">Aktif</span>
                            </div>
                        </div>

                        <!-- Card Tim Pengembang -->
                        <div class="p-3 bg-dark bg-opacity-60 rounded-3 border border-white border-opacity-10 text-center">
                            <small class="text-white-50 d-block mb-1" style="font-size: 0.75rem;">Tim Peneliti & Pengembang:</small>
                            <span class="text-white fw-bold d-block" style="font-size: 0.88rem;">Dimas Rifai &bull; Wahyu &bull; Adit</span>
                            <small class="text-info d-block mt-1 fw-semibold" style="font-size: 0.78rem;">Pembimbing: Aliansi Wicaksono, S.Tr.RMIK</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION TEKNOLOGI UTAMA -->
    <section id="fitur" class="py-5" style="background-color: var(--bg-light);">
        <div class="container py-4">
            <div class="text-center mx-auto mb-5" style="max-width: 650px;">
                <span class="text-primary fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1.5px;">Pilar Teknologi</span>
                <h2 class="fw-bold mt-2">Arsitektur Surveilans Digital Terintegrasi</h2>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card-modern">
                        <div class="icon-square-box bg-primary bg-opacity-10 text-primary mb-3" style="width: 52px; height: 52px; border-radius: 12px;">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Ambang Batas Adaptif</h5>
                        <p class="text-muted mb-0" style="font-size: 0.9rem; line-height: 1.6;">
                            Kalkulasi otomatis tren kenaikan kasus untuk menentukan status Waspada hingga Potensi Wabah tanpa perlu pemrosesan manual.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card-modern">
                        <div class="icon-square-box bg-primary bg-opacity-10 text-primary mb-3" style="width: 52px; height: 52px; border-radius: 12px;">
                            <i class="bi bi-map-fill fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Peta Kerawanan Spasial</h5>
                        <p class="text-muted mb-0" style="font-size: 0.9rem; line-height: 1.6;">
                            Visualisasi Polygon Leaflet.js real-time 11 kelurahan yang langsung berubah warna sesuai kondisi kasus aktif di lapangan.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card-modern">
                        <div class="icon-square-box bg-primary bg-opacity-10 text-primary mb-3" style="width: 52px; height: 52px; border-radius: 12px;">
                            <i class="bi bi-robot fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Asisten EWS AI Co-Pilot</h5>
                        <p class="text-muted mb-0" style="font-size: 0.9rem; line-height: 1.6;">
                            Kemampuan analitis berbasis LLM Groq untuk pelacakan klaster, simulasi fogging, hingga pembuatan draf surat dinas otomatis.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION MANFAAT STAKEHOLDER -->
    <section id="manfaat" class="py-5">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="text-primary fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1.5px;">Implementasi Presisi</span>
                    <h2 class="fw-bold mt-2 mb-4">Solusi Digital untuk Berbagai Tingkat Pengambil Keputusan</h2>

                    <div class="d-flex gap-3 mb-4">
                        <div class="icon-square-box bg-primary bg-opacity-10 text-primary fs-5 flex-shrink-0" style="width: 44px; height: 44px; border-radius: 10px;">
                            <i class="bi bi-hospital-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Petugas Puskesmas & Surveilans</h6>
                            <p class="text-muted mb-0" style="font-size: 0.88rem;">Pencatatan rekam medis kasus berbasis ICD-10, pelacakan histori pasien, serta ekspor ringkasan laporan otomatis PDF/Excel.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-4">
                        <div class="icon-square-box bg-primary bg-opacity-10 text-primary fs-5 flex-shrink-0" style="width: 44px; height: 44px; border-radius: 10px;">
                            <i class="bi bi-building-fill-check"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Dinas Kesehatan & Pemcam Laweyan</h6>
                            <p class="text-muted mb-0" style="font-size: 0.88rem;">Sistem pendukung keputusan (DSS) berbasis bukti untuk alokasi logistik obat dan instruksi penanggulangan lapangan.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="icon-square-box bg-primary bg-opacity-10 text-primary fs-5 flex-shrink-0" style="width: 44px; height: 44px; border-radius: 10px;">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Keamanan & Privasi Rekam Medis</h6>
                            <p class="text-muted mb-0" style="font-size: 0.88rem;">Penerapan proteksi penyamaran NIK (*data masking*) dan arsitektur akses berbasis peran (RBAC).</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="p-4 rounded-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white;">
                        <div class="d-flex align-items-center gap-2 text-warning mb-3 fw-bold" style="font-size: 0.85rem;">
                            <i class="bi bi-stars"></i> SISTEM SURVEILANS MODERN
                        </div>
                        <h4 class="fw-bold mb-3">Mewujudkan Respon Kesehatan Masyarakat yang Cepat & Terukur</h4>
                        <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.6;">
                            SMART EWS dikembangkan sebagai wujud kontribusi akademis Politeknik Indonusa Surakarta dalam memperkuat infrastruktur kesehatan digital di wilayah Kecamatan Laweyan.
                        </p>
                        <a href="auth/login.php" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold btn-glow">
                            Akses Portal Petugas Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION BERITA KESEHATAN REAL-TIME -->
    <section id="berita" class="py-5" style="background-color: var(--bg-light);">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <span class="text-primary fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1.5px;">Informasi Publik</span>
                    <h2 class="fw-bold mt-1 mb-0">Berita Kesehatan Terkini</h2>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill"><i class="bi bi-broadcast me-1"></i> Live Real-Time Feed</span>
            </div>

            <div class="row g-4">
                <?php foreach ($berita_list as $b): ?>
                    <div class="col-md-4">
                        <div class="news-card">
                            <img src="<?= isset($b['image']) ? $b['image'] : 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=500&q=80' ?>" class="news-img" alt="Berita" onerror="this.src='https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=500&q=80'">
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <div class="text-muted mb-2" style="font-size: 0.75rem;"><i class="bi bi-calendar3 me-1"></i> <?= $b['pubDate'] ?> | <?= $b['source'] ?></div>
                                <h6 class="fw-bold text-dark mb-2" style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; font-size: 0.95rem;"><?= $b['title'] ?></h6>
                                <p class="text-muted small mb-3" style="display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; font-size: 0.82rem;"><?= $b['description'] ?></p>
                                <a href="<?= $b['link'] ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-auto rounded-pill fw-semibold">Baca Selengkapnya <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-4 bg-dark text-white border-top border-secondary border-opacity-20" style="background-color: #0f172a !important;">
        <div class="container d-md-flex justify-content-between align-items-center text-center text-md-start">
            <div class="mb-2 mb-md-0" style="font-size: 0.85rem; color: #94a3b8;">
                &copy; <?= date('Y') ?> <b>SMART EWS Laweyan</b>. Politeknik Indonusa Surakarta.
            </div>
            <div>
                <a href="auth/login.php" class="text-decoration-none text-primary fw-bold" style="font-size: 0.85rem;">
                    Portal Akses Petugas Inti <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>