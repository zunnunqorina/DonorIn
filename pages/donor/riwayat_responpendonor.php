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

$st3 = $conn->prepare("SELECT COUNT(*) FROM notifikasi WHERE tujuan_tipe='pendonor' AND tujuan_id=? AND sudah_baca=0");
$st3->execute([$pendonor_id]);
$jml_notif_belum = $st3->fetchColumn();

$q = $conn->prepare(
    "SELECT rd.*, pd.goldar, pd.nama_rs, pd.kota, pd.jumlah_kantong, pd.status AS status_pm, pd.tanggal AS tgl_pm,
            p.nama AS nama_pasien, p.no_hp AS hp_pasien
     FROM respon_donor rd
     JOIN permintaan_darah pd ON rd.permintaan_id = pd.id
     JOIN pasien p ON pd.pasien_id = p.id
     WHERE rd.pendonor_id = ?
     ORDER BY rd.tanggal DESC");
$q->execute([$pendonor_id]);
$riwayat_rows = $q->fetchAll(PDO::FETCH_ASSOC);
$halaman_aktif = 'riwayat_respon';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Respon — DonorIn</title>
    <link rel="stylesheet" href="../../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .riwayat-card {
            background: var(--card);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 20px 24px;
            margin-bottom: 14px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
            transition: box-shadow .2s, transform .2s;
        }
        .riwayat-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.1); transform: translateY(-2px); }
        .goldar-badge {
            min-width: 58px; height: 58px; border-radius: 12px;
            background: linear-gradient(135deg, var(--merah), #a01020);
            color: white; font-size: 1.15rem; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .riwayat-info { flex: 1; min-width: 0; }
        .riwayat-info h4 { margin: 0 0 8px; font-size: 1rem; color: var(--text); }
        .riwayat-info p  { margin: 0; font-size: 0.85rem; color: var(--text-muted); line-height: 1.9; }
        .riwayat-actions { display: flex; flex-direction: column; gap: 8px; flex-shrink: 0; }
        .badge-respon {
            padding: 6px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 700;
            display: inline-block; text-align: center;
        }
        .badge-bersedia { background: rgba(27,138,78,.13); color: #1B8A4E; }
        .badge-tidak    { background: rgba(220,38,38,.12); color: #dc2626; }
        .badge-pm { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-menunggu { background: rgba(212,144,10,.15); color: #D4900A; }
        .badge-diproses { background: rgba(37,99,235,.13); color: #2563eb; }
        .badge-selesai  { background: rgba(27,138,78,.12); color: #1B8A4E; }
        .btn-hubungi {
            background: #1B8A4E; color: white;
            padding: 7px 14px; border-radius: 8px; text-decoration: none;
            font-size: 0.8rem; font-weight: 700; text-align: center;
            transition: opacity .2s; white-space: nowrap;
        }
        .btn-hubungi:hover { opacity: .85; }
        .summary-bar {
            background: var(--card);
            border-radius: var(--radius);
            padding: 18px 24px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
            display: flex; gap: 24px; flex-wrap: wrap;
        }
        .sum-item { text-align: center; }
        .sum-val  { font-size: 1.5rem; font-weight: 800; color: var(--text); }
        .sum-lbl  { font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; }
        @media (max-width:640px) {
            .riwayat-card { flex-direction: column; }
            .riwayat-actions { flex-direction: row; flex-wrap: wrap; }
        }
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
                <div class="topbar-title">Riwayat Respon</div>
                <div class="topbar-breadcrumb">DonorIn / <span>Riwayat Respon Saya</span></div>
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

        <?php
        $total = count($riwayat_rows);
        $bersedia = count(array_filter($riwayat_rows, fn($r) => $r['status'] === 'bersedia'));
        $tidak = $total - $bersedia;
        ?>
        <!-- Summary Bar -->
        <div class="summary-bar">
            <div class="sum-item">
                <div class="sum-val"><?= $total ?></div>
                <div class="sum-lbl">Total Respon</div>
            </div>
            <div class="sum-item" style="border-left:1px solid var(--border); padding-left:24px;">
                <div class="sum-val" style="color:#1B8A4E;"><?= $bersedia ?></div>
                <div class="sum-lbl">Bersedia</div>
            </div>
            <div class="sum-item" style="border-left:1px solid var(--border); padding-left:24px;">
                <div class="sum-val" style="color:#dc2626;"><?= $tidak ?></div>
                <div class="sum-lbl">Tidak Bisa</div>
            </div>
        </div>

        <?php if ($total == 0): ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="fas fa-file-alt"></i>
                    <p>Anda belum pernah merespon permintaan darah.</p>
                </div>
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($riwayat_rows as $r):
                $tgl_respon = date('d M Y, H:i', strtotime($r['tanggal']));
                $is_bersedia = $r['status'] === 'bersedia';
            ?>
            <div class="riwayat-card">
                <div class="goldar-badge"><?= htmlspecialchars($r['goldar']) ?></div>
                <div class="riwayat-info">
                    <h4><?= htmlspecialchars($r['nama_rs']) ?> — <?= htmlspecialchars($r['kota']) ?></h4>
                    <p>
                        <i class="fas fa-user" style="width:14px;"></i> Pasien: <strong><?= htmlspecialchars($r['nama_pasien']) ?></strong><br>
                        <i class="fas fa-tint" style="width:14px;"></i> Jumlah: <?= $r['jumlah_kantong'] ?> kantong<br>
                        <i class="fas fa-comment" style="width:14px;"></i> Pesan Anda: <em><?= htmlspecialchars($r['pesan'] ?: '-') ?></em><br>
                        <i class="fas fa-clock" style="width:14px;"></i> Respon dikirim: <?= $tgl_respon ?>
                    </p>
                    <div style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap;">
                        <span class="badge-respon <?= $is_bersedia ? 'badge-bersedia' : 'badge-tidak' ?>">
                            <?= $is_bersedia ? '✅ Bersedia' : '❌ Tidak Bisa' ?>
                        </span>
                        <span class="badge-pm badge-<?= $r['status_pm'] ?>">
                            Permintaan: <?= ucfirst($r['status_pm']) ?>
                        </span>
                    </div>
                </div>
                <div class="riwayat-actions">
                    <a href="tel:<?= $r['hp_pasien'] ?>" class="btn-hubungi">
                        <i class="fas fa-phone"></i> Hubungi
                    </a>
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