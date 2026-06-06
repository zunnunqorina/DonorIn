CREATE DATABASE IF NOT EXISTS donorin;
USE donorin;


CREATE TABLE IF NOT EXISTS admin (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL  
);

INSERT INTO admin (username, password) VALUES
('karin', MD5('karincantik'));

CREATE TABLE IF NOT EXISTS kritik_saran (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nama         VARCHAR(100) NOT NULL,
    email        VARCHAR(100) NOT NULL,
    kategori     ENUM('kritik','saran','pertanyaan') NOT NULL,
    pesan        TEXT         NOT NULL,
    tanggal      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    sudah_baca   TINYINT(1)   DEFAULT 0,
    balasan      TEXT         NULL,
    tgl_balas    DATETIME     NULL
);


CREATE TABLE IF NOT EXISTS relawan (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    nama           VARCHAR(100) NOT NULL,
    email          VARCHAR(100) NOT NULL,
    no_hp          VARCHAR(20)  NOT NULL,
    tgl_lahir      DATE         NOT NULL,
    umur           INT          NOT NULL,
    jenis_kelamin  ENUM('L','P') NOT NULL,
    goldar         ENUM('A','B','O','AB') NOT NULL,
    berat_badan    INT          NOT NULL,
    kota           VARCHAR(100) NOT NULL,
    pekerjaan      VARCHAR(100),
    alamat         TEXT,
    pernah_donor   ENUM('ya','tidak') NOT NULL,
    terakhir_donor DATE,
    tanggal_daftar DATETIME     DEFAULT CURRENT_TIMESTAMP
);

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
