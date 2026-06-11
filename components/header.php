<?php
// header.php — DonorIn Global Header
// Dipanggil di awal setiap halaman publik dengan: include 'path/to/components/header.php';

$admin_login    = isset($_SESSION['admin_login'])    && $_SESSION['admin_login']    === true;
$pendonor_login = isset($_SESSION['pendonor_login']) && $_SESSION['pendonor_login'] === true;
$pasien_login   = isset($_SESSION['pasien_login'])   && $_SESSION['pasien_login']   === true;

// Halaman aktif untuk highlight nav
$halaman_aktif = $halaman_aktif ?? '';

// Deteksi prefix path
$depth = substr_count($_SERVER['PHP_SELF'], '/') - 1;
$prefix = str_repeat('../', max(0, $depth - 2));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'DonorIn — Sistem Informasi Donor Darah' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ══════════════════════════════════════════════════════
           DonorIn Global CSS — dipakai semua halaman publik
           ══════════════════════════════════════════════════════ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --merah:        #8B0000;
            --merah-gelap:  #6B0000;
            --merah-muda:   #FFF3F3;
            --teks:         #111827;
            --teks-sub:     #6B7280;
            --border:       #E5E7EB;
            --bg:           #F9FAFB;
            --putih:        #fff;
            --radius:       14px;
            --shadow:       0 4px 24px rgba(0,0,0,0.07);
        }

        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; color: var(--teks); background: var(--putih); }
        .wadah { max-width: 1160px; margin: 0 auto; padding: 0 24px; }

        /* ── NAVBAR ─────────────────────────────────────────── */
        .navbar {
            position: sticky; top: 0; z-index: 100;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 0 24px; height: 64px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-brand .dot { width: 32px; height: 32px; background: var(--merah); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; }
        .nav-brand .nama { font-size: 1.1rem; font-weight: 800; color: var(--teks); letter-spacing: -0.3px; }
        .nav-links { display: flex; align-items: center; gap: 4px; }
        .nav-links a { padding: 7px 14px; border-radius: 8px; font-size: 0.88rem; font-weight: 500; color: var(--teks-sub); text-decoration: none; transition: all .2s; }
        .nav-links a:hover, .nav-links a.aktif { background: var(--bg); color: var(--teks); }
        .nav-cta { display: flex; gap: 8px; align-items: center; }
        .btn-outline-red { padding: 8px 18px; border: 1.5px solid var(--merah); border-radius: 8px; font-size: 0.85rem; font-weight: 600; color: var(--merah); text-decoration: none; transition: all .2s; }
        .btn-outline-red:hover { background: var(--merah); color: white; }
        .btn-solid-red { padding: 8px 18px; background: var(--merah); border-radius: 8px; font-size: 0.85rem; font-weight: 600; color: white; text-decoration: none; transition: background .2s; box-shadow: 0 2px 10px rgba(139,0,0,0.25); }
        .btn-solid-red:hover { background: var(--merah-gelap); }

        /* ── SECTION UMUM ───────────────────────────────────── */
        .section { padding: 80px 0; }
        .section-bg { background: var(--bg); }
        .section-label { display: inline-block; font-size: 0.75rem; font-weight: 700; color: var(--merah); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px; }
        .section-judul { font-size: 2rem; font-weight: 800; color: var(--teks); letter-spacing: -0.5px; line-height: 1.3; margin-bottom: 12px; }
        .section-sub { font-size: 0.95rem; color: var(--teks-sub); line-height: 1.7; max-width: 600px; }
        .section-head { margin-bottom: 40px; }
        .section-head.tengah { text-align: center; }
        .section-head.tengah .section-sub { margin: 0 auto; }

        /* ── LAYANAN CARDS ──────────────────────────────────── */
        .grid-layanan { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .kartu-layanan { background: white; border: 1px solid var(--border); border-radius: var(--radius); padding: 28px 24px; text-decoration: none; color: inherit; transition: all .25s; }
        .kartu-layanan:hover { border-color: var(--merah); transform: translateY(-4px); box-shadow: var(--shadow); }
        .layanan-ikon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 16px; }
        .layanan-judul { font-size: 1rem; font-weight: 700; margin-bottom: 8px; }
        .layanan-desc { font-size: 0.85rem; color: var(--teks-sub); line-height: 1.6; }
        .layanan-link { display: inline-flex; align-items: center; gap: 5px; font-size: 0.82rem; font-weight: 600; color: var(--merah); margin-top: 14px; }

        /* ── EVENT ──────────────────────────────────────────── */
        .tab-event { display: flex; gap: 4px; background: var(--bg); border-radius: 10px; padding: 4px; margin-bottom: 32px; width: fit-content; }
        .tab-event button { padding: 8px 20px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; border: none; cursor: pointer; font-family: inherit; background: transparent; color: var(--teks-sub); transition: all .2s; }
        .tab-event button.aktif { background: white; color: var(--teks); box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .grid-event { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .kartu-event { background: white; border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; transition: all .25s; }
        .kartu-event:hover { transform: translateY(-4px); box-shadow: var(--shadow); border-color: transparent; }
        .event-top { padding: 20px; display: flex; gap: 16px; align-items: flex-start; }
        .event-tgl { min-width: 52px; width: 52px; background: var(--merah); border-radius: 10px; padding: 8px; text-align: center; color: white; flex-shrink: 0; }
        .event-tgl.hijau { background: #1B8A4E; }
        .event-tgl-hari { font-size: 1.5rem; font-weight: 900; line-height: 1; }
        .event-tgl-bln  { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; opacity: .85; margin-top: 2px; }
        .event-tgl-thn  { font-size: 0.6rem; opacity: .7; }
        .event-body { flex: 1; }
        .event-tipe { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--merah); margin-bottom: 5px; }
        .event-tipe.hijau { color: #1B8A4E; }
        .event-judul { font-size: 0.92rem; font-weight: 700; color: var(--teks); line-height: 1.4; margin-bottom: 8px; }
        .event-meta { display: flex; flex-direction: column; gap: 4px; }
        .event-meta span { font-size: 0.78rem; color: var(--teks-sub); display: flex; align-items: center; gap: 5px; }
        .event-meta i { color: var(--merah); width: 12px; font-size: 10px; }
        .event-meta i.hijau { color: #1B8A4E; }
        .event-bot { padding: 14px 20px; border-top: 1px solid var(--bg); display: flex; align-items: center; justify-content: space-between; }
        .event-kuota { font-size: 0.75rem; color: var(--teks-sub); display: flex; align-items: center; gap: 4px; }
        .event-badge { font-size: 0.7rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; background: #D1E7DD; color: #0F5132; }
        .event-badge.merah { background: #FFEAEE; color: var(--merah); }
        .panel-event { display: none; }
        .panel-event.aktif { display: block; }
        .kosong-event { text-align: center; padding: 48px 20px; color: var(--teks-sub); border: 1.5px dashed var(--border); border-radius: var(--radius); }
        .kosong-event i { font-size: 2.5rem; opacity: .3; display: block; margin-bottom: 12px; }
        .kosong-event p { font-size: 0.88rem; }

        /* ── MANFAAT ────────────────────────────────────────── */
        .grid-manfaat { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .kartu-manfaat { padding: 24px; background: white; border-radius: var(--radius); border: 1px solid var(--border); }
        .manfaat-ikon  { font-size: 1.8rem; margin-bottom: 12px; }
        .manfaat-judul { font-size: 0.92rem; font-weight: 700; margin-bottom: 8px; }
        .manfaat-desc  { font-size: 0.83rem; color: var(--teks-sub); line-height: 1.6; }

        /* ── SYARAT ─────────────────────────────────────────── */
        .grid-syarat { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .kartu-syarat { background: white; border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; }
        .syarat-head { padding: 18px 20px; font-size: 0.9rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .syarat-head.merah { background: #FFF3F3; color: var(--merah); border-bottom: 1px solid #FFE0E0; }
        .syarat-head.hijau { background: #F0FFF6; color: #1B8A4E; border-bottom: 1px solid #C3E6CC; }
        .syarat-head.biru  { background: #EFF6FF; color: #1D4ED8; border-bottom: 1px solid #BFDBFE; }
        .syarat-body { padding: 16px 20px; }
        .syarat-body li { font-size: 0.83rem; color: var(--teks-sub); padding: 5px 0; border-bottom: 1px solid var(--bg); display: flex; gap: 8px; align-items: flex-start; list-style: none; }
        .syarat-body li:last-child { border-bottom: none; }
        .syarat-body li::before { content: '•'; color: var(--merah); font-weight: 900; flex-shrink: 0; margin-top: 1px; }
        .syarat-head.hijau ~ .syarat-body li::before { color: #1B8A4E; }
        .syarat-head.biru  ~ .syarat-body li::before { color: #1D4ED8; }

        /* ── CTA ────────────────────────────────────────────── */
        .cta-section { background: linear-gradient(135deg, #7a0000, #8B0000, #a50010); padding: 80px 24px; text-align: center; position: relative; overflow: hidden; }
        .cta-section::before { content: ''; position: absolute; inset: 0; background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
        .cta-inner { position: relative; max-width: 600px; margin: 0 auto; }
        .cta-judul { font-size: 2rem; font-weight: 900; color: white; margin-bottom: 14px; letter-spacing: -0.5px; }
        .cta-sub { font-size: 0.95rem; color: rgba(255,255,255,0.75); margin-bottom: 32px; line-height: 1.7; }
        .cta-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .cta-btn-putih { padding: 13px 28px; background: white; color: var(--merah); border-radius: 10px; font-weight: 700; text-decoration: none; font-size: 0.9rem; transition: all .2s; box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
        .cta-btn-putih:hover { transform: translateY(-2px); }
        .cta-btn-transparan { padding: 13px 28px; background: rgba(255,255,255,0.12); color: white; border: 1.5px solid rgba(255,255,255,0.3); border-radius: 10px; font-weight: 600; text-decoration: none; font-size: 0.9rem; transition: background .2s; }
        .cta-btn-transparan:hover { background: rgba(255,255,255,0.22); }

        /* ── FOOTER ─────────────────────────────────────────── */
        .footer { background: #111; color: #aaa; padding: 48px 24px 24px; }
        .footer-grid { max-width: 1160px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 40px; padding-bottom: 32px; border-bottom: 1px solid #222; }
        .footer-brand { font-size: 1.1rem; font-weight: 800; color: white; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        .footer-desc  { font-size: 0.83rem; line-height: 1.7; color: #666; max-width: 280px; }
        .footer-title { font-size: 0.78rem; font-weight: 700; color: white; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
        .footer-links { list-style: none; display: flex; flex-direction: column; gap: 8px; }
        .footer-links a { font-size: 0.83rem; color: #666; text-decoration: none; transition: color .2s; }
        .footer-links a:hover { color: white; }
        .footer-bot { max-width: 1160px; margin: 20px auto 0; display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem; color: #444; }

        /* ── RESPONSIVE ─────────────────────────────────────── */
        @media (max-width: 1024px) {
            .grid-layanan, .grid-event, .grid-manfaat, .grid-syarat { grid-template-columns: repeat(2, 1fr); }
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 640px) {
            .nav-links, .btn-outline-red { display: none; }
            .grid-layanan, .grid-event, .grid-manfaat, .grid-syarat { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr; }
            .section { padding: 56px 0; }
        }

        /* ── EXTRA PAGE CSS (override per halaman) ───────────── */
        <?php if (!empty($extra_css)) echo $extra_css; ?>
    </style>
</head>
<body>

<!-- ══ NAVBAR ══ -->
<nav class="navbar">
    <a href="<?= $prefix ?>index.php" class="nav-brand">
        <div class="dot">🩸</div>
        <span class="nama">DonorIn</span>
    </a>
    <div class="nav-links">
        <a href="<?= $prefix ?>index.php#layanan"  class="<?= $halaman_aktif==='layanan' ?'aktif':'' ?>">Layanan</a>
        <a href="<?= $prefix ?>index.php#event"    class="<?= $halaman_aktif==='event'   ?'aktif':'' ?>">Event</a>
        <a href="<?= $prefix ?>index.php#manfaat"  class="<?= $halaman_aktif==='manfaat' ?'aktif':'' ?>">Manfaat</a>
        <a href="<?= $prefix ?>index.php#syarat"   class="<?= $halaman_aktif==='syarat'  ?'aktif':'' ?>">Syarat</a>
    </div>
    <div class="nav-cta">
        <?php if ($pendonor_login): ?>
            <a href="<?= $prefix ?>pages/donor/dashboard_pendonor.php" class="btn-solid-red">
                <i class="fas fa-user-circle"></i> Dashboard
            </a>
        <?php elseif ($pasien_login): ?>
            <a href="<?= $prefix ?>pages/pasien/dashboard_pasien.php" class="btn-solid-red">
                <i class="fas fa-user-circle"></i> Dashboard
            </a>
        <?php else: ?>
            <a href="<?= $prefix ?>auth/login_pendonor.php" class="btn-outline-red">Masuk</a>
            <a href="<?= $prefix ?>pages/donor/daftar_pendonor.php" class="btn-solid-red">Daftar Pendonor</a>
        <?php endif; ?>
    </div>
</nav>