<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode(['status' => 'error', 'reply' => 'Sesi Anda telah berakhir. Silakan login kembali.']);
    exit;
}

// 1. CONFIGURATION: GROQ API KEY & ENDPOINT
$apiKey = getenv('GROQ_API_KEY') ?: 'YOUR_GROQ_API_KEY_HERE';
$apiUrl = "https://api.groq.com/openai/v1/chat/completions";

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$userMessage = trim($input['message'] ?? '');
$userMessageLower = strtolower($userMessage);

if (empty($userMessage)) {
    echo json_encode(['status' => 'error', 'reply' => 'Pesan tidak boleh kosong.']);
    exit;
}

// 2. KONEKSI DATABASE & PENYEDIAAN CONTEXT DATA RIIL
require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// A. Kasus Baru Hari Ini
$qKasusHariIni = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tabel_kasus WHERE DATE(tanggal_lapor) = CURDATE() AND is_deleted = 0");
$kasusHariIni = mysqli_fetch_assoc($qKasusHariIni)['total'] ?? 0;

// B. Total Kasus Aktif Riil di DB (Suspek & Terkonfirmasi)
$qKasusAktif = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tabel_kasus WHERE is_deleted = 0 AND status_kasus IN ('suspek', 'terkonfirmasi')");
$kasusAktif = mysqli_fetch_assoc($qKasusAktif)['total'] ?? 0;

// C. Transaksi Laporan Terakhir Masuk
$qKasusTerbaru = mysqli_query($koneksi, "
    SELECT k.tanggal_lapor, k.nama_pasien, p.nama_populer, kel.nama_kelurahan, k.status_kasus
    FROM tabel_kasus k
    JOIN tabel_penyakit p ON k.id_penyakit = p.id_penyakit
    JOIN tabel_kelurahan kel ON k.id_kelurahan = kel.id_kelurahan
    WHERE k.is_deleted = 0 
    ORDER BY k.id_kasus DESC LIMIT 1
");
$dataTerbaru = mysqli_fetch_assoc($qKasusTerbaru);
$laporanTerakhir = $dataTerbaru 
    ? "Pasien {$dataTerbaru['nama_pasien']} ({$dataTerbaru['nama_populer']}) di Kel. {$dataTerbaru['nama_kelurahan']} (Status: {$dataTerbaru['status_kasus']})" 
    : "Belum ada laporan terbaru";

// D. Penyakit Terbanyak dari Kasus Aktif
$qRincianPenyakit = mysqli_query($koneksi, "
    SELECT p.nama_populer, COUNT(k.id_kasus) as jumlah 
    FROM tabel_kasus k 
    JOIN tabel_penyakit p ON k.id_penyakit = p.id_penyakit 
    WHERE k.is_deleted = 0 AND k.status_kasus IN ('suspek', 'terkonfirmasi')
    GROUP BY k.id_penyakit 
    ORDER BY jumlah DESC LIMIT 3
");
$rincianPenyakit = [];
while ($row = mysqli_fetch_assoc($qRincianPenyakit)) {
    $rincianPenyakit[] = $row['nama_populer'] . " (" . $row['jumlah'] . " kasus)";
}
$teksPenyakit = !empty($rincianPenyakit) ? implode(", ", $rincianPenyakit) : "Tidak ada";

// E. Episentrum Kelurahan Terbanyak
$qKelurahanTerbanyak = mysqli_query($koneksi, "
    SELECT kel.nama_kelurahan, COUNT(k.id_kasus) as jumlah
    FROM tabel_kasus k
    JOIN tabel_kelurahan kel ON k.id_kelurahan = kel.id_kelurahan
    WHERE k.is_deleted = 0 AND k.status_kasus IN ('suspek', 'terkonfirmasi')
    GROUP BY k.id_kelurahan
    ORDER BY jumlah DESC LIMIT 2
");
$rincianKelurahan = [];
while ($rowKel = mysqli_fetch_assoc($qKelurahanTerbanyak)) {
    $rincianKelurahan[] = "Kel. " . $rowKel['nama_kelurahan'] . " (" . $rowKel['jumlah'] . " kasus)";
}
$teksKelurahan = !empty($rincianKelurahan) ? implode(", ", $rincianKelurahan) : "Semua kelurahan relatif aman";

// F. Alert Peringatan Terakhir
$qAlertTerbaru = mysqli_query($koneksi, "
    SELECT pesan_alert, tingkat_alert 
    FROM tabel_alert 
    ORDER BY id_alert DESC LIMIT 1
");
$dataAlert = mysqli_fetch_assoc($qAlertTerbaru);
$alertTerakhir = $dataAlert ? "{$dataAlert['pesan_alert']} ({$dataAlert['tingkat_alert']})" : "Status Aman";


// =========================================================================
// 3. FITUR KHUSUS LOKAL (EXPORTS, DRAF SURAT, SIMULASI, KLASTER)
// =========================================================================

// FITUR 1: CETAK / EXPORT LAPORAN OTOMATIS (PDF / EXCEL)
if (strpos($userMessageLower, 'cetak') !== false || strpos($userMessageLower, 'export') !== false || strpos($userMessageLower, 'laporan') !== false) {
    $reply = "Draf ringkasan naratif harian Puskesmas berhasil disusun berdasarkan data real-time ($kasusAktif kasus aktif).\n\n"
           . "Silakan unduh berkas laporan rapat melalui tautan berikut:\n"
           . "• <a href='../kasus/export_pdf.php' target='_blank' class='btn btn-sm btn-outline-danger mt-1 me-1'><i class='bi bi-file-earmark-pdf-fill me-1'></i>Unduh Ringkasan PDF</a>\n"
           . "• <a href='../kasus/export_excel.php' target='_blank' class='btn btn-sm btn-outline-success mt-1'><i class='bi bi-file-earmark-excel-fill me-1'></i>Unduh Data Excel</a>";

    echo json_encode(['status' => 'success', 'reply' => $reply]);
    exit;
}

// FITUR 5: ASISTEN PEMBUAT SURAT REKOMENDASI & DINKES DISPATCH
if (strpos($userMessageLower, 'surat') !== false || strpos($userMessageLower, 'draf') !== false || strpos($userMessageLower, 'dinkes') !== false) {
    $tgl = date('d F Y');
    $reply = "[DRAF SURAT KEWASPADAAN DINI SURVEILANS]\n\n"
           . "Hal: Laporan Lintas Sektor & Kewaspadaan Dini EWS Laweyan\n"
           . "Tanggal: $tgl\n"
           . "Kepada Yth: Kepala Dinas Kesehatan Kota Surakarta\n\n"
           . "Dengan hormat,\n"
           . "Melaporkan hasil pemantauan Sistem Early Warning System (SMART EWS) Kecamatan Laweyan:\n"
           . "1. Total kasus aktif saat ini sebanyak $kasusAktif kasus ($teksPenyakit).\n"
           . "2. Wilayah prioritas pemantauan berada di $teksKelurahan.\n"
           . "3. Merekomendasikan pelaksanaan Pengasapan (Fogging) dan Pemantauan Jentik Berkala serentak.\n\n"
           . "Demikian laporan ini disampaikan untuk menjadi bahan kebijakan intervensi lapangan.\n"
           . "(Draf ini siap disalin ke berkas naskah dinas resmi)";

    echo json_encode(['status' => 'success', 'reply' => $reply]);
    exit;
}

// FITUR 6 & 8: SIMULASI WHAT-IF & FAKTOR IKLIM / FOGGING
if (strpos($userMessageLower, 'fogging') !== false || strpos($userMessageLower, 'simulasi') !== false) {
    $reply = "[SIMULASI DAMPAK INTERVENSI & INSIGHT SPASIAL]\n\n"
           . "• Faktor Lingkungan: Genangan air pasca hujan mempercepat masa inkubasi vektor di wilayah $teksKelurahan.\n"
           . "• Proyeksi Intervensi: Jika kegiatan PSN 3M Plus dan Fogging fokus dilakukan dalam 48 jam ke depan, laju penularan diperkirakan dapat ditekan hingga 60%–70% dalam kurun 14 hari.";

    echo json_encode(['status' => 'success', 'reply' => $reply]);
    exit;
}


// =========================================================================
// 4. INTEGRASI GROQ AI ENGINE UNTUK ANALISIS & PERTANYAAN UMUM EWS
// =========================================================================

// SYSTEM PROMPT KETAT (BATASAN DOMAIN EWS SANITAS/EPIDEMIOLOGI)
$systemPrompt = "Kamu adalah 'EWS AI Co-Pilot', sistem kecerdasan buatan khusus analisis epidemiologi dan kewaspadaan dini kesehatan masyarakat di Kecamatan Laweyan (SMART EWS). "
    . "BATASAN TUGAS TERSIAP & TEGAS: "
    . "1. Kamu HANYA merespon pertanyaan yang berkaitan dengan epidemiologi, surveilans kesehatan, analisis penyakit, data kasus EWS, status zona kelurahan, prediksi lonjakan kasus, rekomendasi stok obat/tindakan, dan rekomendasi penanggulangan kesehatan masyarakat. "
    . "2. JIKA pengguna bertanya hal umum di luar topik EWS/Kesehatan (misalnya: sepak bola, CR7, politik, resep masakan, kuis, atau percakapan umum/iseng lainnya), kamu WAJIB menolak secara tegas namun profesional. "
    . "   Contoh penolakan: 'Mohon maaf, sebagai EWS AI Co-Pilot saya hanya dikhususkan untuk menjawab pertanyaan terkait epidemiologi, analisis data kasus, dan sistem kewaspadaan dini kesehatan di Kecamatan Laweyan.' "
    . "3. ATURAN RESPONS: Selalu jawab dalam PARAGRAF RINGKAS (maksimal 2-3 kalimat santun, padat, presisi, dan profesional). "
    . "4. DILARANG keras menggunakan tabel Markdown, garis pemisah, atau simbol blockquote (>). "
    . "DATA LIVE COMMAND CENTER LAWEYAN SAAT INI: "
    . "- Total Kasus Aktif: {$kasusAktif} kasus. "
    . "- Kasus Baru Hari Ini: {$kasusHariIni} kasus. "
    . "- Laporan Kasus Paling Baru: {$laporanTerakhir}. "
    . "- Penyakit Terbanyak: {$teksPenyakit}. "
    . "- Episentrum Utama: {$teksKelurahan}. "
    . "- Status Alert Terakhir: {$alertTerakhir}.";

// PAYLOAD GROQ
$payload = [
    "model" => "openai/gpt-oss-120b",
    "messages" => [
        [
            "role" => "system",
            "content" => $systemPrompt
        ],
        [
            "role" => "user",
            "content" => $userMessage
        ]
    ],
    "temperature" => 0.2,
    "max_tokens" => 350
];

// PANGGIL API VIA cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $responseData = json_decode($response, true);
    $replyText = $responseData['choices'][0]['message']['content'] ?? 'Maaf, terjadi kendala saat mengolah jawaban.';
    echo json_encode(['status' => 'success', 'reply' => $replyText]);
} else {
    echo json_encode([
        'status' => 'error', 
        'reply' => 'Gagal terhubung ke layanan Groq AI. Kode Status: ' . $httpCode
    ]);
}