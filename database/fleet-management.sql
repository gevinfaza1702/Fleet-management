-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 22, 2025 at 11:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fleet-management`
--

-- --------------------------------------------------------

--
-- Table structure for table `kendaraan`
--

CREATE TABLE `kendaraan` (
  `id_kendaraan` int(11) NOT NULL,
  `nomor_polisi` varchar(20) NOT NULL,
  `model` varchar(50) DEFAULT NULL,
  `status` enum('Aktif','Non-Aktif','Servis') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kendaraan`
--

INSERT INTO `kendaraan` (`id_kendaraan`, `nomor_polisi`, `model`, `status`) VALUES
(1, 'B 1234 XYZ', 'Toyota Hilux', 'Aktif'),
(2, 'D 5678 ABC', 'Mitsubishi Fuso', 'Servis'),
(3, 'F 9876 DEF', 'Isuzu Elf', 'Aktif'),
(4, 'H 4321 GHI', 'Hino Dutro', 'Non-Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `konsumsi_bahan_bakar`
--

CREATE TABLE `konsumsi_bahan_bakar` (
  `id_bbm` int(11) NOT NULL,
  `id_kendaraan` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jumlah_bahan_bakar` float DEFAULT NULL,
  `jarak_tempuh` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konsumsi_bahan_bakar`
--

INSERT INTO `konsumsi_bahan_bakar` (`id_bbm`, `id_kendaraan`, `tanggal`, `jumlah_bahan_bakar`, `jarak_tempuh`) VALUES
(1, 1, '2024-06-01', 50, 150),
(2, 2, '2024-06-05', 40, 100),
(3, 3, '2024-06-10', 60, 250);

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int(11) NOT NULL,
  `id_kendaraan` int(11) DEFAULT NULL,
  `pesan` varchar(255) DEFAULT NULL,
  `status` enum('Belum Dibaca','Dibaca') DEFAULT 'Belum Dibaca',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pemeliharaan`
--

CREATE TABLE `pemeliharaan` (
  `id_pemeliharaan` int(11) NOT NULL,
  `id_kendaraan` int(11) DEFAULT NULL,
  `tanggal_servis` date DEFAULT NULL,
  `jenis_perawatan` varchar(100) DEFAULT NULL,
  `status` enum('Selesai','Dalam Proses','Overdue') DEFAULT 'Dalam Proses'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemeliharaan`
--

INSERT INTO `pemeliharaan` (`id_pemeliharaan`, `id_kendaraan`, `tanggal_servis`, `jenis_perawatan`, `status`) VALUES
(1, 2, '2024-06-15', 'Ganti Oli', 'Dalam Proses');

-- --------------------------------------------------------

--
-- Table structure for table `pengemudi`
--

CREATE TABLE `pengemudi` (
  `id_pengemudi` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `sim` varchar(50) DEFAULT NULL,
  `status` enum('Aktif','Cuti','Tidak Aktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengemudi`
--

INSERT INTO `pengemudi` (`id_pengemudi`, `nama`, `sim`, `status`) VALUES
(1, 'Ahmad Setiawan', 'SIM A123456', 'Aktif'),
(2, 'Budi Santoso', 'SIM B987654', 'Aktif'),
(3, 'Siti Aminah', 'SIM C654321', '');

-- --------------------------------------------------------

--
-- Table structure for table `perjalanan`
--

CREATE TABLE `perjalanan` (
  `id_perjalanan` int(11) NOT NULL,
  `id_kendaraan` int(11) DEFAULT NULL,
  `id_pengemudi` int(11) DEFAULT NULL,
  `lokasi_awal` varchar(100) DEFAULT NULL,
  `lokasi_tujuan` varchar(100) DEFAULT NULL,
  `waktu_keberangkatan` datetime DEFAULT NULL,
  `waktu_tiba` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perjalanan`
--

INSERT INTO `perjalanan` (`id_perjalanan`, `id_kendaraan`, `id_pengemudi`, `lokasi_awal`, `lokasi_tujuan`, `waktu_keberangkatan`, `waktu_tiba`) VALUES
(1, 1, 2, 'Jakarta', 'Bandung', '2024-06-01 08:00:00', NULL),
(2, 2, 2, 'Surabaya', 'Malang', '2024-06-05 09:30:00', '2024-06-05 11:00:00'),
(3, 3, 1, 'Jakarta', 'Yogyakarta', '2024-06-10 07:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$4E16LmTqA1Af2jY/KGCjQe7fVOcyvyy2RRG5eCoBG8rsd3EAZpi7W');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kendaraan`
--
ALTER TABLE `kendaraan`
  ADD PRIMARY KEY (`id_kendaraan`);

--
-- Indexes for table `konsumsi_bahan_bakar`
--
ALTER TABLE `konsumsi_bahan_bakar`
  ADD PRIMARY KEY (`id_bbm`),
  ADD KEY `id_kendaraan` (`id_kendaraan`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `id_kendaraan` (`id_kendaraan`);

--
-- Indexes for table `pemeliharaan`
--
ALTER TABLE `pemeliharaan`
  ADD PRIMARY KEY (`id_pemeliharaan`),
  ADD KEY `id_kendaraan` (`id_kendaraan`);

--
-- Indexes for table `pengemudi`
--
ALTER TABLE `pengemudi`
  ADD PRIMARY KEY (`id_pengemudi`);

--
-- Indexes for table `perjalanan`
--
ALTER TABLE `perjalanan`
  ADD PRIMARY KEY (`id_perjalanan`),
  ADD KEY `id_kendaraan` (`id_kendaraan`),
  ADD KEY `id_pengemudi` (`id_pengemudi`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kendaraan`
--
ALTER TABLE `kendaraan`
  MODIFY `id_kendaraan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `konsumsi_bahan_bakar`
--
ALTER TABLE `konsumsi_bahan_bakar`
  MODIFY `id_bbm` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pemeliharaan`
--
ALTER TABLE `pemeliharaan`
  MODIFY `id_pemeliharaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengemudi`
--
ALTER TABLE `pengemudi`
  MODIFY `id_pengemudi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `perjalanan`
--
ALTER TABLE `perjalanan`
  MODIFY `id_perjalanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `konsumsi_bahan_bakar`
--
ALTER TABLE `konsumsi_bahan_bakar`
  ADD CONSTRAINT `konsumsi_bahan_bakar_ibfk_1` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan` (`id_kendaraan`);

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan` (`id_kendaraan`);

--
-- Constraints for table `pemeliharaan`
--
ALTER TABLE `pemeliharaan`
  ADD CONSTRAINT `pemeliharaan_ibfk_1` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan` (`id_kendaraan`);

--
-- Constraints for table `perjalanan`
--
ALTER TABLE `perjalanan`
  ADD CONSTRAINT `perjalanan_ibfk_1` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan` (`id_kendaraan`),
  ADD CONSTRAINT `perjalanan_ibfk_2` FOREIGN KEY (`id_pengemudi`) REFERENCES `pengemudi` (`id_pengemudi`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
