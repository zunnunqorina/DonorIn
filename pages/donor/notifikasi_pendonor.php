<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['pendonor_login']) || $_SESSION['pendonor_login'] !== true) {
    header("Location: ../../login.php");
    exit;
}

$pendonor_id = $_SESSION['pendonor_id'];

$q_pendonor = $conn->prepare("SELECT * FROM pendonor WHERE id = ?");
$q_pendonor->execute([$pendonor_id]);
$pendonor = $q_pendonor->fetch(PDO::FETCH_ASSOC);
$admin_username = $pendonor['nama'];

// Tandai semua sudah dibaca
$upd = $conn->prepare("UPDATE notifikasi SET sudah_baca=1 WHERE tujuan_tipe='pendonor' AND tujuan_id=?");
$upd->execute([$pendonor_id]);

$q_notif = $conn->prepare(
    "SELECT * FROM notifikasi WHERE tujuan_tipe='pendonor' AND tujuan_id=? ORDER BY tanggal DESC");
$q_notif->execute([$pendonor_id]);
$notif_rows = $q_notif->fetchAll(PDO::FETCH_ASSOC);
$jml_notif_belum = 0; // sudah ditandai baca semua
$halaman_aktif = 'notifikasi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi — DonorIn</title>
    <link rel="stylesheet" href="../../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .notif-card {
            background: var(--card);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 16px 20px;
            margin-bottom: 12px;
            display: flex;
            gap: 16px;
            align-items: flex-start;
            transition: box-shadow .2s, transform .2s;
        }
        .notif-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.09); transform: translateY(-1px); }
        .notif-icon {
            width: 44px; height: 44px; border-radius: 12px;
            background: linear-gradient(135deg, var(--merah), #a01020);
            color: white; font-size: 1.1rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .notif-body { flex: 1; min-width: 0; }
        .notif-title { font-size: 0.9rem; font-weight: 700; color: var(--text); margin: 0 0 4px; }
        .notif-pesan { font-size: 0.82rem; color: var(--text-muted); margin: 0 0 6px; line-height: 1.6; }
        .notif-time  { font-size: 0.72rem; color: var(--text-muted); display: flex; align-items: center; gap: 5px; }
        .notif-time i { font-size: 0.65rem; }
    </style>
</head>
<body>

<!-- ══════════════ SIDEBAR ══════════════ -->
<?php include '../../components/sidebar_pendonor.php'; ?>

<!-- ══════════════ MAIN ══════════════ -->
<main class="main">

    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <div class="topbar-title">Notifikasi</div>
                <div class="topbar-breadcrumb">DonorIn / <span>Semua Notifikasi</span></div>
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

        <?php if (count($notif_rows) == 0): ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <p>Belum ada notifikasi untuk Anda.</p>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div style="font-size:.85rem; color:var(--text-muted); margin-bottom:14px;">
                <i class="fas fa-check-double" style="color:var(--merah);"></i>
                Semua notifikasi telah ditandai sebagai sudah dibaca.
            </div>
            <?php foreach ($notif_rows as $notif):
                $tgl_notif = date('d M Y, H:i', strtotime($notif['tanggal']));
            ?>
            <div class="notif-card">
                <div class="notif-icon"><i class="fas fa-bell"></i></div>
                <div class="notif-body">
                    <div class="notif-title"><?= htmlspecialchars($notif['judul']) ?></div>
                    <div class="notif-pesan"><?= htmlspecialchars($notif['pesan']) ?></div>
                    <div class="notif-time"><i class="fas fa-clock"></i> <?= $tgl_notif ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div><!-- /content -->
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