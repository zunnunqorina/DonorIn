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
                $pesan_status = '<div class="pesan-sukses">✅ Pendaftaran berhasil! Silakan <a href="login_pendonor.php" style="color:#155724;font-weight:bold;">login sekarang</a>.</div>';
            } else {
                $pesan_status = '<div class="pesan-error">❌ Gagal menyimpan data. Silakan coba lagi.</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Daftar Pendonor</title>
    <link rel="stylesheet" href="../../assets/styles.css">
</head>
<body style="background:#f4f6f9; padding: 40px 20px;">
<div class="wadah" style="max-width:750px;">
    <div class="blok-konten">
        <h2 style="color:#8b0000; margin-bottom:5px;">🩸 Daftar Sebagai Pendonor</h2>
        <p style="color:#666; margin-bottom:25px;">Isi data di bawah ini untuk membuat akun pendonor aktif di DonorIn.</p>

        <?php echo $pesan_status; ?>

        <form method="POST" action="daftar_pendonor.php">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0 20px;">
                <div class="grup-form">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama" placeholder="Nama lengkap" required>
                </div>
                <div class="grup-form">
                    <label>Email *</label>
                    <input type="email" name="email" placeholder="nama@gmail.com" required>
                </div>
                <div class="grup-form">
                    <label>Password *</label>
                    <input type="password" name="password" placeholder="Min. 6 karakter" required>
                </div>
                <div class="grup-form">
                    <label>No. HP / WhatsApp *</label>
                    <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" required>
                </div>
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
                <div class="grup-form">
                    <label>Kota / Kabupaten *</label>
                    <input type="text" name="kota" placeholder="Mataram" required>
                </div>
                <div class="grup-form">
                    <label>Pekerjaan</label>
                    <input type="text" name="pekerjaan" placeholder="Mahasiswa / Karyawan">
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
            </div>
            <div class="grup-form">
                <label>Alamat Lengkap</label>
                <textarea name="alamat" rows="2" placeholder="Jl. ... No. ..."
                    style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc; font-family:inherit; resize:vertical; box-sizing:border-box;"></textarea>
            </div>
            <div style="background:#fff3f3; border-left:4px solid #8b0000; padding:12px 15px; border-radius:0 8px 8px 0; margin-bottom:20px; font-size:0.88rem; color:#555;">
                ⚠️ Dengan mendaftar, Anda menyatakan bersedia dihubungi tim DonorIn dan memenuhi syarat donor darah (usia 17–65 tahun, berat ≥ 45 kg).
            </div>
            <button type="submit" name="daftar" class="tombol-auth tombol-auth-merah" style="font-size:1rem;">
                ✅ DAFTAR SEKARANG
            </button>
        </form>
        <p style="text-align:center; margin-top:15px; color:#888;">
            Sudah punya akun? <a href="../../auth/login_pendonor.php" style="color:#8b0000; font-weight:bold;">Login di sini</a>
        </p>
    </div>
</div>
<script>
document.getElementById('pernah_donor').addEventListener('change', function() {
    document.getElementById('grup_terakhir').style.display = (this.value === 'ya') ? 'block' : 'none';
});
</script>
</body>
</html>