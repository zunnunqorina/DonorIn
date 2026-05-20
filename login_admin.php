<?php
// ============================================================
//  Login Admin — menggunakan SESSION
//  Materi: session_start(), $_SESSION, session_destroy()
// ============================================================

include 'koneksi.php';

$pesan_error = "";

// Proses form login
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    // Cari admin di database, password dibandingkan dengan MD5
    $query = "SELECT * FROM admin 
              WHERE username = '$username' AND password = MD5('$password')";
    $hasil = mysqli_query($conn, $query);

    if (mysqli_num_rows($hasil) == 1) {
        $data = mysqli_fetch_assoc($hasil);

        // Simpan data ke SESSION
        $_SESSION['admin_login']    = true;
        $_SESSION['admin_username'] = $data['username'];

        header("Location: dashboard_admin.php");
        exit;
    } else {
        $pesan_error = "❌ Username atau password salah!";
    }
}

// Jika sudah login, langsung redirect
if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true) {
    header("Location: dashboard_admin.php");
    exit;
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Login Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .halaman-login {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff9f9;
        }
        .kotak-login {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(139,0,0,0.12);
            width: 100%;
            max-width: 380px;
        }
        .kotak-login h2 {
            color: #8b0000;
            text-align: center;
            margin-bottom: 8px;
        }
        .kotak-login p {
            text-align: center;
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }
        .kotak-login .grup-form input {
            width: 100%;
            box-sizing: border-box;
        }
        .tombol-login {
            width: 100%;
            padding: 12px;
            background: #8b0000;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 5px;
        }
        .tombol-login:hover { background: #6b0000; }
        .link-kembali {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #8b0000;
            font-size: 0.9rem;
            text-decoration: none;
        }
        .link-kembali:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="halaman-login">
        <div class="kotak-login">
            <h2>🔐 Login Admin</h2>
            <p>DonorIn Management System</p>

            <?php if ($pesan_error): ?>
                <div class="pesan-error"><?php echo $pesan_error; ?></div>
            <?php endif; ?>

            <form method="POST" action="login_admin.php">
                <div class="grup-form">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           placeholder="Masukkan username" required>
                </div>
                <div class="grup-form">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Masukkan password" required>
                </div>
                <button type="submit" name="login" class="tombol-login">
                    MASUK
                </button>
            </form>

            <a href="index.php" class="link-kembali">← Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
