<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['pmi_login']) || $_SESSION['pmi_login'] !== true) {
    header("Location: ../../login.php");
    exit;
}

$pmi_nama     = $_SESSION['pmi_nama']     ?? 'Petugas PMI';
$pmi_username = $_SESSION['pmi_username'] ?? 'pmi';

// ── UPDATE STOK (PDO) ──
$pesan_stok = "";
if (isset($_POST['update_stok'])) {
    $ok = true;
    $stmt = $conn->prepare("INSERT INTO stok_darah (goldar, jumlah_kantong, updated_at, updated_by)
                            VALUES (:g, :jml, NOW(), :by)
                            ON DUPLICATE KEY UPDATE jumlah_kantong=:jml2, updated_at=NOW(), updated_by=:by2");
    foreach (['A','B','O','AB'] as $g) {
        $jml = (int)($_POST['stok_'.$g] ?? 0);
        $ok  = $stmt->execute([':g'=>$g,':jml'=>$jml,':by'=>$pmi_nama,':jml2'=>$jml,':by2'=>$pmi_nama]) && $ok;
    }
    $pesan_stok = $ok ? 'sukses' : 'gagal';
}

// ── UPDATE STATUS PERMINTAAN ──
if (isset($_POST['update_status'])) {
    $stmt = $conn->prepare("UPDATE permintaan_darah SET status=:s WHERE id=:id");
    $stmt->execute([':s' => $_POST['status_baru'], ':id' => (int)$_POST['pm_id']]);
    header("Location: dashboard_pmi.php?pesan=status_ok");
    exit;
}

// ── STOK DARAH ──
$q_stok = $conn->query("SELECT * FROM stok_darah ORDER BY FIELD(goldar,'A','B','O','AB')");
$stok   = [];
foreach ($q_stok->fetchAll(PDO::FETCH_ASSOC) as $s) $stok[$s['goldar']] = $s;
foreach (['A','B','O','AB'] as $g)
    if (!isset($stok[$g])) $stok[$g] = ['goldar'=>$g,'jumlah_kantong'=>0,'updated_at'=>null,'updated_by'=>'-'];

// ── STATISTIK ──
$stat_menunggu = $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE status='menunggu'")->fetchColumn() ?? 0;
$stat_diproses = $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE status='diproses'")->fetchColumn() ?? 0;
$stat_selesai  = $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE status='selesai'")->fetchColumn()  ?? 0;
$stat_ditolak  = $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE status='ditolak'")->fetchColumn()  ?? 0;
$stat_pendonor = $conn->query("SELECT COUNT(*) FROM pendonor WHERE status_aktif='aktif'")->fetchColumn()       ?? 0;
$stat_stok_kritis = $conn->query("SELECT COUNT(*) FROM stok_darah WHERE jumlah_kantong <= 5")->fetchColumn()  ?? 0;

// Total permintaan bulan ini
$stat_bulan = $conn->query("SELECT COUNT(*) FROM permintaan_darah
    WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())")->fetchColumn() ?? 0;

// ── PERMINTAAN AKTIF ──
$q_pm = $conn->query(
    "SELECT pd.*, p.nama AS nama_pasien, p.no_hp AS hp_pasien
     FROM permintaan_darah pd
     JOIN pasien p ON pd.pasien_id = p.id
     WHERE pd.status IN ('menunggu','diproses')
     ORDER BY pd.tanggal DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// ── RIWAYAT 8 TERBARU ──
$q_his = $conn->query(
    "SELECT pd.*, p.nama AS nama_pasien
     FROM permintaan_darah pd
     JOIN pasien p ON pd.pasien_id = p.id
     WHERE pd.status IN ('selesai','ditolak')
     ORDER BY pd.tanggal DESC LIMIT 8"
)->fetchAll(PDO::FETCH_ASSOC);

// ── PENDONOR TERBARU ──
$q_pendonor_baru = $conn->query(
    "SELECT nama, goldar, kota, no_hp FROM pendonor
     WHERE status_aktif='aktif'
     ORDER BY id DESC LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

// ── SEBARAN GOLDAR PENDONOR ──
$q_goldar = $conn->query("SELECT goldar, COUNT(*) as total FROM pendonor WHERE status_aktif='aktif' GROUP BY goldar");
$goldar_map = ['A'=>0,'B'=>0,'O'=>0,'AB'=>0];
foreach ($q_goldar->fetchAll(PDO::FETCH_ASSOC) as $r)
    if (isset($goldar_map[$r['goldar']])) $goldar_map[$r['goldar']] = $r['total'];
$goldar_total = max(1, array_sum($goldar_map));

function stokStatus($j) {
    if ($j == 0)  return ['label'=>'Habis',    'warna'=>'#DC3545', 'bg'=>'#F8D7DA', 'ikon'=>'fa-times-circle'];
    if ($j <= 5)  return ['label'=>'Kritis',   'warna'=>'#E65100', 'bg'=>'#FFE0B2', 'ikon'=>'fa-exclamation-circle'];
    if ($j <= 15) return ['label'=>'Terbatas', 'warna'=>'#D4900A', 'bg'=>'#FFF3CD', 'ikon'=>'fa-minus-circle'];
    return               ['label'=>'Tersedia', 'warna'=>'#1B8A4E', 'bg'=>'#D1E7DD', 'ikon'=>'fa-check-circle'];
}

$pesan_url = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard PMI — DonorIn</title>
    <link rel="stylesheet" href="../../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ── STOK FORM ── */
        .stok-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:16px; }
        .stok-item { background:#fff9f9; border:1.5px solid #f0e0e0; border-radius:12px; padding:20px 16px; text-align:center; transition:border-color .2s; }
        .stok-item:focus-within { border-color:var(--merah); }
        .stok-goldar { font-size:2rem; font-weight:900; color:var(--merah); line-height:1; margin-bottom:8px; }
        .stok-input { width:100%; padding:8px 6px; border:1.5px solid #e0e0e0; border-radius:8px; font-size:1.3rem; font-weight:800; text-align:center; font-family:inherit; outline:none; color:#1a1a1a; transition:border-color .2s; }
        .stok-input:focus { border-color:var(--merah); }
        .stok-badge { font-size:0.7rem; font-weight:700; padding:3px 10px; border-radius:20px; display:inline-flex; align-items:center; gap:4px; margin-top:8px; }
        .stok-time { font-size:0.65rem; color:#bbb; margin-top:5px; }

        /* ── PROGRESS BAR GOLDAR ── */
        .goldar-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
        .goldar-item { text-align:center; padding:16px 12px; background:#fff9f9; border-radius:10px; border:1px solid #f0e0e0; }
        .goldar-type { font-size:2rem; font-weight:900; color:var(--merah); }
        .goldar-total { font-size:.8rem; color:#888; margin:4px 0 8px; }
        .goldar-bar { background:#f0f0f0; border-radius:20px; height:6px; overflow:hidden; }
        .goldar-fill { height:100%; border-radius:20px; background:var(--merah); }

        /* ── TABEL ── */
        .tbl { width:100%; border-collapse:collapse; }
        .tbl thead th { background:#f8f8f8; padding:9px 13px; text-align:left; font-size:11px; font-weight:700; color:#aaa; text-transform:uppercase; letter-spacing:.6px; border-bottom:1px solid #f0f0f0; }
        .tbl tbody td { padding:11px 13px; border-bottom:1px solid #f8f8f8; font-size:13px; vertical-align:middle; }
        .tbl tbody tr:last-child td { border-bottom:none; }
        .tbl tbody tr:hover { background:#fff9f9; }
        .tbl-name { display:flex; align-items:center; gap:10px; }
        .tbl-avatar { width:30px; height:30px; background:var(--merah); color:white; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.82rem; flex-shrink:0; }
        .tbl-name-text { font-weight:600; font-size:13px; color:#1a1a1a; }
        .tbl-name-sub { font-size:11px; color:#aaa; }

        /* ── STATUS BADGE ── */
        .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-menunggu { background:#FFF3CD; color:#856404; }
        .badge-diproses { background:#CCE5FF; color:#004085; }
        .badge-selesai  { background:#D1E7DD; color:#0F5132; }
        .badge-ditolak  { background:#F8D7DA; color:#721C24; }
        .badge-merah    { background:#F8D7DA; color:#721C24; }
        .badge-hijau    { background:#D1E7DD; color:#0F5132; }

        /* ── FORM STATUS ── */
        .form-status { display:flex; gap:5px; }
        .form-status select { padding:5px 8px; border:1px solid #e0e0e0; border-radius:6px; font-size:12px; font-family:inherit; outline:none; }
        .form-status select:focus { border-color:var(--merah); }
        .form-status .btn-ubah { padding:5px 11px; background:var(--merah); color:white; border:none; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; }
        .form-status .btn-ubah:hover { background:var(--merah-gelap); }

        /* ── NOTIF BAR ── */
        .notif-bar { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:8px; font-size:13px; font-weight:600; margin-bottom:16px; }
        .notif-sukses { background:#D1E7DD; color:#0F5132; }
        .notif-stok-ok { background:#D1E7DD; color:#0F5132; }
        .notif-stok-err { background:#F8D7DA; color:#721C24; }

        /* ── ALERT KRITIS ── */
        .alert-kritis { background:#FFF3CD; border:1px solid #FFECB5; border-left:4px solid #D4900A; border-radius:0 8px 8px 0; padding:12px 16px; font-size:13px; color:#856404; display:flex; align-items:center; gap:10px; margin-bottom:20px; }

        /* ── WELCOME ── */
        .welcome-banner { background:linear-gradient(135deg,#8b0000,#c0001a); border-radius:14px; padding:24px 28px; color:white; display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
        .welcome-banner h2 { font-size:1.2rem; font-weight:800; margin-bottom:5px; }
        .welcome-banner p { font-size:.85rem; opacity:.85; }
        .welcome-icon { font-size:3rem; opacity:.8; }

        @media(max-width:900px){
            .stok-grid { grid-template-columns:repeat(2,1fr); }
            .goldar-grid { grid-template-columns:repeat(2,1fr); }
        }
    </style>
</head>
<body>

<!-- ══════════ SIDEBAR ══════════ -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-tint"></i></div>
        <div>
            <div class="brand-name">DonorIn</div>
            <div class="brand-sub">Portal PMI</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-label">Utama</div>
            <a href="dashboard_pmi.php" class="nav-item active">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-label">Stok & Permintaan</div>
            <a href="dashboard_pmi.php#stok" class="nav-item">
                <i class="fas fa-tint"></i> Stok Darah
                <?php if ($stat_stok_kritis > 0): ?>
                <span class="nav-badge" style="background:#e65100;"><?= $stat_stok_kritis ?></span>
                <?php endif; ?>
            </a>
            <a href="dashboard_pmi.php#permintaan" class="nav-item">
                <i class="fas fa-clipboard-list"></i> Permintaan Aktif
                <span class="nav-badge"><?= $stat_menunggu + $stat_diproses ?></span>
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-label">Referensi</div>
            <a href="../../pages/donor/cari_pendonor.php" target="_blank" class="nav-item">
                <i class="fas fa-search"></i> Cari Pendonor
            </a>
            <a href="../../pages/donor/stok_darah.php" target="_blank" class="nav-item">
                <i class="fas fa-eye"></i> Halaman Stok Publik
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($pmi_nama, 0, 1)) ?></div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($pmi_nama) ?></div>
                <div class="user-role">Petugas PMI</div>
            </div>
        </div>
        <a href="../../auth/logout_pmi.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </div>
</aside>

<!-- ══════════ MAIN ══════════ -->
<main class="main">

    <!-- TOPBAR -->
    <header class="topbar">
        <div>
            <div class="topbar-title">Dashboard PMI</div>
            <div class="topbar-breadcrumb">DonorIn / <span>Portal PMI</span></div>
        </div>
        <div class="topbar-right">
            <div class="date-chip">
                <i class="fas fa-calendar-day"></i>
                <?= date('d M Y') ?>
            </div>
            <?php if ($stat_menunggu > 0): ?>
            <a href="#permintaan" class="topbar-btn" title="<?= $stat_menunggu ?> permintaan menunggu">
                <i class="fas fa-bell"></i>
                <span class="notif-dot"></span>
            </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- CONTENT -->
    <div class="content">

        <!-- NOTIFIKASI -->
        <?php if ($pesan_url === 'status_ok'): ?>
        <div class="notif-bar notif-sukses"><i class="fas fa-check-circle"></i> Status permintaan berhasil diperbarui.</div>
        <?php endif; ?>
        <?php if ($pesan_stok === 'sukses'): ?>
        <div class="notif-bar notif-stok-ok"><i class="fas fa-check-circle"></i> Stok darah berhasil diperbarui!</div>
        <?php elseif ($pesan_stok === 'gagal'): ?>
        <div class="notif-bar notif-stok-err"><i class="fas fa-times-circle"></i> Gagal memperbarui stok darah.</div>
        <?php endif; ?>

        <!-- ALERT STOK KRITIS -->
        <?php if ($stat_stok_kritis > 0): ?>
        <div class="alert-kritis">
            <i class="fas fa-exclamation-triangle"></i>
            <span><strong><?= $stat_stok_kritis ?> golongan darah</strong> dalam kondisi kritis atau habis. Segera perbarui stok di bawah.</span>
        </div>
        <?php endif; ?>

        <!-- WELCOME BANNER -->
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>Selamat Datang, <?= htmlspecialchars($pmi_nama) ?>! 👋</h2>
                <p>Kelola stok darah dan permintaan masuk dari panel PMI DonorIn. Hari ini, <?= date('l, d F Y') ?>.</p>
            </div>
            <div class="welcome-icon">🩸</div>
        </div>

        <!-- STAT ROW -->
        <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Menunggu</span>
                    <div class="stat-icon kuning"><i class="fas fa-hourglass-half"></i></div>
                </div>
                <div class="stat-value"><?= $stat_menunggu ?></div>
                <div class="stat-footer"><i class="fas fa-circle-dot" style="color:#D4900A;font-size:8px;"></i> Permintaan baru masuk</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Diproses</span>
                    <div class="stat-icon biru"><i class="fas fa-spinner"></i></div>
                </div>
                <div class="stat-value"><?= $stat_diproses ?></div>
                <div class="stat-footer"><i class="fas fa-circle-dot" style="color:#2563EB;font-size:8px;"></i> Sedang ditangani</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Selesai Bulan Ini</span>
                    <div class="stat-icon hijau"><i class="fas fa-check-double"></i></div>
                </div>
                <div class="stat-value"><?= $stat_selesai ?></div>
                <div class="stat-footer"><i class="fas fa-circle-dot" style="color:#1B8A4E;font-size:8px;"></i> <?= $stat_bulan ?> total permintaan bulan ini</div>
            </div>
        </div>

        <div class="stats-grid-2" style="grid-template-columns:repeat(3,1fr);">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Pendonor Aktif</span>
                    <div class="stat-icon merah"><i class="fas fa-hand-holding-heart"></i></div>
                </div>
                <div class="stat-value"><?= $stat_pendonor ?></div>
                <div class="stat-footer"><i class="fas fa-circle-dot" style="color:var(--merah);font-size:8px;"></i> Siap didonasikan</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Stok Kritis</span>
                    <div class="stat-icon" style="background:#FFF3CD;color:#D4900A;"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
                <div class="stat-value" style="color:<?= $stat_stok_kritis > 0 ? '#D4900A' : '#1B8A4E'; ?>"><?= $stat_stok_kritis ?></div>
                <div class="stat-footer"><i class="fas fa-circle-dot" style="color:#D4900A;font-size:8px;"></i> Golongan perlu diisi</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Ditolak</span>
                    <div class="stat-icon pink"><i class="fas fa-times-circle"></i></div>
                </div>
                <div class="stat-value"><?= $stat_ditolak ?></div>
                <div class="stat-footer"><i class="fas fa-circle-dot" style="color:#BE185D;font-size:8px;"></i> Permintaan ditolak</div>
            </div>
        </div>

        <!-- ══ UPDATE STOK ══ -->
        <div id="stok">
            <div class="section-header">
                <div class="section-title"><i class="fas fa-tint"></i> Update Stok Darah</div>
                <span style="font-size:12px;color:#aaa;">Isi jumlah kantong terkini, lalu klik Simpan</span>
            </div>
            <div class="card" style="margin-bottom:24px;">
                <div class="card-body">
                    <form method="POST" action="dashboard_pmi.php#stok">
                        <div class="stok-grid">
                            <?php foreach (['A','B','O','AB'] as $g):
                                $jml    = (int)($stok[$g]['jumlah_kantong'] ?? 0);
                                $st     = stokStatus($jml);
                                $tgl    = $stok[$g]['updated_at']
                                    ? date('d M, H:i', strtotime($stok[$g]['updated_at']))
                                    : 'Belum diperbarui';
                                $oleh   = htmlspecialchars($stok[$g]['updated_by'] ?? '-');
                            ?>
                            <div class="stok-item">
                                <div class="stok-goldar"><?= $g ?></div>
                                <input type="number" class="stok-input" name="stok_<?= $g ?>"
                                       value="<?= $jml ?>" min="0" max="999">
                                <div class="stok-badge" style="background:<?= $st['bg'] ?>;color:<?= $st['warna'] ?>;">
                                    <i class="fas <?= $st['ikon'] ?>" style="font-size:10px;"></i>
                                    <?= $st['label'] ?>
                                </div>
                                <div class="stok-time"><?= $tgl ?> · <?= $oleh ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" name="update_stok" class="btn-lihat"
                                style="background:var(--merah);color:white;border:none;padding:10px 24px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">
                            <i class="fas fa-save"></i> Simpan Stok
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ══ DUA KOLOM: PERMINTAAN AKTIF & SEBARAN GOLDAR ══ -->
        <div class="two-col">

            <!-- Permintaan Aktif -->
            <div id="permintaan">
                <div class="section-header">
                    <div class="section-title"><i class="fas fa-clipboard-list"></i> Permintaan Aktif</div>
                    <span class="badge badge-menunggu"><?= count($q_pm) ?> permintaan</span>
                </div>
                <div class="card">
                    <div class="card-body" style="padding:0;">
                        <?php if (count($q_pm) === 0): ?>
                        <div class="empty-state" style="padding:40px 20px;">
                            <i class="fas fa-clipboard-check"></i>
                            <p>Tidak ada permintaan aktif saat ini</p>
                        </div>
                        <?php else: ?>
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th>Pasien & Kontak</th>
                                    <th>Gol</th>
                                    <th>RS / Kota</th>
                                    <th>Kntg</th>
                                    <th>Status</th>
                                    <th>Ubah</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($q_pm as $pm):
                                $tgl = date('d M', strtotime($pm['tanggal']));
                            ?>
                                <tr>
                                    <td>
                                        <div class="tbl-name">
                                            <div class="tbl-avatar"><?= strtoupper(substr($pm['nama_pasien'],0,1)) ?></div>
                                            <div>
                                                <div class="tbl-name-text"><?= htmlspecialchars($pm['nama_pasien']) ?></div>
                                                <div class="tbl-name-sub">
                                                    <a href="tel:<?= $pm['hp_pasien'] ?>" style="color:#1B8A4E;">
                                                        <i class="fas fa-phone" style="font-size:9px;"></i>
                                                        <?= $pm['hp_pasien'] ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-size:1.1rem;font-weight:900;color:var(--merah);"><?= $pm['goldar'] ?></td>
                                    <td>
                                        <div style="font-weight:600;font-size:12px;"><?= htmlspecialchars($pm['nama_rs']) ?></div>
                                        <div style="font-size:11px;color:#aaa;"><?= htmlspecialchars($pm['kota']) ?></div>
                                    </td>
                                    <td style="font-weight:700;text-align:center;"><?= $pm['jumlah_kantong'] ?></td>
                                    <td><span class="badge badge-<?= $pm['status'] ?>"><?= ucfirst($pm['status']) ?></span></td>
                                    <td>
                                        <form method="POST" action="dashboard_pmi.php" class="form-status">
                                            <input type="hidden" name="pm_id" value="<?= $pm['id'] ?>">
                                            <select name="status_baru">
                                                <option value="menunggu" <?= $pm['status']==='menunggu' ?'selected':'' ?>>Menunggu</option>
                                                <option value="diproses" <?= $pm['status']==='diproses' ?'selected':'' ?>>Diproses</option>
                                                <option value="selesai"  <?= $pm['status']==='selesai'  ?'selected':'' ?>>Selesai</option>
                                                <option value="ditolak"  <?= $pm['status']==='ditolak'  ?'selected':'' ?>>Ditolak</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-ubah">✓</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sebaran Goldar Pendonor -->
            <div>
                <div class="section-header">
                    <div class="section-title"><i class="fas fa-dna"></i> Sebaran Golongan Darah Pendonor</div>
                    <a href="../../pages/donor/cari_pendonor.php" target="_blank" class="btn-lihat">
                        <i class="fas fa-arrow-right"></i> Lihat Semua
                    </a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="goldar-grid">
                            <?php foreach (['A','B','O','AB'] as $g):
                                $jml   = $goldar_map[$g];
                                $pct   = round(($jml / $goldar_total) * 100);
                            ?>
                            <div class="goldar-item">
                                <div class="goldar-type"><?= $g ?></div>
                                <div class="goldar-total"><?= $jml ?> pendonor</div>
                                <div class="goldar-bar">
                                    <div class="goldar-fill" style="width:<?= $pct ?>%;"></div>
                                </div>
                                <div style="font-size:10px;color:#bbb;margin-top:4px;"><?= $pct ?>%</div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pendonor Terbaru -->
                        <div style="margin-top:20px;border-top:1px solid #f5f5f5;padding-top:16px;">
                            <div style="font-size:12px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">
                                Pendonor Terbaru
                            </div>
                            <?php if (count($q_pendonor_baru) === 0): ?>
                            <div class="empty-state"><i class="fas fa-users"></i><p>Belum ada pendonor</p></div>
                            <?php else: ?>
                            <table class="tbl">
                                <thead><tr><th>Nama</th><th>Gol</th><th>Kota</th></tr></thead>
                                <tbody>
                                <?php foreach ($q_pendonor_baru as $pd): ?>
                                <tr>
                                    <td>
                                        <div class="tbl-name">
                                            <div class="tbl-avatar"><?= strtoupper(substr($pd['nama'],0,1)) ?></div>
                                            <div class="tbl-name-text"><?= htmlspecialchars($pd['nama']) ?></div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-merah"><?= $pd['goldar'] ?></span></td>
                                    <td style="font-size:12px;color:#aaa;"><?= htmlspecialchars($pd['kota']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ RIWAYAT PERMINTAAN ══ -->
        <div class="section-header">
            <div class="section-title"><i class="fas fa-history"></i> Riwayat Permintaan (8 Terbaru)</div>
        </div>
        <div class="card" style="margin-bottom:32px;">
            <div class="card-body" style="padding:0;">
                <?php if (count($q_his) === 0): ?>
                <div class="empty-state" style="padding:40px 20px;">
                    <i class="fas fa-folder-open"></i>
                    <p>Belum ada riwayat permintaan</p>
                </div>
                <?php else: ?>
                <table class="tbl">
                    <thead>
                        <tr><th>Pasien</th><th>Goldar</th><th>RS / Kota</th><th>Kantong</th><th>Tanggal</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($q_his as $pm): ?>
                    <tr>
                        <td>
                            <div class="tbl-name">
                                <div class="tbl-avatar"><?= strtoupper(substr($pm['nama_pasien'],0,1)) ?></div>
                                <div class="tbl-name-text"><?= htmlspecialchars($pm['nama_pasien']) ?></div>
                            </div>
                        </td>
                        <td style="font-weight:900;color:var(--merah);font-size:1rem;"><?= $pm['goldar'] ?></td>
                        <td>
                            <div style="font-weight:600;font-size:12px;"><?= htmlspecialchars($pm['nama_rs']) ?></div>
                            <div style="font-size:11px;color:#aaa;"><?= htmlspecialchars($pm['kota']) ?></div>
                        </td>
                        <td style="text-align:center;font-weight:700;"><?= $pm['jumlah_kantong'] ?></td>
                        <td style="font-size:12px;color:#aaa;"><?= date('d M Y', strtotime($pm['tanggal'])) ?></td>
                        <td><span class="badge badge-<?= $pm['status'] ?>"><?= ucfirst($pm['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /content -->
</main>

</body>
</html>