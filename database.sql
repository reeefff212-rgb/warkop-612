-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2026 at 09:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

CREATE DATABASE IF NOT EXISTS `warkop612` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `warkop612`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `warkop612`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `catatan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_menu`
--

CREATE TABLE `kategori_menu` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meja`
--

CREATE TABLE `meja` (
  `id_meja` int(11) NOT NULL,
  `nomor_meja` int(11) NOT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `status_meja` enum('tersedia','terisi') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id_menu` int(11) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(12,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `foto_menu` varchar(255) DEFAULT NULL,
  `status_menu` enum('tersedia','habis') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `metode_pembayaran` enum('QRIS','cash') NOT NULL,
  `jumlah_bayar` decimal(12,2) NOT NULL,
  `status_pembayaran` enum('menunggu','berhasil','gagal') DEFAULT 'menunggu',
  `waktu_pembayaran` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `nama_pengguna` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_meja` int(11) DEFAULT NULL,
  `id_pengguna` int(11) DEFAULT NULL,
  `nama_pemesan` varchar(100) NOT NULL,
  `tipe_pesanan` enum('QR','manual') NOT NULL,
  `jenis_pesanan` enum('dine_in','takeaway') NOT NULL DEFAULT 'dine_in',
  `waktu_pesanan` datetime DEFAULT current_timestamp(),
  `status_pesanan` enum('menunggu','diproses','selesai','dibatalkan') DEFAULT 'menunggu',
  `total_harga` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_menu` (`id_menu`);

--
-- Indexes for table `kategori_menu`
--
ALTER TABLE `kategori_menu`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `meja`
--
ALTER TABLE `meja`
  ADD PRIMARY KEY (`id_meja`),
  ADD UNIQUE KEY `nomor_meja` (`nomor_meja`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_meja` (`id_meja`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori_menu`
--
ALTER TABLE `kategori_menu`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meja`
--
ALTER TABLE `meja`
  MODIFY `id_meja` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_menu` (`id_kategori`);

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_meja`) REFERENCES `meja` (`id_meja`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`);

--
-- Data awal (seed)
--
INSERT INTO `kategori_menu` (`id_kategori`,`nama_kategori`) VALUES
(1,'Kopi Tradisional'),(2,'Minuman Non-Kopi'),(3,'Makanan Berat'),(4,'Camilan / Snack');

INSERT INTO `meja` (`id_meja`,`nomor_meja`,`qr_code`,`status_meja`) VALUES
(1,1,'pelanggan/index.php?meja=1','tersedia'),(2,2,'pelanggan/index.php?meja=2','tersedia'),
(3,3,'pelanggan/index.php?meja=3','tersedia'),(4,4,'pelanggan/index.php?meja=4','tersedia'),
(5,5,'pelanggan/index.php?meja=5','tersedia');

INSERT INTO `menu` (`id_menu`,`id_kategori`,`nama_menu`,`deskripsi`,`harga`,`stok`,`foto_menu`,`status_menu`) VALUES
(1,1,'Kopi Tubruk 612 Signature','Biji kopi robusta pilihan, disangrai medium-dark dengan aroma karamel alami. Disajikan dengan cangkir warkop otentik.',10000,100,NULL,'tersedia'),
(2,1,'Kopi Susu Tradisional','Kopi tubruk dengan susu kental manis, manis dan creamy.',12000,100,NULL,'tersedia'),
(3,1,'Es Kopi Susu Aren','Espresso, susu segar dan gula aren yang seimbang.',15000,80,NULL,'tersedia'),
(4,1,'V60 Gayo Natural','Seduhan manual single origin Gayo, fruity dan bersih.',22000,40,NULL,'tersedia'),
(5,1,'Kopi Jos Arang Panas','Kopi hitam dengan arang membara khas angkringan.',12000,60,NULL,'tersedia'),
(6,2,'Lemon Tea','Teh segar dengan perasan lemon.',7000,100,NULL,'tersedia'),
(7,2,'Nutrisari All Varian','Aneka rasa, pilih varian saat memesan (catatan).',5000,100,NULL,'tersedia'),
(8,2,'Soda Susu','Soda dingin dengan susu dan sirup.',8000,60,NULL,'tersedia'),
(9,2,'Kuku Bima Anggur Susu','Minuman energi rasa anggur dengan susu.',12000,60,NULL,'tersedia'),
(10,2,'Wedang Jahe Susu','Jahe hangat dengan susu, cocok untuk malam.',8000,0,NULL,'habis'),
(11,3,'Indomie Telur Spesial','Indomie goreng/kuah dengan telur, bisa atur level pedas.',10000,100,NULL,'tersedia'),
(12,3,'Magelangan','Nasi goreng campur mie ala angkringan.',15000,50,NULL,'tersedia'),
(13,3,'Kwetiaw Goreng','Kwetiaw goreng gurih dengan sayur dan telur.',15000,50,NULL,'tersedia'),
(14,3,'Nasi Goreng Gila Warkop','Nasi goreng porsi besar dengan sosis, bakso dan telur.',25000,40,NULL,'tersedia'),
(15,4,'Pisang Goreng (Isi 4)','Pisang goreng renyah, disajikan hangat.',10000,60,NULL,'tersedia'),
(16,4,'Roti Bakar All Varian','Roti bakar aneka topping (coklat, keju, srikaya).',10000,60,NULL,'tersedia'),
(17,4,'Kentang Goreng','Kentang goreng crispy dengan saus.',15000,50,NULL,'tersedia'),
(18,4,'Tempe Mendoan (4 pcs)','Mendoan tipis dengan sambal kecap.',12000,50,NULL,'tersedia');

-- Akun pengguna dibuat lewat install.php (kata sandi di-hash dengan password_hash).
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
