<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi berakhir']);
    exit;
}

require_once '../../config/koneksi.php';
/** @var mysqli $koneksi */

// Ambil ID Kasus dari Request
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$id_kasus = $input['id_kasus'] ?? '';

// 1. Coba Ambil Data Kasus dari Database
$nama_pasien = "Pasien EWS";
$nama_penyakit = "Penyakit Menular";
$kode_icd10 = "A90";
$nama_kelurahan = "Laweyan";

if (!empty($id_kasus)) {
    $q = "SELECT k.*, p.nama_populer, p.kode_icd10, kel.nama_kelurahan 
          FROM tabel_kasus k
          LEFT JOIN tabel_penyakit p ON k.id_penyakit = p.id_penyakit
          LEFT JOIN tabel_kelurahan kel ON k.id_kelurahan = kel.id_kelurahan
          WHERE k.id_kasus = '$id_kasus' LIMIT 1";
    
    $res = @mysqli_query($koneksi, $q);
    if ($res && $data = mysqli_fetch_assoc($res)) {
        $nama_pasien = $data['nama_pasien'] ?? $nama_pasien;
        $nama_penyakit = $data['nama_populer'] ?? $nama_penyakit;
        $kode_icd10 = $data['kode_icd10'] ?? $kode_icd10;
        $nama_kelurahan = $data['nama_kelurahan'] ?? $nama_kelurahan;
    }
}

// 2. Generate UUID Acak Standar FHIR Kemenkes
function generateUuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

$satusehat_encounter_id = "ENC-" . generateUuid();
$satusehat_condition_id = "COND-" . generateUuid();
$timestamp_fhir = date('Y-m-d\TH:i:s+07:00');

// 3. Susun Payload JSON Format FHIR R4 Standard Kemenkes
$fhirPayload = [
    "resourceType" => "Bundle",
    "type" => "transaction",
    "entry" => [
        [
            "fullUrl" => "urn:uuid:" . $satusehat_encounter_id,
            "resource" => [
                "resourceType" => "Encounter",
                "id" => $satusehat_encounter_id,
                "status" => "finished",
                "class" => [
                    "system" => "http://terminology.hl7.org/CodeSystem/v3-ActCode",
                    "code" => "AMB",
                    "display" => "ambulatory"
                ],
                "subject" => [
                    "reference" => "Patient/100000012345",
                    "display" => $nama_pasien
                ],
                "period" => [
                    "start" => $timestamp_fhir,
                    "end" => $timestamp_fhir
                ],
                "location" => [
                    [
                        "location" => [
                            "display" => "Puskesmas Laweyan - " . $nama_kelurahan
                        ]
                    ]
                ]
            ]
        ],
        [
            "fullUrl" => "urn:uuid:" . $satusehat_condition_id,
            "resource" => [
                "resourceType" => "Condition",
                "id" => $satusehat_condition_id,
                "clinicalStatus" => [
                    "coding" => [
                        [
                            "system" => "http://terminology.hl7.org/CodeSystem/condition-clinical",
                            "code" => "active"
                        ]
                    ]
                ],
                "code" => [
                    "coding" => [
                        [
                            "system" => "http://hl7.org/fhir/sid/icd-10",
                            "code" => $kode_icd10,
                            "display" => $nama_penyakit
                        ]
                    ]
                ],
                "subject" => [
                    "reference" => "Patient/100000012345"
                ],
                "encounter" => [
                    "reference" => "urn:uuid:" . $satusehat_encounter_id
                ]
            ]
        ]
    ]
];

// Simulasi Delay Latency Server Kemenkes (0.3 Detik)
usleep(300000);

echo json_encode([
    'status' => 'success',
    'http_code' => 201,
    'message' => 'Data Berhasil Disinkronisasi ke SATUSEHAT Platform',
    'satusehat_encounter_id' => $satusehat_encounter_id,
    'satusehat_condition_id' => $satusehat_condition_id,
    'payload_fhir' => $fhirPayload
], JSON_PRETTY_PRINT);