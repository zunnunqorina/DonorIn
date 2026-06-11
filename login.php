<?php
// login.php — Satu halaman login untuk semua role
// PDO Prepared Statement + password_verify (aman)
include 'config/koneksi.php';

// Kalau sudah login, langsung redirect sesuai role
if (isset($_SESSION['admin_login'])    && $_SESSION['admin_login']    === true) { header("Location: pages/admin/dashboard_admin.php");    exit; }
if (isset($_SESSION['pendonor_login']) && $_SESSION['pendonor_login'] === true) { header("Location: pages/donor/dashboard_pendonor.php"); exit; }
if (isset($_SESSION['pmi_login'])      && $_SESSION['pmi_login']      === true) { header("Location: pages/pmi/dashboard_pmi.php");         exit; }

$pesan_error = "";

if (isset($_POST['login'])) {
    $input    = trim($_POST['username_email']);
    $password = trim($_POST['password']);

    // ── 1. Cek ADMIN (by username) ─────────────────────────────────────────
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$input]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['admin_login']    = true;
        $_SESSION['admin_id']       = $data['id'];
        $_SESSION['admin_username'] = $data['username'];
        header("Location: pages/admin/dashboard_admin.php");
        exit;
    }

    // ── 2. Cek PENDONOR (by email) ─────────────────────────────────────────
    $stmt = $conn->prepare("SELECT * FROM pendonor WHERE email = ? AND status_aktif = 'aktif'");
    $stmt->execute([$input]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['pendonor_login']  = true;
        $_SESSION['pendonor_id']     = $data['id'];
        $_SESSION['pendonor_nama']   = $data['nama'];
        $_SESSION['pendonor_goldar'] = $data['goldar'];
        header("Location: pages/donor/dashboard_pendonor.php");
        exit;
    }

    // ── 3. Cek PMI (by username atau email) ────────────────────────────────
    $stmt = $conn->prepare("SELECT * FROM petugas_pmi WHERE username = ? OR email = ?");
    $stmt->execute([$input, $input]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['pmi_login']    = true;
        $_SESSION['pmi_id']       = $data['id'];
        $_SESSION['pmi_nama']     = $data['nama'];
        $_SESSION['pmi_username'] = $data['username'];
        header("Location: pages/pmi/dashboard_pmi.php");
        exit;
    }

    // Tidak ada yang cocok
    $pesan_error = "❌ Username/email atau password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Login</title>
    <link rel="stylesheet" href="assets/styles.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            min-height: 100vh;
            display: flex;
            background: #fff9f9;
        }

        /* ── PANEL KIRI ───────────────────────────────────────────────────── */
        .panel-kiri {
            flex: 1;
            background: linear-gradient(145deg, #6b0000 0%, #8b0000 50%, #a0001a 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        /* Lingkaran dekoratif di background */
        .panel-kiri::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
            top: -100px; right: -100px;
        }
        .panel-kiri::after {
            content: '';
            position: absolute;
            width: 250px; height: 250px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
            bottom: -60px; left: -60px;
        }

        .logo-kiri {
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: -1px;
            margin-bottom: 6px;
            position: relative;
        }
        .tagline-kiri {
            font-size: 0.95rem;
            opacity: 0.75;
            margin-bottom: 52px;
            text-align: center;
            position: relative;
        }

        .drop-besar {
            font-size: 7rem;
            margin-bottom: 36px;
            filter: drop-shadow(0 12px 30px rgba(0,0,0,0.3));
            position: relative;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-10px); }
        }

        .fitur-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 16px;
            width: 100%;
            max-width: 320px;
            position: relative;
        }
        .fitur-list li {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 0.88rem;
            opacity: 0.9;
            line-height: 1.4;
        }
        .fitur-ikon {
            width: 40px; height: 40px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
            backdrop-filter: blur(4px);
        }

        /* ── PANEL KANAN ──────────────────────────────────────────────────── */
        .panel-kanan {
            width: 460px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
            background: white;
            box-shadow: -4px 0 30px rgba(139,0,0,0.08);
        }

        .form-login { width: 100%; }

        .form-login h2 {
            font-size: 1.7rem;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 6px;
        }
        .form-login .sub {
            font-size: 0.88rem;
            color: #888;
            margin-bottom: 32px;
        }

        .grup-input { margin-bottom: 20px; }
        .grup-input label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: #555;
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .input-wrap { position: relative; }
        .input-wrap .ikon-input {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            pointer-events: none;
        }
        .grup-input input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1.5px solid #e8e8e8;
            border-radius: 9px;
            font-size: 0.95rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            color: #1a1a1a;
            background: #fafafa;
        }
        .grup-input input:focus {
            border-color: #8b0000;
            box-shadow: 0 0 0 3px rgba(139,0,0,0.08);
            background: white;
        }

        .tombol-masuk {
            width: 100%;
            padding: 14px;
            background: #8b0000;
            color: white;
            border: none;
            border-radius: 9px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
            margin-top: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .tombol-masuk:hover  { background: #6b0000; box-shadow: 0 4px 16px rgba(139,0,0,0.3); }
        .tombol-masuk:active { transform: scale(0.99); }

        .pesan-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c2c7;
            border-radius: 9px;
            padding: 12px 15px;
            font-size: 0.88rem;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            color: #ccc;
            font-size: 0.8rem;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #eee;
        }

        .tombol-daftar {
            display: block;
            text-align: center;
            background: #fff3f3;
            border: 2px solid #8b0000;
            color: #8b0000;
            padding: 12px;
            border-radius: 9px;
            font-weight: 700;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        .tombol-daftar:hover { background: #ffe5e5; }

        .link-kembali {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #aaa;
            font-size: 0.85rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .link-kembali:hover { color: #8b0000; }

        /* Info role hint */
        .role-hint {
            background: #f8f9fa;
            border-radius: 9px;
            padding: 14px 16px;
            font-size: 0.78rem;
            color: #888;
            margin-top: 22px;
            line-height: 1.8;
            border: 1px solid #f0f0f0;
        }
        .role-hint strong { color: #555; }
        .role-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 700;
            margin-right: 4px;
        }
        .badge-admin    { background: #f8d7da; color: #721c24; }
        .badge-pendonor { background: #d4edda; color: #155724; }
        .badge-pmi      { background: #cce5ff; color: #004085; }

        /* Responsive */
        @media (max-width: 768px) {
            .panel-kiri  { display: none; }
            .panel-kanan { width: 100%; padding: 40px 24px; }
        }
    </style>
</head>
<body>

    <!-- PANEL KIRI — branding -->
    <div class="panel-kiri">
        <div class="logo-kiri">🩸 DonorIn</div>
        <div class="tagline-kiri">Sistem Informasi Donor Darah</div>
        <div class="drop-besar">🩸</div>
        <ul class="fitur-list">
            <li>
                <div class="fitur-ikon">🩸</div>
                <span><strong>Pendonor</strong> — Lihat permintaan darah & respon kebutuhan pasien</span>
            </li>
            <li>
                <div class="fitur-ikon">🏛️</div>
                <span><strong>PMI</strong> — Kelola stok darah, permintaan, dan event donor</span>
            </li>
            <li>
                <div class="fitur-ikon">🔐</div>
                <span><strong>Admin</strong> — Manajemen pengguna dan monitoring sistem</span>
            </li>
        </ul>
    </div>

    <!-- PANEL KANAN — form login -->
    <div class="panel-kanan">
        <div class="form-login">

            <h2>Selamat Datang 👋</h2>
            <p class="sub">Masuk ke akun Anda untuk melanjutkan ke DonorIn.</p>

            <?php if ($pesan_error): ?>
                <div class="pesan-error"><?php echo $pesan_error; ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">

                <div class="grup-input">
                    <label>Username atau Email</label>
                    <div class="input-wrap">
                        <span class="ikon-input">👤</span>
                        <input type="text" name="username_email"
                               placeholder="Masukkan username atau email"
                               value="<?php echo htmlspecialchars($_POST['username_email'] ?? ''); ?>"
                               required autofocus>
                    </div>
                </div>

                <div class="grup-input">
                    <label>Password</label>
                    <div class="input-wrap">
                        <span class="ikon-input">🔑</span>
                        <input type="password" name="password"
                               placeholder="Masukkan password"
                               required>
                    </div>
                </div>

                <button type="submit" name="login" class="tombol-masuk">
                    MASUK <span>→</span>
                </button>

            </form>

            <div class="divider">atau</div>

            <a href="pages/donor/daftar_pendonor.php" class="tombol-daftar">
                🩸 Daftar Sebagai Pendonor
            </a>

            <a href="index.php" class="link-kembali">← Kembali ke Beranda</a>

            <!-- Info role -->
            <div class="role-hint">
                💡 <strong>Login otomatis diarahkan sesuai role:</strong><br>
                <span class="role-badge badge-admin">Admin</span> → Dashboard Admin &nbsp;
                <span class="role-badge badge-pendonor">Pendonor</span> → Dashboard Pendonor &nbsp;
                <span class="role-badge badge-pmi">PMI</span> → Dashboard PMI
            </div>

        </div>
    </div>

</body>
</html>
