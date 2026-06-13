<?php
/* ── Reusable sidebar snippet for PMI pages ─────────────────────────
   Include this file AFTER defining:
     $pmi_nama            – PMI user name from session
     $halaman_aktif_pmi   – active page key (e.g. 'dashboard')
     $conn                – database connection
──────────────────────────────────────────────────────────────────────── */

// Sidebar badge counts
$side_stat_stok_kritis = $conn->query("SELECT COUNT(*) FROM stok_darah WHERE jumlah <= 5")->fetchColumn()  ?? 0;
$side_stat_menunggu = $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE status='menunggu'")->fetchColumn() ?? 0;
$side_stat_diproses = $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE status='diproses'")->fetchColumn() ?? 0;
$side_stat_permintaan_aktif = $side_stat_menunggu + $side_stat_diproses;
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-tint"></i></div>
        <div>
            <div class="brand-name">DonorIn</div>
            <div class="brand-sub">Portal PMI</div>
        </div>
        <button class="btn-close-sidebar" id="btnCloseSidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-label">Utama</div>
            <a href="dashboard_pmi.php" class="nav-item <?= ($halaman_aktif_pmi == 'dashboard') ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-label">Stok & Permintaan</div>
            <a href="dashboard_pmi.php#stok" class="nav-item <?= ($halaman_aktif_pmi == 'stok') ? 'active' : '' ?>">
                <i class="fas fa-tint"></i> Stok Darah
                <?php if ($side_stat_stok_kritis > 0): ?>
                <span class="nav-badge" style="background:#e65100;"><?= $side_stat_stok_kritis ?></span>
                <?php endif; ?>
            </a>
            <a href="dashboard_pmi.php#permintaan" class="nav-item <?= ($halaman_aktif_pmi == 'permintaan') ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i> Permintaan Aktif
                <?php if ($side_stat_permintaan_aktif > 0): ?>
                <span class="nav-badge"><?= $side_stat_permintaan_aktif ?></span>
                <?php endif; ?>
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-label">Referensi</div>
            <a href="../../pages/donor/cari_pendonor.php" target="_blank" class="nav-item">
                <i class="fas fa-search"></i> Cari Pendonor
            </a>
            <a href="../../pages/donor/stok_darah.php" target="_blank" class="nav-item">
                <i class="fas fa-eye"></i> Halaman Stok Publik
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($pmi_nama ?? 'P', 0, 1)) ?></div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($pmi_nama ?? 'Petugas PMI') ?></div>
                <div class="user-role">Petugas PMI</div>
            </div>
        </div>
        <a href="../../auth/logout_pmi.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </div>
</aside>
