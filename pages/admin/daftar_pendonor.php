<?php
include '../../config/koneksi.php';

if (isset($_SESSION['pendonor_login']) && $_SESSION['pendonor_login'] === true) {
    header("Location: ../donor/dashboard_pendonor.php");
    exit;
}

$pesan_status = "";

if (isset($_POST['daftar'])) {
    $nama          = trim($_POST['nama']);
    $email         = trim($_POST['email']);
    $password      = trim($_POST['password']);
    $no_hp         = trim($_POST['no_hp']);
    $tgl_lahir     = $_POST['tgl_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $goldar        = $_POST['goldar'];
    $berat_badan   = (int) $_POST['berat_badan'];
    $kota          = trim($_POST['kota']);
    $pekerjaan     = trim($_POST['pekerjaan'] ?? '');
    $alamat        = trim($_POST['alamat'] ?? '');
    $pernah_donor  = $_POST['pernah_donor'];
    $terakhir_donor = (!empty($_POST['terakhir_donor']) && $pernah_donor === 'ya')
                      ? $_POST['terakhir_donor']
                      : null;

    // Hitung umur
    $lahir = new DateTime($tgl_lahir);
    $umur  = $lahir->diff(new DateTime())->y;

    // Validasi
    if ($nama=='' || $email=='' || $password=='' || $no_hp=='' || $tgl_lahir=='' || $jenis_kelamin=='' || $goldar=='' || $berat_badan==0 || $kota=='') {
        $pesan_status = '<div class="pesan-error">❌ Semua kolom wajib harus diisi!</div>';
    } elseif ($umur < 17 || $umur > 65) {
        $pesan_status = "<div class='pesan-error'>❌ Usia harus antara 17–65 tahun. Usia Anda: $umur tahun.</div>";
    } elseif ($berat_badan < 45) {
        $pesan_status = "<div class='pesan-error'>❌ Berat badan minimal 45 kg. Berat Anda: {$berat_badan} kg.</div>";
    } elseif (strlen($password) < 6) {
        $pesan_status = '<div class="pesan-error">❌ Password minimal 6 karakter.</div>';
    } elseif (strlen($no_hp) < 10 || strlen($no_hp) > 12) {
        $pesan_status = '<div class="pesan-error">❌ Nomor HP harus antara 10-12 digit.</div>';
    } else {
        // Cek email sudah terdaftar
        $cek = $conn->prepare("SELECT id FROM pendonor WHERE email = ?");
        $cek->execute([$email]);
        if ($cek->rowCount() > 0) {
            $pesan_status = '<div class="pesan-error">❌ Email sudah terdaftar. Silakan login.</div>';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO pendonor (nama, email, password, no_hp, tgl_lahir, umur, jenis_kelamin, goldar, berat_badan, kota, pekerjaan, alamat, pernah_donor, terakhir_donor)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $hasil = $stmt->execute([$nama, $email, $hashed_password, $no_hp, $tgl_lahir, $umur, $jenis_kelamin, $goldar, $berat_badan, $kota, $pekerjaan, $alamat, $pernah_donor, $terakhir_donor]);

            if ($hasil) {
                $new_id = $conn->lastInsertId();
                // Buat notifikasi selamat datang
                $notif = $conn->prepare("INSERT INTO notifikasi (tujuan_tipe, tujuan_id, judul, pesan) VALUES ('pendonor', ?, 'Selamat Datang di DonorIn!', 'Akun pendonor Anda telah berhasil dibuat. Anda sekarang bisa melihat permintaan darah dan merespons kebutuhan pasien.')");
                $notif->execute([$new_id]);
                $pesan_status = '<div class="pesan-sukses">✅ Pendaftaran berhasil! Silakan <a href="../../login.php" style="color:#155724;font-weight:bold;">login sekarang</a>.</div>';
            } else {
                $pesan_status = '<div class="pesan-error">❌ Gagal menyimpan data. Silakan coba lagi.</div>';
            }
        }
    }
}

$page_title = 'DonorIn — Daftar Sebagai Pendonor';
include '../../components/header.php';
?>

<style>
    .signup-container {
        display: flex;
        gap: 40px;
        margin-top: 20px;
        align-items: flex-start;
    }

    .signup-visual {
        flex: 1;
        background: linear-gradient(135deg, #7a0000 0%, #8B0000 40%, #a50010 70%, #8B0000 100%);
        border-radius: 16px;
        padding: 40px;
        color: white;
        position: sticky;
        top: 100px;
        box-shadow: 0 10px 30px rgba(139,0,0,0.15);
        overflow: hidden;
    }
    
    .signup-visual::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.5;
    }

    .signup-visual-content {
        position: relative;
        z-index: 1;
    }

    .signup-visual h3 {
        font-size: 1.8rem;
        font-weight: 800;
        margin-bottom: 15px;
        line-height: 1.3;
    }

    .signup-visual p {
        font-size: 0.95rem;
        line-height: 1.7;
        opacity: 0.9;
        margin-bottom: 30px;
    }

    .benefit-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .benefit-item {
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }

    .benefit-icon {
        background: rgba(255,255,255,0.15);
        border-radius: 10px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .benefit-text strong {
        display: block;
        font-size: 0.95rem;
        margin-bottom: 4px;
    }

    .benefit-text p {
        font-size: 0.85rem;
        opacity: 0.8;
        margin: 0;
        line-height: 1.5;
    }

    .signup-form-card {
        flex: 1.3;
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        border: 1px solid var(--border);
    }

    .form-section-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--merah);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin: 25px 0 15px;
        padding-bottom: 6px;
        border-bottom: 1.5px solid var(--border);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-section-title:first-of-type {
        margin-top: 0;
    }

    .input-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px 20px;
    }

    .grup-form {
        margin-bottom: 0;
    }

    .grup-form.full-width {
        grid-column: span 2;
    }

    .grup-form label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--teks);
        margin-bottom: 6px;
    }

    .grup-form input,
    .grup-form select,
    .grup-form textarea {
        width: 100%;
        padding: 11px 14px;
        border-radius: 8px;
        border: 1.5px solid var(--border);
        font-family: inherit;
        font-size: 0.9rem;
        color: var(--teks);
        background: var(--bg);
        transition: all 0.2s;
        outline: none;
    }

    .grup-form input:focus,
    .grup-form select:focus,
    .grup-form textarea:focus {
        border-color: var(--merah);
        background: white;
        box-shadow: 0 0 0 4px rgba(139,0,0,0.1);
    }

    .disclaimer-box {
        background: var(--merah-muda);
        border-left: 4px solid var(--merah);
        padding: 16px;
        border-radius: 0 8px 8px 0;
        font-size: 0.85rem;
        color: var(--teks);
        line-height: 1.6;
        margin: 24px 0;
    }

    .tombol-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 14px;
        background: var(--merah);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
        box-shadow: 0 4px 14px rgba(139,0,0,0.2);
    }

    .tombol-submit:hover {
        background: var(--merah-gelap);
    }

    .tombol-submit:active {
        transform: scale(0.98);
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 992px) {
        .signup-container {
            flex-direction: column;
            align-items: stretch;
        }
        .signup-visual {
            position: static;
            padding: 30px;
        }
    }

    @media (max-width: 576px) {
        .signup-form-card {
            padding: 24px;
        }
        .input-grid {
            grid-template-columns: 1fr;
        }
        .grup-form.full-width {
            grid-column: span 1;
        }
    }
</style>

<main class="wadah" style="padding: 40px 24px;">
    <div class="signup-container">
        <!-- Visual Column -->
        <div class="signup-visual">
            <div class="signup-visual-content">
                <h3>Bagikan Harapan,<br>Selamatkan Nyawa</h3>
                <p>Setiap 3 detik, seseorang membutuhkan transfusi darah. Dengan mendaftar sebagai pendonor aktif di DonorIn, Anda menjadi pahlawan bagi mereka yang sedang berjuang demi kesehatannya.</p>
                
                <ul class="benefit-list">
                    <li class="benefit-item">
                        <div class="benefit-icon">🩸</div>
                        <div class="benefit-text">
                            <strong>Membantu Sesama</strong>
                            <p>Satu sumbangan darah Anda dapat menyelamatkan hingga tiga nyawa sekaligus.</p>
                        </div>
                    </li>
                    <li class="benefit-item">
                        <div class="benefit-icon">📊</div>
                        <div class="benefit-text">
                            <strong>Notifikasi Permintaan Darah</strong>
                            <p>Dapatkan info real-time jika ada pasien terdekat dengan golongan darah Anda yang membutuhkan bantuan.</p>
                        </div>
                    </li>
                    <li class="benefit-item">
                        <div class="benefit-icon">🩺</div>
                        <div class="benefit-text">
                            <strong>Pantau Kesehatan</strong>
                            <p>Dapatkan pemeriksaan kesehatan gratis (tensi, HB, dll.) setiap kali Anda melakukan donor.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Form Column -->
        <div class="signup-form-card">
            <h2 style="color:var(--teks); font-weight:800; margin-bottom:5px; letter-spacing:-0.5px;">Daftar Sebagai Pendonor</h2>
            <p style="color:var(--teks-sub); font-size:0.9rem; margin-bottom:25px;">Lengkapi formulir di bawah ini untuk membuat akun baru Anda.</p>

            <?= $pesan_status ?>

            <form method="POST" action="daftar_pendonor.php">
                
                <!-- SECTION 1: INFORMASI AKUN -->
                <div class="form-section-title">
                    <i class="fas fa-user-circle"></i> Informasi Akun
                </div>
                <div class="input-grid">
                    <div class="grup-form">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="nama" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    <div class="grup-form">
                        <label>Email *</label>
                        <input type="email" name="email" placeholder="contoh@gmail.com" required>
                    </div>
                    <div class="grup-form">
                        <label>Password *</label>
                        <input type="password" name="password" placeholder="Minimal 6 karakter" required>
                    </div>
                    <div class="grup-form">
                        <label>No. HP / WhatsApp *</label>
                        <input type="text" name="no_hp" placeholder="Contoh: 08123456789" required>
                    </div>
                </div>

                <!-- SECTION 2: DATA FISIK & KESEHATAN -->
                <div class="form-section-title">
                    <i class="fas fa-heartbeat"></i> Data Fisik & Kesehatan
                </div>
                <div class="input-grid">
                    <div class="grup-form">
                        <label>Tanggal Lahir *</label>
                        <input type="date" name="tgl_lahir" required>
                    </div>
                    <div class="grup-form">
                        <label>Jenis Kelamin *</label>
                        <select name="jenis_kelamin" required>
                            <option value="">-- Pilih --</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="grup-form">
                        <label>Golongan Darah *</label>
                        <select name="goldar" required>
                            <option value="">-- Pilih --</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="O">O</option>
                            <option value="AB">AB</option>
                        </select>
                    </div>
                    <div class="grup-form">
                        <label>Berat Badan (kg) *</label>
                        <input type="number" name="berat_badan" placeholder="Min. 45 kg" min="30" max="200" required>
                    </div>
                </div>

                <!-- SECTION 3: LOKASI & RIWAYAT -->
                <div class="form-section-title">
                    <i class="fas fa-history"></i> Lokasi & Riwayat Donor
                </div>
                <div class="input-grid">
                    <div class="grup-form">
                        <label>Kota / Kabupaten *</label>
                        <input type="text" name="kota" placeholder="Contoh: Mataram" required>
                    </div>
                    <div class="grup-form">
                        <label>Pekerjaan</label>
                        <input type="text" name="pekerjaan" placeholder="Contoh: Mahasiswa / Karyawan">
                    </div>
                    <div class="grup-form">
                        <label>Pernah Donor Sebelumnya? *</label>
                        <select name="pernah_donor" id="pernah_donor" required>
                            <option value="">-- Pilih --</option>
                            <option value="tidak">Belum Pernah</option>
                            <option value="ya">Sudah Pernah</option>
                        </select>
                    </div>
                    <div class="grup-form" id="grup_terakhir" style="display:none;">
                        <label>Tanggal Donor Terakhir</label>
                        <input type="date" name="terakhir_donor">
                    </div>
                    <div class="grup-form full-width">
                        <label>Alamat Lengkap</label>
                        <textarea name="alamat" rows="2" placeholder="Tuliskan alamat tinggal Anda saat ini..."></textarea>
                    </div>
                </div>

                <div class="disclaimer-box">
                    ⚠️ <strong>Penting:</strong> Dengan mendaftar, Anda menyatakan bersedia dihubungi untuk keperluan donor darah darurat dan memenuhi syarat dasar pendonor (usia 17–65 tahun, berat badan &ge; 45 kg, serta dalam kondisi sehat).
                </div>

                <button type="submit" name="daftar" class="tombol-submit">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button>
            </form>
            
            <p style="text-align:center; margin-top:20px; color:var(--teks-sub); font-size:0.9rem;">
                Sudah memiliki akun? <a href="../../login.php" style="color:var(--merah); font-weight:700; text-decoration:none;">Login di sini</a>
            </p>
        </div>
    </div>
</main>

<script>
document.getElementById('pernah_donor').addEventListener('change', function() {
    document.getElementById('grup_terakhir').style.display = (this.value === 'ya') ? 'block' : 'none';
});
</script>

<?php
$conn = null;
include '../../components/footer.php';
?>