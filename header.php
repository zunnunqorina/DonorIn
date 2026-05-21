<?php

$admin_login = isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true;
?>
<header class="header-utama">
    <div class="wadah flex-header">
        <div class="logo">
            <strong>DonorIn</strong>
        </div>
        <nav class="navigasi-utama">
            <a href="index.php"
               class="<?php echo ($halaman_aktif == 'home') ? 'aktif' : ''; ?>">Home</a>
            <a href="page2.php"
               class="<?php echo ($halaman_aktif == 'donor') ? 'aktif' : ''; ?>">Butuh Donor</a>
            <a href="page2.php#stok-darah">Stok Darah</a>
            <a href="page2.php#daftar-relawan">Daftar Relawan</a>
            <a href="kritik_saran.php"
               class="<?php echo ($halaman_aktif == 'kritik') ? 'aktif' : ''; ?>">Kritik & Saran</a>
        </nav>

        <?php if ($admin_login): ?>
            <a href="dashboard_admin.php" class="tombol-admin" style="text-decoration:none;">
                👤 <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
            </a>
        <?php else: ?>
            <a href="login_admin.php" class="tombol-admin" style="text-decoration:none;">
                LOGIN ADMIN
            </a>
        <?php endif; ?>
    </div>
</header>
