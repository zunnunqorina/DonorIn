<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
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
    $q_status  = mysqli_query($conn, "SELECT sudah_baca FROM kritik_saran WHERE id = $id_toggle");
    if ($row_s = mysqli_fetch_assoc($q_status)) {
        $baru = $row_s['sudah_baca'] ? 0 : 1;
        mysqli_query($conn, "UPDATE kritik_saran SET sudah_baca = $baru WHERE id = $id_toggle");
    }
    header("Location: kritik_saran_admin.php?pesan=update_sukses");
    exit();
}

// ── BALAS (simpan balasan admin) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'balas') {
    $id_pesan = (int) $_POST['id_pesan'];
    $balasan  = mysqli_real_escape_string($conn, trim($_POST['balasan']));
    if ($balasan !== '') {
        mysqli_query($conn, "UPDATE kritik_saran SET balasan = '$balasan', sudah_baca = 1, tgl_balas = NOW() WHERE id = $id_pesan");
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
if ($filter_baca === 'belum') $where .= " AND sudah_baca = 0";
if ($filter_baca === 'sudah') $where .= " AND sudah_baca = 1";
if ($search !== '')      $where .= " AND (nama LIKE '%$search%' OR email LIKE '%$search%' OR pesan LIKE '%$search%')";

$q_total   = mysqli_query($conn, "SELECT COUNT(*) as total FROM kritik_saran $where");
$total     = mysqli_fetch_assoc($q_total)['total'] ?? 0;
$total_pg  = ceil($total / $per_page);

$q_data = mysqli_query($conn, "SELECT * FROM kritik_saran $where ORDER BY tanggal DESC LIMIT $per_page OFFSET $offset");

// Statistik
$stat_total   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM kritik_saran"))['t'] ?? 0;
$stat_belum   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM kritik_saran WHERE sudah_baca = 0"))['t'] ?? 0;
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
    <link rel="stylesheet" href="../../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                        $sudah    = (bool) $baris['sudah_baca'];
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
