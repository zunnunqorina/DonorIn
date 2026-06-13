<?php
/* ── Reusable sidebar snippet for admin pages ─────────────────────────
   Include this file AFTER defining:
     $admin_username   – admin name from session
     $halaman_aktif_admin – active page key (e.g. 'dashboard')
     $conn             – database connection
──────────────────────────────────────────────────────────────────────── */

// Sidebar badge counts
$side_total_pasien = $conn->query("SELECT COUNT(*) FROM user WHERE role = 'pasien'")->fetchColumn() ?? 0;
$side_total_pendonor = $conn->query("SELECT COUNT(*) FROM user WHERE role = 'pendonor'")->fetchColumn() ?? 0;
$side_total_relawan = $conn->query("SELECT COUNT(*) FROM relawan")->fetchColumn() ?? 0;
$side_total_event_donor = $conn->query("SELECT COUNT(*) FROM event_donor WHERE status = 'aktif'")->fetchColumn() ?? 0;
$side_total_event_sosial = $conn->query("SELECT COUNT(*) FROM event_sosialisasi WHERE status = 'aktif'")->fetchColumn() ?? 0;
$side_total_permintaan = $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE status = 'menunggu'")->fetchColumn() ?? 0;
$side_total_ks = $conn->query("SELECT COUNT(*) FROM kritik_saran")->fetchColumn() ?? 0;
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-tint"></i></div>
        <div>
            <div class="brand-name">DonorIn</div>
            <div class="brand-sub">Admin Panel</div>
        </div>
        <button class="btn-close-sidebar" id="btnCloseSidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-label">Utama</div>
            <a href="dashboard_admin.php" class="nav-item <?= ($halaman_aktif_admin == 'dashboard') ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Pengguna</div>
            <a href="pasien_admin.php" class="nav-item <?= ($halaman_aktif_admin == 'pasien') ? 'active' : '' ?>">
                <i class="fas fa-user-injured"></i> Pasien
                <span class="nav-badge"><?= $side_total_pasien ?></span>
            </a>
            <a href="pendonor_admin.php" class="nav-item <?= ($halaman_aktif_admin == 'pendonor') ? 'active' : '' ?>">
                <i class="fas fa-hand-holding-heart"></i> Pendonor
                <span class="nav-badge"><?= $side_total_pendonor ?></span>
            </a>
            <a href="relawan_admin.php" class="nav-item <?= ($halaman_aktif_admin == 'relawan') ? 'active' : '' ?>">
                <i class="fas fa-people-carry-box"></i> Relawan PMI
                <span class="nav-badge"><?= $side_total_relawan ?></span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Event</div>
            <a href="event_donor.php" class="nav-item <?= ($halaman_aktif_admin == 'event_donor') ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i> Event Donor Darah
                <span class="nav-badge"><?= $side_total_event_donor ?></span>
            </a>
            <a href="event_sosialisasi.php" class="nav-item <?= ($halaman_aktif_admin == 'event_sosialisasi') ? 'active' : '' ?>">
                <i class="fas fa-bullhorn"></i> Event Sosialisasi
                <span class="nav-badge"><?= $side_total_event_sosial ?></span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Permintaan</div>
            <a href="permintaan_darah_admin.php" class="nav-item <?= ($halaman_aktif_admin == 'permintaan_darah') ? 'active' : '' ?>">
                <i class="fas fa-hand-holding-medical"></i> Permintaan Darah
                <span class="nav-badge"><?= $side_total_permintaan ?></span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Lainnya</div>
            <a href="kritik_saran_admin.php" class="nav-item <?= ($halaman_aktif_admin == 'kritik_saran') ? 'active' : '' ?>">
                <i class="fas fa-comments"></i> Kritik & Saran
                <span class="nav-badge"><?= $side_total_ks ?></span>
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($admin_username ?? 'A', 0, 1)) ?></div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($admin_username ?? 'Admin') ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        <a href="../../auth/logout_admin.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </div>
</aside>
