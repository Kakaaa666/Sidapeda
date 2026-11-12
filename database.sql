-- ==============================================================
-- DATABASE SI-DAPEDA (PENAMAAN TABEL PERSIS SESUAI NAMA MENU)
-- ==============================================================
-- 1.  Menu Beranda              -> Tabel `beranda`
-- 2.  Menu Perbandingan Daerah  -> Tabel `perbandingan_daerah`
-- 3.  Menu Analisis Ekonomi     -> Tabel `analisis_ekonomi`
-- 4.  Menu Peta Ekonomi         -> Tabel `peta_ekonomi`
-- 5.  Menu Prospek Ekonomi      -> Tabel `prospek_ekonomi`
-- 6.  Menu Early Warning        -> Tabel `early_warning`
-- 7.  Menu Economic Insight     -> Tabel `economic_insight`
-- 8.  Menu Laporan Ekonomi      -> Tabel `laporan_ekonomi`
-- 9.  Menu Database Ekonomi     -> Tabel `database_ekonomi`
-- 10. Menu Katalog Indikator    -> Tabel `katalog_indikator`
-- 11. Menu Akun / Pengguna & Audit -> Tabel `akun`
-- 12. Log Autentikasi / Masuk   -> Tabel `riwayat_masuk`
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `sidapeda` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sidapeda`;

SET FOREIGN_KEY_CHECKS = 0;
DROP VIEW IF EXISTS `pengguna`;
DROP TABLE IF EXISTS `beranda`;
DROP TABLE IF EXISTS `perbandingan_daerah`;
DROP TABLE IF EXISTS `analisis_ekonomi`;
DROP TABLE IF EXISTS `peta_ekonomi`;
DROP TABLE IF EXISTS `prospek_ekonomi`;
DROP TABLE IF EXISTS `early_warning`;
DROP TABLE IF EXISTS `economic_insight`;
DROP TABLE IF EXISTS `laporan_ekonomi`;
DROP TABLE IF EXISTS `database_ekonomi`;
DROP TABLE IF EXISTS `katalog_indikator`;
DROP TABLE IF EXISTS `akun`;
DROP TABLE IF EXISTS `pengguna`;
DROP TABLE IF EXISTS `riwayat_masuk`;

-- Drop legacy table names
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `login_logs`;
DROP TABLE IF EXISTS `economic_series`;
DROP TABLE IF EXISTS `sectors`;
DROP TABLE IF EXISTS `regional_growth`;
DROP TABLE IF EXISTS `districts`;
DROP TABLE IF EXISTS `indicator_records`;
DROP TABLE IF EXISTS `indicator_catalog`;
DROP TABLE IF EXISTS `ews_indicators`;
DROP TABLE IF EXISTS `deret_ekonomi`;
DROP TABLE IF EXISTS `sektor_ekonomi`;
DROP TABLE IF EXISTS `perbandingan_wilayah`;
DROP TABLE IF EXISTS `kecamatan`;
DROP TABLE IF EXISTS `rekaman_indikator`;
DROP TABLE IF EXISTS `indikator_peringatan_dini`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------
-- 1. TABEL: beranda (Sesuai Menu "Beranda")
-- --------------------------------------------------------------
CREATE TABLE `beranda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_indikator` varchar(100) NOT NULL,
  `nilai` varchar(50) NOT NULL,
  `satuan` varchar(50) NOT NULL,
  `perubahan` varchar(50) NOT NULL,
  `arah_tren` enum('up','down') NOT NULL DEFAULT 'up',
  `status` varchar(50) NOT NULL,
  `deret_mini` text NOT NULL,
  `tahun` int(11) NOT NULL DEFAULT 2024,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `beranda` (`nama_indikator`, `nilai`, `satuan`, `perubahan`, `arah_tren`, `status`, `deret_mini`, `tahun`) VALUES
('Pertumbuhan Ekonomi', '5,18', '%', '+0,26%', 'up', 'Meningkat', '[18, 25, 20, 31, 28, 39, 44]', 2024),
('PDRB per Kapita', 'Rp 62,4', 'Juta', '+6,8%', 'up', 'Meningkat', '[16, 20, 24, 28, 31, 37, 42]', 2024),
('Tingkat Inflasi', '2,41', '% yoy', '-0,17%', 'down', 'Stabil', '[28, 35, 30, 38, 26, 24, 22]', 2024),
('Pengangguran Terbuka', '4,03', '%', '-0,28%', 'down', 'Membaik', '[40, 38, 35, 37, 30, 27, 25]', 2024),
('Tingkat Kemiskinan', '7,34', '%', '-0,21%', 'down', 'Membaik', '[43, 39, 37, 35, 32, 28, 26]', 2024),
('IPM', '73,79', 'Poin', '+0,71', 'up', 'Meningkat', '[20, 23, 25, 28, 32, 36, 40]', 2024),
('Realisasi Investasi', 'Rp 3,82', 'Triliun', '+12,4%', 'up', 'Meningkat', '[15, 19, 28, 24, 31, 37, 43]', 2024),
('Pertumbuhan Investasi', '12,42', '%', '-1,8%', 'down', 'Perlu perhatian', '[39, 36, 42, 35, 31, 28, 25]', 2024);

-- --------------------------------------------------------------
-- 2. TABEL: perbandingan_daerah (Sesuai Menu "Perbandingan Daerah")
-- --------------------------------------------------------------
CREATE TABLE `perbandingan_daerah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_wilayah` varchar(100) NOT NULL UNIQUE,
  `nilai_pertumbuhan` decimal(6,2) NOT NULL,
  `perubahan` decimal(6,2) NOT NULL,
  `deret_historis` text NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `perbandingan_daerah` (`nama_wilayah`, `nilai_pertumbuhan`, `perubahan`, `deret_historis`, `is_deleted`) VALUES
('Kota Surabaya', 5.76, 0.34, '[5.21, 5.42, 5.60, 6.00, 6.10, -4.85, 4.29, 6.51, 5.42, 5.76]', 0),
('Kab. Gresik', 5.42, 0.31, '[5.10, 5.20, 5.30, 5.70, 5.60, -3.68, 3.80, 5.16, 5.11, 5.42]', 0),
('Kab. Banyuwangi', 5.18, 0.26, '[5.02, 5.15, 5.28, 5.61, 5.55, -3.58, 4.08, 4.43, 4.92, 5.18]', 0),
('Kota Malang', 5.06, 0.18, '[5.20, 5.30, 5.40, 5.60, 5.70, -2.26, 4.00, 5.41, 4.88, 5.06]', 0),
('Kab. Sidoarjo', 4.98, 0.12, '[5.30, 5.40, 5.50, 5.70, 5.60, -3.69, 4.21, 5.20, 4.86, 4.98]', 0),
('Kota Kediri', 4.88, 0.15, '[5.00, 5.10, 5.20, 5.50, 5.40, -4.10, 3.90, 4.85, 4.73, 4.88]', 0),
('Kab. Jember', 4.62, 0.08, '[4.80, 4.90, 5.00, 5.20, 5.10, -3.20, 3.70, 4.53, 4.54, 4.62]', 0),
('Kab. Pasuruan', 4.55, 0.05, '[4.90, 5.00, 5.10, 5.40, 5.30, -3.80, 3.60, 4.48, 4.50, 4.55]', 0);

-- --------------------------------------------------------------
-- 3. TABEL: analisis_ekonomi (Sesuai Menu "Analisis Ekonomi")
-- --------------------------------------------------------------
CREATE TABLE `analisis_ekonomi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_sektor` varchar(50) NOT NULL UNIQUE,
  `nama_sektor` varchar(150) NOT NULL,
  `nama_pendek` varchar(50) NOT NULL,
  `porsi_daerah` decimal(6,2) NOT NULL,
  `porsi_provinsi` decimal(6,2) NOT NULL,
  `pertumbuhan_daerah` decimal(6,2) NOT NULL,
  `pertumbuhan_provinsi` decimal(6,2) NOT NULL,
  `tenaga_kerja_awal` int(11) NOT NULL,
  `tenaga_kerja_kini` int(11) NOT NULL,
  `tenaga_kerja_provinsi_awal` int(11) NOT NULL,
  `tenaga_kerja_provinsi_kini` int(11) NOT NULL,
  `deret_pertumbuhan` text NOT NULL,
  `deret_pertumbuhan_provinsi` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `analisis_ekonomi` (`kode_sektor`, `nama_sektor`, `nama_pendek`, `porsi_daerah`, `porsi_provinsi`, `pertumbuhan_daerah`, `pertumbuhan_provinsi`, `tenaga_kerja_awal`, `tenaga_kerja_kini`, `tenaga_kerja_provinsi_awal`, `tenaga_kerja_provinsi_kini`, `deret_pertumbuhan`, `deret_pertumbuhan_provinsi`) VALUES
('agri', 'Pertanian, Kehutanan & Perikanan', 'Pertanian', 28.50, 11.70, 4.30, 3.10, 18000, 20500, 1050000, 1120000, '[3.9, 4.1, 4.4, 4.2, 3.8, 2.1, 3.0, 3.6, 4.0, 4.3]', '[3.7, 3.3, 3.4, 3.5, 3.4, -1.2, 2.0, 3.1, 3.0, 3.1]'),
('trade', 'Perdagangan Besar & Eceran', 'Perdagangan', 18.50, 18.20, 5.40, 5.20, 7000, 9800, 680000, 780000, '[5.0, 5.2, 5.4, 5.8, 5.7, -4.2, 4.3, 5.0, 5.2, 5.4]', '[4.4, 5.0, 5.2, 5.4, 5.3, -4.1, 4.0, 5.1, 5.0, 5.2]'),
('manufacture', 'Industri Pengolahan', 'Industri', 12.20, 30.50, 4.10, 4.80, 5200, 7200, 520000, 600000, '[4.0, 4.2, 4.4, 4.8, 4.7, -2.8, 3.1, 4.0, 4.0, 4.1]', '[5.0, 4.8, 4.6, 4.8, 4.7, -2.7, 3.2, 4.2, 4.6, 4.8]'),
('accommodation', 'Akomodasi & Makan Minum', 'Akomodasi', 9.10, 4.20, 7.30, 6.40, 2100, 5600, 110000, 190000, '[6.0, 6.4, 6.9, 7.1, 6.8, -8.0, 5.9, 9.2, 7.0, 7.3]', '[6.5, 6.7, 6.9, 7.2, 6.8, -8.4, 5.4, 8.0, 6.5, 6.4]'),
('construction', 'Konstruksi', 'Konstruksi', 8.00, 9.20, 4.80, 5.10, 4300, 6500, 450000, 530000, '[5.1, 5.3, 5.2, 5.4, 5.3, -3.9, 3.3, 4.5, 4.6, 4.8]', '[5.3, 5.2, 5.1, 5.4, 5.1, -4.2, 2.8, 5.5, 5.0, 5.1]'),
('transport', 'Transportasi & Pergudangan', 'Transportasi', 5.00, 5.20, 5.10, 5.00, 2600, 3600, 230000, 280000, '[4.7, 4.9, 5.2, 5.4, 5.2, -7.2, 4.2, 7.8, 5.3, 5.1]', '[5.8, 5.5, 5.2, 5.5, 5.4, -8.0, 3.3, 8.0, 5.1, 5.0]'),
('services', 'Jasa Lainnya', 'Jasa lainnya', 18.70, 21.00, 5.00, 5.10, 9000, 13000, 850000, 1050000, '[4.8, 4.9, 5.0, 5.1, 5.0, -1.8, 3.7, 4.8, 4.9, 5.0]', '[5.1, 5.0, 4.9, 5.0, 5.0, -2.0, 3.8, 4.8, 5.0, 5.1]');

-- --------------------------------------------------------------
-- 4. TABEL: peta_ekonomi (Sesuai Menu "Peta Ekonomi")
-- --------------------------------------------------------------
CREATE TABLE `peta_ekonomi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kecamatan` varchar(100) NOT NULL UNIQUE,
  `porsi_pdrb` decimal(6,2) NOT NULL,
  `pertumbuhan` decimal(6,2) NOT NULL,
  `kemiskinan` decimal(6,2) NOT NULL,
  `kepadatan` int(11) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `garis_lintang` decimal(9,6) NOT NULL,
  `garis_bujur` decimal(9,6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `peta_ekonomi` (`nama_kecamatan`, `porsi_pdrb`, `pertumbuhan`, `kemiskinan`, `kepadatan`, `kategori`, `garis_lintang`, `garis_bujur`) VALUES
('Banyuwangi', 14.80, 5.82, 5.20, 2450, 'Perkotaan & Jasa', -8.219200, 114.369200),
('Rogojampi', 8.90, 5.41, 6.80, 1320, 'Perdagangan & Agro', -8.304500, 114.292500),
('Genteng', 9.20, 5.35, 6.10, 1480, 'Pusat Niaga Selatan', -8.361900, 114.152800),
('Muncar', 11.40, 5.12, 8.40, 1150, 'Perikanan & Industri Bahari', -8.433300, 114.333300),
('Glenmore', 6.50, 4.90, 7.90, 680, 'Perkebunan & Industri Pangan', -8.310000, 114.050000),
('Singojuruh', 4.80, 4.85, 7.50, 750, 'Pertanian Tanaman Pangan', -8.316700, 114.233300),
('Wongsorejo', 5.60, 4.70, 9.80, 420, 'Kawasan Industri Utara', -7.950000, 114.400000),
('Kalipuro', 8.20, 5.25, 7.10, 920, 'Pelabuhan & Logistik Selat', -8.150000, 114.383300),
('Kabat', 5.90, 5.15, 6.90, 890, 'Penyangga Perkotaan & Pariwisata', -8.283300, 114.316700),
('Cluring', 5.10, 4.95, 7.40, 810, 'Sentra Pangan & Holtikultura', -8.416700, 114.233300),
('Gambiran', 5.30, 5.08, 6.70, 980, 'Agribisnis & UMKM', -8.383300, 114.150000),
('Purwoharjo', 4.30, 4.78, 8.20, 640, 'Pertanian & Konservasi Alas Purwo', -8.500000, 114.250000);

-- --------------------------------------------------------------
-- 5. TABEL: prospek_ekonomi (Sesuai Menu "Prospek Ekonomi")
-- --------------------------------------------------------------
CREATE TABLE `prospek_ekonomi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tahun` int(11) NOT NULL UNIQUE,
  `pertumbuhan_ekonomi` decimal(6,2) NOT NULL,
  `pertumbuhan_jatim` decimal(6,2) NOT NULL,
  `pertumbuhan_nasional` decimal(6,2) NOT NULL,
  `tingkat_pengangguran` decimal(6,2) NOT NULL,
  `pertumbuhan_investasi` decimal(6,2) NOT NULL,
  `tingkat_kemiskinan` decimal(6,2) NOT NULL,
  `inflasi` decimal(6,2) NOT NULL,
  `ipm` decimal(6,2) NOT NULL,
  `pdrb_per_kapita` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `prospek_ekonomi` (`tahun`, `pertumbuhan_ekonomi`, `pertumbuhan_jatim`, `pertumbuhan_nasional`, `tingkat_pengangguran`, `pertumbuhan_investasi`, `tingkat_kemiskinan`, `inflasi`, `ipm`, `pdrb_per_kapita`) VALUES
(2015, 5.02, 5.44, 4.88, 3.20, 6.10, 9.17, 3.12, 68.11, 35.20),
(2016, 5.15, 5.46, 5.03, 3.12, 7.20, 8.75, 2.71, 68.63, 37.10),
(2017, 5.28, 5.45, 5.07, 3.05, 8.00, 8.64, 3.61, 69.13, 39.40),
(2018, 5.61, 5.50, 5.17, 2.94, 8.80, 8.32, 2.88, 69.64, 42.80),
(2019, 5.55, 5.52, 5.02, 3.84, 9.40, 7.52, 2.94, 70.06, 45.90),
(2020, -3.58, -2.33, -2.07, 5.50, -2.00, 8.06, 1.68, 70.62, 43.70),
(2021, 4.08, 3.56, 3.70, 5.10, 4.20, 7.45, 1.87, 71.26, 47.60),
(2022, 4.43, 5.34, 5.31, 4.30, 8.80, 7.51, 6.12, 72.45, 53.20),
(2023, 5.03, 4.95, 5.05, 4.10, 14.20, 7.34, 2.81, 73.08, 58.40),
(2024, 5.18, 4.93, 5.03, 4.03, 12.42, 7.34, 2.41, 73.79, 62.40);

-- --------------------------------------------------------------
-- 6. TABEL: early_warning (Sesuai Menu "Early Warning")
-- --------------------------------------------------------------
CREATE TABLE `early_warning` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_indikator` varchar(50) NOT NULL UNIQUE,
  `nama_indikator` varchar(150) NOT NULL,
  `nilai_terkini` decimal(8,2) NOT NULL,
  `satuan` varchar(50) NOT NULL,
  `ambang_batas` decimal(8,2) NOT NULL,
  `arah_risiko` enum('above','below') NOT NULL DEFAULT 'above',
  `status_risiko` enum('Normal','Waspada','Kritis') NOT NULL DEFAULT 'Normal',
  `sudah_ditindaklanjuti` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `early_warning` (`kode_indikator`, `nama_indikator`, `nilai_terkini`, `satuan`, `ambang_batas`, `arah_risiko`, `status_risiko`, `sudah_ditindaklanjuti`) VALUES
('INF_FOOD', 'Inflasi Komoditas Pangan Pokok', 6.00, '%', 5.00, 'above', 'Kritis', 0),
('BELANJA_MODAL', 'Realisasi Belanja Modal APBD', 48.00, '%', 60.00, 'below', 'Waspada', 1),
('KEMISKINAN_EKSTREM', 'Tingkat Kemiskinan Ekstrem', 0.85, '%', 1.00, 'above', 'Normal', 0),
('PENGANGGURAN_MUDA', 'Tingkat Pengangguran Terbuka Usia Muda', 11.20, '%', 14.00, 'above', 'Normal', 0),
('PERTUMBUHAN_KREDIT', 'Pertumbuhan Kredit UMKM Daerah', 9.40, '%', 7.00, 'below', 'Normal', 0);

-- --------------------------------------------------------------
-- 7. TABEL: economic_insight (Sesuai Menu "Economic Insight")
-- --------------------------------------------------------------
CREATE TABLE `economic_insight` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(200) NOT NULL,
  `ringkasan` text NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `tingkat_urgensi` enum('Tinggi','Sedang','Stabil') NOT NULL DEFAULT 'Sedang',
  `rekomendasi_kebijakan` text NOT NULL,
  `dampak_anggaran` varchar(100) DEFAULT NULL,
  `opd_penanggung_jawab` varchar(100) NOT NULL,
  `tahun` int(11) NOT NULL DEFAULT 2024,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `economic_insight` (`judul`, `ringkasan`, `kategori`, `tingkat_urgensi`, `rekomendasi_kebijakan`, `dampak_anggaran`, `opd_penanggung_jawab`, `tahun`) VALUES
('Akselerasi Pertumbuhan Sektor Pertanian Melalui Hilirisasi Agroindustri', 'Sektor pertanian menyumbang 28,5% PDRB namun memiliki nilai tambah yang perlu ditingkatkan melalui pengolahan pasca panen.', 'Pertumbuhan & Nilai Tambah', 'Tinggi', 'Penguatan kemitraan petani dengan industri pengolahan lokal dan penyediaan fasilitas cold storage komoditas unggulan.', 'Efisiensi Belanja Modal', 'Dinas Pertanian dan Pangan', 2024),
('Stabilisasi Harga Pangan Melalui Penguatan Pasokan dan Distribusi TPID', 'Volatilitas inflasi pangan pokok menuntut pemantauan harian dan operasi pasar terjadwal.', 'Stabilitas Harga', 'Tinggi', 'Optimalisasi BUMD pangan sebagai off-taker dan integrasi data stok pasar berbasis digital.', 'BTT / Subsidi Distribusi', 'Bagian Perekonomian & BPS', 2024),
('Peningkatan Daya Saing Tenaga Kerja Terampil Mendukung Investasi Pariwisata', 'Realisasi investasi Rp 3,82 Triliun memerlukan kesiapan tenaga kerja lokal bersertifikasi kompetensi.', 'Ketenagakerjaan', 'Sedang', 'Revitalisasi BLK dan sinkronisasi kurikulum vokasi SMK dengan industri perhotelan dan pariwisata.', 'APBD & CSR', 'Dinas Tenaga Kerja', 2024);

-- --------------------------------------------------------------
-- 8. TABEL: laporan_ekonomi (Sesuai Menu "Laporan Ekonomi")
-- --------------------------------------------------------------
CREATE TABLE `laporan_ekonomi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_laporan` varchar(100) NOT NULL UNIQUE,
  `judul_laporan` varchar(200) NOT NULL,
  `jenis_laporan` varchar(100) NOT NULL,
  `periode_tahun` int(11) NOT NULL,
  `penyusun` varchar(100) NOT NULL,
  `status` enum('Terpublikasi','Draf') NOT NULL DEFAULT 'Terpublikasi',
  `tanggal_terbit` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `laporan_ekonomi` (`nomor_laporan`, `judul_laporan`, `jenis_laporan`, `periode_tahun`, `penyusun`, `status`, `tanggal_terbit`) VALUES
('LAP-EKO-2024-01', 'Laporan Analisis Kinerja Perekonomian Tahunan 2024', 'Laporan Eksekutif', 2024, 'Tim Analis BAPPEDA', 'Terpublikasi', '2024-12-31'),
('LAP-EKO-2024-02', 'Laporan Pemantauan Dini Risiko Inflasi Daerah', 'Early Warning Brief', 2024, 'Sekretariat TPID Banyuwangi', 'Terpublikasi', '2024-11-15'),
('LAP-EKO-2024-03', 'Kajian Daya Saing Sektoral dan Skenario Prospek 2025', 'Kajian Tematik', 2024, 'Bidang Litbang & Bappeda', 'Terpublikasi', '2024-10-01');

-- --------------------------------------------------------------
-- 9. TABEL: database_ekonomi (Sesuai Menu "Database Ekonomi" & "Validasi Data")
-- --------------------------------------------------------------
CREATE TABLE `database_ekonomi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_indikator` varchar(50) NOT NULL,
  `nama_indikator` varchar(150) NOT NULL,
  `klaster` varchar(100) NOT NULL,
  `wilayah` varchar(100) NOT NULL DEFAULT 'Kabupaten Banyuwangi',
  `periode` varchar(50) NOT NULL DEFAULT 'Tahunan',
  `tahun` int(11) NOT NULL,
  `nilai` decimal(12,2) NOT NULL,
  `satuan` varchar(50) NOT NULL,
  `sumber_data` varchar(100) NOT NULL,
  `status_validasi` enum('Tervalidasi','Perlu review','Ditolak') NOT NULL DEFAULT 'Perlu review',
  `diverifikasi_oleh` varchar(100) DEFAULT NULL,
  `catatan` text,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `database_ekonomi` (`kode_indikator`, `nama_indikator`, `klaster`, `wilayah`, `periode`, `tahun`, `nilai`, `satuan`, `sumber_data`, `status_validasi`, `diverifikasi_oleh`, `catatan`) VALUES
('PERTUMB_EKO', 'Pertumbuhan Ekonomi Banyuwangi', 'Output & Pertumbuhan', 'Kabupaten Banyuwangi', 'Tahunan', 2024, 5.18, '%', 'BPS Banyuwangi', 'Tervalidasi', 'Arif Setiawan', 'Final rilis berita resmi statistik BPS.'),
('INFLASI_YOY', 'Inflasi IHK Banyuwangi', 'Stabilitas Harga', 'Kabupaten Banyuwangi', 'Tahunan', 2024, 2.41, '% yoy', 'BPS Banyuwangi', 'Tervalidasi', 'Arif Setiawan', 'Target inflasi TPID tercapai di rentang 2.5 ± 1%.'),
('TPT', 'Tingkat Pengangguran Terbuka', 'Ketenagakerjaan', 'Kabupaten Banyuwangi', 'Tahunan', 2024, 4.03, '%', 'Sakernas BPS', 'Tervalidasi', 'Arif Setiawan', 'Tren menurun pasca ekspansi sektor pariwisata.'),
('KEMISKINAN_P0', 'Persentase Penduduk Miskin', 'Kesejahteraan Sosial', 'Kabupaten Banyuwangi', 'Tahunan', 2024, 7.34, '%', 'Susenas BPS', 'Tervalidasi', 'Arif Setiawan', 'Capaian penanggulangan kemiskinan terintegrasi UGD Kemiskinan.'),
('IPM', 'Indeks Pembangunan Manusia', 'Kualitas SDM', 'Kabupaten Banyuwangi', 'Tahunan', 2024, 73.79, 'Poin', 'BPS Jawa Timur', 'Tervalidasi', 'Arif Setiawan', 'Status pembangunan manusia kategori tinggi.'),
('INVESTASI_REAL', 'Realisasi Investasi Daerah', 'Investasi & Modal', 'Kabupaten Banyuwangi', 'Tahunan', 2024, 3.82, 'Triliun Rp', 'DPMPTSP Banyuwangi', 'Perlu review', NULL, 'Menunggu audit verifikasi triwulan IV.'),
('PAD', 'Pendapatan Asli Daerah', 'Fiskal Daerah', 'Kabupaten Banyuwangi', 'Tahunan', 2024, 612.40, 'Miliar Rp', 'BPKAD Banyuwangi', 'Perlu review', NULL, 'Rekonsiliasi penutupan buku tahun anggaran 2024.'),
('PDRB_ADHK', 'PDRB ADHK Banyuwangi', 'Output & Pertumbuhan', 'Kabupaten Banyuwangi', 'Tahunan', 2024, 62.40, 'Triliun Rp', 'BPS Banyuwangi', 'Tervalidasi', 'Arif Setiawan', 'Angka tetap PDRB rilis tahunan.');

-- --------------------------------------------------------------
-- 10. TABEL: katalog_indikator (Sesuai Menu "Katalog Indikator")
-- --------------------------------------------------------------
CREATE TABLE `katalog_indikator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_indikator` varchar(50) NOT NULL UNIQUE,
  `nama_indikator` varchar(150) NOT NULL,
  `klaster` varchar(100) NOT NULL,
  `satuan` varchar(50) NOT NULL,
  `frekuensi` varchar(50) NOT NULL,
  `sumber_data` varchar(100) NOT NULL,
  `definisi_operasional` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `katalog_indikator` (`kode_indikator`, `nama_indikator`, `klaster`, `satuan`, `frekuensi`, `sumber_data`, `definisi_operasional`) VALUES
('PDRB_ADHK', 'PDRB Atas Dasar Harga Konstan', 'Output & Pertumbuhan', 'Miliar Rp', 'Tahunan', 'BPS Kabupaten Banyuwangi', 'Nilai tambah bruto seluruh barang dan jasa akhir yang dihasilkan wilayah domestik atas dasar harga tahun dasar 2010.'),
('PERTUMB_EKO', 'Laju Pertumbuhan Ekonomi', 'Output & Pertumbuhan', '%', 'Tahunan', 'BPS Kabupaten Banyuwangi', 'Perubahan persentase nilai PDRB ADHK tahun berjalan dibandingkan dengan tahun sebelumnya.'),
('INFLASI_YOY', 'Tingkat Inflasi Tahunan', 'Stabilitas Harga', '% yoy', 'Bulanan', 'BPS Kabupaten Banyuwangi / TPID', 'Persentase kenaikan Indeks Harga Konsumen (IHK) periode tahun berjalan terhadap periode yang sama tahun lalu.'),
('TPT', 'Tingkat Pengangguran Terbuka', 'Ketenagakerjaan', '%', 'Tahunan', 'Sakernas BPS', 'Persentase angkatan kerja yang sedang mencari pekerjaan terhadap total angkatan kerja daerah.'),
('KEMISKINAN_P0', 'Persentase Penduduk Miskin', 'Kesejahteraan Sosial', '%', 'Tahunan', 'Susenas BPS', 'Persentase penduduk yang pengeluaran per kapitanya di bawah Garis Kemiskinan.'),
('IPM', 'Indeks Pembangunan Manusia', 'Kualitas SDM', 'Poin', 'Tahunan', 'BPS Provinsi Jawa Timur', 'Ukuran capaian pembangunan manusia berbasis tiga dimensi dasar: umur panjang, pengetahuan, dan standar hidup layak.'),
('INVESTASI_REAL', 'Realisasi Investasi Daerah (PMDN & PMA)', 'Investasi & Modal', 'Triliun Rp', 'Tahunan', 'DPMPTSP Kab. Banyuwangi', 'Akumulasi realisasi modal usaha berizin yang ditanamkan oleh badan usaha swasta dan perorangan.'),
('PAD', 'Pendapatan Asli Daerah', 'Fiskal Daerah', 'Miliar Rp', 'Tahunan', 'BPKAD Kabupaten Banyuwangi', 'Penerimaan daerah dari pajak daerah, retribusi, hasil pengelolaan kekayaan daerah yang dipisahkan, dan lain-lain.');

-- --------------------------------------------------------------
-- 11. TABEL: akun (Sesuai Menu "Akun" / "Pengguna & Audit")
-- --------------------------------------------------------------
CREATE TABLE `akun` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pengguna` varchar(50) NOT NULL UNIQUE,
  `kata_sandi` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `peran` enum('Administrator','Analis','Operator','Reviewer') NOT NULL DEFAULT 'Operator',
  `level` int(11) NOT NULL DEFAULT 3,
  `status` enum('Aktif','Nonaktif') NOT NULL DEFAULT 'Aktif',
  `foto_profil` varchar(255) DEFAULT NULL,
  `terakhir_masuk` datetime DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT CURRENT_TIMESTAMP,
  `diperbarui_pada` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `akun` (`id`, `nama_pengguna`, `kata_sandi`, `nama_lengkap`, `email`, `peran`, `level`, `status`) VALUES
(1, 'admin', '$2y$10$zhG3VIpyjPEhIXbfLPSdOet0hNy88aOg5FVXguIjJZp9XU077x9gi', 'Arif Setiawan, S.E., M.Ec.', 'admin.bappeda@banyuwangikab.go.id', 'Administrator', 1, 'Aktif'),
(2, 'analis', '$2y$10$A9/JxxrVFxmg/1yRaQ8L6OkVATWXAv9HDQOBc14/6UGgWUGOH/i4G', 'Dra. Maya Puspitasari', 'analis.ekonomi@banyuwangikab.go.id', 'Analis', 2, 'Aktif'),
(3, 'operator', '$2y$10$9zBx.s7jX4tKo9pIDgm1gO//G9eSZH8BVmNdjwVl7O3l4inGi.aHm', 'Budi Prasetyo, S.Kom.', 'operator.sidapeda@banyuwangikab.go.id', 'Operator', 3, 'Aktif'),
(4, 'bappeda', '$2y$10$xR3n.NmUNkL3s9bKH2ZfoeseiCPWUlPBuNcI9/tXB/qUVmaZ0iexi', 'Kepala Bappeda Banyuwangi', 'kepala.bappeda@banyuwangikab.go.id', 'Administrator', 1, 'Aktif'),
(5, 'reviewer', '$2y$10$n32Y5f.Us0TsrEtj0f5A3O61oBnjzNbmI2Ip.UnKfLydY4JNCE5XS', 'Ir. Hendra Kusuma', 'reviewer.opd@banyuwangikab.go.id', 'Reviewer', 4, 'Nonaktif');

-- View kompatibilitas untuk kueri tabel `pengguna`
CREATE OR REPLACE VIEW `pengguna` AS SELECT * FROM `akun`;

-- --------------------------------------------------------------
-- 12. TABEL: riwayat_masuk (Sesuai Log Masuk / Login Trail)
-- --------------------------------------------------------------
CREATE TABLE `riwayat_masuk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pengguna` varchar(50) NOT NULL,
  `alamat_ip` varchar(45) NOT NULL,
  `perangkat` text,
  `status` enum('Success','Failed') NOT NULL,
  `pesan` varchar(255) DEFAULT NULL,
  `waktu_masuk` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
