<?php
include '../../config/koneksi.php';


$filter_goldar = isset($_GET['goldar']) ? trim($_GET['goldar']) : '';
$filter_kota   = isset($_GET['kota'])   ? trim($_GET['kota'])   : '';
$filter_gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';

$where  = "WHERE status_aktif = 'aktif'";
$params = [];
if ($filter_goldar) { $where .= " AND goldar = ?";              $params[] = $filter_goldar; }
if ($filter_kota)   { $where .= " AND kota LIKE ?";             $params[] = "%$filter_kota%"; }
if ($filter_gender) { $where .= " AND jenis_kelamin = ?";       $params[] = $filter_gender; }

// Statistik per golongan darah
$q_stat      = $conn->query("SELECT goldar, COUNT(*) as total FROM pendonor WHERE status_aktif='aktif' GROUP BY goldar");
$stat_goldar = ['A'=>0,'B'=>0,'O'=>0,'AB'=>0];
foreach ($q_stat->fetchAll(PDO::FETCH_ASSOC) as $s) {
    if (isset($stat_goldar[$s['goldar']])) $stat_goldar[$s['goldar']] = $s['total'];
}

$q_pendonor = $conn->prepare(
    "SELECT id, nama, goldar, kota, jenis_kelamin, umur, pekerjaan, pernah_donor, terakhir_donor, no_hp, status_aktif
     FROM pendonor
     $where
     ORDER BY terakhir_donor ASC, nama ASC");
$q_pendonor->execute($params);
$pendonor_rows = $q_pendonor->fetchAll(PDO::FETCH_ASSOC);

$jumlah        = count($pendonor_rows);
$is_logged_in  = isset($_SESSION['pendonor_login']) && $_SESSION['pendonor_login'] === true;
$halaman_aktif = 'cari_pendonor';

if ($is_logged_in) {
    $pendonor_id = $_SESSION['pendonor_id'];
    $q_pendonor_info = $conn->prepare("SELECT * FROM pendonor WHERE id = ?");
    $q_pendonor_info->execute([$pendonor_id]);
    $pendonor = $q_pendonor_info->fetch(PDO::FETCH_ASSOC);
    $admin_username = $pendonor['nama'];
    $pendonor_goldar = $pendonor['goldar'];

    $st3 = $conn->prepare("SELECT COUNT(*) FROM notifikasi WHERE tujuan_tipe='pendonor' AND tujuan_id=? AND sudah_baca=0");
    $st3->execute([$pendonor_id]);
    $jml_notif_belum = $st3->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Cari Pendonor Aktif</title>
    <?php if ($is_logged_in): ?>
        <link rel="stylesheet" href="../../assets/admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php else: ?>
        <link rel="stylesheet" href="../../assets/styles.css">
    <?php endif; ?>
    <style>
        .grid-pendonor {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .kartu-pendonor {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(139,0,0,0.08);
            border: 1px solid #f0e0e0;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        .kartu-pendonor:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(139,0,0,0.14);
        }

        /* Badge goldar besar di pojok kanan atas */
        .goldar-besar {
            position: absolute;
            top: 16px; right: 16px;
            width: 52px; height: 52px;
            background: #8b0000;
            color: white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; font-weight: 900;
        }

        .avatar-pendonor {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #8b0000, #c0001a);
            color: white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 800;
            margin-bottom: 12px;
        }

        .nama-pendonor {
            font-size: 1rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 4px;
            padding-right: 60px; /* biar tidak nabrak badge goldar */
        }

        .sub-pendonor {
            font-size: 0.82rem;
            color: #888;
            margin-bottom: 14px;
        }

        .info-baris {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
            font-size: 0.85rem;
            color: #555;
        }
        .info-baris span { display: flex; align-items: center; gap: 6px; }

        .chip-donor {
            display: inline-block;
            background: #d4edda; color: #155724;
            padding: 3px 10px; border-radius: 20px;
            font-size: 0.75rem; font-weight: 700;
        }
        .chip-belum {
            background: #fff3cd; color: #856404;
        }

        .aksi-kartu {
            display: flex; gap: 8px; flex-wrap: wrap;
        }

        .tombol-hubungi {
            flex: 1;
            background: #27ae60; color: white;
            padding: 9px 14px; border-radius: 8px;
            text-decoration: none; font-size: 0.83rem; font-weight: 700;
            text-align: center; transition: background 0.2s;
        }
        .tombol-hubungi:hover { background: #1e8449; }

        .tombol-wa {
            flex: 1;
            background: #25D366; color: white;
            padding: 9px 14px; border-radius: 8px;
            text-decoration: none; font-size: 0.83rem; font-weight: 700;
            text-align: center; transition: background 0.2s;
        }
        .tombol-wa:hover { background: #1ebe5a; }

        /* ── STAT GOLDAR ── */
        .stat-goldar {
            display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px;
        }
        .stat-goldar-item {
            background: white; border: 2px solid #f0e0e0;
            border-radius: 10px; padding: 12px 20px;
            text-align: center; min-width: 80px;
            cursor: pointer; transition: all 0.2s;
            text-decoration: none;
        }
        .stat-goldar-item:hover,
        .stat-goldar-item.aktif { border-color: #8b0000; background: #fff3f3; }
        .stat-goldar-item .angka { font-size: 1.4rem; font-weight: 900; color: #8b0000; }
        .stat-goldar-item .label { font-size: 0.75rem; color: #888; margin-top: 2px; }

        /* ── TOOLBAR FILTER ── */
        .toolbar-filter {
            background: white;
            border-radius: 10px;
            padding: 16px 20px;
            box-shadow: 0 1px 6px rgba(139,0,0,0.07);
            border: 1px solid #f0e0e0;
            display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;
            margin-bottom: 20px;
        }
        .toolbar-filter .grup { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px; }
        .toolbar-filter label { font-size: 0.78rem; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .5px; }
        .toolbar-filter select,
        .toolbar-filter input { padding: 9px 12px; border: 1px solid #ddd; border-radius: 7px; font-size: 0.9rem; font-family: inherit; outline: none; }
        .toolbar-filter select:focus,
        .toolbar-filter input:focus { border-color: #8b0000; }

        .tombol-filter {
            background: #8b0000; color: white;
            padding: 9px 20px; border-radius: 7px;
            border: none; font-size: 0.9rem; font-weight: 700;
            cursor: pointer; font-family: inherit;
        }
        .tombol-filter:hover { background: #6b0000; }
        .tombol-reset {
            background: #eee; color: #333;
            padding: 9px 16px; border-radius: 7px;
            text-decoration: none; font-size: 0.9rem; font-weight: 700;
        }

        /* ── HP BLUR ── */
        .hp-blur {
            filter: blur(4px);
            user-select: none;
            transition: filter 0.3s;
            cursor: pointer;
        }
        .hp-blur:hover { filter: blur(0); }

        /* ── KOSONG ── */
        .state-kosong {
            text-align: center; padding: 60px 20px;
            background: white; border-radius: 12px;
            border: 1px dashed #ddd; color: #aaa;
        }
        .state-kosong .ikon { font-size: 3rem; margin-bottom: 12px; }
        .state-kosong p { font-size: 1rem; font-weight: 600; }

        @media (max-width: 600px) {
            .grid-pendonor { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body <?php if (!$is_logged_in) echo 'style="background:#f4f6f9;"'; ?>>

<?php if ($is_logged_in): ?>
    <?php include '../../components/sidebar_pendonor.php'; ?>
    <main class="main">
        <!-- TOPBAR -->
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="btn-toggle-sidebar" id="btnToggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <div class="topbar-title">Cari Pendonor</div>
                    <div class="topbar-breadcrumb">DonorIn / <span>Cari Pendonor Aktif</span></div>
                </div>
            </div>
            <div class="topbar-right">
                <div class="date-chip">
                    <i class="fas fa-calendar-day"></i>
                    <?= date('d M Y') ?>
                </div>
            </div>
        </header>
        <div class="content">
<?php else: ?>
    <?php include '../../components/header.php'; ?>
    <main class="wadah" style="padding: 40px 20px;">
<?php endif; ?>

    <h2 style="color:#8b0000; margin-bottom:5px;">🔍 Cari Pendonor Aktif</h2>
    <p style="color:#888; margin-bottom:24px;">
        Temukan pendonor darah aktif yang siap membantu. Hubungi langsung melalui telepon atau WhatsApp.
    </p>

    <!-- STAT PER GOLDAR -->
    <div class="stat-goldar">
        <a href="cari_pendonor.php" class="stat-goldar-item <?php echo !$filter_goldar ? 'aktif' : ''; ?>">
            <div class="angka"><?php echo array_sum($stat_goldar); ?></div>
            <div class="label">Semua</div>
        </a>
        <?php foreach (['A','B','O','AB'] as $g): ?>
        <a href="cari_pendonor.php?goldar=<?php echo $g; ?><?php echo $filter_kota ? '&kota='.urlencode($filter_kota) : ''; ?>"
           class="stat-goldar-item <?php echo $filter_goldar === $g ? 'aktif' : ''; ?>">
            <div class="angka"><?php echo $stat_goldar[$g]; ?></div>
            <div class="label">Gol. <?php echo $g; ?></div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- FILTER -->
    <form method="GET" action="cari_pendonor.php">
        <div class="toolbar-filter">
            <div class="grup">
                <label>Golongan Darah</label>
                <select name="goldar">
                    <option value="">Semua</option>
                    <?php foreach (['A','B','O','AB'] as $g): ?>
                    <option value="<?php echo $g; ?>" <?php echo $filter_goldar===$g ? 'selected' : ''; ?>>
                        <?php echo $g; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grup">
                <label>Kota / Kabupaten</label>
                <input type="text" name="kota" placeholder="Contoh: Mataram"
                       value="<?php echo htmlspecialchars($filter_kota); ?>">
            </div>
            <div class="grup">
                <label>Jenis Kelamin</label>
                <select name="gender">
                    <option value="">Semua</option>
                    <option value="L" <?php echo $filter_gender==='L' ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="P" <?php echo $filter_gender==='P' ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>
            <div style="display:flex; gap:8px; align-items:flex-end;">
                <button type="submit" class="tombol-filter">🔍 Cari</button>
                <a href="cari_pendonor.php" class="tombol-reset">Reset</a>
            </div>
        </div>
    </form>

    <!-- INFO HASIL -->
    <?php if ($filter_goldar || $filter_kota || $filter_gender): ?>
    <div style="background:#fff3f3; border-left:4px solid #8b0000; padding:10px 16px;
                border-radius:0 8px 8px 0; margin-bottom:16px; font-size:0.88rem; color:#555;">
        Menampilkan <strong style="color:#8b0000;"><?php echo $jumlah; ?> pendonor aktif</strong>
        <?php if ($filter_goldar) echo " &bull; Golongan <strong>$filter_goldar</strong>"; ?>
        <?php if ($filter_kota)   echo " &bull; Kota: <strong>" . htmlspecialchars($filter_kota) . "</strong>"; ?>
        <?php if ($filter_gender) echo " &bull; " . ($filter_gender==='L' ? 'Laki-laki' : 'Perempuan'); ?>
    </div>
    <?php else: ?>
    <p style="color:#666; margin-bottom:12px;">
        Menampilkan <strong><?php echo $jumlah; ?></strong> pendonor aktif
    </p>
    <?php endif; ?>

    <!-- CATATAN PRIVASI -->
    <div style="background:#e8f4fd; border-left:4px solid #3498db; padding:10px 16px;
                border-radius:0 8px 8px 0; margin-bottom:20px; font-size:0.85rem; color:#2c3e50;">
        🔒 Nomor HP ditampilkan samar untuk melindungi privasi. Arahkan kursor ke nomor untuk melihat lengkap,
        atau klik tombol Hubungi / WhatsApp langsung.
    </div>

    <!-- GRID KARTU PENDONOR -->
    <?php if ($jumlah == 0): ?>
        <div class="state-kosong">
            <div class="ikon">🩸</div>
            <p>Tidak ada pendonor aktif<?php echo $filter_goldar ? " untuk golongan $filter_goldar" : ''; ?>
            <?php echo $filter_kota ? " di " . htmlspecialchars($filter_kota) : ''; ?>.</p>
            <p style="font-size:0.85rem; margin-top:8px; font-weight:400;">
                Coba ubah filter atau <a href="cari_pendonor.php" style="color:#8b0000;">lihat semua pendonor</a>.
            </p>
        </div>
    <?php else: ?>
    <div class="grid-pendonor">
        <?php foreach ($pendonor_rows as $pd):
            $inisial = strtoupper(substr($pd['nama'], 0, 1));
            $gender_label = $pd['jenis_kelamin'] === 'L' ? '👨 Laki-laki' : '👩 Perempuan';

            // Sembunyikan sebagian nomor HP: 08xx-xxxx-5678
            $hp = $pd['no_hp'];
            $hp_blur = substr($hp, 0, 4) . str_repeat('*', max(0, strlen($hp)-8)) . substr($hp, -4);

            // Format tanggal donor terakhir
            if ($pd['terakhir_donor']) {
                $tgl_donor = date('d M Y', strtotime($pd['terakhir_donor']));
                // Hitung bulan sejak donor terakhir
                $bln_lalu = (int) ((time() - strtotime($pd['terakhir_donor'])) / (30 * 24 * 3600));
                $siap_lagi = $bln_lalu >= 3;
            } else {
                $tgl_donor = null;
                $siap_lagi = true; // belum pernah donor = siap
            }

            // Format WA link
            $no_wa = preg_replace('/^0/', '62', preg_replace('/\D/', '', $hp));
            $pesan_wa = urlencode("Halo, saya mendapat kontak Anda dari DonorIn. Apakah Anda bersedia menjadi pendonor darah?");
        ?>
        <div class="kartu-pendonor">
            <!-- Badge goldar -->
            <div class="goldar-besar"><?php echo htmlspecialchars($pd['goldar']); ?></div>

            <!-- Avatar & nama -->
            <div class="avatar-pendonor"><?php echo $inisial; ?></div>
            <div class="nama-pendonor"><?php echo htmlspecialchars($pd['nama']); ?></div>
            <div class="sub-pendonor">
                <?php echo $gender_label; ?> &bull; <?php echo $pd['umur']; ?> tahun
            </div>

            <!-- Info -->
            <div class="info-baris">
                <span>📍 <?php echo htmlspecialchars($pd['kota']); ?></span>
                <?php if ($pd['pekerjaan']): ?>
                <span>💼 <?php echo htmlspecialchars($pd['pekerjaan']); ?></span>
                <?php endif; ?>
                <span>📞
                    <span class="hp-blur" title="Arahkan untuk lihat nomor"><?php echo $hp_blur; ?></span>
                </span>
                <span>
                    <?php if ($pd['pernah_donor'] === 'ya' && $tgl_donor): ?>
                        <span class="chip-donor <?php echo $siap_lagi ? '' : 'chip-belum'; ?>">
                            <?php echo $siap_lagi ? '✅ Siap Donor' : '⏳ Perlu Jeda'; ?>
                        </span>
                        &nbsp;Donor terakhir: <?php echo $tgl_donor; ?>
                    <?php else: ?>
                        <span class="chip-donor">✅ Siap Donor</span>
                        &nbsp;Belum pernah donor
                    <?php endif; ?>
                </span>
            </div>

            <!-- Tombol aksi -->
            <div class="aksi-kartu">
                <a href="tel:<?php echo htmlspecialchars($hp); ?>" class="tombol-hubungi">
                    📞 Hubungi
                </a>
                <a href="https://wa.me/<?php echo $no_wa; ?>?text=<?php echo $pesan_wa; ?>"
                   target="_blank" class="tombol-wa">
                    💬 WhatsApp
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

<?php if ($is_logged_in): ?>
        </div>
    </main>
    <script src="../../assets/admin.js"></script>
    <script>
    document.getElementById('btnToggleSidebar').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.add('open');
    });
    document.getElementById('btnCloseSidebar').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.remove('open');
    });
    </script>
<?php else: ?>
    </main>
    <?php include '../../components/footer.php'; ?>
<?php endif; ?>
<?php $conn = null; ?>
</body>
</html>