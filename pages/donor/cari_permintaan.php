<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['pendonor_login']) || $_SESSION['pendonor_login'] !== true) {
    header("Location: ../../login.php");
    exit;
}

$pendonor_id    = $_SESSION['pendonor_id'];
$pendonor_goldar = $_SESSION['pendonor_goldar'];

$q_pendonor = $conn->prepare("SELECT * FROM pendonor WHERE id = ?");
$q_pendonor->execute([$pendonor_id]);
$pendonor = $q_pendonor->fetch(PDO::FETCH_ASSOC);
$admin_username = $pendonor['nama'];

$st3 = $conn->prepare("SELECT COUNT(*) FROM notifikasi WHERE tujuan_tipe='pendonor' AND tujuan_id=? AND sudah_baca=0");
$st3->execute([$pendonor_id]);
$jml_notif_belum = $st3->fetchColumn();

// Filter
$filter_goldar = isset($_GET['goldar']) ? trim($_GET['goldar']) : '';
$filter_kota   = isset($_GET['kota'])   ? trim($_GET['kota'])   : '';

$where  = "WHERE pd.status IN ('menunggu','diproses')";
$params = [];
if ($filter_goldar) { $where .= " AND pd.goldar = ?";      $params[] = $filter_goldar; }
if ($filter_kota)   { $where .= " AND pd.kota LIKE ?";     $params[] = "%$filter_kota%"; }

$q_permintaan = $conn->prepare(
    "SELECT pd.*, p.nama AS nama_pasien, p.no_hp AS hp_pasien
     FROM permintaan_darah pd
     JOIN pasien p ON pd.pasien_id = p.id
     $where ORDER BY pd.tanggal DESC");
$q_permintaan->execute($params);
$permintaan_rows = $q_permintaan->fetchAll(PDO::FETCH_ASSOC);
$jumlah = count($permintaan_rows);
$halaman_aktif = 'cari_permintaan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Permintaan — DonorIn</title>
    <link rel="stylesheet" href="../../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .filter-bar {
            background: var(--card);
            border-radius: var(--radius);
            padding: 20px 24px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .filter-bar label { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: .5px; }
        .filter-bar select,
        .filter-bar input[type="text"] {
            height: 38px; border: 1px solid var(--border); border-radius: 8px;
            padding: 0 12px; background: var(--bg); color: var(--text);
            font-size: 0.875rem; width: 100%;
        }
        .filter-bar select:focus, .filter-bar input[type="text"]:focus { outline: none; border-color: var(--merah); }
        .filter-group { flex: 1; min-width: 150px; }
        .btn-filter { height: 38px; padding: 0 20px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; border: none; display: flex; align-items: center; gap: 6px; }
        .btn-filter.primary { background: var(--merah); color: white; }
        .btn-filter.reset  { background: var(--border); color: var(--text); text-decoration: none; }
        .btn-filter:hover { opacity: 0.88; }
        .info-goldar {
            background: linear-gradient(135deg, rgba(190,30,45,.08), rgba(190,30,45,.03));
            border-left: 4px solid var(--merah);
            border-radius: 0 10px 10px 0;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        .info-goldar strong { color: var(--merah); }
        .result-count { font-size: 0.875rem; color: var(--text-muted); margin-bottom: 16px; }
        .result-count strong { color: var(--text); }

        /* Kartu permintaan */
        .req-card {
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
        .req-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.1); transform: translateY(-2px); }
        .req-card.cocok { border-left: 4px solid #1B8A4E; }
        .goldar-badge {
            min-width: 60px; height: 60px; border-radius: 12px;
            background: linear-gradient(135deg, var(--merah), #a01020);
            color: white; font-size: 1.2rem; font-weight: 800;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .goldar-badge.cocok { background: linear-gradient(135deg, #1B8A4E, #145f36); }
        .goldar-badge small { font-size: 0.52rem; font-weight: 600; letter-spacing: 1px; margin-top: 2px; }
        .req-info { flex: 1; min-width: 0; }
        .req-info h4 { margin: 0 0 8px; font-size: 1rem; color: var(--text); }
        .req-info p { margin: 0; font-size: 0.85rem; color: var(--text-muted); line-height: 1.9; }
        .req-actions { display: flex; flex-direction: column; gap: 8px; flex-shrink: 0; }
        .req-actions .btn-bersedia {
            background: var(--merah); color: white;
            padding: 8px 16px; border-radius: 8px; text-decoration: none;
            font-size: 0.8rem; font-weight: 700; text-align: center;
            transition: opacity .2s;
        }
        .req-actions .btn-hubungi {
            background: #1B8A4E; color: white;
            padding: 8px 16px; border-radius: 8px; text-decoration: none;
            font-size: 0.8rem; font-weight: 700; text-align: center;
            transition: opacity .2s;
        }
        .req-actions .btn-bersedia:hover, .req-actions .btn-hubungi:hover { opacity: .85; }
        .req-actions .badge-sudah {
            background: #d4edda; color: #155724;
            padding: 8px 14px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; text-align: center;
        }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; text-transform: capitalize; }
        .status-menunggu { background: rgba(212,144,10,.15); color: #D4900A; }
        .status-diproses { background: rgba(37,99,235,.13); color: #2563eb; }
        @media (max-width:640px) {
            .req-card { flex-direction: column; }
            .req-actions { flex-direction: row; flex-wrap: wrap; }
        }
    </style>
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
                <div class="topbar-title">Cari Permintaan</div>
                <div class="topbar-breadcrumb">DonorIn / <span>Cari Permintaan Darah</span></div>
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

        <!-- Filter Bar -->
        <form method="GET" action="cari_permintaan.php">
            <div class="filter-bar">
                <div class="filter-group">
                    <label>Golongan Darah</label>
                    <select name="goldar">
                        <option value="">Semua Golongan</option>
                        <?php foreach (['A','B','O','AB'] as $gd): ?>
                        <option value="<?= $gd ?>" <?= $filter_goldar==$gd ? 'selected' : '' ?>><?= $gd ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Kota</label>
                    <input type="text" name="kota" placeholder="Contoh: Mataram" value="<?= htmlspecialchars($filter_kota) ?>">
                </div>
                <div style="display:flex; gap:8px; padding-bottom:1px;">
                    <button type="submit" class="btn-filter primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="cari_permintaan.php" class="btn-filter reset">Reset</a>
                </div>
            </div>
        </form>

        <!-- Info Goldar -->
        <div class="info-goldar">
            <i class="fas fa-info-circle"></i>
            Golongan darah Anda: <strong><?= htmlspecialchars($pendonor_goldar) ?></strong>
            — Anda bisa mendonorkan darah ke pasien yang membutuhkan golongan darah yang sama.
        </div>

        <!-- Result Count -->
        <div class="result-count">
            Menampilkan <strong><?= $jumlah ?></strong> permintaan aktif
            <?= $filter_goldar ? " — Golongan <strong>$filter_goldar</strong>" : '' ?>
            <?= $filter_kota ? " — Kota mengandung <strong>" . htmlspecialchars($filter_kota) . "</strong>" : '' ?>
        </div>

        <!-- Hasil -->
        <?php if ($jumlah == 0): ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="fas fa-tint"></i>
                    <p>Tidak ada permintaan darah aktif<?= $filter_goldar ? " untuk golongan $filter_goldar" : '' ?> saat ini.</p>
                </div>
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($permintaan_rows as $pm):
                $tgl_pm = date('d M Y, H:i', strtotime($pm['tanggal']));
                $status_class = 'status-' . $pm['status'];
                $cek_respon = $conn->prepare("SELECT id FROM respon_donor WHERE permintaan_id = ? AND pendonor_id = ?");
                $cek_respon->execute([$pm['id'], $pendonor_id]);
                $sudah_respon = $cek_respon->rowCount();
                $cocok = ($pm['goldar'] == $pendonor_goldar);
            ?>
            <div class="req-card <?= $cocok ? 'cocok' : '' ?>">
                <div class="goldar-badge <?= $cocok ? 'cocok' : '' ?>">
                    <?= htmlspecialchars($pm['goldar']) ?>
                    <?php if ($cocok): ?><small>COCOK</small><?php endif; ?>
                </div>
                <div class="req-info">
                    <h4><?= htmlspecialchars($pm['nama_rs']) ?> — <?= htmlspecialchars($pm['kota']) ?></h4>
                    <p>
                        <i class="fas fa-user" style="width:14px;"></i> Pasien: <strong><?= htmlspecialchars($pm['nama_pasien']) ?></strong><br>
                        <i class="fas fa-tint" style="width:14px;"></i> Dibutuhkan: <strong><?= $pm['jumlah_kantong'] ?> kantong</strong><br>
                        <i class="fas fa-sticky-note" style="width:14px;"></i> <?= htmlspecialchars($pm['keterangan'] ?? '-') ?><br>
                        <i class="fas fa-map-marker-alt" style="width:14px;"></i> <?= htmlspecialchars($pm['alamat_rs'] ?? $pm['kota']) ?><br>
                        <i class="fas fa-clock" style="width:14px;"></i> <?= $tgl_pm ?>
                    </p>
                    <div style="margin-top:8px;">
                        <span class="status-badge <?= $status_class ?>"><?= $pm['status'] ?></span>
                    </div>
                </div>
                <div class="req-actions">
                    <?php if ($sudah_respon): ?>
                        <span class="badge-sudah">✅ Sudah Merespon</span>
                    <?php else: ?>
                        <a href="respon_permintaan.php?id=<?= $pm['id'] ?>" class="btn-bersedia">
                            <i class="fas fa-heart"></i> Saya Bersedia
                        </a>
                    <?php endif; ?>
                    <a href="tel:<?= $pm['hp_pasien'] ?>" class="btn-hubungi">
                        <i class="fas fa-phone"></i> Hubungi Pasien
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