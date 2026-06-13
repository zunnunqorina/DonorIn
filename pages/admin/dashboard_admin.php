<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../../login.php");
    exit;
}

// Ambil nama admin dari session
$admin_username = $_SESSION['admin_username'] ?? 'Admin';

$halaman_aktif_admin = 'dashboard';

// Total pasien
$total_pasien = $conn->query("SELECT COUNT(*) FROM user WHERE role = 'pasien'")->fetchColumn() ?? 0;

// Total pendonor
$total_pendonor = $conn->query("SELECT COUNT(*) FROM user WHERE role = 'pendonor'")->fetchColumn() ?? 0;

// Total relawan
$total_relawan = $conn->query("SELECT COUNT(*) FROM relawan")->fetchColumn() ?? 0;

// Total event donor
$total_event_donor = $conn->query("SELECT COUNT(*) FROM event_donor WHERE status = 'aktif'")->fetchColumn() ?? 0;

// Total event sosialisasi
$total_event_sosial = $conn->query("SELECT COUNT(*) FROM event_sosialisasi WHERE status = 'aktif'")->fetchColumn() ?? 0;

// Total kritik & saran
$total_ks = $conn->query("SELECT COUNT(*) FROM kritik_saran")->fetchColumn() ?? 0;

// Event donor mendatang (5 terdekat)
$q_upcoming_donor = $conn->query("
    SELECT * FROM event_donor
    WHERE tanggal >= CURDATE() AND status = 'aktif'
    ORDER BY tanggal ASC, jam_mulai ASC
    LIMIT 5
");

// Event sosialisasi mendatang (5 terdekat)
$q_upcoming_sosial = $conn->query("
    SELECT * FROM event_sosialisasi
    WHERE tanggal >= CURDATE() AND status = 'aktif'
    ORDER BY tanggal ASC, jam_mulai ASC
    LIMIT 5
");

// Relawan terbaru (5 terakhir)
$q_relawan_baru = $conn->query("
    SELECT * FROM relawan
    ORDER BY tanggal_daftar DESC
    LIMIT 5
");

// Kritik & saran terbaru (5 terakhir)
$q_ks_baru = $conn->query("
    SELECT * FROM kritik_saran
    ORDER BY tanggal DESC
    LIMIT 5
");

// Sebaran golongan darah relawan
$q_goldar = $conn->query("
    SELECT goldar, COUNT(*) as total
    FROM relawan
    GROUP BY goldar
    ORDER BY total DESC
");
$goldar_map = ['A' => 0, 'B' => 0, 'O' => 0, 'AB' => 0];
while ($row = $q_goldar->fetch()) {
    if (isset($goldar_map[$row['goldar']])) {
        $goldar_map[$row['goldar']] = $row['total'];
    }
}

// Total event donor bulan ini
$total_event_bulan = $conn->query("
    SELECT COUNT(*) FROM event_donor
    WHERE MONTH(tanggal) = MONTH(CURDATE())
    AND YEAR(tanggal) = YEAR(CURDATE())
")->fetchColumn() ?? 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — DonorIn</title>
    <link rel="stylesheet" href="../../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- ══════════════ SIDEBAR ══════════════ -->
<?php include '../../components/sidebar_admin.php'; ?>

<!-- ══════════════ MAIN ══════════════ -->
<main class="main">

    <!-- TOPBAR -->
    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <div class="topbar-title">Dashboard</div>
                <div class="topbar-breadcrumb"><a href="dashboard_admin.php">DonorIn</a> / <span>Beranda</span></div>
            </div>
        </div>
        <div class="topbar-right">
            <div class="date-chip">
                <i class="fas fa-calendar-day"></i>
                <?= date('d M Y') ?>
            </div>
            <a href="kritik_saran_admin.php" class="topbar-btn" title="Kritik & Saran">
                <i class="fas fa-bell"></i>
                <?php if ($side_total_ks > 0): ?><span class="notif-dot"></span><?php endif; ?>
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
            <a href="pendonor_admin.php" class="btn-lihat"><i class="fas fa-arrow-right"></i> Lihat Relawan</a>
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
                    <a href="event_donor.php" class="btn-lihat"><i class="fas fa-arrow-right"></i> Semua</a>
                </div>
                <div class="card">
                    <div class="card-body event-list">
                        <?php $rows_upcoming_donor = $q_upcoming_donor->fetchAll(); if (count($rows_upcoming_donor) > 0):
                            foreach ($rows_upcoming_donor as $row):
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
                        <?php endforeach; else: ?>
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
                    <a href="event_sosialisasi.php" class="btn-lihat"><i class="fas fa-arrow-right"></i> Semua</a>
                </div>
                <div class="card">
                    <div class="card-body event-list">
                        <?php $rows_upcoming_sosial = $q_upcoming_sosial->fetchAll(); if (count($rows_upcoming_sosial) > 0):
                            foreach ($rows_upcoming_sosial as $row):
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
                        <?php endforeach; else: ?>
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
                    <div class="section-title"><i class="fas fa-people-carry-box"></i> Relawan PMI Terbaru</div>
                    <a href="relawan_admin.php" class="btn-lihat"><i class="fas fa-arrow-right"></i> Semua</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <?php $rows_relawan_baru = $q_relawan_baru->fetchAll(); if (count($rows_relawan_baru) > 0): ?>
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Gol. Darah</th>
                                    <th>Kota</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($rows_relawan_baru as $row): ?>
                                <tr>
                                    <td>
                                        <div class="tbl-name">
                                            <div class="tbl-avatar"><?= strtoupper(substr($row['nama'], 0, 1)) ?></div>
                                            <div>
                                                <div class="tbl-name-text"><?= htmlspecialchars($row['nama']) ?></div>
                                                <div class="tbl-name-sub"><?= htmlspecialchars($row['email'] ?? '-') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-merah"><?= htmlspecialchars($row['goldar'] ?? '-') ?></span></td>
                                    <td style="font-size:12px;color:var(--teks-sedang);"><?= htmlspecialchars($row['kota'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
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
                    <a href="kritik_saran_admin.php" class="btn-lihat"><i class="fas fa-arrow-right"></i> Semua</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <?php $rows_ks_baru = $q_ks_baru->fetchAll(); if (count($rows_ks_baru) > 0):
                            foreach ($rows_ks_baru as $row):
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
                        <?php endforeach; else: ?>
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

<script src="../../assets/admin.js"></script>
<script>
document.getElementById('btnToggleSidebar').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.add('open');
});
document.getElementById('btnCloseSidebar').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.remove('open');
});
</script>
</body>
</html>