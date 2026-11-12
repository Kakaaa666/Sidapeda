-- ==============================================================
-- SI-DAPEDA: Skema Database Autentikasi Akun (Bahasa Indonesia)
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `sidapeda` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sidapeda`;

-- 1. Tabel akun
DROP VIEW IF EXISTS `pengguna`;
DROP TABLE IF EXISTS `akun`;
DROP TABLE IF EXISTS `pengguna`;
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

-- View kompatibilitas pengguna
CREATE OR REPLACE VIEW `pengguna` AS SELECT * FROM `akun`;

-- 2. Tabel riwayat_masuk
DROP TABLE IF EXISTS `riwayat_masuk`;
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
