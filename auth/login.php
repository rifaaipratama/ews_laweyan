<?php
session_start();

// 1. Inisialisasi Koneksi Database Fallback (Database: ews_laweyan)
/** @var mysqli|null $koneksi */
$koneksi = null;

if (file_exists('../config/koneksi.php')) {
    include '../config/koneksi.php';
} elseif (file_exists('../koneksi.php')) {
    include '../koneksi.php';
} else {
    $koneksi = @mysqli_connect("localhost", "root", "", "ews_laweyan");
}

// 2. Fungsi Aman Query (Bebas Error & Garis Biru Intelephense)
/**
 * Menghitung total data dari query SQL secara aman
 * 
 * @param mysqli|null $conn Connection instance
 * @param string $query SQL Query string
 * @return int Total count
 */
function get_count_safe(?mysqli $conn, string $query): int {
    if (!$conn) {
        return 0;
    }
    try {
        $res = @mysqli_query($conn, $query);
        if ($res && $row = mysqli_fetch_assoc($res)) {
            return (int)($row['total'] ?? 0);
        }
    } catch (Exception $e) {
        return 0;
    }
    return 0;
}

// 3. Fungsi Memanggil Berita Kesehatan Real-Time (RSS Feed Antara News / Fallback)
function get_realtime_health_news() {
    $rss_url = "https://www.antaranews.com/rss/warta-bumi.xml"; 
    
    $articles = [];
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $rss_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        $response = curl_exec($ch);
        curl_close($ch);

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
    }

    if (empty($articles)) {
        $articles = [
            [
                'title'       => 'Kewaspadaan Dini Demam Berdarah dan Penyakit Menular Musim Pancaroba',
                'description' => 'Dinas Kesehatan mengimbau masyarakat menggalakkan Gerakan 3M Plus guna menekan pengembangbiakan jentik nyamuk Aedes aegypti.',
                'link'        => 'https://www.kompas.com',
                'pubDate'     => date('d M Y'),
                'source'      => 'Kompas Health',
                'image'       => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?w=500&q=80'
            ],
            [
                'title'       => 'Pencegahan Penyakit Menular Berbasis Lingkungan Pemukiman Perkotaan',
                'description' => 'Sanitasi lingkungan dan pemantauan drainase menjadi faktor kunci pencegahan penularan penyakit berbasis lingkungan.',
                'link'        => 'https://health.detik.com',
                'pubDate'     => date('d M Y'),
                'source'      => 'detikHealth',
                'image'       => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=500&q=80'
            ],
            [
                'title'       => 'Transformasi Digital Integrasi Data Surveilans Kesehatan Daerah',
                'description' => 'Pemanfaatan sistem informasi berbasis spasial mempercepat deteksi dini dan intervensi respon cepat di tingkat kecamatan.',
                'link'        => 'https://kemkes.go.id',
                'pubDate'     => date('d M Y'),
                'source'      => 'Kemenkes RI',
                'image'       => 'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?w=500&q=80'
            ]
        ];
    }
    return $articles;
}

$berita_list = get_realtime_health_news();

// 4. Proses Logika Login
$error_message = '';
$show_login_modal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password) && $koneksi) {
        $stmt = mysqli_prepare($koneksi, "SELECT id_pengguna, id_role, nama_lengkap, username, password_hash, status_aktif FROM tabel_pengguna WHERE username = ? LIMIT 1");
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($user = mysqli_fetch_assoc($result)) {
                if ($user['status_aktif'] !== 'aktif') {
                    $error_message = "Akun Anda sedang nonaktif. Silakan hubungi Administrator.";
                    $show_login_modal = true;
                } elseif (password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id']       = $user['id_pengguna'];
                    $_SESSION['id_pengguna']   = $user['id_pengguna'];
                    $_SESSION['username']      = $user['username'];
                    $_SESSION['nama_lengkap']  = $user['nama_lengkap'];
                    $_SESSION['role_id']       = $user['id_role'];
                    $_SESSION['id_role']       = $user['id_role'];

                    $update_stmt = mysqli_prepare($koneksi, "UPDATE tabel_pengguna SET last_login = NOW() WHERE id_pengguna = ?");
                    if ($update_stmt) {
                        mysqli_stmt_bind_param($update_stmt, "i", $user['id_pengguna']);
                        mysqli_stmt_execute($update_stmt);
                    }

                    $log_desc = "Melakukan login ke dalam sistem";
                    $log_stmt = mysqli_prepare($koneksi, "INSERT INTO tabel_riwayat_aktivitas (id_pengguna, jenis_aktivitas, deskripsi) VALUES (?, 'Login', ?)");
                    if ($log_stmt) {
                        mysqli_stmt_bind_param($log_stmt, "is", $user['id_pengguna'], $log_desc);
                        mysqli_stmt_execute($log_stmt);
                    }

                    header("Location: ../pages/dashboard/index.php");
                    exit;
                } else {
                    $error_message = "Password yang Anda masukkan salah.";
                    $show_login_modal = true;
                }
            } else {
                $error_message = "Username tidak ditemukan.";
                $show_login_modal = true;
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "Terjadi kesalahan sistem pada database.";
            $show_login_modal = true;
        }
    } else {
        $error_message = "Silakan isi Username dan Password!";
        $show_login_modal = true;
    }
}

// 5. Ambil Data Real-time 4 Card Statistik Utama
$total_kasus     = get_count_safe($koneksi, "SELECT COUNT(*) as total FROM tabel_kasus WHERE is_deleted = 0");
$total_alert     = get_count_safe($koneksi, "SELECT COUNT(*) as total FROM tabel_alert WHERE status_verifikasi = 'valid' AND status_baca = 'belum'");
$total_penyakit  = get_count_safe($koneksi, "SELECT COUNT(*) as total FROM tabel_penyakit WHERE status_pantau = 'aktif'");
$total_kelurahan = get_count_safe($koneksi, "SELECT COUNT(*) as total FROM tabel_kelurahan");

// 6. Ambil Ringkasan Detail 11 Kelurahan + Rincian Kasus Terkonfirmasi per Penyakit dari DB
$kelurahan_list = [];
$count_merah  = 0;
$count_kuning = 0;
$count_hijau  = 0;

$query_kelurahan = "
    SELECT 
        k.id_kelurahan,
        k.nama_kelurahan,
        k.jumlah_penduduk,
        k.luas_wilayah_km2,
        (
            SELECT tingkat_alert 
            FROM tabel_alert a 
            WHERE a.id_kelurahan = k.id_kelurahan 
            ORDER BY a.created_at DESC LIMIT 1
        ) AS alert_terakhir,
        (
            SELECT COUNT(*) 
            FROM tabel_kasus ks 
            WHERE ks.id_kelurahan = k.id_kelurahan AND ks.is_deleted = 0 AND ks.status_kasus = 'terkonfirmasi'
        ) AS total_kasus_aktif
    FROM tabel_kelurahan k
    ORDER BY k.nama_kelurahan ASC
";

if ($koneksi) {
    $res = @mysqli_query($koneksi, $query_kelurahan);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $status = $row['alert_terakhir'] ?? 'penurunan';
            if ($status === 'potensi_wabah') {
                $count_merah++;
            } elseif ($status === 'waspada') {
                $count_kuning++;
            } else {
                $count_hijau++;
            }

            // Sub-query rincian kasus terkonfirmasi per penyakit (Tanpa Nama Pasien)
            $rincian_kasus = [];
            $id_kel = (int)$row['id_kelurahan'];
            $q_detail = "
                SELECT p.nama_populer, COUNT(ks.id_kasus) AS total_kasus
                FROM tabel_kasus ks
                JOIN tabel_penyakit p ON ks.id_penyakit = p.id_penyakit
                WHERE ks.id_kelurahan = $id_kel 
                  AND ks.is_deleted = 0 
                  AND ks.status_kasus = 'terkonfirmasi'
                GROUP BY ks.id_penyakit, p.nama_populer
                ORDER BY total_kasus DESC
            ";
            $res_detail = @mysqli_query($koneksi, $q_detail);
            if ($res_detail) {
                while ($d = mysqli_fetch_assoc($res_detail)) {
                    $rincian_kasus[] = $d;
                }
            }
            $row['rincian_kasus'] = $rincian_kasus;
            $kelurahan_list[] = $row;
        }
    }
}
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

        /* Navbar Style */
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

        /* Hero Section */
        .hero-section {
            background: radial-gradient(circle at 10% 20%, rgba(13, 110, 253, 0.18) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(15, 23, 42, 0.98) 0%, #0f172a 100%);
            color: #ffffff;
            padding: 130px 0 90px;
            position: relative;
        }

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

        .btn-glow {
            box-shadow: 0 0 20px rgba(13, 110, 253, 0.4);
            transition: all 0.2s ease;
        }
        .btn-glow:hover { box-shadow: 0 0 30px rgba(13, 110, 253, 0.6); }

        /* Card 4 Data Real */
        .stat-card-modern {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s ease;
            height: 100%;
        }
        .stat-card-modern:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.06);
            border-color: rgba(13, 110, 253, 0.3);
        }

        /* Card Kelurahan Interactive */
        .kelurahan-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 18px;
            transition: all 0.25s ease;
            cursor: pointer;
            position: relative;
        }
        .kelurahan-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
            border-color: rgba(13, 110, 253, 0.4);
        }

        /* Card Berita */
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

        /* Modal Login Styling */
        .modal-login-content {
            border-radius: 20px;
            overflow: hidden;
            border: none;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        }

        .modal-banner-side {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.82), rgba(15, 23, 42, 0.92)), 
                        url('../assets/img/bg-login-baru.png') center/cover no-repeat;
            color: white;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .modal-form-side {
            padding: 40px 35px;
            background: #ffffff;
        }

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
                    <li class="nav-item"><a class="nav-link" href="#data-real">Statistik Real-time</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kelurahan-section">Status Kelurahan</a></li>
                    <li class="nav-item"><a class="nav-link" href="#berita-section">Berita Kesehatan</a></li>
                    <li class="nav-item ms-lg-2">
                        <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold btn-glow text-white" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sesi Petugas
                        </button>
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
                        <img src="../assets/img/logo-indonusa.png" alt="Politeknik Indonusa Surakarta" onerror="this.src='https://via.placeholder.com/100x40?text=Indonusa'">
                        <span class="mx-2 text-secondary opacity-50">|</span>
                        <img src="../assets/img/uin jogja.png" alt="UIN Jogja" onerror="this.src='https://via.placeholder.com/100x40?text=UIN+Jogja'">
                        <span class="mx-2 text-secondary opacity-50">|</span>
                        <img src="../assets/img/LOGO HITAM.png" alt="UINIC 08" onerror="this.src='https://via.placeholder.com/100x40?text=UINIC+08'">
                    </div>

                    <div class="text-info fw-bold mb-2 small" style="letter-spacing: 1px;">
                        POLITEKNIK INDONUSA SURAKARTA
                    </div>

                    <h1 class="hero-title mb-3">Pusat Surveilans Epidemiologi & Deteksi Dini Spasial</h1>
                    <p class="hero-subtitle mb-4">
                        Mengintegrasikan data kasus kesehatan harian dengan peta kerawanan spasial adaptif dan asisten analitis kecerdasan buatan untuk merespon potensi Kejadian Luar Biasa (KLB) di Kecamatan Laweyan.
                    </p>
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <button type="button" class="btn btn-primary btn-lg rounded-pill px-4 py-2.5 fw-bold btn-glow" style="font-size: 0.95rem;" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Akses Portal Petugas <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                        <a href="#data-real" class="btn btn-outline-light btn-lg rounded-pill px-4 py-2.5 fw-semibold" style="font-size: 0.95rem;">
                            Lihat Data Real-Time
                        </a>
                    </div>
                </div>

                <!-- VISUAL INTERACTIVE CARD KANAN -->
                <div class="col-lg-5">
                    <div class="glass-card">
                        <div class="mb-4">
                            <small class="text-primary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 1px;">Status Wilayah Monitoring</small>
                            <h5 class="text-white fw-bold mb-0 mt-1">Kecamatan Laweyan</h5>
                        </div>

                        <div class="d-flex flex-column gap-3 mb-4">
                            <!-- Metric 1: Icon Gedung Kelurahan -->
                            <div class="metric-pill d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-square-box bg-primary text-white shadow-sm">
                                        <i class="bi bi-building-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-white fw-bold" style="font-size: 0.9rem;"><?= $total_kelurahan ?> Kelurahan</div>
                                        <small class="text-white-50" style="font-size: 0.75rem;">Pemantauan Spasial Presisi</small>
                                    </div>
                                </div>
                                <span class="text-primary fw-bold" style="font-size: 0.85rem;">100% Data</span>
                            </div>

                            <!-- Metric 2: Icon Robot AI -->
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

    <!-- SECTION 4 CARD DATA REAL -->
    <section id="data-real" class="py-5" style="background-color: var(--bg-light);">
        <div class="container py-4">
            <div class="text-center mx-auto mb-5" style="max-width: 650px;">
                <span class="text-primary fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1.5px;">Indikator Utama</span>
                <h2 class="fw-bold mt-1">Statistik Surveilans Real-Time Kecamatan Laweyan</h2>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card-modern d-flex align-items-center">
                        <div class="icon-square-box bg-primary bg-opacity-10 text-primary me-3" style="width: 54px; height: 54px; font-size: 1.5rem;">
                            <i class="bi bi-virus"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-semibold">Total Kasus Aktif</small>
                            <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_kasus); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="stat-card-modern d-flex align-items-center">
                        <div class="icon-square-box bg-warning bg-opacity-10 text-warning me-3" style="width: 54px; height: 54px; font-size: 1.5rem;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-semibold">Peringatan / Alert</small>
                            <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_alert); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="stat-card-modern d-flex align-items-center">
                        <div class="icon-square-box bg-info bg-opacity-10 text-info me-3" style="width: 54px; height: 54px; font-size: 1.5rem;">
                            <i class="bi bi-file-earmark-medical-fill"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-semibold">Penyakit Dipantau</small>
                            <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_penyakit); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="stat-card-modern d-flex align-items-center">
                        <div class="icon-square-box bg-success bg-opacity-10 text-success me-3" style="width: 54px; height: 54px; font-size: 1.5rem;">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-semibold">Kelurahan Target</small>
                            <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_kelurahan); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION RINGKASAN DATA WILAYAH KELURAHAN (INTERAKTIF MODAL RINCIAN KASUS) -->
    <section id="kelurahan-section" class="py-5 bg-white">
        <div class="container py-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                <div>
                    <span class="text-primary fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1.5px;">Monitoring Spasial</span>
                    <h2 class="fw-bold mt-1 mb-1">Status Kewaspadaan 11 Kelurahan</h2>
                    <p class="text-muted mb-0">Klik pada card kelurahan untuk melihat rincian sebaran penyakit yang terkonfirmasi</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2 flex-wrap">
                    <span class="badge bg-danger px-3 py-2"><i class="bi bi-circle-fill me-1"></i> Potensi Wabah: <?= $count_merah ?></span>
                    <span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-circle-fill me-1"></i> Waspada: <?= $count_kuning ?></span>
                    <span class="badge bg-success px-3 py-2"><i class="bi bi-circle-fill me-1"></i> Aman/Penurunan: <?= $count_hijau ?></span>
                </div>
            </div>

            <div class="row g-3">
                <?php foreach ($kelurahan_list as $kel): ?>
                    <?php
                        $st = $kel['alert_terakhir'] ?? 'penurunan';
                        $badge_bg = 'bg-success';
                        $st_label = 'Aman / Penurunan';
                        $border_color = 'border-start border-4 border-success';

                        if ($st === 'potensi_wabah') {
                            $badge_bg = 'bg-danger';
                            $st_label = 'Potensi Wabah';
                            $border_color = 'border-start border-4 border-danger';
                        } elseif ($st === 'waspada') {
                            $badge_bg = 'bg-warning text-dark';
                            $st_label = 'Waspada';
                            $border_color = 'border-start border-4 border-warning';
                        }

                        // Prepare JSON rincian untuk dipanggil di Javascript Modal
                        $json_detail = htmlspecialchars(json_encode([
                            'nama_kelurahan' => $kel['nama_kelurahan'],
                            'jumlah_penduduk' => number_format($kel['jumlah_penduduk']),
                            'total_kasus_aktif' => $kel['total_kasus_aktif'],
                            'status_label' => $st_label,
                            'badge_bg' => $badge_bg,
                            'rincian' => $kel['rincian_kasus']
                        ]), ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="kelurahan-card <?= $border_color ?>" onclick="bukaModalKelurahan(<?= $json_detail ?>)">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                                    <?= htmlspecialchars($kel['nama_kelurahan']) ?>
                                    <i class="bi bi-info-circle text-primary opacity-50 small"></i>
                                </h6>
                                <span class="badge <?= $badge_bg ?>"><?= $st_label ?></span>
                            </div>
                            <div class="small text-muted mb-2">
                                <i class="bi bi-people me-1"></i> Populasi: <b><?= number_format($kel['jumlah_penduduk']) ?></b> Jiwa
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light">
                                <span class="small fw-semibold text-secondary">Kasus Terkonfirmasi:</span>
                                <span class="fw-bold text-dark fs-6"><?= $kel['total_kasus_aktif'] ?> Kasus <i class="bi bi-chevron-right text-muted ms-1 small"></i></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- SECTION BERITA KESEHATAN REAL-TIME (KAPSUL LIVE REAL-TIME FEED SUDAH DIHAPUS) -->
    <section id="berita-section" class="py-5" style="background-color: var(--bg-light);">
        <div class="container py-4">
            <div class="mb-4">
                <span class="text-primary fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1.5px;">Informasi Publik</span>
                <h2 class="fw-bold mt-1 mb-0">Berita Kesehatan Terkini</h2>
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
                <button type="button" class="btn btn-link text-decoration-none text-primary fw-bold p-0" style="font-size: 0.85rem;" data-bs-toggle="modal" data-bs-target="#loginModal">
                    Portal Akses Petugas Inti <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </footer>

    <!-- MODAL POP-UP RINCIAN KASUS KELURAHAN -->
    <div class="modal fade" id="modalDetailKelurahan" tabindex="-1" aria-labelledby="modalDetailKelurahanLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header bg-primary text-white p-3 px-4">
                    <div>
                        <small class="text-white-50 text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 1px;">Detail Sebaran Penyakit</small>
                        <h5 class="modal-title fw-bold" id="modalKelurahanTitle">Kelurahan -</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border mb-3">
                        <div>
                            <small class="text-muted d-block">Status Kewaspadaan</small>
                            <span id="modalKelurahanBadge" class="badge">Aman</span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Total Terkonfirmasi</small>
                            <span id="modalKelurahanTotal" class="fw-bold text-primary fs-5">0 Kasus</span>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-2" style="font-size: 0.88rem;">Rincian Jenis Kasus Terkonfirmasi:</h6>
                    <div id="modalKelurahanBodyList">
                        <!-- Rendered via JS -->
                    </div>
                </div>
                <div class="modal-footer p-2 px-4 bg-white border-top-0">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL POP-UP LOGIN CARD -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-login-content">
                <div class="row g-0">
                    <!-- Sisi Kiri: Banner Gambar bg-login-baru -->
                    <div class="col-lg-6 modal-banner-side d-none d-lg-flex">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-shield-halved fa-2x me-3"></i>
                            <div>
                                <h4 class="fw-bold mb-0">EWS LAWEYAN</h4>
                                <small class="text-white-50">Surveillance System</small>
                            </div>
                        </div>
                        <h6 class="fw-semibold mt-3 mb-2">Sistem Peringatan Dini Kesehatan Masyarakat</h6>
                        <p class="text-white-50 small mb-0">Kecamatan Laweyan, Kota Surakarta. Memantau sebaran penyakit & mendeteksi potensi wabah secara real-time.</p>
                    </div>

                    <!-- Sisi Kanan: Form Login -->
                    <div class="col-lg-6 modal-form-side">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold text-dark mb-0">Selamat Datang</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <p class="text-muted small mb-4">Silakan masuk menggunakan akun petugas EWS Anda</p>

                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger py-2 small" role="alert">
                                <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="username" class="form-label small fw-semibold text-secondary">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-regular fa-user text-muted"></i></span>
                                    <input type="text" name="username" id="username" class="form-control border-start-0 bg-light" placeholder="Masukkan username" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                    <input type="password" name="password" id="password" class="form-control border-start-0 bg-light" placeholder="Masukkan password" required>
                                </div>
                            </div>

                            <button type="submit" name="login" class="btn btn-primary w-100 py-2.5 font-weight-bold mb-2">
                                <i class="fa-solid fa-right-to-bracket me-2"></i>Masuk Ke Sistem
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // JS Function untuk membuka Pop-up Modal Rincian Kasus per Kelurahan
        function bukaModalKelurahan(data) {
            document.getElementById('modalKelurahanTitle').textContent = 'Kelurahan ' + data.nama_kelurahan;
            
            const badgeElem = document.getElementById('modalKelurahanBadge');
            badgeElem.className = 'badge ' + data.badge_bg;
            badgeElem.textContent = data.status_label;

            document.getElementById('modalKelurahanTotal').textContent = data.total_kasus_aktif + ' Kasus';

            const container = document.getElementById('modalKelurahanBodyList');
            container.innerHTML = '';

            if (data.rincian && data.rincian.length > 0) {
                let html = '<ul class="list-group list-group-flush border rounded-3 overflow-hidden">';
                data.rincian.forEach(item => {
                    html += `
                        <li class="list-group-flush list-group-item d-flex justify-content-between align-items-center py-2.5">
                            <span class="fw-medium text-dark"><i class="bi bi-virus me-2 text-danger"></i>${item.nama_populer}</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3">${item.total_kasus} Kasus</span>
                        </li>
                    `;
                });
                html += '</ul>';
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="text-center py-4 bg-white rounded-3 border">
                        <i class="bi bi-shield-check text-success display-6 d-block mb-2 opacity-75"></i>
                        <span class="text-muted small">Tidak ada kasus penyakit terkonfirmasi di wilayah ini.</span>
                    </div>
                `;
            }

            var modal = new bootstrap.Modal(document.getElementById('modalDetailKelurahan'));
            modal.show();
        }
    </script>

    <!-- Auto Pop-up Modal Jika Gagal Login -->
    <?php if ($show_login_modal): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var myModal = new bootstrap.Modal(document.getElementById('loginModal'));
            myModal.show();
        });
    </script>
    <?php endif; ?>

</body>
</html>