-- Travel Tour - bagian [- AP]: struktur + data demo
-- Import ke database kosong `travel_tour` (phpMyAdmin / mysql CLI).
-- Password semua akun demo: password123
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS JADWAL_PERJALANAN, PEMBAYARAN, PEMESANAN, DRIVER, KENDARAAN, PAKET_TRAVEL, UMUM;
-- DROP TABLE IF EXISTS `UMUM`;
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

INSERT INTO `UMUM` VALUES ('USR-2026-001','Rina Wulandari','staff@traveltour.test','081234560001','$2y$12$yM4E1bIOL7oD2xecuZbwhe.uPHaeip3oNMKVw85.6lzGC3l352loi','Staff',NULL,NULL),('USR-2026-002','Hendra Gunawan','manager@traveltour.test','081234560002','$2y$12$yM4E1bIOL7oD2xecuZbwhe.uPHaeip3oNMKVw85.6lzGC3l352loi','Manager',NULL,NULL),('USR-2026-003','Budi Santoso','driver@traveltour.test','081234560003','$2y$12$yM4E1bIOL7oD2xecuZbwhe.uPHaeip3oNMKVw85.6lzGC3l352loi','Driver',NULL,NULL),('USR-2026-004','Agus Pratama','driver2@traveltour.test','081234560004','$2y$12$yM4E1bIOL7oD2xecuZbwhe.uPHaeip3oNMKVw85.6lzGC3l352loi','Driver',NULL,NULL),('USR-2026-005','Siti Rahmawati','siti.rahmawati@mail.test','081311220005','$2y$12$yM4E1bIOL7oD2xecuZbwhe.uPHaeip3oNMKVw85.6lzGC3l352loi','Customer','3171014501900005','uploads/identitas/ktp-contoh.svg'),('USR-2026-006','Dewi Lestari','dewi.lestari@mail.test','081311220006','$2y$12$yM4E1bIOL7oD2xecuZbwhe.uPHaeip3oNMKVw85.6lzGC3l352loi','Customer','3273026702920006','uploads/identitas/ktp-contoh.svg'),('USR-2026-007','Andi Saputra','andi.saputra@mail.test','081311220007','$2y$12$yM4E1bIOL7oD2xecuZbwhe.uPHaeip3oNMKVw85.6lzGC3l352loi','Customer','3578031103880007',NULL),('USR-2026-008','Maya Anggraini','maya.anggraini@mail.test','081311220008','$2y$12$yM4E1bIOL7oD2xecuZbwhe.uPHaeip3oNMKVw85.6lzGC3l352loi','Customer','5171044408950008','uploads/identitas/ktp-contoh.svg'),('USR-2026-009','Rizky Ramadhan','rizky.ramadhan@mail.test','081311220009','$2y$12$yM4E1bIOL7oD2xecuZbwhe.uPHaeip3oNMKVw85.6lzGC3l352loi','Customer',NULL,NULL),('USR-2026-010','Putri Ayu Lestari','putri.ayu@mail.test','081311220010','$2y$12$yM4E1bIOL7oD2xecuZbwhe.uPHaeip3oNMKVw85.6lzGC3l352loi','Customer','3374055209970010','uploads/identitas/ktp-contoh.svg'),('USR-2026-011','Fajar Nugroho','fajar.nugroho@mail.test','081311220011','$2y$12$yM4E1bIOL7oD2xecuZbwhe.uPHaeip3oNMKVw85.6lzGC3l352loi','Customer',NULL,NULL),('USR-2026-012','Nadia Kusuma','nadia.kusuma@mail.test','081311220012','$2y$12$yM4E1bIOL7oD2xecuZbwhe.uPHaeip3oNMKVw85.6lzGC3l352loi','Customer','3201066106990012','uploads/identitas/ktp-contoh.svg');
-- DROP TABLE IF EXISTS `PAKET_TRAVEL`;
CREATE TABLE `PAKET_TRAVEL` (
  `id_paket` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_paket` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tujuan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `durasi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `kuota` int NOT NULL,
  `jadwal_keberangkatan` date NOT NULL,
  `deskripsi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_paket` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_paket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `PAKET_TRAVEL` VALUES ('PKT-BALI-01','Bali Explore 4D3N','Bali','4 Hari 3 Malam',2500000.00,20,'2026-11-19','Pantai, Ubud, dan Tanah Lot','assets/img/paket-bali.jpg'),('PKT-BRMO-01','Bromo Sunrise 2D1N','Malang','2 Hari 1 Malam',1250000.00,20,'2026-11-26','Sunrise Penanjakan dan kawah Bromo','assets/img/paket-bromo.jpg'),('PKT-LBJ-01','Labuan Bajo Sailing 3D2N','Labuan Bajo','3 Hari 2 Malam',3500000.00,20,'2026-12-10','Pulau Komodo dan Padar','assets/img/paket-labuan.jpg'),('PKT-LMBK-01','Lombok Gili Trip 3D2N','Lombok','3 Hari 2 Malam',2900000.00,20,'2026-12-03','Gili Trawangan dan Pantai Pink','assets/img/paket-lombok.jpg'),('PKT-TOBA-01','Danau Toba Heritage 3D2N','Sumatera Utara','3 Hari 2 Malam',2100000.00,20,'2026-12-17','Samosir dan Desa Tomok','assets/img/paket-toba.jpg');
-- DROP TABLE IF EXISTS `KENDARAAN`;
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

INSERT INTO `KENDARAAN` VALUES ('KND-001','B 7123 TXA','Minibus','Toyota Hiace',14,2023,'Tersedia'),('KND-002','B 7456 TXB','Bus Medium','Isuzu Elf',19,2022,'Tersedia'),('KND-003','B 1890 TXC','MPV','Toyota Innova',7,2024,'Tersedia');
-- DROP TABLE IF EXISTS `DRIVER`;
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

INSERT INTO `DRIVER` VALUES ('DRV-001','Budi Santoso','081234560003','920312345601','Jl. Kenanga No. 12, Malang','Bertugas','USR-2026-003'),('DRV-002','Agus Pratama','081234560004','920312345602','Jl. Melati No. 7, Surabaya','Tersedia','USR-2026-004');
-- DROP TABLE IF EXISTS `PEMESANAN`;
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
  PRIMARY KEY (`id_pemesanan`),
  KEY `pemesanan_fk_id_pengguna_foreign` (`FK_id_pengguna`),
  KEY `pemesanan_fk_id_paket_foreign` (`FK_id_paket`),
  CONSTRAINT `pemesanan_fk_id_paket_foreign` FOREIGN KEY (`FK_id_paket`) REFERENCES `PAKET_TRAVEL` (`id_paket`),
  CONSTRAINT `pemesanan_fk_id_pengguna_foreign` FOREIGN KEY (`FK_id_pengguna`) REFERENCES `UMUM` (`id_pengguna`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `PEMESANAN` VALUES ('PSN-202605-001','TRV-26052201','USR-2026-005','PKT-BALI-01','2026-06-12',4,10000000.00,'Terkonfirmasi','Kuota Terkunci','2026-05-22'),('PSN-202605-002','TRV-26053102','USR-2026-006','PKT-BRMO-01','2026-06-21',3,3750000.00,'Terkonfirmasi','Kuota Terkunci','2026-05-31'),('PSN-202606-003','TRV-26060903','USR-2026-007','PKT-LMBK-01','2026-06-30',5,14500000.00,'Terkonfirmasi','Kuota Terkunci','2026-06-09'),('PSN-202606-004','TRV-26061804','USR-2026-008','PKT-LBJ-01','2026-07-09',2,7000000.00,'Terkonfirmasi','Kuota Terkunci','2026-06-18'),('PSN-202606-005','TRV-26062705','USR-2026-009','PKT-TOBA-01','2026-07-18',6,12600000.00,'Menunggu Verifikasi','Kuota Terkunci','2026-06-27'),('PSN-202607-006','TRV-26070606','USR-2026-010','PKT-BALI-01','2026-07-27',1,2500000.00,'Terkonfirmasi','Kuota Terkunci','2026-07-06'),('PSN-202607-007','TRV-26071507','USR-2026-011','PKT-BRMO-01','2026-08-05',3,3750000.00,'Dibatalkan','Kuota Dilepas','2026-07-15'),('PSN-202607-008','TRV-26072408','USR-2026-012','PKT-LMBK-01','2026-08-14',2,5800000.00,'Terkonfirmasi','Kuota Terkunci','2026-07-24'),('PSN-202608-009','TRV-26080209','USR-2026-005','PKT-LBJ-01','2026-08-23',4,14000000.00,'Terkonfirmasi','Kuota Terkunci','2026-08-02'),('PSN-202608-010','TRV-26081110','USR-2026-006','PKT-TOBA-01','2026-09-01',3,6300000.00,'Menunggu Verifikasi','Kuota Terkunci','2026-08-11'),('PSN-202608-011','TRV-26082011','USR-2026-007','PKT-BALI-01','2026-09-10',5,12500000.00,'Terkonfirmasi','Kuota Terkunci','2026-08-20'),('PSN-202608-012','TRV-26082912','USR-2026-008','PKT-BRMO-01','2026-09-19',2,2500000.00,'Terkonfirmasi','Kuota Terkunci','2026-08-29'),('PSN-202609-013','TRV-26090713','USR-2026-009','PKT-LMBK-01','2026-09-28',6,17400000.00,'Terkonfirmasi','Kuota Terkunci','2026-09-07'),('PSN-202609-014','TRV-26091614','USR-2026-010','PKT-LBJ-01','2026-10-07',1,3500000.00,'Dibatalkan','Kuota Dilepas','2026-09-16'),('PSN-202609-015','TRV-26092515','USR-2026-011','PKT-TOBA-01','2026-10-16',3,6300000.00,'Menunggu Verifikasi','Kuota Terkunci','2026-09-25'),('PSN-202610-016','TRV-26100416','USR-2026-012','PKT-BALI-01','2026-10-25',2,5000000.00,'Terkonfirmasi','Kuota Terkunci','2026-10-04'),('PSN-202610-017','TRV-26101317','USR-2026-005','PKT-BRMO-01','2026-11-03',4,5000000.00,'Terkonfirmasi','Kuota Terkunci','2026-10-13'),('PSN-202610-018','TRV-26102218','USR-2026-006','PKT-LMBK-01','2026-11-12',3,8700000.00,'Terkonfirmasi','Kuota Terkunci','2026-10-22'),('PSN-202610-019','TRV-26103119','USR-2026-007','PKT-LBJ-01','2026-11-21',5,17500000.00,'Terkonfirmasi','Kuota Terkunci','2026-10-31'),('PSN-202611-020','TRV-26110920','USR-2026-008','PKT-TOBA-01','2026-11-30',2,4200000.00,'Menunggu Verifikasi','Kuota Terkunci','2026-11-09');
-- DROP TABLE IF EXISTS `PEMBAYARAN`;
CREATE TABLE `PEMBAYARAN` (
  `id_pembayaran` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_invoice` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_pemesanan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nominal` decimal(10,2) DEFAULT NULL,
  `bukti_pembayaran` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_bayar` date DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_pembayaran`),
  KEY `pembayaran_id_pemesanan_foreign` (`id_pemesanan`),
  CONSTRAINT `pembayaran_id_pemesanan_foreign` FOREIGN KEY (`id_pemesanan`) REFERENCES `PEMESANAN` (`id_pemesanan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `PEMBAYARAN` VALUES ('PYM-202605-001','INV/2026/05/001','PSN-202605-001',10000000.00,'bukti_transfer_001.jpg','2026-05-22','Lunas'),('PYM-202605-002','INV/2026/05/002','PSN-202605-002',3750000.00,'bukti_transfer_002.jpg','2026-05-31','Lunas'),('PYM-202606-003','INV/2026/06/003','PSN-202606-003',14500000.00,'bukti_transfer_003.jpg','2026-06-09','Lunas'),('PYM-202606-004','INV/2026/06/004','PSN-202606-004',7000000.00,'bukti_transfer_004.jpg','2026-06-18','Lunas'),('PYM-202606-005','INV/2026/06/005','PSN-202606-005',12600000.00,'bukti_transfer_005.jpg','2026-06-27','Proses Verifikasi'),('PYM-202607-006','INV/2026/07/006','PSN-202607-006',2500000.00,'bukti_transfer_006.jpg','2026-07-06','Lunas'),('PYM-202607-007','INV/2026/07/007','PSN-202607-007',3750000.00,'bukti_transfer_007.jpg','2026-07-15','Ditolak'),('PYM-202607-008','INV/2026/07/008','PSN-202607-008',5800000.00,'bukti_transfer_008.jpg','2026-07-24','Lunas'),('PYM-202608-009','INV/2026/08/009','PSN-202608-009',14000000.00,'bukti_transfer_009.jpg','2026-08-02','Lunas'),('PYM-202608-010','INV/2026/08/010','PSN-202608-010',6300000.00,'bukti_transfer_010.jpg','2026-08-11','Proses Verifikasi'),('PYM-202608-011','INV/2026/08/011','PSN-202608-011',12500000.00,'bukti_transfer_011.jpg','2026-08-20','Lunas'),('PYM-202608-012','INV/2026/08/012','PSN-202608-012',2500000.00,'bukti_transfer_012.jpg','2026-08-29','Lunas'),('PYM-202609-013','INV/2026/09/013','PSN-202609-013',17400000.00,'bukti_transfer_013.jpg','2026-09-07','Lunas'),('PYM-202609-014','INV/2026/09/014','PSN-202609-014',3500000.00,'bukti_transfer_014.jpg','2026-09-16','Ditolak'),('PYM-202609-015','INV/2026/09/015','PSN-202609-015',6300000.00,'bukti_transfer_015.jpg','2026-09-25','Proses Verifikasi'),('PYM-202610-016','INV/2026/10/016','PSN-202610-016',5000000.00,'bukti_transfer_016.jpg','2026-10-04','Lunas'),('PYM-202610-017','INV/2026/10/017','PSN-202610-017',5000000.00,'bukti_transfer_017.jpg','2026-10-13','Lunas'),('PYM-202610-018','INV/2026/10/018','PSN-202610-018',8700000.00,'bukti_transfer_018.jpg','2026-10-22','Lunas'),('PYM-202610-019','INV/2026/10/019','PSN-202610-019',17500000.00,'bukti_transfer_019.jpg','2026-10-31','Lunas'),('PYM-202611-020','INV/2026/11/020','PSN-202611-020',4200000.00,'bukti_transfer_020.jpg','2026-11-09','Proses Verifikasi');
-- DROP TABLE IF EXISTS `JADWAL_PERJALANAN`;
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

INSERT INTO `JADWAL_PERJALANAN` VALUES ('JDW-2026-001','PSN-202608-009','KND-001','DRV-001','2026-08-23','Selesai'),('JDW-2026-002','PSN-202608-011','KND-002','DRV-001','2026-09-10','Selesai'),('JDW-2026-003','PSN-202608-012','KND-003','DRV-001','2026-09-19','Selesai'),('JDW-2026-004','PSN-202609-013','KND-001','DRV-001','2026-09-28','Selesai'),('JDW-2026-005','PSN-202610-016','KND-002','DRV-001','2026-10-25','Selesai'),('JDW-2026-006','PSN-202610-017','KND-003','DRV-001','2026-11-03','Selesai'),('JDW-2026-007','PSN-202610-018','KND-001','DRV-001','2026-11-12','Terjadwal'),('JDW-2026-008','PSN-202610-019','KND-002','DRV-001','2026-11-21','Terjadwal');



SET FOREIGN_KEY_CHECKS=1;
