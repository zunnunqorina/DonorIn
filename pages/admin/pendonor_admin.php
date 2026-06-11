<?php
include '../../config/koneksi.php';
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../../auth/login_admin.php");
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM user WHERE id = ? AND role = 'pendonor'");
    $stmt->execute([$id_hapus]);
    header("Location: pendonor_admin.php?pesan=hapus_sukses");
    exit();
}

$error_tambah = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama   = trim($_POST['nama']);
    $email  = trim($_POST['email']);
    $no_hp  = trim($_POST['no_hp']);
    $goldar = $_POST['goldar'];
    $kota   = trim($_POST['kota']);
    $pass   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek email duplikat
    $cek = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $cek->execute([$email]);
    if ($cek->rowCount() > 0) {
        $error_tambah = 'Email sudah terdaftar!';
    } else {
        $stmt = $conn->prepare("INSERT INTO user (nama, email, password, role, no_hp, goldar, kota)
              VALUES (?, ?, ?, 'pendonor', ?, ?, ?)");
        if ($stmt->execute([$nama, $email, $pass, $no_hp, $goldar, $kota])) {
            header("Location: pendonor_admin.php?pesan=tambah_sukses");
            exit();
        } else {
            $error_tambah = 'Gagal menambahkan pendonor.';
        }
    }
}

$error_edit = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id_edit = (int) $_POST['id_edit'];
    $nama    = trim($_POST['nama']);
    $email   = trim($_POST['email']);
    $no_hp   = trim($_POST['no_hp']);
    $goldar  = $_POST['goldar'];
    $kota    = trim($_POST['kota']);

    // Cek email duplikat (selain diri sendiri)
    $cek = $conn->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
    $cek->execute([$email, $id_edit]);
    if ($cek->rowCount() > 0) {
        $error_edit = 'Email sudah digunakan akun lain!';
    } else {
        if (!empty($_POST['password'])) {
            $pass_baru = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE user SET nama=?, email=?, no_hp=?, goldar=?, kota=?, password=? WHERE id=? AND role='pendonor'");
            $params = [$nama, $email, $no_hp, $goldar, $kota, $pass_baru, $id_edit];
        } else {
            $stmt = $conn->prepare("UPDATE user SET nama=?, email=?, no_hp=?, goldar=?, kota=? WHERE id=? AND role='pendonor'");
            $params = [$nama, $email, $no_hp, $goldar, $kota, $id_edit];
        }
        if ($stmt->execute($params)) {
            header("Location: pendonor_admin.php?pesan=edit_sukses");
            exit();
        } else {
            $error_edit = 'Gagal memperbarui data.';
        }
    }
}

$search    = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_gd = isset($_GET['goldar'])  ? $_GET['goldar']      : '';
$page      = isset($_GET['page'])    ? max(1, (int) $_GET['page']) : 1;
$per_page  = 10;
$offset    = ($page - 1) * $per_page;

$where  = "WHERE role = 'pendonor'";
$params = [];
if ($search !== '') {
    $where   .= " AND (nama LIKE ? OR email LIKE ? OR no_hp LIKE ? OR kota LIKE ?)";
    $like     = "%$search%";
    $params   = array_merge($params, [$like, $like, $like, $like]);
}
if ($filter_gd !== '') {
    $where  .= " AND goldar = ?";
    $params[] = $filter_gd;
}

// Total untuk paginasi
$q_total = $conn->prepare("SELECT COUNT(*) as total FROM user $where");
$q_total->execute($params);
$total    = $q_total->fetch(PDO::FETCH_ASSOC)['total'];
$total_pg = ceil($total / $per_page);

// Data pendonor
// Data pendonor
$q_pendonor = $conn->prepare("SELECT * FROM user $where ORDER BY tanggal_daftar DESC LIMIT {$per_page} OFFSET {$offset}");
$q_pendonor->execute($params);
$pendonor_rows = $q_pendonor->fetchAll(PDO::FETCH_ASSOC);

// Statistik ringkas
$stat_total = $conn->query("SELECT COUNT(*) as t FROM user WHERE role='pendonor'")->fetch(PDO::FETCH_ASSOC)['t'] ?? 0;
$stat_bulan = $conn->query("SELECT COUNT(*) as t FROM user WHERE role='pendonor' AND MONTH(tanggal_daftar)=MONTH(CURDATE()) AND YEAR(tanggal_daftar)=YEAR(CURDATE())")->fetch(PDO::FETCH_ASSOC)['t'] ?? 0;

$goldar_stat = [];
foreach ($conn->query("SELECT goldar, COUNT(*) as total FROM user WHERE role='pendonor' AND goldar IS NOT NULL GROUP BY goldar ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $goldar_stat[$r['goldar']] = $r['total'];
}

// Data untuk form edit (jika ada)
$edit_data = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id_e      = (int) $_GET['edit'];
    $q_edit    = $conn->prepare("SELECT * FROM user WHERE id = ? AND role = 'pendonor'");
    $q_edit->execute([$id_e]);
    $edit_data = $q_edit->fetch(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/admin.css">
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
            <a href="pasien_admin.php" class="nav-item"><i class="fas fa-user-injured"></i> Pasien</a>
            <a href="pendonor_admin.php" class="nav-item active"><i class="fas fa-hand-holding-heart"></i> Pendonor</a>
            <a href="relawan_admin.php" class="nav-item"><i class="fas fa-people-carry-box"></i> Relawan PMI</a>
        </div>
        <div class="nav-section">
            <div class="nav-label">Event</div>
            <a href="event_donor.php" class="nav-item"><i class="fas fa-calendar-alt"></i> Event Donor Darah</a>
            <a href="event_sosialisasi.php" class="nav-item"><i class="fas fa-bullhorn"></i> Event Sosialisasi</a>
        </div>
        <div class="nav-section">
            <div class="nav-label">Lainnya</div>
            <a href="kritik_saran_admin.php" class="nav-item"><i class="fas fa-comments"></i> Kritik & Saran</a>
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
                    <?php if (count($pendonor_rows) > 0):
                        $no = $offset + 1;
                        foreach ($pendonor_rows as $row):
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
                    <?php endforeach; else: ?>
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