<?php
include '../../config/koneksi.php';
$halaman_aktif = 'donor';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Layanan</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <script src="script.js" defer></script>
</head>
<body>

<?php include '../../components/header.php'; ?>

<main class="wadah konten-halaman">

    <section id="stok-darah" class="blok-konten">
        <h2>Ketersediaan Stok Darah</h2>
        <table class="tabel-data">
            <thead>
                <tr>
                    <th>Golongan Darah</th>
                    <th>Jumlah Kantong</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>A</td><td>12</td><td>Tersedia</td></tr>
                <tr><td>B</td><td>3</td><td>Kritis</td></tr>
                <tr><td>AB</td><td>35</td><td>Tersedia</td></tr>
                <tr><td>O</td><td>5</td><td>Kritis</td></tr>
            </tbody>
        </table>
    </section>

    <section id="daftar-relawan" class="blok-konten">
        <h3>📋 Pendaftaran Relawan Donor Darah</h3>
        <p style="color:#666; margin-bottom:20px;">
            Isi formulir di bawah ini dengan data yang benar dan lengkap.
        </p>

        <form action="simpan_relawan.php" class="form-pendaftaran" method="post">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0 20px;">

                <div class="grup-form">
                    <label for="full-name">Nama Lengkap *</label>
                    <input type="text" id="full-name" name="nama"
                           placeholder="Masukkan Nama Lengkap" required>
                </div>

                <div class="grup-form">
                    <label for="no-hp">No. HP / WhatsApp *</label>
                    <input type="text" id="no-hp" name="no_hp"
                           placeholder="08xxxxxxxxxx" required>
                </div>

                <div class="grup-form">
                    <label for="user-email">Email Aktif *</label>
                    <input type="email" id="user-email" name="email"
                           placeholder="nama@email.com" required>
                </div>

                <div class="grup-form">
                    <label for="birth-date">Tanggal Lahir *</label>
                    <input type="date" id="birth-date" name="tgl" required>
                </div>

                <div class="grup-form">
                    <label for="jenis-kelamin">Jenis Kelamin *</label>
                    <select id="jenis-kelamin" name="jenis_kelamin" required>
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>

                <div class="grup-form">
                    <label for="blood-type">Golongan Darah *</label>
                    <select id="blood-type" name="goldar" required>
                        <option value="">-- Pilih --</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="O">O</option>
                        <option value="AB">AB</option>
                    </select>
                </div>

                <div class="grup-form">
                    <label for="berat-badan">Berat Badan (kg) *</label>
                    <input type="number" id="berat-badan" name="berat_badan"
                           placeholder="Contoh: 60" min="30" max="200" required>
                </div>

                <div class="grup-form">
                    <label for="pekerjaan">Pekerjaan</label>
                    <input type="text" id="pekerjaan" name="pekerjaan"
                           placeholder="Mahasiswa / Karyawan / dll">
                </div>

                <div class="grup-form">
                    <label for="kota">Kota / Kabupaten *</label>
                    <input type="text" id="kota" name="kota"
                           placeholder="Contoh: Mataram" required>
                </div>

                <div class="grup-form">
                    <label for="pernah-donor">Pernah Donor Sebelumnya? *</label>
                    <select id="pernah-donor" name="pernah_donor" required>
                        <option value="">-- Pilih --</option>
                        <option value="tidak">Belum Pernah</option>
                        <option value="ya">Sudah Pernah</option>
                    </select>
                </div>

            </div>

            <!-- Field tanggal donor terakhir (muncul jika pilih "Sudah Pernah") -->
            <div class="grup-form" id="grup-terakhir-donor" style="display:none;">
                <label for="terakhir-donor">Tanggal Donor Terakhir</label>
                <input type="date" id="terakhir-donor" name="terakhir_donor">
            </div>

            <div class="grup-form">
                <label for="alamat">Alamat Lengkap</label>
                <textarea id="alamat" name="alamat" rows="2"
                    placeholder="Jl. ... No. ... Kel/Desa ..."
                    style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;
                           font-family:inherit; resize:vertical; box-sizing:border-box;"></textarea>
            </div>

            <div style="background:#fff3f3; border-left:4px solid #8b0000; padding:12px 15px;
                        border-radius:0 8px 8px 0; margin-bottom:15px; font-size:0.88rem; color:#555;">
                ⚠️ Dengan mendaftar, Anda menyatakan bahwa data yang diberikan benar
                dan bersedia dihubungi oleh tim DonorIn.
            </div>

            <button type="submit" name="kirim_relawan" class="tombol-kirim"
                    style="padding:12px 30px; font-size:1rem;">
                ✅ DAFTAR SEBAGAI RELAWAN
            </button>

        </form>
    </section>

</main>

<?php include '../../components/footer.php'; ?>
<?php $conn = null; ?>

<script>
// Tampilkan/sembunyikan field tanggal donor terakhir
document.getElementById('pernah-donor').addEventListener('change', function () {
    const grup = document.getElementById('grup-terakhir-donor');
    grup.style.display = (this.value === 'ya') ? 'block' : 'none';
});
</script>

</body>
</html>