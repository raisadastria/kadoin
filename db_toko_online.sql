-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 31, 2025 at 04:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_toko_online`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `nama_produk_saat_pesan` varchar(255) NOT NULL,
  `harga_saat_pesan` decimal(10,2) NOT NULL,
  `ukuran_produk` varchar(50) DEFAULT NULL,
  `add_on_produk` varchar(100) DEFAULT NULL,
  `catatan_item_produk` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_produk`, `nama_produk_saat_pesan`, `harga_saat_pesan`, `ukuran_produk`, `add_on_produk`, `catatan_item_produk`, `quantity`, `subtotal`) VALUES
(1, 1, 7, 'Bouquet Kuning', 210000.00, NULL, NULL, NULL, 3, 630000.00),
(2, 1, 6, 'Produk ABCD', 500000.00, NULL, NULL, NULL, 1, 500000.00),
(3, 2, 1, 'Bouquet Putih', 150000.00, NULL, NULL, NULL, 2, 300000.00),
(4, 2, 7, 'Bouquet Kuning', 210000.00, NULL, NULL, NULL, 1, 210000.00),
(5, 3, 1, 'Bouquet Putih', 150000.00, NULL, NULL, NULL, 1, 150000.00),
(6, 4, 7, 'Bouquet Kuning', 210000.00, NULL, NULL, NULL, 1, 210000.00),
(7, 5, 1, 'Bouquet Putih', 150000.00, NULL, NULL, NULL, 1, 150000.00),
(8, 6, 7, 'Bouquet Kuning', 210000.00, NULL, NULL, NULL, 1, 210000.00),
(9, 13, 7, 'Bouquet Kuning', 210000.00, NULL, NULL, NULL, 1, 210000.00),
(10, 14, 7, 'Bouquet Kuning', 210000.00, NULL, NULL, NULL, 1, 210000.00),
(11, 14, 6, 'Produk ABCD', 500000.00, NULL, NULL, NULL, 1, 500000.00);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `slug_kategori` varchar(100) NOT NULL,
  `deskripsi_kategori` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `slug_kategori`, `deskripsi_kategori`, `created_at`) VALUES
(1, 'Combination Bouquet', 'combination-bouquet', NULL, '2025-05-30 10:56:18'),
(2, 'Artificial Flower', 'artificial-flower', NULL, '2025-05-30 10:56:18'),
(3, 'Money Bouquet', 'money-bouquet', '', '2025-05-30 10:56:18'),
(4, ' Polaroid Bouquet', 'polaroid-bouquet ', NULL, '2025-05-30 10:56:18'),
(5, 'Man Bouquet', 'man-bouquet', NULL, '2025-05-30 10:56:18'),
(6, 'Butterfly Bouquet ', 'butterfly-bouque', NULL, '2025-05-30 10:56:18');

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nama_penerima` varchar(100) NOT NULL,
  `alamat_pengiriman` text NOT NULL,
  `telepon_penerima` varchar(20) NOT NULL,
  `total_harga` decimal(12,2) NOT NULL,
  `status_pesanan` enum('pending','processing','shipped','completed','cancelled') DEFAULT 'pending',
  `catatan_pesanan` text DEFAULT NULL,
  `waktu_pesanan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_user`, `nama_penerima`, `alamat_pengiriman`, `telepon_penerima`, `total_harga`, `status_pesanan`, `catatan_pesanan`, `waktu_pesanan`) VALUES
(1, 1, 'Admin (Dev)', 'wkermwkekwet', '09204023403', 1130000.00, 'pending', 'wemrwmer me r', '2025-05-28 04:53:21'),
(2, 2, 'Rizky Dimas', 'Cimahi', '09204023403', 510000.00, 'pending', '', '2025-05-29 13:06:11'),
(3, 2, 'Rizky Dimas', 'ytrhjr', '45674', 150000.00, 'pending', '', '2025-05-29 13:06:33'),
(4, 100, 'Nabila', 'Bandung', '34563567', 210000.00, 'shipped', '', '2025-05-29 14:17:46'),
(5, 100, 'Nabila', 'Cimahi', '09204023403', 150000.00, 'pending', 'aaa', '2025-05-29 16:18:32'),
(6, 101, 'Dimas', 'Bandung', '5678', 210000.00, 'pending', '', '2025-05-30 09:48:39'),
(13, 100, 'Nabila', 'kl/l;l', '808908909', 210000.00, 'pending', 'l;il;il;', '2025-05-30 10:40:38'),
(14, 100, 'Nabila Alya', 'abcd dot id', '09204023403', 710000.00, 'pending', '', '2025-05-30 10:47:25');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `id_kategori` int(11) DEFAULT NULL,
  `gambar1` varchar(255) DEFAULT NULL,
  `gambar2` varchar(255) DEFAULT NULL,
  `gambar3` varchar(255) DEFAULT NULL,
  `gambar4` varchar(255) DEFAULT NULL,
  `status_produk` enum('aktif','tidak aktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `deskripsi`, `harga`, `stok`, `id_kategori`, `gambar1`, `gambar2`, `gambar3`, `gambar4`, `status_produk`, `created_at`) VALUES
(1, 'Artificial Flower - Putih', 'Ini buqet ajaib, gk tdur 2 hari', 65000.00, 9, 2, 'produk_edit_6839c07e82d328.40749987.jpg', 'produk_edit_6839c07e832006.10815525.jpg', 'produk_edit_6839c07e83a542.36583752.jpg', 'produk_edit_6839c07e84c5c3.62802829.jpg', 'aktif', '2025-05-27 15:05:37'),
(5, 'Artificial Flower - Merah', 'Merah Bouquet', 65000.00, 5, 2, 'produk_edit_6839c03623e4d9.79142716.jpg', 'produk_edit_6839c036251bd0.37449855.jpg', 'produk_edit_6839c0362670b9.17766314.jpg', 'produk_edit_6839c036276391.80514109.jpg', 'aktif', '2025-05-27 22:11:42'),
(6, 'Artificial Flower - Biru', 'Artificial Flower Berwarna Biru', 65000.00, 10, 2, 'produk_edit_6839bfdc9f0b39.34498065.jpg', 'produk_edit_6839bfdca105c9.94342682.jpg', 'produk_edit_6839bfdca267b1.57351105.jpg', 'produk_edit_6839bfdca33ec7.37722683.jpg', 'aktif', '2025-05-27 22:17:56'),
(7, 'Artificial Flower - Pink', 'Artificial Flower', 60000.00, 7, 2, 'produk_edit_6839bf3ff3f574.00078052.jpg', 'produk_edit_6839bf40013aa8.28331722.jpg', 'produk_edit_6839bf400206b7.26064489.jpg', 'produk_edit_6839bf40029964.31195630.jpg', 'aktif', '2025-05-27 22:19:03'),
(9, 'Polaroid Bouquet - Small', 'Foto polaroid 3', 30000.00, 7, 4, 'produk_6839c1131929e7.69763462.jpg', 'produk_6839c113197c67.69712697.jpg', '', '', 'aktif', '2025-05-30 14:30:43'),
(10, 'Polaroid Bouquet - Medium', 'Isi 5 Foto', 45000.00, 8, 4, 'produk_6839c6b4ad03b0.83489820.jpg', NULL, NULL, NULL, 'aktif', '2025-05-30 14:54:44'),
(11, 'Polaroid Bouquet - Big', 'Isi 20 Foto', 80000.00, 5, 4, 'produk_6839c700c50296.84980237.jpg', 'produk_6839c700c53338.06099795.jpg', 'produk_6839c700c549a0.90113908.jpg', NULL, 'aktif', '2025-05-30 14:56:00'),
(12, 'Bouquet Man - Beer', '3 Beer', 100000.00, 10, 5, 'produk_6839c7e5cbb445.37987983.jpg', 'produk_6839c7e5cbd778.80579371.jpg', NULL, NULL, 'aktif', '2025-05-30 14:59:49'),
(14, 'Bouquet Man - Skincare', 'Paket Skincare Kahf', 150000.00, 10, 5, 'produk_6839c870ae97d4.16508020.jpg', 'produk_6839c870aecd61.00280100.jpg', 'produk_6839c870b50a98.45082278.jpg', NULL, 'aktif', '2025-05-30 15:02:08'),
(15, 'Bouquet Man - Rokok', 'Bisa request Rokok 3 Bungkus (Tulis di Catatan)', 175000.00, 10, 5, 'produk_6839c8e1b739c0.84708353.jpg', 'produk_6839c8e1b75ac5.50259095.jpg', NULL, NULL, 'aktif', '2025-05-30 15:04:01'),
(16, 'Bouquet Man - Black Flower', '10 Tangkai Bunga berwarna hitam', 20000.00, 10, 5, 'produk_6839cb020cb7c7.56931464.jpg', 'produk_6839cb020d0174.72289973.jpg', 'produk_6839cb020d1f48.39274356.jpg', NULL, 'aktif', '2025-05-30 15:13:06'),
(17, 'Combination Bouquet - Flower X Money 100K', '10 Flower + 9 lembar uang Rp.100.000', 1999900.00, 10, 1, 'produk_6839cb8ac11602.20495343.jpg', 'produk_6839cb8ac13956.63976593.jpg', '', '', 'aktif', '2025-05-30 15:15:22'),
(18, 'Combination Bouquet - Polaroid X Snack', '6 Polaroid photo + req 5 Snack', 70000.00, 10, 1, 'produk_6839cc46426d02.86090404.jpg', 'produk_6839cc46429116.80445545.jpg', NULL, NULL, 'aktif', '2025-05-30 15:18:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama_user` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama_user`, `email`, `password`, `alamat`, `telepon`, `role`, `created_at`) VALUES
(1, 'Admin Toko', 'admin@toko.com', '$2y$10$U..pr4kSidPXhDOqUPUB1OnlRXurHNcW.MEdDDZxaInRatzqdHHK', 'Cimahi', NULL, 'admin', '2025-05-27 13:40:47'),
(2, 'Kidim', 'rizkydimassi006@gmail.com', '$2y$10$HnF/pNFRf8WtWFgSA1BYh.ow28iA94R/y/GZZFaxWy2.PkYD61Xoa', 'Cimahi', '08912312312', 'user', '2025-05-29 13:05:29'),
(100, 'Nabila', 'Nabila@gmail.com', '$2y$10$cKSK1tvbfH7a/YQp.hVYB.Px4usHmFd84L/MsLrIltd777G3dneq2', 'Cimahi', '0891231211', 'admin', '2025-05-29 14:07:10'),
(101, 'Dimas', 'Dimas@gmail.com', '$2y$10$caFnY.CKm9AHEqcEIiw0huDA1i.IGlMJeU7mzIo2bAXYxkmgFEk1e', 'Cimahi', '587878', 'user', '2025-05-30 09:47:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `slug_kategori` (`slug_kategori`);

--
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `fk_produk_kategori` (`id_kategori`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`),
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
