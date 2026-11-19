-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: travel_tour
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `DRIVER`
--

DROP TABLE IF EXISTS `DRIVER`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `DRIVER` (
  `id_driver` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_telepon` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_sim` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `FK_id_pengguna` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_driver`),
  UNIQUE KEY `driver_nomor_sim_unique` (`nomor_sim`),
  KEY `driver_fk_id_pengguna_foreign` (`FK_id_pengguna`),
  CONSTRAINT `driver_fk_id_pengguna_foreign` FOREIGN KEY (`FK_id_pengguna`) REFERENCES `UMUM` (`id_pengguna`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DRIVER`
--

LOCK TABLES `DRIVER` WRITE;
/*!40000 ALTER TABLE `DRIVER` DISABLE KEYS */;
INSERT INTO `DRIVER` VALUES ('DRV-001','Budi Santoso','081234560003','920312345601','Jl. Kenanga No. 12, Malang','Bertugas','USR-2026-003'),('DRV-002','Agus Pratama','081234560004','920312345602','Jl. Melati No. 7, Surabaya','Tersedia','USR-2026-004');
/*!40000 ALTER TABLE `DRIVER` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `JADWAL_PERJALANAN`
--

DROP TABLE IF EXISTS `JADWAL_PERJALANAN`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `JADWAL_PERJALANAN` (
  `id_jadwal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_pemesanan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kendaraan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_driver` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_berangkat` date NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_jadwal`),
  KEY `jadwal_perjalanan_id_pemesanan_foreign` (`id_pemesanan`),
  KEY `jadwal_perjalanan_id_kendaraan_foreign` (`id_kendaraan`),
  KEY `jadwal_perjalanan_id_driver_foreign` (`id_driver`),
  CONSTRAINT `jadwal_perjalanan_id_driver_foreign` FOREIGN KEY (`id_driver`) REFERENCES `DRIVER` (`id_driver`),
  CONSTRAINT `jadwal_perjalanan_id_kendaraan_foreign` FOREIGN KEY (`id_kendaraan`) REFERENCES `KENDARAAN` (`id_kendaraan`),
  CONSTRAINT `jadwal_perjalanan_id_pemesanan_foreign` FOREIGN KEY (`id_pemesanan`) REFERENCES `PEMESANAN` (`id_pemesanan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `JADWAL_PERJALANAN`
--

LOCK TABLES `JADWAL_PERJALANAN` WRITE;
/*!40000 ALTER TABLE `JADWAL_PERJALANAN` DISABLE KEYS */;
INSERT INTO `JADWAL_PERJALANAN` VALUES ('JDW-2026-001','PSN-202608-009','KND-001','DRV-001','2026-09-02','Selesai'),('JDW-2026-002','PSN-202608-011','KND-002','DRV-001','2026-09-20','Selesai'),('JDW-2026-003','PSN-202609-012','KND-003','DRV-001','2026-09-29','Selesai'),('JDW-2026-004','PSN-202609-013','KND-001','DRV-001','2026-10-08','Selesai'),('JDW-2026-005','PSN-202610-016','KND-002','DRV-001','2026-11-04','Selesai'),('JDW-2026-006','PSN-202610-017','KND-003','DRV-001','2026-11-13','Selesai'),('JDW-2026-007','PSN-202611-018','KND-001','DRV-001','2026-11-22','Terjadwal'),('JDW-2026-008','PSN-202611-019','KND-002','DRV-001','2026-12-01','Terjadwal');
/*!40000 ALTER TABLE `JADWAL_PERJALANAN` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `KENDARAAN`
--

DROP TABLE IF EXISTS `KENDARAAN`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `KENDARAAN` (
  `id_kendaraan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plat_nomor` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_kendaraan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `merek` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kapasitas` int NOT NULL,
  `tahun_produksi` int NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_kendaraan`),
  UNIQUE KEY `kendaraan_plat_nomor_unique` (`plat_nomor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `KENDARAAN`
--

LOCK TABLES `KENDARAAN` WRITE;
/*!40000 ALTER TABLE `KENDARAAN` DISABLE KEYS */;
INSERT INTO `KENDARAAN` VALUES ('KND-001','B 7123 TXA','Minibus','Toyota Hiace',14,2023,'Tersedia'),('KND-002','B 7456 TXB','Bus Medium','Isuzu Elf',19,2022,'Tersedia'),('KND-003','B 1890 TXC','MPV','Toyota Innova',7,2024,'Tersedia');
/*!40000 ALTER TABLE `KENDARAAN` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PAKET_TRAVEL`
--

DROP TABLE IF EXISTS `PAKET_TRAVEL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PAKET_TRAVEL` (
  `id_paket` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_paket` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tujuan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `durasi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `kuota` int NOT NULL,
  `jadwal_keberangkatan` date NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `foto_paket` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fasilitas` text COLLATE utf8mb4_unicode_ci,
  `biaya_admin` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_paket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PAKET_TRAVEL`
--

LOCK TABLES `PAKET_TRAVEL` WRITE;
/*!40000 ALTER TABLE `PAKET_TRAVEL` DISABLE KEYS */;
INSERT INTO `PAKET_TRAVEL` VALUES ('PKT-BALI-01','Bali Explore 4D3N','Bali','4 Hari 3 Malam',2500000.00,20,'2026-11-29','Pantai, Ubud, dan Tanah Lot','assets/img/paket-bali.jpg',NULL,0.00),('PKT-BRMO-01','Bromo Sunrise 2D1N','Malang','2 Hari 1 Malam',1250000.00,20,'2026-12-06','Sunrise Penanjakan dan kawah Bromo','assets/img/paket-bromo.jpg',NULL,0.00),('PKT-LBJ-01','Labuan Bajo Sailing 3D2N','Labuan Bajo','3 Hari 2 Malam',3500000.00,20,'2026-12-20','Pulau Komodo dan Padar','assets/img/paket-labuan.jpg',NULL,0.00),('PKT-LMBK-01','Lombok Gili Trip 3D2N','Lombok','3 Hari 2 Malam',2900000.00,20,'2026-12-13','Gili Trawangan dan Pantai Pink','assets/img/paket-lombok.jpg',NULL,0.00),('PKT-TOBA-01','Danau Toba Heritage 3D2N','Sumatera Utara','3 Hari 2 Malam',2100000.00,20,'2026-12-27','Samosir dan Desa Tomok','assets/img/paket-toba.jpg',NULL,0.00);
/*!40000 ALTER TABLE `PAKET_TRAVEL` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PEMBAYARAN`
--

DROP TABLE IF EXISTS `PEMBAYARAN`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PEMBAYARAN` (
  `id_pembayaran` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_invoice` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_pemesanan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nominal` decimal(10,2) DEFAULT NULL,
  `bukti_pembayaran` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_bayar` date DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alasan` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_pembayaran`),
  KEY `pembayaran_id_pemesanan_foreign` (`id_pemesanan`),
  CONSTRAINT `pembayaran_id_pemesanan_foreign` FOREIGN KEY (`id_pemesanan`) REFERENCES `PEMESANAN` (`id_pemesanan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PEMBAYARAN`
--

LOCK TABLES `PEMBAYARAN` WRITE;
/*!40000 ALTER TABLE `PEMBAYARAN` DISABLE KEYS */;
INSERT INTO `PEMBAYARAN` VALUES ('PYM-202606-001','INV/2026/06/001','PSN-202606-001',10000000.00,'bukti_transfer_001.jpg','2026-06-01','Lunas',NULL),('PYM-202606-002','INV/2026/06/002','PSN-202606-002',3750000.00,'bukti_transfer_002.jpg','2026-06-10','Lunas',NULL),('PYM-202606-003','INV/2026/06/003','PSN-202606-003',14500000.00,'bukti_transfer_003.jpg','2026-06-19','Lunas',NULL),('PYM-202606-004','INV/2026/06/004','PSN-202606-004',7000000.00,'bukti_transfer_004.jpg','2026-06-28','Lunas',NULL),('PYM-202607-005','INV/2026/07/005','PSN-202607-005',12600000.00,'bukti_transfer_005.jpg','2026-07-07','Proses Verifikasi',NULL),('PYM-202607-006','INV/2026/07/006','PSN-202607-006',2500000.00,'bukti_transfer_006.jpg','2026-07-16','Lunas',NULL),('PYM-202607-007','INV/2026/07/007','PSN-202607-007',3750000.00,'bukti_transfer_007.jpg','2026-07-25','Ditolak',NULL),('PYM-202608-008','INV/2026/08/008','PSN-202608-008',5800000.00,'bukti_transfer_008.jpg','2026-08-03','Lunas',NULL),('PYM-202608-009','INV/2026/08/009','PSN-202608-009',14000000.00,'bukti_transfer_009.jpg','2026-08-12','Lunas',NULL),('PYM-202608-010','INV/2026/08/010','PSN-202608-010',6300000.00,'bukti_transfer_010.jpg','2026-08-21','Proses Verifikasi',NULL),('PYM-202608-011','INV/2026/08/011','PSN-202608-011',12500000.00,'bukti_transfer_011.jpg','2026-08-30','Lunas',NULL),('PYM-202609-012','INV/2026/09/012','PSN-202609-012',2500000.00,'bukti_transfer_012.jpg','2026-09-08','Lunas',NULL),('PYM-202609-013','INV/2026/09/013','PSN-202609-013',17400000.00,'bukti_transfer_013.jpg','2026-09-17','Lunas',NULL),('PYM-202609-014','INV/2026/09/014','PSN-202609-014',3500000.00,'bukti_transfer_014.jpg','2026-09-26','Ditolak',NULL),('PYM-202610-015','INV/2026/10/015','PSN-202610-015',6300000.00,'bukti_transfer_015.jpg','2026-10-05','Proses Verifikasi',NULL),('PYM-202610-016','INV/2026/10/016','PSN-202610-016',5000000.00,'bukti_transfer_016.jpg','2026-10-14','Lunas',NULL),('PYM-202610-017','INV/2026/10/017','PSN-202610-017',5000000.00,'bukti_transfer_017.jpg','2026-10-23','Lunas',NULL),('PYM-202611-018','INV/2026/11/018','PSN-202611-018',8700000.00,'bukti_transfer_018.jpg','2026-11-01','Lunas',NULL),('PYM-202611-019','INV/2026/11/019','PSN-202611-019',17500000.00,'bukti_transfer_019.jpg','2026-11-10','Lunas',NULL),('PYM-202611-020','INV/2026/11/020','PSN-202611-020',4200000.00,'bukti_transfer_020.jpg','2026-11-19','Proses Verifikasi',NULL);
/*!40000 ALTER TABLE `PEMBAYARAN` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PEMESANAN`
--

DROP TABLE IF EXISTS `PEMESANAN`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PEMESANAN` (
  `id_pemesanan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_pemesanan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FK_id_pengguna` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FK_id_paket` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jadwal_keberangkatan` date DEFAULT NULL,
  `jumlah_peserta` int DEFAULT NULL,
  `total_pembayaran` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_kuota` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `batas_waktu_pembayaran` date DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penumpang` json DEFAULT NULL,
  `link_grup_wa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alasan` text COLLATE utf8mb4_unicode_ci,
  `kedaluwarsa_pada` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_pemesanan`),
  KEY `pemesanan_fk_id_pengguna_foreign` (`FK_id_pengguna`),
  KEY `pemesanan_fk_id_paket_foreign` (`FK_id_paket`),
  CONSTRAINT `pemesanan_fk_id_paket_foreign` FOREIGN KEY (`FK_id_paket`) REFERENCES `PAKET_TRAVEL` (`id_paket`),
  CONSTRAINT `pemesanan_fk_id_pengguna_foreign` FOREIGN KEY (`FK_id_pengguna`) REFERENCES `UMUM` (`id_pengguna`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PEMESANAN`
--

LOCK TABLES `PEMESANAN` WRITE;
/*!40000 ALTER TABLE `PEMESANAN` DISABLE KEYS */;
INSERT INTO `PEMESANAN` VALUES ('PSN-202606-001','TRV-26060101','USR-2026-005','PKT-BALI-01','2026-06-22',4,10000000.00,'Terkonfirmasi','Kuota Terkunci','2026-06-01',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202606-002','TRV-26061002','USR-2026-006','PKT-BRMO-01','2026-07-01',3,3750000.00,'Terkonfirmasi','Kuota Terkunci','2026-06-10',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202606-003','TRV-26061903','USR-2026-007','PKT-LMBK-01','2026-07-10',5,14500000.00,'Terkonfirmasi','Kuota Terkunci','2026-06-19',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202606-004','TRV-26062804','USR-2026-008','PKT-LBJ-01','2026-07-19',2,7000000.00,'Terkonfirmasi','Kuota Terkunci','2026-06-28',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202607-005','TRV-26070705','USR-2026-009','PKT-TOBA-01','2026-07-28',6,12600000.00,'Menunggu Verifikasi','Kuota Terkunci','2026-07-07',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202607-006','TRV-26071606','USR-2026-010','PKT-BALI-01','2026-08-06',1,2500000.00,'Terkonfirmasi','Kuota Terkunci','2026-07-16',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202607-007','TRV-26072507','USR-2026-011','PKT-BRMO-01','2026-08-15',3,3750000.00,'Dibatalkan','Kuota Dilepas','2026-07-25',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202608-008','TRV-26080308','USR-2026-012','PKT-LMBK-01','2026-08-24',2,5800000.00,'Terkonfirmasi','Kuota Terkunci','2026-08-03',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202608-009','TRV-26081209','USR-2026-005','PKT-LBJ-01','2026-09-02',4,14000000.00,'Terkonfirmasi','Kuota Terkunci','2026-08-12',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202608-010','TRV-26082110','USR-2026-006','PKT-TOBA-01','2026-09-11',3,6300000.00,'Menunggu Verifikasi','Kuota Terkunci','2026-08-21',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202608-011','TRV-26083011','USR-2026-007','PKT-BALI-01','2026-09-20',5,12500000.00,'Terkonfirmasi','Kuota Terkunci','2026-08-30',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202609-012','TRV-26090812','USR-2026-008','PKT-BRMO-01','2026-09-29',2,2500000.00,'Terkonfirmasi','Kuota Terkunci','2026-09-08',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202609-013','TRV-26091713','USR-2026-009','PKT-LMBK-01','2026-10-08',6,17400000.00,'Terkonfirmasi','Kuota Terkunci','2026-09-17',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202609-014','TRV-26092614','USR-2026-010','PKT-LBJ-01','2026-10-17',1,3500000.00,'Dibatalkan','Kuota Dilepas','2026-09-26',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202610-015','TRV-26100515','USR-2026-011','PKT-TOBA-01','2026-10-26',3,6300000.00,'Menunggu Verifikasi','Kuota Terkunci','2026-10-05',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202610-016','TRV-26101416','USR-2026-012','PKT-BALI-01','2026-11-04',2,5000000.00,'Terkonfirmasi','Kuota Terkunci','2026-10-14',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202610-017','TRV-26102317','USR-2026-005','PKT-BRMO-01','2026-11-13',4,5000000.00,'Terkonfirmasi','Kuota Terkunci','2026-10-23',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202611-018','TRV-26110118','USR-2026-006','PKT-LMBK-01','2026-11-22',3,8700000.00,'Terkonfirmasi','Kuota Terkunci','2026-11-01',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202611-019','TRV-26111019','USR-2026-007','PKT-LBJ-01','2026-12-01',5,17500000.00,'Terkonfirmasi','Kuota Terkunci','2026-11-10',NULL,NULL,NULL,NULL,NULL,NULL),('PSN-202611-020','TRV-26111920','USR-2026-008','PKT-TOBA-01','2026-12-10',2,4200000.00,'Menunggu Verifikasi','Kuota Terkunci','2026-11-19',NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `PEMESANAN` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `UMUM`
--

DROP TABLE IF EXISTS `UMUM`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `UMUM` (
  `id_pengguna` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_lengkap` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_telepon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_identitas` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dokumen_identitas` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_pengguna`),
  UNIQUE KEY `umum_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `UMUM`
--

LOCK TABLES `UMUM` WRITE;
/*!40000 ALTER TABLE `UMUM` DISABLE KEYS */;
INSERT INTO `UMUM` VALUES ('USR-2026-001','Rina Wulandari','staff@traveltour.test','081234560001','$2y$04$rQJL8bFX/IJPB3/hP6Act.zjLhLGmsV72qpu4g7yA26HqmuqCtroO','Staff',NULL,NULL),('USR-2026-002','Hendra Gunawan','manager@traveltour.test','081234560002','$2y$04$rQJL8bFX/IJPB3/hP6Act.zjLhLGmsV72qpu4g7yA26HqmuqCtroO','Manager',NULL,NULL),('USR-2026-003','Budi Santoso','driver@traveltour.test','081234560003','$2y$04$rQJL8bFX/IJPB3/hP6Act.zjLhLGmsV72qpu4g7yA26HqmuqCtroO','Driver',NULL,NULL),('USR-2026-004','Agus Pratama','driver2@traveltour.test','081234560004','$2y$04$rQJL8bFX/IJPB3/hP6Act.zjLhLGmsV72qpu4g7yA26HqmuqCtroO','Driver',NULL,NULL),('USR-2026-005','Siti Rahmawati','siti.rahmawati@mail.test','081311220005','$2y$04$rQJL8bFX/IJPB3/hP6Act.zjLhLGmsV72qpu4g7yA26HqmuqCtroO','Customer','3171014501900005','uploads/identitas/ktp-contoh.svg'),('USR-2026-006','Dewi Lestari','dewi.lestari@mail.test','081311220006','$2y$04$rQJL8bFX/IJPB3/hP6Act.zjLhLGmsV72qpu4g7yA26HqmuqCtroO','Customer','3273026702920006','uploads/identitas/ktp-contoh.svg'),('USR-2026-007','Andi Saputra','andi.saputra@mail.test','081311220007','$2y$04$rQJL8bFX/IJPB3/hP6Act.zjLhLGmsV72qpu4g7yA26HqmuqCtroO','Customer','3578031103880007',NULL),('USR-2026-008','Maya Anggraini','maya.anggraini@mail.test','081311220008','$2y$04$rQJL8bFX/IJPB3/hP6Act.zjLhLGmsV72qpu4g7yA26HqmuqCtroO','Customer','5171044408950008','uploads/identitas/ktp-contoh.svg'),('USR-2026-009','Rizky Ramadhan','rizky.ramadhan@mail.test','081311220009','$2y$04$rQJL8bFX/IJPB3/hP6Act.zjLhLGmsV72qpu4g7yA26HqmuqCtroO','Customer',NULL,NULL),('USR-2026-010','Putri Ayu Lestari','putri.ayu@mail.test','081311220010','$2y$04$rQJL8bFX/IJPB3/hP6Act.zjLhLGmsV72qpu4g7yA26HqmuqCtroO','Customer','3374055209970010','uploads/identitas/ktp-contoh.svg'),('USR-2026-011','Fajar Nugroho','fajar.nugroho@mail.test','081311220011','$2y$04$rQJL8bFX/IJPB3/hP6Act.zjLhLGmsV72qpu4g7yA26HqmuqCtroO','Customer',NULL,NULL),('USR-2026-012','Nadia Kusuma','nadia.kusuma@mail.test','081311220012','$2y$04$rQJL8bFX/IJPB3/hP6Act.zjLhLGmsV72qpu4g7yA26HqmuqCtroO','Customer','3201066106990012','uploads/identitas/ktp-contoh.svg');
/*!40000 ALTER TABLE `UMUM` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_11_09_000001_create_travel_tables',1),(2,'2026_11_09_000002_tambahan_bagian_ap',1),(3,'2026_11_19_000003_complete_travel',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
