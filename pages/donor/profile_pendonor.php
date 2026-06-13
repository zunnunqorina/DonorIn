<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['pendonor_login']) || $_SESSION['pendonor_login'] !== true) {
    header("Location: ../../login.php");
    exit;
}

$pendonor_id  = $_SESSION['pendonor_id'];
$pesan_sukses = '';
$pesan_error  = '';

// Proses update profil
if (isset($_POST['update'])) {
    $nama          = trim($_POST['nama']);
    $no_hp         = trim($_POST['no_hp']);
    $berat_badan   = (int) $_POST['berat_badan'];
    $kota          = trim($_POST['kota']);
    $pekerjaan     = trim($_POST['pekerjaan'] ?? '');
    $alamat        = trim($_POST['alamat'] ?? '');
    $pernah_donor  = $_POST['pernah_donor'];
    $terakhir_donor = (!empty($_POST['terakhir_donor']) && $pernah_donor === 'ya')
                      ? $_POST['terakhir_donor'] : null;

    if ($berat_badan < 45) {
        $pesan_error = 'Berat badan minimal 45 kg.';
    } else {
        $stmt = $conn->prepare("UPDATE pendonor SET nama=?, no_hp=?, berat_badan=?, kota=?, pekerjaan=?, alamat=?, pernah_donor=?, terakhir_donor=? WHERE id = ?");
        if ($stmt->execute([$nama, $no_hp, $berat_badan, $kota, $pekerjaan, $alamat, $pernah_donor, $terakhir_donor, $pendonor_id])) {
            $_SESSION['pendonor_nama'] = $nama;
            $pesan_sukses = 'Profil berhasil diperbarui!';
        } else {
            $pesan_error = 'Gagal memperbarui data.';
        }
    }
}

// Ganti password
if (isset($_POST['ganti_password'])) {
    $password_lama = trim($_POST['password_lama']);
    $password_baru = trim($_POST['password_baru']);
    $cek = $conn->prepare("SELECT password FROM pendonor WHERE id = ?");
    $cek->execute([$pendonor_id]);
    $row = $cek->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($password_lama, $row['password'])) {
        $pesan_error = 'Password lama salah!';
    } elseif (strlen($password_baru) < 6) {
        $pesan_error = 'Password baru minimal 6 karakter.';
    } else {
        $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE pendonor SET password=? WHERE id=?");
        $upd->execute([$hash_baru, $pendonor_id]);
        $pesan_sukses = 'Password berhasil diubah!';
    }
}

// Ambil data terkini
$q = $conn->prepare("SELECT * FROM pendonor WHERE id = ?");
$q->execute([$pendonor_id]);
$pendonor = $q->fetch(PDO::FETCH_ASSOC);
$admin_username = $pendonor['nama'];

$st3 = $conn->prepare("SELECT COUNT(*) FROM notifikasi WHERE tujuan_tipe='pendonor' AND tujuan_id=? AND sudah_baca=0");
$st3->execute([$pendonor_id]);
$jml_notif_belum = $st3->fetchColumn();
$halaman_aktif = 'profile_pendonor';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya — DonorIn</title>
    <link rel="stylesheet" href="../../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .profil-layout { display: grid; grid-template-columns: 300px 1fr; gap: 24px; }
        @media (max-width: 900px) { .profil-layout { grid-template-columns: 1fr; } }

        .profil-card {
            background: var(--card);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 30px 24px;
            text-align: center;
            position: sticky;
            top: 24px;
        }
        .profil-avatar-big {
            width: 90px; height: 90px; border-radius: 50%;
            background: linear-gradient(135deg, var(--merah), #a01020);
            color: white; font-size: 2.2rem; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
        }
        .profil-nama { font-size: 1.15rem; font-weight: 700; color: var(--text); }
        .profil-email { font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; }
        .goldar-chip {
            display: inline-block; margin-top: 12px;
            background: linear-gradient(135deg, var(--merah), #a01020);
            color: white; padding: 6px 22px; border-radius: 20px;
            font-weight: 800; font-size: 1rem;
        }
        .profil-meta { margin-top: 20px; text-align: left; }
        .profil-meta-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; border-bottom: 1px solid var(--border);
            font-size: 0.85rem;
        }
        .profil-meta-item:last-child { border-bottom: none; }
        .profil-meta-item .label { color: var(--text-muted); }
        .profil-meta-item .val { color: var(--text); font-weight: 600; }
        .status-aktif { color: #1B8A4E !important; }
        .status-nonaktif { color: #dc2626 !important; }

        .form-section {
            background: var(--card);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 24px;
            margin-bottom: 0;
        }
        .form-section + .form-section {
            margin-top: 0;
            border-top: none;
            border-radius: 0 0 var(--radius) var(--radius);
        }
        .form-section:first-child {
            border-radius: var(--radius) var(--radius) 0 0;
        }
        .section-divider {
            display: flex; align-items: center; gap: 12px;
            padding: 0 24px;
            background: var(--card);
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border);
        }
        .section-divider::before,
        .section-divider::after {
            content: ''; flex: 1; height: 1px; background: var(--border);
        }
        .section-divider-label {
            font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; color: var(--text-muted);
            padding: 10px 0; white-space: nowrap;
        }
        .form-section-pw {
            background: var(--bg);
        }
        .form-section-title {
            font-size: 1rem; font-weight: 700; color: var(--text);
            margin: 0 0 20px; padding-bottom: 14px;
            border-bottom: 2px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .form-section-title i { color: var(--merah); }
        .form-section-title.pw-title i { color: #64748b; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 18px; }
        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }

        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; font-size: 0.75rem; font-weight: 600;
            color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .5px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%; height: 40px; border: 1px solid var(--border);
            border-radius: 8px; padding: 0 12px; background: var(--bg);
            color: var(--text); font-size: 0.875rem; box-sizing: border-box;
            transition: border-color .2s;
        }
        .form-group textarea { height: auto; padding: 10px 12px; resize: vertical; }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus { outline: none; border-color: var(--merah); }

        .btn-save {
            background: var(--merah); color: white; border: none;
            padding: 10px 24px; border-radius: 8px; font-weight: 700;
            font-size: 0.875rem; cursor: pointer; display: inline-flex;
            align-items: center; gap: 8px; transition: opacity .2s;
        }
        .btn-save:hover { opacity: .88; }
        .btn-save-gray { background: #64748b; }
    </style>
</head>
<body>

<?php if ($pesan_sukses): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({ icon:'success', title:'Berhasil!', text:'<?= addslashes($pesan_sukses) ?>', timer:2500, showConfirmButton:false, toast:true, position:'top-end' });
});
</script>
<?php endif; ?>
<?php if ($pesan_error): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({ icon:'error', title:'Gagal!', text:'<?= addslashes($pesan_error) ?>', timer:3000, showConfirmButton:false, toast:true, position:'top-end' });
});
</script>
<?php endif; ?>

<!-- ══════════════ SIDEBAR ══════════════ -->
<?php include '../../components/sidebar_pendonor.php'; ?>

<!-- ══════════════ MAIN ══════════════ -->
<main class="main">

    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <div class="topbar-title">Profil Saya</div>
                <div class="topbar-breadcrumb">DonorIn / <span>Profil Pendonor</span></div>
            </div>
        </div>
        <div class="topbar-right">
            <div class="date-chip">
                <i class="fas fa-calendar-day"></i>
                <?= date('d M Y') ?>
            </div>
        </div>
    </header>

    <div class="content">
        <div class="profil-layout">

            <!-- KARTU PROFIL -->
            <div>
                <div class="profil-card">
                    <div class="profil-avatar-big"><?= strtoupper(substr($pendonor['nama'], 0, 1)) ?></div>
                    <div class="profil-nama"><?= htmlspecialchars($pendonor['nama']) ?></div>
                    <div class="profil-email"><?= htmlspecialchars($pendonor['email']) ?></div>
                    <div class="goldar-chip">Gol. <?= $pendonor['goldar'] ?></div>
                    <div class="profil-meta">
                        <div class="profil-meta-item">
                            <span class="label">Umur</span>
                            <span class="val"><?= $pendonor['umur'] ?> tahun</span>
                        </div>
                        <div class="profil-meta-item">
                            <span class="label">Jenis Kelamin</span>
                            <span class="val"><?= $pendonor['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></span>
                        </div>
                        <div class="profil-meta-item">
                            <span class="label">Kota</span>
                            <span class="val"><?= htmlspecialchars($pendonor['kota']) ?></span>
                        </div>
                        <div class="profil-meta-item">
                            <span class="label">No. HP</span>
                            <span class="val"><?= htmlspecialchars($pendonor['no_hp']) ?></span>
                        </div>
                        <div class="profil-meta-item">
                            <span class="label">Status</span>
                            <span class="val <?= $pendonor['status_aktif'] == 'aktif' ? 'status-aktif' : 'status-nonaktif' ?>">
                                <?= ucfirst($pendonor['status_aktif']) ?>
                            </span>
                        </div>
                        <div class="profil-meta-item">
                            <span class="label">Pernah Donor</span>
                            <span class="val"><?= ucfirst($pendonor['pernah_donor']) ?></span>
                        </div>
                        <?php if ($pendonor['terakhir_donor']): ?>
                        <div class="profil-meta-item">
                            <span class="label">Donor Terakhir</span>
                            <span class="val"><?= date('d M Y', strtotime($pendonor['terakhir_donor'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- FORM EDIT -->
            <div>
                <!-- Update Profil -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-edit"></i> Perbarui Data Profil
                    </div>
                    <form method="POST" action="profile_pendonor.php">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Nama Lengkap *</label>
                                <input type="text" name="nama" value="<?= htmlspecialchars($pendonor['nama']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>No. HP *</label>
                                <input type="text" name="no_hp" value="<?= htmlspecialchars($pendonor['no_hp']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Berat Badan (kg) *</label>
                                <input type="number" name="berat_badan" value="<?= $pendonor['berat_badan'] ?>" min="30" max="200" required>
                            </div>
                            <div class="form-group">
                                <label>Kota *</label>
                                <input type="text" name="kota" value="<?= htmlspecialchars($pendonor['kota']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Pekerjaan</label>
                                <input type="text" name="pekerjaan" value="<?= htmlspecialchars($pendonor['pekerjaan'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Pernah Donor?</label>
                                <select name="pernah_donor" id="pd_edit">
                                    <option value="tidak" <?= $pendonor['pernah_donor']=='tidak' ? 'selected' : '' ?>>Belum Pernah</option>
                                    <option value="ya"    <?= $pendonor['pernah_donor']=='ya'    ? 'selected' : '' ?>>Sudah Pernah</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id="grup_td_edit" style="display:<?= $pendonor['pernah_donor']=='ya' ? 'block' : 'none' ?>;">
                            <label>Tanggal Donor Terakhir</label>
                            <input type="date" name="terakhir_donor" value="<?= $pendonor['terakhir_donor'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea name="alamat" rows="2"><?= htmlspecialchars($pendonor['alamat'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" name="update" class="btn-save">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>

                <!-- Divider -->
                <div class="section-divider">
                    <span class="section-divider-label"><i class="fas fa-lock" style="margin-right:6px;"></i>Keamanan Akun</span>
                </div>

                <!-- Ganti Password -->
                <div class="form-section form-section-pw">
                    <div class="form-section-title pw-title">
                        <i class="fas fa-key"></i> Ganti Password
                        <span style="margin-left:auto; font-size:0.72rem; font-weight:400; color:var(--text-muted);">Minimal 6 karakter</span>
                    </div>
                    <form method="POST" action="profile_pendonor.php">
                        <div class="form-group">
                            <label>Password Lama</label>
                            <input type="password" name="password_lama" placeholder="Password saat ini" required>
                        </div>
                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" name="password_baru" placeholder="Minimal 6 karakter" required>
                        </div>
                        <button type="submit" name="ganti_password" class="btn-save btn-save-gray">
                            <i class="fas fa-key"></i> Ganti Password
                        </button>
                    </form>
                </div>
            </div>

        </div><!-- /profil-layout -->
    </div><!-- /content -->
</main>

<script src="../../assets/admin.js"></script>
<script>
document.getElementById('btnToggleSidebar').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.add('open');
});
document.getElementById('btnCloseSidebar').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.remove('open');
});
document.getElementById('pd_edit').addEventListener('change', function() {
    document.getElementById('grup_td_edit').style.display = (this.value === 'ya') ? 'block' : 'none';
});
</script>
</body>
</html>
<?php $conn = null; ?>