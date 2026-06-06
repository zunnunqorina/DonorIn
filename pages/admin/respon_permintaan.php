<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['pendonor_login']) || $_SESSION['pendonor_login'] !== true) {
    header("Location: ../../auth/login_pendonor.php");
    exit;
}

$pendonor_id = $_SESSION['pendonor_id'];
$pm_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$pm_id) { header("Location: cari_permintaan.php"); exit; }

// Ambil detail permintaan
$q = $conn->prepare("SELECT pd.*, p.nama AS nama_pasien, p.no_hp AS hp_pasien, p.email AS email_pasien
     FROM permintaan_darah pd JOIN pasien p ON pd.pasien_id=p.id WHERE pd.id=?");
$q->execute([$pm_id]);
if ($q->rowCount() == 0) { header("Location: cari_permintaan.php"); exit; }
$pm = $q->fetch(PDO::FETCH_ASSOC);

$pesan_status = "";

if (isset($_POST['kirim_respon'])) {
    $status_respon = $_POST['status_respon'];
    $pesan_respon  = trim($_POST['pesan'] ?? '');

    // Cek sudah respon belum
    $cek = $conn->prepare("SELECT id FROM respon_donor WHERE permintaan_id=? AND pendonor_id=?");
    $cek->execute([$pm_id, $pendonor_id]);
    if ($cek->rowCount() > 0) {
        $pesan_status = "<div class='pesan-error'>❌ Anda sudah merespon permintaan ini sebelumnya.</div>";
    } else {
        $insert = $conn->prepare("INSERT INTO respon_donor (permintaan_id, pendonor_id, pesan, status) VALUES (?, ?, ?, ?)");
        $insert->execute([$pm_id, $pendonor_id, $pesan_respon, $status_respon]);
        if ($insert->rowCount() > 0) {
            // Update status permintaan jika bersedia
            if ($status_respon == 'bersedia') {
                $upd = $conn->prepare("UPDATE permintaan_darah SET status='diproses' WHERE id=?");
                $upd->execute([$pm_id]);
            }
            // Kirim notifikasi ke pasien
            $nama_pendonor   = htmlspecialchars($_SESSION['pendonor_nama']);
            $goldar_pendonor = $_SESSION['pendonor_goldar'];
            $pesan_notif     = "Pendonor $nama_pendonor (Gol. $goldar_pendonor) menyatakan bersedia mendonorkan darah untuk permintaan Anda di {$pm['nama_rs']}. Segera hubungi mereka.";
            $notif = $conn->prepare("INSERT INTO notifikasi (tujuan_tipe, tujuan_id, judul, pesan) VALUES ('pasien', ?, 'Ada Pendonor Bersedia!', ?)");
            $notif->execute([$pm['pasien_id'], $pesan_notif]);

            $pesan_status = "<div class='pesan-sukses'>✅ Respon Anda berhasil dikirim! Pasien akan mendapat notifikasi.</div>";
        } else {
            $pesan_status = "<div class='pesan-error'>❌ Gagal menyimpan respon.</div>";
        }
    }
}

$halaman_aktif = 'cari_permintaan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Respon Permintaan</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body style="background:#f4f4f4;">

<?php include 'components/header.php'; ?>

<main class="wadah" style="padding:40px 20px; max-width:700px;">
    <h2 style="color:#8b0000; margin-bottom:5px;">🩸 Respon Permintaan Darah</h2>
    <p style="color:#888; margin-bottom:25px;"><a href="cari_permintaan.php" style="color:#8b0000;">← Kembali</a></p>

    <?php echo $pesan_status; ?>

    <!-- Detail Permintaan -->
    <div class="blok-konten" style="margin-bottom:25px; border-left:5px solid #8b0000;">
        <h3 style="margin-top:0; color:#8b0000;">Detail Permintaan</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div><strong>Rumah Sakit:</strong><br><?php echo htmlspecialchars($pm['nama_rs']); ?></div>
            <div><strong>Kota:</strong><br><?php echo htmlspecialchars($pm['kota']); ?></div>
            <div><strong>Golongan Darah:</strong><br>
                <span style="font-size:1.5rem; font-weight:bold; color:#8b0000;"><?php echo $pm['goldar']; ?></span>
            </div>
            <div><strong>Jumlah Kantong:</strong><br><?php echo $pm['jumlah_kantong']; ?> kantong</div>
            <div><strong>Nama Pasien:</strong><br><?php echo htmlspecialchars($pm['nama_pasien']); ?></div>
            <div><strong>No. HP Pasien:</strong><br>
                <a href="tel:<?php echo $pm['hp_pasien']; ?>" style="color:#27ae60; font-weight:bold;">
                    📞 <?php echo $pm['hp_pasien']; ?>
                </a>
            </div>
        </div>
        <?php if ($pm['keterangan']): ?>
        <div style="margin-top:12px; background:#fff3f3; padding:12px; border-radius:8px;">
            <strong>Keterangan:</strong> <?php echo htmlspecialchars($pm['keterangan']); ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Form Respon -->
    <div class="blok-konten">
        <h3 style="color:#8b0000; border-bottom:2px solid #8b0000; padding-bottom:8px; margin-bottom:20px;">
            💬 Kirim Respon Anda
        </h3>
        <form method="POST" action="respon_permintaan.php?id=<?php echo $pm_id; ?>">
            <div class="grup-form">
                <label>Status Respon *</label>
                <div class="radio-grup">
                    <label class="radio-item">
                        <input type="radio" name="status_respon" value="bersedia" required checked> ✅ Saya Bersedia Donor
                    </label>
                    <label class="radio-item">
                        <input type="radio" name="status_respon" value="tidak_bisa"> ❌ Tidak Bisa Saat Ini
                    </label>
                </div>
            </div>
            <div class="grup-form">
                <label>Pesan / Keterangan (Opsional)</label>
                <textarea name="pesan" rows="3" placeholder="Contoh: Saya bisa datang besok pagi ke RSUP NTB..."
                    style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc; font-family:inherit; resize:vertical; box-sizing:border-box;"></textarea>
            </div>
            <button type="submit" name="kirim_respon" class="tombol-auth tombol-auth-merah">
                📨 KIRIM RESPON
            </button>
        </form>
    </div>
</main>

<?php include 'components/footer.php'; ?>
<?php mysqli_close($conn); ?>
</body>
</html>