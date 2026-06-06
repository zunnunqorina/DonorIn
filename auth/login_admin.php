<?php
include '../config/koneksi.php';
if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true) {
    header("Location: ../pages/admin/dashboard_admin.php");
    exit;
}
$pesan_error = "";
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $query = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    $hasil = mysqli_query($conn, $query);
    if (mysqli_num_rows($hasil) == 1) {
        $data = mysqli_fetch_assoc($hasil);
        $_SESSION['admin_login']    = true;
        $_SESSION['admin_username'] = $data['username'];
        header("Location: ../pages/admin/dashboard_admin.php");
        exit;
    } else {
        $pesan_error = "❌ Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Login Admin</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
<div class="halaman-auth">
    <div class="kotak-auth">

        <!-- TAB sama persis kayak login pendonor -->
        <div class="tab-auth">
            <a href="login_pendonor.php">🩸 Pendonor</a>
            <a href="login_pasien.php">🏥 Pasien</a>
            <a href="login_admin.php" class="aktif-tab">🔐 Admin</a>
        </div>

        <h2 style="color:#8b0000; text-align:center;">Login Admin</h2>
        <p style="text-align:center;">Masuk sebagai administrator DonorIn</p>

        <?php if ($pesan_error): ?>
            <div class="pesan-error"><?php echo $pesan_error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login_admin.php">
            <div class="grup-form">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required>
            </div>
            <div class="grup-form">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" name="login" class="tombol-auth tombol-auth-merah">
                MASUK SEBAGAI ADMIN
            </button>
        </form>

        <a href="../index.php" class="link-auth" style="color:#888;">← Kembali ke Beranda</a>
    </div>
</div>
</body>
</html>