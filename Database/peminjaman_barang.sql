-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table peminjaman_barang.barang
CREATE TABLE IF NOT EXISTS `barang` (
  `id_barang` int NOT NULL AUTO_INCREMENT,
  `nama_barang` varchar(100) NOT NULL,
  `kode_barang` varchar(50) NOT NULL,
  `id_kategori` int NOT NULL,
  `tahun` int NOT NULL,
  `stok` int NOT NULL,
  `gambar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id_barang`),
  UNIQUE KEY `kode_barang` (`kode_barang`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table peminjaman_barang.barang: ~6 rows (approximately)
INSERT INTO `barang` (`id_barang`, `nama_barang`, `kode_barang`, `id_kategori`, `tahun`, `stok`, `gambar`) VALUES
	(11, 'Squier Mini', 'BRG-20260518-001', 3, 2010, 2, 'images (9).jpeg'),
	(12, 'Yamaha U38383', 'BRG-20260518-012', 5, 2011, 4, 'yamaha_yas_280_800x800-584cf-3037_586-twebp80.webp'),
	(13, 'Ukulele', 'BRG-20260518-013', 2, 2019, 10, 'images (8).jpeg'),
	(14, 'Fender Telecaster', 'BRG-20260519-014', 2, 2025, 15, 'fender telecaster.jpg'),
	(15, 'Action V Plus', 'BRG-20260526-015', 3, 2015, 1, 'cort-action-bass-v-plus-bk-basses-guitar-gal-1.jpg'),
	(16, 'Yamaha', 'BRG-20260526-016', 4, 2018, 1, 'c7x_preorder.webp');

-- Dumping structure for table peminjaman_barang.denda
CREATE TABLE IF NOT EXISTS `denda` (
  `id_denda` int NOT NULL AUTO_INCREMENT,
  `id_pinjam` int DEFAULT NULL,
  `jumlah_denda` int NOT NULL DEFAULT '0',
  `status_denda` enum('Belum Lunas','Lunas') DEFAULT 'Belum Lunas',
  PRIMARY KEY (`id_denda`),
  KEY `id_pinjam` (`id_pinjam`),
  CONSTRAINT `denda_ibfk_1` FOREIGN KEY (`id_pinjam`) REFERENCES `peminjaman` (`id_pinjam`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table peminjaman_barang.denda: ~2 rows (approximately)
INSERT INTO `denda` (`id_denda`, `id_pinjam`, `jumlah_denda`, `status_denda`) VALUES
	(1, 18, 40000, 'Belum Lunas'),
	(2, 19, 30000, 'Lunas');

-- Dumping structure for table peminjaman_barang.kategori
CREATE TABLE IF NOT EXISTS `kategori` (
  `id_kategori` int NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table peminjaman_barang.kategori: ~4 rows (approximately)
INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
	(2, 'Gitar'),
	(3, 'Bass'),
	(4, 'Piano'),
	(5, 'Saxophone');

-- Dumping structure for table peminjaman_barang.peminjaman
CREATE TABLE IF NOT EXISTS `peminjaman` (
  `id_pinjam` int NOT NULL AUTO_INCREMENT,
  `kode_peminjaman` varchar(50) DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `id_barang` int DEFAULT NULL,
  `jumlah_pinjam` int NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `catatan` varchar(100) DEFAULT NULL,
  `tgl_kembali` date DEFAULT NULL,
  `status` enum('Pending','disetujui','ditolak','dipinjam','dikembalikan') NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`id_pinjam`),
  UNIQUE KEY `kode_peminjaman` (`kode_peminjaman`),
  KEY `id_barang` (`id_barang`),
  KEY `fk_peminjaman_users` (`id_user`),
  CONSTRAINT `fk_peminjaman_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table peminjaman_barang.peminjaman: ~12 rows (approximately)
INSERT INTO `peminjaman` (`id_pinjam`, `kode_peminjaman`, `id_user`, `id_barang`, `jumlah_pinjam`, `tgl_mulai`, `tgl_selesai`, `catatan`, `tgl_kembali`, `status`) VALUES
	(5, NULL, 3, 12, 1, '2026-05-21', '2026-05-28', '1', '2026-05-19', 'dikembalikan'),
	(6, NULL, 3, 13, 2, '2026-05-28', '2026-05-28', '1', '2026-05-19', 'dikembalikan'),
	(7, NULL, 3, 12, 1, '2026-05-22', '2026-05-24', '1', '2026-05-19', 'dikembalikan'),
	(8, NULL, 3, 12, 21, '2026-05-21', '2026-05-25', 'Digunakan untuk keperluan lomba musik di ITS', NULL, 'ditolak'),
	(9, 'UPNV-20260521-6CD8', 3, 13, 2, '2026-05-30', '2026-06-05', '323232', '2026-05-21', 'dikembalikan'),
	(10, 'UPNVJT-20260521-2BA2', 3, 12, 2, '2026-05-29', '2026-05-30', '2eqeq', '2026-05-21', 'dikembalikan'),
	(11, 'UPNVJT-20260521-9349', 3, 14, 5, '2026-05-22', '2026-05-24', 'kdokwodkwokwd', '2026-05-01', 'dikembalikan'),
	(15, 'PMJ-2026-002', 3, 14, 2, '2026-05-06', '2026-05-12', 'Tugas Akhir Elektro', '2026-05-08', 'dikembalikan'),
	(17, 'UPNVJT-20260526-2894', 3, 14, 2, '2026-05-26', '2026-05-27', 'OKKK', '2026-05-26', 'dikembalikan'),
	(18, 'UPNVJT-20260526-DBDF', 3, 14, 2, '2026-05-26', '2026-05-27', 'dkwkdwkod', '2026-05-31', 'dikembalikan'),
	(19, 'UPNVJT-20260526-5BAF', 3, 14, 2, '2026-05-26', '2026-05-27', 'dwodwk', '2026-05-30', 'dikembalikan'),
	(20, 'UPNVJT-20260526-A8FC', 3, 14, 2, '2026-05-28', '2026-05-29', '', '2026-05-31', 'dikembalikan'),
	(21, 'UPNVJT-20260601-31C0', 8, 15, 1, '2026-06-10', '2026-06-30', 'Latihan', NULL, 'ditolak');

-- Dumping structure for table peminjaman_barang.users
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `npm` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `telp` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `level` enum('Admin','Super_Admin','Mahasiswa') NOT NULL DEFAULT 'Mahasiswa',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `npm` (`npm`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table peminjaman_barang.users: ~7 rows (approximately)
INSERT INTO `users` (`id_user`, `nama`, `npm`, `email`, `telp`, `password`, `foto`, `level`) VALUES
	(2, 'aaa', 'a', 'admin@pibiti.himatifaupnvjt.org', 'a', '$2y$10$HmonR4Wi6MPG6ppDuiqe8eGjrpKSwHBrBAFCvhG5JKjhgxpviDR7K', NULL, 'Mahasiswa'),
	(3, 'user', '240202', 'user@gmail.com', '093939', '$2y$10$UBkTS0xvvmKoxRuJXH2gbOsg//DdrHJzYP/unucLzD2.gFx/L/pC2', '6a0e63b7eeb6d.jpg', 'Mahasiswa'),
	(4, 'admin', '-', 'admin@123.com', '093299329', '$2y$10$pL8bW2MugpP9v9.o2u2OVOg6T1pZqI7Qj8bXshH12fO7hD6IkaZ3y', NULL, 'Admin'),
	(7, 'Admin', '244', 'admin@1.com', '0292', '$2y$10$nyG3/feqZSgWxCr8QcTEYOSRcWty.welbXsCEW42G9ghZ3sFm2VRu', '', 'Admin'),
	(8, 'Muhammad Fawwaz Zildan', '24081010050', 'mfawwazzildan@gmail.com', '082203030', '$2y$10$g/j0bfdwIJuGakenJNcX3.DiO3sLYt1.gsWMjYyDFRReLzTCp9V3G', NULL, 'Mahasiswa'),
	(9, 'admin', '', 'admin@1.co', '03993939', '$2y$10$hf5Wc9a933H6My5goe0QDuv9/03r0Am8KQiDDpnqkrM4T/g537ZA6', '', 'Admin');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
