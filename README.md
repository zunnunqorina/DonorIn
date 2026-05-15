# 🩸 DonorIn
> Platform pencarian pendonor darah berbasis web yang menghubungkan pencari donor dengan pendonor aktif secara cepat, mudah, dan terarah.

---

## 📌 Website Name
**DonorIn** *(Donor Information & Connection Platform)*

---

## 📖 Short Description
DonorIn adalah sistem informasi pencarian pendonor darah berbasis web yang dirancang untuk menjawab permasalahan nyata masyarakat dalam proses pencarian donor darah. Sistem ini berfungsi sebagai jembatan digital yang mempertemukan pencari donor dengan pendonor aktif berdasarkan golongan darah dan lokasi secara langsung, dilengkapi fitur informasi stok darah real-time, form permintaan donor, edukasi donor darah, serta dashboard pengelolaan untuk Admin dan UDD PMI.

---

## 👥 Team Members & Responsibilities

| Nama | NIM | Role | Responsibilities |
|------|-----|------|-----------------|
| Zunnun Qorina | F1D02410030 | Project Manager & Frontend Developer | Koordinasi tim, manajemen proyek, desain antarmuka website, pembuatan halaman frontend, analisis kebutuhan sistem, dokumentasi UI/UX |
| Lalu Tirta Putra Tandela | F1D02410119 | Backend Developer | Pengembangan backend sistem, autentikasi pengguna, integrasi API, koneksi database, pengelolaan logika server |
| Imas Nazalia Rahmawati | F1D02410055 | Database Designer | Mendesain database, membuat ERD, struktur tabel |

---

## 👤 Website Users / Actors

### 1. Admin
| Fitur |
|-------|
| - Login & Dashboard |
| - Manajemen Data Pengguna (Lihat, Tambah, Ubah, Hapus, Cari) |
| - Monitoring Aktivitas Sistem |
| - Kelola Kritik & Saran |
| - Laporan Sistem |

### 2. Pendonor
| Fitur |
|-------|
| - Login & Profil |
| - Pendaftaran sebagai Pendonor Aktif |
| - Lihat & Perbarui Data Profil |
| - Akses Jadwal & Lokasi Event Donor |
| - Terima Notifikasi Pengingat Donor |
| - Ajukan Permintaan Darah (jika dibutuhkan) |
| - Akses Edukasi Donor Darah |

### 3. Pasien / Pencari Donor
| Fitur |
|-------|
| - Login & Profil |
| - Ajukan Form Permintaan Darah |
| - Lihat Ketersediaan Stok Darah |
| - Cari & Lihat Info Pendonor Aktif |
| - Hubungi Pendonor Secara Langsung |
| - Pantau Status Permintaan |

### 4. UDD PMI
| Fitur |
|-------|
| - Login & Profil |
| - Kelola Data Stok Darah (Lihat, Tambah, Perbarui, Hapus) |
| - Kelola Data Permintaan Darah |
| - Pantau Data Pendonor |
| - Kelola Event Donor Darah |
| - Koordinasi dengan Pendonor Aktif |

---

## 🗂️ Sitemap / Menu Structure

```
Public
├── Beranda
├── Tentang DonorIn
├── Edukasi Donor Darah
├── Cari Pendonor
├── Event Donor Darah
├── Kritik & Saran
└── Kontak

Pendonor
├── Dashboard
├── Profil Saya
├── Daftar sebagai Pendonor
├── Status Ketersediaan
├── Permintaan Darah (jika butuh)
├── Event Donor
└── Notifikasi

Pasien / Pencari Donor
├── Dashboard
├── Profil Saya
├── Form Butuh Donor
├── Status Permintaan
├── Cari Pendonor
└── Info Stok Darah

UDD PMI
├── Dashboard
├── Kelola Stok Darah
├── Kelola Permintaan Darah
├── Data Pendonor
└── Kelola Event

Admin
├── Dashboard
├── Manajemen Pengguna
├── Monitoring Sistem
├── Kelola Kritik & Saran
└── Laporan
```

---

## 🛠️ Tech Stack

**Frontend**
- HTML5
- CSS3
- JavaScript
- Blade Template Engine

**Backend**
- PHP
- Laravel

**Database**
- MySQL

**Development Tools**
- Visual Studio Code
- Laragon / XAMPP
- GitHub

---

## 🗄️ DBMS Configuration

| Konfigurasi | Detail |
|-------------|--------|
| DBMS Used | MySQL |
| Database Name | `donorin_db` |
| Default Port | `3306` |

**Database Connection Example**
```php
<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "donorin_db"
);

if(!$conn){
    die("Connection Failed");
}

?>
```

---

## 📋 Table Specifications

### 1. users
| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | User ID |
| fullname | VARCHAR(100) | Nama Lengkap |
| email | VARCHAR(100) | Alamat Email |
| password | VARCHAR(255) | Password Akun |
| role | ENUM | admin / pendonor / pasien / pmi |

### 2. pendonor_profiles
| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Profile ID |
| user_id | INT (FK) | Referensi User |
| golongan_darah | ENUM | A / B / AB / O (± Rhesus) |
| berat_badan | FLOAT | Berat Badan (kg) |
| tanggal_lahir | DATE | Tanggal Lahir |
| kota | VARCHAR(100) | Kota / Kabupaten |
| alamat | TEXT | Alamat Lengkap |
| no_hp | VARCHAR(20) | Nomor HP / WhatsApp |
| status | ENUM | available / unavailable |
| last_donor | DATE | Tanggal Donor Terakhir |

### 3. permintaan_darah
| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Permintaan ID |
| user_id | INT (FK) | Referensi Pasien |
| golongan_darah | ENUM | Golongan Darah yang Dibutuhkan |
| jumlah_kantong | INT | Jumlah Kantong Darah |
| nama_rs | VARCHAR(150) | Nama Rumah Sakit |
| status | ENUM | pending / fulfilled / cancelled |
| created_at | TIMESTAMP | Tanggal Pengajuan |

### 4. stok_darah
| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Stok ID |
| golongan_darah | ENUM | Golongan Darah |
| jumlah_kantong | INT | Jumlah Kantong Tersedia |
| updated_at | TIMESTAMP | Terakhir Diperbarui |
| updated_by | INT (FK) | Referensi User (PMI) |

### 5. events
| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Event ID |
| judul | VARCHAR(150) | Judul Kegiatan Donor |
| lokasi | VARCHAR(200) | Lokasi Kegiatan |
| tanggal | DATE | Tanggal Pelaksanaan |
| deskripsi | TEXT | Detail Kegiatan |
| created_by | INT (FK) | Referensi User (PMI/Admin) |

### 6. feedback
| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Feedback ID |
| user_id | INT (FK) | Referensi User (nullable) |
| isi_pesan | TEXT | Isi Kritik atau Saran |
| created_at | TIMESTAMP | Tanggal Dikirim |

---

## 🚀 Main Features

- **Pencarian Pendonor** — Cari pendonor berdasarkan golongan darah dan lokasi secara langsung dan real-time
- **Informasi Stok Darah** — Tampilkan ketersediaan stok darah per golongan yang diperbarui oleh UDD PMI
- **Form Permintaan Donor** — Pengajuan kebutuhan darah secara online dengan pemantauan status terstruktur
- **Pendaftaran Pendonor Aktif** — Formulir online untuk mendaftar sebagai pendonor dengan data tersimpan terpusat
- **Event Donor Darah** — Informasi jadwal & lokasi kegiatan donor darah keliling dari PMI dan komunitas
- **Edukasi Donor Darah** — Konten informatif mengenai syarat, manfaat, dan prosedur donor darah
- **Dashboard Admin & PMI** — Pengelolaan data pengguna, stok, permintaan, dan monitoring aktivitas sistem
- **Kritik & Saran** — Fitur masukan dari pengguna untuk pengembangan sistem secara berkelanjutan

---

## 🔮 Future Development

- Notifikasi & Pengingat Donor Otomatis
- Integrasi WhatsApp Gateway
- Kartu Donor Digital
- Sistem Reward Pendonor Aktif
- Integrasi API PMI Nasional
- Versi Aplikasi Mobile
- Peta Sebaran Pendonor (Maps Integration)

---

## 📊 Project Status

🚧 **Currently Under Development**

---

## 🎯 Project Goals

DonorIn bertujuan untuk mengatasi kesulitan pencarian donor darah yang selama ini dilakukan secara manual dan tidak terstruktur. Sistem ini hadir untuk mempertemukan pencari donor dengan pendonor aktif secara langsung berdasarkan golongan darah dan lokasi, meningkatkan kesadaran masyarakat terhadap pentingnya donor darah, serta menyediakan platform terpusat bagi UDD PMI dalam mengelola stok darah dan permintaan donor secara efisien dan real-time.

---

> **Mata Kuliah:** Analisis dan Perancangan Berorientasi Objek  
> **Dosen Pengampu:** Dwi Ratnasari, S.Kom., M.T.  
> **Program Studi:** Teknik Informatika — Fakultas Teknik, Universitas Mataram  
> **Tahun:** 2026
