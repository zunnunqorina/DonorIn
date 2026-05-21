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
    id       INT AUTO_INCREMENT PRIMARY KEY,
    nama     VARCHAR(100) NOT NULL,
    email    VARCHAR(100) NOT NULL,
    kategori ENUM('kritik','saran','pertanyaan') NOT NULL,
    pesan    TEXT         NOT NULL,
    tanggal  DATETIME     DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO kritik_saran (nama, email, kategori, pesan) VALUES
('Budi Santoso', 'budi@gmail.com',  'saran',      'Semoga stok darah selalu diperbarui secara real-time!'),
('Siti Aminah',  'siti@yahoo.com',  'kritik',     'Butuh fitur notifikasi ketika darah kritis.'),
('Ahmad Rifai',  'ahmad@gmail.com', 'pertanyaan', 'Bagaimana cara menghubungi petugas jika butuh darah segera?');

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
