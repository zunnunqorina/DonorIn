<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../../auth/login_admin.php");
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// HAPUS
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $stmt = $conn->prepare("DELETE FROM user WHERE id = ? AND role = 'pasien'");
    $stmt->execute([(int) $_GET['hapus']]);
    header("Location: pasien_admin.php?pesan=hapus_sukses");
    exit();
}

// TAMBAH
$error_tambah = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $nama  = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $goldar= $_POST['goldar'];
    $kota  = trim($_POST['kota']);
    $pass  = trim($_POST['password']);

    $cek = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $cek->execute([$email]);
    if ($cek->rowCount() > 0) {
        $error_tambah = 'Email sudah terdaftar!';
    } else {
        $stmt = $conn->prepare("INSERT INTO user (nama, email, password, role, no_hp, goldar, kota) VALUES (?,?,?,'pasien',?,?,?)");
        if ($stmt->execute([$nama, $email, $pass, $no_hp, $goldar, $kota])) {
            header("Location: pasien_admin.php?pesan=tambah_sukses"); exit();
        } else {
            $error_tambah = 'Gagal menambahkan pasien!';
        }
    }
}

// EDIT
$error_edit = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'edit') {
    $id_edit = (int) $_POST['id_edit'];
    $nama    = trim($_POST['nama']);
    $email   = trim($_POST['email']);
    $no_hp   = trim($_POST['no_hp']);
    $goldar  = $_POST['goldar'];
    $kota    = trim($_POST['kota']);

    $cek = $conn->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
    $cek->execute([$email, $id_edit]);
    if ($cek->rowCount() > 0) {
        $error_edit = 'Email sudah digunakan akun lain!';
    } else {
        if (!empty($_POST['password'])) {
            $stmt = $conn->prepare("UPDATE user SET nama=?,email=?,no_hp=?,goldar=?,kota=?,password=? WHERE id=? AND role='pasien'");
            $ok = $stmt->execute([$nama, $email, $no_hp, $goldar, $kota, $_POST['password'], $id_edit]);
        } else {
            $stmt = $conn->prepare("UPDATE user SET nama=?,email=?,no_hp=?,goldar=?,kota=? WHERE id=? AND role='pasien'");
            $ok = $stmt->execute([$nama, $email, $no_hp, $goldar, $kota, $id_edit]);
        }
        if ($ok) {
            header("Location: pasien_admin.php?pesan=edit_sukses"); exit();
        } else {
            $error_edit = 'Gagal memperbarui data!';
        }
    }
}

// FILTER & PAGINASI
$search    = trim($_GET['search'] ?? '');
$filter_gd = $_GET['goldar'] ?? '';
$page      = max(1, (int) ($_GET['page'] ?? 1));
$per_page  = 10;
$offset    = ($page - 1) * $per_page;

$where  = "WHERE role = 'pasien'";
$params = [];
if ($search !== '') {
    $where   .= " AND (nama LIKE ? OR email LIKE ? OR no_hp LIKE ? OR kota LIKE ?)";
    $s        = "%$search%";
    $params   = [$s, $s, $s, $s];
}
if ($filter_gd !== '') {
    $where   .= " AND goldar = ?";
    $params[] = $filter_gd;
}

$q_total = $conn->prepare("SELECT COUNT(*) FROM user $where");
$q_total->execute($params);
$total    = (int) $q_total->fetchColumn();
$total_pg = (int) ceil($total / $per_page);

$q_pasien = $conn->prepare("SELECT * FROM user $where ORDER BY tanggal_daftar DESC LIMIT :lim OFFSET :off");
foreach ($params as $i => $val) $q_pasien->bindValue($i + 1, $val);
$q_pasien->bindValue(':lim', $per_page, PDO::PARAM_INT);
$q_pasien->bindValue(':off', $offset,   PDO::PARAM_INT);
$q_pasien->execute();

// Statistik
$stat_total = (int) $conn->query("SELECT COUNT(*) FROM user WHERE role='pasien'")->fetchColumn();
$stat_bulan = (int) $conn->query("SELECT COUNT(*) FROM user WHERE role='pasien' AND MONTH(tanggal_daftar)=MONTH(CURDATE()) AND YEAR(tanggal_daftar)=YEAR(CURDATE())")->fetchColumn();
$goldar_rows = $conn->query("SELECT goldar, COUNT(*) as total FROM user WHERE role='pasien' AND goldar IS NOT NULL GROUP BY goldar")->fetchAll();
$goldar_stat = [];
foreach ($goldar_rows as $r) $goldar_stat[$r['goldar']] = $r['total'];

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pasien — DonorIn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/admin.css">
</head>
<body>

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
            <a href="pasien_admin.php" class="nav-item active"><i class="fas fa-user-injured"></i> Pasien</a>
            <a href="pendonor_admin.php" class="nav-item"><i class="fas fa-hand-holding-heart"></i> Pendonor</a>
            <a href="relawan_admin.php" class="nav-item"><i class="fas fa-people-carry-box"></i> Relawan</a>
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

<main class="main">
    <header class="topbar">
        <div>
            <div class="topbar-title">Manajemen Pasien</div>
            <div class="topbar-breadcrumb">
                <a href="dashboard_admin.php" style="color:var(--abu-sedang);text-decoration:none;">DonorIn</a>
                › <span>Pasien</span>
            </div>
        </div>
        <div class="topbar-right">
            <div class="date-chip"><i class="fas fa-calendar-day"></i><?= date('d M Y') ?></div>
        </div>
    </header>

    <div class="content">

        <?php if ($pesan === 'tambah_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Pasien berhasil ditambahkan!</div>
        <?php elseif ($pesan === 'edit_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Data pasien berhasil diperbarui!</div>
        <?php elseif ($pesan === 'hapus_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Pasien berhasil dihapus.</div>
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
                <div class="mini-icon mi-biru"><i class="fas fa-user-injured"></i></div>
                <div><div class="mini-val"><?= $stat_total ?></div><div class="mini-label">Total Pasien</div></div>
            </div>
            <div class="mini-card">
                <div class="mini-icon mi-pink"><i class="fas fa-user-plus"></i></div>
                <div><div class="mini-val"><?= $stat_bulan ?></div><div class="mini-label">Daftar Bulan Ini</div></div>
            </div>
            <?php foreach (['A'=>'mi-a','B'=>'mi-b','O'=>'mi-o','AB'=>'mi-ab'] as $gd => $cls): ?>
            <div class="mini-card">
                <div class="mini-icon <?= $cls ?>"><i class="fas fa-tint"></i></div>
                <div><div class="mini-val"><?= $goldar_stat[$gd] ?? 0 ?></div><div class="mini-label">Golongan <?= $gd ?></div></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- PANEL TABEL -->
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="fas fa-user-injured"></i> Daftar Pasien
                    <span style="font-size:12px;font-weight:500;color:var(--abu-sedang);">
                        (<?= $total ?> data<?= ($search || $filter_gd) ? ' — difilter' : '' ?>)
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
                            <option value="">Semua Gol. Darah</option>
                            <?php foreach (['A','B','O','AB'] as $g): ?>
                            <option value="<?= $g ?>" <?= $filter_gd===$g?'selected':'' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-filter"></i> Filter</button>
                        <?php if ($search || $filter_gd): ?>
                        <a href="pasien_admin.php" class="btn btn-ghost btn-sm"><i class="fas fa-times"></i> Reset</a>
                        <?php endif; ?>
                    </form>
                    <button class="btn btn-primary btn-sm" onclick="bukaModal('modalTambah')">
                        <i class="fas fa-plus"></i> Tambah Pasien
                    </button>
                </div>
            </div>

            <div class="tbl-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Nama Pasien</th>
                            <th>Gol. Darah</th>
                            <th>No. HP</th>
                            <th>Kota</th>
                            <th>Tanggal Daftar</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($q_pasien->rowCount() > 0):
                        $no = $offset + 1;
                        while ($row = $q_pasien->fetch()):
                            $gd = $row['goldar'] ?? '';
                    ?>
                        <tr>
                            <td style="color:var(--abu-sedang);font-size:12px;font-weight:600;"><?= $no++ ?></td>
                            <td>
                                <div class="tbl-name">
                                    <div class="tbl-avatar" style="background:#EDF4FF;color:#2563EB;">
                                        <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="tbl-name-text"><?= htmlspecialchars($row['nama']) ?></div>
                                        <div class="tbl-name-sub"><?= htmlspecialchars($row['email']) ?></div>
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
                        <tr><td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-user-injured"></i>
                                <p><?= ($search || $filter_gd) ? 'Tidak ada pasien yang cocok dengan filter.' : 'Belum ada pasien terdaftar.' ?></p>
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
                    dari <strong><?= $total ?></strong> pasien
                </div>
                <div class="pagi-btns">
                    <?php $qs = http_build_query(['search'=>$search,'goldar'=>$filter_gd]); ?>
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

<!-- MODAL TAMBAH -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal" style="max-width:580px;">
        <div class="modal-head">
            <div class="modal-title"><i class="fas fa-user-plus"></i> Tambah Pasien Baru</div>
            <button class="modal-close" onclick="tutupModal('modalTambah')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="pasien_admin.php">
            <input type="hidden" name="aksi" value="tambah">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" name="nama" class="form-input" placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="req">*</span></label>
                        <input type="email" name="email" class="form-input" placeholder="email@contoh.com" required>
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
                            <?php foreach (['A','B','O','AB'] as $g): ?>
                            <option value="<?= $g ?>"><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group full">
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

<!-- MODAL EDIT -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal" style="max-width:580px;">
        <div class="modal-head">
            <div class="modal-title"><i class="fas fa-pen"></i> Edit Data Pasien</div>
            <button class="modal-close" onclick="tutupModal('modalEdit')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="pasien_admin.php">
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
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-input" placeholder="Kosongkan jika tidak diubah">
                        <div class="form-hint">Kosongkan jika tidak ingin mengubah password</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="no_hp" id="edit_no_hp" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Golongan Darah</label>
                        <select name="goldar" id="edit_goldar" class="form-select">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['A','B','O','AB'] as $g): ?>
                            <option value="<?= $g ?>"><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Kota</label>
                        <input type="text" name="kota" id="edit_kota" class="form-input">
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

<!-- MODAL HAPUS -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal" style="max-width:400px;">
        <div class="modal-body" style="padding:32px 28px;text-align:center;">
            <div class="hapus-icon"><i class="fas fa-trash-alt"></i></div>
            <h3 style="font-size:18px;font-weight:800;margin-bottom:8px;">Hapus Pasien?</h3>
            <p style="font-size:14px;color:var(--teks-sedang);margin-bottom:4px;">Kamu akan menghapus pasien:</p>
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

function bukaModalEdit(data) {
    document.getElementById('edit_id').value    = data.id;
    document.getElementById('edit_nama').value  = data.nama;
    document.getElementById('edit_email').value = data.email;
    document.getElementById('edit_no_hp').value = data.no_hp  || '';
    document.getElementById('edit_kota').value  = data.kota   || '';
    document.getElementById('edit_goldar').value= data.goldar || '';
    bukaModal('modalEdit');
}

function konfirmasiHapus(id, nama) {
    document.getElementById('hapus_nama').textContent = nama;
    document.getElementById('hapus_link').href = 'pasien_admin.php?hapus=' + id;
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