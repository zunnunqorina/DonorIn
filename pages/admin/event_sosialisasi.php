<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../../login.php");
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// Sidebar badge counts
$side_total_pasien = $conn->query("SELECT COUNT(*) FROM user WHERE role = 'pasien'")->fetchColumn() ?? 0;
$side_total_pendonor = $conn->query("SELECT COUNT(*) FROM user WHERE role = 'pendonor'")->fetchColumn() ?? 0;
$side_total_relawan = $conn->query("SELECT COUNT(*) FROM relawan")->fetchColumn() ?? 0;
$side_total_event_donor = $conn->query("SELECT COUNT(*) FROM event_donor WHERE status = 'aktif'")->fetchColumn() ?? 0;
$side_total_event_sosial = $conn->query("SELECT COUNT(*) FROM event_sosialisasi WHERE status = 'aktif'")->fetchColumn() ?? 0;
$side_total_permintaan = $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE status = 'menunggu'")->fetchColumn() ?? 0;
$side_total_ks = $conn->query("SELECT COUNT(*) FROM kritik_saran")->fetchColumn() ?? 0;


// ============================================================
// HANDLE HAPUS
// ============================================================
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $stmt = $conn->prepare("DELETE FROM event_sosialisasi WHERE id = ?");
    $stmt->execute([(int) $_GET['hapus']]);
    header("Location: event_sosialisasi.php?pesan=hapus_sukses");
    exit();
}

// ============================================================
// HANDLE TAMBAH
// ============================================================
$error_tambah = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $judul          = trim($_POST['judul']);
    $deskripsi      = trim($_POST['deskripsi'] ?? '');
    $lokasi         = trim($_POST['lokasi']);
    $alamat         = trim($_POST['alamat'] ?? '');
    $kota           = trim($_POST['kota']);
    $tanggal        = $_POST['tanggal'];
    $jam_mulai      = $_POST['jam_mulai'];
    $jam_selesai    = $_POST['jam_selesai'];
    $pembicara      = trim($_POST['pembicara'] ?? '');
    $target_peserta = (int) ($_POST['target_peserta'] ?? 0);
    $kontak         = trim($_POST['kontak'] ?? '');
    $status         = $_POST['status'] ?? 'aktif';

    // Validasi server-side
    if (empty($judul) || empty($lokasi) || empty($kota) || empty($tanggal) || empty($jam_mulai) || empty($jam_selesai)) {
        $error_tambah = 'Mohon lengkapi semua field yang wajib diisi!';
    } elseif ($jam_selesai <= $jam_mulai) {
        $error_tambah = 'Jam selesai harus lebih dari jam mulai!';
    } else {
        $stmt = $conn->prepare("INSERT INTO event_sosialisasi
            (judul, deskripsi, lokasi, alamat, kota, tanggal, jam_mulai, jam_selesai, pembicara, target_peserta, kontak, status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        if ($stmt->execute([$judul, $deskripsi, $lokasi, $alamat, $kota, $tanggal, $jam_mulai, $jam_selesai, $pembicara, $target_peserta, $kontak, $status])) {
            header("Location: event_sosialisasi.php?pesan=tambah_sukses");
            exit();
        } else {
            $error_tambah = 'Gagal menambahkan event sosialisasi!';
        }
    }
}

// ============================================================
// HANDLE EDIT
// ============================================================
$error_edit = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'edit') {
    $id_edit        = (int) $_POST['id_edit'];
    $judul          = trim($_POST['judul']);
    $deskripsi      = trim($_POST['deskripsi'] ?? '');
    $lokasi         = trim($_POST['lokasi']);
    $alamat         = trim($_POST['alamat'] ?? '');
    $kota           = trim($_POST['kota']);
    $tanggal        = $_POST['tanggal'];
    $jam_mulai      = $_POST['jam_mulai'];
    $jam_selesai    = $_POST['jam_selesai'];
    $pembicara      = trim($_POST['pembicara'] ?? '');
    $target_peserta = (int) ($_POST['target_peserta'] ?? 0);
    $kontak         = trim($_POST['kontak'] ?? '');
    $status         = $_POST['status'] ?? 'aktif';

    if (empty($judul) || empty($lokasi) || empty($kota) || empty($tanggal) || empty($jam_mulai) || empty($jam_selesai)) {
        $error_edit = 'Mohon lengkapi semua field yang wajib diisi!';
    } elseif ($jam_selesai <= $jam_mulai) {
        $error_edit = 'Jam selesai harus lebih dari jam mulai!';
    } else {
        $stmt = $conn->prepare("UPDATE event_sosialisasi SET
            judul=?, deskripsi=?, lokasi=?, alamat=?, kota=?, tanggal=?,
            jam_mulai=?, jam_selesai=?, pembicara=?, target_peserta=?, kontak=?, status=?
            WHERE id=?");
        if ($stmt->execute([$judul, $deskripsi, $lokasi, $alamat, $kota, $tanggal, $jam_mulai, $jam_selesai, $pembicara, $target_peserta, $kontak, $status, $id_edit])) {
            header("Location: event_sosialisasi.php?pesan=edit_sukses");
            exit();
        } else {
            $error_edit = 'Gagal memperbarui data event!';
        }
    }
}

// ============================================================
// FILTER & PAGINASI
// ============================================================
$search       = trim($_GET['search'] ?? '');
$filter_status= $_GET['status_filter'] ?? '';
$page         = max(1, (int) ($_GET['page'] ?? 1));
$per_page     = 10;
$offset       = ($page - 1) * $per_page;

$where  = "WHERE 1=1";
$params = [];
if ($search !== '') {
    $where   .= " AND (judul LIKE ? OR lokasi LIKE ? OR kota LIKE ? OR pembicara LIKE ?)";
    $s        = "%$search%";
    $params   = [$s, $s, $s, $s];
}
if ($filter_status !== '') {
    $where  .= " AND status = ?";
    $params[] = $filter_status;
}

$q_total = $conn->prepare("SELECT COUNT(*) FROM event_sosialisasi $where");
$q_total->execute($params);
$total    = (int) $q_total->fetchColumn();
$total_pg = (int) ceil($total / $per_page);

$q_data = $conn->prepare("SELECT * FROM event_sosialisasi $where ORDER BY tanggal DESC LIMIT ? OFFSET ?");
foreach ($params as $i => $val) {
    $q_data->bindValue($i + 1, $val);
}
$param_count = count($params);
$q_data->bindValue($param_count + 1, $per_page, PDO::PARAM_INT);
$q_data->bindValue($param_count + 2, $offset,   PDO::PARAM_INT);
$q_data->execute();
$rows = $q_data->fetchAll(PDO::FETCH_ASSOC);

// Statistik
$stat_total   = (int) $conn->query("SELECT COUNT(*) FROM event_sosialisasi")->fetchColumn();
$stat_aktif   = (int) $conn->query("SELECT COUNT(*) FROM event_sosialisasi WHERE status='aktif'")->fetchColumn();
$stat_selesai = (int) $conn->query("SELECT COUNT(*) FROM event_sosialisasi WHERE status='selesai'")->fetchColumn();
$stat_bulan   = (int) $conn->query("SELECT COUNT(*) FROM event_sosialisasi WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())")->fetchColumn();

// Navigasi badge
$total_pendonor = $conn->query("SELECT COUNT(*) FROM user WHERE role='pendonor'")->fetchColumn() ?? 0;
$total_pasien   = $conn->query("SELECT COUNT(*) FROM user WHERE role='pasien'")->fetchColumn() ?? 0;
$total_relawan  = $conn->query("SELECT COUNT(*) FROM relawan")->fetchColumn() ?? 0;
$total_ks       = $conn->query("SELECT COUNT(*) FROM kritik_saran")->fetchColumn() ?? 0;
$total_ev_donor = $conn->query("SELECT COUNT(*) FROM event_donor WHERE status='aktif'")->fetchColumn() ?? 0;

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Sosialisasi — DonorIn Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .status-aktif   { background:#D1E7DD; color:#0F5132; }
        .status-selesai { background:#E2E3E5; color:#383D41; }
        .status-batal   { background:#F8D7DA; color:#721C24; }
        .event-tanggal-box {
            display:flex; flex-direction:column; align-items:center; justify-content:center;
            width:46px; min-width:46px; height:52px;
            background:#1B8A4E; border-radius:10px; color:#fff;
        }
        .event-tanggal-box .hari   { font-family:'Fraunces',serif; font-size:20px; font-weight:900; line-height:1; }
        .event-tanggal-box .bulan  { font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; opacity:.85; }
        .event-tanggal-box .tahun  { font-size:9px; opacity:.7; }
        .event-tanggal-box.selesai { background:#6C757D; }
        .event-tanggal-box.batal   { background:#DC3545; }
        .td-event { display:flex; align-items:center; gap:14px; }
        .td-event-info .title { font-weight:700; font-size:13px; }
        .td-event-info .meta  { font-size:11px; color:var(--abu-sedang); margin-top:2px; display:flex; gap:8px; flex-wrap:wrap; }
        .td-event-info .meta i { color:#1B8A4E; margin-right:2px; }
        .jam-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; background:#E8F8F0; border:1px solid #A3D9BE; border-radius:20px; font-size:11px; font-weight:700; color:#1B8A4E; }
        .target-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; background:#EDF4FF; border:1px solid #BFDBFE; border-radius:20px; font-size:11px; font-weight:700; color:#1565C0; }
        .pembicara-info { font-size:12px; color:var(--teks-sedang); display:flex; align-items:center; gap:4px; }
        .pembicara-info i { color:#1B8A4E; }
    </style>
</head>
<body>

<?php
$halaman_aktif_admin = 'event_sosialisasi';
include '../../components/sidebar_admin.php';
?>

<!-- ══ MAIN ══ -->
<main class="main">
    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <div class="topbar-title"><i class="fas fa-bullhorn" style="color:#1B8A4E;margin-right:8px;"></i>Event Sosialisasi</div>
                <div class="topbar-breadcrumb">
                    <a href="dashboard_admin.php">DonorIn</a> /
                    <span>Event</span> /
                    <span>Event Sosialisasi</span>
                </div>
            </div>
        </div>
        <div class="topbar-right">
            <div class="date-chip"><i class="fas fa-calendar-day"></i><?= date('d M Y') ?></div>
            <a href="kritik_saran_admin.php" class="topbar-btn" title="Kritik & Saran">
                <i class="fas fa-bell"></i>
                <?php if ($total_ks > 0): ?><span class="notif-dot"></span><?php endif; ?>
            </a>
        </div>
    </header>

    <div class="content">

        <!-- NOTIFIKASI -->
        <?php if ($pesan === 'tambah_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Event sosialisasi berhasil ditambahkan!</div>
        <?php elseif ($pesan === 'edit_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Data event sosialisasi berhasil diperbarui!</div>
        <?php elseif ($pesan === 'hapus_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Event sosialisasi berhasil dihapus.</div>
        <?php endif; ?>
        <?php if ($error_tambah): ?>
        <div class="notif notif-error"><i class="fas fa-exclamation-circle"></i> <?= $error_tambah ?></div>
        <?php endif; ?>
        <?php if ($error_edit): ?>
        <div class="notif notif-error"><i class="fas fa-exclamation-circle"></i> <?= $error_edit ?></div>
        <?php endif; ?>

        <!-- MINI STATS -->
        <div class="mini-stats" style="grid-template-columns:repeat(4,1fr);">
            <div class="mini-card">
                <div class="mini-icon" style="background:#E8F8F0;color:#1B8A4E;"><i class="fas fa-bullhorn"></i></div>
                <div><div class="mini-val"><?= $stat_total ?></div><div class="mini-label">Total Sosialisasi</div></div>
            </div>
            <div class="mini-card">
                <div class="mini-icon" style="background:#EDF4FF;color:#2563EB;"><i class="fas fa-check-circle"></i></div>
                <div><div class="mini-val"><?= $stat_aktif ?></div><div class="mini-label">Aktif</div></div>
            </div>
            <div class="mini-card">
                <div class="mini-icon" style="background:#F1F3F5;color:#6B7280;"><i class="fas fa-flag-checkered"></i></div>
                <div><div class="mini-val"><?= $stat_selesai ?></div><div class="mini-label">Selesai</div></div>
            </div>
            <div class="mini-card">
                <div class="mini-icon" style="background:#FFF8E6;color:#D4900A;"><i class="fas fa-calendar-check"></i></div>
                <div><div class="mini-val"><?= $stat_bulan ?></div><div class="mini-label">Bulan Ini</div></div>
            </div>
        </div>

        <!-- PANEL TABEL -->
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="fas fa-bullhorn" style="color:#1B8A4E;"></i> Daftar Event Sosialisasi
                    <span style="font-size:12px;font-weight:500;color:var(--abu-sedang);">
                        (<?= $total ?> event<?= ($search || $filter_status) ? ' — difilter' : '' ?>)
                    </span>
                </div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <div style="position:relative;">
                            <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--abu-sedang);font-size:13px;pointer-events:none;"></i>
                            <input type="text" name="search" class="input-search"
                                   placeholder="Cari judul, lokasi, pembicara…"
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <select name="status_filter" class="select-filter" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="aktif"   <?= $filter_status === 'aktif'   ? 'selected' : '' ?>>Aktif</option>
                            <option value="selesai" <?= $filter_status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="batal"   <?= $filter_status === 'batal'   ? 'selected' : '' ?>>Batal</option>
                        </select>
                        <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-filter"></i> Filter</button>
                        <?php if ($search || $filter_status): ?>
                        <a href="event_sosialisasi.php" class="btn btn-ghost btn-sm"><i class="fas fa-times"></i> Reset</a>
                        <?php endif; ?>
                    </form>
                    <button class="btn btn-primary btn-sm" style="background:#1B8A4E;" onclick="bukaModal('modalTambah')">
                        <i class="fas fa-plus"></i> Tambah Sosialisasi
                    </button>
                </div>
            </div>

            <div class="tbl-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Event Sosialisasi</th>
                            <th>Jam</th>
                            <th>Target Peserta</th>
                            <th>Pembicara</th>
                            <th>Status</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($rows) > 0):
                        $no = $offset + 1;
                        foreach ($rows as $row):
                            $tgl_ts = strtotime($row['tanggal']);
                            $st_cls = $row['status'];
                    ?>
                        <tr>
                            <td style="color:var(--abu-sedang);font-size:12px;font-weight:600;"><?= $no++ ?></td>
                            <td>
                                <div class="td-event">
                                    <div class="event-tanggal-box <?= $st_cls ?>">
                                        <span class="hari"><?= date('d', $tgl_ts) ?></span>
                                        <span class="bulan"><?= date('M', $tgl_ts) ?></span>
                                        <span class="tahun"><?= date('Y', $tgl_ts) ?></span>
                                    </div>
                                    <div class="td-event-info">
                                        <div class="title"><?= htmlspecialchars($row['judul']) ?></div>
                                        <div class="meta">
                                            <span><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($row['lokasi']) ?></span>
                                            <span><i class="fas fa-city"></i><?= htmlspecialchars($row['kota']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="jam-badge">
                                    <i class="fas fa-clock" style="font-size:10px;"></i>
                                    <?= substr($row['jam_mulai'], 0, 5) ?> – <?= substr($row['jam_selesai'], 0, 5) ?>
                                </span>
                            </td>
                            <td>
                                <span class="target-badge">
                                    <i class="fas fa-users" style="font-size:10px;"></i>
                                    <?= $row['target_peserta'] ?> org
                                </span>
                            </td>
                            <td>
                                <div class="pembicara-info">
                                    <i class="fas fa-user-tie"></i>
                                    <?= htmlspecialchars($row['pembicara'] ?? '—') ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge status-<?= $row['status'] ?>">
                                    <?php if ($row['status'] === 'aktif'): ?>
                                        <i class="fas fa-circle" style="font-size:7px;"></i>
                                    <?php elseif ($row['status'] === 'selesai'): ?>
                                        <i class="fas fa-flag-checkered" style="font-size:9px;"></i>
                                    <?php else: ?>
                                        <i class="fas fa-times-circle" style="font-size:9px;"></i>
                                    <?php endif; ?>
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="aksi-wrap" style="justify-content:center;">
                                    <button class="btn btn-sm btn-edit btn-icon"
                                        onclick='bukaModalEdit(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)'
                                        title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-hapus btn-icon"
                                        onclick='konfirmasiHapus(<?= $row['id'] ?>, "<?= addslashes(htmlspecialchars($row['judul'])) ?>")'
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-bullhorn"></i>
                                <p><?= ($search || $filter_status) ? 'Tidak ada event yang cocok dengan filter.' : 'Belum ada event sosialisasi. Tambahkan yang pertama!' ?></p>
                            </div>
                        </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINASI -->
            <?php if ($total_pg > 1): ?>
            <div class="pagination-wrap">
                <div class="pagi-info">
                    Menampilkan <strong><?= $offset+1 ?>–<?= min($offset+$per_page, $total) ?></strong>
                    dari <strong><?= $total ?></strong> event
                </div>
                <div class="pagi-btns">
                    <?php $qs = http_build_query(['search' => $search, 'status_filter' => $filter_status]); ?>
                    <a href="?page=<?= $page-1 ?>&<?= $qs ?>" class="pagi-btn <?= $page<=1?'disabled':'' ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php for ($i=1; $i<=$total_pg; $i++): ?>
                    <a href="?page=<?= $i ?>&<?= $qs ?>" class="pagi-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <a href="?page=<?= $page+1 ?>&<?= $qs ?>" class="pagi-btn <?= $page>=$total_pg?'disabled':'' ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /content -->
</main>

<!-- ══ MODAL TAMBAH ══ -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal" style="max-width:680px;">
        <div class="modal-head">
            <div class="modal-title"><i class="fas fa-bullhorn" style="color:#1B8A4E;"></i> Tambah Event Sosialisasi</div>
            <button class="modal-close" onclick="tutupModal('modalTambah')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="event_sosialisasi.php">
            <input type="hidden" name="aksi" value="tambah">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Judul Event <span class="req">*</span></label>
                        <input type="text" name="judul" class="form-input" placeholder="Contoh: Sosialisasi Pentingnya Donor Darah" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-input" rows="2" placeholder="Deskripsi singkat kegiatan sosialisasi..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lokasi / Tempat <span class="req">*</span></label>
                        <input type="text" name="lokasi" class="form-input" placeholder="Contoh: Aula SMAN 1 Mataram" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kota <span class="req">*</span></label>
                        <input type="text" name="kota" class="form-input" placeholder="Contoh: Mataram" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Alamat Lengkap</label>
                        <input type="text" name="alamat" class="form-input" placeholder="Jl. ... No. ...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal <span class="req">*</span></label>
                        <input type="date" name="tanggal" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Target Peserta</label>
                        <input type="number" name="target_peserta" class="form-input" placeholder="0" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Mulai <span class="req">*</span></label>
                        <input type="time" name="jam_mulai" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Selesai <span class="req">*</span></label>
                        <input type="time" name="jam_selesai" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pembicara / Narasumber</label>
                        <input type="text" name="pembicara" class="form-input" placeholder="Contoh: dr. Hendra Kusuma">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kontak</label>
                        <input type="text" name="kontak" class="form-input" placeholder="Nomor HP / Telp">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="aktif">Aktif</option>
                            <option value="selesai">Selesai</option>
                            <option value="batal">Batal</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="tutupModal('modalTambah')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#1B8A4E;"><i class="fas fa-save"></i> Simpan Event</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ MODAL EDIT ══ -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal" style="max-width:680px;">
        <div class="modal-head">
            <div class="modal-title"><i class="fas fa-pen"></i> Edit Event Sosialisasi</div>
            <button class="modal-close" onclick="tutupModal('modalEdit')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="event_sosialisasi.php">
            <input type="hidden" name="aksi"    value="edit">
            <input type="hidden" name="id_edit" id="edit_id">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Judul Event <span class="req">*</span></label>
                        <input type="text" name="judul" id="edit_judul" class="form-input" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="edit_deskripsi" class="form-input" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lokasi / Tempat <span class="req">*</span></label>
                        <input type="text" name="lokasi" id="edit_lokasi" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kota <span class="req">*</span></label>
                        <input type="text" name="kota" id="edit_kota" class="form-input" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Alamat Lengkap</label>
                        <input type="text" name="alamat" id="edit_alamat" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal <span class="req">*</span></label>
                        <input type="date" name="tanggal" id="edit_tanggal" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Target Peserta</label>
                        <input type="number" name="target_peserta" id="edit_target" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Mulai <span class="req">*</span></label>
                        <input type="time" name="jam_mulai" id="edit_jam_mulai" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Selesai <span class="req">*</span></label>
                        <input type="time" name="jam_selesai" id="edit_jam_selesai" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pembicara / Narasumber</label>
                        <input type="text" name="pembicara" id="edit_pembicara" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kontak</label>
                        <input type="text" name="kontak" id="edit_kontak" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="aktif">Aktif</option>
                            <option value="selesai">Selesai</option>
                            <option value="batal">Batal</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="tutupModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#1B8A4E;"><i class="fas fa-save"></i> Perbarui Event</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ MODAL HAPUS ══ -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal" style="max-width:400px;">
        <div class="modal-body" style="padding:32px 28px;text-align:center;">
            <div class="hapus-icon"><i class="fas fa-trash-alt"></i></div>
            <h3 style="font-size:18px;font-weight:800;margin-bottom:8px;">Hapus Event Sosialisasi?</h3>
            <p style="font-size:14px;color:var(--teks-sedang);margin-bottom:4px;">Kamu akan menghapus event:</p>
            <p style="font-size:15px;font-weight:700;color:var(--merah);margin-bottom:12px;" id="hapus_nama">—</p>
            <p style="font-size:13px;color:var(--abu-sedang);">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-foot" style="justify-content:center;">
            <button class="btn btn-ghost" onclick="tutupModal('modalHapus')"><i class="fas fa-times"></i> Batal</button>
            <a href="#" id="hapus_link" class="btn btn-danger"><i class="fas fa-trash"></i> Ya, Hapus</a>
        </div>
    </div>
</div>

<script src="../../assets/admin.js"></script>
<script>
function bukaModal(id)  { document.getElementById(id).classList.add('show'); document.body.style.overflow='hidden'; }
function tutupModal(id) { document.getElementById(id).classList.remove('show'); document.body.style.overflow=''; }

document.getElementById('btnToggleSidebar').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.add('open');
});
document.getElementById('btnCloseSidebar').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.remove('open');
});

document.querySelectorAll('.modal-overlay').forEach(function(el) {
    el.addEventListener('click', function(e) { if (e.target === el) tutupModal(el.id); });
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.show').forEach(function(el){ tutupModal(el.id); });
});

function bukaModalEdit(data) {
    document.getElementById('edit_id').value          = data.id;
    document.getElementById('edit_judul').value       = data.judul          || '';
    document.getElementById('edit_deskripsi').value   = data.deskripsi      || '';
    document.getElementById('edit_lokasi').value      = data.lokasi         || '';
    document.getElementById('edit_kota').value        = data.kota           || '';
    document.getElementById('edit_alamat').value      = data.alamat         || '';
    document.getElementById('edit_tanggal').value     = data.tanggal        || '';
    document.getElementById('edit_target').value      = data.target_peserta || 0;
    document.getElementById('edit_jam_mulai').value   = data.jam_mulai      || '';
    document.getElementById('edit_jam_selesai').value = data.jam_selesai    || '';
    document.getElementById('edit_pembicara').value   = data.pembicara      || '';
    document.getElementById('edit_kontak').value      = data.kontak         || '';
    document.getElementById('edit_status').value      = data.status         || 'aktif';
    bukaModal('modalEdit');
}

function konfirmasiHapus(id, judul) {
    hapusDataSweet(id, judul, 'event_sosialisasi.php?hapus=');
}

<?php if ($error_tambah): ?> bukaModal('modalTambah'); <?php endif; ?>
<?php if ($error_edit):   ?> bukaModal('modalEdit');   <?php endif; ?>
</script>

</body>
</html>
