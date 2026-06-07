<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../../auth/login_admin.php");
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// ============================================================
// HANDLE HAPUS
// ============================================================
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $stmt = $conn->prepare("DELETE FROM relawan WHERE id = ?");
    $stmt->execute([(int) $_GET['hapus']]);
    header("Location: relawan_admin.php?pesan=hapus_sukses");
    exit();
}

// ============================================================
// HANDLE TAMBAH
// ============================================================
$error_tambah = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $nama           = trim($_POST['nama']);
    $email          = trim($_POST['email']);
    $no_hp          = trim($_POST['no_hp']);
    $tgl_lahir      = $_POST['tgl_lahir'];
    $jenis_kelamin  = $_POST['jenis_kelamin'];
    $goldar         = $_POST['goldar'];
    $berat_badan    = (int) $_POST['berat_badan'];
    $kota           = trim($_POST['kota']);
    $pekerjaan      = trim($_POST['pekerjaan'] ?? '');
    $alamat         = trim($_POST['alamat'] ?? '');

    // Hitung umur dari tgl_lahir
    $umur = (int) date_diff(date_create($tgl_lahir), date_create('today'))->y;

    // Cek email duplikat
    $cek = $conn->prepare("SELECT id FROM relawan WHERE email = ?");
    $cek->execute([$email]);
    if ($cek->rowCount() > 0) {
        $error_tambah = 'Email sudah terdaftar!';
    } else {
        $stmt = $conn->prepare("INSERT INTO relawan 
            (nama, email, no_hp, tgl_lahir, umur, jenis_kelamin, goldar, berat_badan, kota, pekerjaan, alamat)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        if ($stmt->execute([$nama, $email, $no_hp, $tgl_lahir, $umur, $jenis_kelamin, $goldar, $berat_badan, $kota, $pekerjaan, $alamat])) {
            header("Location: relawan_admin.php?pesan=tambah_sukses");
            exit();
        } else {
            $error_tambah = 'Gagal menambahkan relawan!';
        }
    }
}

// ============================================================
// HANDLE EDIT
// ============================================================
$error_edit = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'edit') {
    $id_edit        = (int) $_POST['id_edit'];
    $nama           = trim($_POST['nama']);
    $email          = trim($_POST['email']);
    $no_hp          = trim($_POST['no_hp']);
    $tgl_lahir      = $_POST['tgl_lahir'];
    $jenis_kelamin  = $_POST['jenis_kelamin'];
    $goldar         = $_POST['goldar'];
    $berat_badan    = (int) $_POST['berat_badan'];
    $kota           = trim($_POST['kota']);
    $pekerjaan      = trim($_POST['pekerjaan'] ?? '');
    $alamat         = trim($_POST['alamat'] ?? '');
    $umur           = (int) date_diff(date_create($tgl_lahir), date_create('today'))->y;

    $cek = $conn->prepare("SELECT id FROM relawan WHERE email = ? AND id != ?");
    $cek->execute([$email, $id_edit]);
    if ($cek->rowCount() > 0) {
        $error_edit = 'Email sudah digunakan relawan lain!';
    } else {
        $stmt = $conn->prepare("UPDATE relawan SET
            nama=?, email=?, no_hp=?, tgl_lahir=?, umur=?, jenis_kelamin=?,
            goldar=?, berat_badan=?, kota=?, pekerjaan=?, alamat=?
            WHERE id=?");
        if ($stmt->execute([$nama, $email, $no_hp, $tgl_lahir, $umur, $jenis_kelamin, $goldar, $berat_badan, $kota, $pekerjaan, $alamat, $id_edit])) {
            header("Location: relawan_admin.php?pesan=edit_sukses");
            exit();
        } else {
            $error_edit = 'Gagal memperbarui data!';
        }
    }
}

$search    = trim($_GET['search'] ?? '');
$filter_gd = $_GET['goldar'] ?? '';
$filter_jk = $_GET['jk'] ?? '';
$page      = max(1, (int) ($_GET['page'] ?? 1));
$per_page  = 10;
$offset    = ($page - 1) * $per_page;

$where  = "WHERE 1=1";
$params = [];
if ($search !== '') {
    $where   .= " AND (nama LIKE ? OR email LIKE ? OR no_hp LIKE ? OR kota LIKE ?)";
    $s        = "%$search%";
    $params   = [$s, $s, $s, $s];
}
if ($filter_gd !== '') { $where .= " AND goldar = ?"; $params[] = $filter_gd; }
if ($filter_jk !== '') { $where .= " AND jenis_kelamin = ?"; $params[] = $filter_jk; }

$q_total = $conn->prepare("SELECT COUNT(*) FROM relawan $where");
$q_total->execute($params);
$total    = (int) $q_total->fetchColumn();
$total_pg = (int) ceil($total / $per_page);

$q_data = $conn->prepare("SELECT * FROM relawan $where ORDER BY tanggal_daftar DESC LIMIT :lim OFFSET :off");
foreach ($params as $i => $val) $q_data->bindValue($i + 1, $val);
$q_data->bindValue(':lim', $per_page, PDO::PARAM_INT);
$q_data->bindValue(':off', $offset,   PDO::PARAM_INT);
$q_data->execute();

// Statistik
$stat_total  = (int) $conn->query("SELECT COUNT(*) FROM relawan")->fetchColumn();
$stat_bulan  = (int) $conn->query("SELECT COUNT(*) FROM relawan WHERE MONTH(tanggal_daftar)=MONTH(CURDATE()) AND YEAR(tanggal_daftar)=YEAR(CURDATE())")->fetchColumn();
$goldar_rows = $conn->query("SELECT goldar, COUNT(*) as total FROM relawan WHERE goldar IS NOT NULL GROUP BY goldar")->fetchAll();
$goldar_stat = [];
foreach ($goldar_rows as $r) $goldar_stat[$r['goldar']] = $r['total'];

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Relawan — DonorIn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/admin.css">
    
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
        <div class="nav-section">
            <div class="nav-label">Utama</div>
            <a href="dashboard_admin.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
        </div>
        <div class="nav-section">
            <div class="nav-label">Pengguna</div>
            <a href="pasien_admin.php" class="nav-item"><i class="fas fa-user-injured"></i> Pasien</a>
            <a href="pendonor_admin.php" class="nav-item"><i class="fas fa-hand-holding-heart"></i> Pendonor</a>
            <a href="relawan_admin.php" class="nav-item active"><i class="fas fa-people-carry-box"></i> Relawan PMI</a>
        </div>
        <div class="nav-section">
            <div class="nav-label">Event</div>
            <a href="event_donor_admin.php" class="nav-item"><i class="fas fa-calendar-alt"></i> Event Donor</a>
            <a href="event_sosialisasi_admin.php" class="nav-item"><i class="fas fa-bullhorn"></i> Event Sosialisasi</a>
        </div>
        <div class="nav-section">
            <div class="nav-label">Lainnya</div>
            <a href="kritik_saran_admin.php" class="nav-item"><i class="fas fa-comments"></i> Kritik & Saran</a>
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
        <a href="../../auth/logout_admin.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </div>
</aside>

<!-- ══ MAIN ══ -->
<main class="main">
    <header class="topbar">
        <div>
            <div class="topbar-title">Manajemen Relawan PMI</div>
            <div class="topbar-breadcrumb">
                <a href="dashboard_admin.php" style="color:var(--abu-sedang);text-decoration:none;">DonorIn</a>
                › <span>Relawan PMI</span>
            </div>
        </div>
        <div class="topbar-right">
            <div class="date-chip"><i class="fas fa-calendar-day"></i><?= date('d M Y') ?></div>
        </div>
    </header>

    <div class="content">

        <!-- NOTIFIKASI -->
        <?php if ($pesan === 'tambah_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Relawan berhasil ditambahkan!</div>
        <?php elseif ($pesan === 'edit_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Data relawan berhasil diperbarui!</div>
        <?php elseif ($pesan === 'hapus_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Relawan berhasil dihapus.</div>
        <?php endif; ?>
        <?php if ($error_tambah): ?>
        <div class="notif notif-error"><i class="fas fa-exclamation-circle"></i> <?= $error_tambah ?></div>
        <?php endif; ?>
        <?php if ($error_edit): ?>
        <div class="notif notif-error"><i class="fas fa-exclamation-circle"></i> <?= $error_edit ?></div>
        <?php endif; ?>

        <!-- MINI STATS -->
        <div class="mini-stats">
            <div class="mini-card">
                <div class="mini-icon mi-ungu"><i class="fas fa-people-carry-box"></i></div>
                <div><div class="mini-val"><?= $stat_total ?></div><div class="mini-label">Total Relawan</div></div>
            </div>
            <div class="mini-card">
                <div class="mini-icon mi-pink"><i class="fas fa-user-plus"></i></div>
                <div><div class="mini-val"><?= $stat_bulan ?></div><div class="mini-label">Daftar Bln Ini</div></div>
            </div>
            <!-- <div class="mini-card">
                <div class="mini-icon mi-hijau"><i class="fas fa-heart"></i></div>
                <div><div class="mini-val"><?= $stat_aktif ?></div><div class="mini-label">Pernah Donor</div></div>
            </div> -->
            <?php foreach (['A'=>'mi-a','B'=>'mi-b','O'=>'mi-o','AB'=>'mi-ab'] as $gd => $cls): ?>
            <div class="mini-card">
                <div class="mini-icon <?= $cls ?>"><i class="fas fa-tint"></i></div>
                <div><div class="mini-val"><?= $goldar_stat[$gd] ?? 0 ?></div><div class="mini-label">Gol. <?= $gd ?></div></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- PANEL -->
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="fas fa-people-carry-box"></i> Daftar Relawan PMI
                    <span style="font-size:12px;font-weight:500;color:var(--abu-sedang);">
                        (<?= $total ?> data<?= ($search||$filter_gd||$filter_jk)?' — difilter':'' ?>)
                    </span>
                </div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <div style="position:relative;">
                            <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--abu-sedang);font-size:13px;pointer-events:none;"></i>
                            <input type="text" name="search" class="input-search"
                                   placeholder="Cari nama, email, kota…"
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <select name="goldar" class="select-filter" onchange="this.form.submit()">
                            <option value="">Semua Gol.</option>
                            <?php foreach (['A','B','O','AB'] as $g): ?>
                            <option value="<?= $g ?>" <?= $filter_gd===$g?'selected':'' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="jk" class="select-filter" onchange="this.form.submit()">
                            <option value="">Semua JK</option>
                            <option value="L" <?= $filter_jk==='L'?'selected':'' ?>>Laki-laki</option>
                            <option value="P" <?= $filter_jk==='P'?'selected':'' ?>>Perempuan</option>
                        </select>
                        <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-filter"></i> Filter</button>
                        <?php if ($search||$filter_gd||$filter_jk): ?>
                        <a href="relawan_admin.php" class="btn btn-ghost btn-sm"><i class="fas fa-times"></i> Reset</a>
                        <?php endif; ?>
                    </form>
                    <button class="btn btn-primary btn-sm" onclick="bukaModal('modalTambah')">
                        <i class="fas fa-plus"></i> Tambah Relawan
                    </button>
                </div>
            </div>

            <div class="tbl-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th style="width:36px;">#</th>
                            <th>Nama Relawan</th>
                            <th>JK</th>
                            <th>Gol. Darah</th>
                            <th>No. HP</th>
                            <th>Kota</th>
                            <th>Umur</th>
                            <th>Pernah Donor</th>
                            <th>Tgl. Daftar</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($q_data->rowCount() > 0):
                        $no = $offset + 1;
                        while ($row = $q_data->fetch()):
                            $gd = $row['goldar'] ?? '';
                    ?>
                        <tr>
                            <td style="color:var(--abu-sedang);font-size:12px;font-weight:600;"><?= $no++ ?></td>
                            <td>
                                <div class="tbl-name">
                                    <div class="tbl-avatar"><?= strtoupper(substr($row['nama'],0,1)) ?></div>
                                    <div>
                                        <div class="tbl-name-text"><?= htmlspecialchars($row['nama']) ?></div>
                                        <div class="tbl-name-sub"><?= htmlspecialchars($row['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="jk-badge jk-<?= $row['jenis_kelamin'] ?>">
                                    <?= $row['jenis_kelamin'] === 'L' ? '♂ L' : '♀ P' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (in_array($gd, ['A','B','O','AB'])): ?>
                                <span class="gd-badge gd-<?= $gd ?>"><?= $gd ?></span>
                                <?php else: ?>
                                <span class="badge badge-abu">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:13px;"><?= htmlspecialchars($row['no_hp']) ?></td>
                            <td style="font-size:13px;color:var(--teks-sedang);"><?= htmlspecialchars($row['kota']) ?></td>
                            <td style="font-size:13px;text-align:center;"><?= $row['umur'] ?> th</td>
                            
                            <td style="font-size:12px;color:var(--abu-sedang);">
                                <?= $row['tanggal_daftar'] ? date('d M Y', strtotime($row['tanggal_daftar'])) : '—' ?>
                            </td>
                            <td>
                                <div class="aksi-wrap">
                                    <button class="btn btn-sm btn-edit btn-icon"
                                        onclick='bukaModalEdit(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)'
                                        title="Edit"><i class="fas fa-pen"></i></button>
                                    <button class="btn btn-sm btn-hapus btn-icon"
                                        onclick='konfirmasiHapus(<?= $row['id'] ?>, "<?= addslashes(htmlspecialchars($row['nama'])) ?>")'
                                        title="Hapus"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="10">
                            <div class="empty-state">
                                <i class="fas fa-people-carry-box"></i>
                                <p><?= ($search||$filter_gd||$filter_jk) ? 'Tidak ada relawan yang cocok dengan filter.' : 'Belum ada relawan terdaftar.' ?></p>
                            </div>
                        </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pg > 1): ?>
            <div class="pagination-wrap">
                <div class="pagi-info">
                    Menampilkan <strong><?= $offset+1 ?>–<?= min($offset+$per_page, $total) ?></strong>
                    dari <strong><?= $total ?></strong> relawan
                </div>
                <div class="pagi-btns">
                    <?php $qs = http_build_query(['search'=>$search,'goldar'=>$filter_gd,'jk'=>$filter_jk]); ?>
                    <a href="?page=<?= $page-1 ?>&<?= $qs ?>" class="pagi-btn <?= $page<=1?'disabled':'' ?>"><i class="fas fa-chevron-left"></i></a>
                    <?php for ($i=1; $i<=$total_pg; $i++): ?>
                    <a href="?page=<?= $i ?>&<?= $qs ?>" class="pagi-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <a href="?page=<?= $page+1 ?>&<?= $qs ?>" class="pagi-btn <?= $page>=$total_pg?'disabled':'' ?>"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<!-- ══ MODAL TAMBAH ══ -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal" style="max-width:640px;">
        <div class="modal-head">
            <div class="modal-title"><i class="fas fa-user-plus"></i> Tambah Relawan PMI</div>
            <button class="modal-close" onclick="tutupModal('modalTambah')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="relawan_admin.php">
            <input type="hidden" name="aksi" value="tambah">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" name="nama" class="form-input" placeholder="Nama lengkap" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="req">*</span></label>
                        <input type="email" name="email" class="form-input" placeholder="email@contoh.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP <span class="req">*</span></label>
                        <input type="text" name="no_hp" class="form-input" placeholder="08xxxxxxxxxx" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir <span class="req">*</span></label>
                        <input type="date" name="tgl_lahir" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin <span class="req">*</span></label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Golongan Darah <span class="req">*</span></label>
                        <select name="goldar" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach (['A','B','O','AB'] as $g): ?>
                            <option value="<?= $g ?>"><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Berat Badan (kg) <span class="req">*</span></label>
                        <input type="number" name="berat_badan" class="form-input" placeholder="Min. 45 kg" min="30" max="200" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kota <span class="req">*</span></label>
                        <input type="text" name="kota" class="form-input" placeholder="Contoh: Mataram" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" name="pekerjaan" class="form-input" placeholder="Mahasiswa / Karyawan">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-input" rows="2" placeholder="Jl. ... No. ..." style="resize:vertical;"></textarea>
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

<!-- ══ MODAL EDIT ══ -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal" style="max-width:640px;">
        <div class="modal-head">
            <div class="modal-title"><i class="fas fa-pen"></i> Edit Data Relawan</div>
            <button class="modal-close" onclick="tutupModal('modalEdit')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="relawan_admin.php">
            <input type="hidden" name="aksi" value="edit">
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
                        <label class="form-label">No. HP <span class="req">*</span></label>
                        <input type="text" name="no_hp" id="edit_no_hp" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir <span class="req">*</span></label>
                        <input type="date" name="tgl_lahir" id="edit_tgl_lahir" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin <span class="req">*</span></label>
                        <select name="jenis_kelamin" id="edit_jk" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Golongan Darah <span class="req">*</span></label>
                        <select name="goldar" id="edit_goldar" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach (['A','B','O','AB'] as $g): ?>
                            <option value="<?= $g ?>"><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Berat Badan (kg) <span class="req">*</span></label>
                        <input type="number" name="berat_badan" id="edit_bb" class="form-input" min="30" max="200" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kota <span class="req">*</span></label>
                        <input type="text" name="kota" id="edit_kota" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" name="pekerjaan" id="edit_pekerjaan" class="form-input">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="alamat" id="edit_alamat" class="form-input" rows="2" style="resize:vertical;"></textarea>
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

<!-- ══ MODAL HAPUS ══ -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal" style="max-width:400px;">
        <div class="modal-body" style="padding:32px 28px;text-align:center;">
            <div class="hapus-icon"><i class="fas fa-trash-alt"></i></div>
            <h3 style="font-size:18px;font-weight:800;margin-bottom:8px;">Hapus Relawan?</h3>
            <p style="font-size:14px;color:var(--teks-sedang);margin-bottom:4px;">Kamu akan menghapus relawan:</p>
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

function toggleTerakhir(mode) {
    var grup = document.getElementById('grup_terakhir_' + mode);
    if (sel && grup) grup.style.display = sel.value === 'ya' ? 'block' : 'none';
}

function bukaModalEdit(data) {
    document.getElementById('edit_id').value           = data.id;
    document.getElementById('edit_nama').value         = data.nama;
    document.getElementById('edit_email').value        = data.email;
    document.getElementById('edit_no_hp').value        = data.no_hp         || '';
    document.getElementById('edit_tgl_lahir').value    = data.tgl_lahir     || '';
    document.getElementById('edit_jk').value           = data.jenis_kelamin || '';
    document.getElementById('edit_goldar').value       = data.goldar        || '';
    document.getElementById('edit_bb').value           = data.berat_badan   || '';
    document.getElementById('edit_kota').value         = data.kota          || '';
    document.getElementById('edit_pekerjaan').value    = data.pekerjaan     || '';
    document.getElementById('edit_alamat').value       = data.alamat        || '';

    bukaModal('modalEdit');
}

function konfirmasiHapus(id, nama) {
    document.getElementById('hapus_nama').textContent = nama;
    document.getElementById('hapus_link').href = 'relawan_admin.php?hapus=' + id;
    bukaModal('modalHapus');
}

<?php if ($error_tambah): ?> bukaModal('modalTambah'); <?php endif; ?>
<?php if ($error_edit):   ?> bukaModal('modalEdit');   <?php endif; ?>

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