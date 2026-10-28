# 🛡️ SMART EWS LAWEYAN

**Sistem Surveilans Digital dengan Ambang Peringatan Adaptif, Peta Risiko Spasial, Portal Publik Real-Time, dan EWS AI Co-Pilot**

SMART EWS (SIAGA LAWEYAN) merupakan platform *Early Warning System* berbasis web terpadu untuk mendeteksi, memantau, serta mengantisipasi potensi Kejadian Luar Biasa (KLB) penyakit menular endemik di tingkat lokal secara proaktif. Platform ini mengolaborasikan komputasi algoritma *Adaptive Threshold*, pemetaan spasial GIS 11 kelurahan, portal publik berita kesehatan real-time, dan asisten kecerdasan buatan (*EWS AI Co-Pilot*) sebagai *Decision Support System* (DSS).

---

## 🚀 Fitur Unggulan Sistem

### 1. 🔔 Pusat Notifikasi Real-Time & Manajemen Alert (New Feature)
- **Push Notification Peringatan Dini**: Notifikasi otomatis saat sistem mendeteksi dinamika kasus epidemiologi harian.
- **Deteksi Tren Dinamis (Kenaikan & Penurunan Kasus)**: Admin dan Petugas mendapatkan pemberitahuan instan saat terjadi lonjakan kasus baru maupun tren penurunan kasus di wilayah kelurahan.
- **Kategori Status Alert Kontekstual**:
  - 🟢 **Sembuh / Aman / Penurunan**: Peringatan pemulihan zona atau penurunan tren transmisi kasus.
  - 🟡 **Waspada**: Peringatan saat laju kasus mendekati ambang batas adaptif (*warning threshold*).
  - 🔴 **Potensi Wabah**: Peringatan bahaya tingkat tinggi (*outbreak alert*) saat jumlah kasus melampaui *adaptive threshold*.
- **Buku Arsip & Verifikasi Alert**: Pusat riwayat notifikasi terpusat untuk memverifikasi, menandai status dibaca/ditindaklanjuti, dan menelusuri pemicu alert per kelurahan.

### 2. 🌐 Portal Landing Page Publik (Modern Public Gateway)
- **Antarmuka Publik Non-Login**: Halaman transparan untuk masyarakat umum berisi *overview* sistem, pilar teknologi surveilans, serta manfaat bagi stakeholder.
- **Berita Kesehatan Real-Time (Live Feed)**: Integrasi berita kesehatan otomatis dari kanal resmi via GNews API dan cURL RSS Feed (Antara News / Kompas / detikHealth).
- **Akses Portal Petugas Presisi**: Modul otentikasi terenkripsi untuk beralih ke sesi operasional petugas dan administrator.

### 3. 🤖 SMART EWS AI Co-Pilot (10 Fitur Analitis Cerdas)
- **Analisis Real-Time Command Center**: Membaca *live database* MySQL untuk menyajikan total kasus aktif, tren harian, dan episentrum kelurahan.
- **Smart Guardrail Domain Protection**: Proteksi *system prompt* yang memastikan AI berfokus khusus pada domain epidemiologi dan EWS kesehatan.
- **Export Laporan Otomatis (PDF & Excel)**: Menyusun ringkasan rapat naratif dan menyediakan tombol cetak/unduh dokumen PDF dan Excel secara instan.
- **Filter Waktu & Penyakit Kustom**: Analisis tren lonjakan kasus per penyakit dan kelurahan dalam rentang 30, 60, atau 90 hari.
- **Prediksi Deteksi Dini (EWS Anomaly Detection)**: Membandingkan tren kenaikan kasus harian terhadap *baseline* historis untuk memproyeksi risiko wabah.
- **Asisten Pembuat Surat Dinas (Dinkes Dispatch)**: Otomatisasi draf naskah dinas resmi pelaporan kewaspadaan dini ke Dinas Kesehatan Kota Surakarta.
- **Pelacak Klaster Penularan (Cluster Tracking)**: Deteksi pemusatan kasus aktif pada wilayah kelurahan/RT berdekatan untuk mengidentifikasi penularan lokal.
- **Simulasi Dampak Intervensi (What-If Scenario)**: Kalkulasi estimasi penurunan laju kasus jika tindakan Fogging dan PSN 3M Plus dieksekusi dalam 48 jam.
- **Climate & Spatial Correlation Insight**: Analisis keterkaitan lonjakan penyakit lingkungan (DBD, Chikungunya) dengan pola curah hujan dan genangan air.
- **Rekomendasi Alokasi Logistik Obat & SOP**: Saran alokasi stok Abate, Kaporit, serta jadwal penyuluhan lapangan berbasis jumlah kasus aktif.

### 4. 🗺️ Fitur Inti Surveilans & Integrasi Digital
- **Interactive GIS Mapping**: Visualisasi pemetaan status kerawanan 11 kelurahan di Kecamatan Laweyan (Zona Merah/Potensi Wabah, Kuning/Waspada, Hijau/Aman) menggunakan Leaflet.js & OpenStreetMap.
- **Adaptive Threshold Calibration**: Pengaturan parameter Batas Waspada dan Batas Wabah dinamis berbasis rentang waktu hitung 7 hingga 30 hari.
- **Smart Patient Lookup & Single-Entry**: Pencarian cepat NIK/Nama pasien berbasis koding ICD-10 untuk mencegah duplikasi data rekam medis.
- **Simulasi SATUSEHAT Kemenkes**: Generator *payload* JSON FHIR R4 (*Encounter* & *Condition*) untuk kesiapan integrasi platform nasional SATUSEHAT Kemenkes RI.
- **Security & Audit Trail**: Proteksi *Role-Based Access Control* (RBAC) serta pencatatan log riwayat aktivitas pengguna hingga satuan detik.

## 🛠️ Stack Teknologi & Dependensi

- **Core Platform**: Web-Based Responsive Application (PHP Native 8.x & MySQL Server)
- **Front-End & Styling**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3, Plus Jakarta Sans, Glassmorphism UI
- **AI Engine**: LLM Integration (Groq API - `openai/gpt-oss-120b` Engine)
- **Geospatial Mapping**: Leaflet.js & OpenStreetMap API (Polygon Presisi 11 Kelurahan Laweyan)
- **External Integration**: GNews API, Automated cURL RSS Feed, SATUSEHAT FHIR R4 JSON Schema

---

## 💻 Panduan Instalasi & Pengujian Lokal


1. Pengaturan Database

Buka phpMyAdmin (http://localhost/phpmyadmin).

Buat database baru bernama ews_laweyan.

Import file ews_laweyan.sql yang tersedia di root proyek ini.


2. Konfigurasi Lingkungan (.env)

Salin file `.env.example` menjadi `.env`.
   - Sesuaikan kredensial koneksi database server lokal Anda serta kunci API LLM yang digunakan:
     ```env
     DB_HOST=localhost
     DB_USER=root
     DB_PASS=
     DB_NAME=ews_laweyan
     
     # Konfigurasi LLM API (Wajib diisi untuk fitur EWS AI Co-Pilot)
     GROQ_API_KEY=your_groq_api_key_here
     ```
   - *Catatan API Key*: Sistem default dirancang menggunakan **Groq API** (`openai/gpt-oss-120b`). Anda dapat memasukkan Groq API Key pribadi yang diperoleh gratis dari [Groq Console](https://console.groq.com). Sistem ini juga mendukung kustomisasi ke provider LLM API lain (seperti OpenAI, Claude, atau Gemini) dengan menyesuaikan `apiUrl` dan format *payload* pada file `pages/dashboard/ai_assistant.php`.

3. Jalankan Aplikasi

Pindahkan folder proyek ke web root server lokal (htdocs XAMPP / www Laragon).

Akses melalui browser: http://localhost/ews_laweyan/auth/login.php

Tim Peneliti & Pengembang (Tim Nyoba Daftar)
Ketua Tim: Dimas Rifai Pratama (F25045)

Anggota 1: Wahyu (F24)

Anggota 2: Adit (F24)

Pembimbing: Aliansi Wicaksono, S.Tr.RMIK

Institusi: Politeknik Indonusa Surakarta

1. **Clone Repositori**
   ```bash
   git clone https://github.com/rifaaipratama/ews_laweyan.git