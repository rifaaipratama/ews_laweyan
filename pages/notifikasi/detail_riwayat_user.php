<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: riwayat_aktivitas.php");
    exit;
}

$id_user = intval($_GET['id']);

// Ambil info detail pengguna
$q_user = mysqli_query($koneksi, "
    SELECT p.nama_lengkap, p.username, r.nama_role 
    FROM tabel_pengguna p 
    JOIN tabel_role r ON p.id_role = r.id_role 
    WHERE p.id_pengguna = '$id_user'
");

if (mysqli_num_rows($q_user) == 0) {
    echo "<script>alert('Data pengguna tidak ditemukan!'); window.location='riwayat_aktivitas.php';</script>";
    exit;
}
$user = mysqli_fetch_assoc($q_user);

// Panggil HANYA header untuk memuat CSS & Tema Mode Terang/Gelap
include '../../layout/header.php';
?>

<style>
    /* Halaman Distraction-Free terpusat */
    body {
        background-color: var(--bg-body) !important;
        display: flex;
        justify-content: center;
        min-height: 100vh;
        margin: 0;
        padding: 40px 20px;
    }

    .detail-container {
        background-color: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 900px;
        position: relative;
        overflow: hidden;
    }
    
    .detail-container::before {
        content: '';
        position: absolute;
        top: 0; right: 0; left: 0;
        height: 6px;
        background: linear-gradient(90deg, #198754, #20c997);
    }

    .profile-header {
        background-color: rgba(0,0,0,0.02);
        padding: 2.5rem;
        border-bottom: 1px dashed var(--border-color);
        display: flex;
        align-items: center;
        gap: 20px;
    }
    [data-theme="dark"] .profile-header { background-color: rgba(255,255,255,0.02); }

    .profile-icon {
        width: 75px; height: 75px;
        background-color: #198754;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
    }

    /* Tabel Minimalis Modern */
    .table-custom {
        --bs-table-color: var(--text-color);
        --bs-table-bg: transparent;
        --bs-table-border-color: var(--border-color);
    }
    .table-custom thead th {
        background-color: var(--bg-body);
        color: var(--text-color);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 1rem;
        border-bottom: 2px solid var(--border-color);
    }
    .table-custom tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
    }
</style>

<div class="detail-container">
    
    <!-- Bagian Header Profil -->
    <div class="profile-header">
        <div class="profile-icon">
            <i class="bi bi-person-bounding-box"></i>
        </div>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="fw-bold mb-1 text-dynamic"><?= htmlspecialchars($user['nama_lengkap']); ?></h3>
                    <div class="d-flex gap-3 text-muted small mt-2">
                        <span><i class="bi bi-person-badge me-1"></i> Username: <b><?= htmlspecialchars($user['username']); ?></b></span>
                        <span><i class="bi bi-shield-lock me-1"></i> Jabatan: <b><?= htmlspecialchars($user['nama_role']); ?></b></span>
                    </div>
                </div>
                <a href="riwayat_aktivitas.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Bagian Tabel Full Riwayat -->
    <div class="p-4">
        <h5 class="fw-bold mb-4 text-dynamic">
            <i class="bi bi-list-check text-success me-2"></i> Rekam Jejak Aktivitas (Buku Induk)
        </h5>
        
        <div class="table-responsive">
            <table class="table table-custom table-hover">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">No</th>
                        <th style="width: 25%;">Waktu Eksekusi</th>
                        <th style="width: 25%;">Jenis Aktivitas</th>
                        <th style="width: 45%;">Detail / Deskripsi Lengkap</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_full = mysqli_query($koneksi, "
                        SELECT * FROM tabel_riwayat_aktivitas 
                        WHERE id_pengguna = '$id_user' 
                        ORDER BY waktu_aktivitas DESC
                    ");
                    $no = 1;
                    if (mysqli_num_rows($q_full) > 0) {
                        while ($row = mysqli_fetch_assoc($q_full)) {
                            $waktu = date('d M Y, H:i:s', strtotime($row['waktu_aktivitas']));
                            
                            // Badge Warna sesuai jenis aktivitas
                            $jenis = strtolower($row['jenis_aktivitas']);
                            $badge_class = 'bg-primary';
                            if (strpos($jenis, 'login') !== false || strpos($jenis, 'masuk') !== false) $badge_class = 'bg-success';
                            if (strpos($jenis, 'logout') !== false || strpos($jenis, 'keluar') !== false) $badge_class = 'bg-secondary';
                            if (strpos($jenis, 'hapus') !== false || strpos($jenis, 'abaikan') !== false) $badge_class = 'bg-danger';
                            if (strpos($jenis, 'edit') !== false || strpos($jenis, 'ubah') !== false) $badge_class = 'bg-warning text-dark';
                            
                            echo "<tr>";
                            echo "<td class='text-center text-muted'>{$no}</td>";
                            echo "<td><span class='fw-semibold text-dynamic'><i class='bi bi-calendar-event me-1 text-muted'></i> {$waktu}</span></td>";
                            echo "<td><span class='badge {$badge_class} bg-opacity-10 text-dynamic border px-2 py-1'>{$row['jenis_aktivitas']}</span></td>";
                            echo "<td class='text-muted small' style='line-height: 1.5;'>{$row['deskripsi']}</td>";
                            echo "</tr>";
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center text-muted py-5'>Belum ada rekam jejak untuk pengguna ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../layout/footer.php'; ?>