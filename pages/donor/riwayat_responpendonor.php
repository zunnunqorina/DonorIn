<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['pendonor_login']) || $_SESSION['pendonor_login'] !== true) {
    header("Location: ../../auth/login_pendonor.php");
    exit;
}

$pendonor_id = $_SESSION['pendonor_id'];

$q = mysqli_query($conn,
    "SELECT rd.*, pd.goldar, pd.nama_rs, pd.kota, pd.jumlah_kantong, pd.status AS status_pm, pd.tanggal AS tgl_pm,
            p.nama AS nama_pasien, p.no_hp AS hp_pasien
     FROM respon_donor rd
     JOIN permintaan_darah pd ON rd.permintaan_id = pd.id
     JOIN pasien p ON pd.pasien_id = p.id
     WHERE rd.pendonor_id = $pendonor_id
     ORDER BY rd.tanggal DESC");

$halaman_aktif = 'dashboard_pendonor';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Riwayat Respon</title>
    <link rel="stylesheet" href="../../assets/styles.css">
</head>
<body style="background:#f4f4f4;">

<?php include '../../components/header.php'; ?>

<main class="wadah" style="padding:40px 20px;">
    <h2 style="color:#8b0000; margin-bottom:5px;">📋 Riwayat Respon Saya</h2>
    <p style="color:#888; margin-bottom:25px;"><a href="dashboard_pendonor.php" style="color:#8b0000;">← Dashboard</a></p>

    <?php if (mysqli_num_rows($q) == 0): ?>
        <div class="blok-konten">
            <p class="kosong">Anda belum pernah merespon permintaan darah.</p>
        </div>
    <?php else: ?>
        <?php while ($r = mysqli_fetch_assoc($q)):
            $tgl_respon = date('d M Y, H:i', strtotime($r['tanggal']));
            $warna_respon = $r['status'] == 'bersedia' ? '#27ae60' : '#e74c3c';
        ?>
        <div class="kartu-permintaan">
            <div class="goldar-badge"><?php echo $r['goldar']; ?></div>
            <div class="info-permintaan">
                <h4><?php echo htmlspecialchars($r['nama_rs']); ?> — <?php echo htmlspecialchars($r['kota']); ?></h4>
                <p>
                    👤 Pasien: <strong><?php echo htmlspecialchars($r['nama_pasien']); ?></strong><br>
                    🩸 Jumlah: <?php echo $r['jumlah_kantong']; ?> kantong<br>
                    💬 Pesan Anda: <em><?php echo htmlspecialchars($r['pesan'] ?: '-'); ?></em><br>
                    🕐 Respon dikirim: <?php echo $tgl_respon; ?>
                </p>
            </div>
            <div class="aksi-permintaan">
                <span class="status-badge" style="background:<?php echo $r['status']=='bersedia' ? '#d4edda' : '#f8d7da'; ?>; color:<?php echo $r['status']=='bersedia' ? '#155724' : '#721c24'; ?>;">
                    <?php echo $r['status'] == 'bersedia' ? '✅ Bersedia' : '❌ Tidak Bisa'; ?>
                </span>
                <span class="status-badge <?php echo 'status-'.$r['status_pm']; ?>">
                    PM: <?php echo $r['status_pm']; ?>
                </span>
                <a href="tel:<?php echo $r['hp_pasien']; ?>"
                   style="background:#27ae60; color:white; padding:8px 15px; border-radius:6px; text-decoration:none; font-size:0.85rem; font-weight:bold;">
                    📞 Hubungi
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</main>

<?php include '../../components/footer.php'; ?>
<?php mysqli_close($conn); ?>
</body>
</html>