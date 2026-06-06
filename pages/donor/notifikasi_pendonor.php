<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['pendonor_login']) || $_SESSION['pendonor_login'] !== true) {
    header("Location: ../../auth/login_pendonor.php");
    exit;
}

$pendonor_id = $_SESSION['pendonor_id'];

// Tandai semua sudah dibaca
$upd = $conn->prepare("UPDATE notifikasi SET sudah_baca=1 WHERE tujuan_tipe='pendonor' AND tujuan_id=?");
$upd->execute([$pendonor_id]);

$q_notif = $conn->prepare(
    "SELECT * FROM notifikasi WHERE tujuan_tipe='pendonor' AND tujuan_id=? ORDER BY tanggal DESC");
$q_notif->execute([$pendonor_id]);
$notif_rows = $q_notif->fetchAll(PDO::FETCH_ASSOC);

$halaman_aktif = 'dashboard_pendonor';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Notifikasi Pendonor</title>
    <link rel="stylesheet" href="../../assets/styles.css">
</head>
<body style="background:#f4f4f4;">

<?php include '../../components/header.php'; ?>

<main class="wadah" style="padding:40px 20px; max-width:800px;">
    <h2 style="color:#8b0000; margin-bottom:5px;">🔔 Semua Notifikasi</h2>
    <p style="color:#888; margin-bottom:25px;"><a href="dashboard_pendonor.php" style="color:#8b0000;">← Dashboard</a></p>

    <?php
    $jml = count($notif_rows);
    if ($jml == 0):
    ?>
        <div class="blok-konten">
            <p class="kosong">Belum ada notifikasi.</p>
        </div>
    <?php else:
        foreach ($notif_rows as $notif):
            $tgl_notif = date('d M Y, H:i', strtotime($notif['tanggal']));
    ?>
        <div class="kartu-notif sudah-baca" style="margin-bottom:12px;">
            <div class="ikon-notif">🔔</div>
            <div class="isi-notif" style="flex:1;">
                <h5><?php echo htmlspecialchars($notif['judul']); ?></h5>
                <p><?php echo htmlspecialchars($notif['pesan']); ?></p>
                <div class="waktu-notif"><?php echo $tgl_notif; ?></div>
            </div>
        </div>
    <?php endforeach; endif; ?>
</main>

<?php include '../../components/footer.php'; ?>
<?php $conn = null; ?>
</body>
</html>