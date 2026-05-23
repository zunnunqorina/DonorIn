<?php
include 'koneksi.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM user WHERE id = $id_hapus AND role = 'pendonor'");
    header("Location: pendonor_admin.php?pesan=hapus_sukses");
    exit();
}

$error_tambah = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama   = trim(mysqli_real_escape_string($conn, $_POST['nama']));
    $email  = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $no_hp  = trim(mysqli_real_escape_string($conn, $_POST['no_hp']));
    $goldar = mysqli_real_escape_string($conn, $_POST['goldar']);
    $kota   = trim(mysqli_real_escape_string($conn, $_POST['kota']));
    $pass   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek email duplikat
    $cek = mysqli_query($conn, "SELECT id FROM user WHERE email = '$email'");
    if (mysqli_num_rows($cek) > 0) {
        $error_tambah = 'Email sudah terdaftar!';
    } else {
        $q = "INSERT INTO user (nama, email, password, role, no_hp, goldar, kota)
              VALUES ('$nama','$email','$pass','pendonor','$no_hp','$goldar','$kota')";
        if (mysqli_query($conn, $q)) {
            header("Location: pendonor_admin.php?pesan=tambah_sukses");
            exit();
        } else {
            $error_tambah = 'Gagal menambahkan pendonor: ' . mysqli_error($conn);
        }
    }
}

$error_edit = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id_edit = (int) $_POST['id_edit'];
    $nama    = trim(mysqli_real_escape_string($conn, $_POST['nama']));
    $email   = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $no_hp   = trim(mysqli_real_escape_string($conn, $_POST['no_hp']));
    $goldar  = mysqli_real_escape_string($conn, $_POST['goldar']);
    $kota    = trim(mysqli_real_escape_string($conn, $_POST['kota']));

    // Cek email duplikat (selain diri sendiri)
    $cek = mysqli_query($conn, "SELECT id FROM user WHERE email = '$email' AND id != $id_edit");
    if (mysqli_num_rows($cek) > 0) {
        $error_edit = 'Email sudah digunakan akun lain!';
    } else {
        $set_pass = '';
        if (!empty($_POST['password'])) {
            $pass_baru = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $set_pass  = ", password = '$pass_baru'";
        }
        $q = "UPDATE user SET
                nama   = '$nama',
                email  = '$email',
                no_hp  = '$no_hp',
                goldar = '$goldar',
                kota   = '$kota'
                $set_pass
              WHERE id = $id_edit AND role = 'pendonor'";
        if (mysqli_query($conn, $q)) {
            header("Location: pendonor_admin.php?pesan=edit_sukses");
            exit();
        } else {
            $error_edit = 'Gagal memperbarui data: ' . mysqli_error($conn);
        }
    }
}

$search    = isset($_GET['search']) ? trim(mysqli_real_escape_string($conn, $_GET['search'])) : '';
$filter_gd = isset($_GET['goldar'])  ? mysqli_real_escape_string($conn, $_GET['goldar'])      : '';
$page      = isset($_GET['page'])    ? max(1, (int) $_GET['page'])                            : 1;
$per_page  = 10;
$offset    = ($page - 1) * $per_page;

$where = "WHERE role = 'pendonor'";
if ($search !== '')    $where .= " AND (nama LIKE '%$search%' OR email LIKE '%$search%' OR no_hp LIKE '%$search%' OR kota LIKE '%$search%')";
if ($filter_gd !== '') $where .= " AND goldar = '$filter_gd'";

// Total untuk paginasi
$q_total  = mysqli_query($conn, "SELECT COUNT(*) as total FROM user $where");
$total    = mysqli_fetch_assoc($q_total)['total'];
$total_pg = ceil($total / $per_page);

// Data pendonor
$q_pendonor = mysqli_query($conn, "
    SELECT * FROM user $where
    ORDER BY tanggal_daftar DESC
    LIMIT $per_page OFFSET $offset
");

// Statistik ringkas
$q_stat_total  = mysqli_query($conn, "SELECT COUNT(*) as t FROM user WHERE role='pendonor'");
$stat_total    = mysqli_fetch_assoc($q_stat_total)['t'] ?? 0;

$q_stat_bulan  = mysqli_query($conn, "SELECT COUNT(*) as t FROM user WHERE role='pendonor' AND MONTH(tanggal_daftar)=MONTH(CURDATE()) AND YEAR(tanggal_daftar)=YEAR(CURDATE())");
$stat_bulan    = mysqli_fetch_assoc($q_stat_bulan)['t'] ?? 0;

$q_goldar_stat = mysqli_query($conn, "SELECT goldar, COUNT(*) as total FROM user WHERE role='pendonor' AND goldar IS NOT NULL GROUP BY goldar ORDER BY total DESC");
$goldar_stat   = [];
while ($r = mysqli_fetch_assoc($q_goldar_stat)) $goldar_stat[$r['goldar']] = $r['total'];

// Data untuk form edit (jika ada)
$edit_data = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id_e     = (int) $_GET['edit'];
    $q_edit   = mysqli_query($conn, "SELECT * FROM user WHERE id = $id_e AND role = 'pendonor'");
    $edit_data = mysqli_fetch_assoc($q_edit);
}

// Pesan notifikasi
$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pendonor — DonorIn</title>
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
            --shadow-lg:    0 10px 40px rgba(192,0,26,.18), 0 4px 16px rgba(0,0,0,.10);
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
        }

        /* ══ SIDEBAR ══ */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: linear-gradient(175deg, #8B0012 0%, #C0001A 55%, #A0001A 100%);
            position: fixed; top: 0; left: 0;
            display: flex; flex-direction: column;
            z-index: 100;
            box-shadow: 4px 0 24px rgba(139,0,18,.35);
        }
        .sidebar-brand {
            padding: 28px 24px 24px;
            border-bottom: 1px solid rgba(255,255,255,.12);
            display: flex; align-items: center; gap: 12px;
        }
        .brand-icon { width: 42px; height: 42px; background: var(--putih); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .brand-icon i { color: var(--merah); font-size: 20px; }
        .brand-name { font-family: 'Fraunces', serif; font-size: 22px; font-weight: 900; color: var(--putih); line-height: 1; }
        .brand-sub  { font-size: 10px; color: rgba(255,255,255,.6); font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase; margin-top: 3px; }

        .sidebar-nav { flex: 1; padding: 20px 0; overflow-y: auto; }
        .nav-section { padding: 0 14px; margin-bottom: 4px; }
        .nav-label   { font-size: 10px; font-weight: 700; letter-spacing: 1.8px; text-transform: uppercase; color: rgba(255,255,255,.4); padding: 12px 10px 6px; }
        .nav-item    { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: var(--radius-sm); color: rgba(255,255,255,.75); text-decoration: none; font-size: 14px; font-weight: 500; transition: var(--trans); margin-bottom: 2px; }
        .nav-item:hover  { background: rgba(255,255,255,.12); color: var(--putih); transform: translateX(3px); }
        .nav-item.active { background: var(--putih); color: var(--merah); font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,.2); }
        .nav-item.active i { color: var(--merah); }
        .nav-item i  { width: 18px; text-align: center; font-size: 15px; }
        .nav-badge   { margin-left: auto; background: rgba(255,255,255,.2); color: var(--putih); font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 20px; }
        .nav-item.active .nav-badge { background: var(--merah-muda); color: var(--merah); }

        .sidebar-footer { padding: 18px 14px; border-top: 1px solid rgba(255,255,255,.12); }
        .sidebar-user   { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: var(--radius-sm); background: rgba(255,255,255,.08); margin-bottom: 10px; }
        .user-avatar    { width: 36px; height: 36px; border-radius: 50%; background: var(--putih); display: flex; align-items: center; justify-content: center; font-size: 15px; color: var(--merah); font-weight: 700; flex-shrink: 0; }
        .user-name      { font-size: 13px; font-weight: 600; color: var(--putih); }
        .user-role      { font-size: 11px; color: rgba(255,255,255,.5); }
        .btn-logout     { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px; border-radius: var(--radius-sm); background: rgba(255,255,255,.1); color: rgba(255,255,255,.8); font-size: 13px; font-weight: 600; text-decoration: none; border: 1px solid rgba(255,255,255,.15); transition: var(--trans); }
        .btn-logout:hover { background: rgba(255,255,255,.2); color: var(--putih); }

        /* ══ MAIN ══ */
        .main    { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .topbar  { background: var(--putih); border-bottom: 1px solid var(--abu); padding: 0 32px; height: 68px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; box-shadow: var(--shadow-sm); }
        .topbar-title       { font-size: 18px; font-weight: 800; color: var(--teks-gelap); }
        .topbar-breadcrumb  { font-size: 12px; color: var(--abu-sedang); margin-top: 3px; }
        .topbar-breadcrumb a { color: var(--abu-sedang); text-decoration: none; }
        .topbar-breadcrumb a:hover { color: var(--merah); }
        .topbar-breadcrumb span { color: var(--merah); font-weight: 600; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .date-chip { display: flex; align-items: center; gap: 7px; padding: 8px 14px; border-radius: 10px; background: var(--merah-tipis); border: 1px solid var(--merah-muda); font-size: 13px; font-weight: 600; color: var(--merah); }

        /* ══ CONTENT ══ */
        .content { padding: 28px 32px 48px; flex: 1; }

        /* ══ NOTIFIKASI ══ */
        .notif {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 20px; border-radius: var(--radius-sm);
            font-size: 14px; font-weight: 600;
            margin-bottom: 22px; animation: fadeUp .4s ease;
        }
        .notif-sukses { background: #E8F8F0; border: 1px solid #A7DFC0; color: #1B8A4E; }
        .notif-error  { background: var(--merah-muda); border: 1px solid #FFC0C8; color: var(--merah-gelap); }
        .notif i { font-size: 16px; }

        /* ══ STAT MINI CARDS ══ */
        .mini-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-bottom: 24px; }
        .mini-card  {
            background: var(--putih); border: 1px solid var(--abu);
            border-radius: var(--radius); padding: 18px 20px;
            display: flex; align-items: center; gap: 14px;
            box-shadow: var(--shadow-sm); transition: var(--trans);
            animation: fadeUp .4s ease both;
        }
        .mini-card:hover { border-color: var(--merah-muda); box-shadow: var(--shadow-md); transform: translateY(-1px); }
        .mini-card:nth-child(1) { animation-delay: .05s; }
        .mini-card:nth-child(2) { animation-delay: .10s; }
        .mini-card:nth-child(3) { animation-delay: .13s; }
        .mini-card:nth-child(4) { animation-delay: .16s; }
        .mini-card:nth-child(5) { animation-delay: .19s; }

        .mini-icon  { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .mi-merah   { background: var(--merah-muda); color: var(--merah); }
        .mi-a       { background: #FFF3E0; color: #E65100; }
        .mi-b       { background: #E3F2FD; color: #1565C0; }
        .mi-o       { background: #E8F5E9; color: #2E7D32; }
        .mi-ab      { background: #F3E5F5; color: #6A1B9A; }

        .mini-val   { font-family: 'Fraunces', serif; font-size: 26px; font-weight: 900; color: var(--teks-gelap); line-height: 1; }
        .mini-label { font-size: 11px; color: var(--abu-sedang); font-weight: 600; margin-top: 3px; }

        /* ══ PANEL UTAMA ══ */
        .panel { background: var(--putih); border-radius: var(--radius); border: 1px solid var(--abu); box-shadow: var(--shadow-sm); overflow: hidden; animation: fadeUp .4s ease .2s both; }

        .panel-head {
            padding: 20px 24px;
            border-bottom: 1px solid var(--abu);
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 14px;
        }
        .panel-title { font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px; }
        .panel-title i { color: var(--merah); }

        /* ══ TOOLBAR: SEARCH + FILTER + TAMBAH ══ */
        .toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

        .search-wrap { position: relative; }
        .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--abu-sedang); font-size: 13px; pointer-events: none; }
        .input-search {
            padding: 9px 14px 9px 36px;
            border: 1px solid var(--abu);
            border-radius: var(--radius-sm);
            font-size: 13px; font-family: inherit;
            outline: none; width: 220px;
            transition: var(--trans);
            background: var(--abu-terang);
            color: var(--teks-gelap);
        }
        .input-search:focus { border-color: var(--merah); background: var(--putih); box-shadow: 0 0 0 3px rgba(192,0,26,.08); }

        .select-filter {
            padding: 9px 14px;
            border: 1px solid var(--abu);
            border-radius: var(--radius-sm);
            font-size: 13px; font-family: inherit;
            outline: none; background: var(--abu-terang);
            color: var(--teks-gelap); cursor: pointer;
            transition: var(--trans);
        }
        .select-filter:focus { border-color: var(--merah); background: var(--putih); }

        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px; border-radius: var(--radius-sm);
            font-size: 13px; font-weight: 700; font-family: inherit;
            cursor: pointer; text-decoration: none; border: none;
            transition: var(--trans); white-space: nowrap;
        }
        .btn-primary { background: var(--merah); color: var(--putih); }
        .btn-primary:hover { background: var(--merah-gelap); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(192,0,26,.3); }
        .btn-outline { background: var(--putih); color: var(--merah); border: 1px solid var(--merah-muda); }
        .btn-outline:hover { background: var(--merah-tipis); }
        .btn-ghost  { background: var(--abu-terang); color: var(--teks-sedang); border: 1px solid var(--abu); }
        .btn-ghost:hover { background: var(--abu); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-icon { padding: 7px 10px; }

        /* ══ TABLE ══ */
        .tbl-wrap { overflow-x: auto; }
        .tbl { width: 100%; border-collapse: collapse; min-width: 700px; }
        .tbl thead th {
            padding: 11px 20px; text-align: left;
            font-size: 11px; font-weight: 700; color: var(--abu-sedang);
            text-transform: uppercase; letter-spacing: .8px;
            background: var(--abu-terang); border-bottom: 1px solid var(--abu);
            white-space: nowrap;
        }
        .tbl thead th.sortable { cursor: pointer; user-select: none; }
        .tbl thead th.sortable:hover { color: var(--merah); }
        .tbl tbody tr { border-bottom: 1px solid var(--abu); transition: var(--trans); }
        .tbl tbody tr:last-child { border-bottom: none; }
        .tbl tbody tr:hover { background: var(--merah-tipis); }
        .tbl td { padding: 13px 20px; font-size: 13px; vertical-align: middle; }

        .td-nama { display: flex; align-items: center; gap: 11px; }
        .td-avatar {
            width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700;
            background: var(--merah-muda); color: var(--merah);
        }
        .td-nama-text { font-weight: 600; color: var(--teks-gelap); }
        .td-nama-sub  { font-size: 11px; color: var(--abu-sedang); margin-top: 1px; }

        /* ── Golongan Darah Badge Warna ── */
        .gd-badge {
            display: inline-flex; align-items: center; justify-content: center;
            width: 34px; height: 34px; border-radius: 50%;
            font-size: 12px; font-weight: 800;
        }
        .gd-A  { background: #FFF3E0; color: #E65100; }
        .gd-B  { background: #E3F2FD; color: #1565C0; }
        .gd-O  { background: #E8F5E9; color: #2E7D32; }
        .gd-AB { background: #F3E5F5; color: #6A1B9A; border-radius: 10px; width: auto; padding: 0 9px; }

        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-hijau   { background: #E8F8F0; color: #1B8A4E; }
        .badge-abu     { background: #F1F3F5; color: #6B7280; }

        /* ══ AKSI BUTTON ══ */
        .aksi-wrap { display: flex; gap: 6px; }
        .btn-edit { background: #EDF4FF; color: #2563EB; border: 1px solid #BFDBFE; }
        .btn-edit:hover { background: #2563EB; color: var(--putih); }
        .btn-hapus { background: var(--merah-muda); color: var(--merah); border: 1px solid #FFBFC8; }
        .btn-hapus:hover { background: var(--merah); color: var(--putih); }
        .btn-detail { background: #FFF8E6; color: #D4900A; border: 1px solid #FDE68A; }
        .btn-detail:hover { background: #D4900A; color: var(--putih); }

        /* ══ EMPTY STATE ══ */
        .empty-state { text-align: center; padding: 52px 20px; color: var(--abu-sedang); }
        .empty-state .empty-icon { font-size: 52px; color: var(--merah-muda); display: block; margin-bottom: 14px; }
        .empty-state h3 { font-size: 16px; font-weight: 700; color: var(--teks-sedang); margin-bottom: 6px; }
        .empty-state p  { font-size: 13px; }

        /* ══ PAGINASI ══ */
        .pagination-wrap { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-top: 1px solid var(--abu); flex-wrap: wrap; gap: 10px; }
        .pagi-info { font-size: 13px; color: var(--abu-sedang); }
        .pagi-info strong { color: var(--teks-gelap); }
        .pagi-btns { display: flex; gap: 6px; }
        .pagi-btn {
            width: 34px; height: 34px; border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 600; text-decoration: none;
            border: 1px solid var(--abu); color: var(--teks-sedang);
            background: var(--putih); transition: var(--trans);
        }
        .pagi-btn:hover   { border-color: var(--merah-muda); color: var(--merah); background: var(--merah-tipis); }
        .pagi-btn.active  { background: var(--merah); color: var(--putih); border-color: var(--merah); }
        .pagi-btn.disabled { opacity: .4; pointer-events: none; }

        /* ══ MODAL ══ */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(26,26,46,.55);
            backdrop-filter: blur(4px);
            z-index: 200;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
            opacity: 0; pointer-events: none;
            transition: opacity .3s ease;
        }
        .modal-overlay.show { opacity: 1; pointer-events: all; }

        .modal {
            background: var(--putih);
            border-radius: 18px;
            width: 100%; max-width: 540px;
            box-shadow: var(--shadow-lg);
            transform: translateY(20px) scale(.97);
            transition: var(--trans);
            max-height: 90vh;
            display: flex; flex-direction: column;
        }
        .modal-overlay.show .modal { transform: translateY(0) scale(1); }

        .modal-head {
            padding: 22px 26px 18px;
            border-bottom: 1px solid var(--abu);
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .modal-title { font-size: 17px; font-weight: 800; display: flex; align-items: center; gap: 9px; }
        .modal-title i { color: var(--merah); }
        .modal-close { width: 32px; height: 32px; border-radius: 8px; background: var(--abu-terang); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--teks-sedang); font-size: 15px; transition: var(--trans); }
        .modal-close:hover { background: var(--merah-muda); color: var(--merah); }

        .modal-body { padding: 22px 26px; overflow-y: auto; flex: 1; }
        .modal-foot { padding: 16px 26px; border-top: 1px solid var(--abu); display: flex; gap: 10px; justify-content: flex-end; flex-shrink: 0; }

        /* ══ FORM ══ */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-label { font-size: 12px; font-weight: 700; color: var(--teks-sedang); text-transform: uppercase; letter-spacing: .5px; }
        .form-label .req { color: var(--merah); margin-left: 2px; }
        .form-input, .form-select {
            padding: 10px 14px; border: 1px solid var(--abu); border-radius: var(--radius-sm);
            font-size: 14px; font-family: inherit; outline: none;
            background: var(--abu-terang); color: var(--teks-gelap);
            transition: var(--trans);
        }
        .form-input:focus, .form-select:focus { border-color: var(--merah); background: var(--putih); box-shadow: 0 0 0 3px rgba(192,0,26,.08); }
        .form-hint { font-size: 11px; color: var(--abu-sedang); }

        /* ══ KONFIRMASI HAPUS ══ */
        .modal-hapus .modal { max-width: 400px; }
        .hapus-icon { width: 64px; height: 64px; border-radius: 50%; background: var(--merah-muda); display: flex; align-items: center; justify-content: center; font-size: 28px; color: var(--merah); margin: 0 auto 16px; }

        /* ══ ANIMASI ══ */
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

        /* ══ SCROLLBAR ══ */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--merah-muda); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--merah); }

        @media (max-width: 900px) { .mini-stats { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } .content { padding: 20px 16px 40px; } }
    </style>
</head>
<body>

<!-- ══════════ SIDEBAR ══════════ -->
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
            <a href="dashboard_admin.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
        </div>
        <div class="nav-section">
            <div class="nav-label">Pengguna</div>
            <a href="pasien.php" class="nav-item"><i class="fas fa-user-injured"></i> Pasien</a>
            <a href="pendonor_admin.php" class="nav-item active"><i class="fas fa-hand-holding-heart"></i> Pendonor</a>
            <a href="relawan.php" class="nav-item"><i class="fas fa-people-carry-box"></i> Relawan</a>
        </div>
        <div class="nav-section">
            <div class="nav-label">Event</div>
            <a href="event_donor.php" class="nav-item"><i class="fas fa-calendar-alt"></i> Event Donor Darah</a>
            <a href="event_sosialisasi.php" class="nav-item"><i class="fas fa-bullhorn"></i> Event Sosialisasi</a>
        </div>
        <div class="nav-section">
            <div class="nav-label">Lainnya</div>
            <a href="kritik_saran.php" class="nav-item"><i class="fas fa-comments"></i> Kritik & Saran</a>
        </div>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($admin_username, 0, 1)) ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($admin_username) ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        <a href="../logout.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </div>
</aside>

<!-- ══════════ MAIN ══════════ -->
<main class="main">
    <header class="topbar">
        <div>
            <div class="topbar-title">Manajemen Pendonor</div>
            <div class="topbar-breadcrumb">
                <a href="../dashboard.php">DonorIn</a> ›
                <a href="pendonor_admin.php">Pengguna</a> ›
                <span>Pendonor</span>
            </div>
        </div>
        <div class="topbar-right">
            <div class="date-chip"><i class="fas fa-calendar-day"></i><?= date('d M Y') ?></div>
        </div>
    </header>

    <div class="content">

        <!-- ── NOTIFIKASI ── -->
        <?php if ($pesan === 'tambah_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Pendonor berhasil ditambahkan!</div>
        <?php elseif ($pesan === 'edit_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Data pendonor berhasil diperbarui!</div>
        <?php elseif ($pesan === 'hapus_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Pendonor berhasil dihapus.</div>
        <?php endif; ?>

        <?php if ($error_tambah): ?>
        <div class="notif notif-error"><i class="fas fa-exclamation-circle"></i><?= $error_tambah ?></div>
        <?php endif; ?>
        <?php if ($error_edit): ?>
        <div class="notif notif-error"><i class="fas fa-exclamation-circle"></i><?= $error_edit ?></div>
        <?php endif; ?>

        <!-- ── STAT MINI CARDS ── -->
        <div class="mini-stats">
            <div class="mini-card">
                <div class="mini-icon mi-merah"><i class="fas fa-users"></i></div>
                <div>
                    <div class="mini-val"><?= $stat_total ?></div>
                    <div class="mini-label">Total Pendonor</div>
                </div>
            </div>
            <div class="mini-card">
                <div class="mini-icon mi-merah" style="background:#FFF0F0;"><i class="fas fa-user-plus"></i></div>
                <div>
                    <div class="mini-val"><?= $stat_bulan ?></div>
                    <div class="mini-label">Daftar Bulan Ini</div>
                </div>
            </div>
            <?php foreach (['A'=>'mi-a','B'=>'mi-b','O'=>'mi-o','AB'=>'mi-ab'] as $gd => $cls): ?>
            <div class="mini-card">
                <div class="mini-icon <?= $cls ?>"><i class="fas fa-tint"></i></div>
                <div>
                    <div class="mini-val"><?= $goldar_stat[$gd] ?? 0 ?></div>
                    <div class="mini-label">Golongan <?= $gd ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── PANEL UTAMA ── -->
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><i class="fas fa-hand-holding-heart"></i> Daftar Pendonor
                    <span style="font-size:12px;font-weight:500;color:var(--abu-sedang);font-family:'Plus Jakarta Sans',sans-serif;">
                        (<?= $total ?> data<?= ($search||$filter_gd)?' — difilter':'' ?>)
                    </span>
                </div>
                <div class="toolbar">
                    <!-- Form Search & Filter -->
                    <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <div class="search-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" class="input-search"
                                   placeholder="Cari nama, email, kota…"
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <select name="goldar" class="select-filter" onchange="this.form.submit()">
                            <option value="">Semua Gol. Darah</option>
                            <?php foreach (['A','B','O','AB'] as $g): ?>
                            <option value="<?= $g ?>" <?= $filter_gd===$g?'selected':'' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-filter"></i> Filter</button>
                        <?php if ($search || $filter_gd): ?>
                        <a href="pendonor_admin.php" class="btn btn-ghost btn-sm"><i class="fas fa-times"></i> Reset</a>
                        <?php endif; ?>
                    </form>
                    <button class="btn btn-primary" onclick="bukaModalTambah()">
                        <i class="fas fa-plus"></i> Tambah Pendonor
                    </button>
                </div>
            </div>

            <!-- TABLE -->
            <div class="tbl-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Nama Pendonor</th>
                            <th>Gol. Darah</th>
                            <th>No. HP</th>
                            <th>Kota</th>
                            <th>Tanggal Daftar</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($q_pendonor) > 0):
                        $no = $offset + 1;
                        while ($row = mysqli_fetch_assoc($q_pendonor)):
                            $gd     = $row['goldar'] ?? '-';
                            $gd_cls = in_array($gd, ['A','B','O','AB']) ? "gd-$gd" : 'badge-abu';
                    ?>
                        <tr>
                            <td style="color:var(--abu-sedang);font-size:12px;font-weight:600;"><?= $no++ ?></td>
                            <td>
                                <div class="td-nama">
                                    <div class="td-avatar"><?= strtoupper(substr($row['nama'],0,1)) ?></div>
                                    <div>
                                        <div class="td-nama-text"><?= htmlspecialchars($row['nama']) ?></div>
                                        <div class="td-nama-sub"><?= htmlspecialchars($row['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (in_array($gd, ['A','B','O','AB'])): ?>
                                <span class="gd-badge gd-<?= $gd ?>"><?= $gd ?></span>
                                <?php else: ?>
                                <span class="badge badge-abu">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:13px;"><?= htmlspecialchars($row['no_hp'] ?? '—') ?></td>
                            <td style="font-size:13px;color:var(--teks-sedang);"><?= htmlspecialchars($row['kota'] ?? '—') ?></td>
                            <td style="font-size:12px;color:var(--abu-sedang);">
                                <?= $row['tanggal_daftar'] ? date('d M Y', strtotime($row['tanggal_daftar'])) : '—' ?>
                            </td>
                            <td>
                                <div class="aksi-wrap" style="justify-content:center;">
                                    <button class="btn btn-sm btn-edit btn-icon"
                                        onclick='bukaModalEdit(<?= json_encode($row) ?>)'
                                        title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-hapus btn-icon"
                                        onclick='konfirmasiHapus(<?= $row['id'] ?>, "<?= addslashes(htmlspecialchars($row['nama'])) ?>")'
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-hand-holding-heart empty-icon"></i>
                                    <h3>Tidak ada data pendonor</h3>
                                    <p><?= ($search||$filter_gd) ? 'Coba ubah kata kunci atau filter pencarian.' : 'Mulai tambahkan pendonor pertama.' ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINASI -->
            <?php if ($total_pg > 1): ?>
            <div class="pagination-wrap">
                <div class="pagi-info">
                    Menampilkan <strong><?= $offset+1 ?>–<?= min($offset+$per_page, $total) ?></strong>
                    dari <strong><?= $total ?></strong> pendonor
                </div>
                <div class="pagi-btns">
                    <?php
                    $qs = http_build_query(['search'=>$search,'goldar'=>$filter_gd]);
                    ?>
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
        </div><!-- /panel -->

    </div><!-- /content -->
</main>

<!-- ══════════ MODAL TAMBAH ══════════ -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal" style="max-width:620px;">
        <div class="modal-head">
            <div class="modal-title"><i class="fas fa-user-plus"></i> Tambah Pendonor</div>
            <button class="modal-close" onclick="tutupModal('modalTambah')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="pendonor_admin.php">
            <input type="hidden" name="aksi" value="tambah">
            <div class="modal-body">
                <div class="form-grid">

                    <div class="form-group full">
                        <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" name="nama" class="form-input" placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email <span class="req">*</span></label>
                        <input type="email" name="email" class="form-input" placeholder="nama@gmail.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password <span class="req">*</span></label>
                        <input type="password" name="password" class="form-input" placeholder="Min. 6 karakter" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="no_hp" class="form-input" placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Golongan Darah</label>
                        <select name="goldar" class="form-select">
                            <option value="">-- Pilih --</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="O">O</option>
                            <option value="AB">AB</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kota</label>
                        <input type="text" name="kota" class="form-input" placeholder="Contoh: Mataram">
                    </div>

                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="tutupModal('modalTambah')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════ MODAL EDIT ══════════ -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-title"><i class="fas fa-pen"></i> Edit Data Pendonor</div>
            <button class="modal-close" onclick="tutupModal('modalEdit')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="pendonor_admin.php">
            <input type="hidden" name="aksi"    value="edit">
            <input type="hidden" name="id_edit" id="edit_id">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" name="nama" id="edit_nama" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="req">*</span></label>
                        <input type="email" name="email" id="edit_email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-input" placeholder="Kosongkan jika tidak diubah" minlength="6">
                        <span class="form-hint">Kosongkan jika tidak ingin mengubah password</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="no_hp" id="edit_no_hp" class="form-input" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Golongan Darah</label>
                        <select name="goldar" id="edit_goldar" class="form-select">
                            <option value="">-- Pilih --</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="O">O</option>
                            <option value="AB">AB</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Kota</label>
                        <input type="text" name="kota" id="edit_kota" class="form-input" placeholder="Contoh: Mataram">
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="tutupModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════ MODAL HAPUS ══════════ -->
<div class="modal-overlay modal-hapus" id="modalHapus">
    <div class="modal">
        <div class="modal-body" style="padding:32px 28px;text-align:center;">
            <div class="hapus-icon"><i class="fas fa-trash-alt"></i></div>
            <h3 style="font-size:18px;font-weight:800;margin-bottom:8px;">Hapus Pendonor?</h3>
            <p style="font-size:14px;color:var(--teks-sedang);margin-bottom:4px;">Kamu akan menghapus pendonor:</p>
            <p style="font-size:15px;font-weight:700;color:var(--merah);margin-bottom:16px;" id="hapus_nama">—</p>
            <p style="font-size:13px;color:var(--abu-sedang);">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-foot" style="justify-content:center;">
            <button class="btn btn-ghost" onclick="tutupModal('modalHapus')"><i class="fas fa-times"></i> Batal</button>
            <a href="#" id="hapus_link" class="btn btn-primary" style="background:var(--merah);">
                <i class="fas fa-trash"></i> Ya, Hapus
            </a>
        </div>
    </div>
</div>

<script>
// ── Modal helpers ──
function bukaModal(id) {
    document.getElementById(id).classList.add('show');
    document.body.style.overflow = 'hidden';
}
function tutupModal(id) {
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow = '';
}

// Tutup modal klik di luar
document.querySelectorAll('.modal-overlay').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (e.target === el) tutupModal(el.id);
    });
});

// ESC tutup modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.show').forEach(function(el) {
            tutupModal(el.id);
        });
    }
});

// ── Buka modal tambah ──
function bukaModalTambah() {
    bukaModal('modalTambah');
}

// ── Buka modal edit (isi form dengan data baris) ──
function bukaModalEdit(data) {
    document.getElementById('edit_id').value     = data.id;
    document.getElementById('edit_nama').value   = data.nama;
    document.getElementById('edit_email').value  = data.email;
    document.getElementById('edit_no_hp').value  = data.no_hp  || '';
    document.getElementById('edit_kota').value   = data.kota   || '';

    var sel = document.getElementById('edit_goldar');
    sel.value = data.goldar || '';

    bukaModal('modalEdit');
}

// ── Konfirmasi hapus ──
function konfirmasiHapus(id, nama) {
    document.getElementById('hapus_nama').textContent = nama;
    document.getElementById('hapus_link').href = 'pendonor_admin.php?hapus=' + id;
    bukaModal('modalHapus');
}

// ── Auto-buka modal jika ada error ──
<?php if ($error_tambah): ?>
bukaModalTambah();
<?php endif; ?>
<?php if ($error_edit): ?>
// Buka modal edit kembali jika error
bukaModal('modalEdit');
document.getElementById('edit_id').value    = '<?= (int)($_POST['id_edit'] ?? 0) ?>';
document.getElementById('edit_nama').value  = '<?= addslashes(htmlspecialchars($_POST['nama'] ?? '')) ?>';
document.getElementById('edit_email').value = '<?= addslashes(htmlspecialchars($_POST['email'] ?? '')) ?>';
document.getElementById('edit_no_hp').value = '<?= addslashes(htmlspecialchars($_POST['no_hp'] ?? '')) ?>';
document.getElementById('edit_kota').value  = '<?= addslashes(htmlspecialchars($_POST['kota'] ?? '')) ?>';
document.getElementById('edit_goldar').value= '<?= addslashes(htmlspecialchars($_POST['goldar'] ?? '')) ?>';
<?php endif; ?>

// ── Auto-hilangkan notifikasi setelah 4 detik ──
setTimeout(function() {
    var notif = document.querySelector('.notif');
    if (notif) {
        notif.style.opacity = '0';
        notif.style.transform = 'translateY(-8px)';
        notif.style.transition = 'all .4s ease';
        setTimeout(function() { notif.remove(); }, 400);
    }
}, 4000);
</script>

</body>
</html>