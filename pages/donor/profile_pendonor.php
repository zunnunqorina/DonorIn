<?php
include 'koneksi.php';

if (!isset($_SESSION['pendonor_login']) || $_SESSION['pendonor_login'] !== true) {
    header("Location: login_pendonor.php");
    exit;
}

$pendonor_id  = $_SESSION['pendonor_id'];
$pesan_status = "";

// Proses update profil
if (isset($_POST['update'])) {
    $nama          = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $no_hp         = mysqli_real_escape_string($conn, trim($_POST['no_hp']));
    $berat_badan   = (int) $_POST['berat_badan'];
    $kota          = mysqli_real_escape_string($conn, trim($_POST['kota']));
    $pekerjaan     = mysqli_real_escape_string($conn, trim($_POST['pekerjaan'] ?? ''));
    $alamat        = mysqli_real_escape_string($conn, trim($_POST['alamat'] ?? ''));
    $pernah_donor  = mysqli_real_escape_string($conn, $_POST['pernah_donor']);
    $terakhir_donor = (!empty($_POST['terakhir_donor']) && $pernah_donor === 'ya')
                      ? "'" . mysqli_real_escape_string($conn, $_POST['terakhir_donor']) . "'"
                      : "NULL";

    if ($berat_badan < 45) {
        $pesan_status = "<div class='pesan-error'>❌ Berat badan minimal 45 kg.</div>";
    } else {
        $query = "UPDATE pendonor SET
                    nama='$nama', no_hp='$no_hp', berat_badan=$berat_badan,
                    kota='$kota', pekerjaan='$pekerjaan', alamat='$alamat',
                    pernah_donor='$pernah_donor', terakhir_donor=$terakhir_donor
                  WHERE id = $pendonor_id";
        if (mysqli_query($conn, $query)) {
            $_SESSION['pendonor_nama'] = $nama;
            $pesan_status = "<div class='pesan-sukses'>✅ Profil berhasil diperbarui!</div>";
        } else {
            $pesan_status = "<div class='pesan-error'>❌ Gagal memperbarui: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Ganti password
if (isset($_POST['ganti_password'])) {
    $password_lama = trim($_POST['password_lama']);
    $password_baru = trim($_POST['password_baru']);

    $cek = mysqli_query($conn, "SELECT id FROM pendonor WHERE id=$pendonor_id AND password=MD5('$password_lama')");
    if (mysqli_num_rows($cek) == 0) {
        $pesan_status = "<div class='pesan-error'>❌ Password lama salah!</div>";
    } elseif (strlen($password_baru) < 6) {
        $pesan_status = "<div class='pesan-error'>❌ Password baru minimal 6 karakter.</div>";
    } else {
        mysqli_query($conn, "UPDATE pendonor SET password=MD5('$password_baru') WHERE id=$pendonor_id");
        $pesan_status = "<div class='pesan-sukses'>✅ Password berhasil diubah!</div>";
    }
}

// Ambil data terkini
$q = mysqli_query($conn, "SELECT * FROM pendonor WHERE id = $pendonor_id");
$pendonor = mysqli_fetch_assoc($q);
$halaman_aktif = 'dashboard_pendonor';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Profil Pendonor</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body style="background:#f4f4f4;">

<?php include 'header.php'; ?>

<main class="wadah" style="padding:40px 20px;">
    <h2 style="color:#8b0000; margin-bottom:5px;">👤 Profil Pendonor</h2>
    <p style="color:#888; margin-bottom:25px;"><a href="dashboard_pendonor.php" style="color:#8b0000;">← Dashboard</a></p>

    <?php echo $pesan_status; ?>

    <div style="display:grid; grid-template-columns:1fr 1.5fr; gap:25px; flex-wrap:wrap;">

        <!-- Kartu Info Profil -->
        <div class="blok-konten" style="text-align:center;">
            <div class="profil-avatar">🩸</div>
            <div class="profil-nama"><?php echo htmlspecialchars($pendonor['nama']); ?></div>
            <div class="profil-sub"><?php echo htmlspecialchars($pendonor['email']); ?></div>
            <div style="margin:10px 0;">
                <span style="background:#8b0000; color:white; padding:6px 20px; border-radius:20px; font-weight:bold; font-size:1.1rem;">
                    Gol. <?php echo $pendonor['goldar']; ?>
                </span>
            </div>
            <div class="profil-grid" style="margin-top:20px; text-align:left;">
                <div class="profil-item">
                    <label>Umur</label>
                    <span><?php echo $pendonor['umur']; ?> tahun</span>
                </div>
                <div class="profil-item">
                    <label>Jenis Kelamin</label>
                    <span><?php echo $pendonor['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></span>
                </div>
                <div class="profil-item">
                    <label>Kota</label>
                    <span><?php echo htmlspecialchars($pendonor['kota']); ?></span>
                </div>
                <div class="profil-item">
                    <label>No. HP</label>
                    <span><?php echo htmlspecialchars($pendonor['no_hp']); ?></span>
                </div>
                <div class="profil-item">
                    <label>Status</label>
                    <span style="color:<?php echo $pendonor['status_aktif']=='aktif' ? '#27ae60' : '#e74c3c'; ?>; font-weight:bold;">
                        <?php echo ucfirst($pendonor['status_aktif']); ?>
                    </span>
                </div>
                <div class="profil-item">
                    <label>Pernah Donor</label>
                    <span><?php echo ucfirst($pendonor['pernah_donor']); ?></span>
                </div>
                <?php if ($pendonor['terakhir_donor']): ?>
                <div class="profil-item" style="grid-column:span 2;">
                    <label>Donor Terakhir</label>
                    <span><?php echo date('d M Y', strtotime($pendonor['terakhir_donor'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Form Update Profil -->
        <div>
            <div class="blok-konten" style="margin-bottom:20px;">
                <h3 style="color:#8b0000; border-bottom:2px solid #8b0000; padding-bottom:8px; margin-bottom:20px;">
                    ✏️ Perbarui Data Profil
                </h3>
                <form method="POST" action="profil_pendonor.php">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0 15px;">
                        <div class="grup-form">
                            <label>Nama Lengkap *</label>
                            <input type="text" name="nama" value="<?php echo htmlspecialchars($pendonor['nama']); ?>" required>
                        </div>
                        <div class="grup-form">
                            <label>No. HP *</label>
                            <input type="text" name="no_hp" value="<?php echo htmlspecialchars($pendonor['no_hp']); ?>" required>
                        </div>
                        <div class="grup-form">
                            <label>Berat Badan (kg) *</label>
                            <input type="number" name="berat_badan" value="<?php echo $pendonor['berat_badan']; ?>" min="30" max="200" required>
                        </div>
                        <div class="grup-form">
                            <label>Kota *</label>
                            <input type="text" name="kota" value="<?php echo htmlspecialchars($pendonor['kota']); ?>" required>
                        </div>
                        <div class="grup-form">
                            <label>Pekerjaan</label>
                            <input type="text" name="pekerjaan" value="<?php echo htmlspecialchars($pendonor['pekerjaan'] ?? ''); ?>">
                        </div>
                        <div class="grup-form">
                            <label>Pernah Donor?</label>
                            <select name="pernah_donor" id="pd_edit">
                                <option value="tidak" <?php echo $pendonor['pernah_donor']=='tidak' ? 'selected' : ''; ?>>Belum Pernah</option>
                                <option value="ya"    <?php echo $pendonor['pernah_donor']=='ya'    ? 'selected' : ''; ?>>Sudah Pernah</option>
                            </select>
                        </div>
                    </div>
                    <div class="grup-form" id="grup_td_edit" style="display:<?php echo $pendonor['pernah_donor']=='ya' ? 'block' : 'none'; ?>;">
                        <label>Tanggal Donor Terakhir</label>
                        <input type="date" name="terakhir_donor" value="<?php echo $pendonor['terakhir_donor'] ?? ''; ?>">
                    </div>
                    <div class="grup-form">
                        <label>Alamat</label>
                        <textarea name="alamat" rows="2" style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc; font-family:inherit; resize:vertical; box-sizing:border-box;"><?php echo htmlspecialchars($pendonor['alamat'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="update" class="tombol-auth tombol-auth-merah">
                        💾 SIMPAN PERUBAHAN
                    </button>
                </form>
            </div>

            <!-- Ganti Password -->
            <div class="blok-konten">
                <h3 style="color:#555; border-bottom:2px solid #eee; padding-bottom:8px; margin-bottom:20px;">
                    🔑 Ganti Password
                </h3>
                <form method="POST" action="profil_pendonor.php">
                    <div class="grup-form">
                        <label>Password Lama</label>
                        <input type="password" name="password_lama" placeholder="Password saat ini" required>
                    </div>
                    <div class="grup-form">
                        <label>Password Baru</label>
                        <input type="password" name="password_baru" placeholder="Min. 6 karakter" required>
                    </div>
                    <button type="submit" name="ganti_password" class="tombol-auth" style="background:#555; color:white;">
                        🔑 GANTI PASSWORD
                    </button>
                </form>
            </div>
        </div>

    </div>
</main>

<?php include 'footer.php'; ?>
<?php mysqli_close($conn); ?>
<script>
document.getElementById('pd_edit').addEventListener('change', function() {
    document.getElementById('grup_td_edit').style.display = (this.value === 'ya') ? 'block' : 'none';
});
</script>
</body>
</html>