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
// HANDLE UBAH STATUS
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'ubah_status') {
    $id_ubah = (int) $_POST['id_permintaan'];
    $status  = $_POST['status_baru'];
    $allowed = ['menunggu', 'diproses', 'terpenuhi', 'dibatalkan'];
    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE permintaan_darah SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id_ubah]);
        header("Location: permintaan_darah_admin.php?pesan=status_sukses");
        exit();
    }
}

// ============================================================
// HANDLE HAPUS
// ============================================================
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $stmt = $conn->prepare("DELETE FROM permintaan_darah WHERE id = ?");
    $stmt->execute([(int) $_GET['hapus']]);
    header("Location: permintaan_darah_admin.php?pesan=hapus_sukses");
    exit();
}

// ============================================================
// FILTER & PAGINASI
// ============================================================
$search        = trim($_GET['search'] ?? '');
$filter_status = $_GET['filter_status'] ?? '';
$filter_goldar = $_GET['goldar'] ?? '';
$page          = max(1, (int) ($_GET['page'] ?? 1));
$per_page      = 10;
$offset        = ($page - 1) * $per_page;

$where  = "WHERE 1=1";
$params = [];

if ($search !== '') {
    $where   .= " AND (p.nama_rs LIKE ? OR p.kota LIKE ? OR ps.nama LIKE ? OR ps.email LIKE ?)";
    $s        = "%$search%";
    $params   = [$s, $s, $s, $s];
}
if ($filter_status !== '') { $where .= " AND p.status = ?";   $params[] = $filter_status; }
if ($filter_goldar !== '') { $where .= " AND p.goldar = ?";   $params[] = $filter_goldar; }

// JOIN dengan tabel pasien untuk dapat nama pasien
$base_query = "FROM permintaan_darah p
               LEFT JOIN pasien ps ON p.pasien_id = ps.id";

$q_total = $conn->prepare("SELECT COUNT(*) $base_query $where");
$q_total->execute($params);
$total    = (int) $q_total->fetchColumn();
$total_pg = (int) ceil($total / $per_page);

$q_data = $conn->prepare("SELECT p.*, ps.nama as nama_pasien, ps.no_hp as hp_pasien, ps.email as email_pasien
    $base_query $where
    ORDER BY
        CASE p.status WHEN 'menunggu' THEN 1 WHEN 'diproses' THEN 2 WHEN 'terpenuhi' THEN 3 ELSE 4 END,
        p.tanggal DESC
    LIMIT ? OFFSET ?");
foreach ($params as $i => $val) {
    $q_data->bindValue($i + 1, $val);
}
$param_count = count($params);
$q_data->bindValue($param_count + 1, $per_page, PDO::PARAM_INT);
$q_data->bindValue($param_count + 2, $offset,   PDO::PARAM_INT);
$q_data->execute();

// ============================================================
// STATISTIK
// ============================================================
$stat_total     = (int) $conn->query("SELECT COUNT(*) FROM permintaan_darah")->fetchColumn();
$stat_menunggu  = (int) $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE status='menunggu'")->fetchColumn();
$stat_diproses  = (int) $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE status='diproses'")->fetchColumn();
$stat_terpenuhi = (int) $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE status='terpenuhi'")->fetchColumn();
$stat_batal     = (int) $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE status='dibatalkan'")->fetchColumn();
$stat_bulan     = (int) $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())")->fetchColumn();

// Golongan darah yang paling banyak diminta
$goldar_rows = $conn->query("SELECT goldar, COUNT(*) as total FROM permintaan_darah GROUP BY goldar ORDER BY total DESC")->fetchAll();
$goldar_stat = [];
foreach ($goldar_rows as $r) $goldar_stat[$r['goldar']] = $r['total'];

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Permintaan Darah — DonorIn Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../assets/script.js" defer></script>
    <script src="../../assets/admin.js" defer></script>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<?php
$halaman_aktif_admin = 'permintaan_darah';
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
                <div class="topbar-title">Kelola Permintaan Darah</div>
                <div class="topbar-breadcrumb">
                    <a href="dashboard_admin.php">DonorIn</a> /
                    <span>Permintaan</span> /
                    <span>Permintaan Darah</span>
                </div>
            </div>
        </div>
        <div class="topbar-right">
            <?php if ($stat_menunggu > 0): ?>
            <div style="display:flex;align-items:center;gap:7px;padding:8px 14px;border-radius:10px;background:#FFF8E6;border:1px solid #FDE68A;font-size:13px;font-weight:600;color:#D4900A;">
                <i class="fas fa-exclamation-triangle"></i>
                <?= $stat_menunggu ?> permintaan menunggu
            </div>
            <?php endif; ?>
            <div class="date-chip"><i class="fas fa-calendar-day"></i><?= date('d M Y') ?></div>
            <a href="kritik_saran_admin.php" class="topbar-btn" title="Kritik & Saran">
                <i class="fas fa-bell"></i>
                <?php if ($side_total_ks > 0): ?><span class="notif-dot"></span><?php endif; ?>
            </a>
        </div>
    </header>

    <div class="content">

        <!-- NOTIFIKASI -->
        <?php if ($pesan === 'status_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Status permintaan berhasil diperbarui!</div>
        <?php elseif ($pesan === 'hapus_sukses'): ?>
        <div class="notif notif-sukses"><i class="fas fa-check-circle"></i> Permintaan berhasil dihapus.</div>
        <?php endif; ?>

        <!-- MINI STATS — bisa diklik untuk filter -->
        <div class="mini-stats">
            <a href="permintaan_darah_admin.php" style="text-decoration:none;">
                <div class="mini-card <?= $filter_status===''?'aktif-filter':'' ?>">
                    <div class="mini-icon mi-merah"><i class="fas fa-list"></i></div>
                    <div><div class="mini-val"><?= $stat_total ?></div><div class="mini-label">Semua</div></div>
                </div>
            </a>
            <a href="permintaan_darah_admin.php?filter_status=menunggu" style="text-decoration:none;">
                <div class="mini-card <?= $filter_status==='menunggu'?'aktif-filter':'' ?>">
                    <div class="mini-icon mi-kuning"><i class="fas fa-clock"></i></div>
                    <div><div class="mini-val"><?= $stat_menunggu ?></div><div class="mini-label">Menunggu</div></div>
                </div>
            </a>
            <a href="permintaan_darah_admin.php?filter_status=diproses" style="text-decoration:none;">
                <div class="mini-card <?= $filter_status==='diproses'?'aktif-filter':'' ?>">
                    <div class="mini-icon mi-biru"><i class="fas fa-spinner"></i></div>
                    <div><div class="mini-val"><?= $stat_diproses ?></div><div class="mini-label">Diproses</div></div>
                </div>
            </a>
            <a href="permintaan_darah_admin.php?filter_status=terpenuhi" style="text-decoration:none;">
                <div class="mini-card <?= $filter_status==='terpenuhi'?'aktif-filter':'' ?>">
                    <div class="mini-icon mi-hijau"><i class="fas fa-check-circle"></i></div>
                    <div><div class="mini-val"><?= $stat_terpenuhi ?></div><div class="mini-label">Terpenuhi</div></div>
                </div>
            </a>
            <a href="permintaan_darah_admin.php?filter_status=dibatalkan" style="text-decoration:none;">
                <div class="mini-card <?= $filter_status==='dibatalkan'?'aktif-filter':'' ?>">
                    <div class="mini-icon mi-abu"><i class="fas fa-ban"></i></div>
                    <div><div class="mini-val"><?= $stat_batal ?></div><div class="mini-label">Dibatalkan</div></div>
                </div>
            </a>
            <div class="mini-card">
                <div class="mini-icon mi-pink"><i class="fas fa-calendar-day"></i></div>
                <div><div class="mini-val"><?= $stat_bulan ?></div><div class="mini-label">Bulan Ini</div></div>
            </div>
        </div>

        <!-- REKAP GOLONGAN DARAH -->
        <?php
        $max_goldar = max(array_values($goldar_stat) ?: [1]);
        ?>
        <div class="section-header" style="margin-bottom:14px;">
            <div class="section-title"><i class="fas fa-tint"></i> Rekap Permintaan per Golongan Darah</div>
        </div>
        <div class="rekap-goldar" style="margin-bottom:24px;">
            <?php
            $warna = ['A'=>['#E65100','#FFF3E0'],'B'=>['#1565C0','#E3F2FD'],'O'=>['#2E7D32','#E8F5E9'],'AB'=>['#6A1B9A','#F3E5F5']];
            foreach (['A','B','O','AB'] as $g):
                $jml   = $goldar_stat[$g] ?? 0;
                $pct   = $max_goldar > 0 ? round(($jml / $max_goldar) * 100) : 0;
                $warna_teks = $warna[$g][0];
                $warna_bg   = $warna[$g][1];
            ?>
            <div class="rekap-card">
                <div class="rekap-gd" style="color:<?= $warna_teks ?>;"><?= $g ?></div>
                <div class="rekap-total"><?= $jml ?> permintaan</div>
                <div class="rekap-bar">
                    <div class="rekap-bar-fill" style="width:<?= $pct ?>%;background:<?= $warna_teks ?>;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- PANEL TABEL -->
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="fas fa-hand-holding-medical"></i> Daftar Permintaan Darah
                    <span style="font-size:12px;font-weight:500;color:var(--abu-sedang);">
                        (<?= $total ?> data<?= ($search||$filter_status||$filter_goldar)?' — difilter':'' ?>)
                    </span>
                </div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <div style="position:relative;">
                            <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--abu-sedang);font-size:13px;pointer-events:none;"></i>
                            <input type="text" name="search" class="input-search"
                                   placeholder="Cari RS, kota, pasien…"
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <select name="filter_status" class="select-filter" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="menunggu"   <?= $filter_status==='menunggu'?'selected':'' ?>>Menunggu</option>
                            <option value="diproses"   <?= $filter_status==='diproses'?'selected':'' ?>>Diproses</option>
                            <option value="terpenuhi"  <?= $filter_status==='terpenuhi'?'selected':'' ?>>Terpenuhi</option>
                            <option value="dibatalkan" <?= $filter_status==='dibatalkan'?'selected':'' ?>>Dibatalkan</option>
                        </select>
                        <select name="goldar" class="select-filter" onchange="this.form.submit()">
                            <option value="">Semua Gol.</option>
                            <?php foreach (['A','B','O','AB'] as $g): ?>
                            <option value="<?= $g ?>" <?= $filter_goldar===$g?'selected':'' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-filter"></i> Filter</button>
                        <?php if ($search||$filter_status||$filter_goldar): ?>
                        <a href="permintaan_darah_admin.php" class="btn btn-ghost btn-sm"><i class="fas fa-times"></i> Reset</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="tbl-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th style="width:36px;">#</th>
                            <th>Pasien</th>
                            <th>Gol. Darah</th>
                            <th>Rumah Sakit</th>
                            <th>Kantong</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($q_data->rowCount() > 0):
                        $no = $offset + 1;
                        while ($row = $q_data->fetch()):
                            $gd       = $row['goldar'];
                            $tgl      = date('d M Y', strtotime($row['tanggal']));
                            $row_cls  = $row['status'] === 'menunggu' ? 'row-menunggu' : '';
                            $data_js  = htmlspecialchars(json_encode($row), ENT_QUOTES);
                    ?>
                        <tr class="<?= $row_cls ?>">
                            <td style="color:var(--abu-sedang);font-size:12px;font-weight:600;"><?= $no++ ?></td>
                            <td>
                                <div class="tbl-name">
                                    <div class="tbl-avatar" style="background:#EDF4FF;color:#2563EB;">
                                        <?= strtoupper(substr($row['nama_pasien'] ?? 'P', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="tbl-name-text"><?= htmlspecialchars($row['nama_pasien'] ?? 'Pasien #'.$row['pasien_id']) ?></div>
                                        <div class="tbl-name-sub"><?= htmlspecialchars($row['hp_pasien'] ?? '—') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="gd-badge gd-<?= $gd ?>"><?= $gd ?></span>
                            </td>
                            <td style="max-width:180px;">
                                <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($row['nama_rs']) ?></div>
                                <div style="font-size:11px;color:var(--abu-sedang);">
                                    <i class="fas fa-map-marker-alt" style="color:var(--merah);"></i>
                                    <?= htmlspecialchars($row['kota']) ?>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <div style="font-family:'Fraunces',serif;font-size:20px;font-weight:900;color:var(--merah);"><?= $row['jumlah_kantong'] ?></div>
                                <div style="font-size:10px;color:var(--abu-sedang);">kantong</div>
                            </td>
                            <td style="font-size:12px;color:var(--abu-sedang);white-space:nowrap;"><?= $tgl ?></td>
                            <td>
                                <span class="badge st-<?= $row['status'] ?>">
                                    <?php
                                    $ikon_st = ['menunggu'=>'clock','diproses'=>'spinner','terpenuhi'=>'check-circle','dibatalkan'=>'ban'];
                                    ?>
                                    <i class="fas fa-<?= $ikon_st[$row['status']] ?>" style="font-size:9px;"></i>
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="aksi-wrap">
                                    <!-- Lihat detail & ubah status -->
                                    <button class="btn btn-sm btn-edit btn-icon"
                                        onclick='bukaModalDetail(<?= $data_js ?>)'
                                        title="Lihat Detail & Ubah Status">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <!-- Kirim pengingat ke PMI (hanya jika menunggu/diproses) -->
                                    <?php if (in_array($row['status'], ['menunggu','diproses'])): ?>
                                    <button class="btn btn-sm btn-reminder btn-icon"
                                        onclick='bukaModalReminder(<?= $data_js ?>)'
                                        title="Kirim Pengingat ke PMI">
                                        <i class="fas fa-bell"></i>
                                    </button>
                                    <?php endif; ?>
                                    <!-- Hapus -->
                                    <button class="btn btn-sm btn-hapus btn-icon"
                                        onclick='konfirmasiHapus(<?= $row['id'] ?>, "<?= addslashes(htmlspecialchars($row['nama_pasien'] ?? 'Pasien #'.$row['pasien_id'])) ?>")'
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-hand-holding-medical"></i>
                                <p><?= ($search||$filter_status||$filter_goldar) ? 'Tidak ada permintaan yang cocok dengan filter.' : 'Belum ada permintaan darah masuk.' ?></p>
                            </div>
                        </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pg > 1): ?>
            <div class="pagination-wrap">
                <div class="pagi-info">
                    Menampilkan <strong><?= $offset+1 ?>–<?= min($offset+$per_page,$total) ?></strong>
                    dari <strong><?= $total ?></strong> permintaan
                </div>
                <div class="pagi-btns">
                    <?php $qs = http_build_query(['search'=>$search,'filter_status'=>$filter_status,'goldar'=>$filter_goldar]); ?>
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

<!-- ══ MODAL DETAIL & UBAH STATUS ══ -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal" style="max-width:560px;">
        <div class="modal-head">
            <div class="modal-title"><i class="fas fa-hand-holding-medical"></i> Detail Permintaan Darah</div>
            <button class="modal-close" onclick="tutupModal('modalDetail')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <!-- Info pasien -->
            <div style="background:var(--abu-terang);border-radius:var(--radius-sm);padding:14px 16px;margin-bottom:16px;">
                <div style="font-size:11px;font-weight:700;color:var(--abu-sedang);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Data Pasien</div>
                <div class="detail-row"><span class="detail-label">Nama Pasien</span><span class="detail-val" id="d_nama_pasien">—</span></div>
                <div class="detail-row"><span class="detail-label">No. HP</span><span class="detail-val" id="d_hp">—</span></div>
                <div class="detail-row" style="margin-bottom:0;"><span class="detail-label">Email</span><span class="detail-val" id="d_email">—</span></div>
            </div>

            <!-- Info permintaan -->
            <div style="font-size:11px;font-weight:700;color:var(--abu-sedang);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Detail Permintaan</div>
            <div class="detail-row"><span class="detail-label">Golongan Darah</span><span class="detail-val" id="d_goldar">—</span></div>
            <div class="detail-row"><span class="detail-label">Jumlah Kantong</span><span class="detail-val" id="d_kantong">—</span></div>
            <div class="detail-row"><span class="detail-label">Rumah Sakit</span><span class="detail-val" id="d_rs">—</span></div>
            <div class="detail-row"><span class="detail-label">Kota</span><span class="detail-val" id="d_kota">—</span></div>
            <div class="detail-row"><span class="detail-label">Alamat RS</span><span class="detail-val" id="d_alamat">—</span></div>
            <div class="detail-row"><span class="detail-label">Keterangan</span><span class="detail-val" id="d_ket">—</span></div>
            <div class="detail-row"><span class="detail-label">Tanggal Masuk</span><span class="detail-val" id="d_tgl">—</span></div>
            <div class="detail-row" style="margin-bottom:0;"><span class="detail-label">Status Saat Ini</span><span class="detail-val" id="d_status_badge">—</span></div>

            <div class="divider"></div>

            <!-- Form ubah status -->
            <form method="POST" action="permintaan_admin.php" id="formStatus">
                <input type="hidden" name="aksi" value="ubah_status">
                <input type="hidden" name="id_permintaan" id="d_id">
                <div style="display:flex;align-items:flex-end;gap:10px;">
                    <div style="flex:1;">
                        <label class="form-label" style="font-size:11px;font-weight:700;color:var(--teks-sedang);text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">
                            <i class="fas fa-exchange-alt" style="color:var(--merah);margin-right:4px;"></i> Ubah Status Permintaan
                        </label>
                        <select name="status_baru" id="d_status_select" class="form-select" style="width:100%;">
                            <option value="menunggu">⏳ Menunggu</option>
                            <option value="diproses">🔄 Diproses PMI</option>
                            <option value="terpenuhi">✅ Terpenuhi</option>
                            <option value="dibatalkan">❌ Dibatalkan</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
                        <i class="fas fa-save"></i> Simpan Status
                    </button>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" onclick="tutupModal('modalDetail')">Tutup</button>
            <button class="btn btn-reminder" onclick="bukaReminderDariDetail()">
                <i class="fas fa-bell"></i> Kirim Pengingat ke PMI
            </button>
        </div>
    </div>
</div>

<!-- ══ MODAL PENGINGAT PMI ══ -->
<div class="modal-overlay" id="modalReminder">
    <div class="modal" style="max-width:520px;">
        <div class="modal-head">
            <div class="modal-title" style="color:#D4900A;"><i class="fas fa-bell"></i> Kirim Pengingat ke PMI</div>
            <button class="modal-close" onclick="tutupModal('modalReminder')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div style="background:#FFF8E6;border:1px solid #FDE68A;border-radius:var(--radius-sm);padding:14px 16px;margin-bottom:16px;">
                <div style="font-size:13px;font-weight:700;color:#D4900A;margin-bottom:4px;">
                    <i class="fas fa-info-circle"></i> Informasi Pengingat
                </div>
                <div style="font-size:12px;color:var(--teks-sedang);line-height:1.6;">
                    Pengingat ini berisi detail permintaan darah yang belum/sedang diproses.
                    Salin pesan di bawah dan kirim ke PMI via WhatsApp atau email.
                </div>
            </div>

            <div style="font-size:12px;font-weight:700;color:var(--teks-sedang);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px;">
                Template Pesan Pengingat
            </div>

            <div class="reminder-template" id="reminder_text">—</div>

            <div style="display:flex;gap:10px;margin-top:14px;">
                <button class="btn btn-primary btn-sm" onclick="salinPesan()" style="flex:1;">
                    <i class="fas fa-copy"></i> Salin Pesan
                </button>
                <a href="#" id="wa_link" target="_blank" class="btn btn-sm" style="flex:1;background:#25D366;color:white;border:none;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;">
                    <i class="fab fa-whatsapp"></i> Buka WhatsApp
                </a>
            </div>

            <div style="margin-top:14px;padding:12px 14px;background:var(--abu-terang);border-radius:var(--radius-sm);font-size:12px;color:var(--abu-sedang);">
                <i class="fas fa-lightbulb" style="color:#D4900A;margin-right:4px;"></i>
                Tip: Kirim pesan ini ke nomor PMI setempat atau koordinator donor darah di kota tersebut.
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" onclick="tutupModal('modalReminder')">Tutup</button>
        </div>
    </div>
</div>

<!-- ══ MODAL HAPUS ══ -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal" style="max-width:400px;">
        <div class="modal-body" style="padding:32px 28px;text-align:center;">
            <div class="hapus-icon"><i class="fas fa-trash-alt"></i></div>
            <h3 style="font-size:18px;font-weight:800;margin-bottom:8px;">Hapus Permintaan?</h3>
            <p style="font-size:14px;color:var(--teks-sedang);margin-bottom:4px;">Permintaan dari pasien:</p>
            <p style="font-size:15px;font-weight:700;color:var(--merah);margin-bottom:12px;" id="hapus_nama">—</p>
            <p style="font-size:13px;color:var(--abu-sedang);">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-foot" style="justify-content:center;">
            <button class="btn btn-ghost" onclick="tutupModal('modalHapus')"><i class="fas fa-times"></i> Batal</button>
            <a href="#" id="hapus_link" class="btn btn-danger"><i class="fas fa-trash"></i> Ya, Hapus</a>
        </div>
    </div>
</div>

</body>
</html>