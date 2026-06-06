<?php
include '../config/koneksi.php';

// Jika sudah login, redirect
if (isset($_SESSION['pendonor_login']) && $_SESSION['pendonor_login'] === true) {
    header("Location: ../../pages/donor/dashboard_pendonor.php");
    exit;
}

$pesan_error = "";

if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);

    $query = "SELECT * FROM pendonor WHERE email = '$email' AND password = MD5('$password') AND status_aktif = 'aktif'";
    $hasil = mysqli_query($conn, $query);

    if (mysqli_num_rows($hasil) == 1) {
        $data = mysqli_fetch_assoc($hasil);
        $_SESSION['pendonor_login'] = true;
        $_SESSION['pendonor_id']    = $data['id'];
        $_SESSION['pendonor_nama']  = $data['nama'];
        $_SESSION['pendonor_goldar']= $data['goldar'];
        header("Location: ../../pages/donor/dashboard_pendonor.php");
        exit;
    } else {
        $pesan_error = "❌ Email atau password salah, atau akun tidak aktif!";
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Login Pendonor</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
<div class="halaman-auth">
    <div class="kotak-auth">
        <div class="tab-auth">
            <a href="auth/login_pendonor.php" class="aktif-tab">🩸 Pendonor</a>
            <a href="login_pasien.php">🏥 Pasien</a>
            <a href="auth/login_admin.php">🔐 Admin</a>
        </div>
        <h2 style="color:#8b0000; text-align:center;">Login Pendonor</h2>
        <p style="text-align:center;">Masuk sebagai pendonor darah aktif</p>

        <?php if ($pesan_error): ?>
            <div class="pesan-error"><?php echo $pesan_error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login_pendonor.php">
            <div class="grup-form">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@gmail.com" required>
            </div>
            <div class="grup-form">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" name="login" class="tombol-auth tombol-auth-merah">
                MASUK SEBAGAI PENDONOR
            </button>
        </form>

        <a href="pages/donor/daftar_pendonor.php" class="link-auth link-auth-merah" style="margin-top:12px;">
            Belum punya akun? <strong>Daftar Sekarang</strong>
        </a>
        <a href="index.php" class="link-auth" style="color:#888;">← Kembali ke Beranda</a>
    </div>
</div>
</body>
</html>