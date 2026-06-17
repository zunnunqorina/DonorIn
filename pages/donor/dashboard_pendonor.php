<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['pendonor_login']) || $_SESSION['pendonor_login'] !== true) {
    header("Location: ../../login.php");
    exit;
}

$pendonor_id = $_SESSION['pendonor_id'];

$q_pendonor = $conn->prepare("SELECT * FROM pendonor WHERE id = ?");
$q_pendonor->execute([$pendonor_id]);
$pendonor   = $q_pendonor->fetch(PDO::FETCH_ASSOC);

$st1 = $conn->prepare("SELECT COUNT(*) FROM permintaan_darah WHERE status IN ('menunggu','diproses') AND goldar = ?");
$st1->execute([$pendonor['goldar']]);
$jml_permintaan_aktif = $st1->fetchColumn();

$st2 = $conn->prepare("SELECT COUNT(*) FROM respon_donor WHERE pendonor_id = ?");
$st2->execute([$pendonor_id]);
$jml_respon = $st2->fetchColumn();

$st3 = $conn->prepare("SELECT COUNT(*) FROM notifikasi WHERE tujuan_tipe='pendonor' AND tujuan_id=? AND sudah_baca=0");
$st3->execute([$pendonor_id]);
$jml_notif_belum = $st3->fetchColumn();

$q_permintaan = $conn->prepare(
    "SELECT pd.*, p.nama AS nama_pasien, p.no_hp AS hp_pasien
     FROM permintaan_darah pd
     JOIN pasien p ON pd.pasien_id = p.id
     WHERE pd.goldar = ? AND pd.status IN ('menunggu','diproses')
     ORDER BY pd.tanggal DESC LIMIT 5");
$q_permintaan->execute([$pendonor['goldar']]);
$permintaan_rows = $q_permintaan->fetchAll(PDO::FETCH_ASSOC);

$q_notif = $conn->prepare(
    "SELECT * FROM notifikasi WHERE tujuan_tipe='pendonor' AND tujuan_id=? ORDER BY tanggal DESC LIMIT 5");
$q_notif->execute([$pendonor_id]);
$notif_rows = $q_notif->fetchAll(PDO::FETCH_ASSOC);

$halaman_aktif = 'dashboard_pendonor';
$admin_username = $pendonor['nama'];
$total_pendonor = 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pendonor — DonorIn</title>
    <link rel="stylesheet" href="../../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- ══════════════ SIDEBAR ══════════════ -->
<?php include '../../components/sidebar_pendonor.php'; ?>

<!-- ══════════════ MAIN ══════════════ -->
<main class="main">

    <!-- TOPBAR -->
    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <div class="topbar-title">Dashboard</div>
                <div class="topbar-breadcrumb">DonorIn / <span>Beranda</span></div>
            </div>
        </div>
        <div class="topbar-right">
            <div class="date-chip">
                <i class="fas fa-calendar-day"></i>
                <?= date('d M Y') ?>
            </div>
        </div>
    </header>

    <!-- CONTENT -->
    <div class="content">

        <!-- WELCOME -->
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>Selamat Datang, <?= htmlspecialchars($admin_username) ?>! 👋</h2>
                <p>Gunakan portal ini untuk mencari dan merespon permintaan donor darah.</p>
            </div>
            <div class="welcome-icon">🩸</div>
        </div>

        <!-- STAT ROW -->
        <div class="stats-grid">
            <a href="cari_permintaan.php" class="stat-card" style="text-decoration: none; color: inherit;">
                <div class="stat-header">
                    <span class="stat-label">Permintaan Aktif</span>
                    <div class="stat-icon merah"><i class="fas fa-tint"></i></div>
                </div>
                <div class="stat-value"><?= $jml_permintaan_aktif ?></div>
                <div class="stat-footer">
                    <i class="fas fa-circle-dot" style="color:var(--merah);font-size:8px;"></i>
                    Golongan darah: <strong><?= $pendonor['goldar'] ?></strong>
                </div>
            </a>
            <a href="riwayat_responpendonor.php" class="stat-card" style="text-decoration: none; color: inherit;">
                <div class="stat-header">
                    <span class="stat-label">Respon Dikirim</span>
                    <div class="stat-icon hijau"><i class="fas fa-check-circle"></i></div>
                </div>
                <div class="stat-value"><?= $jml_respon ?></div>
                <div class="stat-footer">
                    <i class="fas fa-circle-dot" style="color:#1B8A4E;font-size:8px;"></i>
                    Total riwayat respon
                </div>
            </a>
            <a href="notifikasi_pendonor.php" class="stat-card" style="text-decoration: none; color: inherit;">
                <div class="stat-header">
                    <span class="stat-label">Notifikasi</span>
                    <div class="stat-icon kuning"><i class="fas fa-bell"></i></div>
                </div>
                <div class="stat-value"><?= $jml_notif_belum ?></div>
                <div class="stat-footer">
                    <i class="fas fa-circle-dot" style="color:#D4900A;font-size:8px;"></i>
                    Belum dibaca
                </div>
            </a>
        </div>

        <!-- PERMINTAAN DARAH -->
        <div class="section-header">
            <div class="section-title"><i class="fas fa-tint"></i> Permintaan Darah Golongan <?= $pendonor['goldar'] ?></div>
            <a href="cari_permintaan.php" class="btn-lihat"><i class="fas fa-arrow-right"></i> Lihat Semua</a>
        </div>
        <div class="card">
            <div class="card-body">
                <?php if (count($permintaan_rows) == 0): ?>
                <div class="empty-state">
                    <i class="fas fa-tint"></i>
                    <p>Tidak ada permintaan aktif untuk golongan darah Anda saat ini.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($permintaan_rows as $pm):
                        $tgl_pm = date('d M Y, H:i', strtotime($pm['tanggal']));
                        $status_class = 'status-' . $pm['status'];
                    ?>
                    <div class="ks-item" style="margin-bottom: 15px;">
                        <div class="ks-header">
                            <span class="ks-nama"><?= htmlspecialchars($pm['nama_rs'] ?? $pm['nama_pasien']) ?></span>
                            <span class="badge badge-merah"><?= $pm['goldar'] ?></span>
                        </div>
                        <p style="margin: 8px 0; font-size: 0.9rem;">
                            👤 Pasien: <strong><?= htmlspecialchars($pm['nama_pasien']) ?></strong><br>
                            🩸 Jumlah: <strong><?= $pm['jumlah_kantong'] ?> kantong</strong><br>
                            📍 <?= htmlspecialchars($pm['kota'] ?? '-') ?><br>
                            🕐 <?= $tgl_pm ?>
                        </p>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <a href="respon_permintaan.php?id=<?= $pm['id'] ?>"
                               style="background:var(--merah); color:white; padding:6px 12px; border-radius:4px; text-decoration:none; font-size:0.8rem; font-weight:bold;">
                               Saya Bersedia
                            </a>
                            <a href="tel:<?= $pm['hp_pasien'] ?>"
                               style="background:#27ae60; color:white; padding:6px 12px; border-radius:4px; text-decoration:none; font-size:0.8rem; font-weight:bold;">
                               📞 Hubungi
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- NOTIFIKASI -->
        <div class="section-header">
            <div class="section-title"><i class="fas fa-bell"></i> Notifikasi Terbaru</div>
            <a href="notifikasi_pendonor.php" class="btn-lihat"><i class="fas fa-arrow-right"></i> Semua</a>
        </div>
        <div class="card">
            <div class="card-body">
                <?php if (count($notif_rows) == 0): ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <p>Belum ada notifikasi.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($notif_rows as $notif):
                        $kelas_notif = $notif['sudah_baca'] ? 'sudah-baca' : 'belum-baca';
                        $tgl_notif = date('d M Y', strtotime($notif['tanggal']));
                    ?>
                    <div class="kartu-notif <?= $kelas_notif ?>" style="margin-bottom: 10px;">
                        <div class="ikon-notif">🔔</div>
                        <div class="isi-notif">
                            <h5><?= htmlspecialchars($notif['judul']) ?></h5>
                            <p><?= htmlspecialchars($notif['pesan']) ?></p>
                            <div class="waktu-notif"><?= $tgl_notif ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

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
</body>
</html>
<?php $conn = null; ?>