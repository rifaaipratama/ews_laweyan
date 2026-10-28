login.php bckup

<?php
session_start();
// Jika sudah login, arahkan ke dashboard
if (isset($_SESSION['id_pengguna'])) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sesi - Siaga Laweyan</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            overflow: hidden; /* Mencegah scroll bar tidak perlu */
        }
        
        /* Background Full Layar */
        .login-wrapper {
            min-height: 100vh;
            background-image: url('../assets/img/bg-login-baru.png'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
            display: flex;
            align-items: center;
        }

        .login-wrapper::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to right, rgba(13, 35, 58, 0.85) 0%, rgba(255, 255, 255, 0) 100%);
            z-index: 1;
        }

        .content-container {
            position: relative;
            z-index: 2;
            width: 100%;
        }

        /* Branding Indonusa (Pojok Kiri Atas) */
        .brand-top-left {
            position: absolute;
            top: 2rem;
            left: 3rem;
            z-index: 10;
            display: flex;
            align-items: center;
        }
        
        .brand-top-left img {
            height: 55px;
            width: auto;
            margin-right: 15px;
            filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.4));
        }

        .brand-top-left .brand-text {
            color: #ffffff;
            text-shadow: 0 2px 6px rgba(0,0,0,0.6);
            line-height: 1.1;
        }

        /* Teks Deskripsi Kiri */
        .hero-text {
            color: #ffffff;
            text-shadow: 0 2px 15px rgba(0,0,0,0.3);
            padding-right: 3rem;
            margin-top: 2rem;
        }
        
        /* Badge Logo Penyelenggara */
        .organizer-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            padding: 0.5rem 1.5rem;
            border-radius: 50px; /* Bentuk Pill */
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .organizer-badge img {
            height: 45px;
            width: auto;
        }
        /* Membulatkan logo digital pulse jika aslinya kotak */
        .logo-pulse {
            border-radius: 50%;
        }

        .hero-title {
            font-weight: 800;
            letter-spacing: 2px;
            font-size: 3.5rem;
            margin-bottom: 0.5rem;
        }
        .hero-subtitle {
            font-size: 1.25rem;
            font-weight: 500;
            color: #e0f2fe;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        .hero-desc {
            font-size: 1rem;
            font-weight: 300;
            color: #f8f9fa;
            opacity: 0.9;
        }

        /* ----- ANIMASI & STATE FORM LOGIN ----- */
        .ui-element {
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .state-hidden {
            opacity: 0;
            visibility: hidden;
            transform: translateY(30px) scale(0.95);
            position: absolute;
            pointer-events: none;
            width: 100%;
        }

        .state-visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
            position: relative;
            pointer-events: auto;
            width: 100%;
        }

        .btn-trigger {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            color: #0284c7;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 2rem;
            padding: 0.6rem 1.5rem;
            font-weight: 700;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            margin-top: 15rem;
        }
        .btn-trigger:hover {
            background: #ffffff;
            color: #0369a1;
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .btn-close-card {
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            background: transparent;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            transition: all 0.2s;
        }
        .btn-close-card:hover {
            background: rgba(0,0,0,0.05);
            color: #0f172a;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 1.2rem;
            padding: 3rem 2.5rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .shield-icon {
            font-size: 3.2rem;
            color: #1a3c4b;
            margin-bottom: 0.5rem;
            display: inline-block;
        }

        .custom-input-group {
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid #ced4da;
            border-radius: 0.6rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        .custom-input-group:focus-within {
            border-color: #1a3c4b;
            box-shadow: 0 0 0 4px rgba(26, 60, 75, 0.15);
            background-color: #ffffff;
        }
        .custom-input-group .input-group-text, .custom-input-group .form-control, .custom-input-group .btn {
            border: none; background: transparent;
        }
        .custom-input-group .input-group-text {
            color: #1a3c4b; padding-left: 1.2rem; padding-right: 0.5rem;
        }
        .custom-input-group .form-control:focus {
            box-shadow: none;
        }

        .btn-login {
            background: linear-gradient(135deg, #1a3c4b 0%, #2a5a70 100%);
            border: none;
            color: white;
            border-radius: 0.6rem;
            padding: 0.8rem;
            font-size: 1.1rem;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #122b36 0%, #1a3c4b 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 60, 75, 0.3);
        }

       /* Credit Team Footer (Pojok Kiri Bawah) */
        .team-footer {
            position: absolute;
            bottom: 2rem;
            left: 3rem;
            z-index: 10;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.5;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }
        .team-footer .team-name {
            font-weight: 700;
            color: rgba(255, 255, 255, 0.95);
            letter-spacing: 1.5px;
            margin-bottom: 0.2rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    
    <!-- HEADER BRANDING KIRI ATAS (Logo Kampus Anda) -->
    <div class="brand-top-left">
        <img src="../assets/img/logo-indonusa.png" alt="Politeknik Indonusa">
        <div class="brand-text d-none d-sm-block">
            <div style="font-size: 1.1rem; font-weight: 800; letter-spacing: 1px;">POLITEKNIK INDONUSA</div>
            <div style="font-size: 0.85rem; font-weight: 300; letter-spacing: 3px;">SURAKARTA</div>
        </div>
    </div>

    <div class="container content-container">
        <div class="row align-items-center justify-content-between">
            
            <!-- KOLOM KIRI: Teks Deskripsi Sistem & Logo Penyelenggara -->
            <div class="col-lg-6 d-none d-lg-block hero-text">
                
                <!-- LOGO PENYELENGGARA LOMBA (Esa Unggul & Digital Pulse) -->
                <div class="organizer-badge">
                    <img src="../assets/img/uin jogja.png" alt="uin jogja">
                    <span class="mx-3 text-secondary opacity-50">|</span>
                    <img src="../assets/img/LOGO HITAM.png" alt="UINIC 08" class="UINIC 08">
                </div>

                <h1 class="hero-title">SMART EWS</h1>
                <h2 class="hero-subtitle">Sistem Surveilans Digital dengan Peringatan Berbasis Ambang Adaptif dan Peta Risiko Spasial</h2>
                <p class="hero-desc">
                    Membangun ketahanan kesehatan daerah melalui integrasi data epidemiologi real-time. Mempercepat deteksi Kejadian Luar Biasa (KLB) untuk intervensi lapangan yang presisi, terarah, dan efektif demi melindungi masyarakat Kecamatan Laweyan.
                </p>
            </div>

            <!-- KOLOM KANAN: Interaksi Form -->
            <div class="col-lg-5 col-md-8 col-sm-10 mx-auto d-flex flex-column align-items-center justify-content-center position-relative" style="min-height: 450px;">
                
                <!-- TOMBOL TRIGGER (Awalnya Terlihat) -->
                <div id="triggerMode" class="ui-element state-visible text-center">
                    <button id="btnShowCard" class="btn btn-trigger transition-all">
                        Masuk Sesi <i class="bi bi-box-arrow-in-right ms-1"></i>
                    </button>
                </div>

                <!-- CARD FORM LOGIN (Awalnya Sembunyi) -->
                <div id="cardMode" class="ui-element state-hidden">
                    <div class="glass-card text-center">
                        
                        <!-- Tombol Sembunyikan -->
                        <button id="btnHideCard" class="btn-close-card position-absolute top-0 end-0 mt-3 me-3">
                            <i class="bi bi-eye-slash-fill me-1"></i> Sembunyikan
                        </button>
                        
                        <!-- Header Form -->
                        <div class="mb-4 mt-2">
                            <i class="bi bi-shield-lock-fill shield-icon"></i>
                            <h2 class="fw-bold text-dark mb-1">Masuk Sesi</h2>
                            <p class="text-muted small">Otentikasi aman untuk mengakses sistem pusat</p>
                        </div>

                        <?php
                        if (isset($_SESSION['error_msg'])) {
                            echo '<div class="alert alert-danger py-2 text-center small rounded-3 border-0 shadow-sm">' . $_SESSION['error_msg'] . '</div>';
                            unset($_SESSION['error_msg']);
                        }
                        ?>

                        <!-- Form -->
                        <form action="proses_login.php" method="POST" class="text-start">
                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary mb-1">Nama Pengguna</label>
                                <div class="custom-input-group input-group p-1">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" name="username" class="form-control" required autocomplete="off" placeholder="Masukkan username">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary mb-1">Kata Sandi</label>
                                <div class="custom-input-group input-group p-1">
                                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                    <input type="password" name="password" id="inputPassword" class="form-control" required placeholder="Masukkan kata sandi">
                                    <button class="btn text-secondary pe-3" type="button" id="btnTogglePassword">
                                        <i class="bi bi-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-login w-100 fw-bold mt-2">
                                Mulai Sesi <i class="bi bi-arrow-right-circle ms-2"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- KREDIT TIM (Pojok Kiri Bawah) -->
    <div class="team-footer d-none d-md-block">
        <div class="team-name">PRESENTED BY</div>
        <div>Dimas Rifai &middot; Wahyu &middot; Adit </div>
        <div>Supervisor: Aliansi Wicaksono, S.Tr.RMIK</div>
    </div>
</div>

<!-- Script untuk Interaksi Form & Password -->
<script>
    const btnShowCard = document.getElementById('btnShowCard');
    const btnHideCard = document.getElementById('btnHideCard');
    const triggerMode = document.getElementById('triggerMode');
    const cardMode = document.getElementById('cardMode');

    btnShowCard.addEventListener('click', () => {
        triggerMode.classList.remove('state-visible');
        triggerMode.classList.add('state-hidden');
        cardMode.classList.remove('state-hidden');
        cardMode.classList.add('state-visible');
    });

    btnHideCard.addEventListener('click', () => {
        cardMode.classList.remove('state-visible');
        cardMode.classList.add('state-hidden');
        triggerMode.classList.remove('state-hidden');
        triggerMode.classList.add('state-visible');
    });

    const btnTogglePassword = document.getElementById('btnTogglePassword');
    const inputPassword = document.getElementById('inputPassword');
    const eyeIcon = document.getElementById('eyeIcon');

    btnTogglePassword.addEventListener('click', function () {
        const type = inputPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        inputPassword.setAttribute('type', type);
        eyeIcon.classList.toggle('bi-eye');
        eyeIcon.classList.toggle('bi-eye-slash');
    });
</script>

</body>
</html>