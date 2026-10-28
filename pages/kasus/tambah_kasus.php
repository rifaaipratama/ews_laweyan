<?php

session_start();



if (!isset($_SESSION['id_pengguna'])) {

    header("Location: ../../auth/login.php");

    exit;

}



require_once '../../config/koneksi.php';

/** @var mysqli $koneksi */



// Ambil data untuk Header/Sidebar

$id_role      = $_SESSION['id_role'];

$nama_lengkap = $_SESSION['nama_lengkap'];



$query_role = mysqli_query($koneksi, "SELECT nama_role FROM tabel_role WHERE id_role = '$id_role'");

$nama_role  = ($row = mysqli_fetch_assoc($query_role)) ? $row['nama_role'] : "User";



// === LOGIKA FILTER PENCARIAN ===

$keyword_cari  = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';

$filter_gender = isset($_GET['gender']) ? mysqli_real_escape_string($koneksi, $_GET['gender']) : '';



// Susun string klausa WHERE secara dinamis

$where_clauses = [];

if (!empty($keyword_cari)) {

    $where_clauses[] = "(nik_pasien LIKE '%$keyword_cari%' OR nama_pasien LIKE '%$keyword_cari%')";

}

if (!empty($filter_gender)) {

    $where_clauses[] = "jenis_kelamin = '$filter_gender'";

}



// Gabungkan klausa WHERE jika ada filter yang aktif

$where_sql = "";

if (count($where_clauses) > 0) {

    $where_sql = "WHERE " . implode(" AND ", $where_clauses);

}

// ===============================



include '../../layout/header.php';

include '../../layout/sidebar.php';

?>



<style>

    .panel-card {

        background-color: var(--bg-panel);

        border: 1px solid var(--border-color);

        border-radius: 12px;

        box-shadow: 0 4px 20px var(--shadow-color);

    }

   

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

        white-space: nowrap;

    }

    .table-custom tbody td {

        background-color: var(--bg-panel);

        color: var(--text-color) !important;

        border-bottom: 1px solid var(--border-color);

        vertical-align: middle;

    }

    [data-theme="light"] .table-custom.table-hover tbody tr:hover td { background-color: rgba(0, 0, 0, 0.03); }

    [data-theme="dark"] .table-custom.table-hover tbody tr:hover td { background-color: rgba(255, 255, 255, 0.05); }



    .filter-box {

        background-color: rgba(13, 110, 253, 0.05); /* Soft Blue background */

        border: 1px solid rgba(13, 110, 253, 0.2);

        border-radius: 10px;

    }

</style>



<div id="main-content">



    <?php include '../../layout/topbar.php'; ?>



    <!-- HEADER & TOMBOL AKSI -->

    <div class="row mb-4 align-items-center mt-2">

        <div class="col-md-7">

            <h4 class="text-dynamic fw-bold mb-1">

                <i class="bi bi-search me-2 text-primary"></i> Cari Data Pasien

            </h4>

            <p class="mb-0" style="color: var(--text-color); opacity: 0.75; font-size: 0.9rem;">

                Pilih pasien dari database untuk ditambahkan catatan kasus baru.

            </p>

        </div>

        <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">

            <a href="index.php" class="btn btn-outline-secondary shadow-sm">Batal</a>

            <!-- LOMPAT KE MENU PENDAFTARAN PASIEN BARU -->

            <a href="../data_riwayat_pasien/tambah_pasien.php" class="btn btn-info text-white shadow-sm fw-medium">

                <i class="bi bi-person-plus-fill me-1"></i> Daftarkan Pasien Baru

            </a>

        </div>

    </div>



    <!-- PANEL FILTER & PENCARIAN -->

    <div class="row mb-4">

        <div class="col-12">

            <div class="filter-box p-3">

                <form action="tambah_kasus.php" method="GET" class="row g-3 align-items-end">

                    <div class="col-md-6">

                        <label class="form-label small fw-semibold text-dynamic mb-1">Cari NIK / Nama Pasien</label>

                        <div class="input-group">

                            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>

                            <input type="text" class="form-control border-start-0 ps-0" name="cari" placeholder="Ketik NIK atau Nama Pasien..." value="<?= htmlspecialchars($keyword_cari); ?>" autofocus>

                        </div>

                    </div>

                   

                    <div class="col-md-3">

                        <label class="form-label small fw-semibold text-dynamic mb-1">Filter Gender</label>

                        <select class="form-select" name="gender">

                            <option value="">Semua</option>

                            <option value="L" <?= ($filter_gender == 'L') ? 'selected' : ''; ?>>Laki-laki (L)</option>

                            <option value="P" <?= ($filter_gender == 'P') ? 'selected' : ''; ?>>Perempuan (P)</option>

                        </select>

                    </div>



                    <div class="col-md-3 d-flex gap-2">

                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill me-1"></i> Cari</button>

                        <?php if(!empty($keyword_cari) || !empty($filter_gender)): ?>

                            <a href="tambah_kasus.php" class="btn btn-outline-secondary w-100" title="Reset Pencarian"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>

                        <?php endif; ?>

                    </div>

                </form>

            </div>

        </div>

    </div>



    <!-- TABEL DATA PASIEN (HASIL PENCARIAN) -->

    <div class="row">

        <div class="col-12">

            <div class="panel-card p-4">

                <div class="table-responsive">

                    <table class="table table-custom table-hover">

                        <thead>

                            <tr>

                                <th>NIK Pasien</th>

                                <th>Nama Lengkap</th>

                                <th>L/P</th>

                                <th>Usia</th>

                                <th>Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php

                            // Mengambil data unik pasien (karena 1 pasien bisa punya banyak kasus sebelumnya)

                            $query_pasien = "

                                SELECT

                                    nik_pasien,

                                    nama_pasien,

                                    jenis_kelamin,

                                    tanggal_lahir

                                FROM tabel_kasus

                                $where_sql

                                GROUP BY nik_pasien, nama_pasien, jenis_kelamin, tanggal_lahir

                                ORDER BY nama_pasien ASC

                                LIMIT 50

                            "; // Dibatasi 50 agar tidak berat, admin bisa pakai fitur search

                           

                            $result_pasien = mysqli_query($koneksi, $query_pasien);



                            if ($result_pasien && mysqli_num_rows($result_pasien) > 0) {

                                while ($row = mysqli_fetch_assoc($result_pasien)) {

                                   

                                    // Hitung Usia

                                    $usia = "-";

                                    if (!empty($row['tanggal_lahir'])) {

                                        $tgl_lahir = new DateTime($row['tanggal_lahir']);

                                        $hari_ini = new DateTime('today');

                                        $usia = $hari_ini->diff($tgl_lahir)->y . " Thn";

                                    }



                                    $jk_badge = ($row['jenis_kelamin'] == 'L') ?

                                                '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2">L</span>' :

                                                '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2">P</span>';



                                   // === LOGIKA PENYAMARAN NIK ===
                                    $nik_asli = $row['nik_pasien'];
                                    // Ambil 4 digit awal dan 4 digit akhir
                                    $nik_samar = substr($nik_asli, 0, 4) . '********' . substr($nik_asli, -4);
                                    
                                    echo "<tr>";
                                    echo "<td class='text-muted fw-medium' style='font-family: monospace;' title='" . htmlspecialchars($nik_asli) . "'>" . htmlspecialchars($nik_samar) . "</td>";

                                    echo "<td class='fw-bold'>" . htmlspecialchars($row['nama_pasien']) . "</td>";

                                    echo "<td>" . $jk_badge . "</td>";

                                    echo "<td>" . $usia . "</td>";

                                   

                                    // TOMBOL PILIH PASIEN (Akan melempar NIK ke halaman form_tambah_kasus.php)

                                    echo "<td>

                                            <a href='form_tambah_kasus.php?nik=" . urlencode($row['nik_pasien']) . "' class='btn btn-sm btn-primary shadow-sm rounded-pill px-3'>

                                                <i class=" . '"bi bi-check2-circle me-1"' . "></i> Pilih & Tambah Kasus

                                            </a>

                                          </td>";

                                    echo "</tr>";

                                }

                            } else {

                                echo "<tr><td colspan='5' class='text-center text-muted py-5'>

                                        <i class='bi bi-person-x display-6 d-block mb-3 opacity-50'></i>

                                        Data pasien tidak ditemukan.<br>

                                        <a href='../data_riwayat_pasien/tambah_pasien.php' class='btn btn-sm btn-info text-white mt-3'>Daftarkan Pasien Baru</a>

                                      </td></tr>";

                            }

                            ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>



<?php include '../../layout/footer.php'; ?>



