<?php
/** 
 * KODE PENENANG INTELEPHENSE
 * @var string $nama_lengkap
 */

date_default_timezone_set('Asia/Jakarta');
$jam = date('H');

// 1. Sapaan & Ikon Waktu
if ($jam >= 5 && $jam < 11) {
    $salam = "Selamat Pagi"; $icon = "bi-brightness-high-fill text-warning";
} elseif ($jam >= 11 && $jam < 15) {
    $salam = "Selamat Siang"; $icon = "bi-sun-fill text-warning";
} elseif ($jam >= 15 && $jam < 18) {
    $salam = "Selamat Sore"; $icon = "bi-cloud-sun-fill text-info";
} else {
    $salam = "Selamat Malam"; $icon = "bi-moon-stars-fill text-primary";
}

// 2. Kalender Digital (Otomatis Support Tahun Kabisat)
$hari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$namaHari = $hari[date('l')];
$tanggal = date('d') . ' ' . $bulan[(int)date('m')] . ' ' . date('Y');

// 3. Ekstraksi Nama Panggilan & Inisial
$namaArray = explode(' ', trim($nama_lengkap));
$nama_panggilan = ucfirst($namaArray[0]); // Mengambil kata pertama saja
$inisial = '';
foreach($namaArray as $n){
    $inisial .= strtoupper(substr($n,0,1));
    if(strlen($inisial) >= 2) break;
}

// 4. Hitung Alert untuk Lonceng Dropdown
$jumlah_notif_baru = 0;
$data_notif_dropdown = [];
if (isset($koneksi)) {
    // Hitung total semua yang belum dibaca
    $q_hitung = mysqli_query($koneksi, "SELECT COUNT(id_alert) as total FROM tabel_alert WHERE status_baca = 'belum' AND status_verifikasi != 'abaikan'");
    if ($q_hitung) $jumlah_notif_baru = mysqli_fetch_assoc($q_hitung)['total'];

    // Ambil maksimal 3 terbaru untuk isi pop-up
    $q_notif = mysqli_query($koneksi, "
        SELECT a.id_alert, a.pesan_alert, a.tingkat_alert, a.created_at, p.nama_populer, k.nama_kelurahan 
        FROM tabel_alert a
        JOIN tabel_penyakit p ON a.id_penyakit = p.id_penyakit
        JOIN tabel_kelurahan k ON a.id_kelurahan = k.id_kelurahan
        WHERE a.status_baca = 'belum' AND a.status_verifikasi != 'abaikan'
        ORDER BY a.created_at DESC LIMIT 3
    ");
    if ($q_notif) {
        while ($row = mysqli_fetch_assoc($q_notif)) {
            $data_notif_dropdown[] = $row;
        }
    }
}
?>

<style>
/* ========================================================
   STYLING TOPBAR MODERN 
======================================================== */
.topbar-modern { 
    display: flex; justify-content: space-between; align-items: center; 
    margin-bottom: 35px; flex-wrap: wrap; gap: 20px; 
}
.topbar-title { font-weight: 800; color: var(--text-main); margin-bottom: 5px; font-size: 1.6rem; letter-spacing: -0.5px;}
.topbar-subtitle { color: var(--text-muted); margin: 0; font-size: 0.95rem; font-weight: 500; }
.topbar-right { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }

/* Komponen Box Transparan */
.glass-box {
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    transition: all 0.3s ease;
}
.glass-box:hover { box-shadow: 0 6px 25px rgba(0,0,0,0.06); transform: translateY(-2px); }

/* Waktu */
.clock-box { padding: 8px 24px; min-width: 210px; text-align: center; }
.clock-day { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
.clock-time { font-size: 1.5rem; font-weight: 800; color: #0d6efd; font-variant-numeric: tabular-nums; }

/* Profil User */
.user-box { display: flex; align-items: center; gap: 12px; padding: 6px 16px 6px 6px; }
.user-avatar { 
    width: 42px; height: 42px; border-radius: 10px; 
    background: linear-gradient(135deg, #0d6efd, #0aa2c0); 
    color: white; font-weight: 700; display: flex; align-items: center; justify-content: center; font-size: 15px;
    box-shadow: 0 4px 10px rgba(13,110,253,0.3);
}
.user-name { font-weight: 700; color: var(--text-main); font-size: 0.9rem; }
.user-role { font-size: 0.75rem; color: #0aa2c0; font-weight: 600;}

/* Tombol Aksi (Tema & Lonceng) */
.action-btn {
    background: var(--bg-panel); border: 1px solid var(--border-color); color: var(--text-main);
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 1.25rem;
    cursor: pointer; position: relative; transition: all 0.3s ease;
}
.action-btn:hover { background: rgba(13, 110, 253, 0.05); color: #0d6efd; border-color: rgba(13, 110, 253, 0.3); transform: scale(1.05); }

/* Badge Lonceng */
.notif-badge-top {
    position: absolute; top: -5px; right: -5px;
    background: #dc3545; color: white;
    font-size: 0.65rem; font-weight: 700;
    padding: 3px 6px; border-radius: 50px;
    border: 2px solid var(--bg-body);
    box-shadow: 0 2px 5px rgba(220,53,69,0.4);
    animation: pulse-red 2s infinite;
}

@keyframes pulse-red {
    0% { box-shadow: 0 0 0 0 rgba(220,53,69,0.7); }
    70% { box-shadow: 0 0 0 6px rgba(220,53,69,0); }
    100% { box-shadow: 0 0 0 0 rgba(220,53,69,0); }
}

/* ========================================================
   DROPDOWN NOTIFIKASI
======================================================== */
.dropdown-notif-menu {
    width: 320px;
    padding: 0; border: 1px solid var(--border-color); border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1); background-color: var(--bg-panel);
    overflow: hidden; margin-top: 10px !important;
}
.dropdown-notif-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 15px 20px; font-weight: 700; color: #495057;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex; justify-content: space-between; align-items: center;
}
[data-theme="dark"] .dropdown-notif-header { background: linear-gradient(135deg, #2a2d32, #212529); color: #f8f9fa; border-color: rgba(255,255,255,0.05); }

.notif-item { padding: 15px 20px; border-bottom: 1px solid var(--border-color); display: block; text-decoration: none; transition: background 0.2s;}
.notif-item:hover { background-color: rgba(13,110,253,0.03); }
.notif-title { font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 4px; line-height: 1.3;}
.notif-desc { font-size: 0.75rem; color: var(--text-muted); display: flex; justify-content: space-between;}
.notif-footer { padding: 12px; text-align: center; background: rgba(0,0,0,0.01); }
</style>

<div class="topbar-modern">
    <!-- Bagian Sapaan -->
    <div>
        <h3 class="topbar-title">
            <?= $salam; ?>, <?= $nama_panggilan; ?> <i class="bi <?= $icon; ?> ms-1"></i>
        </h3>
        <p class="topbar-subtitle">Selamat datang kembali di Command Center.</p>
    </div>

    <!-- Bagian Kanan -->
    <div class="topbar-right">
        
        <!-- TOMBOL SWITCH TEMA -->
        <button id="themeToggle" class="action-btn glass-box" title="Ganti Tema">
            <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
        </button>

        <!-- DROPDOWN NOTIFIKASI LONCENG -->
        <div class="dropdown">
            <button class="action-btn glass-box" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell-fill"></i>
                <?php if ($jumlah_notif_baru > 0): ?>
                    <span class="notif-badge-top"><?= $jumlah_notif_baru > 99 ? '99+' : $jumlah_notif_baru; ?></span>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-notif-menu">
                <div class="dropdown-notif-header">
                    <span>Notifikasi Baru</span>
                    <?php if ($jumlah_notif_baru > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?= $jumlah_notif_baru; ?></span>
                    <?php endif; ?>
                </div>
                
                <div style="max-height: 300px; overflow-y: auto;">
                    <?php if (count($data_notif_dropdown) > 0): ?>
                        <?php foreach ($data_notif_dropdown as $notif): 
                            $warna_text = ($notif['tingkat_alert'] == 'waspada') ? 'text-warning' : 'text-danger';
                            $waktu_pendek = date('H:i, d M', strtotime($notif['created_at']));
                        ?>
                            <a class="notif-item" href="/ews_laweyan/pages/notifikasi/index.php">
                                <div class="notif-title">
                                    <i class="bi bi-exclamation-circle-fill <?= $warna_text; ?> me-1"></i> 
                                    <?= htmlspecialchars($notif['nama_populer']); ?> (Kel. <?= htmlspecialchars($notif['nama_kelurahan']); ?>)
                                </div>
                                <div class="notif-desc">
                                    <span>Peringatan EWS Terpicu</span>
                                    <span><i class="bi bi-clock me-1"></i><?= $waktu_pendek; ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center p-4 text-muted small">
                            <i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>
                            Tidak ada notifikasi baru.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="notif-footer border-top" style="border-color: var(--border-color) !important;">
                    <a href="/ews_laweyan/pages/notifikasi/index.php" class="text-decoration-none fw-bold small" style="color: #0d6efd;">Lihat Semua Peringatan <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </ul>
        </div>

        <!-- WAKTU DIGITAL -->
        <div class="clock-box glass-box">
            <div class="clock-day"><?= $namaHari; ?>, <?= $tanggal; ?></div>
            <div class="clock-time" id="digitalClock">00:00:00</div>
        </div>

        <!-- USER AVATAR -->
        <div class="user-box glass-box">
            <div class="user-avatar"><?= $inisial; ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($nama_lengkap); ?></div>
                <div class="user-role"><i class="bi bi-circle-fill text-success me-1" style="font-size: 8px;"></i>Online</div>
            </div>
        </div>
    </div>
</div>

<script>
// Jam Digital Sinkronisasi Real-Time
function updateClock(){
    const now = new Date();
    let h = String(now.getHours()).padStart(2, '0');
    let m = String(now.getMinutes()).padStart(2, '0');
    let s = String(now.getSeconds()).padStart(2, '0');
    const clockElement = document.getElementById('digitalClock');
    if(clockElement) clockElement.innerHTML = h + ":" + m + ":" + s;
}
setInterval(updateClock, 1000);
updateClock();

// Logic Ganti Tema Seamless
document.addEventListener("DOMContentLoaded", function() {
    const themeToggleBtn = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    
    // Set icon awal berdasarkan tema yang tersimpan
    if(document.documentElement.getAttribute('data-theme') === 'light') {
        themeIcon.className = 'bi bi-sun-fill text-warning';
    } else {
        themeIcon.className = 'bi bi-moon-stars-fill text-primary';
    }

    themeToggleBtn.addEventListener('click', () => {
        let currentTheme = document.documentElement.getAttribute('data-theme');
        let newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        if(newTheme === 'light') {
            themeIcon.className = 'bi bi-sun-fill text-warning';
        } else {
            themeIcon.className = 'bi bi-moon-stars-fill text-primary';
        }
    });
});
</script>