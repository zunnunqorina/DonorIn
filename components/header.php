<?php
$admin_login    = isset($_SESSION['admin_login'])    && $_SESSION['admin_login']    === true;
$pendonor_login = isset($_SESSION['pendonor_login']) && $_SESSION['pendonor_login'] === true;
$pasien_login   = isset($_SESSION['pasien_login'])   && $_SESSION['pasien_login']   === true;

// Deteksi prefix path agar link navigasi tidak rusak di subfolder mana pun
$prefix = '';
if (file_exists('index.php')) {
    $prefix = './';
} elseif (file_exists('../index.php')) {
    $prefix = '../';
} elseif (file_exists('../../index.php')) {
    $prefix = '../../';
}
?>
<header class="header-utama">
    <div class="wadah flex-header">
        <div class="logo">
            <strong>DonorIn</strong>
        </div>
        <nav class="navigasi-utama">
            <a href="<?php echo $prefix; ?>index.php"
               class="<?php echo ($halaman_aktif == 'home') ? 'aktif' : ''; ?>">Home</a>
            <a href="<?php echo $prefix; ?>pages/donor/page2.php"
               class="<?php echo ($halaman_aktif == 'donor') ? 'aktif' : ''; ?>">Butuh Donor</a>
            <a href="<?php echo $prefix; ?>pages/donor/stok_darah.php">Stok Darah</a>
            <?php if ($pendonor_login): ?>
                <a href="<?php echo $prefix; ?>pages/donor/dashboard_pendonor.php"
                   class="<?php echo ($halaman_aktif == 'dashboard_pendonor') ? 'aktif' : ''; ?>">Dashboard</a>
                <a href="<?php echo $prefix; ?>pages/donor/cari_permintaan.php"
                   class="<?php echo ($halaman_aktif == 'cari_permintaan') ? 'aktif' : ''; ?>">Permintaan Darah</a>
            <?php elseif ($pasien_login): ?>
                <a href="<?php echo $prefix; ?>dashboard_pasien.php"
                   class="<?php echo ($halaman_aktif == 'dashboard_pasien') ? 'aktif' : ''; ?>">Dashboard</a>
                <a href="<?php echo $prefix; ?>pages/donor/ajukan_permintaan.php"
                   class="<?php echo ($halaman_aktif == 'ajukan_permintaan') ? 'aktif' : ''; ?>">Ajukan Permintaan</a>
                <a href="<?php echo $prefix; ?>pages/donor/cari_pendonor.php"
                   class="<?php echo ($halaman_aktif == 'cari_pendonor') ? 'aktif' : ''; ?>">Cari Pendonor</a>
            <?php else: ?>
                <a href="<?php echo $prefix; ?>pages/donor/page2.php#daftar-relawan">Daftar Relawan</a>
            <?php endif; ?>
            <a href="<?php echo $prefix; ?>pages/donor/kritik_saran.php"
               class="<?php echo ($halaman_aktif == 'kritik') ? 'aktif' : ''; ?>">Kritik & Saran</a>
        </nav>

        <div style="display:flex; gap:8px; align-items:center;">
        <?php if ($admin_login): ?>
            <a href="<?php echo $prefix; ?>pages/admin/dashboard_admin.php" class="tombol-admin" style="text-decoration:none;">
                👤 <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
            </a>
        <?php elseif ($pendonor_login): ?>
            <a href="<?php echo $prefix; ?>pages/donor/dashboard_pendonor.php" class="tombol-admin tombol-pendonor" style="text-decoration:none;">
                🩸 <?php echo htmlspecialchars($_SESSION['pendonor_nama']); ?>
            </a>
            <a href="<?php echo $prefix; ?>auth/logout_pendonor.php" class="tombol-admin" style="text-decoration:none; background:#8b0000; color:white; font-size:0.8rem; padding:6px 12px;">
                Logout
            </a>
        <?php elseif ($pasien_login): ?>
            <a href="<?php echo $prefix; ?>dashboard_pasien.php" class="tombol-admin tombol-pasien" style="text-decoration:none;">
                🏥 <?php echo htmlspecialchars($_SESSION['pasien_nama']); ?>
            </a>
            <a href="<?php echo $prefix; ?>logout_pasien.php" class="tombol-admin" style="text-decoration:none; background:#8b0000; color:white; font-size:0.8rem; padding:6px 12px;">
                Logout
            </a>
        <?php else: ?>
            <a href="<?php echo $prefix; ?>auth/login_pendonor.php" class="tombol-admin tombol-pendonor" style="text-decoration:none; font-size:0.82rem;">
                🩸 Pendonor
            </a>
            <a href="<?php echo $prefix; ?>auth/login_pasien.php" class="tombol-admin tombol-pasien" style="text-decoration:none; font-size:0.82rem;">
                🏥 Pasien
            </a>
            <a href="<?php echo $prefix; ?>auth/login_admin.php" class="tombol-admin" style="text-decoration:none; font-size:0.82rem;">
                🔐 Admin
            </a>
        <?php endif; ?>
        </div>
    </div>
</header>