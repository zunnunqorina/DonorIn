-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Jun 2026 pada 11.05
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.5.6

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
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'karin', 'karincantik'),
(2, 'putra', 'putrakeren'),
(3, 'imas', 'sukamulia'),
(0, 'karin', 'ecc25196f81ac83be793ee36158717e6');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `event_donor`
--

CREATE TABLE `event_donor` (
  `id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `lokasi` varchar(200) NOT NULL,
  `alamat` text DEFAULT NULL,
  `kota` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `kuota` int(11) DEFAULT 0,
  `penyelenggara` varchar(150) DEFAULT NULL,
  `kontak` varchar(100) DEFAULT NULL,
  `status` enum('aktif','selesai','batal') DEFAULT 'aktif',
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `event_donor`
--

INSERT INTO `event_donor` (`id`, `judul`, `deskripsi`, `lokasi`, `alamat`, `kota`, `tanggal`, `jam_mulai`, `jam_selesai`, `kuota`, `penyelenggara`, `kontak`, `status`, `dibuat_pada`) VALUES
(1, 'Donor Darah HUT RI ke-80', 'Kegiatan donor darah dalam rangka HUT Kemerdekaan RI', 'Lapangan Sangkareang', 'Jl. Pejanggik No.1, Cakranegara', 'Mataram', '2025-08-17', '08:00:00', '12:00:00', 100, 'PMI Kota Mataram', '0370-123456', 'aktif', '2026-05-23 18:31:00'),
(2, 'Donor Darah Kampus UNRAM', 'Kegiatan donor darah rutin mahasiswa UNRAM', 'Gedung Rektorat UNRAM', 'Jl. Majapahit No.62, Mataram', 'Mataram', '2025-09-10', '09:00:00', '14:00:00', 80, 'BEM UNRAM & PMI', '081234567890', 'aktif', '2026-05-23 18:31:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `event_sosialisasi`
--

CREATE TABLE `event_sosialisasi` (
  `id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `lokasi` varchar(200) NOT NULL,
  `alamat` text DEFAULT NULL,
  `kota` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `pembicara` varchar(150) DEFAULT NULL,
  `target_peserta` int(11) DEFAULT 0,
  `kontak` varchar(100) DEFAULT NULL,
  `status` enum('aktif','selesai','batal') DEFAULT 'aktif',
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `event_sosialisasi`
--

INSERT INTO `event_sosialisasi` (`id`, `judul`, `deskripsi`, `lokasi`, `alamat`, `kota`, `tanggal`, `jam_mulai`, `jam_selesai`, `pembicara`, `target_peserta`, `kontak`, `status`, `dibuat_pada`) VALUES
(1, 'Sosialisasi Pentingnya Donor Darah', 'Edukasi manfaat dan syarat donor darah untuk masyarakat umum', 'Balai Desa Ampenan', 'Jl. Pabean No.5, Ampenan', 'Mataram', '2025-08-05', '09:00:00', '11:00:00', 'dr. Hendra Kusuma', 50, '081298765432', 'aktif', '2026-05-23 18:31:00'),
(2, 'Talkshow Donor Darah di SMAN 1 Mataram', 'Sosialisasi donor darah untuk pelajar SMA', 'Aula SMAN 1 Mataram', 'Jl. Pendidikan No.1, Mataram', 'Mataram', '2025-09-20', '10:00:00', '12:00:00', 'Tim PMI Mataram', 200, '0370-654321', 'aktif', '2026-05-23 18:31:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kritik_saran`
--

CREATE TABLE `kritik_saran` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `kategori` enum('kritik','saran','pertanyaan') NOT NULL,
  `pesan` text NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `sudah_baca` tinyint(1) DEFAULT 0,
  `balasan` text DEFAULT NULL,
  `tgl_balas` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kritik_saran`
--

INSERT INTO `kritik_saran` (`id`, `nama`, `email`, `kategori`, `pesan`, `tanggal`, `sudah_baca`, `balasan`, `tgl_balas`) VALUES
(1, 'tirta tandela', 'tandela@gmail.com', 'saran', 'Semoga stok darah selalu diperbarui secara real-time!', '2026-04-30 11:59:15', 0, NULL, NULL),
(2, 'imas nazalia', 'nazalia@gmail.com', 'kritik', 'Butuh fitur notifikasi ketika darah kritis. Butuh banget soalnya...', '2026-04-30 11:59:15', 0, NULL, NULL),
(3, 'gaza rabbani', 'rabbani@gmail.com', 'pertanyaan', 'Gimana cara hubungin petugas jika butuh darah urgent?', '2026-04-30 11:59:15', 0, NULL, NULL),
(4, 'karin', 'karin@gmail.com', 'saran', 'tambahin beberapa dokumentasi di webnnya supaya lebih menarik', '2026-04-30 12:01:55', 0, NULL, NULL),
(5, 'nisasanisa', 'nisasanisa@gmail.com', 'saran', 'bagus bangett tapi tambahin beberapa jadwal kegiatan donor darah supaya saya bisa kesana', '2026-05-06 21:17:04', 0, NULL, NULL),
(11, 'sobi', 'sobi@gmail.com', 'saran', 'bagus bagus bagus', '2026-05-07 11:26:57', 0, NULL, NULL),
(12, 'sobi', 'sobi@gmail.com', 'saran', 'bagus bagus bagus', '2026-05-07 12:01:20', 0, NULL, NULL),
(13, 'sobi', 'sobi@gmail.com', 'saran', 'bagus bagus bagus', '2026-05-07 12:01:44', 0, NULL, NULL),
(14, 'Budi Santoso', 'budi@gmail.com', 'saran', 'Semoga stok darah selalu diperbarui secara real-time!', '2026-05-23 18:31:00', 1, 'terima kasih sarannya budi', '2026-06-06 16:37:37'),
(15, 'Siti Aminah', 'siti@yahoo.com', 'kritik', 'Butuh fitur notifikasi ketika darah kritis.', '2026-05-23 18:31:00', 0, NULL, NULL),
(16, 'Ahmad Rifai', 'ahmad@gmail.com', 'pertanyaan', 'Bagaimana cara menghubungi petugas jika butuh darah segera?', '2026-05-23 18:31:00', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL,
  `tujuan_tipe` enum('pendonor','pasien') NOT NULL,
  `tujuan_id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `pesan` text NOT NULL,
  `sudah_baca` tinyint(1) DEFAULT 0,
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pasien`
--

CREATE TABLE `pasien` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `goldar_dibutuhkan` enum('A','B','O','AB') NOT NULL,
  `kota` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `nama_rs` varchar(150) DEFAULT NULL,
  `tanggal_daftar` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pendonor`
--

CREATE TABLE `pendonor` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `tgl_lahir` date NOT NULL,
  `umur` int(11) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `goldar` enum('A','B','O','AB') NOT NULL,
  `berat_badan` int(11) NOT NULL,
  `kota` varchar(100) NOT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `pernah_donor` enum('ya','tidak') DEFAULT 'tidak',
  `terakhir_donor` date DEFAULT NULL,
  `status_aktif` enum('aktif','nonaktif') DEFAULT 'aktif',
  `foto` varchar(255) DEFAULT NULL,
  `tanggal_daftar` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `permintaan_darah`
--

CREATE TABLE `permintaan_darah` (
  `id` int(11) NOT NULL,
  `pasien_id` int(11) NOT NULL,
  `goldar` enum('A','B','O','AB') NOT NULL,
  `jumlah_kantong` int(11) NOT NULL DEFAULT 1,
  `nama_rs` varchar(150) NOT NULL,
  `kota` varchar(100) NOT NULL,
  `alamat_rs` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('menunggu','diproses','terpenuhi','dibatalkan') DEFAULT 'menunggu',
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `relawan`
--

CREATE TABLE `relawan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `tgl_lahir` date NOT NULL,
  `goldar` enum('A','B','O','AB') NOT NULL,
  `tanggal_daftar` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `respon_donor`
--

CREATE TABLE `respon_donor` (
  `id` int(11) NOT NULL,
  `permintaan_id` int(11) NOT NULL,
  `pendonor_id` int(11) NOT NULL,
  `pesan` text DEFAULT NULL,
  `status` enum('bersedia','tidak_bisa') DEFAULT 'bersedia',
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('LQ5kgrOcABTqY1XNk2u9NxwDTcS3ShhX2qj138dB', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'eyJfdG9rZW4iOiJkSlp5bEtGWEg2ZFBPZzlyTDNHb0sxT0xnNW1jU1ZESFN6NHgwYnpyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1778854929);

-- --------------------------------------------------------

--
-- Struktur dari tabel `stok_darah`
--

CREATE TABLE `stok_darah` (
  `id` int(11) NOT NULL,
  `goldar` enum('A','B','O','AB') NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 0,
  `status` enum('Tersedia','Kritis','Habis') DEFAULT 'Tersedia',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pasien','pendonor') NOT NULL DEFAULT 'pendonor',
  `no_hp` varchar(20) DEFAULT NULL,
  `goldar` enum('A','B','O','AB') DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `tanggal_daftar` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `nama`, `email`, `password`, `role`, `no_hp`, `goldar`, `kota`, `tanggal_daftar`) VALUES
(1, 'Imas Nazalia', 'imas@gmail.com', '$2y$12$Rd5CuN9jST2st5UU.VG50uobjEY5kk1j6UgCN4CRoofq3p1eOg2.G', 'pendonor', '085555552154', 'O', 'Sukamulia', '2026-05-24 00:56:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--
CREATE TABLE IF NOT EXISTS stok_darah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    goldar ENUM('A','B','O','AB') NOT NULL UNIQUE,
    jumlah_kantong INT DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by VARCHAR(100) DEFAULT 'Admin'
);

-- Isi data awal
INSERT INTO stok_darah (goldar, jumlah_kantong) VALUES ('A',0),('B',0),('O',0),('AB',0);
--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `event_donor`
--
ALTER TABLE `event_donor`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `event_sosialisasi`
--
ALTER TABLE `event_sosialisasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kritik_saran`
--
ALTER TABLE `kritik_saran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `pendonor`
--
ALTER TABLE `pendonor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `permintaan_darah`
--
ALTER TABLE `permintaan_darah`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pasien_id` (`pasien_id`);

--
-- Indeks untuk tabel `relawan`
--
ALTER TABLE `relawan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `respon_donor`
--
ALTER TABLE `respon_donor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permintaan_id` (`permintaan_id`),
  ADD KEY `pendonor_id` (`pendonor_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `stok_darah`
--
ALTER TABLE `stok_darah`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `goldar` (`goldar`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `event_donor`
--
ALTER TABLE `event_donor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `event_sosialisasi`
--
ALTER TABLE `event_sosialisasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kritik_saran`
--
ALTER TABLE `kritik_saran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pasien`
--
ALTER TABLE `pasien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pendonor`
--
ALTER TABLE `pendonor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `permintaan_darah`
--
ALTER TABLE `permintaan_darah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `relawan`
--
ALTER TABLE `relawan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `respon_donor`
--
ALTER TABLE `respon_donor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stok_darah`
--
ALTER TABLE `stok_darah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `permintaan_darah`
--
ALTER TABLE `permintaan_darah`
  ADD CONSTRAINT `permintaan_darah_ibfk_1` FOREIGN KEY (`pasien_id`) REFERENCES `pasien` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `respon_donor`
--
ALTER TABLE `respon_donor`
  ADD CONSTRAINT `respon_donor_ibfk_1` FOREIGN KEY (`permintaan_id`) REFERENCES `permintaan_darah` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `respon_donor_ibfk_2` FOREIGN KEY (`pendonor_id`) REFERENCES `pendonor` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
