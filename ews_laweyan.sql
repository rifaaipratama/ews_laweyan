-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 28, 2026 at 02:05 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ews_laweyan`
--

-- --------------------------------------------------------

--
-- Table structure for table `tabel_alert`
--

CREATE TABLE `tabel_alert` (
  `id_alert` int NOT NULL,
  `id_pengguna` int DEFAULT NULL COMMENT 'Relasi ke petugas pembuat/pemicu alert',
  `id_kelurahan` int NOT NULL COMMENT 'Relasi ke lokasi yang memicu alert',
  `id_penyakit` int NOT NULL COMMENT 'Relasi ke penyakit wabahnya',
  `tingkat_alert` enum('waspada','potensi_wabah','penurunan') NOT NULL COMMENT 'Kuning, Merah, atau Hijau',
  `jumlah_kasus_aktual` int NOT NULL COMMENT 'Jumlah kasus di periode saat ini',
  `ambang_batas_digunakan` int NOT NULL COMMENT 'Angka threshold yang tertembus',
  `periode_hari_digunakan` int NOT NULL COMMENT 'Rentang waktu (misal: 7 hari)',
  `kasus_periode_sebelumnya` int NOT NULL COMMENT 'Jumlah kasus 7 hari sebelumnya',
  `persentase_kenaikan` decimal(5,2) NOT NULL COMMENT 'Nilai % kenaikan',
  `pesan_alert` varchar(255) NOT NULL COMMENT 'Teks yang akan dibaca petugas (misal: Kenaikan Kasus Diare)',
  `status_baca` enum('belum','sudah') DEFAULT 'belum' COMMENT 'Untuk menghilangkan tanda merah di ikon lonceng jika sudah diklik',
  `status_verifikasi` enum('menunggu','valid','abaikan') DEFAULT 'menunggu' COMMENT 'Status validasi dari atasan',
  `id_verifikator` int DEFAULT NULL COMMENT 'ID User yang melakukan verifikasi',
  `waktu_verifikasi` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu peringatan dipicu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tabel_alert`
--

INSERT INTO `tabel_alert` (`id_alert`, `id_pengguna`, `id_kelurahan`, `id_penyakit`, `tingkat_alert`, `jumlah_kasus_aktual`, `ambang_batas_digunakan`, `periode_hari_digunakan`, `kasus_periode_sebelumnya`, `persentase_kenaikan`, `pesan_alert`, `status_baca`, `status_verifikasi`, `id_verifikator`, `waktu_verifikasi`, `created_at`) VALUES
(1, 2, 1, 1, 'potensi_wabah', 0, 0, 0, 0, 0.00, 'POTENSI WABAH DBD', 'sudah', 'menunggu', NULL, NULL, '2026-05-20 02:45:00'),
(2, 2, 2, 1, 'waspada', 0, 0, 0, 0, 0.00, 'WASPADA DBD', 'sudah', 'menunggu', NULL, NULL, '2026-07-31 08:39:41'),
(3, 2, 6, 2, 'waspada', 0, 0, 0, 0, 0.00, 'WASPADA HIV/AIDS', 'sudah', 'menunggu', NULL, NULL, '2026-08-01 08:38:00'),
(4, 2, 10, 6, 'waspada', 2, 2, 7, 0, 100.00, 'WASPADA Hipertensi', 'sudah', 'valid', 2, '2026-08-11 09:06:00', '2026-08-11 02:01:19'),
(5, 2, 4, 2, 'waspada', 2, 2, 7, 0, 100.00, 'WASPADA HIV/AIDS', 'sudah', 'valid', 2, '2026-08-11 10:28:41', '2026-08-11 03:26:11'),
(6, 2, 4, 2, 'penurunan', 1, 2, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-08-11 03:28:05'),
(7, 2, 11, 2, 'waspada', 2, 2, 7, 0, 100.00, 'WASPADA HIV/AIDS', 'sudah', 'menunggu', NULL, NULL, '2026-08-15 03:00:28'),
(8, 2, 9, 2, 'waspada', 2, 2, 7, 0, 100.00, 'WASPADA HIV/AIDS', 'sudah', 'menunggu', NULL, NULL, '2026-08-16 02:28:58'),
(9, 2, 4, 2, 'waspada', 2, 2, 7, 0, 100.00, 'WASPADA HIV/AIDS', 'sudah', 'menunggu', NULL, NULL, '2026-08-16 02:39:54'),
(10, 2, 9, 2, 'penurunan', 1, 2, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-08-16 03:31:34'),
(11, 2, 8, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-08-16 08:25:34'),
(12, NULL, 11, 2, 'waspada', 3, 2, 7, 0, 100.00, 'WASPADA HIV/AIDS', 'sudah', 'valid', 2, '2026-08-19 09:44:11', '2026-08-19 02:27:57'),
(13, NULL, 7, 6, 'waspada', 2, 2, 7, 0, 100.00, 'WASPADA Hipertensi', 'sudah', 'valid', 2, '2026-08-19 09:44:14', '2026-08-19 02:36:24'),
(14, NULL, 10, 6, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Hipertensi', 'sudah', 'valid', NULL, NULL, '2026-08-19 03:04:36'),
(15, NULL, 4, 2, 'penurunan', 1, 2, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-08-19 03:06:23'),
(16, NULL, 6, 2, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-08-19 03:10:44'),
(17, NULL, 9, 2, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-08-19 03:15:08'),
(18, NULL, 7, 6, 'penurunan', 1, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Hipertensi', 'sudah', 'valid', NULL, NULL, '2026-08-19 03:25:41'),
(19, NULL, 2, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-08-19 03:26:03'),
(20, NULL, 1, 2, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-08-26 07:07:34'),
(22, NULL, 8, 2, 'waspada', 2, 2, 7, 0, 100.00, 'WASPADA HIV/AIDS', 'sudah', 'valid', 2, '2026-08-31 11:59:37', '2026-08-31 04:56:58'),
(23, NULL, 7, 6, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Hipertensi', 'sudah', 'valid', NULL, NULL, '2026-08-31 05:09:57'),
(24, NULL, 5, 2, 'waspada', 2, 2, 7, 1, 100.00, 'WASPADA HIV/AIDS', 'sudah', 'valid', 2, '2026-09-07 10:04:13', '2026-08-31 05:17:37'),
(25, NULL, 5, 2, 'potensi_wabah', 5, 5, 7, 1, 400.00, 'POTENSI WABAH HIV/AIDS', 'sudah', 'valid', 2, '2026-09-01 20:37:29', '2026-08-31 07:08:39'),
(26, NULL, 7, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-01 02:06:57'),
(27, NULL, 2, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-01 02:07:06'),
(28, NULL, 6, 2, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-01 02:07:19'),
(29, NULL, 4, 2, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-03 10:17:55'),
(30, NULL, 6, 2, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-06 03:43:09'),
(31, NULL, 9, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-07 03:01:55'),
(32, NULL, 11, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-07 03:02:39'),
(33, NULL, 7, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-07 03:03:08'),
(34, NULL, 4, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-07 03:03:19'),
(35, NULL, 8, 2, 'penurunan', 2, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-07 03:03:43'),
(36, NULL, 10, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-07 03:07:21'),
(37, NULL, 2, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-07 03:07:56'),
(38, NULL, 5, 2, 'penurunan', 2, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-07 03:09:26'),
(39, NULL, 7, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-07 03:10:18'),
(40, NULL, 1, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-07 03:11:02'),
(41, NULL, 7, 6, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Chikungunya', 'sudah', 'valid', NULL, NULL, '2026-09-07 03:11:10'),
(42, NULL, 6, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-07 03:12:41'),
(43, NULL, 2, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-07 03:12:53'),
(44, NULL, 11, 12, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Campak', 'sudah', 'valid', NULL, NULL, '2026-09-09 02:21:25'),
(45, NULL, 5, 3, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Pneumonia', 'sudah', 'valid', NULL, NULL, '2026-09-09 02:21:37'),
(46, NULL, 2, 6, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Chikungunya', 'sudah', 'valid', NULL, NULL, '2026-09-09 02:21:50'),
(47, NULL, 9, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:27:42'),
(48, NULL, 7, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:27:49'),
(49, NULL, 5, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:27:53'),
(50, NULL, 8, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:27:57'),
(51, NULL, 6, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:28:16'),
(52, NULL, 3, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:28:22'),
(53, NULL, 10, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:28:36'),
(54, NULL, 4, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:28:39'),
(55, NULL, 2, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:28:44'),
(56, NULL, 1, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:29:20'),
(57, NULL, 11, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:29:24'),
(58, NULL, 9, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:30:08'),
(59, NULL, 5, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:32:44'),
(60, NULL, 4, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:32:53'),
(61, NULL, 1, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-16 02:42:18'),
(62, NULL, 1, 12, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Campak', 'sudah', 'valid', NULL, NULL, '2026-09-16 03:16:59'),
(63, NULL, 1, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:06:43'),
(64, NULL, 5, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:06:48'),
(65, NULL, 3, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:06:52'),
(66, NULL, 2, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:06:57'),
(67, NULL, 4, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:07:02'),
(68, NULL, 11, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:07:12'),
(69, NULL, 10, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:08:06'),
(70, NULL, 9, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:08:11'),
(71, NULL, 2, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:08:16'),
(72, NULL, 8, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:08:21'),
(73, NULL, 7, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:08:25'),
(74, NULL, 6, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:08:33'),
(75, NULL, 11, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:08:41'),
(76, NULL, 3, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:08:49'),
(77, NULL, 9, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:09:07'),
(78, NULL, 10, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:09:18'),
(79, NULL, 7, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:09:43'),
(80, NULL, 6, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:09:51'),
(81, NULL, 5, 2, 'penurunan', 0, 3, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-17 13:25:34'),
(82, NULL, 9, 6, 'waspada', 2, 2, 7, 0, 100.00, 'WASPADA Chikungunya', 'sudah', 'menunggu', NULL, NULL, '2026-09-17 14:02:41'),
(83, NULL, 7, 2, 'waspada', 2, 2, 7, 0, 100.00, 'WASPADA HIV/AIDS', 'sudah', 'menunggu', NULL, NULL, '2026-09-18 04:05:19'),
(84, NULL, 7, 2, 'potensi_wabah', 5, 5, 7, 1, 400.00, 'POTENSI WABAH HIV/AIDS', 'sudah', 'menunggu', NULL, NULL, '2026-09-18 04:07:20'),
(85, NULL, 8, 6, 'waspada', 2, 2, 7, 0, 100.00, 'WASPADA Chikungunya', 'sudah', 'menunggu', NULL, NULL, '2026-09-23 03:47:57'),
(86, NULL, 7, 2, 'waspada', 4, 2, 7, 3, 33.33, 'WASPADA HIV/AIDS', 'sudah', 'menunggu', NULL, NULL, '2026-09-23 04:09:41'),
(87, NULL, 9, 6, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Chikungunya', 'sudah', 'valid', NULL, NULL, '2026-09-24 03:12:54'),
(88, NULL, 7, 2, 'penurunan', 1, 2, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-24 03:14:21'),
(89, NULL, 7, 6, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Chikungunya', 'sudah', 'valid', NULL, NULL, '2026-09-24 03:14:40'),
(90, NULL, 5, 10, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Malaria', 'sudah', 'valid', NULL, NULL, '2026-09-24 03:14:47'),
(91, NULL, 8, 6, 'penurunan', 1, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Chikungunya', 'sudah', 'valid', NULL, NULL, '2026-09-24 03:15:36'),
(92, NULL, 4, 2, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-09-24 03:17:05'),
(93, NULL, 10, 6, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Chikungunya', 'sudah', 'valid', NULL, NULL, '2026-09-24 03:17:20'),
(94, NULL, 5, 1, 'waspada', 2, 2, 7, 0, 100.00, 'WASPADA DBD', 'sudah', 'menunggu', NULL, NULL, '2026-09-24 03:56:35'),
(95, NULL, 8, 3, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Pneumonia', 'sudah', 'valid', NULL, NULL, '2026-10-26 12:29:08'),
(96, NULL, 5, 1, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK DBD', 'sudah', 'valid', NULL, NULL, '2026-10-26 12:29:14'),
(97, NULL, 8, 6, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Chikungunya', 'sudah', 'valid', NULL, NULL, '2026-10-26 12:30:39'),
(98, NULL, 7, 6, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK Chikungunya', 'sudah', 'valid', NULL, NULL, '2026-10-26 12:30:42'),
(99, NULL, 7, 2, 'penurunan', 0, 2, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-10-26 12:30:51'),
(100, NULL, 4, 2, 'waspada', 2, 2, 7, 0, 100.00, 'WASPADA HIV/AIDS', 'sudah', 'menunggu', NULL, NULL, '2026-10-26 12:51:24'),
(101, NULL, 4, 2, 'penurunan', 1, 2, 7, 0, 0.00, 'KONDISI MEMBAIK HIV/AIDS', 'sudah', 'valid', NULL, NULL, '2026-10-26 12:52:03'),
(102, NULL, 4, 6, 'waspada', 2, 2, 7, 0, 100.00, 'WASPADA Chikungunya', 'belum', 'menunggu', NULL, NULL, '2026-10-28 13:35:00');

-- --------------------------------------------------------

--
-- Table structure for table `tabel_kasus`
--

CREATE TABLE `tabel_kasus` (
  `id_kasus` int NOT NULL,
  `id_pengguna` int NOT NULL COMMENT 'Relasi ke petugas yang menginput data',
  `id_penyakit` int NOT NULL COMMENT 'Relasi ke data penyakit (DBD, ISPA, dll)',
  `id_kelurahan` int NOT NULL COMMENT 'Relasi ke lokasi kejadian',
  `nik_pasien` varchar(16) NOT NULL,
  `nama_pasien` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL DEFAULT 'L',
  `tanggal_lahir` date DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `alamat_lengkap` text,
  `tanggal_lapor` date NOT NULL,
  `tanggal_onset` date DEFAULT NULL,
  `tanggal_diagnosis` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status_kasus` enum('suspek','terkonfirmasi','sembuh','meninggal') DEFAULT 'terkonfirmasi',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tabel_kasus`
--

INSERT INTO `tabel_kasus` (`id_kasus`, `id_pengguna`, `id_penyakit`, `id_kelurahan`, `nik_pasien`, `nama_pasien`, `jenis_kelamin`, `tanggal_lahir`, `no_telepon`, `alamat_lengkap`, `tanggal_lapor`, `tanggal_onset`, `tanggal_diagnosis`, `tanggal_selesai`, `status_kasus`, `is_deleted`, `created_at`, `updated_at`) VALUES
(24, 2, 1, 3, '3372014402900001', 'Ratih Kumalasari', 'P', '1990-02-14', '081234567811', 'Jl. KH. Samanhudi No. 10, Sondakan, Surakarta', '2026-09-01', '2026-08-28', '2026-08-30', '2026-09-05', 'sembuh', 1, '2026-09-01 01:15:00', '2026-09-16 02:29:06'),
(25, 3, 2, 5, '3372016607850002', 'Dewi Anggraini', 'P', '1985-07-26', '081234567822', 'Jl. Ahmad Yani No. 99, Kerten, Surakarta', '2026-09-02', '2026-08-27', '2026-08-31', '2026-09-07', 'sembuh', 1, '2026-09-02 02:30:00', '2026-09-16 02:42:25'),
(26, 2, 1, 9, '3372011105990003', 'Araka Aditya', 'L', '1999-05-11', '081234567833', 'Jl. Kebangkitan Nasional No. 45, Bumi, Surakarta', '2026-09-04', '2026-08-30', '2026-09-02', '2026-09-08', 'sembuh', 1, '2026-09-04 07:20:00', '2026-09-16 02:42:22'),
(28, 2, 1, 11, '3372014408950005', 'Sarah Maharani', 'P', '1995-08-24', '081234567855', 'Jl. Museum No. 12, Sriwedari, Surakarta', '2026-09-08', '2026-09-05', '2026-09-07', '2026-09-09', 'sembuh', 1, '2026-09-08 04:05:00', '2026-09-16 02:42:14'),
(32, 2, 2, 8, '3333990056677789', 'Arga Pratama', 'L', '2005-02-16', '085667889006', 'Jl.Radjiman No.12 Kota Surakarta', '2026-08-31', '2026-08-31', '2026-08-31', '2026-09-07', 'sembuh', 1, '2026-08-31 04:43:24', '2026-09-07 04:22:35'),
(34, 3, 2, 5, '0000006633220060', 'Apriliani Putri Marathesa', 'L', '2000-03-08', '082334567890', 'Jl.Purwosari No.17 Kota Surakarta', '2026-08-31', '2026-08-31', '2026-09-01', '2026-09-05', 'sembuh', 1, '2026-08-31 05:17:04', '2026-09-07 04:24:53'),
(35, 3, 2, 5, '3333300056660088', 'Aida Kusuma Wardani', 'L', '2007-02-03', '082334788566', 'Jl.Raya Sondakan No.11 Kota Surakarta', '2026-08-31', '2026-08-31', '2026-09-02', '2026-09-06', 'sembuh', 1, '2026-08-31 05:17:37', '2026-09-07 04:27:00'),
(39, 2, 6, 2, '3333990056677789', 'Arga Pratama', 'L', '2005-02-16', NULL, NULL, '2026-09-06', '2026-09-09', NULL, '2026-09-09', 'sembuh', 1, '2026-09-09 02:13:58', '2026-09-09 02:21:54'),
(40, 2, 3, 5, '3333300056660088', 'Aida Kusuma Wardani', 'L', '2007-02-03', NULL, NULL, '2026-09-09', '2026-09-07', NULL, '2026-09-09', 'sembuh', 1, '2026-09-09 02:15:20', '2026-09-09 02:21:42'),
(41, 2, 10, 7, '0000006633220060', 'Apriliani Putri Marathesa', 'L', '2000-03-08', NULL, NULL, '2026-09-12', '2026-09-09', NULL, '2026-09-09', 'sembuh', 1, '2026-09-09 02:15:44', '2026-09-09 02:21:17'),
(42, 2, 12, 11, '3333300056660088', 'Aida Kusuma Wardani', 'L', '2007-02-03', NULL, NULL, '2026-09-09', '2026-09-09', NULL, '2026-09-09', 'sembuh', 1, '2026-09-09 02:18:54', '2026-09-09 02:21:29'),
(43, 2, 1, 2, '3372011204050001', 'Aditya Pratama', 'L', '2005-04-12', '082100001111', 'Jl. Perintis Kemerdekaan No. 10, Purwosari, Surakarta', '2026-09-10', '2026-09-06', '2026-09-08', '2026-09-14', 'sembuh', 1, '2026-09-10 02:15:00', '2026-09-16 02:33:01'),
(44, 3, 1, 3, '3372010808010002', 'Bagas Wicaksono', 'L', '2001-08-08', '082100002222', 'Jl. KH Samanhudi No. 25, Bumi, Surakarta', '2026-09-10', '2026-09-07', '2026-09-09', '2026-09-16', 'sembuh', 1, '2026-09-10 03:30:00', '2026-09-16 02:32:57'),
(45, 2, 1, 4, '3372011502070003', 'Candra Wijaya', 'L', '2007-02-15', '082100003333', 'Jl. Prof. Suharso No. 18, Jajar, Surakarta', '2026-09-10', '2026-09-05', '2026-09-09', '2026-09-16', 'sembuh', 1, '2026-09-10 04:45:00', '2026-09-16 02:32:53'),
(46, 2, 2, 5, '3372012011990004', 'Dika Anggara', 'L', '1999-11-20', '082100004444', 'Jl. Ahmad Yani No. 55, Kerten, Surakarta', '2026-09-11', '2026-09-01', '2026-09-05', '2026-09-16', 'sembuh', 1, '2026-09-11 01:20:00', '2026-09-16 02:30:51'),
(47, 3, 1, 6, '3372010505030005', 'Eko Prasetyo', 'L', '2003-05-05', '082100005555', 'Jl. Matoa Raya No. 12, Karangasem, Surakarta', '2026-09-11', '2026-09-08', '2026-09-10', '2026-09-16', 'sembuh', 1, '2026-09-11 02:30:00', '2026-09-16 02:30:40'),
(48, 2, 1, 7, '3372011409020006', 'Fajar Ramadhan', 'L', '2002-09-14', '082100006666', 'Jl. Joko Tingkir No. 8, Pajang, Surakarta', '2026-09-11', '2026-09-09', '2026-09-11', '2026-09-16', 'sembuh', 1, '2026-09-11 06:10:00', '2026-09-16 02:30:36'),
(49, 2, 1, 8, '3372012512060007', 'Gilang Saputra', 'L', '2006-12-25', '082100007777', 'Jl. Dr. Rajiman No. 102, Laweyan, Surakarta', '2026-09-12', '2026-09-08', '2026-09-11', '2026-09-16', 'sembuh', 1, '2026-09-12 00:45:00', '2026-09-16 02:30:13'),
(50, 3, 2, 9, '3372011708000008', 'Hendra Gunawan', 'L', '2000-08-17', '082100008888', 'Jl. Kyai Mojo No. 34, Bumi, Surakarta', '2026-09-12', '2026-09-05', '2026-09-09', '2026-09-16', 'sembuh', 1, '2026-09-12 03:05:00', '2026-09-16 02:30:08'),
(51, 2, 1, 10, '3372011010040009', 'Iqbal Maulana', 'L', '2004-10-10', '082100009999', 'Jl. Veteran No. 88, Panularan, Surakarta', '2026-09-12', '2026-09-10', '2026-09-12', '2026-09-16', 'sembuh', 1, '2026-09-12 07:30:00', '2026-09-16 02:29:44'),
(52, 2, 1, 11, '3372010101080010', 'Joko Susilo', 'L', '2008-01-01', '082100010000', 'Jl. Kebangkitan Nasional No. 15, Sriwedari, Surakarta', '2026-09-13', '2026-09-09', '2026-09-11', '2026-09-16', 'sembuh', 1, '2026-09-13 01:50:00', '2026-09-16 02:29:24'),
(53, 3, 1, 1, '3372012802010011', 'Kevin Sanjaya', 'L', '2001-02-28', '082100011111', 'Jl. Kenari No. 5, Penumping, Surakarta', '2026-09-13', '2026-09-10', '2026-09-12', '2026-09-16', 'sembuh', 1, '2026-09-13 04:15:00', '2026-09-16 02:29:20'),
(54, 2, 1, 2, '3372011903050012', 'Lukman Hakim', 'L', '2005-03-19', '082100012222', 'Jl. Slamet Riyadi No. 110, Purwosari, Surakarta', '2026-09-14', '2026-09-11', '2026-09-13', '2026-09-16', 'sembuh', 1, '2026-09-14 02:10:00', '2026-09-16 02:28:44'),
(55, 2, 2, 4, '3372012207990013', 'Maulana Malik', 'L', '1999-07-22', '082100013333', 'Jl. Adi Sucipto No. 66, Jajar, Surakarta', '2026-09-14', '2026-09-08', '2026-09-11', '2026-09-16', 'sembuh', 1, '2026-09-14 06:45:00', '2026-09-16 02:28:39'),
(56, 3, 1, 6, '3372013008060014', 'Niko Saputra', 'L', '2006-08-30', '082100014444', 'Gg. Nanas V, Karangasem, Surakarta', '2026-09-15', '2026-09-12', '2026-09-14', '2026-09-16', 'sembuh', 1, '2026-09-15 03:20:00', '2026-09-16 02:28:16'),
(57, 2, 1, 8, '3372011111020015', 'Oka Darmawan', 'L', '2002-11-11', '082100015555', 'Jl. KH Samanhudi No. 50, Laweyan, Surakarta', '2026-09-15', '2026-09-12', '2026-09-14', '2026-09-16', 'sembuh', 1, '2026-09-15 07:00:00', '2026-09-16 02:27:57'),
(58, 2, 1, 3, '3372015204050016', 'Ayu Lestari', 'P', '2005-04-12', '082200001111', 'Jl. Samanhudi No. 12, Sondakan, Surakarta', '2026-09-10', '2026-09-07', '2026-09-09', '2026-09-16', 'sembuh', 1, '2026-09-10 02:30:00', '2026-09-16 02:32:48'),
(59, 3, 1, 5, '3372014808010017', 'Bella Saphira', 'P', '2001-08-08', '082200002222', 'Jl. Ahmad Yani No. 80, Kerten, Surakarta', '2026-09-10', '2026-09-06', '2026-09-08', '2026-09-15', 'sembuh', 1, '2026-09-10 04:00:00', '2026-09-16 02:32:44'),
(60, 2, 1, 7, '3372015502070018', 'Citra Kirana', 'P', '2007-02-15', '082200003333', 'Jl. Sidomukti No. 10, Pajang, Surakarta', '2026-09-11', '2026-09-08', '2026-09-10', '2026-09-16', 'sembuh', 1, '2026-09-11 01:15:00', '2026-09-16 02:30:27'),
(61, 2, 2, 9, '3372016011990019', 'Dina Lorenza', 'P', '1999-11-20', '082200004444', 'Jl. Kyai Mojo No. 20, Bumi, Surakarta', '2026-09-11', '2026-09-05', '2026-09-08', '2026-09-16', 'sembuh', 1, '2026-09-11 03:40:00', '2026-09-16 02:30:22'),
(62, 3, 1, 11, '3372014505030020', 'Eka Fitriani', 'P', '2003-05-05', '082200005555', 'Jl. Museum No. 9, Sriwedari, Surakarta', '2026-09-11', '2026-09-09', '2026-09-11', '2026-09-16', 'sembuh', 1, '2026-09-11 07:20:00', '2026-09-16 02:30:18'),
(63, 2, 1, 1, '3372015409020021', 'Fitri Handayani', 'P', '2002-09-14', '082200006666', 'Jl. Slamet Riyadi No. 200, Penumping, Surakarta', '2026-09-12', '2026-09-08', '2026-09-11', '2026-09-16', 'sembuh', 1, '2026-09-12 02:10:00', '2026-09-16 02:29:39'),
(64, 2, 1, 2, '3372016512060022', 'Gita Gutawa', 'P', '2006-12-25', '082200007777', 'Jl. Agus Salim No. 33, Purwosari, Surakarta', '2026-09-12', '2026-09-10', '2026-09-12', '2026-09-16', 'sembuh', 1, '2026-09-12 04:50:00', '2026-09-16 02:29:35'),
(65, 3, 2, 4, '3372015708000023', 'Hana Maharani', 'P', '2000-08-17', '082200008888', 'Jl. Prof. Suharso No. 44, Jajar, Surakarta', '2026-09-13', '2026-09-05', '2026-09-10', '2026-09-16', 'sembuh', 1, '2026-09-13 01:30:00', '2026-09-16 02:29:10'),
(66, 2, 1, 6, '3372015010040024', 'Intan Permata', 'P', '2004-10-10', '082200009999', 'Jl. Matoa Raya No. 25, Karangasem, Surakarta', '2026-09-13', '2026-09-11', '2026-09-13', '2026-09-16', 'sembuh', 1, '2026-09-13 03:15:00', '2026-09-16 02:28:55'),
(67, 2, 1, 8, '3372014101080025', 'Jihan Audy', 'P', '2008-01-01', '082200010000', 'Jl. Dr. Wahidin No. 55, Laweyan, Surakarta', '2026-09-13', '2026-09-10', '2026-09-12', '2026-09-16', 'sembuh', 1, '2026-09-13 06:40:00', '2026-09-16 02:28:49'),
(68, 3, 1, 10, '3372016802010026', 'Kiki Fatmala', 'P', '2001-02-28', '082200011111', 'Jl. Bhayangkara No. 90, Panularan, Surakarta', '2026-09-14', '2026-09-11', '2026-09-13', '2026-09-16', 'sembuh', 1, '2026-09-14 02:25:00', '2026-09-16 02:28:36'),
(69, 2, 1, 3, '3372015903050027', 'Laras Sekar', 'P', '2005-03-19', '082200012222', 'Jl. KH Samanhudi No. 30, Sondakan, Surakarta', '2026-09-14', '2026-09-12', '2026-09-14', '2026-09-16', 'sembuh', 1, '2026-09-14 04:55:00', '2026-09-16 02:28:22'),
(70, 2, 2, 5, '3372016207990028', 'Maya Septha', 'P', '1999-07-22', '082200013333', 'Jl. Sam Ratulangi No. 12, Kerten, Surakarta', '2026-09-15', '2026-09-10', '2026-09-13', '2026-09-16', 'sembuh', 1, '2026-09-15 01:10:00', '2026-09-16 02:27:53'),
(71, 3, 1, 7, '3372017008060029', 'Nadia Vega', 'P', '2006-08-30', '082200014444', 'Jl. Sidomukti No. 40, Pajang, Surakarta', '2026-09-15', '2026-09-12', '2026-09-14', '2026-09-16', 'sembuh', 1, '2026-09-15 06:20:00', '2026-09-16 02:27:49'),
(72, 2, 1, 9, '3372015111020030', 'Olivia Jensen', 'P', '2002-11-11', '082200015555', 'Jl. Kyai Mojo No. 50, Bumi, Surakarta', '2026-09-16', '2026-09-13', '2026-09-15', '2026-09-16', 'sembuh', 1, '2026-09-16 02:00:00', '2026-09-16 02:27:42'),
(73, 2, 2, 1, '3372011204050001', 'Aditya Pratama', 'L', '2005-04-12', NULL, NULL, '2026-09-14', '2026-09-16', NULL, '2026-09-16', 'sembuh', 1, '2026-09-16 02:43:49', '2026-09-16 03:17:17'),
(74, 2, 2, 1, '0000006633220060', 'Apriliani Putri Marathesa', 'L', '2000-03-08', NULL, NULL, '2026-09-16', '2026-09-07', NULL, '2026-09-16', 'sembuh', 1, '2026-09-16 02:44:17', '2026-09-16 03:16:46'),
(75, 2, 12, 1, '3333300056660088', 'Aida Kusuma Wardani', 'L', '2007-02-03', NULL, NULL, '2026-09-14', '2026-09-12', NULL, '2026-09-16', 'sembuh', 1, '2026-09-16 02:44:51', '2026-09-16 03:17:03'),
(76, 2, 1, 1, '3333990056677789', 'Arga Pratama', 'L', '2005-02-16', NULL, NULL, '2026-09-10', '2026-09-15', NULL, '2026-09-16', 'sembuh', 1, '2026-09-16 02:46:49', '2026-09-16 03:17:36'),
(77, 2, 1, 1, '3372014101000001', 'Nisa Rahmawati', 'P', '2000-01-11', '081300000001', 'Jl. Rajawali No. 1, Penumping, Surakarta', '2026-09-02', '2026-09-01', '2026-09-02', '2026-09-08', 'sembuh', 1, '2026-09-02 02:15:00', '2026-09-17 13:06:43'),
(78, 3, 1, 2, '3372014202010002', 'Rina Melati', 'P', '2001-02-12', '081300000002', 'Jl. Merpati No. 12, Purwosari, Surakarta', '2026-09-03', '2026-09-01', '2026-09-02', '2026-09-09', 'sembuh', 1, '2026-09-03 03:30:00', '2026-09-17 13:26:42'),
(79, 2, 2, 3, '3372014303020003', 'Siti Aisyah', 'P', '2002-03-13', '081300000003', 'Jl. Kenari No. 23, Sondakan, Surakarta', '2026-09-04', '2026-09-02', '2026-09-03', '2026-09-10', 'sembuh', 1, '2026-09-04 01:20:00', '2026-09-17 13:10:24'),
(80, 3, 1, 4, '3372014404030004', 'Putri Kirana', 'P', '2003-04-14', '081300000004', 'Jl. Cendrawasih No. 34, Jajar, Surakarta', '2026-09-05', '2026-09-03', '2026-09-04', '2026-09-11', 'sembuh', 1, '2026-09-05 04:10:00', '2026-09-17 13:10:14'),
(81, 2, 1, 5, '3372014505040005', 'Vina Panduwinata', 'P', '2004-05-15', '081300000005', 'Jl. Nuri No. 45, Kerten, Surakarta', '2026-09-06', '2026-09-04', '2026-09-05', '2026-09-12', 'sembuh', 1, '2026-09-06 02:45:00', '2026-09-17 13:10:09'),
(82, 2, 2, 6, '3372014606050006', 'Salsa Billa', 'P', '2005-06-16', '081300000006', 'Jl. Garuda No. 56, Karangasem, Surakarta', '2026-09-07', '2026-09-05', '2026-09-06', '2026-09-13', 'sembuh', 1, '2026-09-07 07:20:00', '2026-09-17 13:09:51'),
(83, 3, 1, 7, '3372014707060007', 'Dinda Kanya', 'P', '2006-07-17', '081300000007', 'Jl. Elang No. 67, Pajang, Surakarta', '2026-09-08', '2026-09-06', '2026-09-07', '2026-09-14', 'sembuh', 1, '2026-09-08 06:15:00', '2026-09-17 13:09:47'),
(84, 2, 1, 8, '3372014808070008', 'Rani Permata', 'P', '2007-08-18', '081300000008', 'Jl. Kakaktua No. 78, Laweyan, Surakarta', '2026-09-09', '2026-09-07', '2026-09-08', '2026-09-15', 'sembuh', 1, '2026-09-09 03:05:00', '2026-09-17 13:09:36'),
(85, 3, 1, 9, '3372014909080009', 'Sinta Nuriyah', 'P', '2008-09-19', '081300000009', 'Jl. Kepodang No. 89, Bumi, Surakarta', '2026-09-10', '2026-09-08', '2026-09-09', '2026-09-16', 'sembuh', 1, '2026-09-10 01:30:00', '2026-09-17 13:09:29'),
(86, 2, 2, 10, '3372015010090010', 'Tari Larasati', 'P', '2009-10-20', '081300000010', 'Jl. Beo No. 90, Panularan, Surakarta', '2026-09-11', '2026-09-09', '2026-09-10', '2026-09-17', 'sembuh', 1, '2026-09-11 04:45:00', '2026-09-17 13:09:18'),
(87, 3, 1, 11, '3372015111990011', 'Wulan Sari', 'P', '1999-11-21', '081300000011', 'Jl. Mutiara No. 11, Sriwedari, Surakarta', '2026-09-12', '2026-09-10', '2026-09-11', '2026-09-18', 'sembuh', 1, '2026-09-12 02:20:00', '2026-09-17 13:09:11'),
(88, 2, 1, 1, '3372015212000012', 'Yuni Kartika', 'P', '2000-12-22', '081300000012', 'Jl. Intan No. 22, Penumping, Surakarta', '2026-09-13', '2026-09-11', '2026-09-12', '2026-09-19', 'sembuh', 1, '2026-09-13 07:10:00', '2026-09-17 13:09:02'),
(89, 2, 1, 2, '3372015301010013', 'Zaskia Putri', 'P', '2001-01-23', '081300000013', 'Jl. Berlian No. 33, Purwosari, Surakarta', '2026-09-14', '2026-09-12', '2026-09-13', '2026-09-20', 'sembuh', 1, '2026-09-14 06:25:00', '2026-09-17 13:08:57'),
(90, 3, 2, 3, '3372015402020014', 'Amalia Hasanah', 'P', '2002-02-24', '081300000014', 'Jl. Zamrud No. 44, Sondakan, Surakarta', '2026-09-15', '2026-09-13', '2026-09-14', '2026-09-21', 'sembuh', 1, '2026-09-15 01:50:00', '2026-09-17 13:08:49'),
(91, 2, 1, 4, '3372015503030015', 'Bunga Citra', 'P', '2003-03-25', '081300000015', 'Jl. Ruby No. 55, Jajar, Surakarta', '2026-09-16', '2026-09-14', '2026-09-15', '2026-09-22', 'sembuh', 1, '2026-09-16 03:15:00', '2026-09-17 13:08:45'),
(92, 3, 1, 5, '3372015604040016', 'Clara Shinta', 'P', '2004-04-26', '081300000016', 'Jl. Safir No. 66, Kerten, Surakarta', '2026-09-03', '2026-09-01', '2026-09-02', '2026-09-09', 'sembuh', 1, '2026-09-03 04:40:00', '2026-09-24 03:39:07'),
(93, 2, 1, 6, '3372015705050017', 'Desy Maharani', 'P', '2005-05-27', '081300000017', 'Jl. Giok No. 77, Karangasem, Surakarta', '2026-09-04', '2026-09-02', '2026-09-03', '2026-09-10', 'sembuh', 1, '2026-09-04 02:05:00', '2026-09-24 03:39:07'),
(94, 3, 2, 7, '3372015806060018', 'Elvira Cantika', 'P', '2006-06-28', '081300000018', 'Jl. Topaz No. 88, Pajang, Surakarta', '2026-09-05', '2026-09-03', '2026-09-04', '2026-09-11', 'sembuh', 1, '2026-09-05 07:30:00', '2026-09-24 03:39:07'),
(95, 2, 1, 8, '3372015907070019', 'Farah Annisa', 'P', '2007-07-29', '081300000019', 'Jl. Opal No. 99, Laweyan, Surakarta', '2026-09-06', '2026-09-04', '2026-09-05', '2026-09-12', 'sembuh', 1, '2026-09-06 06:10:00', '2026-09-24 03:39:07'),
(96, 3, 1, 9, '3372016008080020', 'Gisella Rahma', 'P', '2008-08-30', '081300000020', 'Jl. Koral No. 10, Bumi, Surakarta', '2026-09-07', '2026-09-05', '2026-09-06', '2026-09-13', 'sembuh', 1, '2026-09-07 01:45:00', '2026-09-24 03:39:07'),
(97, 2, 1, 10, '3372016109090021', 'Hesti Lestari', 'P', '2009-09-01', '081300000021', 'Jl. Mawar No. 21, Panularan, Surakarta', '2026-09-08', '2026-09-06', '2026-09-07', '2026-09-14', 'sembuh', 1, '2026-09-08 03:20:00', '2026-09-24 03:39:07'),
(98, 3, 2, 11, '3372016210990022', 'Indah Permatasari', 'P', '1999-10-02', '081300000022', 'Jl. Melati No. 32, Sriwedari, Surakarta', '2026-09-09', '2026-09-07', '2026-09-08', '2026-09-14', 'sembuh', 1, '2026-09-09 04:35:00', '2026-09-24 03:39:07'),
(99, 2, 1, 1, '3372016311000023', 'Jessica Kumala', 'P', '2000-11-03', '081300000023', 'Jl. Anggrek No. 43, Penumping, Surakarta', '2026-09-10', '2026-09-08', '2026-09-09', '2026-09-14', 'sembuh', 1, '2026-09-10 02:50:00', '2026-09-24 03:39:07'),
(100, 2, 1, 2, '3372016412010024', 'Kirana Larasati', 'P', '2001-12-04', '081300000024', 'Jl. Tulip No. 54, Purwosari, Surakarta', '2026-09-11', '2026-09-09', '2026-09-10', '2026-09-14', 'sembuh', 1, '2026-09-11 07:15:00', '2026-09-24 03:39:07'),
(101, 3, 1, 3, '3372016501020025', 'Luna Safitri', 'P', '2002-01-05', '081300000025', 'Jl. Flamboyan No. 65, Sondakan, Surakarta', '2026-09-11', '2026-09-10', '2026-09-10', '2026-09-14', 'sembuh', 1, '2026-09-11 01:10:00', '2026-09-24 03:39:07'),
(102, 2, 1, 4, '3372011101990026', 'Budi Santoso', 'L', '1999-01-11', '081400000001', 'Jl. Cempaka No. 76, Jajar, Surakarta', '2026-09-02', '2026-09-01', '2026-09-01', '2026-09-07', 'sembuh', 1, '2026-09-02 02:30:00', '2026-09-17 13:26:48'),
(103, 3, 1, 5, '3372011202000027', 'Andi Wijaya', 'L', '2000-02-12', '081400000002', 'Jl. Teratai No. 87, Kerten, Surakarta', '2026-09-04', '2026-09-02', '2026-09-03', '2026-09-10', 'sembuh', 1, '2026-09-04 03:45:00', '2026-09-17 13:10:19'),
(104, 2, 2, 6, '3372011303010028', 'Ricky Saputra', 'L', '2001-03-13', '081400000003', 'Jl. Bougenville No. 98, Karangasem, Surakarta', '2026-09-06', '2026-09-04', '2026-09-05', '2026-09-12', 'sembuh', 1, '2026-09-06 07:10:00', '2026-09-17 13:09:55'),
(105, 3, 1, 7, '3372011404020029', 'Reza Pratama', 'L', '2002-04-14', '081400000004', 'Jl. Dahlia No. 12, Pajang, Surakarta', '2026-09-08', '2026-09-06', '2026-09-07', '2026-09-14', 'sembuh', 1, '2026-09-08 04:20:00', '2026-09-17 13:09:43'),
(106, 2, 1, 8, '3372011505030030', 'Samuel Anggara', 'L', '2003-05-15', '081400000005', 'Jl. Kenanga No. 23, Laweyan, Surakarta', '2026-09-10', '2026-09-08', '2026-09-09', '2026-09-16', 'sembuh', 1, '2026-09-10 01:50:00', '2026-09-17 13:09:24'),
(107, 3, 2, 9, '3372011606040031', 'Rio Ramadhan', 'L', '2004-06-16', '081400000006', 'Jl. Kamboja No. 34, Bumi, Surakarta', '2026-09-12', '2026-09-10', '2026-09-11', '2026-09-18', 'sembuh', 1, '2026-09-12 06:15:00', '2026-09-17 13:09:07'),
(108, 2, 1, 10, '3372011707050032', 'Vino Kurniawan', 'L', '2005-07-17', '081400000007', 'Jl. Soka No. 45, Panularan, Surakarta', '2026-09-14', '2026-09-12', '2026-09-13', '2026-09-20', 'sembuh', 1, '2026-09-14 03:05:00', '2026-09-17 13:08:53'),
(109, 2, 1, 11, '3372011808060033', 'Abimana Putra', 'L', '2006-08-18', '081400000008', 'Jl. Asoka No. 56, Sriwedari, Surakarta', '2026-09-16', '2026-09-14', '2026-09-15', '2026-09-22', 'sembuh', 1, '2026-09-16 02:45:00', '2026-09-17 13:08:41'),
(110, 3, 1, 1, '3372011909070034', 'Chico Hakim', 'L', '2007-09-19', '081400000009', 'Jl. Teratai Indah No. 67, Penumping, Surakarta', '2026-09-04', '2026-09-02', '2026-09-03', '2026-09-10', 'sembuh', 1, '2026-09-04 07:20:00', '2026-09-24 03:39:07'),
(111, 2, 2, 2, '3372012010080035', 'Denny Setiawan', 'L', '2008-10-20', '081400000010', 'Jl. Nusa Indah No. 78, Purwosari, Surakarta', '2026-09-06', '2026-09-04', '2026-09-05', '2026-09-12', 'sembuh', 1, '2026-09-06 04:10:00', '2026-09-24 03:39:07'),
(112, 3, 1, 3, '3372012111090036', 'Eza Maulana', 'L', '2009-11-21', '081400000011', 'Jl. Wijaya Kusuma No. 89, Sondakan, Surakarta', '2026-09-08', '2026-09-06', '2026-09-07', '2026-09-14', 'sembuh', 1, '2026-09-08 01:30:00', '2026-09-24 03:39:07'),
(113, 2, 1, 4, '3372012212990037', 'Fadly Hidayat', 'L', '1999-12-22', '081400000012', 'Jl. Bunga Raya No. 90, Jajar, Surakarta', '2026-09-10', '2026-09-08', '2026-09-09', '2026-09-14', 'sembuh', 1, '2026-09-10 06:45:00', '2026-09-24 03:39:07'),
(114, 2, 1, 5, '3372012301000038', 'Galih Kusuma', 'L', '2000-01-23', '081400000013', 'Jl. Sakura No. 11, Kerten, Surakarta', '2026-09-11', '2026-09-09', '2026-09-10', '2026-09-14', 'sembuh', 1, '2026-09-11 02:10:00', '2026-09-24 03:39:07'),
(115, 2, 1, 6, '3372016602030040', 'Nadia Safitri', 'P', '2003-02-16', '081500000001', 'Jl. Matoa Raya No. 15, Karangasem, Surakarta', '2026-09-03', '2026-09-01', '2026-09-02', '2026-09-08', 'sembuh', 1, '2026-09-03 01:00:00', '2026-09-17 13:26:38'),
(116, 3, 2, 7, '3372016703040041', 'Tiara Andini', 'P', '2004-03-17', '081500000002', 'Jl. Joko Tingkir No. 20, Pajang, Surakarta', '2026-09-05', '2026-09-03', '2026-09-04', '2026-09-10', 'sembuh', 1, '2026-09-05 02:15:00', '2026-09-17 13:26:31'),
(117, 2, 1, 8, '3372016804050042', 'Rara Sekar', 'P', '2005-04-18', '081500000003', 'Jl. Dr. Rajiman No. 110, Laweyan, Surakarta', '2026-09-06', '2026-09-04', '2026-09-05', '2026-09-11', 'sembuh', 1, '2026-09-06 03:30:00', '2026-09-17 13:26:27'),
(118, 3, 1, 9, '3372016905060043', 'Sivia Azizah', 'P', '2006-05-19', '081500000004', 'Jl. Kyai Mojo No. 40, Bumi, Surakarta', '2026-09-07', '2026-09-05', '2026-09-06', '2026-09-13', 'sembuh', 1, '2026-09-07 04:45:00', '2026-09-17 13:26:20'),
(119, 2, 2, 10, '3372017006070044', ' Zahra Aulia', 'P', '2007-06-20', '081500000005', 'Jl. Veteran No. 95, Panularan, Surakarta', '2026-09-08', '2026-09-06', '2026-09-07', '2026-09-14', 'sembuh', 1, '2026-09-08 06:00:00', '2026-09-17 13:26:16'),
(120, 2, 1, 11, '3372017107080045', 'Fatimah Azzahra', 'P', '2008-07-21', '081500000006', 'Jl. Museum No. 18, Sriwedari, Surakarta', '2026-09-09', '2026-09-07', '2026-09-08', '2026-09-15', 'sembuh', 1, '2026-09-09 07:15:00', '2026-09-17 13:26:09'),
(121, 3, 1, 1, '3372017208090046', 'Safira Maharani', 'P', '2009-08-22', '081500000007', 'Jl. Kenari No. 15, Penumping, Surakarta', '2026-09-10', '2026-09-08', '2026-09-09', '2026-09-16', 'sembuh', 1, '2026-09-10 01:30:00', '2026-09-17 13:26:05'),
(122, 2, 1, 2, '3372017309100047', 'Dewi Lestari', 'P', '2000-09-23', '081500000008', 'Jl. Slamet Riyadi No. 120, Purwosari, Surakarta', '2026-09-11', '2026-09-09', '2026-09-10', '2026-09-17', 'sembuh', 1, '2026-09-11 02:45:00', '2026-09-17 13:25:57'),
(123, 3, 2, 3, '3372017410110048', 'Risma Anggraini', 'P', '2001-10-24', '081500000009', 'Jl. KH Samanhudi No. 45, Sondakan, Surakarta', '2026-09-12', '2026-09-10', '2026-09-11', '2026-09-18', 'sembuh', 1, '2026-09-12 03:10:00', '2026-09-17 13:25:53'),
(124, 2, 1, 4, '3372017511120049', 'Alya Nurul', 'P', '2002-11-25', '081500000010', 'Jl. Adi Sucipto No. 70, Jajar, Surakarta', '2026-09-13', '2026-09-11', '2026-09-12', '2026-09-19', 'sembuh', 1, '2026-09-13 04:20:00', '2026-09-17 13:25:45'),
(125, 2, 1, 5, '3372017612010050', 'Luthfi Aulia', 'P', '2003-12-26', '081500000011', 'Jl. Ahmad Yani No. 85, Kerten, Surakarta', '2026-09-14', '2026-09-12', '2026-09-13', '2026-09-20', 'sembuh', 1, '2026-09-14 06:30:00', '2026-09-17 13:25:39'),
(126, 3, 2, 6, '3372017701020051', 'Rahma Diani', 'P', '2004-01-27', '081500000012', 'Gg. Nanas III, Karangasem, Surakarta', '2026-09-15', '2026-09-13', '2026-09-14', '2026-09-21', 'sembuh', 1, '2026-09-15 07:40:00', '2026-09-17 13:25:29'),
(127, 2, 1, 7, '3372017802030052', 'Nabila Syakira', 'P', '2005-02-28', '081500000013', 'Jl. Sidomukti No. 15, Pajang, Surakarta', '2026-09-16', '2026-09-14', '2026-09-15', '2026-09-22', 'sembuh', 1, '2026-09-16 01:15:00', '2026-09-17 13:25:26'),
(128, 3, 1, 8, '3372017903040053', 'Salma Salsabila', 'P', '2006-03-01', '081500000014', 'Jl. KH Samanhudi No. 60, Laweyan, Surakarta', '2026-09-04', '2026-09-02', '2026-09-03', '2026-09-10', 'sembuh', 1, '2026-09-04 02:30:00', '2026-09-24 03:39:07'),
(129, 2, 1, 9, '3372018004050054', 'Khaerunisa Fitri', 'P', '2007-04-02', '081500000015', 'Jl. Kyai Mojo No. 55, Bumi, Surakarta', '2026-09-06', '2026-09-04', '2026-09-05', '2026-09-12', 'sembuh', 1, '2026-09-06 03:45:00', '2026-09-24 03:39:07'),
(130, 3, 2, 10, '3372018105060055', 'Syifa Hadju', 'P', '2008-05-03', '081500000016', 'Jl. Bhayangkara No. 100, Panularan, Surakarta', '2026-09-08', '2026-09-06', '2026-09-07', '2026-09-14', 'sembuh', 1, '2026-09-08 04:55:00', '2026-09-24 03:39:07'),
(131, 2, 1, 11, '3372012402030039', 'Fajar Sidik', 'L', '2001-02-14', '081600000001', 'Jl. Sriwedari No. 5, Sriwedari, Surakarta', '2026-09-04', '2026-09-02', '2026-09-03', '2026-09-10', 'sembuh', 1, '2026-09-04 02:00:00', '2026-09-17 13:26:34'),
(132, 3, 1, 1, '3372012503040040', 'Gilang Ramadhan', 'L', '2002-03-15', '081600000002', 'Jl. Slamet Riyadi No. 180, Penumping, Surakarta', '2026-09-06', '2026-09-04', '2026-09-05', '2026-09-12', 'sembuh', 1, '2026-09-06 03:15:00', '2026-09-17 13:26:23'),
(133, 2, 2, 2, '3372012604050041', 'Hendra Setiawan', 'L', '2003-04-16', '081600000003', 'Jl. Purwosari Indah No. 8, Purwosari, Surakarta', '2026-09-08', '2026-09-06', '2026-09-07', '2026-09-14', 'sembuh', 1, '2026-09-08 04:30:00', '2026-09-17 13:26:13'),
(134, 3, 1, 3, '3372012705060042', 'Irfan Bachdim', 'L', '2004-05-17', '081600000004', 'Jl. KH Samanhudi No. 20, Sondakan, Surakarta', '2026-09-10', '2026-09-08', '2026-09-09', '2026-09-16', 'sembuh', 1, '2026-09-10 06:45:00', '2026-09-17 13:26:01'),
(135, 2, 1, 4, '3372012806070043', 'Joko Susilo', 'L', '2005-06-18', '081600000005', 'Jl. Prof. Suharso No. 25, Jajar, Surakarta', '2026-09-12', '2026-09-10', '2026-09-11', '2026-09-18', 'sembuh', 1, '2026-09-12 07:00:00', '2026-09-17 13:25:49'),
(136, 2, 2, 5, '3372012907080044', 'Kevin Sanjaya', 'L', '2006-07-19', '081600000006', 'Jl. Ahmad Yani No. 60, Kerten, Surakarta', '2026-09-14', '2026-09-12', '2026-09-13', '2026-09-20', 'sembuh', 1, '2026-09-14 01:30:00', '2026-09-17 13:25:34'),
(137, 3, 1, 6, '3372013008090045', 'Lukman Sardi', 'L', '2007-08-20', '081600000007', 'Jl. Matoa Raya No. 30, Karangasem, Surakarta', '2026-09-16', '2026-09-14', '2026-09-15', '2026-09-22', 'sembuh', 1, '2026-09-16 02:45:00', '2026-09-17 13:25:22'),
(138, 2, 1, 7, '3372013109100046', 'Maulana Malik', 'L', '2008-09-21', '081600000008', 'Jl. Sidomukti No. 22, Pajang, Surakarta', '2026-09-05', '2026-09-03', '2026-09-04', '2026-09-11', 'sembuh', 1, '2026-09-05 04:00:00', '2026-09-24 03:39:07'),
(139, 3, 1, 8, '3372013210110047', 'Niko Al Hakim', 'L', '2009-10-22', '081600000009', 'Jl. Dr. Wahidin No. 40, Laweyan, Surakarta', '2026-09-07', '2026-09-05', '2026-09-06', '2026-09-14', 'sembuh', 1, '2026-09-07 06:15:00', '2026-09-24 03:39:07'),
(144, 2, 6, 9, '3372017006070044', ' Zahra Aulia', 'P', '2007-06-20', NULL, NULL, '2026-09-16', '2026-09-17', NULL, '2026-09-24', 'sembuh', 1, '2026-09-17 14:02:09', '2026-09-24 03:54:54'),
(145, 2, 6, 9, '3372011808060033', 'Abimana Putra', 'L', '2006-08-18', NULL, NULL, '2026-09-14', '2026-09-15', NULL, '2026-09-24', 'sembuh', 1, '2026-09-17 14:02:41', '2026-09-24 03:54:59'),
(146, 2, 6, 9, '3372011204050001', 'Aditya Pratama', 'L', '2005-04-12', NULL, NULL, '2026-09-16', '2026-09-17', NULL, '2026-09-24', 'sembuh', 1, '2026-09-17 14:03:09', '2026-09-24 03:54:46'),
(147, 2, 2, 7, '3372011202000027', 'Andi Wijaya', 'L', '2000-02-12', NULL, NULL, '2026-09-04', '2026-09-01', NULL, '2026-09-10', 'sembuh', 1, '2026-09-04 04:04:35', '2026-09-24 03:54:34'),
(148, 2, 2, 7, '3372015604040016', 'Clara Shinta', 'P', '2004-04-26', NULL, NULL, '2026-09-16', '2026-09-17', NULL, '2026-09-24', 'sembuh', 1, '2026-09-18 04:05:19', '2026-09-24 03:54:42'),
(149, 2, 2, 7, '3372012010080035', 'Denny Setiawan', 'L', '2008-10-20', NULL, NULL, '2026-09-12', '2026-09-18', NULL, '2026-09-24', 'sembuh', 1, '2026-09-18 04:05:50', '2026-09-24 03:55:02'),
(150, 2, 2, 7, '3372014505030020', 'Eka Fitriani', 'P', '2003-05-05', NULL, NULL, '2026-09-15', '2026-09-18', NULL, '2026-09-24', 'sembuh', 1, '2026-09-18 04:06:12', '2026-09-24 03:54:50'),
(151, 2, 2, 7, '3372012212990037', 'Fadly Hidayat', 'L', '1999-12-22', NULL, NULL, '2026-09-09', '2026-09-13', NULL, '2026-09-24', 'sembuh', 1, '2026-09-18 04:06:39', '2026-10-26 12:30:51'),
(152, 2, 2, 7, '3372011101990026', 'Budi Santoso', 'L', '1999-01-11', NULL, NULL, '2026-09-16', '2026-09-17', NULL, '2026-09-24', 'sembuh', 1, '2026-09-18 04:07:20', '2026-09-24 03:54:39'),
(153, 5, 6, 8, '0000006633220896', 'coba', 'L', '2007-02-22', '087888556896', 'radjiman', '2026-09-09', '2026-09-09', NULL, '2026-09-10', 'sembuh', 1, '2026-09-09 03:30:01', '2026-10-26 12:30:54'),
(154, 5, 6, 7, '0000006633229900', 'Salsabila', 'P', '2001-08-14', '0878885569900', 'cemani', '2026-09-08', '2026-09-09', NULL, '2026-09-10', 'sembuh', 1, '2026-09-09 03:37:10', '2026-10-26 12:30:42'),
(155, 5, 6, 8, '0088667789077800', 'Hanun', 'P', '2008-02-18', '097888567889', 'surakarta', '2026-09-07', '2026-09-09', NULL, '2026-09-10', 'sembuh', 1, '2026-09-09 03:47:57', '2026-10-26 12:30:39'),
(156, 5, 2, 4, '3372011909070034', 'Chico Hakim', 'L', '2007-09-19', NULL, NULL, '2026-09-09', '2026-09-07', NULL, '2026-09-10', 'sembuh', 1, '2026-09-09 03:48:32', '2026-09-24 03:55:21'),
(157, 3, 10, 5, '0000006633221050', 'kiara rifai', 'P', '2004-06-22', '087888556999', 'surakarta', '2026-09-08', '2026-09-09', NULL, '2026-09-10', 'sembuh', 1, '2026-09-09 03:50:53', '2026-09-24 03:55:12'),
(158, 3, 6, 8, '0000006633222030', 'dwirara', 'P', '2001-07-17', '087778999966', 'Sumenep', '2026-09-08', '2026-09-09', NULL, '2026-09-10', 'sembuh', 1, '2026-09-09 04:08:08', '2026-10-26 12:30:46'),
(159, 2, 2, 7, '0000333356678995', 'bintang', 'P', '2007-01-13', '082241249977', 'boyolali', '2026-09-09', '2026-09-09', NULL, '2026-09-10', 'sembuh', 1, '2026-09-09 04:09:41', '2026-09-24 03:55:18'),
(160, 3, 6, 10, '0000006633229065', 'Bimanesa', 'L', '2000-01-18', '087778993020', 'Jl adi sumarmo', '2026-09-09', '2026-09-09', NULL, '2026-09-10', 'sembuh', 1, '2026-09-09 04:11:53', '2026-09-24 03:55:15'),
(161, 3, 6, 8, '0000006633229988', 'Farkhan', 'L', '2007-08-02', '097888567990', 'Cemani', '2026-09-09', '2026-09-09', NULL, '2026-09-10', 'sembuh', 1, '2026-09-09 06:01:26', '2026-09-24 03:55:07'),
(162, 2, 1, 5, '3372017309100047', 'Dewi Lestari', 'P', '2000-09-23', NULL, NULL, '2026-09-24', '2026-09-23', NULL, '2026-10-26', 'sembuh', 1, '2026-09-24 03:56:11', '2026-10-26 12:30:56'),
(163, 2, 1, 5, '3372017107080045', 'Fatimah Azzahra', 'P', '2008-07-21', NULL, NULL, '2026-09-24', '2026-09-21', NULL, '2026-10-26', 'sembuh', 1, '2026-09-24 03:56:35', '2026-10-26 12:30:58'),
(164, 2, 3, 8, '3372011204050001', 'Aditya Pratama', 'L', '2005-04-12', NULL, NULL, '2026-10-26', '2026-10-26', NULL, '2026-10-26', 'sembuh', 1, '2026-10-26 06:47:15', '2026-10-26 12:31:02'),
(165, 2, 2, 4, '3372015604040016', 'Clara Shinta', 'P', '2004-04-26', NULL, NULL, '2026-10-26', '2026-10-26', NULL, '2026-10-26', 'sembuh', 0, '2026-10-26 12:51:01', '2026-10-26 12:52:03'),
(166, 2, 2, 4, '3372011909070034', 'Chico Hakim', 'L', '2007-09-19', NULL, NULL, '2026-10-25', '2026-10-25', NULL, '2026-10-26', 'sembuh', 0, '2026-10-26 12:51:24', '2026-10-26 12:52:33'),
(167, 2, 6, 4, '0000006633229065', 'Bimanesa', 'L', '2000-01-18', NULL, NULL, '2026-10-27', '2026-10-27', NULL, NULL, 'terkonfirmasi', 0, '2026-10-27 11:49:25', '2026-10-27 11:49:25'),
(168, 2, 6, 4, '3372012010080035', 'Denny Setiawan', 'L', '2008-10-20', NULL, NULL, '2026-10-28', '2026-10-28', NULL, NULL, 'terkonfirmasi', 0, '2026-10-28 13:35:00', '2026-10-28 13:35:00');

-- --------------------------------------------------------

--
-- Table structure for table `tabel_kelurahan`
--

CREATE TABLE `tabel_kelurahan` (
  `id_kelurahan` int NOT NULL,
  `id_pengguna` int DEFAULT NULL COMMENT 'Relasi ke admin yang mendaftarkan wilayah',
  `kode_wilayah` varchar(20) NOT NULL COMMENT 'Kode referensi Kemendagri atau BPS (berguna untuk integrasi SATUSEHAT nantinya)',
  `nama_kelurahan` varchar(100) NOT NULL,
  `kodepos` varchar(10) DEFAULT NULL,
  `jumlah_penduduk` int DEFAULT '0' COMMENT 'Populasi riil untuk pembagi perhitungan rasio penyebaran wabah',
  `luas_wilayah_km2` decimal(10,2) DEFAULT '0.00' COMMENT 'Untuk menganalisis kepadatan penduduk berbanding kasus',
  `geo_polygon` json DEFAULT NULL COMMENT 'Menyimpan koordinat batas wilayah format GeoJSON untuk digambar di peta Bootstrap/Leaflet.js',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tabel_kelurahan`
--

INSERT INTO `tabel_kelurahan` (`id_kelurahan`, `id_pengguna`, `kode_wilayah`, `nama_kelurahan`, `kodepos`, `jumlah_penduduk`, `luas_wilayah_km2`, `geo_polygon`, `created_at`, `updated_at`) VALUES
(1, 2, '33.72.01.1001', 'Penumping', '57141', 4520, 0.55, '{\"type\": \"Polygon\", \"coordinates\": [[[110.8123, -7.5678], [110.815, -7.5678], [110.815, -7.57], [110.8123, -7.57], [110.8123, -7.5678]]]}', '2026-07-23 07:25:09', '2026-08-18 04:29:15'),
(2, 2, '33.72.01.1002', 'Purwosari', '57142', 5100, 0.62, '{\"type\": \"Polygon\", \"coordinates\": [[[110.791, -7.561], [110.796, -7.563], [110.801, -7.561], [110.803, -7.566], [110.792, -7.567], [110.791, -7.561]]]}', '2026-07-28 09:17:43', '2026-08-18 04:29:15'),
(3, 2, '33.72.01.1003', 'Sondakan', '57147', 6300, 0.75, '{\"type\": \"Polygon\", \"coordinates\": [[[110.785, -7.562], [110.791, -7.561], [110.792, -7.567], [110.783, -7.569], [110.785, -7.562]]]}', '2026-07-28 09:17:43', '2026-08-18 04:29:15'),
(4, 3, '33.72.01.1004', 'Jajar', '57144', 5800, 0.68, '{\"type\": \"Polygon\", \"coordinates\": [[[110.782, -7.556], [110.789, -7.555], [110.791, -7.561], [110.785, -7.562], [110.782, -7.556]]]}', '2026-07-28 09:17:43', '2026-08-18 04:29:15'),
(5, 2, '33.72.01.1005', 'Kerten', '57143', 4900, 0.51, '{\"type\": \"Polygon\", \"coordinates\": [[[110.789, -7.555], [110.798, -7.557], [110.796, -7.563], [110.791, -7.561], [110.789, -7.555]]]}', '2026-07-28 09:17:43', '2026-08-18 04:29:15'),
(6, 2, '33.72.01.1006', 'Karangasem', '57145', 7200, 0.89, '{\"type\": \"Polygon\", \"coordinates\": [[[110.775, -7.558], [110.782, -7.556], [110.785, -7.562], [110.776, -7.564], [110.775, -7.558]]]}', '2026-07-28 09:17:43', '2026-08-18 04:29:15'),
(7, 3, '33.72.01.1007', 'Pajang', '57146', 11500, 1.45, '{\"type\": \"Polygon\", \"coordinates\": [[[110.776, -7.564], [110.785, -7.562], [110.783, -7.569], [110.773, -7.571], [110.776, -7.564]]]}', '2026-07-28 09:17:43', '2026-08-18 04:29:15'),
(8, 2, '33.72.01.1008', 'Laweyan', '57148', 4100, 0.48, '{\"type\": \"Polygon\", \"coordinates\": [[[110.773, -7.571], [110.783, -7.569], [110.786, -7.576], [110.778, -7.578], [110.773, -7.571]]]}', '2026-07-28 09:17:43', '2026-08-18 04:29:15'),
(9, 2, '33.72.01.1009', 'Bumi', '57148', 3901, 0.42, '{\"type\": \"Polygon\", \"coordinates\": [[[110.783, -7.569], [110.792, -7.567], [110.794, -7.574], [110.786, -7.576], [110.783, -7.569]]]}', '2026-07-28 09:17:43', '2026-08-28 12:30:24'),
(10, 3, '33.72.01.1010', 'Panularan', '57149', 5300, 0.59, '{\"type\": \"Polygon\", \"coordinates\": [[[110.792, -7.567], [110.803, -7.566], [110.807, -7.568], [110.812, -7.57], [110.809, -7.576], [110.794, -7.574], [110.792, -7.567]]]}', '2026-07-28 09:17:43', '2026-08-18 04:29:15'),
(11, 2, '33.72.01.1011', 'Sriwedari', '57141', 3600, 0.39, '{\"type\": \"Polygon\", \"coordinates\": [[[110.808, -7.562], [110.814, -7.564], [110.812, -7.57], [110.807, -7.568], [110.808, -7.562]]]}', '2026-07-28 09:17:43', '2026-08-18 04:29:15');

-- --------------------------------------------------------

--
-- Table structure for table `tabel_master_icd10`
--

CREATE TABLE `tabel_master_icd10` (
  `kode_icd` varchar(10) NOT NULL COMMENT 'Kode ICD-10 (contoh: A91)',
  `deskripsi_en` varchar(255) NOT NULL COMMENT 'Nama penyakit standar internasional',
  `deskripsi_id` varchar(255) NOT NULL COMMENT 'Nama penyakit terjemahan bahasa Indonesia',
  `kategori` varchar(100) DEFAULT NULL COMMENT 'Bab atau kelompok penyakit dalam ICD-10'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tabel_master_icd10`
--

INSERT INTO `tabel_master_icd10` (`kode_icd`, `deskripsi_en`, `deskripsi_id`, `kategori`) VALUES
('A00.9', 'Cholera, unspecified', 'Kolera, tidak spesifik', 'Penyakit infeksi dan parasit tertentu'),
('A01.0', 'Typhoid fever', 'Demam tifoid (Tipes)', 'Penyakit infeksi dan parasit tertentu'),
('A09', 'Diarrhoea and gastroenteritis of presumed infectious origin', 'Diare dan gastroenteritis infeksius', 'Penyakit infeksi dan parasit tertentu'),
('A15', 'Respiratory tuberculosis', 'Tuberkulosis saluran pernapasan', 'Penyakit infeksi dan parasit tertentu'),
('A27.9', 'Leptospirosis, unspecified', 'Leptospirosis, tidak spesifik', 'Penyakit infeksi dan parasit tertentu'),
('A35', 'Other tetanus', 'Tetanus lainnya', 'Penyakit infeksi dan parasit tertentu'),
('A36.9', 'Diphtheria, unspecified', 'Difteri, tidak spesifik', 'Penyakit infeksi dan parasit tertentu'),
('A37.9', 'Whooping cough, unspecified', 'Batuk rejan (Pertusis)', 'Penyakit infeksi dan parasit tertentu'),
('A82.9', 'Rabies, unspecified', 'Rabies, tidak spesifik', 'Penyakit infeksi dan parasit tertentu'),
('A91', 'Dengue haemorrhagic fever', 'Demam berdarah dengue', 'Penyakit infeksi dan parasit tertentu'),
('A92.0', 'Chikungunya virus disease', 'Penyakit virus Chikungunya', 'Penyakit infeksi dan parasit tertentu'),
('B05.9', 'Measles without complication', 'Campak tanpa komplikasi', 'Penyakit infeksi dan parasit tertentu'),
('B15.9', 'Hepatitis A without hepatic coma', 'Hepatitis A', 'Penyakit infeksi dan parasit tertentu'),
('B20', 'Human immunodeficiency virus [HIV] disease', 'Penyakit virus imunodefisiensi manusia [HIV]', 'Penyakit infeksi dan parasit tertentu'),
('B54', 'Unspecified malaria', 'Malaria yang tidak spesifik', 'Penyakit infeksi dan parasit tertentu'),
('C71.9', 'Malignant neoplasm of brain, unspecified', 'Neoplasma Ganas Otak', 'Kangker otak Neoplasma Bab II C00-D48'),
('F31', 'Bipolar affective disorder', 'Gangguan afektif bipolar', 'Gangguan mental dan perilaku'),
('I10', 'Essential (primary) hypertension', 'Hipertensi esensial (primer)', 'Penyakit pada sistem sirkulasi darah'),
('J18', 'Pneumonia, organism unspecified', 'Pneumonia, organisme tidak spesifik', 'Penyakit pada sistem pernapasan'),
('U07.1', 'COVID-19, virus identified', 'COVID-19, virus teridentifikasi', 'Kode untuk tujuan khusus');

-- --------------------------------------------------------

--
-- Table structure for table `tabel_pengaturan_threshold`
--

CREATE TABLE `tabel_pengaturan_threshold` (
  `id_threshold` int NOT NULL,
  `id_penyakit` int NOT NULL COMMENT 'Relasi ke penyakit (misal: DBD, ISPA)',
  `id_pengguna` int NOT NULL COMMENT 'Relasi ke admin/petugas yang mengatur angka ini',
  `batas_waspada` int NOT NULL COMMENT 'Jumlah kasus untuk memicu status Kuning/Waspada',
  `batas_wabah` int NOT NULL COMMENT 'Jumlah kasus untuk memicu status Merah/Potensi Wabah',
  `periode_hari` int DEFAULT '7' COMMENT 'Rentang waktu analisis (default 7 hari sesuai desain dashboard)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tabel_pengaturan_threshold`
--

INSERT INTO `tabel_pengaturan_threshold` (`id_threshold`, `id_penyakit`, `id_pengguna`, `batas_waspada`, `batas_wabah`, `periode_hari`, `created_at`, `updated_at`) VALUES
(3, 1, 3, 2, 5, 7, '2026-08-01 08:09:19', '2026-08-01 08:09:19'),
(9, 2, 2, 2, 5, 7, '2026-08-01 08:33:31', '2026-09-17 13:40:51'),
(10, 3, 3, 2, 5, 7, '2026-08-01 08:33:31', '2026-08-01 08:33:31'),
(11, 4, 3, 2, 5, 7, '2026-08-01 08:33:31', '2026-08-01 08:33:31'),
(12, 5, 3, 2, 5, 7, '2026-08-01 08:33:31', '2026-08-01 08:33:31'),
(13, 6, 3, 2, 5, 7, '2026-08-01 08:33:31', '2026-08-01 08:33:31'),
(14, 18, 2, 2, 5, 7, '2026-09-06 05:32:26', '2026-09-06 05:32:26'),
(15, 12, 2, 2, 5, 7, '2026-09-06 05:33:44', '2026-09-06 05:33:44'),
(16, 13, 2, 2, 5, 7, '2026-09-06 05:34:31', '2026-09-06 05:34:31'),
(17, 16, 2, 2, 5, 7, '2026-09-06 05:36:05', '2026-09-06 05:36:05'),
(18, 14, 2, 2, 2, 7, '2026-09-17 13:37:41', '2026-09-17 13:41:54'),
(19, 10, 2, 2, 5, 7, '2026-09-17 13:38:16', '2026-09-17 13:38:16'),
(20, 17, 2, 2, 5, 7, '2026-09-17 13:38:42', '2026-09-17 13:38:42'),
(21, 15, 2, 2, 5, 7, '2026-09-17 13:39:17', '2026-09-17 13:41:20'),
(22, 19, 2, 2, 5, 7, '2026-09-17 13:39:52', '2026-09-17 13:39:52'),
(23, 11, 2, 2, 5, 7, '2026-09-17 13:40:16', '2026-09-17 13:40:16');

-- --------------------------------------------------------

--
-- Table structure for table `tabel_pengguna`
--

CREATE TABLE `tabel_pengguna` (
  `id_pengguna` int NOT NULL,
  `id_role` int NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nip` varchar(50) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL COMMENT 'L = Laki-laki, P = Perempuan',
  `alamat` text,
  `status_kepegawaian` enum('tetap','kontrak','magang') DEFAULT NULL,
  `status_aktif` enum('aktif','nonaktif') DEFAULT 'aktif',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tabel_pengguna`
--

INSERT INTO `tabel_pengguna` (`id_pengguna`, `id_role`, `nama_lengkap`, `username`, `password_hash`, `email`, `nip`, `jabatan`, `no_telepon`, `jenis_kelamin`, `alamat`, `status_kepegawaian`, `status_aktif`, `last_login`, `created_at`, `updated_at`) VALUES
(2, 1, 'Dimas Rifai', 'Super Admin', '$2y$10$yGXqYEpd4S/x.cP6C.IOfeNCnWMLx1U.C0FSONu4v3.NfrlAUDBgy', 'dimasrifai@gmail.com', '330066545', 'Kepala Staff Admin', '082241244652', 'L', 'Jl. Dr. Radjiman No 12. Kota Surakarta', 'tetap', 'aktif', '2026-10-28 13:45:12', '2026-07-25 03:15:08', '2026-10-28 13:45:12'),
(3, 2, 'Anggun Citra', 'angguncitra', '$2y$10$VeLq/mKEuVXNIDgQardxJe.1Ygqz7KxoskRyT9yJIxbxDhJydbUQK', 'angguncitra@gmail.com', '330076893', 'Petugas Surveilans', '082244556789', 'P', 'Jl. Embarkasi Haji. donohudan boyolali', 'tetap', 'aktif', '2026-10-28 13:45:46', '2026-08-01 03:47:19', '2026-10-28 13:45:46'),
(4, 2, 'Tio Miko Syahputra', 'tiomiko', '$2y$10$7t1A1P.WUxzyZntc9Xg1J.Qvc0po6HI4AYZiaa1PdFIht8kIwBvJa', 'tiosyahputra@gmail.com', '', 'Petugas Surveilans', '087667557890', 'L', 'Jl. raya slamet riyadi no 14 kota surakarta', 'magang', 'aktif', '2026-09-01 14:26:51', '2026-09-01 14:23:49', '2026-09-01 14:26:51'),
(5, 2, 'danar malik', 'danar', '$2y$10$GHB2kZ4IpARxLRbhALML1uquKXvkm4jk87EMly9EzGzR6iKCeo3yG', 'danar@gmail.com', '00045778', 'petugas', '082241244689', 'L', 'Cemani', 'magang', 'aktif', '2026-09-23 03:09:41', '2026-09-23 03:09:28', '2026-09-23 03:09:41');

-- --------------------------------------------------------

--
-- Table structure for table `tabel_penyakit`
--

CREATE TABLE `tabel_penyakit` (
  `id_penyakit` int NOT NULL,
  `kode_icd` varchar(10) NOT NULL,
  `nama_populer` varchar(100) NOT NULL COMMENT 'Nama yang akan muncul di grafik dashboard (DBD, ISPA, dll)',
  `status_pantau` enum('aktif','nonaktif') DEFAULT 'aktif' COMMENT 'Apakah penyakit ini masuk dalam radar peringatan dini saat ini?',
  `id_pengguna` int DEFAULT NULL COMMENT 'ID user yang menambahkan/mengedit data terakhir',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tabel_penyakit`
--

INSERT INTO `tabel_penyakit` (`id_penyakit`, `kode_icd`, `nama_populer`, `status_pantau`, `id_pengguna`, `created_at`, `updated_at`) VALUES
(1, 'A91', 'DBD', 'aktif', NULL, '2026-07-23 07:35:22', '2026-07-23 07:35:22'),
(2, 'B20', 'HIV/AIDS', 'aktif', NULL, '2026-08-01 08:27:31', '2026-08-01 08:27:31'),
(3, 'J18', 'Pneumonia', 'aktif', 2, '2026-08-01 08:27:31', '2026-08-19 03:52:43'),
(4, 'A09', 'Diare', 'aktif', 2, '2026-08-01 08:27:31', '2026-09-06 05:24:20'),
(5, 'A15', 'TBC', 'aktif', NULL, '2026-08-01 08:27:31', '2026-08-01 08:27:31'),
(6, 'A92.0', 'Chikungunya', 'aktif', 2, '2026-08-01 08:27:31', '2026-09-06 05:24:20'),
(10, 'B54', 'Malaria', 'aktif', NULL, '2026-09-06 05:30:50', '2026-09-06 05:30:50'),
(11, 'A01.0', 'Tifus / Tipes', 'aktif', NULL, '2026-09-06 05:30:50', '2026-09-06 05:30:50'),
(12, 'B05.9', 'Campak', 'aktif', NULL, '2026-09-06 05:30:50', '2026-09-06 05:30:50'),
(13, 'A00.9', 'Kolera', 'aktif', NULL, '2026-09-06 05:30:50', '2026-09-06 05:30:50'),
(14, 'A27.9', 'Leptospirosis', 'aktif', NULL, '2026-09-06 05:30:50', '2026-09-06 05:30:50'),
(15, 'A82.9', 'Rabies', 'aktif', NULL, '2026-09-06 05:30:50', '2026-09-06 05:30:50'),
(16, 'A36.9', 'Difteri', 'aktif', NULL, '2026-09-06 05:30:50', '2026-09-06 05:30:50'),
(17, 'A37.9', 'Pertusis', 'aktif', NULL, '2026-09-06 05:30:50', '2026-09-06 05:30:50'),
(18, 'U07.1', 'COVID-19', 'aktif', NULL, '2026-09-06 05:30:50', '2026-09-06 05:30:50'),
(19, 'A35', 'Tetanus', 'aktif', NULL, '2026-09-06 05:30:50', '2026-09-06 05:30:50');

-- --------------------------------------------------------

--
-- Table structure for table `tabel_riwayat_aktivitas`
--

CREATE TABLE `tabel_riwayat_aktivitas` (
  `id_log` int NOT NULL,
  `id_pengguna` int NOT NULL COMMENT 'Relasi ke petugas yang melakukan aktivitas',
  `jenis_aktivitas` varchar(50) NOT NULL COMMENT 'Kategori untuk membedakan ikon/warna di frontend',
  `deskripsi` text NOT NULL COMMENT 'Detail teks aktivitas (misal: Kasus baru ditambahkan - DBD di Kel. Penumping)',
  `waktu_aktivitas` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu rekam jejak otomatis'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tabel_riwayat_aktivitas`
--

INSERT INTO `tabel_riwayat_aktivitas` (`id_log`, `id_pengguna`, `jenis_aktivitas`, `deskripsi`, `waktu_aktivitas`) VALUES
(2, 2, 'login', 'Melakukan login ke dalam sistem', '2026-07-25 03:15:37'),
(3, 2, 'logout', 'Melakukan logout dari sistem', '2026-07-25 03:42:58'),
(4, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-07-25 07:34:34'),
(5, 2, 'Logout', 'Melakukan logout dari sistem', '2026-07-25 08:15:40'),
(6, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-07-25 08:16:06'),
(7, 2, 'Logout', 'Melakukan logout dari sistem', '2026-07-25 08:32:33'),
(8, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-07-25 08:33:22'),
(9, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-07-27 01:13:26'),
(10, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-07-28 07:17:33'),
(11, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-07-29 03:37:49'),
(12, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-07-30 02:05:55'),
(13, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-07-31 01:28:06'),
(14, 2, 'Logout', 'Melakukan logout dari sistem', '2026-07-31 08:44:03'),
(15, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-01 01:59:01'),
(16, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-01 03:47:56'),
(17, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-08-01 03:48:22'),
(18, 3, 'Logout', 'Melakukan logout dari sistem', '2026-08-01 04:12:18'),
(19, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-08-01 07:58:13'),
(20, 3, 'Logout', 'Melakukan logout dari sistem', '2026-08-01 08:09:50'),
(21, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-08-01 08:10:20'),
(22, 3, 'Logout', 'Melakukan logout dari sistem', '2026-08-01 08:34:35'),
(23, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-08-01 08:34:56'),
(24, 3, 'Logout', 'Melakukan logout dari sistem', '2026-08-01 09:07:45'),
(25, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-01 09:08:06'),
(26, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-01 09:14:22'),
(27, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-02 00:51:12'),
(28, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-02 03:55:43'),
(29, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-03 01:21:57'),
(30, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-03 07:27:15'),
(31, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-03 09:22:59'),
(32, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-11 00:02:10'),
(33, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-11 07:16:55'),
(34, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-15 02:47:12'),
(35, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-15 02:55:32'),
(36, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-15 02:59:04'),
(37, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-15 03:03:24'),
(38, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-08-15 03:03:36'),
(39, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-15 04:06:42'),
(40, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-15 04:08:17'),
(41, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-08-15 04:09:03'),
(42, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-15 05:23:21'),
(43, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-16 01:52:59'),
(44, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-16 08:43:14'),
(45, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-16 14:01:02'),
(46, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-16 14:32:58'),
(47, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-17 03:20:16'),
(48, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-17 04:11:34'),
(49, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-18 01:29:08'),
(50, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-18 06:05:27'),
(51, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-19 01:17:09'),
(52, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Adeleo Ariko (Kel. Pajang) dengan diagnosis Hipertensi.', '2026-08-19 02:36:24'),
(53, 2, 'Tambah Pasien', 'Mendaftarkan pasien baru atas nama Cantika Kusuma Dewi (Kel. Purwosari) dengan diagnosis awal HIV/AIDS.', '2026-08-19 02:52:51'),
(54, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Burharudin (Kel. Panularan). Status saat ini: TERKONFIRMASI.', '2026-08-19 02:54:41'),
(55, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Sabrina Mutiara Azzahra (Kel. Karangasem). Status saat ini: SEMBUH.', '2026-08-19 03:10:44'),
(56, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Baskoro  (Kel. Pajang). Status saat ini: SEMBUH.', '2026-08-19 03:25:41'),
(57, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Khusnun Salsabila (Kel. Purwosari). Status saat ini: SEMBUH.', '2026-08-19 03:26:03'),
(58, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Baskoro  (Kel. Pajang) dengan diagnosis Hipertensi.', '2026-08-19 03:27:31'),
(59, 2, 'Tambah Penyakit', 'Mendaftarkan master data penyakit baru: <b>Kangker Otak</b> (Kode ICD: C71.9).', '2026-08-19 03:51:20'),
(60, 2, 'Edit Penyakit', 'Memperbarui master data penyakit: <b>Pneumonia</b> (ICD: J18). Status EWS: AKTIF.', '2026-08-19 03:52:43'),
(61, 2, 'Hapus Penyakit', 'Menghapus permanen penyakit <b>DM Type 2</b> (ICD: E11.5) dari sistem pantauan EWS.', '2026-08-19 03:54:08'),
(62, 2, 'Hapus Penyakit', 'Menghapus Soft Delete penyakit <b>Kangker Otak</b> (ICD: C71.9) dari sistem pantauan EWS Data masih Tersimpan di DB Master Data ICD.', '2026-08-19 03:56:52'),
(63, 2, 'Tambah Kelurahan', 'Mendaftarkan wilayah pantauan baru: <b>Kel. Pangandaran</b> (Kode: 33.82.01.1455) dengan populasi 500 jiwa.', '2026-08-19 04:22:08'),
(64, 2, 'Edit Kelurahan', 'Memperbarui master data profil wilayah <b>Kel. Pangandaran</b> (Kode: 33.82.01.1455).', '2026-08-19 04:23:26'),
(65, 2, 'Tambah Kelurahan', 'Mendaftarkan wilayah pantauan baru: <b>Kel. trajan</b> (Kode: 33.89.51.1244) dengan populasi 500 jiwa.', '2026-08-19 06:09:02'),
(66, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-19 07:02:44'),
(67, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-20 07:15:05'),
(68, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-20 07:45:20'),
(69, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-20 07:45:41'),
(70, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-20 09:21:47'),
(71, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-21 01:35:24'),
(72, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-21 03:59:33'),
(73, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-25 15:45:53'),
(74, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-25 16:26:05'),
(75, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-26 07:00:52'),
(76, 2, 'Tambah Pasien', 'Mendaftarkan pasien baru atas nama Yudi syahputra (Kel. Penumping) dengan diagnosis awal HIV/AIDS.', '2026-08-26 07:05:49'),
(77, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Yudi syahputra (Kel. Penumping). Status saat ini: SEMBUH.', '2026-08-26 07:07:34'),
(78, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Adeleo Ariko (Kel. Pangandaran) dengan diagnosis Hipertensi.', '2026-08-26 07:11:24'),
(79, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Anggara Syahputra (Kel. Pangandaran) dengan diagnosis Hipertensi.', '2026-08-26 07:12:37'),
(80, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-26 07:16:18'),
(81, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-08-26 07:17:19'),
(82, 3, 'Logout', 'Melakukan logout dari sistem', '2026-08-26 09:39:52'),
(83, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-28 11:26:27'),
(84, 2, 'Edit Kelurahan', 'Memperbarui master data profil wilayah <b>Kel. Bumi</b> (Kode: 33.72.01.1009).', '2026-08-28 12:30:24'),
(85, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-28 12:30:42'),
(86, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-31 04:35:03'),
(87, 2, 'Tambah Pasien', 'Mendaftarkan pasien baru atas nama Arga Pratama (Kel. Laweyan) dengan diagnosis awal HIV/AIDS.', '2026-08-31 04:43:24'),
(88, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Arga Pratama (Kel. Laweyan). Status saat ini: TERKONFIRMASI.', '2026-08-31 04:46:19'),
(89, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Arief Wicaksono (Kel. Laweyan) dengan diagnosis HIV/AIDS.', '2026-08-31 04:56:58'),
(90, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Arief Wicaksono (Kel. Laweyan). Status saat ini: TERKONFIRMASI.', '2026-08-31 04:58:02'),
(91, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Adeleo Ariko (Kel. Pajang). Status saat ini: SEMBUH.', '2026-08-31 05:09:57'),
(92, 2, 'Edit Penyakit', 'Memperbarui master data penyakit: <b>Hipertensi</b> (ICD: I10). Status EWS: NONAKTIF.', '2026-08-31 05:10:15'),
(93, 2, 'Edit Penyakit', 'Memperbarui master data penyakit: <b>Hipertensi</b> (ICD: I10). Status EWS: AKTIF.', '2026-08-31 05:11:23'),
(94, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-31 05:15:01'),
(95, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-08-31 05:16:13'),
(96, 3, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Baskoro  (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-08-31 05:17:04'),
(97, 3, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Burharudin (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-08-31 05:17:37'),
(98, 3, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Fathan Aryo Mahardito (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-08-31 05:18:04'),
(99, 3, 'Logout', 'Melakukan logout dari sistem', '2026-08-31 06:24:48'),
(100, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-08-31 06:25:03'),
(101, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Cantika Kusuma Dewi (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-08-31 07:08:03'),
(102, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Tio Ardhi Verdiansyah (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-08-31 07:08:39'),
(103, 2, 'Logout', 'Melakukan logout dari sistem', '2026-08-31 14:10:36'),
(104, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-01 02:06:30'),
(105, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Anindya Putri  (Kel. Pajang). Status saat ini: SEMBUH.', '2026-09-01 02:06:57'),
(106, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Nayaka Putri Maharani (Kel. Purwosari). Status saat ini: SEMBUH.', '2026-09-01 02:07:06'),
(107, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Dyah Ayu Kumala (Kel. Karangasem). Status saat ini: SEMBUH.', '2026-09-01 02:07:19'),
(108, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-01 02:34:58'),
(109, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-09-01 02:35:44'),
(110, 3, 'Logout', 'Melakukan logout dari sistem', '2026-09-01 03:08:56'),
(111, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-01 03:09:14'),
(112, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-01 04:07:53'),
(113, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-01 13:11:54'),
(114, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-01 14:26:37'),
(115, 4, 'Login', 'Melakukan login ke dalam sistem', '2026-09-01 14:26:51'),
(116, 4, 'Logout', 'Melakukan logout dari sistem', '2026-09-01 14:27:50'),
(117, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-02 01:12:53'),
(118, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-02 01:27:40'),
(119, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-03 02:21:40'),
(120, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-03 03:35:52'),
(121, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-03 03:36:26'),
(122, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-03 03:37:26'),
(123, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-09-03 03:37:39'),
(124, 3, 'Logout', 'Melakukan logout dari sistem', '2026-09-03 03:38:55'),
(125, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-03 03:39:08'),
(126, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-03 04:15:23'),
(127, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-03 09:45:57'),
(128, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Sularto Handoko (Kel. Jajar). Status saat ini: SEMBUH.', '2026-09-03 10:17:55'),
(129, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-03 10:37:53'),
(130, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-04 12:16:00'),
(131, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-04 13:26:15'),
(132, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-05 02:27:13'),
(133, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-05 03:09:45'),
(134, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-05 05:53:46'),
(135, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-05 05:56:04'),
(136, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-05 06:47:50'),
(137, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-06 03:06:09'),
(138, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-06 03:31:03'),
(139, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-09-06 03:31:17'),
(140, 3, 'Logout', 'Melakukan logout dari sistem', '2026-09-06 03:33:31'),
(141, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-06 03:33:45'),
(142, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Adeleo Ariko (Kel. Karangasem). Status saat ini: SEMBUH.', '2026-09-06 03:43:09'),
(143, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-06 05:49:14'),
(144, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-09-06 05:49:26'),
(145, 3, 'Logout', 'Melakukan logout dari sistem', '2026-09-06 05:50:30'),
(146, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-07 01:57:05'),
(147, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Arief Wicaksono (Kel. Bumi). Status saat ini: SEMBUH.', '2026-09-07 03:01:55'),
(148, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama tes (Kel. Sriwedari). Status saat ini: SEMBUH.', '2026-09-07 03:02:39'),
(149, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama tes 2 (Kel. Sriwedari). Status saat ini: SEMBUH.', '2026-09-07 03:02:55'),
(150, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Budianto (Kel. Pajang). Status saat ini: SEMBUH.', '2026-09-07 03:03:08'),
(151, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Sunarto (Kel. Jajar). Status saat ini: SEMBUH.', '2026-09-07 03:03:19'),
(152, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Praditya Melia Putri (Kel. Laweyan). Status saat ini: SEMBUH.', '2026-09-07 03:03:43'),
(153, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Tio Ardhi Verdiansyah (Kel. Kerten). Status saat ini: SEMBUH.', '2026-09-07 03:03:54'),
(154, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Burharudin (Kel. Panularan). Status saat ini: SEMBUH.', '2026-09-07 03:07:21'),
(155, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Arka syahputra (Kel. Sriwedari). Status saat ini: SEMBUH.', '2026-09-07 03:07:44'),
(156, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Cantika Kusuma Dewi (Kel. Purwosari). Status saat ini: SEMBUH.', '2026-09-07 03:07:56'),
(157, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Arga Pratama (Kel. Laweyan). Status saat ini: SEMBUH.', '2026-09-07 03:08:06'),
(158, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Arief Wicaksono (Kel. Laweyan). Status saat ini: SEMBUH.', '2026-09-07 03:08:28'),
(159, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Baskoro  (Kel. Kerten). Status saat ini: SEMBUH.', '2026-09-07 03:08:48'),
(160, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Burharudin (Kel. Kerten). Status saat ini: SEMBUH.', '2026-09-07 03:08:57'),
(161, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Fathan Aryo Mahardito (Kel. Kerten). Status saat ini: SEMBUH.', '2026-09-07 03:09:26'),
(162, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Cantika Kusuma Dewi (Kel. Kerten). Status saat ini: SEMBUH.', '2026-09-07 03:09:36'),
(163, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Tio Ardhi Verdiansyah (Kel. Kerten). Status saat ini: SEMBUH.', '2026-09-07 03:09:43'),
(164, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Anindya Putri  (Kel. Pajang) dengan diagnosis DBD.', '2026-09-07 03:10:18'),
(165, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Tio Ardhi Verdiansyah (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-09-07 03:10:32'),
(166, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Cantika Kusuma Dewi (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-09-07 03:10:37'),
(167, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Fathan Aryo Mahardito (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-09-07 03:10:41'),
(168, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Burharudin (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-09-07 03:10:45'),
(169, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Baskoro  (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-09-07 03:10:50'),
(170, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Arief Wicaksono (Kel. Laweyan) dengan diagnosis HIV/AIDS.', '2026-09-07 03:10:54'),
(171, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Arga Pratama (Kel. Laweyan) dengan diagnosis HIV/AIDS.', '2026-09-07 03:10:58'),
(172, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Yudi syahputra (Kel. Penumping) dengan diagnosis HIV/AIDS.', '2026-09-07 03:11:02'),
(173, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Cantika Kusuma Dewi (Kel. Purwosari) dengan diagnosis HIV/AIDS.', '2026-09-07 03:11:05'),
(174, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Adeleo Ariko (Kel. Pajang) dengan diagnosis Chikungunya.', '2026-09-07 03:11:10'),
(175, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Arka syahputra (Kel. Sriwedari) dengan diagnosis HIV/AIDS.', '2026-09-07 03:11:14'),
(176, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Burharudin (Kel. Panularan) dengan diagnosis HIV/AIDS.', '2026-09-07 03:11:20'),
(177, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Tio Ardhi Verdiansyah (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-09-07 03:11:24'),
(178, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Praditya Melia Putri (Kel. Laweyan) dengan diagnosis HIV/AIDS.', '2026-09-07 03:11:38'),
(179, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Sunarto (Kel. Jajar) dengan diagnosis HIV/AIDS.', '2026-09-07 03:11:42'),
(180, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Budianto (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-07 03:11:46'),
(181, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama tes 2 (Kel. Sriwedari) dengan diagnosis HIV/AIDS.', '2026-09-07 03:11:49'),
(182, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama tes (Kel. Sriwedari) dengan diagnosis HIV/AIDS.', '2026-09-07 03:11:52'),
(183, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Arief Wicaksono (Kel. Bumi) dengan diagnosis HIV/AIDS.', '2026-09-07 03:12:37'),
(184, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Adeleo Ariko (Kel. Karangasem) dengan diagnosis HIV/AIDS.', '2026-09-07 03:12:41'),
(185, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Sularto Handoko (Kel. Jajar) dengan diagnosis HIV/AIDS.', '2026-09-07 03:12:44'),
(186, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Sabrina Mutiara Azzahra (Kel. Karangasem) dengan diagnosis HIV/AIDS.', '2026-09-07 03:12:47'),
(187, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Dyah Ayu Kumala (Kel. Karangasem) dengan diagnosis HIV/AIDS.', '2026-09-07 03:12:50'),
(188, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Nayaka Putri Maharani (Kel. Purwosari) dengan diagnosis DBD.', '2026-09-07 03:12:53'),
(189, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Khusnun Salsabila (Kel. Purwosari) dengan diagnosis DBD.', '2026-09-07 03:12:56'),
(190, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-07 04:47:34'),
(191, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-09 02:06:12'),
(192, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Arga Pratama (Kel. Purwosari) dengan diagnosis Chikungunya.', '2026-09-09 02:13:58'),
(193, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Aida Kusuma Wardani (Kel. Kerten) dengan diagnosis Pneumonia.', '2026-09-09 02:15:20'),
(194, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Apriliani Putri Marathesa (Kel. Pajang) dengan diagnosis Malaria.', '2026-09-09 02:15:44'),
(195, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Apriliani Putri Marathesa (Kel. Pajang). Status saat ini: TERKONFIRMASI.', '2026-09-09 02:16:52'),
(196, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Arga Pratama (Kel. Purwosari). Status saat ini: TERKONFIRMASI.', '2026-09-09 02:17:28'),
(197, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Apriliani Putri Marathesa (Kel. Pajang). Status saat ini: TERKONFIRMASI.', '2026-09-09 02:17:58'),
(198, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Aida Kusuma Wardani (Kel. Sriwedari) dengan diagnosis Campak.', '2026-09-09 02:18:54'),
(199, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Apriliani Putri Marathesa (Kel. Pajang). Status saat ini: MENINGGAL.', '2026-09-09 02:19:37'),
(200, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Apriliani Putri Marathesa (Kel. Pajang). Status saat ini: SEMBUH.', '2026-09-09 02:20:31'),
(201, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Apriliani Putri Marathesa (Kel. Pajang) dengan diagnosis Malaria.', '2026-09-09 02:21:17'),
(202, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Aida Kusuma Wardani (Kel. Sriwedari). Status saat ini: SEMBUH.', '2026-09-09 02:21:25'),
(203, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Aida Kusuma Wardani (Kel. Sriwedari) dengan diagnosis Campak.', '2026-09-09 02:21:29'),
(204, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Aida Kusuma Wardani (Kel. Kerten). Status saat ini: SEMBUH.', '2026-09-09 02:21:37'),
(205, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Aida Kusuma Wardani (Kel. Kerten) dengan diagnosis Pneumonia.', '2026-09-09 02:21:42'),
(206, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Arga Pratama (Kel. Purwosari). Status saat ini: SEMBUH.', '2026-09-09 02:21:50'),
(207, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Arga Pratama (Kel. Purwosari) dengan diagnosis Chikungunya.', '2026-09-09 02:21:54'),
(208, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-09 03:22:37'),
(209, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-09-09 03:23:17'),
(210, 3, 'Logout', 'Melakukan logout dari sistem', '2026-09-09 03:24:09'),
(211, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-09 03:24:46'),
(212, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-09 12:19:34'),
(213, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-11 02:04:55'),
(214, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-15 13:30:02'),
(215, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-15 13:35:55'),
(216, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-16 02:07:45'),
(217, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-16 02:09:46'),
(218, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-16 02:17:06'),
(219, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Olivia Jensen (Kel. Bumi) dengan diagnosis DBD.', '2026-09-16 02:27:42'),
(220, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Nadia Vega (Kel. Pajang) dengan diagnosis DBD.', '2026-09-16 02:27:49'),
(221, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Maya Septha (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-09-16 02:27:53'),
(222, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Oka Darmawan (Kel. Laweyan) dengan diagnosis DBD.', '2026-09-16 02:27:57'),
(223, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Niko Saputra (Kel. Karangasem) dengan diagnosis DBD.', '2026-09-16 02:28:16'),
(224, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Laras Sekar (Kel. Sondakan) dengan diagnosis DBD.', '2026-09-16 02:28:22'),
(225, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Kiki Fatmala (Kel. Panularan) dengan diagnosis DBD.', '2026-09-16 02:28:36'),
(226, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Maulana Malik (Kel. Jajar) dengan diagnosis HIV/AIDS.', '2026-09-16 02:28:39'),
(227, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Lukman Hakim (Kel. Purwosari) dengan diagnosis DBD.', '2026-09-16 02:28:44'),
(228, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Jihan Audy (Kel. Laweyan) dengan diagnosis DBD.', '2026-09-16 02:28:49'),
(229, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Intan Permata (Kel. Karangasem) dengan diagnosis DBD.', '2026-09-16 02:28:55'),
(230, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Ratih Kumalasari (Kel. Sondakan) dengan diagnosis DBD.', '2026-09-16 02:29:06'),
(231, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Hana Maharani (Kel. Jajar) dengan diagnosis HIV/AIDS.', '2026-09-16 02:29:10'),
(232, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Kevin Sanjaya (Kel. Penumping) dengan diagnosis DBD.', '2026-09-16 02:29:20'),
(233, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Joko Susilo (Kel. Sriwedari) dengan diagnosis DBD.', '2026-09-16 02:29:24'),
(234, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Gita Gutawa (Kel. Purwosari) dengan diagnosis DBD.', '2026-09-16 02:29:35'),
(235, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Fitri Handayani (Kel. Penumping) dengan diagnosis DBD.', '2026-09-16 02:29:39'),
(236, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Iqbal Maulana (Kel. Panularan) dengan diagnosis DBD.', '2026-09-16 02:29:44'),
(237, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Hendra Gunawan (Kel. Bumi) dengan diagnosis HIV/AIDS.', '2026-09-16 02:30:08'),
(238, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Gilang Saputra (Kel. Laweyan) dengan diagnosis DBD.', '2026-09-16 02:30:13'),
(239, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Eka Fitriani (Kel. Sriwedari) dengan diagnosis DBD.', '2026-09-16 02:30:18'),
(240, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Dina Lorenza (Kel. Bumi) dengan diagnosis HIV/AIDS.', '2026-09-16 02:30:22'),
(241, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Citra Kirana (Kel. Pajang) dengan diagnosis DBD.', '2026-09-16 02:30:27'),
(242, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Fajar Ramadhan (Kel. Pajang) dengan diagnosis DBD.', '2026-09-16 02:30:36'),
(243, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Eko Prasetyo (Kel. Karangasem) dengan diagnosis DBD.', '2026-09-16 02:30:40'),
(244, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Dika Anggara (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-09-16 02:30:51'),
(245, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Bella Saphira (Kel. Kerten) dengan diagnosis DBD.', '2026-09-16 02:32:44'),
(246, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Ayu Lestari (Kel. Sondakan) dengan diagnosis DBD.', '2026-09-16 02:32:48'),
(247, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Candra Wijaya (Kel. Jajar) dengan diagnosis DBD.', '2026-09-16 02:32:53'),
(248, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Bagas Wicaksono (Kel. Sondakan) dengan diagnosis DBD.', '2026-09-16 02:32:57'),
(249, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Aditya Pratama (Kel. Purwosari) dengan diagnosis DBD.', '2026-09-16 02:33:01'),
(250, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Sarah Maharani (Kel. Sriwedari) dengan diagnosis DBD.', '2026-09-16 02:42:14'),
(251, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Ayu Lestari (Kel. Penumping) dengan diagnosis HIV/AIDS.', '2026-09-16 02:42:18'),
(252, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Araka Aditya (Kel. Bumi) dengan diagnosis DBD.', '2026-09-16 02:42:22'),
(253, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Dewi Anggraini (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-09-16 02:42:25'),
(254, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Aditya Pratama (Kel. Penumping) dengan diagnosis HIV/AIDS.', '2026-09-16 02:43:49'),
(255, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Apriliani Putri Marathesa (Kel. Penumping) dengan diagnosis HIV/AIDS.', '2026-09-16 02:44:17'),
(256, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Aida Kusuma Wardani (Kel. Penumping) dengan diagnosis Campak.', '2026-09-16 02:44:51'),
(257, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Arga Pratama (Kel. Penumping) dengan diagnosis DBD.', '2026-09-16 02:46:49'),
(258, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Aida Kusuma Wardani (Kel. Penumping). Status saat ini: TERKONFIRMASI.', '2026-09-16 02:53:07'),
(259, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Aditya Pratama (Kel. Penumping). Status saat ini: TERKONFIRMASI.', '2026-09-16 02:53:17'),
(260, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Apriliani Putri Marathesa (Kel. Penumping). Status saat ini: SEMBUH.', '2026-09-16 03:16:40'),
(261, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Apriliani Putri Marathesa (Kel. Penumping) dengan diagnosis HIV/AIDS.', '2026-09-16 03:16:46'),
(262, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Aida Kusuma Wardani (Kel. Penumping). Status saat ini: SEMBUH.', '2026-09-16 03:16:59'),
(263, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Aida Kusuma Wardani (Kel. Penumping) dengan diagnosis Campak.', '2026-09-16 03:17:03'),
(264, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Aditya Pratama (Kel. Penumping). Status saat ini: SEMBUH.', '2026-09-16 03:17:14'),
(265, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Aditya Pratama (Kel. Penumping) dengan diagnosis HIV/AIDS.', '2026-09-16 03:17:17'),
(266, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Arga Pratama (Kel. Penumping). Status saat ini: SEMBUH.', '2026-09-16 03:17:27'),
(267, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Arga Pratama (Kel. Penumping) dengan diagnosis DBD.', '2026-09-16 03:17:36'),
(268, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-16 13:28:22'),
(269, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-17 11:20:37'),
(270, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-17 11:20:51'),
(271, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-09-17 11:36:43'),
(272, 3, 'Logout', 'Melakukan logout dari sistem', '2026-09-17 11:36:52'),
(273, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-17 12:45:23'),
(274, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Nisa Rahmawati (Kel. Penumping) dengan diagnosis DBD.', '2026-09-17 13:06:43'),
(275, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Galih Kusuma (Kel. Kerten) dengan diagnosis DBD.', '2026-09-17 13:06:48'),
(276, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Luna Safitri (Kel. Sondakan) dengan diagnosis DBD.', '2026-09-17 13:06:52'),
(277, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Kirana Larasati (Kel. Purwosari) dengan diagnosis DBD.', '2026-09-17 13:06:57'),
(278, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Fadly Hidayat (Kel. Jajar) dengan diagnosis DBD.', '2026-09-17 13:07:02'),
(279, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Jessica Kumala (Kel. Penumping) dengan diagnosis DBD.', '2026-09-17 13:07:07'),
(280, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Indah Permatasari (Kel. Sriwedari) dengan diagnosis HIV/AIDS.', '2026-09-17 13:07:12'),
(281, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Eza Maulana (Kel. Sondakan) dengan diagnosis DBD.', '2026-09-17 13:07:17'),
(282, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Hesti Lestari (Kel. Panularan) dengan diagnosis DBD.', '2026-09-17 13:08:06'),
(283, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Gisella Rahma (Kel. Bumi) dengan diagnosis DBD.', '2026-09-17 13:08:11'),
(284, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Denny Setiawan (Kel. Purwosari) dengan diagnosis HIV/AIDS.', '2026-09-17 13:08:16'),
(285, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Farah Annisa (Kel. Laweyan) dengan diagnosis DBD.', '2026-09-17 13:08:21'),
(286, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Elvira Cantika (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-17 13:08:25'),
(287, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Chico Hakim (Kel. Penumping) dengan diagnosis DBD.', '2026-09-17 13:08:29'),
(288, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Desy Maharani (Kel. Karangasem) dengan diagnosis DBD.', '2026-09-17 13:08:33'),
(289, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Clara Shinta (Kel. Kerten) dengan diagnosis DBD.', '2026-09-17 13:08:37'),
(290, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Abimana Putra (Kel. Sriwedari) dengan diagnosis DBD.', '2026-09-17 13:08:41'),
(291, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Bunga Citra (Kel. Jajar) dengan diagnosis DBD.', '2026-09-17 13:08:45'),
(292, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Amalia Hasanah (Kel. Sondakan) dengan diagnosis HIV/AIDS.', '2026-09-17 13:08:49'),
(293, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Vino Kurniawan (Kel. Panularan) dengan diagnosis DBD.', '2026-09-17 13:08:53'),
(294, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Zaskia Putri (Kel. Purwosari) dengan diagnosis DBD.', '2026-09-17 13:08:57'),
(295, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Yuni Kartika (Kel. Penumping) dengan diagnosis DBD.', '2026-09-17 13:09:02'),
(296, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Rio Ramadhan (Kel. Bumi) dengan diagnosis HIV/AIDS.', '2026-09-17 13:09:07'),
(297, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Wulan Sari (Kel. Sriwedari) dengan diagnosis DBD.', '2026-09-17 13:09:11'),
(298, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Tari Larasati (Kel. Panularan) dengan diagnosis HIV/AIDS.', '2026-09-17 13:09:18'),
(299, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Samuel Anggara (Kel. Laweyan) dengan diagnosis DBD.', '2026-09-17 13:09:24'),
(300, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Sinta Nuriyah (Kel. Bumi) dengan diagnosis DBD.', '2026-09-17 13:09:29'),
(301, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Rani Permata (Kel. Laweyan) dengan diagnosis DBD.', '2026-09-17 13:09:36'),
(302, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Reza Pratama (Kel. Pajang) dengan diagnosis DBD.', '2026-09-17 13:09:43'),
(303, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Dinda Kanya (Kel. Pajang) dengan diagnosis DBD.', '2026-09-17 13:09:47'),
(304, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Salsa Billa (Kel. Karangasem) dengan diagnosis HIV/AIDS.', '2026-09-17 13:09:51'),
(305, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Ricky Saputra (Kel. Karangasem) dengan diagnosis HIV/AIDS.', '2026-09-17 13:09:55'),
(306, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Vina Panduwinata (Kel. Kerten) dengan diagnosis DBD.', '2026-09-17 13:10:09'),
(307, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Putri Kirana (Kel. Jajar) dengan diagnosis DBD.', '2026-09-17 13:10:15'),
(308, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Andi Wijaya (Kel. Kerten) dengan diagnosis DBD.', '2026-09-17 13:10:19'),
(309, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Siti Aisyah (Kel. Sondakan) dengan diagnosis HIV/AIDS.', '2026-09-17 13:10:24'),
(310, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Syifa Hadju (Kel. Panularan) dengan diagnosis HIV/AIDS.', '2026-09-17 13:25:00'),
(311, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Niko Al Hakim (Kel. Laweyan) dengan diagnosis DBD.', '2026-09-17 13:25:05'),
(312, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Khaerunisa Fitri (Kel. Bumi) dengan diagnosis DBD.', '2026-09-17 13:25:09'),
(313, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Maulana Malik (Kel. Pajang) dengan diagnosis DBD.', '2026-09-17 13:25:14'),
(314, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Salma Salsabila (Kel. Laweyan) dengan diagnosis DBD.', '2026-09-17 13:25:18'),
(315, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Lukman Sardi (Kel. Karangasem) dengan diagnosis DBD.', '2026-09-17 13:25:22'),
(316, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Nabila Syakira (Kel. Pajang) dengan diagnosis DBD.', '2026-09-17 13:25:26'),
(317, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Rahma Diani (Kel. Karangasem) dengan diagnosis HIV/AIDS.', '2026-09-17 13:25:30'),
(318, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Kevin Sanjaya (Kel. Kerten) dengan diagnosis HIV/AIDS.', '2026-09-17 13:25:34'),
(319, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Luthfi Aulia (Kel. Kerten) dengan diagnosis DBD.', '2026-09-17 13:25:39'),
(320, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Alya Nurul (Kel. Jajar) dengan diagnosis DBD.', '2026-09-17 13:25:45'),
(321, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Joko Susilo (Kel. Jajar) dengan diagnosis DBD.', '2026-09-17 13:25:49'),
(322, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Risma Anggraini (Kel. Sondakan) dengan diagnosis HIV/AIDS.', '2026-09-17 13:25:53'),
(323, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Dewi Lestari (Kel. Purwosari) dengan diagnosis DBD.', '2026-09-17 13:25:57'),
(324, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Irfan Bachdim (Kel. Sondakan) dengan diagnosis DBD.', '2026-09-17 13:26:01'),
(325, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Safira Maharani (Kel. Penumping) dengan diagnosis DBD.', '2026-09-17 13:26:05'),
(326, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Fatimah Azzahra (Kel. Sriwedari) dengan diagnosis DBD.', '2026-09-17 13:26:09'),
(327, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Hendra Setiawan (Kel. Purwosari) dengan diagnosis HIV/AIDS.', '2026-09-17 13:26:13'),
(328, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama  Zahra Aulia (Kel. Panularan) dengan diagnosis HIV/AIDS.', '2026-09-17 13:26:16'),
(329, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Sivia Azizah (Kel. Bumi) dengan diagnosis DBD.', '2026-09-17 13:26:20'),
(330, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Gilang Ramadhan (Kel. Penumping) dengan diagnosis DBD.', '2026-09-17 13:26:23'),
(331, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Rara Sekar (Kel. Laweyan) dengan diagnosis DBD.', '2026-09-17 13:26:27'),
(332, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Tiara Andini (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-17 13:26:31'),
(333, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Fajar Sidik (Kel. Sriwedari) dengan diagnosis DBD.', '2026-09-17 13:26:34'),
(334, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Nadia Safitri (Kel. Karangasem) dengan diagnosis DBD.', '2026-09-17 13:26:38'),
(335, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Rina Melati (Kel. Purwosari) dengan diagnosis DBD.', '2026-09-17 13:26:42'),
(336, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Budi Santoso (Kel. Jajar) dengan diagnosis DBD.', '2026-09-17 13:26:48'),
(337, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama  Zahra Aulia (Kel. Karangasem) dengan diagnosis Malaria.', '2026-09-17 13:27:49'),
(338, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Abimana Putra (Kel. Karangasem) dengan diagnosis Malaria.', '2026-09-17 13:28:35'),
(339, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Aditya Pratama (Kel. Karangasem) dengan diagnosis Malaria.', '2026-09-17 13:29:54'),
(340, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Alya Nurul (Kel. Karangasem) dengan diagnosis Malaria.', '2026-09-17 13:30:20'),
(341, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama  Zahra Aulia (Kel. Bumi) dengan diagnosis Chikungunya.', '2026-09-17 14:02:09'),
(342, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Abimana Putra (Kel. Bumi) dengan diagnosis Chikungunya.', '2026-09-17 14:02:41'),
(343, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Aditya Pratama (Kel. Bumi) dengan diagnosis Chikungunya.', '2026-09-17 14:03:09'),
(344, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-17 14:04:10'),
(345, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-18 04:03:25'),
(346, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Andi Wijaya (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-18 04:04:35'),
(347, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Clara Shinta (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-18 04:05:19'),
(348, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Denny Setiawan (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-18 04:05:50'),
(349, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Eka Fitriani (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-18 04:06:12'),
(350, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Fadly Hidayat (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-18 04:06:39'),
(351, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Budi Santoso (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-18 04:07:20'),
(352, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-18 14:50:27'),
(353, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-21 03:02:24'),
(354, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-21 10:30:06'),
(355, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-09-23 02:45:46'),
(356, 3, 'Logout', 'Melakukan logout dari sistem', '2026-09-23 03:07:40'),
(357, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-23 03:08:01'),
(358, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-23 03:09:32'),
(359, 5, 'Login', 'Melakukan login ke dalam sistem', '2026-09-23 03:09:41'),
(360, 5, 'Tambah Pasien', 'Mendaftarkan pasien baru atas nama coba (Kel. Laweyan) dengan diagnosis awal Chikungunya.', '2026-09-23 03:30:01'),
(361, 5, 'Tambah Pasien', 'Mendaftarkan pasien baru atas nama Salsabila (Kel. Pajang) dengan diagnosis awal Chikungunya.', '2026-09-23 03:37:10'),
(362, 5, 'Tambah Pasien', 'Mendaftarkan pasien baru atas nama Hanun (Kel. Laweyan) dengan diagnosis awal Chikungunya.', '2026-09-23 03:47:57'),
(363, 5, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Chico Hakim (Kel. Jajar) dengan diagnosis HIV/AIDS.', '2026-09-23 03:48:32'),
(364, 5, 'Logout', 'Melakukan logout dari sistem', '2026-09-23 03:49:28'),
(365, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-09-23 03:49:43'),
(366, 3, 'Tambah Pasien', 'Mendaftarkan pasien baru atas nama kiara rifai (Kel. Kerten) dengan diagnosis awal Malaria.', '2026-09-23 03:50:53'),
(367, 3, 'Tambah Pasien', 'Mendaftarkan pasien baru atas nama dwirara (Kel. Laweyan) dengan diagnosis awal Chikungunya.', '2026-09-23 04:08:08'),
(368, 3, 'Logout', 'Melakukan logout dari sistem', '2026-09-23 04:08:19'),
(369, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-23 04:08:33'),
(370, 2, 'Tambah Pasien', 'Mendaftarkan pasien baru atas nama bintang (Kel. Pajang) dengan diagnosis awal HIV/AIDS.', '2026-09-23 04:09:41'),
(371, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-23 04:10:03'),
(372, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-09-23 04:10:17'),
(373, 3, 'Tambah Pasien', 'Mendaftarkan pasien baru atas nama Bimanesa (Kel. Panularan) dengan diagnosis awal Chikungunya.', '2026-09-23 04:11:54'),
(374, 3, 'Logout', 'Melakukan logout dari sistem', '2026-09-23 05:53:27'),
(375, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-23 05:53:40'),
(376, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-23 05:59:40'),
(377, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-09-23 05:59:52'),
(378, 3, 'Tambah Pasien', 'Mendaftarkan pasien baru atas nama Farkhan (Kel. Laweyan) dengan diagnosis awal Chikungunya.', '2026-09-23 06:01:26'),
(379, 3, 'Logout', 'Melakukan logout dari sistem', '2026-09-23 08:27:01'),
(380, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-09-24 03:12:08'),
(381, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Fadly Hidayat (Kel. Pajang). Status saat ini: SEMBUH.', '2026-09-24 03:12:39'),
(382, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Denny Setiawan (Kel. Pajang). Status saat ini: SEMBUH.', '2026-09-24 03:12:47'),
(383, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Abimana Putra (Kel. Bumi). Status saat ini: SEMBUH.', '2026-09-24 03:12:54'),
(384, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Eka Fitriani (Kel. Pajang). Status saat ini: SEMBUH.', '2026-09-24 03:13:00'),
(385, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama  Zahra Aulia (Kel. Bumi). Status saat ini: SEMBUH.', '2026-09-24 03:13:08'),
(386, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Clara Shinta (Kel. Pajang). Status saat ini: SEMBUH.', '2026-09-24 03:13:16'),
(387, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Budi Santoso (Kel. Pajang). Status saat ini: SEMBUH.', '2026-09-24 03:13:24'),
(388, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Aditya Pratama (Kel. Bumi). Status saat ini: SEMBUH.', '2026-09-24 03:14:13'),
(389, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Andi Wijaya (Kel. Pajang). Status saat ini: SEMBUH.', '2026-09-24 03:14:21'),
(390, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Hanun (Kel. Laweyan). Status saat ini: SEMBUH.', '2026-09-24 03:14:33'),
(391, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Salsabila (Kel. Pajang). Status saat ini: SEMBUH.', '2026-09-24 03:14:40'),
(392, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama kiara rifai (Kel. Kerten). Status saat ini: SEMBUH.', '2026-09-24 03:14:47'),
(393, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama dwirara (Kel. Laweyan). Status saat ini: SEMBUH.', '2026-09-24 03:15:27'),
(394, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama coba (Kel. Laweyan). Status saat ini: SEMBUH.', '2026-09-24 03:15:36'),
(395, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Chico Hakim (Kel. Jajar). Status saat ini: SEMBUH.', '2026-09-24 03:17:05'),
(396, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama bintang (Kel. Pajang). Status saat ini: SEMBUH.', '2026-09-24 03:17:13'),
(397, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Bimanesa (Kel. Panularan). Status saat ini: SEMBUH.', '2026-09-24 03:17:20'),
(398, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Farkhan (Kel. Laweyan). Status saat ini: SEMBUH.', '2026-09-24 03:17:25'),
(399, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Andi Wijaya (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-24 03:54:34'),
(400, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Budi Santoso (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-24 03:54:39'),
(401, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Clara Shinta (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-24 03:54:42'),
(402, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Aditya Pratama (Kel. Bumi) dengan diagnosis Chikungunya.', '2026-09-24 03:54:46'),
(403, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Eka Fitriani (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-24 03:54:50'),
(404, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama  Zahra Aulia (Kel. Bumi) dengan diagnosis Chikungunya.', '2026-09-24 03:54:54'),
(405, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Abimana Putra (Kel. Bumi) dengan diagnosis Chikungunya.', '2026-09-24 03:54:59'),
(406, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Denny Setiawan (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-24 03:55:02'),
(407, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Farkhan (Kel. Laweyan) dengan diagnosis Chikungunya.', '2026-09-24 03:55:07'),
(408, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama kiara rifai (Kel. Kerten) dengan diagnosis Malaria.', '2026-09-24 03:55:12'),
(409, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Bimanesa (Kel. Panularan) dengan diagnosis Chikungunya.', '2026-09-24 03:55:15'),
(410, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama bintang (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-09-24 03:55:18'),
(411, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Chico Hakim (Kel. Jajar) dengan diagnosis HIV/AIDS.', '2026-09-24 03:55:21'),
(412, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Dewi Lestari (Kel. Kerten) dengan diagnosis DBD.', '2026-09-24 03:56:11'),
(413, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Fatimah Azzahra (Kel. Kerten) dengan diagnosis DBD.', '2026-09-24 03:56:35');
INSERT INTO `tabel_riwayat_aktivitas` (`id_log`, `id_pengguna`, `jenis_aktivitas`, `deskripsi`, `waktu_aktivitas`) VALUES
(414, 2, 'Logout', 'Melakukan logout dari sistem', '2026-09-24 12:30:13'),
(415, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-02 01:30:09'),
(416, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-24 04:10:05'),
(417, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-24 05:49:59'),
(418, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-10-24 05:50:15'),
(419, 3, 'Logout', 'Melakukan logout dari sistem', '2026-10-24 05:56:07'),
(420, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-24 05:56:21'),
(421, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-24 05:56:56'),
(422, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-10-24 05:57:12'),
(423, 3, 'Logout', 'Melakukan logout dari sistem', '2026-10-24 05:58:35'),
(424, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-24 05:58:50'),
(425, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-24 06:25:18'),
(426, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-10-24 06:25:30'),
(427, 3, 'Logout', 'Melakukan logout dari sistem', '2026-10-24 06:33:24'),
(428, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-24 06:33:38'),
(429, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-26 06:21:38'),
(430, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Aditya Pratama (Kel. Laweyan) dengan diagnosis Pneumonia.', '2026-10-26 06:47:15'),
(431, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Aditya Pratama (Kel. Laweyan). Status saat ini: SEMBUH.', '2026-10-26 12:29:08'),
(432, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Fatimah Azzahra (Kel. Kerten). Status saat ini: SEMBUH.', '2026-10-26 12:29:14'),
(433, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Dewi Lestari (Kel. Kerten). Status saat ini: SEMBUH.', '2026-10-26 12:29:20'),
(434, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Hanun (Kel. Laweyan) dengan diagnosis Chikungunya.', '2026-10-26 12:30:39'),
(435, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Salsabila (Kel. Pajang) dengan diagnosis Chikungunya.', '2026-10-26 12:30:42'),
(436, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama dwirara (Kel. Laweyan) dengan diagnosis Chikungunya.', '2026-10-26 12:30:46'),
(437, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Fadly Hidayat (Kel. Pajang) dengan diagnosis HIV/AIDS.', '2026-10-26 12:30:51'),
(438, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama coba (Kel. Laweyan) dengan diagnosis Chikungunya.', '2026-10-26 12:30:54'),
(439, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Dewi Lestari (Kel. Kerten) dengan diagnosis DBD.', '2026-10-26 12:30:56'),
(440, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Fatimah Azzahra (Kel. Kerten) dengan diagnosis DBD.', '2026-10-26 12:30:58'),
(441, 2, 'Hapus Kasus', 'Mengarsipkan (Soft Delete) data kasus atas nama Aditya Pratama (Kel. Laweyan) dengan diagnosis Pneumonia.', '2026-10-26 12:31:02'),
(442, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Clara Shinta (Kel. Jajar) dengan diagnosis HIV/AIDS.', '2026-10-26 12:51:01'),
(443, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Chico Hakim (Kel. Jajar) dengan diagnosis HIV/AIDS.', '2026-10-26 12:51:24'),
(444, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Clara Shinta (Kel. Jajar). Status saat ini: SEMBUH.', '2026-10-26 12:52:03'),
(445, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Clara Shinta (Kel. Jajar). Status saat ini: SEMBUH.', '2026-10-26 12:52:28'),
(446, 2, 'Edit Kasus', 'Memperbarui data medis pasien atas nama Chico Hakim (Kel. Jajar). Status saat ini: SEMBUH.', '2026-10-26 12:52:33'),
(447, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-26 13:29:52'),
(448, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-27 02:14:00'),
(449, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-27 10:44:44'),
(450, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-27 11:07:33'),
(451, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Bimanesa (Kel. Jajar) dengan diagnosis Chikungunya.', '2026-10-27 11:49:25'),
(452, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-27 11:51:03'),
(453, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-27 12:01:05'),
(454, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-27 12:03:22'),
(455, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-28 04:49:21'),
(456, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-28 04:49:25'),
(457, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-28 04:52:11'),
(458, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-28 04:57:39'),
(459, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-28 07:51:41'),
(460, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-28 08:39:04'),
(461, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-28 08:39:26'),
(462, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-28 08:44:50'),
(463, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-28 08:45:49'),
(464, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-28 08:46:04'),
(465, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-10-28 08:46:22'),
(466, 3, 'Logout', 'Melakukan logout dari sistem', '2026-10-28 08:46:38'),
(467, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-28 13:34:31'),
(468, 2, 'Tambah Kasus', 'Menambahkan data kasus baru atas nama Denny Setiawan (Kel. Jajar) dengan diagnosis Chikungunya.', '2026-10-28 13:35:00'),
(469, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-28 13:35:17'),
(470, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-28 13:37:23'),
(471, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-28 13:37:28'),
(472, 2, 'Login', 'Melakukan login ke dalam sistem', '2026-10-28 13:45:12'),
(473, 2, 'Logout', 'Melakukan logout dari sistem', '2026-10-28 13:45:28'),
(474, 3, 'Login', 'Melakukan login ke dalam sistem', '2026-10-28 13:45:46'),
(475, 3, 'Logout', 'Melakukan logout dari sistem', '2026-10-28 13:45:53');

-- --------------------------------------------------------

--
-- Table structure for table `tabel_role`
--

CREATE TABLE `tabel_role` (
  `id_role` int NOT NULL,
  `nama_role` varchar(50) NOT NULL,
  `deskripsi` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tabel_role`
--

INSERT INTO `tabel_role` (`id_role`, `nama_role`, `deskripsi`, `created_at`) VALUES
(1, 'Administrator', 'Memiliki hak akses penuh ke seluruh fitur EWS Command Center dan manajemen pengguna', '2026-07-23 07:19:54'),
(2, 'Petugas', 'Bertanggung jawab melakukan entri data kasus harian, memperbarui status klinis pasien, dan memvalidasi data demografi guna mendukung akurasi sistem peringatan dini.', '2026-08-01 03:45:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tabel_alert`
--
ALTER TABLE `tabel_alert`
  ADD PRIMARY KEY (`id_alert`),
  ADD KEY `fk_alert_kelurahan` (`id_kelurahan`),
  ADD KEY `fk_alert_penyakit` (`id_penyakit`),
  ADD KEY `fk_alert_verifikator` (`id_verifikator`),
  ADD KEY `fk_alert_pemicu` (`id_pengguna`);

--
-- Indexes for table `tabel_kasus`
--
ALTER TABLE `tabel_kasus`
  ADD PRIMARY KEY (`id_kasus`),
  ADD KEY `fk_kasus_pengguna` (`id_pengguna`),
  ADD KEY `fk_kasus_penyakit` (`id_penyakit`),
  ADD KEY `fk_kasus_kelurahan` (`id_kelurahan`);

--
-- Indexes for table `tabel_kelurahan`
--
ALTER TABLE `tabel_kelurahan`
  ADD PRIMARY KEY (`id_kelurahan`),
  ADD UNIQUE KEY `kode_wilayah` (`kode_wilayah`),
  ADD KEY `fk_kelurahan_admin` (`id_pengguna`);

--
-- Indexes for table `tabel_master_icd10`
--
ALTER TABLE `tabel_master_icd10`
  ADD PRIMARY KEY (`kode_icd`);

--
-- Indexes for table `tabel_pengaturan_threshold`
--
ALTER TABLE `tabel_pengaturan_threshold`
  ADD PRIMARY KEY (`id_threshold`),
  ADD KEY `fk_threshold_penyakit` (`id_penyakit`),
  ADD KEY `fk_threshold_pengguna` (`id_pengguna`);

--
-- Indexes for table `tabel_pengguna`
--
ALTER TABLE `tabel_pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_pengguna_role` (`id_role`);

--
-- Indexes for table `tabel_penyakit`
--
ALTER TABLE `tabel_penyakit`
  ADD PRIMARY KEY (`id_penyakit`),
  ADD KEY `fk_penyakit_icd` (`kode_icd`),
  ADD KEY `fk_penyakit_pengguna` (`id_pengguna`);

--
-- Indexes for table `tabel_riwayat_aktivitas`
--
ALTER TABLE `tabel_riwayat_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_log_pengguna` (`id_pengguna`);

--
-- Indexes for table `tabel_role`
--
ALTER TABLE `tabel_role`
  ADD PRIMARY KEY (`id_role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tabel_alert`
--
ALTER TABLE `tabel_alert`
  MODIFY `id_alert` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `tabel_kasus`
--
ALTER TABLE `tabel_kasus`
  MODIFY `id_kasus` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT for table `tabel_kelurahan`
--
ALTER TABLE `tabel_kelurahan`
  MODIFY `id_kelurahan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tabel_pengaturan_threshold`
--
ALTER TABLE `tabel_pengaturan_threshold`
  MODIFY `id_threshold` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tabel_pengguna`
--
ALTER TABLE `tabel_pengguna`
  MODIFY `id_pengguna` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tabel_penyakit`
--
ALTER TABLE `tabel_penyakit`
  MODIFY `id_penyakit` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tabel_riwayat_aktivitas`
--
ALTER TABLE `tabel_riwayat_aktivitas`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=476;

--
-- AUTO_INCREMENT for table `tabel_role`
--
ALTER TABLE `tabel_role`
  MODIFY `id_role` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tabel_alert`
--
ALTER TABLE `tabel_alert`
  ADD CONSTRAINT `fk_alert_kelurahan` FOREIGN KEY (`id_kelurahan`) REFERENCES `tabel_kelurahan` (`id_kelurahan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_alert_pemicu` FOREIGN KEY (`id_pengguna`) REFERENCES `tabel_pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_alert_penyakit` FOREIGN KEY (`id_penyakit`) REFERENCES `tabel_penyakit` (`id_penyakit`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_alert_verifikator` FOREIGN KEY (`id_verifikator`) REFERENCES `tabel_pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tabel_kasus`
--
ALTER TABLE `tabel_kasus`
  ADD CONSTRAINT `fk_kasus_kelurahan` FOREIGN KEY (`id_kelurahan`) REFERENCES `tabel_kelurahan` (`id_kelurahan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kasus_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tabel_pengguna` (`id_pengguna`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kasus_penyakit` FOREIGN KEY (`id_penyakit`) REFERENCES `tabel_penyakit` (`id_penyakit`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `tabel_kelurahan`
--
ALTER TABLE `tabel_kelurahan`
  ADD CONSTRAINT `fk_kelurahan_admin` FOREIGN KEY (`id_pengguna`) REFERENCES `tabel_pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tabel_pengaturan_threshold`
--
ALTER TABLE `tabel_pengaturan_threshold`
  ADD CONSTRAINT `fk_threshold_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tabel_pengguna` (`id_pengguna`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_threshold_penyakit` FOREIGN KEY (`id_penyakit`) REFERENCES `tabel_penyakit` (`id_penyakit`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `tabel_pengguna`
--
ALTER TABLE `tabel_pengguna`
  ADD CONSTRAINT `fk_pengguna_role` FOREIGN KEY (`id_role`) REFERENCES `tabel_role` (`id_role`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `tabel_penyakit`
--
ALTER TABLE `tabel_penyakit`
  ADD CONSTRAINT `fk_penyakit_icd` FOREIGN KEY (`kode_icd`) REFERENCES `tabel_master_icd10` (`kode_icd`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_penyakit_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tabel_pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tabel_riwayat_aktivitas`
--
ALTER TABLE `tabel_riwayat_aktivitas`
  ADD CONSTRAINT `fk_log_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tabel_pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
