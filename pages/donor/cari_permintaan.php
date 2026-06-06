<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['pendonor_login']) || $_SESSION['pendonor_login'] !== true) {
    header("Location: ../../auth/login_pendonor.php");
    exit;
}

$pendonor_id   = $_SESSION['pendonor_id'];
$pendonor_goldar = $_SESSION['pendonor_goldar'];

// Filter
$filter_goldar = isset($_GET['goldar']) ? mysqli_real_escape_string($conn, $_GET['goldar']) : '';
$filter_kota   = isset($_GET['kota'])   ? mysqli_real_escape_string($conn, trim($_GET['kota']))   : '';

$where = "WHERE pd.status IN ('menunggu','diproses')";
if ($filter_goldar) $where .= " AND pd.goldar='$filter_goldar'";
if ($filter_kota)   $where .= " AND pd.kota LIKE '%$filter_kota%'";

$q_permintaan = mysqli_query($conn,
    "SELECT pd.*, p.nama AS nama_pasien, p.no_hp AS hp_pasien
     FROM permintaan_darah pd
     JOIN pasien p ON pd.pasien_id = p.id
     $where ORDER BY pd.tanggal DESC");

$halaman_aktif = 'cari_permintaan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Permintaan Darah</title>
    <link rel="stylesheet" href="../../assets/styles.css">
</head>
<body style="background:#f4f4f4;">

<?php include '../../components/header.php'; ?>

<main class="wadah" style="padding:40px 20px;">
    <h2 style="color:#8b0000; margin-bottom:5px;">🩸 Permintaan Darah Aktif</h2>
    <p style="color:#888; margin-bottom:20px;"><a href="dashboard_pendonor.php" style="color:#8b0000;">← Dashboard</a></p>

    <!-- Filter -->
    <div class="blok-konten" style="margin-bottom:20px;">
        <form method="GET" action="cari_permintaan.php" style="display:flex; gap:15px; flex-wrap:wrap; align-items:flex-end;">
            <div class="grup-form" style="margin:0; flex:1; min-width:150px;">
                <label>Filter Golongan Darah</label>
                <select name="goldar">
                    <option value="">Semua Golongan</option>
                    <?php foreach (['A','B','O','AB'] as $gd): ?>
                    <option value="<?php echo $gd; ?>" <?php echo $filter_goldar==$gd ? 'selected' : ''; ?>>
                        <?php echo $gd; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grup-form" style="margin:0; flex:1; min-width:150px;">
                <label>Filter Kota</label>
                <input type="text" name="kota" placeholder="Contoh: Mataram" value="<?php echo htmlspecialchars($filter_kota); ?>">
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="tombol-auth tombol-auth-merah" style="padding:10px 20px; width:auto;">
                    🔍 Filter
                </button>
                <a href="cari_permintaan.php" style="background:#eee; color:#333; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:bold;">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Info Goldar Pendonor -->
    <div style="background:#fff3f3; border-left:4px solid #8b0000; padding:12px 18px; border-radius:0 8px 8px 0; margin-bottom:20px; font-size:0.9rem; color:#555;">
        💡 Golongan darah Anda: <strong style="color:#8b0000;"><?php echo $pendonor_goldar; ?></strong> — 
        Anda bisa mendonorkan darah ke pasien yang membutuhkan golongan darah yang sama.
    </div>

    <?php
    $jumlah = mysqli_num_rows($q_permintaan);
    if ($jumlah == 0):
    ?>
        <div class="blok-konten">
            <p class="kosong">Tidak ada permintaan darah aktif saat ini<?php echo $filter_goldar ? " untuk golongan $filter_goldar" : ''; ?>.</p>
        </div>
    <?php else: ?>
        <p style="color:#666; margin-bottom:15px;">Menampilkan <strong><?php echo $jumlah; ?></strong> permintaan aktif</p>
        <?php while ($pm = mysqli_fetch_assoc($q_permintaan)):
            $tgl_pm = date('d M Y, H:i', strtotime($pm['tanggal']));
            $status_class = 'status-' . $pm['status'];
            // Cek apakah sudah merespon
            $sudah_respon = mysqli_num_rows(mysqli_query($conn,
                "SELECT id FROM respon_donor WHERE permintaan_id={$pm['id']} AND pendonor_id=$pendonor_id"));
            $cocok = ($pm['goldar'] == $pendonor_goldar);
        ?>
        <div class="kartu-permintaan" style="<?php echo $cocok ? 'border-left-color:#27ae60;' : ''; ?>">
            <div class="goldar-badge" style="<?php echo $cocok ? 'background:#d4edda; color:#155724;' : ''; ?>">
                <?php echo $pm['goldar']; ?>
                <?php if ($cocok): ?><br><small style="font-size:0.55rem; font-weight:normal;">COCOK</small><?php endif; ?>
            </div>
            <div class="info-permintaan">
                <h4><?php echo htmlspecialchars($pm['nama_rs']); ?> — <?php echo htmlspecialchars($pm['kota']); ?></h4>
                <p>
                    👤 Pasien: <strong><?php echo htmlspecialchars($pm['nama_pasien']); ?></strong><br>
                    🩸 Jumlah dibutuhkan: <strong><?php echo $pm['jumlah_kantong']; ?> kantong</strong><br>
                    📝 <?php echo htmlspecialchars($pm['keterangan'] ?? '-'); ?><br>
                    📍 <?php echo htmlspecialchars($pm['alamat_rs'] ?? $pm['kota']); ?><br>
                    🕐 <?php echo $tgl_pm; ?>
                </p>
            </div>
            <div class="aksi-permintaan">
                <span class="status-badge <?php echo $status_class; ?>"><?php echo $pm['status']; ?></span>
                <?php if ($sudah_respon): ?>
                    <span style="background:#d4edda; color:#155724; padding:8px 15px; border-radius:6px; font-size:0.85rem; font-weight:bold;">
                        ✅ Sudah Merespon
                    </span>
                <?php else: ?>
                    <a href="respon_permintaan.php?id=<?php echo $pm['id']; ?>"
                       style="background:#8b0000; color:white; padding:8px 15px; border-radius:6px; text-decoration:none; font-size:0.85rem; font-weight:bold;">
                        🩸 Saya Bersedia
                    </a>
                <?php endif; ?>
                <a href="tel:<?php echo $pm['hp_pasien']; ?>"
                   style="background:#27ae60; color:white; padding:8px 15px; border-radius:6px; text-decoration:none; font-size:0.85rem; font-weight:bold;">
                    📞 Hubungi Pasien
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</main>

<?php include '../../components/footer.php'; ?>
<?php mysqli_close($conn); ?>
</body>
</html>