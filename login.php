<?php
// login.php — Satu halaman login untuk semua role
include 'config/koneksi.php';

// Kalau sudah login, langsung redirect sesuai role
if (isset($_SESSION['admin_login'])    && $_SESSION['admin_login']    === true) { header("Location: pages/admin/dashboard_admin.php");    exit; }
if (isset($_SESSION['pendonor_login']) && $_SESSION['pendonor_login'] === true) { header("Location: pages/donor/dashboard_pendonor.php"); exit; }
if (isset($_SESSION['pmi_login'])      && $_SESSION['pmi_login']      === true) { header("Location: pages/pmi/dashboard_pmi.php");         exit; }

$pesan_error   = "";
$pesan_sukses  = "";
$buka_modal    = false; // untuk re-open modal kalau ada error registrasi

// ── PROSES DAFTAR RELAWAN PMI ──────────────────────────────────────────────
if (isset($_POST['daftar_pmi'])) {
    $buka_modal = true;
    $nama       = trim($_POST['nama'] ?? '');
    $username   = trim($_POST['username_pmi'] ?? '');
    $email      = trim($_POST['email_pmi'] ?? '');
    $password   = trim($_POST['password_pmi'] ?? '');
    $konfirmasi = trim($_POST['konfirmasi_pmi'] ?? '');

    // Validasi isi
    if (empty($nama) || empty($username) || empty($email) || empty($password) || empty($konfirmasi)) {
        $pesan_error = "Semua field wajib diisi!";

    // Validasi email
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error = "Format email tidak valid!";

    // Validasi password
    } elseif (strlen($password) < 6) {
        $pesan_error = "Password minimal 6 karakter!";

    // Validasi konfirmasi
    } elseif ($password !== $konfirmasi) {
        $pesan_error = "Konfirmasi password tidak cocok!";

    } else {
        // Cek username & email sudah ada belum
        $cek = $conn->prepare("SELECT id FROM petugas_pmi WHERE username = ? OR email = ?");
        $cek->execute([$username, $email]);

        if ($cek->fetch()) {
            $pesan_error = "Username atau email sudah terdaftar!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $conn->prepare("
                INSERT INTO petugas_pmi (nama, username, email, password, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            if ($ins->execute([$nama, $username, $email, $hash])) {
                $pesan_sukses = "Pendaftaran relawan PMI berhasil! Silakan login.";
                $buka_modal   = false; // tutup modal, tampilkan pesan sukses di halaman utama
            } else {
                $pesan_error = "Gagal mendaftar, coba lagi.";
            }
        }
    }
}

// ── PROSES LOGIN ───────────────────────────────────────────────────────────
if (isset($_POST['login'])) {
    $input    = trim($_POST['username_email']);
    $password = trim($_POST['password']);

    // 1. Cek ADMIN
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

    // 2. Cek PENDONOR
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

    // 3. Cek PMI
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

    $pesan_error = "Username/email atau password salah!";
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

        /* ── PANEL KIRI ───────────────────────────────── */
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
        .logo-kiri { font-size: 2.5rem; font-weight: 900; letter-spacing: -1px; margin-bottom: 6px; position: relative; }
        .tagline-kiri { font-size: 0.95rem; opacity: 0.75; margin-bottom: 52px; text-align: center; position: relative; }
        .drop-besar { font-size: 7rem; margin-bottom: 36px; filter: drop-shadow(0 12px 30px rgba(0,0,0,0.3)); position: relative; animation: float 3s ease-in-out infinite; }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .fitur-list { list-style: none; display: flex; flex-direction: column; gap: 16px; width: 100%; max-width: 320px; position: relative; }
        .fitur-list li { display: flex; align-items: center; gap: 14px; font-size: 0.88rem; opacity: 0.9; line-height: 1.4; }
        .fitur-ikon { width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; backdrop-filter: blur(4px); }

        /* ── PANEL KANAN ──────────────────────────────── */
        .panel-kanan { width: 460px; display: flex; align-items: center; justify-content: center; padding: 48px; background: white; box-shadow: -4px 0 30px rgba(139,0,0,0.08); }
        .form-login { width: 100%; }
        .form-login h2 { font-size: 1.7rem; font-weight: 800; color: #1a1a1a; margin-bottom: 6px; }
        .form-login .sub { font-size: 0.88rem; color: #888; margin-bottom: 32px; }

        .grup-input { margin-bottom: 20px; }
        .grup-input label { display: block; font-size: 0.8rem; font-weight: 700; color: #555; margin-bottom: 7px; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-wrap { position: relative; }
        .input-wrap .ikon-input { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); font-size: 1rem; pointer-events: none; }
        .grup-input input { width: 100%; padding: 12px 14px 12px 40px; border: 1.5px solid #e8e8e8; border-radius: 9px; font-size: 0.95rem; font-family: inherit; outline: none; transition: border-color 0.2s, box-shadow 0.2s; color: #1a1a1a; background: #fafafa; }
        .grup-input input:focus { border-color: #8b0000; box-shadow: 0 0 0 3px rgba(139,0,0,0.08); background: white; }

        .tombol-masuk { width: 100%; padding: 14px; background: #8b0000; color: white; border: none; border-radius: 9px; font-size: 1rem; font-weight: 700; cursor: pointer; font-family: inherit; transition: background 0.2s, transform 0.1s, box-shadow 0.2s; margin-top: 4px; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .tombol-masuk:hover { background: #6b0000; box-shadow: 0 4px 16px rgba(139,0,0,0.3); }
        .tombol-masuk:active { transform: scale(0.99); }

        .pesan-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c2c7; border-radius: 9px; padding: 12px 15px; font-size: 0.88rem; margin-bottom: 22px; display: flex; align-items: center; gap: 8px; }
        .pesan-sukses { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 9px; padding: 12px 15px; font-size: 0.88rem; margin-bottom: 22px; display: flex; align-items: center; gap: 8px; }

        .divider { display: flex; align-items: center; gap: 12px; margin: 24px 0; color: #ccc; font-size: 0.8rem; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #eee; }

        .tombol-daftar { display: block; text-align: center; background: #fff3f3; border: 2px solid #8b0000; color: #8b0000; padding: 12px; border-radius: 9px; font-weight: 700; text-decoration: none; font-size: 0.9rem; transition: background 0.2s; cursor: pointer; width: 100%; font-family: inherit; }
        .tombol-daftar:hover { background: #ffe5e5; }

        /* Tombol daftar PMI — biru */
        .tombol-daftar-pmi { display: block; text-align: center; background: #f0f6ff; border: 2px solid #1a56a0; color: #1a56a0; padding: 12px; border-radius: 9px; font-weight: 700; text-decoration: none; font-size: 0.9rem; transition: background 0.2s; cursor: pointer; width: 100%; font-family: inherit; margin-top: 10px; }
        .tombol-daftar-pmi:hover { background: #dceeff; }

        .link-kembali { display: block; text-align: center; margin-top: 18px; color: #aaa; font-size: 0.85rem; text-decoration: none; transition: color 0.2s; }
        .link-kembali:hover { color: #8b0000; }

        /* ── MODAL ────────────────────────────────────── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.aktif { display: flex; }

        .modal-box {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.25s ease;
        }
        @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .modal-header {
            padding: 24px 28px 16px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-header h3 { font-size: 1.2rem; font-weight: 800; color: #1a1a1a; }
        .modal-header p  { font-size: 0.82rem; color: #888; margin-top: 3px; }
        .modal-tutup { background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #aaa; line-height: 1; padding: 4px; border-radius: 6px; }
        .modal-tutup:hover { background: #f5f5f5; color: #555; }

        .modal-body { padding: 20px 28px 28px; }

        .modal-body .grup-input { margin-bottom: 16px; }
        .modal-body .grup-input label { font-size: 0.78rem; }
        .modal-body .grup-input input { padding: 11px 14px 11px 38px; font-size: 0.9rem; }

        .modal-error  { background: #f8d7da; color: #721c24; border: 1px solid #f5c2c7; border-radius: 8px; padding: 10px 14px; font-size: 0.85rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }

        .tombol-submit-modal { width: 100%; padding: 13px; background: #1a56a0; color: white; border: none; border-radius: 9px; font-size: 0.95rem; font-weight: 700; cursor: pointer; font-family: inherit; transition: background 0.2s; margin-top: 4px; }
        .tombol-submit-modal:hover { background: #154380; }

        .pmi-badge { display: inline-flex; align-items: center; gap: 6px; background: #cce5ff; color: #004085; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; margin-bottom: 16px; }

        @media (max-width: 768px) {
            .panel-kiri  { display: none; }
            .panel-kanan { width: 100%; padding: 40px 24px; }
        }
    </style>
</head>
<body>

    <!-- PANEL KIRI -->
    <div class="panel-kiri">
        <div class="logo-kiri">🩸 DonorIn</div>
        <div class="tagline-kiri">Sistem Informasi Donor Darah</div>
        <div class="drop-besar">🩸</div>
        <ul class="fitur-list">
            <li>
                <div class="fitur-ikon">🩸</div>
                <span><strong>Pendonor</strong> — Lihat permintaan darah &amp; respon kebutuhan pasien</span>
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

    <!-- PANEL KANAN -->
    <div class="panel-kanan">
        <div class="form-login">

            <h2>Selamat Datang 👋</h2>
            <p class="sub">Masuk ke akun Anda untuk melanjutkan ke DonorIn.</p>

            <?php if ($pesan_error && !$buka_modal): ?>
                <div class="pesan-error">❌ <?= htmlspecialchars($pesan_error) ?></div>
            <?php endif; ?>

            <?php if ($pesan_sukses): ?>
                <div class="pesan-sukses">✅ <?= htmlspecialchars($pesan_sukses) ?></div>
            <?php endif; ?>

            <!-- FORM LOGIN -->
            <form method="POST" action="login.php">
                <div class="grup-input">
                    <label>Username atau Email</label>
                    <div class="input-wrap">
                        <span class="ikon-input">👤</span>
                        <input type="text" name="username_email"
                               placeholder="Masukkan username atau email"
                               value="<?= htmlspecialchars($_POST['username_email'] ?? '') ?>"
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

            <div class="divider">atau daftar sebagai</div>

            <!-- Tombol daftar pendonor -->
            <a href="pages/donor/daftar_pendonor.php" class="tombol-daftar">
                🩸 Daftar Sebagai Pendonor
            </a>

            <!-- Tombol daftar relawan PMI — buka modal -->
            <button class="tombol-daftar-pmi" onclick="bukaModal()">
                🏛️ Daftar Sebagai Relawan PMI
            </button>

            <a href="index.php" class="link-kembali">← Kembali ke Beranda</a>
        </div>
    </div>

    <!-- ── MODAL DAFTAR RELAWAN PMI ───────────────────────────────────────── -->
    <div class="modal-overlay <?= $buka_modal ? 'aktif' : '' ?>" id="modalPMI" onclick="tutupModalLuar(event)">
        <div class="modal-box">

            <div class="modal-header">
                <div>
                    <h3>🏛️ Daftar Relawan PMI</h3>
                    <p>Isi data diri Anda untuk bergabung sebagai petugas PMI</p>
                </div>
                <button class="modal-tutup" onclick="tutupModal()">✕</button>
            </div>

            <div class="modal-body">

                <div class="pmi-badge">🏛️ Relawan PMI</div>

                <?php if ($pesan_error && $buka_modal): ?>
                    <div class="modal-error">❌ <?= htmlspecialchars($pesan_error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">

                    <div class="grup-input">
                        <label>Nama Lengkap</label>
                        <div class="input-wrap">
                            <span class="ikon-input">👤</span>
                            <input type="text" name="nama"
                                   placeholder="Nama lengkap Anda"
                                   value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                                   required>
                        </div>
                    </div>

                    <div class="grup-input">
                        <label>Username</label>
                        <div class="input-wrap">
                            <span class="ikon-input">🔖</span>
                            <input type="text" name="username_pmi"
                                   placeholder="Buat username unik"
                                   value="<?= htmlspecialchars($_POST['username_pmi'] ?? '') ?>"
                                   required>
                        </div>
                    </div>

                    <div class="grup-input">
                        <label>Email</label>
                        <div class="input-wrap">
                            <span class="ikon-input">📧</span>
                            <input type="email" name="email_pmi"
                                   placeholder="Email aktif Anda"
                                   value="<?= htmlspecialchars($_POST['email_pmi'] ?? '') ?>"
                                   required>
                        </div>
                    </div>

                    <div class="grup-input">
                        <label>Password</label>
                        <div class="input-wrap">
                            <span class="ikon-input">🔑</span>
                            <input type="password" name="password_pmi"
                                   placeholder="Minimal 6 karakter"
                                   required>
                        </div>
                    </div>

                    <div class="grup-input">
                        <label>Konfirmasi Password</label>
                        <div class="input-wrap">
                            <span class="ikon-input">🔒</span>
                            <input type="password" name="konfirmasi_pmi"
                                   placeholder="Ulangi password"
                                   required>
                        </div>
                    </div>

                    <button type="submit" name="daftar_pmi" class="tombol-submit-modal">
                        Daftar Sekarang →
                    </button>

                </form>
            </div>
        </div>
    </div>

    <script>
        function bukaModal()  { document.getElementById('modalPMI').classList.add('aktif'); }
        function tutupModal() { document.getElementById('modalPMI').classList.remove('aktif'); }
        function tutupModalLuar(e) {
            if (e.target === document.getElementById('modalPMI')) tutupModal();
        }
        // Escape key untuk tutup modal
        document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupModal(); });
    </script>

</body>
</html>