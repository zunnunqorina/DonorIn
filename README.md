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
| Zunnun Qorina | F1D02410030 | Fullstack Developer | Frontend: Membangun seluruh halaman untuk aktor pendonor menggunakan HTML5, CSS3 custom (styles.css), dan JavaScript — Backend: Logika PHP Native untuk pendaftaran pendonor, pengiriman permintaan darah, dan form kritik & saran menggunakan PDO Prepared Statement — UI/UX: Desain sistem visual (palet warna merah darah, tipografi Inter, layout responsif) — Koordinasi: Manajemen tim, analisis kebutuhan sistem, dan dokumentasi proyek|
| Lalu Tirta Putra Tandela | F1D02410119 | Fullstack Developer | Frontend: Membangun seluruh halaman publik dan PMI menggunakan HTML5, CSS3 custom (styles.css), dan JavaScript vanilla — Backend: Logika PHP Native untuk pendaftaran pendonor, pengiriman permintaan darah, dan form kritik & saran menggunakan PDO Prepared Statement — UI/UX: Desain sistem visual (palet warna merah darah, tipografi Inter, layout responsif) — Koordinasi: Manajemen tim, analisis kebutuhan sistem, dan dokumentasi proyek |
| Imas Nazalia Rahmawati | F1D02410055 | Fullstack Developer | Frontend: Membangun seluruh halaman admin menggunakan HTML5, CSS3 custom (styles.css), dan JavaScript vanilla — Backend: Logika PHP Native untuk pendaftaran pendonor, pengiriman permintaan darah, dan form kritik & saran menggunakan PDO Prepared Statement — UI/UX: Desain sistem visual (palet warna merah darah, tipografi Inter, layout responsif) — Koordinasi: Manajemen tim, analisis kebutuhan sistem, dan dokumentasi proyek |

---

## 👤 Website Users / Actors

### 1. Admin
| Fitur |
|-------|
| - Login |
| - Dashboard Admin|
| - Kelola Pasien (Lihat, Tambah, Ubah, Hapus, Cari) |
| - Kelola Pendonor (Lihat, Tambah, Ubah, Hapus, Cari) |
| - Kelola Relawan PMI (Lihat, Tambah, Ubah, Hapus, Cari) |
| - Kelola Event Donor Darah (Lihat, Tambah, Ubah, Hapus, Cari) |
| - Kelola Event Sosialisasi (Lihat, Tambah, Ubah, Hapus, Cari) |
| - Kelola Permintaan Darah (Lihat, Ubah, Hapus, Cari) |
| - Kelola Kritik & Saran |
| - Logout |

### 2. Pendonor
| Fitur |
|-------|
| - Login |
| - Dashboard Pendonor|
| - Cari Permintaan |
| - Riwayat Respon |
| - Cari Pendonor |
| - Ajukan Permintaan Darah |
| - Stok Darah |
| - Profil Saya|
| - Notiifikasi |
| - Logout |

### 3. Pasien / Umum
| Fitur |
|-------|
| - Dashboard |
| - Layanan (Cari Pendonor, Ajukan Permintaan, Cek Stok Darah, Daftar Pendonor, Edukasi Donor, dan Kritik Saran) |
| - Event (Event Donor Darah dan Sosialisasi Donor Darah ) |
| - Manfaat Donor Darah |
| - Syarat Donor Darah |
| - Ajukan Form Permintaan Darah |
| - Lihat Ketersediaan Stok Darah |
| - Cari & Lihat Info Pendonor Aktif |
| - Hubungi Pendonor Secara Langsung |
| - Pantau Status Permintaan |

### 4. UDD PMI
| Fitur |
|-------|
| - Login |
| - Dashboard PMI |
| - Kelola Stok Darah (Lihat, Tambah, Ubah) |
| - Kelola Permintaan Darah Aktif (Lihat, Tambah, Ubah) |
| - Pantau Data Pendonor |
| - Koordinasi dengan Pendonor Aktif |

---

## 🗂️ Sitemap / Menu Structure

```
DonorIn
├── assets
|   ├── admin.css
|   ├── admin.js
|   ├── donor.css
|   ├── script.js
|   └── stylee.css
├── auth
|   ├── logout_admin.php
|   ├── logout_pendonor.php
|   └── logout_pmi.php
├── components
|   ├── footer.php
|   ├── header.php
|   ├── sidebar_admin.php
|   ├── sidebar_pendonor.php
|   └── sidebar_pmi.php
├── config
|   ├── donorin_sql
|   └── koneksi.php
├── pages
|   ├── admin
|   |   ├── daftar_pendonor.php
|   |   ├── dashboard_admin.php
|   |   ├── event_donor.php
|   |   ├── event_sosialisasi.php
|   |   ├── kritik_saran_admin.php
|   |   ├── pasien_admin.php
|   |   ├── pendonor_admin.php
|   |   ├── permintaan_darah_admin.php
|   |   ├── relawan_admin.php
|   |   └── tampil_kritik.php
|   ├── donor
|   |   ├── ajukan_permintaan.php
|   |   ├── cari_pendonor.php
|   |   ├── cari_permintaan.php
|   |   ├── dashboard_pendonor.php
|   |   ├── edukasi_donor.php
|   |   ├── kritik_saran.php
|   |   ├── notifikasi_pendonor.php
|   |   ├── page2.php
|   |   ├── profile_pendonor.php
|   |   ├── respon_permintaan.php
|   |   ├── riwayat_responpendonor.php
|   |   ├── simpan_kritik.php
|   |   ├── simpan_relawan.php
|   |   └── stok_darah.php
|   └── pmi
|   |   ├── dashboard_pmi.php
|   |   └── respon_permintaan.php
├── index.php
└── login.php
```

---

## 🛠️ Tech Stack

**Frontend**
- HTML5
- CSS
- JavaScript

**Backend**
- Multilevel user + session
- CRUD + MySQL (PDO prepared statement)
- Validasi input server-side
- keamanan (hash)


**Database**
- MySQL

**Development Tools**
- Visual Studio Code
- XAMPP
- GitHub

---

## 🗄️ DBMS Configuration

| Konfigurasi | Detail |
|-------------|--------|
| DBMS Used | MySQL |
| Database Name | `donorin` |
| Default Port | `3306` |

**Database Connection Example**
```php
<?php
session_start();

$dbServer = "localhost";
$dbUser   = "root";
$dbPass   = "";
$dbName   = "donorin";

try {
    $conn = new PDO(
        "mysql:host=$dbServer;dbname=$dbName;charset=utf8",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("<p style='color:red;font-family:sans-serif;'>
        Koneksi gagal: " . $e->getMessage() . "
    </p>");
}
?>
```

---

## 📋 Table Specifications

### 1. admin
| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| id | INT(11) | PK, AUTO_INCREMENT | ID Admin |
| username | VARCHAR(50) | NOT NULL | Username login admin |
| password | VARCHAR(255) | NOT NULL | Password admin (hashed) |

---

### 2. pendonor
| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| id | INT(11) | PK, AUTO_INCREMENT | ID Pendonor |
| nama | VARCHAR(100) | NOT NULL | Nama lengkap pendonor |
| email | VARCHAR(100) | NOT NULL, UNIQUE | Alamat email (login) |
| password | VARCHAR(255) | NOT NULL | Password akun (hashed) |
| no_hp | VARCHAR(20) | NOT NULL | Nomor HP / WhatsApp |
| tgl_lahir | DATE | NOT NULL | Tanggal lahir |
| umur | INT(11) | NOT NULL | Usia pendonor |
| jenis_kelamin | ENUM('L','P') | NOT NULL | Jenis kelamin |
| goldar | ENUM('A','B','O','AB') | NOT NULL | Golongan darah |
| berat_badan | INT(11) | NOT NULL | Berat badan (kg) |
| kota | VARCHAR(100) | NOT NULL | Kota / kabupaten |
| pekerjaan | VARCHAR(100) | NULL | Pekerjaan |
| alamat | TEXT | NULL | Alamat lengkap |
| pernah_donor | ENUM('ya','tidak') | DEFAULT 'tidak' | Riwayat donor sebelumnya |
| terakhir_donor | DATE | NULL | Tanggal donor terakhir |
| status_aktif | ENUM('aktif','nonaktif') | DEFAULT 'aktif' | Status keaktifan pendonor |
| foto | VARCHAR(255) | NULL | Path foto profil |
| tanggal_daftar | DATETIME | DEFAULT NOW() | Tanggal pendaftaran |

---

### 3. pasien
| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| id | INT(11) | PK, AUTO_INCREMENT | ID Pasien |
| nama | VARCHAR(100) | NOT NULL | Nama lengkap pasien |
| email | VARCHAR(100) | NOT NULL, UNIQUE | Alamat email |
| password | VARCHAR(255) | NOT NULL | Password akun (hashed) |
| no_hp | VARCHAR(20) | NOT NULL | Nomor HP / WhatsApp |
| goldar_dibutuhkan | ENUM('A','B','O','AB') | NOT NULL | Golongan darah yang dibutuhkan |
| kota | VARCHAR(100) | NOT NULL | Kota / kabupaten |
| alamat | TEXT | NULL | Alamat lengkap |
| nama_rs | VARCHAR(150) | NULL | Nama rumah sakit |
| tanggal_daftar | DATETIME | DEFAULT NOW() | Tanggal pendaftaran |

---

### 4. permintaan_darah
| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| id | INT(11) | PK, AUTO_INCREMENT | ID Permintaan |
| pasien_id | INT(11) | NOT NULL, FK → pasien.id | Referensi pasien pengaju |
| goldar | ENUM('A','B','O','AB') | NOT NULL | Golongan darah yang diminta |
| jumlah_kantong | INT(11) | NOT NULL, DEFAULT 1 | Jumlah kantong darah dibutuhkan |
| nama_rs | VARCHAR(150) | NOT NULL | Nama rumah sakit tujuan |
| kota | VARCHAR(100) | NOT NULL | Kota lokasi rumah sakit |
| alamat_rs | TEXT | NULL | Alamat lengkap rumah sakit |
| keterangan | TEXT | NULL | Keterangan tambahan |
| status | ENUM('menunggu','diproses','terpenuhi','dibatalkan') | DEFAULT 'menunggu' | Status permintaan |
| tanggal | DATETIME | DEFAULT NOW() | Tanggal pengajuan |

---

### 5. respon_donor
| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| id | INT(11) | PK, AUTO_INCREMENT | ID Respon |
| permintaan_id | INT(11) | NOT NULL, FK → permintaan_darah.id | Referensi permintaan darah |
| pendonor_id | INT(11) | NOT NULL, FK → pendonor.id | Referensi pendonor yang merespon |
| pesan | TEXT | NULL | Pesan dari pendonor |
| status | ENUM('bersedia','tidak_bisa') | DEFAULT 'bersedia' | Status kesediaan pendonor |
| tanggal | DATETIME | DEFAULT NOW() | Tanggal respon |

---

### 6. notifikasi
| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| id | INT(11) | PK, AUTO_INCREMENT | ID Notifikasi |
| tujuan_tipe | ENUM('pendonor','pasien') | NOT NULL | Tipe penerima notifikasi |
| tujuan_id | INT(11) | NOT NULL | ID penerima (pendonor/pasien) |
| judul | VARCHAR(200) | NOT NULL | Judul notifikasi |
| pesan | TEXT | NOT NULL | Isi pesan notifikasi |
| sudah_baca | TINYINT(1) | DEFAULT 0 | Status baca (0=belum, 1=sudah) |
| tanggal | DATETIME | DEFAULT NOW() | Tanggal notifikasi dikirim |

---

### 7. stok_darah
| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| id | INT(11) | PK, AUTO_INCREMENT | ID Stok |
| goldar | ENUM('A','B','O','AB') | NOT NULL, UNIQUE | Golongan darah |
| jumlah | INT(11) | NOT NULL, DEFAULT 0 | Jumlah kantong tersedia |
| status | ENUM('Tersedia','Kritis','Habis') | DEFAULT 'Tersedia' | Status ketersediaan stok |
| updated_at | DATETIME | DEFAULT NOW() ON UPDATE | Waktu pembaruan terakhir |

---

### 8. event_donor
| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| id | INT(11) | PK, AUTO_INCREMENT | ID Event |
| judul | VARCHAR(200) | NOT NULL | Judul kegiatan donor darah |
| deskripsi | TEXT | NULL | Deskripsi kegiatan |
| lokasi | VARCHAR(200) | NOT NULL | Nama tempat kegiatan |
| alamat | TEXT | NULL | Alamat lengkap lokasi |
| kota | VARCHAR(100) | NOT NULL | Kota pelaksanaan |
| tanggal | DATE | NOT NULL | Tanggal pelaksanaan |
| jam_mulai | TIME | NOT NULL | Jam mulai kegiatan |
| jam_selesai | TIME | NOT NULL | Jam selesai kegiatan |
| kuota | INT(11) | DEFAULT 0 | Kuota peserta |
| penyelenggara | VARCHAR(150) | NULL | Nama penyelenggara |
| kontak | VARCHAR(100) | NULL | Kontak panitia |
| status | ENUM('aktif','selesai','batal') | DEFAULT 'aktif' | Status event |
| dibuat_pada | DATETIME | DEFAULT NOW() | Tanggal dibuat |

---

### 9. event_sosialisasi
| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| id | INT(11) | PK, AUTO_INCREMENT | ID Event Sosialisasi |
| judul | VARCHAR(200) | NOT NULL | Judul kegiatan sosialisasi |
| deskripsi | TEXT | NULL | Deskripsi kegiatan |
| lokasi | VARCHAR(200) | NOT NULL | Nama tempat kegiatan |
| alamat | TEXT | NULL | Alamat lengkap lokasi |
| kota | VARCHAR(100) | NOT NULL | Kota pelaksanaan |
| tanggal | DATE | NOT NULL | Tanggal pelaksanaan |
| jam_mulai | TIME | NOT NULL | Jam mulai kegiatan |
| jam_selesai | TIME | NOT NULL | Jam selesai kegiatan |
| pembicara | VARCHAR(150) | NULL | Nama pembicara / narasumber |
| target_peserta | INT(11) | DEFAULT 0 | Jumlah target peserta |
| kontak | VARCHAR(100) | NULL | Kontak panitia |
| status | ENUM('aktif','selesai','batal') | DEFAULT 'aktif' | Status event |
| dibuat_pada | DATETIME | DEFAULT NOW() | Tanggal dibuat |

---

### 10. kritik_saran
| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| id | INT(11) | PK, AUTO_INCREMENT | ID Kritik/Saran |
| nama | VARCHAR(100) | NOT NULL | Nama pengirim |
| email | VARCHAR(100) | NOT NULL | Email pengirim |
| kategori | ENUM('kritik','saran','pertanyaan') | NOT NULL | Kategori pesan |
| pesan | TEXT | NOT NULL | Isi kritik / saran |
| tanggal | DATETIME | DEFAULT NOW() | Tanggal dikirim |
| sudah_baca | TINYINT(1) | DEFAULT 0 | Status baca admin (0=belum, 1=sudah) |
| balasan | TEXT | NULL | Balasan dari admin |
| tgl_balas | DATETIME | NULL | Tanggal balasan dikirim |

---

### 11. relawan
| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| id | INT(11) | PK, AUTO_INCREMENT | ID Relawan |
| nama | VARCHAR(100) | NOT NULL | Nama lengkap relawan |
| email | VARCHAR(100) | NOT NULL | Alamat email |
| tgl_lahir | DATE | NOT NULL | Tanggal lahir |
| goldar | ENUM('A','B','O','AB') | NOT NULL | Golongan darah |
| tanggal_daftar | DATETIME | DEFAULT NOW() | Tanggal pendaftaran |

---

### 12. user
| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| id | INT(11) | PK, AUTO_INCREMENT | ID User |
| nama | VARCHAR(100) | NOT NULL | Nama lengkap |
| email | VARCHAR(100) | NOT NULL, UNIQUE | Alamat email (login) |
| password | VARCHAR(255) | NOT NULL | Password akun (hashed) |
| role | ENUM('pasien','pendonor') | NOT NULL, DEFAULT 'pendonor' | Peran pengguna |
| no_hp | VARCHAR(20) | NULL | Nomor HP / WhatsApp |
| goldar | ENUM('A','B','O','AB') | NULL | Golongan darah |
| kota | VARCHAR(100) | NULL | Kota / kabupaten |
| tanggal_daftar | DATETIME | DEFAULT NOW() | Tanggal pendaftaran |

---

## 🚀 Main Features

| Fitur | Deskripsi | Aktor |
|-------|-----------|-------|
| 🔍 Pencarian Pendonor | Cari pendonor aktif berdasarkan golongan darah dan kota secara real-time, dilengkapi info kontak langsung | Umum / Pasien |
| 🩸 Informasi Stok Darah | Tampilkan ketersediaan stok darah per golongan (A/B/O/AB) yang dikelola oleh Admin/PMI | Semua Aktor |
| 📋 Form Permintaan Darah | Pengajuan kebutuhan darah secara online tanpa harus login; notifikasi otomatis dikirim ke pendonor yang cocok | Umum / Pasien |
| 👤 Pendaftaran Pendonor | Formulir registrasi pendonor aktif lengkap dengan data kesehatan; data tersimpan terpusat di database | Umum |
| 📅 Event Donor Darah | Informasi jadwal, lokasi, dan kuota kegiatan donor darah keliling dari PMI dan komunitas | Semua Aktor |
| 🎤 Event Sosialisasi | Informasi kegiatan edukasi dan sosialisasi donor darah beserta detail pembicara dan peserta | Semua Aktor |
| 📚 Edukasi Donor Darah | Konten informatif mengenai syarat, manfaat, prosedur, dan mitos seputar donor darah | Semua Aktor |
| 🔔 Sistem Notifikasi | Pendonor mendapat notifikasi otomatis ketika ada permintaan darah yang sesuai golongannya | Pendonor |
| ✅ Respon Permintaan | Pendonor dapat merespon permintaan darah (bersedia/tidak bisa) dengan pesan tambahan | Pendonor |
| 📊 Dashboard Admin | Kelola seluruh data: pendonor, pasien, permintaan darah, event, relawan, stok, dan kritik saran | Admin |
| 🏥 Dashboard PMI | Kelola stok darah dan pantau permintaan darah aktif yang masuk ke sistem | PMI |
| 💬 Kritik & Saran | Fitur pengiriman masukan dari pengguna; admin dapat membaca dan membalas langsung | Umum / Admin |
| 👤 Profil Pendonor | Pendonor dapat melihat dan memperbarui data profil, foto, serta riwayat aktivitas donor | Pendonor |
| 📝 Riwayat Respon | Pendonor dapat melihat histori respon yang pernah diberikan terhadap permintaan darah | Pendonor |

---

## 🔮 Future Development

| Fitur | Deskripsi |
|-------|-----------|
| 🔔 Notifikasi & Pengingat Otomatis | Kirim pengingat donor via email/SMS ketika pendonor sudah layak donor kembali (setelah 3 bulan) |
| 💬 Integrasi WhatsApp Gateway | Notifikasi permintaan darah dan konfirmasi respon pendonor langsung via WhatsApp |
| 🪪 Kartu Donor Digital | Sertifikat/kartu digital resmi bagi pendonor aktif yang dapat diunduh dan dibagikan |
| 🏆 Sistem Reward Pendonor | Poin dan badge untuk pendonor aktif sebagai bentuk apresiasi dan motivasi donor rutin |
| 🔗 Integrasi API PMI Nasional | Sinkronisasi data stok darah real-time langsung dari sistem pusat PMI Indonesia |
| 📱 Versi Aplikasi Mobile | Pengembangan aplikasi Android/iOS agar lebih mudah diakses dari perangkat mobile |
| 🗺️ Peta Sebaran Pendonor | Visualisasi lokasi pendonor aktif di peta interaktif untuk mempercepat pencarian donor terdekat |
| 🔐 Verifikasi Email Pendonor | Konfirmasi email saat registrasi untuk memastikan keabsahan akun pendonor |
| 📊 Laporan & Statistik Admin | Dashboard analitik dengan grafik tren permintaan darah, stok, dan aktivitas pendonor per periode |

---

## 📊 Project Status

🚧 **Currently Under Development**

---

## 🎯 Project Goals

DonorIn bertujuan untuk mengatasi kesulitan pencarian donor darah yang selama ini dilakukan secara manual dan tidak terstruktur. Sistem ini hadir untuk mempertemukan pencari donor dengan pendonor aktif secara langsung berdasarkan golongan darah dan lokasi, meningkatkan kesadaran masyarakat terhadap pentingnya donor darah, serta menyediakan platform terpusat bagi UDD PMI dalam mengelola stok darah dan permintaan donor secara efisien dan real-time.

---

## Bug Log

### Bug #1 — PDO Exception Tidak Tertangkap saat Email Duplikat
- **Gejala :** Ketika mengisi form `ajukan_permintaan.php` menggunakan email yang pernah dipakai sebelumnya (dengan nomor HP berbeda), halaman langsung crash tanpa menampilkan pesan error apapun.
- **Langkah reproduksi :**
  1. Buka halaman `ajukan_permintaan.php`
  2. Isi semua field dengan email yang sudah pernah terdaftar di tabel `pasien`, namun gunakan nomor HP yang berbeda
  3. Klik tombol "Ajukan Permintaan Darah"
  4. Halaman kosong / blank screen
- **Hipotesis penyebab :** Tabel `pasien` memiliki constraint `UNIQUE` pada kolom `email`. Kode hanya mengecek duplikat berdasarkan `no_hp`, sehingga jika no_hp berbeda namun email sama, query `INSERT` dieksekusi dan melempar `PDOException` yang tidak ditangkap (`try-catch` tidak ada).
- **Fix (apa yang diubah) :**
  - Query pengecekan pasien diubah dari `WHERE no_hp = ?` menjadi `WHERE no_hp = ? OR email = ?` sehingga pasien lama terdeteksi dan dilakukan `UPDATE` bukan `INSERT`
  - Seluruh blok operasi database dibungkus dengan `try-catch (PDOException $e)` agar error database ditangkap dan ditampilkan sebagai pesan error yang ramah ke pengguna
- **Bukti :** Perbaikan dilakukan pada file `pages/donor/ajukan_permintaan.php` baris 26–74

---

### Bug #2 — Session Pendonor Belum Terhubung ke Halaman
- **Gejala :** Setelah login sebagai pendonor, beberapa halaman tidak mengenali session login (tidak menampilkan nama/data pendonor, atau redirect loop ke halaman login).
- **Langkah reproduksi :**
  1. Login sebagai pendonor melalui `login.php`
  2. Buka salah satu halaman dashboard pendonor
  3. Data pendonor tidak muncul / halaman redirect kembali ke login
- **Hipotesis penyebab :** File koneksi `koneksi.php` tidak memanggil `session_start()` di awal, atau halaman yang membutuhkan session tidak meng-include `koneksi.php` terlebih dahulu sebelum mengecek `$_SESSION`.
- **Fix (apa yang diubah) :**
  - Pastikan `session_start()` dipanggil paling awal di `koneksi.php` sebelum logika apapun
  - Pastikan setiap halaman pendonor meng-`include` file `koneksi.php` di baris pertama sebelum mengakses `$_SESSION['pendonor_id']` atau `$_SESSION['pendonor_login']`
- **Bukti :** Pemeriksaan dilakukan pada file `config/koneksi.php` dan seluruh halaman di `pages/donor/`

---

## AI Usage Statement

### Penggunaan 1 — Perbaikan Bug PDO Exception & Duplicate Email
- **Tool :** Claude.AI  
- **Untuk apa:** Menganalisis penyebab crash pada halaman `ajukan_permintaan.php` ketika email duplikat diinput, dan memperbaiki penanganan error database.
- **2-3 prompt utama:**
  1. *"kenapa pada file ajukan_permintaan.php jika menggunakan email yang pernah dipakai sebelumnya untuk mengajukan dia bakal error, dan kenapa saat error dia tidak menampilkan pesan error dan tidak kembali ke halaman sebelumnya?"*
  2. *"database yang digunakan pada file donorin.sql"*
- **Bagian output AI yang dipakai:**
  - Penjelasan bahwa kolom `email` di tabel `pasien` memiliki constraint `UNIQUE` yang menyebabkan `PDOException` saat INSERT duplikat
  - Kode perbaikan query pengecekan pasien: `WHERE no_hp = ? OR email = ?`
  - Kode penambahan blok `try-catch (PDOException $e)` di sekitar operasi database

---

### Penggunaan 2 — Debugging Session Pendonor Tidak Terhubung
- **Tool :** Claude.AI
- **Untuk apa:** Mengidentifikasi mengapa session pendonor tidak terbaca di beberapa halaman setelah login berhasil.
- **2-3 prompt utama:**
  1. *"session pendonor belum terhubung, setelah login redirect ke dashboard tapi data pendonor tidak muncul"*
  2. *"cek file koneksi.php dan halaman donor apakah session_start sudah dipanggil dengan benar"*
- **Bagian output AI yang dipakai:**
  - Penjelasan urutan eksekusi PHP: `session_start()` harus dipanggil sebelum output HTML apapun
  - Rekomendasi memastikan `include 'koneksi.php'` ada di baris pertama setiap halaman yang mengakses `$_SESSION`
- **Bagian yang saya ubah + alasan:**
  - Disesuaikan dengan struktur include yang sudah ada di project — hanya memindahkan posisi `session_start()` ke paling awal file `koneksi.php`

---

> **Mata Kuliah:** Analisis dan Perancangan Berorientasi Objek  
> **Dosen Pengampu:** Royana Afwani, S.Kom., M.T.  
> **Program Studi:** Teknik Informatika — Fakultas Teknik, Universitas Mataram  
> **Tahun:** 2026

