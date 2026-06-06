<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../auth/login_admin.php");
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// ── HAPUS ──
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM kritik_saran WHERE id = $id_hapus");
    header("Location: kritik_saran_admin.php?pesan=hapus_sukses");
    exit();
}

// ── TANDAI SUDAH DIBACA / BELUM ──
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id_toggle = (int) $_GET['toggle'];
    $q_status  = mysqli_query($conn, "SELECT sudah_dibaca FROM kritik_saran WHERE id = $id_toggle");
    if ($row_s = mysqli_fetch_assoc($q_status)) {
        $baru = $row_s['sudah_dibaca'] ? 0 : 1;
        mysqli_query($conn, "UPDATE kritik_saran SET sudah_dibaca = $baru WHERE id = $id_toggle");
    }
    header("Location: kritik_saran_admin.php?pesan=update_sukses");
    exit();
}

// ── BALAS (simpan balasan admin) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'balas') {
    $id_pesan = (int) $_POST['id_pesan'];
    $balasan  = mysqli_real_escape_string($conn, trim($_POST['balasan']));
    if ($balasan !== '') {
        mysqli_query($conn, "UPDATE kritik_saran SET balasan = '$balasan', sudah_dibaca = 1, tgl_balas = NOW() WHERE id = $id_pesan");
        header("Location: kritik_saran_admin.php?pesan=balas_sukses");
        exit();
    }
}

// ── FILTER & PAGINASI ──
$filter_kat  = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';
$filter_baca = isset($_GET['status'])   ? $_GET['status'] : '';
$search      = isset($_GET['search'])   ? trim(mysqli_real_escape_string($conn, $_GET['search'])) : '';
$page        = isset($_GET['page'])     ? max(1, (int) $_GET['page']) : 1;
$per_page    = 10;
$offset      = ($page - 1) * $per_page;

$where = "WHERE 1=1";
if ($filter_kat !== '')  $where .= " AND kategori = '$filter_kat'";
if ($filter_baca === 'belum') $where .= " AND sudah_dibaca = 0";
if ($filter_baca === 'sudah') $where .= " AND sudah_dibaca = 1";
if ($search !== '')      $where .= " AND (nama LIKE '%$search%' OR email LIKE '%$search%' OR pesan LIKE '%$search%')";

$q_total   = mysqli_query($conn, "SELECT COUNT(*) as total FROM kritik_saran $where");
$total     = mysqli_fetch_assoc($q_total)['total'] ?? 0;
$total_pg  = ceil($total / $per_page);

$q_data = mysqli_query($conn, "SELECT * FROM kritik_saran $where ORDER BY tanggal DESC LIMIT $per_page OFFSET $offset");

// Statistik
$stat_total   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM kritik_saran"))['t'] ?? 0;
$stat_belum   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM kritik_saran WHERE sudah_dibaca = 0"))['t'] ?? 0;
$stat_kritik  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM kritik_saran WHERE kategori='kritik'"))['t'] ?? 0;
$stat_saran   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM kritik_saran WHERE kategori='saran'"))['t'] ?? 0;

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kritik & Saran — DonorIn Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --merah:        #C0001A;
            --merah-gelap:  #8B0012;
            --merah-muda:   #FFE5E9;
            --merah-tipis:  #FFF5F6;
            --putih:        #FFFFFF;
            --abu-terang:   #F7F8FA;
            --abu:          #E8EAED;
            --abu-sedang:   #9DA3AE;
            --teks-gelap:   #1A1A2E;
            --teks-sedang:  #4A4A6A;
            --sidebar-w:    260px;
            --shadow-sm:    0 1px 3px rgba(192,0,26,.08);
            --shadow-md:    0 4px 16px rgba(192,0,26,.10);
            --radius:       14px;
            --radius-sm:    8px;
            --trans:        all .25s cubic-bezier(.4,0,.2,1);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--abu-terang); color: var(--teks-gelap); min-height: 100vh; display: flex; }

        /* ── SIDEBAR ── */
        .sidebar { width: var(--sidebar-w); min-height: 100vh; background: linear-gradient(175deg,#8B0012 0%,#C0001A 55%,#A0001A 100%); position: fixed; top: 0; left: 0; display: flex; flex-direction: column; z-index: 100; box-shadow: 4px 0 24px rgba(139,0,18,.35); }
        .sidebar-brand { padding: 28px 24px 24px; border-bottom: 1px solid rgba(255,255,255,.12); display: flex; align-items: center; gap: 12px; }
        .brand-icon { width: 42px; height: 42px; background: var(--putih); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .brand-icon i { color: var(--merah); font-size: 20px; }
        .brand-name { font-family: 'Fraunces', serif; font-size: 22px; font-weight: 900; color: var(--putih); line-height: 1; }
        .brand-sub  { font-size: 10px; color: rgba(255,255,255,.6); font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase; margin-top: 3px; }
        .sidebar-nav { padding: 20px 16px; flex: 1; overflow-y: auto; }
        .nav-section { font-size: 10px; font-weight: 700; color: rgba(255,255,255,.4); letter-spacing: 1.5px; text-transform: uppercase; padding: 0 10px 8px; margin-top: 16px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 10px; color: rgba(255,255,255,.75); text-decoration: none; font-size: 14px; font-weight: 500; transition: var(--trans); margin-bottom: 2px; }
        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,.15); color: var(--putih); }
        .nav-item i { width: 18px; text-align: center; font-size: 15px; }
        .sidebar-foot { padding: 16px; border-top: 1px solid rgba(255,255,255,.12); }
        .user-card { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: rgba(255,255,255,.1); border-radius: 10px; margin-bottom: 10px; }
        .user-avatar { width: 34px; height: 34px; background: rgba(255,255,255,.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; color: white; }
        .user-info { flex: 1; }
        .user-name { font-size: 13px; font-weight: 700; color: white; }
        .user-role { font-size: 11px; color: rgba(255,255,255,.55); }
        .btn-logout { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; border-radius: 10px; background: rgba(255,255,255,.1); color: rgba(255,255,255,.8); text-decoration: none; font-size: 13px; font-weight: 600; transition: var(--trans); border: 1px solid rgba(255,255,255,.15); }
        .btn-logout:hover { background: rgba(255,255,255,.2); color: white; }

        /* ── MAIN ── */
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar { background: var(--putih); border-bottom: 1px solid var(--abu); padding: 0 32px; height: 64px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .topbar-title { font-size: 18px; font-weight: 800; color: var(--teks-gelap); }
        .topbar-sub { font-size: 13px; color: var(--abu-sedang); margin-top: 2px; }
        .content { padding: 28px 32px; flex: 1; }

        /* ── STAT CARDS ── */
        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: var(--putih); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 16px; border: 1px solid var(--abu); }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .stat-icon.merah  { background: var(--merah-muda); color: var(--merah); }
        .stat-icon.orange { background: #FFF3E0; color: #E65100; }
        .stat-icon.hijau  { background: #E8F5E9; color: #1B5E20; }
        .stat-icon.biru   { background: #E3F2FD; color: #0D47A1; }
        .stat-val  { font-size: 26px; font-weight: 800; color: var(--teks-gelap); line-height: 1; }
        .stat-lbl  { font-size: 12px; color: var(--abu-sedang); margin-top: 4px; }

        /* ── TOOLBAR ── */
        .toolbar { background: var(--putih); border-radius: var(--radius); padding: 16px 20px; box-shadow: var(--shadow-sm); border: 1px solid var(--abu); margin-bottom: 20px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
        .search-wrap { flex: 1; min-width: 200px; position: relative; }
        .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--abu-sedang); font-size: 14px; }
        .search-input { width: 100%; padding: 9px 12px 9px 36px; border: 1px solid var(--abu); border-radius: var(--radius-sm); font-size: 14px; font-family: inherit; outline: none; transition: var(--trans); }
        .search-input:focus { border-color: var(--merah); box-shadow: 0 0 0 3px rgba(192,0,26,.08); }
        .filter-select { padding: 9px 12px; border: 1px solid var(--abu); border-radius: var(--radius-sm); font-size: 14px; font-family: inherit; outline: none; background: white; cursor: pointer; }
        .filter-select:focus { border-color: var(--merah); }
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: var(--trans); font-family: inherit; }
        .btn-primary { background: var(--merah); color: white; }
        .btn-primary:hover { background: var(--merah-gelap); }
        .btn-ghost { background: transparent; color: var(--teks-sedang); border: 1px solid var(--abu); }
        .btn-ghost:hover { background: var(--abu-terang); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-danger { background: #DC3545; color: white; }
        .btn-danger:hover { background: #b02a37; }
        .btn-success { background: #198754; color: white; }
        .btn-success:hover { background: #146c43; }
        .btn-info { background: #0dcaf0; color: #000; }
        .btn-info:hover { background: #0aa2c0; }

        /* ── TABEL ── */
        .card { background: var(--putih); border-radius: var(--radius); box-shadow: var(--shadow-sm); border: 1px solid var(--abu); overflow: hidden; }
        .tbl { width: 100%; border-collapse: collapse; }
        .tbl thead th { background: var(--abu-terang); padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700; color: var(--abu-sedang); text-transform: uppercase; letter-spacing: .8px; border-bottom: 1px solid var(--abu); }
        .tbl tbody td { padding: 14px 16px; border-bottom: 1px solid var(--abu); font-size: 13px; vertical-align: top; }
        .tbl tbody tr:last-child td { border-bottom: none; }
        .tbl tbody tr:hover { background: var(--merah-tipis); }
        .tbl tbody tr.belum-baca { background: #FFFDE7; }
        .tbl tbody tr.belum-baca:hover { background: #FFF9C4; }

        /* ── BADGE ── */
        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .badge-kritik     { background: #f8d7da; color: #721c24; }
        .badge-saran      { background: #d4edda; color: #155724; }
        .badge-pertanyaan { background: #cce5ff; color: #004085; }
        .badge-belum { background: #FFF3CD; color: #856404; }
        .badge-sudah { background: #D1E7DD; color: #0F5132; }

        /* ── PESAN PENUH ── */
        .pesan-cell { max-width: 280px; }
        .pesan-teks { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; color: var(--teks-sedang); line-height: 1.5; }
        .balasan-chip { margin-top: 6px; display: inline-flex; align-items: center; gap: 5px; background: #D1E7DD; color: #0F5132; padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }

        /* ── AKSI ── */
        .aksi-grup { display: flex; gap: 6px; flex-wrap: wrap; }

        /* ── PAGINASI ── */
        .paginasi { display: flex; gap: 6px; justify-content: center; padding: 20px; }
        .paginasi a, .paginasi span { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; text-decoration: none; border: 1px solid var(--abu); color: var(--teks-sedang); transition: var(--trans); }
        .paginasi a:hover { border-color: var(--merah); color: var(--merah); }
        .paginasi .aktif { background: var(--merah); color: white; border-color: var(--merah); }

        /* ── NOTIFIKASI ── */
        .notif { position: fixed; top: 20px; right: 24px; z-index: 9999; padding: 14px 20px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 600; box-shadow: var(--shadow-md); display: flex; align-items: center; gap: 10px; animation: slideIn .3s ease; }
        .notif-sukses { background: #D1E7DD; color: #0F5132; border: 1px solid #A3CFBB; }
        @keyframes slideIn { from { opacity:0; transform:translateY(-12px); } to { opacity:1; transform:none; } }

        /* ── MODAL ── */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
        .modal-overlay.show { display: flex; }
        .modal { background: var(--putih); border-radius: var(--radius); width: 100%; max-width: 560px; box-shadow: 0 20px 60px rgba(0,0,0,.25); overflow: hidden; }
        .modal-head { padding: 20px 24px; border-bottom: 1px solid var(--abu); display: flex; align-items: center; justify-content: space-between; }
        .modal-title { font-size: 16px; font-weight: 800; color: var(--teks-gelap); display: flex; align-items: center; gap: 8px; }
        .modal-close { background: none; border: none; cursor: pointer; color: var(--abu-sedang); font-size: 18px; padding: 4px; border-radius: 6px; }
        .modal-close:hover { background: var(--abu); color: var(--teks-gelap); }
        .modal-body { padding: 24px; }
        .modal-foot { padding: 16px 24px; border-top: 1px solid var(--abu); display: flex; justify-content: flex-end; gap: 10px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--teks-sedang); margin-bottom: 6px; }
        .form-input, .form-textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--abu); border-radius: var(--radius-sm); font-size: 14px; font-family: inherit; outline: none; transition: var(--trans); }
        .form-input:focus, .form-textarea:focus { border-color: var(--merah); box-shadow: 0 0 0 3px rgba(192,0,26,.08); }
        .form-textarea { resize: vertical; min-height: 100px; }
        .detail-box { background: var(--abu-terang); border-radius: var(--radius-sm); padding: 14px 16px; margin-bottom: 16px; border: 1px solid var(--abu); }
        .detail-box .label { font-size: 11px; font-weight: 700; color: var(--abu-sedang); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 4px; }
        .detail-box .val { font-size: 14px; color: var(--teks-gelap); line-height: 1.6; }

        /* ── EMPTY ── */
        .empty-state { text-align: center; padding: 60px 20px; color: var(--abu-sedang); }
        .empty-state i { font-size: 48px; margin-bottom: 16px; display: block; opacity: .4; }
        .empty-state p { font-size: 15px; font-weight: 600; }

        @media (max-width: 900px) {
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-tint"></i></div>
        <div>
            <div class="brand-name">DonorIn</div>
            <div class="brand-sub">Admin Panel</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Menu Utama</div>
        <a href="dashboard_admin.php" class="nav-item"><i class="fas fa-chart-pie"></i> Dashboard</a>
        <a href="pendonor_admin.php" class="nav-item"><i class="fas fa-users"></i> Manajemen Pendonor</a>
        <a href="kritik_saran_admin.php" class="nav-item active"><i class="fas fa-comments"></i> Kritik & Saran</a>
        <div class="nav-section">Lainnya</div>
        <a href="respon_permintaan.php" class="nav-item"><i class="fas fa-hand-holding-heart"></i> Permintaan Darah</a>
        <a href="tampil_kritik.php" class="nav-item"><i class="fas fa-eye"></i> Halaman Publik</a>
    </nav>
    <div class="sidebar-foot">
        <div class="user-card">
            <div class="user-avatar"><?= strtoupper(substr($admin_username, 0, 1)) ?></div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($admin_username) ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        <a href="../../auth/logout_admin.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</a>
    </div>
</aside>

<!-- ══ MAIN ══ -->
<main class="main">
    <div class="topbar">
        <div>
            <div class="topbar-title"><i class="fas fa-comments" style="color:var(--merah);margin-right:8px;"></i>Kritik & Saran</div>
            <div class="topbar-sub">Moderasi pesan masuk dari pengguna</div>
        </div>
    </div>

    <div class="content">

        <!-- NOTIFIKASI -->
        <?php if ($pesan === 'hapus_sukses'): ?>
            <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Pesan berhasil dihapus.</div>
        <?php elseif ($pesan === 'balas_sukses'): ?>
            <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Balasan berhasil disimpan.</div>
        <?php elseif ($pesan === 'update_sukses'): ?>
            <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Status berhasil diperbarui.</div>
        <?php endif; ?>

        <!-- STATISTIK -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon merah"><i class="fas fa-inbox"></i></div>
                <div><div class="stat-val"><?= $stat_total ?></div><div class="stat-lbl">Total Pesan</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-envelope"></i></div>
                <div><div class="stat-val"><?= $stat_belum ?></div><div class="stat-lbl">Belum Dibaca</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon merah"><i class="fas fa-exclamation-circle"></i></div>
                <div><div class="stat-val"><?= $stat_kritik ?></div><div class="stat-lbl">Kritik</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon hijau"><i class="fas fa-lightbulb"></i></div>
                <div><div class="stat-val"><?= $stat_saran ?></div><div class="stat-lbl">Saran</div></div>
            </div>
        </div>

        <!-- TOOLBAR FILTER -->
        <form method="GET" action="kritik_saran_admin.php">
            <div class="toolbar">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="search-input" placeholder="Cari nama, email, atau isi pesan..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <select name="kategori" class="filter-select">
                    <option value="">Semua Kategori</option>
                    <option value="kritik"     <?= $filter_kat === 'kritik'     ? 'selected' : '' ?>>Kritik</option>
                    <option value="saran"      <?= $filter_kat === 'saran'      ? 'selected' : '' ?>>Saran</option>
                    <option value="pertanyaan" <?= $filter_kat === 'pertanyaan' ? 'selected' : '' ?>>Pertanyaan</option>
                </select>
                <select name="status" class="filter-select">
                    <option value="">Semua Status</option>
                    <option value="belum" <?= $filter_baca === 'belum' ? 'selected' : '' ?>>Belum Dibaca</option>
                    <option value="sudah" <?= $filter_baca === 'sudah' ? 'selected' : '' ?>>Sudah Dibaca</option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="kritik_saran_admin.php" class="btn btn-ghost"><i class="fas fa-redo"></i> Reset</a>
            </div>
        </form>

        <!-- TABEL DATA -->
        <div class="card">
            <?php if (mysqli_num_rows($q_data) === 0): ?>
                <div class="empty-state">
                    <i class="fas fa-comment-slash"></i>
                    <p>Tidak ada pesan yang ditemukan.</p>
                </div>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pengirim</th>
                            <th>Kategori</th>
                            <th>Pesan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = $offset + 1;
                    while ($baris = mysqli_fetch_assoc($q_data)):
                        $tgl      = date('d M Y, H:i', strtotime($baris['tanggal']));
                        $sudah    = (bool) $baris['sudah_dibaca'];
                        $rowClass = $sudah ? '' : 'belum-baca';
                        // Encode data untuk modal
                        $data_js  = htmlspecialchars(json_encode([
                            'id'      => $baris['id'],
                            'nama'    => $baris['nama'],
                            'email'   => $baris['email'],
                            'kategori'=> $baris['kategori'],
                            'pesan'   => $baris['pesan'],
                            'balasan' => $baris['balasan'] ?? '',
                            'tanggal' => $tgl,
                        ]), ENT_QUOTES);
                    ?>
                        <tr class="<?= $rowClass ?>">
                            <td style="font-weight:700; color:var(--abu-sedang);"><?= $no++ ?></td>
                            <td>
                                <div style="font-weight:700; font-size:13px;"><?= htmlspecialchars($baris['nama']) ?></div>
                                <div style="font-size:11px; color:var(--abu-sedang);"><?= htmlspecialchars($baris['email']) ?></div>
                            </td>
                            <td>
                                <span class="badge badge-<?= $baris['kategori'] ?>"><?= ucfirst($baris['kategori']) ?></span>
                            </td>
                            <td class="pesan-cell">
                                <div class="pesan-teks"><?= htmlspecialchars($baris['pesan']) ?></div>
                                <?php if (!empty($baris['balasan'])): ?>
                                    <div class="balasan-chip"><i class="fas fa-reply"></i> Sudah dibalas</div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px; color:var(--abu-sedang); white-space:nowrap;"><?= $tgl ?></td>
                            <td>
                                <?php if ($sudah): ?>
                                    <span class="badge badge-sudah"><i class="fas fa-check" style="margin-right:4px;"></i> Dibaca</span>
                                <?php else: ?>
                                    <span class="badge badge-belum"><i class="fas fa-dot-circle" style="margin-right:4px;"></i> Belum</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="aksi-grup">
                                    <button class="btn btn-sm btn-ghost" onclick='bukaModalDetail(<?= $data_js ?>)' title="Lihat detail & balas">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="kritik_saran_admin.php?toggle=<?= $baris['id'] ?>" class="btn btn-sm btn-ghost" title="<?= $sudah ? 'Tandai belum dibaca' : 'Tandai sudah dibaca' ?>">
                                        <i class="fas fa-<?= $sudah ? 'envelope' : 'envelope-open' ?>"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" onclick="konfirmasiHapus(<?= $baris['id'] ?>, '<?= addslashes(htmlspecialchars($baris['nama'])) ?>')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINASI -->
            <?php if ($total_pg > 1): ?>
            <div class="paginasi">
                <?php
                $q_str = http_build_query(array_filter(['search'=>$search,'kategori'=>$filter_kat,'status'=>$filter_baca]));
                for ($p = 1; $p <= $total_pg; $p++):
                    $link = "kritik_saran_admin.php?page=$p" . ($q_str ? "&$q_str" : '');
                ?>
                    <?php if ($p === $page): ?>
                        <span class="aktif"><?= $p ?></span>
                    <?php else: ?>
                        <a href="<?= $link ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>

    </div><!-- /content -->
</main>

<!-- ══ MODAL DETAIL & BALAS ══ -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-title"><i class="fas fa-envelope-open-text"></i> Detail Pesan</div>
            <button class="modal-close" onclick="tutupModal('modalDetail')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="detail-box">
                <div class="label">Pengirim</div>
                <div class="val" id="d_nama">—</div>
                <div style="font-size:12px; color:var(--abu-sedang); margin-top:2px;" id="d_email">—</div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                <div class="detail-box" style="margin-bottom:0;">
                    <div class="label">Kategori</div>
                    <div class="val" id="d_kategori">—</div>
                </div>
                <div class="detail-box" style="margin-bottom:0;">
                    <div class="label">Tanggal</div>
                    <div class="val" id="d_tanggal">—</div>
                </div>
            </div>
            <div class="detail-box">
                <div class="label">Isi Pesan</div>
                <div class="val" id="d_pesan" style="white-space:pre-wrap;">—</div>
            </div>

            <!-- Form balas -->
            <form method="POST" action="kritik_saran_admin.php" id="formBalas">
                <input type="hidden" name="aksi"     value="balas">
                <input type="hidden" name="id_pesan" id="d_id">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-reply"></i> Balasan Admin</label>
                    <textarea name="balasan" id="d_balasan" class="form-textarea" placeholder="Tulis balasan untuk pengirim..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" onclick="tutupModal('modalDetail')">Tutup</button>
            <button class="btn btn-primary" onclick="document.getElementById('formBalas').submit()">
                <i class="fas fa-paper-plane"></i> Kirim Balasan
            </button>
        </div>
    </div>
</div>

<!-- ══ MODAL HAPUS ══ -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal" style="max-width:400px;">
        <div class="modal-body" style="padding:32px 28px; text-align:center;">
            <div style="width:60px;height:60px;background:#f8d7da;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px;color:#DC3545;">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h3 style="font-size:18px;font-weight:800;margin-bottom:8px;">Hapus Pesan?</h3>
            <p style="font-size:14px;color:var(--teks-sedang);margin-bottom:4px;">Pesan dari:</p>
            <p style="font-size:15px;font-weight:700;color:var(--merah);margin-bottom:12px;" id="hapus_nama">—</p>
            <p style="font-size:13px;color:var(--abu-sedang);">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-foot" style="justify-content:center;">
            <button class="btn btn-ghost" onclick="tutupModal('modalHapus')"><i class="fas fa-times"></i> Batal</button>
            <a href="#" id="hapus_link" class="btn btn-danger"><i class="fas fa-trash"></i> Ya, Hapus</a>
        </div>
    </div>
</div>

<script>
function bukaModal(id)  { document.getElementById(id).classList.add('show'); document.body.style.overflow='hidden'; }
function tutupModal(id) { document.getElementById(id).classList.remove('show'); document.body.style.overflow=''; }

document.querySelectorAll('.modal-overlay').forEach(function(el) {
    el.addEventListener('click', function(e) { if (e.target === el) tutupModal(el.id); });
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.show').forEach(function(el){ tutupModal(el.id); });
});

function bukaModalDetail(data) {
    document.getElementById('d_id').value      = data.id;
    document.getElementById('d_nama').textContent    = data.nama;
    document.getElementById('d_email').textContent   = data.email;
    document.getElementById('d_kategori').textContent = data.kategori.charAt(0).toUpperCase() + data.kategori.slice(1);
    document.getElementById('d_tanggal').textContent  = data.tanggal;
    document.getElementById('d_pesan').textContent    = data.pesan;
    document.getElementById('d_balasan').value        = data.balasan || '';
    bukaModal('modalDetail');
}

function konfirmasiHapus(id, nama) {
    document.getElementById('hapus_nama').textContent = nama;
    document.getElementById('hapus_link').href = 'kritik_saran_admin.php?hapus=' + id;
    bukaModal('modalHapus');
}

// Auto-hilangkan notifikasi
setTimeout(function() {
    var notif = document.querySelector('.notif');
    if (notif) {
        notif.style.opacity = '0';
        notif.style.transform = 'translateY(-8px)';
        notif.style.transition = 'all .4s ease';
        setTimeout(function(){ notif.remove(); }, 400);
    }
}, 4000);
</script>

</body>
</html>
