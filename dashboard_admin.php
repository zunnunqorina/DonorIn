<?php
include 'koneksi.php';

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login_admin.php");
    exit;
}

// Ambil nama admin dari session
$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// Total pasien
$q_pasien = mysqli_query($conn, "SELECT COUNT(*) as total FROM user WHERE role = 'pasien'");
$total_pasien = mysqli_fetch_assoc($q_pasien)['total'] ?? 0;

// Total pendonor
$q_pendonor = mysqli_query($conn, "SELECT COUNT(*) as total FROM user WHERE role = 'pendonor'");
$total_pendonor = mysqli_fetch_assoc($q_pendonor)['total'] ?? 0;

// Total relawan
$q_relawan = mysqli_query($conn, "SELECT COUNT(*) as total FROM relawan");
$total_relawan = mysqli_fetch_assoc($q_relawan)['total'] ?? 0;

// Total event donor
$q_event_donor = mysqli_query($conn, "SELECT COUNT(*) as total FROM event_donor WHERE status = 'aktif'");
$total_event_donor = mysqli_fetch_assoc($q_event_donor)['total'] ?? 0;

// Total event sosialisasi
$q_event_sosial = mysqli_query($conn, "SELECT COUNT(*) as total FROM event_sosialisasi WHERE status = 'aktif'");
$total_event_sosial = mysqli_fetch_assoc($q_event_sosial)['total'] ?? 0;

// Total kritik & saran
$q_ks = mysqli_query($conn, "SELECT COUNT(*) as total FROM kritik_saran");
$total_ks = mysqli_fetch_assoc($q_ks)['total'] ?? 0;

// Event donor mendatang (5 terdekat)
$q_upcoming_donor = mysqli_query($conn, "
    SELECT * FROM event_donor
    WHERE tanggal >= CURDATE() AND status = 'aktif'
    ORDER BY tanggal ASC, jam_mulai ASC
    LIMIT 5
");

// Event sosialisasi mendatang (5 terdekat)
$q_upcoming_sosial = mysqli_query($conn, "
    SELECT * FROM event_sosialisasi
    WHERE tanggal >= CURDATE() AND status = 'aktif'
    ORDER BY tanggal ASC, jam_mulai ASC
    LIMIT 5
");

// Relawan terbaru (5 terakhir)
$q_relawan_baru = mysqli_query($conn, "
    SELECT * FROM relawan
    ORDER BY tanggal_daftar DESC
    LIMIT 5
");

// Kritik & saran terbaru (5 terakhir)
$q_ks_baru = mysqli_query($conn, "
    SELECT * FROM kritik_saran
    ORDER BY tanggal DESC
    LIMIT 5
");

// Sebaran golongan darah relawan
$q_goldar = mysqli_query($conn, "
    SELECT goldar, COUNT(*) as total
    FROM relawan
    GROUP BY goldar
    ORDER BY total DESC
");
$goldar_map = ['A' => 0, 'B' => 0, 'O' => 0, 'AB' => 0];
while ($row = mysqli_fetch_assoc($q_goldar)) {
    if (isset($goldar_map[$row['goldar']])) {
        $goldar_map[$row['goldar']] = $row['total'];
    }
}

// Total event donor bulan ini
$q_event_bulan = mysqli_query($conn, "
    SELECT COUNT(*) as total FROM event_donor
    WHERE MONTH(tanggal) = MONTH(CURDATE())
    AND YEAR(tanggal) = YEAR(CURDATE())
");
$total_event_bulan = mysqli_fetch_assoc($q_event_bulan)['total'] ?? 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — DonorIn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --merah:        #C0001A;
            --merah-gelap:  #8B0012;
            --merah-terang: #E8001F;
            --merah-muda:   #FFE5E9;
            --merah-tipis:  #FFF5F6;
            --putih:        #FFFFFF;
            --abu-terang:   #F7F8FA;
            --abu:          #E8EAED;
            --abu-sedang:   #9DA3AE;
            --teks-gelap:   #1A1A2E;
            --teks-sedang:  #4A4A6A;
            --sidebar-w:    260px;
            --shadow-sm:    0 1px 3px rgba(192,0,26,.08), 0 1px 2px rgba(0,0,0,.05);
            --shadow-md:    0 4px 16px rgba(192,0,26,.10), 0 2px 6px rgba(0,0,0,.06);
            --shadow-lg:    0 10px 40px rgba(192,0,26,.15), 0 4px 16px rgba(0,0,0,.08);
            --radius:       14px;
            --radius-sm:    8px;
            --trans:        all .25s cubic-bezier(.4,0,.2,1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--abu-terang);
            color: var(--teks-gelap);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: linear-gradient(175deg, #8B0012 0%, #C0001A 55%, #A0001A 100%);
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 4px 0 24px rgba(139,0,18,.35);
        }

        .sidebar-brand {
            padding: 28px 24px 24px;
            border-bottom: 1px solid rgba(255,255,255,.12);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 42px; height: 42px;
            background: var(--putih);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .brand-icon i { color: var(--merah); font-size: 20px; }

        .brand-name {
            font-family: 'Fraunces', serif;
            font-size: 22px; font-weight: 900;
            color: var(--putih); line-height: 1; letter-spacing: -.5px;
        }
        .brand-sub {
            font-size: 10px; color: rgba(255,255,255,.6);
            font-weight: 500; letter-spacing: 1.5px;
            text-transform: uppercase; margin-top: 3px;
        }

        .sidebar-nav { flex: 1; padding: 20px 0; overflow-y: auto; }

        .nav-section { padding: 0 14px; margin-bottom: 4px; }

        .nav-label {
            font-size: 10px; font-weight: 700; letter-spacing: 1.8px;
            text-transform: uppercase; color: rgba(255,255,255,.4);
            padding: 12px 10px 6px;
        }

        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 14px; border-radius: var(--radius-sm);
            color: rgba(255,255,255,.75); text-decoration: none;
            font-size: 14px; font-weight: 500;
            transition: var(--trans); margin-bottom: 2px;
        }
        .nav-item:hover { background: rgba(255,255,255,.12); color: var(--putih); transform: translateX(3px); }
        .nav-item.active { background: var(--putih); color: var(--merah); font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,.2); }
        .nav-item.active i { color: var(--merah); }
        .nav-item i { width: 18px; text-align: center; font-size: 15px; }

        .nav-badge {
            margin-left: auto; background: rgba(255,255,255,.2);
            color: var(--putih); font-size: 10px; font-weight: 700;
            padding: 2px 7px; border-radius: 20px;
        }
        .nav-item.active .nav-badge { background: var(--merah-muda); color: var(--merah); }

        .sidebar-footer { padding: 18px 14px; border-top: 1px solid rgba(255,255,255,.12); }

        .sidebar-user {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px; border-radius: var(--radius-sm);
            background: rgba(255,255,255,.08); margin-bottom: 10px;
        }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--putih); display: flex; align-items: center; justify-content: center;
            font-size: 16px; color: var(--merah); font-weight: 700; flex-shrink: 0;
        }
        .user-info { flex: 1; min-width: 0; }
        .user-name { font-size: 13px; font-weight: 600; color: var(--putih); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 11px; color: rgba(255,255,255,.5); }

        .btn-logout {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 10px; border-radius: var(--radius-sm);
            background: rgba(255,255,255,.1); color: rgba(255,255,255,.8);
            font-size: 13px; font-weight: 600; text-decoration: none;
            border: 1px solid rgba(255,255,255,.15); transition: var(--trans); cursor: pointer;
        }
        .btn-logout:hover { background: rgba(255,255,255,.2); color: var(--putih); }

        /* ── MAIN ── */
        .main { margin-left: var(--sidebar-w); flex: 1; min-width: 0; display: flex; flex-direction: column; }

        /* ── TOPBAR ── */
        .topbar {
            background: var(--putih); border-bottom: 1px solid var(--abu);
            padding: 0 32px; height: 68px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50; box-shadow: var(--shadow-sm);
        }
        .topbar-title { font-size: 18px; font-weight: 800; color: var(--teks-gelap); line-height: 1; }
        .topbar-breadcrumb { font-size: 12px; color: var(--abu-sedang); margin-top: 3px; }
        .topbar-breadcrumb span { color: var(--merah); font-weight: 600; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }

        .topbar-btn {
            width: 38px; height: 38px; border-radius: 10px;
            border: 1px solid var(--abu); background: var(--putih);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--teks-sedang); font-size: 15px;
            transition: var(--trans); text-decoration: none; position: relative;
        }
        .topbar-btn:hover { background: var(--merah-tipis); border-color: var(--merah-muda); color: var(--merah); }
        .notif-dot { position: absolute; top: 7px; right: 7px; width: 7px; height: 7px; border-radius: 50%; background: var(--merah); border: 2px solid var(--putih); }

        .date-chip {
            display: flex; align-items: center; gap: 7px;
            padding: 8px 14px; border-radius: 10px;
            background: var(--merah-tipis); border: 1px solid var(--merah-muda);
            font-size: 13px; font-weight: 600; color: var(--merah);
        }

        .content { padding: 28px 32px 40px; flex: 1; }

        .welcome-banner {
            background: linear-gradient(135deg, var(--merah-gelap) 0%, var(--merah) 60%, var(--merah-terang) 100%);
            border-radius: var(--radius); padding: 28px 32px; margin-bottom: 28px;
            display: flex; align-items: center; justify-content: space-between;
            position: relative; overflow: hidden; box-shadow: var(--shadow-lg);
        }
        .welcome-banner::before { content: ''; position: absolute; top: -40px; right: -40px; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,.06); }
        .welcome-banner::after  { content: ''; position: absolute; bottom: -60px; right: 80px; width: 150px; height: 150px; border-radius: 50%; background: rgba(255,255,255,.04); }
        .welcome-text h2 { font-family: 'Fraunces', serif; font-size: 26px; font-weight: 900; color: var(--putih); line-height: 1.2; margin-bottom: 6px; }
        .welcome-text p  { font-size: 14px; color: rgba(255,255,255,.75); }
        .welcome-icon { width: 80px; height: 80px; background: rgba(255,255,255,.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; flex-shrink: 0; z-index: 1; }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-bottom: 20px; }
        .stats-grid-2 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-bottom: 28px; }

        .stat-card {
            background: var(--putih); border-radius: var(--radius);
            padding: 22px 24px; display: flex; flex-direction: column; gap: 14px;
            border: 1px solid var(--abu); transition: var(--trans);
            box-shadow: var(--shadow-sm); position: relative; overflow: hidden;
        }
        .stat-card::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: var(--merah); transform: scaleX(0); transform-origin: left; transition: var(--trans); }
        .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); border-color: var(--merah-muda); }
        .stat-card:hover::after { transform: scaleX(1); }

        .stat-header { display: flex; align-items: center; justify-content: space-between; }
        .stat-label { font-size: 12px; font-weight: 600; color: var(--abu-sedang); text-transform: uppercase; letter-spacing: .8px; }
        .stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 17px; }
        .stat-icon.merah  { background: var(--merah-muda); color: var(--merah); }
        .stat-icon.ungu   { background: #F0F0FF; color: #5B4FCC; }
        .stat-icon.hijau  { background: #E8F8F0; color: #1B8A4E; }
        .stat-icon.kuning { background: #FFF8E6; color: #D4900A; }
        .stat-icon.biru   { background: #EDF4FF; color: #2563EB; }
        .stat-icon.pink   { background: #FFF0F7; color: #BE185D; }

        .stat-value { font-family: 'Fraunces', serif; font-size: 38px; font-weight: 900; color: var(--teks-gelap); line-height: 1; }
        .stat-footer { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--abu-sedang); }

        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .section-title { font-size: 16px; font-weight: 800; color: var(--teks-gelap); display: flex; align-items: center; gap: 8px; }
        .section-title i { color: var(--merah); }

        .btn-lihat {
            font-size: 12px; font-weight: 700; color: var(--merah); text-decoration: none;
            display: flex; align-items: center; gap: 5px;
            padding: 6px 14px; border-radius: var(--radius-sm);
            border: 1px solid var(--merah-muda); background: var(--merah-tipis); transition: var(--trans);
        }
        .btn-lihat:hover { background: var(--merah); color: var(--putih); border-color: var(--merah); }

        .two-col   { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .three-col { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px; }

        .card { background: var(--putih); border-radius: var(--radius); border: 1px solid var(--abu); box-shadow: var(--shadow-sm); overflow: hidden; }
        .card-header { padding: 18px 22px 16px; border-bottom: 1px solid var(--abu); }
        .card-body { padding: 0; }

        .tbl { width: 100%; border-collapse: collapse; }
        .tbl thead th { padding: 10px 20px; text-align: left; font-size: 11px; font-weight: 700; color: var(--abu-sedang); text-transform: uppercase; letter-spacing: .8px; background: var(--abu-terang); border-bottom: 1px solid var(--abu); }
        .tbl tbody tr { border-bottom: 1px solid var(--abu); transition: var(--trans); }
        .tbl tbody tr:last-child { border-bottom: none; }
        .tbl tbody tr:hover { background: var(--merah-tipis); }
        .tbl tbody td { padding: 12px 20px; font-size: 13px; color: var(--teks-gelap); }

        .tbl-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--merah-muda); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: var(--merah); flex-shrink: 0; }
        .tbl-name { display: flex; align-items: center; gap: 10px; }
        .tbl-name-text { font-weight: 600; font-size: 13px; }
        .tbl-name-sub  { font-size: 11px; color: var(--abu-sedang); margin-top: 1px; }

        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-merah  { background: var(--merah-muda);  color: var(--merah); }
        .badge-hijau  { background: #E8F8F0; color: #1B8A4E; }
        .badge-kuning { background: #FFF8E6; color: #D4900A; }
        .badge-biru   { background: #EDF4FF; color: #2563EB; }
        .badge-abu    { background: #F1F3F5; color: #6B7280; }
        .badge-pink   { background: #FFF0F7; color: #BE185D; }

        .goldar-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; padding: 20px; }
        .goldar-item { background: var(--merah-tipis); border: 1px solid var(--merah-muda); border-radius: var(--radius-sm); padding: 14px 10px; text-align: center; transition: var(--trans); }
        .goldar-item:hover { background: var(--merah-muda); border-color: var(--merah); transform: scale(1.03); }
        .goldar-type  { font-family: 'Fraunces', serif; font-size: 24px; font-weight: 900; color: var(--merah); line-height: 1; margin-bottom: 4px; }
        .goldar-total { font-size: 12px; font-weight: 600; color: var(--teks-sedang); }

        .event-list { padding: 4px 0; }
        .event-item { display: flex; align-items: center; gap: 14px; padding: 13px 20px; border-bottom: 1px solid var(--abu); transition: var(--trans); }
        .event-item:last-child { border-bottom: none; }
        .event-item:hover { background: var(--merah-tipis); }

        .event-date-box { width: 46px; height: 50px; border-radius: 10px; background: var(--merah); display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; color: var(--putih); }
        .event-date-box .day   { font-family: 'Fraunces', serif; font-size: 20px; font-weight: 900; line-height: 1; }
        .event-date-box .month { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; opacity: .8; }

        .event-info { flex: 1; min-width: 0; }
        .event-name  { font-size: 13px; font-weight: 700; color: var(--teks-gelap); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .event-meta  { font-size: 11px; color: var(--abu-sedang); margin-top: 3px; display: flex; gap: 10px; flex-wrap: wrap; }
        .event-meta i { color: var(--merah); margin-right: 2px; }
        .event-time  { font-size: 11px; font-weight: 600; color: var(--merah); white-space: nowrap; }

        .highlight-card {
            background: linear-gradient(135deg, #1A1A2E 0%, #2D1B3D 100%);
            border-radius: var(--radius); padding: 22px 24px;
            border: 1px solid var(--abu); box-shadow: var(--shadow-sm);
            display: flex; align-items: center; gap: 18px;
        }
        .highlight-icon { width: 56px; height: 56px; border-radius: 14px; background: var(--merah); display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--putih); flex-shrink: 0; }
        .highlight-info h3 { font-family: 'Fraunces', serif; font-size: 32px; font-weight: 900; color: var(--putih); line-height: 1; }
        .highlight-info p  { font-size: 12px; color: rgba(255,255,255,.5); margin-top: 4px; }

        .ks-item { padding: 14px 20px; border-bottom: 1px solid var(--abu); transition: var(--trans); }
        .ks-item:last-child { border-bottom: none; }
        .ks-item:hover { background: var(--merah-tipis); }
        .ks-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
        .ks-nama   { font-size: 13px; font-weight: 700; color: var(--teks-gelap); }
        .ks-pesan  { font-size: 12px; color: var(--teks-sedang); line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

        .empty-state { text-align: center; padding: 32px 20px; color: var(--abu-sedang); }
        .empty-state i { font-size: 36px; color: var(--merah-muda); display: block; margin-bottom: 10px; }
        .empty-state p { font-size: 13px; }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
        .welcome-banner { animation: fadeUp .3s ease both; }
        .stat-card { animation: fadeUp .4s ease both; }
        .stat-card:nth-child(1) { animation-delay: .05s; }
        .stat-card:nth-child(2) { animation-delay: .10s; }
        .stat-card:nth-child(3) { animation-delay: .15s; }
        .card, .highlight-card { animation: fadeUp .35s ease both; animation-delay: .2s; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--merah-muda); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--merah); }

        @media (max-width: 1100px) { .stats-grid, .stats-grid-2 { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 860px)  { .two-col, .three-col { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<!-- ══════════════ SIDEBAR ══════════════ -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-tint"></i></div>
        <div>
            <div class="brand-name">DonorIn</div>
            <div class="brand-sub">Admin Panel</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-label">Utama</div>
            <a href="dashboard_admin.php" class="nav-item active">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Pengguna</div>
            <a href="admin/pasien.php" class="nav-item">
                <i class="fas fa-user-injured"></i> Pasien
                <span class="nav-badge"><?= $total_pasien ?></span>
            </a>
            <a href="pendonor_admin.php" class="nav-item">
                <i class="fas fa-hand-holding-heart"></i> Pendonor
                <span class="nav-badge"><?= $total_pendonor ?></span>
            </a>
            <a href="admin/relawan.php" class="nav-item">
                <i class="fas fa-people-carry-box"></i> Relawan
                <span class="nav-badge"><?= $total_relawan ?></span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Event</div>
            <a href="admin/event_donor.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i> Event Donor Darah
                <span class="nav-badge"><?= $total_event_donor ?></span>
            </a>
            <a href="admin/event_sosialisasi.php" class="nav-item">
                <i class="fas fa-bullhorn"></i> Event Sosialisasi
                <span class="nav-badge"><?= $total_event_sosial ?></span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Lainnya</div>
            <a href="admin/kritik_saran.php" class="nav-item">
                <i class="fas fa-comments"></i> Kritik & Saran
                <span class="nav-badge"><?= $total_ks ?></span>
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($admin_username, 0, 1)) ?></div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($admin_username) ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        <a href="logout.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </div>
</aside>

<!-- ══════════════ MAIN ══════════════ -->
<main class="main">

    <!-- TOPBAR -->
    <header class="topbar">
        <div>
            <div class="topbar-title">Dashboard</div>
            <div class="topbar-breadcrumb">DonorIn / <span>Beranda</span></div>
        </div>
        <div class="topbar-right">
            <div class="date-chip">
                <i class="fas fa-calendar-day"></i>
                <?= date('d M Y') ?>
            </div>
            <a href="admin/kritik_saran.php" class="topbar-btn" title="Kritik & Saran">
                <i class="fas fa-bell"></i>
                <?php if ($total_ks > 0): ?><span class="notif-dot"></span><?php endif; ?>
            </a>
        </div>
    </header>

    <!-- CONTENT -->
    <div class="content">

        <!-- WELCOME -->
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>Selamat Datang, <?= htmlspecialchars($admin_username) ?>! 👋</h2>
                <p>Kelola sistem donor darah <strong>DonorIn</strong> dengan mudah dari panel ini. Hari ini, <?= date('l, d F Y') ?>.</p>
            </div>
            <div class="welcome-icon">🩸</div>
        </div>

        <!-- STAT ROW 1: Pengguna & Relawan -->
        <div class="stats-grid">
            <!-- Pasien -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total Pasien</span>
                    <div class="stat-icon biru"><i class="fas fa-user-injured"></i></div>
                </div>
                <div class="stat-value"><?= $total_pasien ?></div>
                <div class="stat-footer">
                    <i class="fas fa-circle-dot" style="color:#2563EB;font-size:8px;"></i>
                    Pengguna role pasien
                </div>
            </div>

            <!-- Pendonor -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total Pendonor</span>
                    <div class="stat-icon merah"><i class="fas fa-hand-holding-heart"></i></div>
                </div>
                <div class="stat-value"><?= $total_pendonor ?></div>
                <div class="stat-footer">
                    <i class="fas fa-circle-dot" style="color:var(--merah);font-size:8px;"></i>
                    Pengguna role pendonor
                </div>
            </div>

            <!-- Relawan -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total Relawan</span>
                    <div class="stat-icon ungu"><i class="fas fa-people-carry-box"></i></div>
                </div>
                <div class="stat-value"><?= $total_relawan ?></div>
                <div class="stat-footer">
                    <i class="fas fa-circle-dot" style="color:#5B4FCC;font-size:8px;"></i>
                    Relawan terdaftar
                    <!-- <?php if ($kota_terbanyak): ?>
                    &nbsp;·&nbsp; Terbanyak: <strong><?= htmlspecialchars($kota_terbanyak['kota']) ?></strong>
                    <?php endif; ?> -->
                </div>
            </div>
        </div>

        <!-- STAT ROW 2: Event & Feedback -->
        <div class="stats-grid-2">
            <!-- Event Donor Aktif -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Event Donor Aktif</span>
                    <div class="stat-icon hijau"><i class="fas fa-calendar-alt"></i></div>
                </div>
                <div class="stat-value"><?= $total_event_donor ?></div>
                <div class="stat-footer">
                    <i class="fas fa-circle-dot" style="color:#1B8A4E;font-size:8px;"></i>
                    <?= $total_event_bulan ?> event bulan ini
                </div>
            </div>

            <!-- Event Sosialisasi Aktif -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Sosialisasi Aktif</span>
                    <div class="stat-icon kuning"><i class="fas fa-bullhorn"></i></div>
                </div>
                <div class="stat-value"><?= $total_event_sosial ?></div>
                <div class="stat-footer">
                    <i class="fas fa-circle-dot" style="color:#D4900A;font-size:8px;"></i>
                    Event sosialisasi berjalan
                </div>
            </div>

            <!-- Kritik & Saran -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Kritik & Saran</span>
                    <div class="stat-icon pink"><i class="fas fa-comments"></i></div>
                </div>
                <div class="stat-value"><?= $total_ks ?></div>
                <div class="stat-footer">
                    <i class="fas fa-circle-dot" style="color:#BE185D;font-size:8px;"></i>
                    Masukan dari masyarakat
                </div>
            </div>
        </div>

        <!-- ROW: GOLDAR RELAWAN -->
        <div class="section-header">
            <div class="section-title"><i class="fas fa-dna"></i> Sebaran Golongan Darah Relawan</div>
            <a href="admin/relawan.php" class="btn-lihat"><i class="fas fa-arrow-right"></i> Lihat Relawan</a>
        </div>
        <div class="card" style="margin-bottom:24px;">
            <div class="card-body">
                <div class="goldar-grid">
                    <?php foreach (['A','B','O','AB'] as $g): ?>
                    <div class="goldar-item">
                        <div class="goldar-type"><?= $g ?></div>
                        <div class="goldar-total"><?= $goldar_map[$g] ?> orang</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ROW: EVENT DONOR & SOSIALISASI MENDATANG -->
        <div class="two-col">
            <!-- Event Donor Mendatang -->
            <div>
                <div class="section-header">
                    <div class="section-title"><i class="fas fa-calendar-alt"></i> Event Donor Mendatang</div>
                    <a href="admin/event_donor.php" class="btn-lihat"><i class="fas fa-arrow-right"></i> Semua</a>
                </div>
                <div class="card">
                    <div class="card-body event-list">
                        <?php if (mysqli_num_rows($q_upcoming_donor) > 0):
                            while ($row = mysqli_fetch_assoc($q_upcoming_donor)):
                                $tgl = strtotime($row['tanggal']); ?>
                        <div class="event-item">
                            <div class="event-date-box">
                                <span class="day"><?= date('d', $tgl) ?></span>
                                <span class="month"><?= date('M', $tgl) ?></span>
                            </div>
                            <div class="event-info">
                                <div class="event-name"><?= htmlspecialchars($row['judul']) ?></div>
                                <div class="event-meta">
                                    <span><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($row['kota']) ?></span>
                                    <span><i class="fas fa-users"></i>Kuota: <?= $row['kuota'] ?></span>
                                </div>
                            </div>
                            <div class="event-time">
                                <i class="fas fa-clock" style="font-size:10px;"></i>
                                <?= substr($row['jam_mulai'], 0, 5) ?>
                            </div>
                        </div>
                        <?php endwhile; else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <p>Tidak ada event donor mendatang</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Event Sosialisasi Mendatang -->
            <div>
                <div class="section-header">
                    <div class="section-title"><i class="fas fa-bullhorn"></i> Sosialisasi Mendatang</div>
                    <a href="admin/event_sosialisasi.php" class="btn-lihat"><i class="fas fa-arrow-right"></i> Semua</a>
                </div>
                <div class="card">
                    <div class="card-body event-list">
                        <?php if (mysqli_num_rows($q_upcoming_sosial) > 0):
                            while ($row = mysqli_fetch_assoc($q_upcoming_sosial)):
                                $tgl = strtotime($row['tanggal']); ?>
                        <div class="event-item">
                            <div class="event-date-box" style="background:#1B8A4E;">
                                <span class="day"><?= date('d', $tgl) ?></span>
                                <span class="month"><?= date('M', $tgl) ?></span>
                            </div>
                            <div class="event-info">
                                <div class="event-name"><?= htmlspecialchars($row['judul']) ?></div>
                                <div class="event-meta">
                                    <span><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($row['kota']) ?></span>
                                    <span><i class="fas fa-user-tie"></i><?= htmlspecialchars($row['pembicara'] ?? '-') ?></span>
                                </div>
                            </div>
                            <div class="event-time" style="color:#1B8A4E;">
                                <i class="fas fa-clock" style="font-size:10px;"></i>
                                <?= substr($row['jam_mulai'], 0, 5) ?>
                            </div>
                        </div>
                        <?php endwhile; else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bullhorn"></i>
                            <p>Tidak ada sosialisasi mendatang</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW: RELAWAN BARU & KRITIK SARAN TERBARU -->
        <div class="two-col">
            <!-- Relawan Terbaru -->
            <div>
                <div class="section-header">
                    <div class="section-title"><i class="fas fa-people-carry-box"></i> Relawan Terbaru</div>
                    <a href="admin/relawan.php" class="btn-lihat"><i class="fas fa-arrow-right"></i> Semua</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <?php if (mysqli_num_rows($q_relawan_baru) > 0): ?>
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Gol. Darah</th>
                                    <th>Kota</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while ($row = mysqli_fetch_assoc($q_relawan_baru)): ?>
                                <tr>
                                    <td>
                                        <div class="tbl-name">
                                            <div class="tbl-avatar"><?= strtoupper(substr($row['nama'], 0, 1)) ?></div>
                                            <div>
                                                <div class="tbl-name-text"><?= htmlspecialchars($row['nama']) ?></div>
                                                <div class="tbl-name-sub"><?= htmlspecialchars($row['no_hp']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-merah"><?= htmlspecialchars($row['goldar']) ?></span></td>
                                    <td style="font-size:12px;color:var(--teks-sedang);"><?= htmlspecialchars($row['kota']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>Belum ada relawan terdaftar</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Kritik & Saran Terbaru -->
            <div>
                <div class="section-header">
                    <div class="section-title"><i class="fas fa-comments"></i> Kritik & Saran Terbaru</div>
                    <a href="admin/kritik_saran.php" class="btn-lihat"><i class="fas fa-arrow-right"></i> Semua</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <?php if (mysqli_num_rows($q_ks_baru) > 0):
                            while ($row = mysqli_fetch_assoc($q_ks_baru)):
                                $badge_map = ['kritik' => 'badge-merah', 'saran' => 'badge-hijau', 'pertanyaan' => 'badge-biru'];
                                $badge = $badge_map[$row['kategori']] ?? 'badge-abu';
                        ?>
                        <div class="ks-item">
                            <div class="ks-header">
                                <span class="ks-nama"><?= htmlspecialchars($row['nama']) ?></span>
                                <span class="badge <?= $badge ?>"><?= ucfirst($row['kategori']) ?></span>
                            </div>
                            <div class="ks-pesan"><?= htmlspecialchars($row['pesan']) ?></div>
                        </div>
                        <?php endwhile; else: ?>
                        <div class="empty-state">
                            <i class="fas fa-comment-slash"></i>
                            <p>Belum ada kritik & saran masuk</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /content -->
</main>

</body>
</html>