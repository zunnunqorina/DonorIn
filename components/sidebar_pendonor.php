<?php
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-tint"></i></div>
        <div>
            <div class="brand-name">DonorIn</div>
            <div class="brand-sub">Portal Pendonor</div>
        </div>
        <button class="btn-close-sidebar" id="btnCloseSidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-label">Utama</div>
            <a href="dashboard_pendonor.php" class="nav-item <?= ($halaman_aktif=='dashboard_pendonor') ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Donor Darah</div>
            <a href="cari_permintaan.php" class="nav-item <?= ($halaman_aktif=='cari_permintaan') ? 'active' : '' ?>">
                <i class="fas fa-search"></i> Cari Permintaan
            </a>
            <a href="riwayat_responpendonor.php" class="nav-item <?= ($halaman_aktif=='riwayat_respon') ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i> Riwayat Respon
            </a>
            <a href="cari_pendonor.php" class="nav-item <?= ($halaman_aktif=='cari_pendonor') ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Cari Pendonor
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Permintaan</div>
            <a href="ajukan_permintaan.php" class="nav-item <?= ($halaman_aktif=='ajukan_permintaan') ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i> Ajukan Permintaan
            </a>
            <a href="stok_darah.php" class="nav-item <?= ($halaman_aktif=='stok_darah') ? 'active' : '' ?>">
                <i class="fas fa-tint"></i> Stok Darah
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Akun</div>
            <a href="profile_pendonor.php" class="nav-item <?= ($halaman_aktif=='profile_pendonor') ? 'active' : '' ?>">
                <i class="fas fa-user"></i> Profil Saya
            </a>
            <a href="notifikasi_pendonor.php" class="nav-item <?= ($halaman_aktif=='notifikasi') ? 'active' : '' ?>">
                <i class="fas fa-bell"></i> Notifikasi
                <?php if (!empty($jml_notif_belum) && $jml_notif_belum > 0): ?>
                <span class="nav-badge"><?= $jml_notif_belum ?></span>
                <?php endif; ?>
            </a>
            <a href="edukasi_donor.php" class="nav-item <?= ($halaman_aktif=='edukasi_donor') ? 'active' : '' ?>">
                <i class="fas fa-book"></i> Edukasi Donor
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($admin_username ?? 'P', 0, 1)) ?></div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($admin_username ?? '') ?></div>
                <div class="user-role">Pendonor</div>
            </div>
        </div>
        <a href="../../auth/logout_pendonor.php" class="btn-logout" id="btnLogout">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </div>
</aside>
