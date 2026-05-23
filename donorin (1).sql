-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2026 at 12:41 PM
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
-- Database: `donorin`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'karin', 'ecc25196f81ac83be793ee36158717e6');

-- --------------------------------------------------------

--
-- Table structure for table `kritik_saran`
--

CREATE TABLE `kritik_saran` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `kategori` enum('kritik','saran','pertanyaan') NOT NULL,
  `pesan` text NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kritik_saran`
--

INSERT INTO `kritik_saran` (`id`, `nama`, `email`, `kategori`, `pesan`, `tanggal`) VALUES
(1, 'tirta tandela', 'tandela@gmail.com', 'saran', 'Semoga stok darah selalu diperbarui secara real-time!', '2026-04-30 11:59:15'),
(2, 'imas nazalia', 'nazalia@gmail.com', 'kritik', 'Butuh fitur notifikasi ketika darah kritis. Butuh banget soalnya...', '2026-04-30 11:59:15'),
(3, 'gaza rabbani', 'rabbani@gmail.com', 'pertanyaan', 'Gimana cara hubungin petugas jika butuh darah urgent?', '2026-04-30 11:59:15'),
(4, 'karin', 'karin@gmail.com', 'saran', 'tambahin beberapa dokumentasi di webnnya supaya lebih menarik', '2026-04-30 12:01:55'),
(5, 'nisasanisa', 'nisasanisa@gmail.com', 'saran', 'bagus bangett tapi tambahin beberapa jadwal kegiatan donor darah supaya saya bisa kesana', '2026-05-06 21:17:04'),
(11, 'sobi', 'sobi@gmail.com', 'saran', 'bagus bagus bagus', '2026-05-07 11:26:57'),
(12, 'sobi', 'sobi@gmail.com', 'saran', 'bagus bagus bagus', '2026-05-07 12:01:20'),
(13, 'sobi', 'sobi@gmail.com', 'saran', 'bagus bagus bagus', '2026-05-07 12:01:44'),
(14, 'karin', 'karin@gmail.com', 'saran', 'lebih lengkap lagi', '2026-05-21 12:15:40');

-- --------------------------------------------------------

--
-- Table structure for table `relawan`
--

CREATE TABLE `relawan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `tgl_lahir` date NOT NULL,
  `goldar` enum('A','B','O','AB') NOT NULL,
  `tanggal_daftar` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `kritik_saran`
--
ALTER TABLE `kritik_saran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `relawan`
--
ALTER TABLE `relawan`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kritik_saran`
--
ALTER TABLE `kritik_saran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `relawan`
--
ALTER TABLE `relawan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

-- =============================================
-- TABEL PENDONOR (akun login pendonor)
-- =============================================
CREATE TABLE IF NOT EXISTS pendonor (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    nama           VARCHAR(100) NOT NULL,
    email          VARCHAR(100) NOT NULL UNIQUE,
    password       VARCHAR(255) NOT NULL,
    no_hp          VARCHAR(20)  NOT NULL,
    tgl_lahir      DATE         NOT NULL,
    umur           INT          NOT NULL,
    jenis_kelamin  ENUM('L','P') NOT NULL,
    goldar         ENUM('A','B','O','AB') NOT NULL,
    berat_badan    INT          NOT NULL,
    kota           VARCHAR(100) NOT NULL,
    pekerjaan      VARCHAR(100),
    alamat         TEXT,
    pernah_donor   ENUM('ya','tidak') DEFAULT 'tidak',
    terakhir_donor DATE,
    status_aktif   ENUM('aktif','nonaktif') DEFAULT 'aktif',
    foto           VARCHAR(255) DEFAULT NULL,
    tanggal_daftar DATETIME     DEFAULT CURRENT_TIMESTAMP
);


-- =============================================
-- TABEL PASIEN (akun login pasien/pencari donor)
-- =============================================
CREATE TABLE IF NOT EXISTS pasien (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    nama           VARCHAR(100) NOT NULL,
    email          VARCHAR(100) NOT NULL UNIQUE,
    password       VARCHAR(255) NOT NULL,
    no_hp          VARCHAR(20)  NOT NULL,
    goldar_dibutuhkan ENUM('A','B','O','AB') NOT NULL,
    kota           VARCHAR(100) NOT NULL,
    alamat         TEXT,
    nama_rs        VARCHAR(150),
    tanggal_daftar DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- TABEL PERMINTAAN DARAH
-- =============================================
CREATE TABLE IF NOT EXISTS permintaan_darah (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    pasien_id      INT NOT NULL,
    goldar         ENUM('A','B','O','AB') NOT NULL,
    jumlah_kantong INT NOT NULL DEFAULT 1,
    nama_rs        VARCHAR(150) NOT NULL,
    kota           VARCHAR(100) NOT NULL,
    alamat_rs      TEXT,
    keterangan     TEXT,
    status         ENUM('menunggu','diproses','terpenuhi','dibatalkan') DEFAULT 'menunggu',
    tanggal        DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pasien_id) REFERENCES pasien(id) ON DELETE CASCADE
);


-- =============================================
-- TABEL RESPON PENDONOR ke PERMINTAAN DARAH
-- =============================================
CREATE TABLE IF NOT EXISTS respon_donor (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    permintaan_id   INT NOT NULL,
    pendonor_id     INT NOT NULL,
    pesan           TEXT,
    status          ENUM('bersedia','tidak_bisa') DEFAULT 'bersedia',
    tanggal         DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (permintaan_id) REFERENCES permintaan_darah(id) ON DELETE CASCADE,
    FOREIGN KEY (pendonor_id)   REFERENCES pendonor(id) ON DELETE CASCADE
);

-- =============================================
-- TABEL NOTIFIKASI
-- =============================================
CREATE TABLE IF NOT EXISTS notifikasi (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    tujuan_tipe ENUM('pendonor','pasien') NOT NULL,
    tujuan_id   INT NOT NULL,
    judul       VARCHAR(200) NOT NULL,
    pesan       TEXT NOT NULL,
    sudah_baca  TINYINT(1) DEFAULT 0,
    tanggal     DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- TABEL STOK DARAH (dinamis, dikelola admin)
-- =============================================
CREATE TABLE IF NOT EXISTS stok_darah (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    goldar     ENUM('A','B','O','AB') NOT NULL UNIQUE,
    jumlah     INT NOT NULL DEFAULT 0,
    status     ENUM('Tersedia','Kritis','Habis') DEFAULT 'Tersedia',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
