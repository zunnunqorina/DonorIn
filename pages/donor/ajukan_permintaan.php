<?php
include '../../config/koneksi.php';
// F-13: Form pengajuan permintaan darah online — untuk pasien (tidak perlu login)

$pesan_status = "";
$sukses = false;

if (isset($_POST['ajukan'])) {
    $nama_pasien    = trim($_POST['nama_pasien']);
    $no_hp          = trim($_POST['no_hp']);
    $email          = trim($_POST['email'] ?? '');
    $goldar         = $_POST['goldar'];
    $jumlah_kantong = (int)$_POST['jumlah_kantong'];
    $nama_rs        = trim($_POST['nama_rs']);
    $kota           = trim($_POST['kota']);
    $alamat_rs      = trim($_POST['alamat_rs'] ?? '');
    $keterangan     = trim($_POST['keterangan'] ?? '');
    $kebutuhan      = $_POST['kebutuhan'];

    // Validasi
    if (!$nama_pasien || !$no_hp || !$goldar || !$jumlah_kantong || !$nama_rs || !$kota) {
        $pesan_status = '<div class="pesan-error">❌ Semua kolom bertanda * wajib diisi!</div>';
    } elseif ($jumlah_kantong < 1 || $jumlah_kantong > 20) {
        $pesan_status = '<div class="pesan-error">❌ Jumlah kantong harus antara 1–20.</div>';
    } else {
        // Cek apakah pasien sudah ada berdasarkan no_hp
        $cek_pasien = $conn->prepare("SELECT id FROM pasien WHERE no_hp = ?");
        $cek_pasien->execute([$no_hp]);
        $pasien_existing = $cek_pasien->fetch(PDO::FETCH_ASSOC);

        if ($pasien_existing) {
            $pasien_id = $pasien_existing['id'];
            // Update nama dan email jika berubah
            $upd = $conn->prepare("UPDATE pasien SET nama = ?, email = ? WHERE id = ?");
            $upd->execute([$nama_pasien, $email, $pasien_id]);
        } else {
            // Insert pasien baru — password placeholder untuk pengajuan anonim
            $placeholder_pass = password_hash(uniqid('', true), PASSWORD_DEFAULT);
            $ins = $conn->prepare(
                "INSERT INTO pasien (nama, email, password, no_hp, goldar_dibutuhkan, kota) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $ins->execute([$nama_pasien, $email ?: 'noemail@donorin.id', $placeholder_pass, $no_hp, $goldar, $kota]);
            $pasien_id = (int)$conn->lastInsertId();
        }

        // Simpan permintaan darah (tanpa kolom 'kebutuhan' yg tidak ada di DB)
        $q_insert = $conn->prepare(
            "INSERT INTO permintaan_darah (pasien_id, goldar, jumlah_kantong, nama_rs, kota, alamat_rs, keterangan, status, tanggal)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'menunggu', NOW())"
        );
        $q_insert->execute([$pasien_id, $goldar, $jumlah_kantong, $nama_rs, $kota, $alamat_rs, $keterangan]);

        if ($q_insert->rowCount() > 0) {
            $id_permintaan = (int)$conn->lastInsertId();

            // Notifikasi ke pendonor yang goldarnya cocok
            $q_pendonor_cocok = $conn->prepare("SELECT id FROM pendonor WHERE goldar = ? AND status_aktif = 'aktif'");
            $q_pendonor_cocok->execute([$goldar]);
            foreach ($q_pendonor_cocok->fetchAll(PDO::FETCH_ASSOC) as $pd) {
                $notif = $conn->prepare(
                    "INSERT INTO notifikasi (tujuan_tipe, tujuan_id, judul, pesan) VALUES ('pendonor', ?, ?, ?)"
                );
                $notif->execute([
                    $pd['id'],
                    "Ada Permintaan Darah Golongan $goldar!",
                    "Pasien membutuhkan $jumlah_kantong kantong darah golongan $goldar di $nama_rs, $kota. Segera cek di halaman permintaan."
                ]);
            }

            $sukses = true;
            $pesan_status = '<div class="pesan-sukses">✅ Permintaan darah berhasil diajukan! ID Permintaan: <strong>#' . $id_permintaan . '</strong>. Pendonor yang cocok akan mendapat notifikasi.</div>';
        } else {
            $pesan_status = '<div class="pesan-error">❌ Gagal menyimpan permintaan. Silakan coba lagi.</div>';
        }
    }
}

// Ambil stok darah untuk info referensi
$q_stok = $conn->query("SELECT goldar, jumlah FROM stok_darah");
$is_logged_in  = isset($_SESSION['pendonor_login']) && $_SESSION['pendonor_login'] === true;
$halaman_aktif = 'ajukan_permintaan';

if ($is_logged_in) {
    $pendonor_id = $_SESSION['pendonor_id'];
    $q_pendonor_info = $conn->prepare("SELECT * FROM pendonor WHERE id = ?");
    $q_pendonor_info->execute([$pendonor_id]);
    $pendonor = $q_pendonor_info->fetch(PDO::FETCH_ASSOC);
    $admin_username = $pendonor['nama'];
    $pendonor_goldar = $pendonor['goldar'];

    $st3 = $conn->prepare("SELECT COUNT(*) FROM notifikasi WHERE tujuan_tipe='pendonor' AND tujuan_id=? AND sudah_baca=0");
    $st3->execute([$pendonor_id]);
    $jml_notif_belum = $st3->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Ajukan Permintaan Darah</title>
    <?php if ($is_logged_in): ?>
        <link rel="stylesheet" href="../../assets/admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php else: ?>
        <link rel="stylesheet" href="../../assets/styles.css">
    <?php endif; ?>
    <style>
        .form-wrap {
            max-width: 720px;
            margin: 0 auto;
        }

        .blok-konten {
            background: white;
            border-radius: 12px;
            padding: 28px 32px;
            box-shadow: 0 2px 12px rgba(139,0,0,0.08);
            border: 1px solid #f0e0e0;
            margin-bottom: 20px;
        }

        .seksi-judul {
            color: #8b0000;
            font-size: 1rem;
            font-weight: 700;
            border-bottom: 2px solid #8b0000;
            padding-bottom: 8px;
            margin: 0 0 20px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 20px;
        }

        .grup-form { margin-bottom: 16px; }
        .grup-form label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
        }
        .req { color: #dc3545; }
        .grup-form input,
        .grup-form select,
        .grup-form textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 7px;
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .grup-form input:focus,
        .grup-form select:focus,
        .grup-form textarea:focus { border-color: #8b0000; }

        /* Radio kebutuhan */
        .radio-kebutuhan { display: flex; gap: 12px; }
        .radio-kebutuhan label {
            flex: 1; border: 2px solid #ddd; border-radius: 8px;
            padding: 12px; text-align: center; cursor: pointer;
            font-size: 0.88rem; font-weight: 600; color: #555;
            transition: all 0.2s; margin: 0;
        }
        .radio-kebutuhan input[type=radio] { display: none; }
        .radio-kebutuhan input[type=radio]:checked + label {
            border-color: #8b0000; background: #fff3f3; color: #8b0000;
        }

        /* Info stok inline */
        .stok-hint {
            font-size: 0.78rem;
            margin-top: 5px;
            padding: 5px 10px;
            border-radius: 6px;
            display: none;
        }

        .tombol-ajukan {
            width: 100%;
            padding: 14px;
            background: #8b0000;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.2s;
        }
        .tombol-ajukan:hover { background: #6b0000; }

        /* Sukses state */
        .sukses-box {
            text-align: center;
            padding: 40px 20px;
        }
        .sukses-ikon { font-size: 4rem; margin-bottom: 12px; }
        .sukses-judul { font-size: 1.4rem; font-weight: 800; color: #8b0000; margin-bottom: 8px; }

        .pesan-error { background:#f8d7da; color:#721c24; border:1px solid #f5c2c7; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:0.9rem; }
        .pesan-sukses { background:#d1e7dd; color:#0f5132; border:1px solid #a3cfbb; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:0.9rem; }

        @media (max-width: 600px) {
            .form-grid-2 { grid-template-columns: 1fr; }
            .blok-konten { padding: 20px 16px; }
        }
    </style>
</head>
<body <?php if (!$is_logged_in) echo 'style="background:#f4f6f9;"'; ?>>

<?php if ($is_logged_in): ?>
    <?php include '../../components/sidebar_pendonor.php'; ?>
    <main class="main">
        <!-- TOPBAR -->
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="btn-toggle-sidebar" id="btnToggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <div class="topbar-title">Ajukan Permintaan</div>
                    <div class="topbar-breadcrumb">DonorIn / <span>Ajukan Permintaan Darah</span></div>
                </div>
            </div>
            <div class="topbar-right">
                <div class="date-chip">
                    <i class="fas fa-calendar-day"></i>
                    <?= date('d M Y') ?>
                </div>
            </div>
        </header>
        <div class="content">
<?php else: ?>
    <?php include '../../components/header.php'; ?>
    <main class="wadah" style="padding: 40px 20px;">
<?php endif; ?>

    <div class="form-wrap">
        <h2 style="color:#8b0000; margin-bottom:5px;">📋 Ajukan Permintaan Darah</h2>
        <p style="color:#888; margin-bottom:24px;">
            Isi formulir di bawah ini untuk mengajukan kebutuhan darah. Pendonor yang cocok akan mendapat notifikasi.
        </p>

        <?php if ($sukses): ?>
        <!-- TAMPILAN SUKSES -->
        <div class="blok-konten">
            <div class="sukses-box">
                <div class="sukses-ikon">✅</div>
                <div class="sukses-judul">Permintaan Berhasil Diajukan!</div>
                <p style="color:#555; margin-bottom:20px;">
                    Permintaan darah Anda telah kami terima. Pendonor dengan golongan darah yang cocok
                    akan mendapat notifikasi dan dapat menghubungi Anda langsung.
                </p>
                <?php echo $pesan_status; ?>
                <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:20px;">
                    <a href="stok_darah.php" style="background:#8b0000;color:white;padding:10px 24px;border-radius:8px;font-weight:700;text-decoration:none;">
                        🩸 Lihat Stok Darah
                    </a>
                    <a href="ajukan_permintaan.php" style="background:#eee;color:#333;padding:10px 24px;border-radius:8px;font-weight:700;text-decoration:none;">
                        📋 Ajukan Lagi
                    </a>
                    <a href="cari_pendonor.php" style="background:#27ae60;color:white;padding:10px 24px;border-radius:8px;font-weight:700;text-decoration:none;">
                        🔍 Cari Pendonor
                    </a>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- FORM PENGAJUAN -->
        <?php echo $pesan_status; ?>

        <form method="POST" action="ajukan_permintaan.php">

            <!-- SEKSI 1: DATA PASIEN -->
            <div class="blok-konten">
                <h3 class="seksi-judul">👤 Data Pasien / Pemohon</h3>
                <div class="form-grid-2">
                    <div class="grup-form">
                        <label>Nama Pasien <span class="req">*</span></label>
                        <input type="text" name="nama_pasien" placeholder="Nama lengkap pasien" required
                               value="<?php echo htmlspecialchars($_POST['nama_pasien'] ?? ''); ?>">
                    </div>
                    <div class="grup-form">
                        <label>No. HP / WhatsApp <span class="req">*</span></label>
                        <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" required
                               value="<?php echo htmlspecialchars($_POST['no_hp'] ?? ''); ?>">
                    </div>
                    <div class="grup-form" style="grid-column:span 2;">
                        <label>Email (untuk notifikasi)</label>
                        <input type="email" name="email" placeholder="nama@email.com"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- SEKSI 2: KEBUTUHAN DARAH -->
            <div class="blok-konten">
                <h3 class="seksi-judul">🩸 Kebutuhan Darah</h3>

                <!-- Jenis kebutuhan -->
                <div class="grup-form">
                    <label>Jenis Kebutuhan <span class="req">*</span></label>
                    <div class="radio-kebutuhan">
                        <div>
                            <input type="radio" name="kebutuhan" id="darurat" value="darurat"
                                   <?php echo ($_POST['kebutuhan']??'darurat')==='darurat' ? 'checked' : ''; ?>>
                            <label for="darurat">🚨 Darurat<br><small style="font-weight:400;">Butuh segera hari ini</small></label>
                        </div>
                        <div>
                            <input type="radio" name="kebutuhan" id="terencana" value="terencana"
                                   <?php echo ($_POST['kebutuhan']??'')==='terencana' ? 'checked' : ''; ?>>
                            <label for="terencana">📅 Terencana<br><small style="font-weight:400;">Untuk operasi/prosedur</small></label>
                        </div>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="grup-form">
                        <label>Golongan Darah <span class="req">*</span></label>
                        <select name="goldar" id="sel_goldar" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach (['A','B','O','AB'] as $g):
                                $selected = ($_POST['goldar'] ?? '') === $g ? 'selected' : '';
                            ?>
                            <option value="<?php echo $g; ?>" <?php echo $selected; ?>
                                    data-stok="<?php echo $stok_g; ?>">
                                 <?php echo $g; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="stok_hint" class="stok-hint"></div>
                    </div>
                    <div class="grup-form">
                        <label>Jumlah Kantong Dibutuhkan <span class="req">*</span></label>
                        <input type="number" name="jumlah_kantong" min="1" max="20" placeholder="Contoh: 2" required
                               value="<?php echo htmlspecialchars($_POST['jumlah_kantong'] ?? ''); ?>">
                    </div>
                </div>

                <div class="grup-form">
                    <label>Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="2"
                        placeholder="Contoh: untuk operasi caesar, jadwal Senin pagi..."
                        style="resize:vertical;"><?php echo htmlspecialchars($_POST['keterangan'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- SEKSI 3: LOKASI RS -->
            <div class="blok-konten">
                <h3 class="seksi-judul">🏥 Lokasi Rumah Sakit / Fasilitas Kesehatan</h3>
                <div class="form-grid-2">
                    <div class="grup-form">
                        <label>Nama Rumah Sakit <span class="req">*</span></label>
                        <input type="text" name="nama_rs" placeholder="Contoh: RSUP NTB" required
                               value="<?php echo htmlspecialchars($_POST['nama_rs'] ?? ''); ?>">
                    </div>
                    <div class="grup-form">
                        <label>Kota / Kabupaten <span class="req">*</span></label>
                        <input type="text" name="kota" placeholder="Contoh: Mataram" required
                               value="<?php echo htmlspecialchars($_POST['kota'] ?? ''); ?>">
                    </div>
                    <div class="grup-form" style="grid-column:span 2;">
                        <label>Alamat Lengkap RS</label>
                        <input type="text" name="alamat_rs" placeholder="Jl. Pejanggik No. 6, Mataram"
                               value="<?php echo htmlspecialchars($_POST['alamat_rs'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- PERSETUJUAN -->
            <div style="background:#fff3f3; border-left:4px solid #8b0000; padding:12px 16px;
                        border-radius:0 8px 8px 0; margin-bottom:20px; font-size:0.85rem; color:#555;">
                ⚠️ Dengan mengajukan permintaan ini, Anda menyatakan bahwa informasi yang diberikan
                adalah benar dan bersedia dihubungi oleh pendonor atau tim DonorIn.
            </div>

            <button type="submit" name="ajukan" class="tombol-ajukan">
                📨 AJUKAN PERMINTAAN DARAH
            </button>
        </form>
        <?php endif; ?>

    </div>
<?php if ($is_logged_in): ?>
        </div>
    </main>
    <script src="../../assets/admin.js"></script>
    <script>
    document.getElementById('btnToggleSidebar').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.add('open');
    });
    document.getElementById('btnCloseSidebar').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.remove('open');
    });
    </script>
<?php else: ?>
    </main>
    <?php include '../../components/footer.php'; ?>
<?php endif; ?>
<?php $conn = null; ?>

<script>
// Tampilkan info stok saat pilih goldar
const selGoldar = document.getElementById('sel_goldar');
const stokHint  = document.getElementById('stok_hint');

if (selGoldar) {
    selGoldar.addEventListener('change', function() {
        const opt   = this.options[this.selectedIndex];
        const stok  = parseInt(opt.dataset.stok || 0);
        if (!this.value) { stokHint.style.display = 'none'; return; }

        let warna, teks;
        if (stok === 0)      { warna = '#f8d7da'; teks = '🔴 Stok habis di PMI. Akan dicarikan pendonor aktif.'; }
        else if (stok <= 5)  { warna = '#ffe0b2'; teks = `🟠 Stok kritis (${stok} kantong). Segera ajukan.`; }
        else if (stok <= 15) { warna = '#fff3cd'; teks = `🟡 Stok terbatas (${stok} kantong).`; }
        else                 { warna = '#d1e7dd'; teks = `🟢 Stok tersedia (${stok} kantong) di PMI.`; }

        stokHint.style.display  = 'block';
        stokHint.style.background = warna;
        stokHint.textContent    = teks;
    });
}
</script>
</body>
</html>
