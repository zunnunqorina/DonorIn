<?php
include '../config/koneksi.php';

if (isset($_SESSION['pmi_login']) && $_SESSION['pmi_login'] === true) {
    header("Location: ../pages/pmi/dashboard_pmi.php");
    exit;
}

$pesan_error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM petugas_pmi WHERE username = ?");
    $stmt->execute([$username]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['pmi_login']    = true;
        $_SESSION['pmi_id']       = $data['id'];
        $_SESSION['pmi_nama']     = $data['nama'];
        $_SESSION['pmi_username'] = $data['username'];
        header("Location: ../pages/pmi/dashboard_pmi.php");
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
    <title>DonorIn — Login PMI</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
<div class="halaman-auth">
    <div class="kotak-auth">

        <div class="tab-auth">
            <a href="login_pendonor.php">🩸 Pendonor</a>
            <a href="login_pasien.php">🏥 Pasien</a>
            <a href="login_admin.php">🔐 Admin</a>
            <a href="login_pmi.php" class="aktif-tab">🏛️ PMI</a>
        </div>

        <h2 style="color:#8b0000; text-align:center;">Login PMI</h2>
        <p style="text-align:center;">Masuk sebagai petugas Unit Donor Darah PMI</p>

        <?php if ($pesan_error): ?>
            <div class="pesan-error"><?php echo $pesan_error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login_pmi.php">
            <div class="grup-form">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username PMI" required autofocus>
            </div>
            <div class="grup-form">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" name="login" class="tombol-auth tombol-auth-merah">
                MASUK SEBAGAI PETUGAS PMI
            </button>
        </form>

        <a href="../index.php" class="link-auth" style="color:#888;">← Kembali ke Beranda</a>
    </div>
</div>
</body>
</html>
