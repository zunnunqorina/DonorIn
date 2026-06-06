<?php
include '../../config/koneksi.php';

// Cek session pendonor
if (!isset($_SESSION['pendonor_login']) || $_SESSION['pendonor_login'] !== true) {
    header("Location: ../../auth/login_pendonor.php");
    exit;
}

$pendonor_id = $_SESSION['pendonor_id'];

// Ambil data pendonor terkini
$q_pendonor = mysqli_query($conn, "SELECT * FROM pendonor WHERE id = $pendonor_id");
$pendonor   = mysqli_fetch_assoc($q_pendonor);

// Hitung statistik
$jml_permintaan_aktif = mysqli_num_rows(mysqli_query($conn,
    "SELECT id FROM permintaan_darah WHERE status IN ('menunggu','diproses') AND goldar = '{$pendonor['goldar']}'"));
$jml_respon = mysqli_num_rows(mysqli_query($conn,
    "SELECT id FROM respon_donor WHERE pendonor_id = $pendonor_id"));
$jml_notif_belum = mysqli_num_rows(mysqli_query($conn,
    "SELECT id FROM notifikasi WHERE tujuan_tipe='pendonor' AND tujuan_id=$pendonor_id AND sudah_baca=0"));

// Permintaan darah terbaru sesuai goldar pendonor
$q_permintaan = mysqli_query($conn,
    "SELECT pd.*, p.nama AS nama_pasien, p.no_hp AS hp_pasien
     FROM permintaan_darah pd
     JOIN pasien p ON pd.pasien_id = p.id
     WHERE pd.goldar = '{$pendonor['goldar']}' AND pd.status IN ('menunggu','diproses')
     ORDER BY pd.tanggal DESC LIMIT 5");

// Notifikasi terbaru
$q_notif = mysqli_query($conn,
    "SELECT * FROM notifikasi WHERE tujuan_tipe='pendonor' AND tujuan_id=$pendonor_id ORDER BY tanggal DESC LIMIT 5");

$halaman_aktif = 'dashboard_pendonor';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Dashboard Pendonor</title>
    <link rel="stylesheet" href="../../asssets/styles.css">
</head>
<body style="background:#f4f4f4;">

<header class="header-pendonor">
    <div class="wadah flex-header">
        <div class="logo"><strong>DonorIn</strong> &mdash; Portal Pendonor</div>
        <div style="font-size:0.9rem; color:rgba(255,255,255,0.85);">
            Login sebagai: <strong style="color:white;"><?php echo htmlspecialchars($pendonor['nama']); ?></strong>
            &nbsp;|&nbsp; Golongan Darah: <strong style="color:#ffcccc;"><?php echo $pendonor['goldar']; ?></strong>
        </div>
        <a href="auth/logout_pendonor.php" style="background:rgba(255,255,255,0.2); color:white; border:1px solid rgba(255,255,255,0.4); padding:8px 18px; border-radius:20px; font-weight:bold; text-decoration:none; font-size:0.9rem;">
            🚪 Logout
        </a>
    </div>
</header>

<main class="wadah" style="padding:40px 20px;">

    <!-- Kartu Statistik -->
    <div class="grid-dashboard">
        <a href="cari_permintaan.php" class="kartu-dashboard merah">
            <div class="ikon-besar">🩸</div>
            <div class="label-kartu"><?php echo $jml_permintaan_aktif; ?> Permintaan Aktif</div>
            <div class="sub-label">Golongan darah <?php echo $pendonor['goldar']; ?></div>
        </a>
        <a href="riwayat_responpendonor.php" class="kartu-dashboard hijau">
            <div class="ikon-besar">✅</div>
            <div class="label-kartu"><?php echo $jml_respon; ?> Respon Dikirim</div>
            <div class="sub-label">Total riwayat respon Anda</div>
        </a>
        <a href="notifikasi_pendonor.php" class="kartu-dashboard orange">
            <div class="ikon-besar">🔔</div>
            <div class="label-kartu"><?php echo $jml_notif_belum; ?> Notifikasi Baru</div>
            <div class="sub-label">Pengingat & informasi donor</div>
        </a>
        <a href="profile_pendonor.php" class="kartu-dashboard biru">
            <div class="ikon-besar">👤</div>
            <div class="label-kartu">Profil Saya</div>
            <div class="sub-label">Lihat & perbarui data diri</div>
        </a>
    </div>

    <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:25px; flex-wrap:wrap;">

        <!-- Permintaan Darah Cocok -->
        <div class="blok-konten">
            <h3 style="color:#8b0000; border-bottom:2px solid #8b0000; padding-bottom:8px; margin-bottom:15px;">
                🩸 Permintaan Darah Golongan <?php echo $pendonor['goldar']; ?>
            </h3>
            <?php if (mysqli_num_rows($q_permintaan) == 0): ?>
                <p class="kosong">Tidak ada permintaan aktif untuk golongan darah Anda saat ini.</p>
            <?php else: ?>
                <?php while ($pm = mysqli_fetch_assoc($q_permintaan)):
                    $tgl_pm = date('d M Y, H:i', strtotime($pm['tanggal']));
                    $status_class = 'status-' . $pm['status'];
                ?>
                <div class="kartu-permintaan">
                    <div class="goldar-badge"><?php echo $pm['goldar']; ?></div>
                    <div class="info-permintaan">
                        <h4><?php echo htmlspecialchars($pm['nama_rs']); ?> — <?php echo htmlspecialchars($pm['kota']); ?></h4>
                        <p>
                            👤 Pasien: <strong><?php echo htmlspecialchars($pm['nama_pasien']); ?></strong><br>
                            🩸 Jumlah: <strong><?php echo $pm['jumlah_kantong']; ?> kantong</strong><br>
                            📝 <?php echo htmlspecialchars($pm['keterangan'] ?? '-'); ?><br>
                            🕐 <?php echo $tgl_pm; ?>
                        </p>
                    </div>
                    <div class="aksi-permintaan">
                        <span class="status-badge <?php echo $status_class; ?>"><?php echo $pm['status']; ?></span>
                        <a href="respon_permintaan.php?id=<?php echo $pm['id']; ?>"
                           style="background:#8b0000; color:white; padding:8px 15px; border-radius:6px; text-decoration:none; font-size:0.85rem; font-weight:bold;">
                            Saya Bersedia
                        </a>
                        <a href="tel:<?php echo $pm['hp_pasien']; ?>"
                           style="background:#27ae60; color:white; padding:8px 15px; border-radius:6px; text-decoration:none; font-size:0.85rem; font-weight:bold;">
                            📞 Hubungi
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
                <a href="cari_permintaan.php" style="color:#8b0000; font-weight:bold; font-size:0.9rem;">Lihat semua permintaan →</a>
            <?php endif; ?>
        </div>

        <!-- Notifikasi Terbaru -->
        <div class="blok-konten">
            <h3 style="color:#8b0000; border-bottom:2px solid #8b0000; padding-bottom:8px; margin-bottom:15px;">
                🔔 Notifikasi Terbaru
            </h3>
            <?php if (mysqli_num_rows($q_notif) == 0): ?>
                <p class="kosong">Belum ada notifikasi.</p>
            <?php else: ?>
                <?php while ($notif = mysqli_fetch_assoc($q_notif)):
                    $kelas_notif = $notif['sudah_baca'] ? 'sudah-baca' : 'belum-baca';
                    $tgl_notif = date('d M Y', strtotime($notif['tanggal']));
                ?>
                <div class="kartu-notif <?php echo $kelas_notif; ?>">
                    <div class="ikon-notif">🔔</div>
                    <div class="isi-notif">
                        <h5><?php echo htmlspecialchars($notif['judul']); ?></h5>
                        <p><?php echo htmlspecialchars($notif['pesan']); ?></p>
                        <div class="waktu-notif"><?php echo $tgl_notif; ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
                <a href="notifikasi_pendonor.php" style="color:#8b0000; font-weight:bold; font-size:0.9rem;">Lihat semua →</a>
            <?php endif; ?>
        </div>

    </div>

    <!-- Menu Cepat -->
    <div class="blok-konten" style="margin-top:25px;">
        <h3 style="color:#8b0000; border-bottom:2px solid #8b0000; padding-bottom:8px; margin-bottom:20px;">
            ⚡ Akses Cepat
        </h3>
        <div style="display:flex; gap:15px; flex-wrap:wrap;">
            <a href="cari_permintaan.php" class="tombol-utama" style="font-size:0.9rem; padding:10px 20px;">
                🔍 Lihat Semua Permintaan
            </a>
            <a href="profile_pendonor.php" class="tombol-sekunder" style="font-size:0.9rem; padding:10px 20px;">
                ✏️ Perbarui Profil
            </a>
            <a href="riwayat_responpendonor.php" class="tombol-sekunder" style="font-size:0.9rem; padding:10px 20px;">
                📋 Riwayat Respon Saya
            </a>
            <a href="notifikasi_pendonor.php" class="tombol-sekunder" style="font-size:0.9rem; padding:10px 20px;">
                🔔 Semua Notifikasi
            </a>
            <a href="edukasi_donor.php" class="tombol-sekunder" style="font-size:0.9rem; padding:10px 20px;">
                📖 Edukasi Donor Darah
            </a>
        </div>
    </div>

</main>

<footer class="footer-utama">
    <div class="wadah">
        <p>&copy; 2026 DonorIn System. Dibuat oleh: ZUNNUN QORINA (F1D02410030)</p>
    </div>
</footer>

<?php mysqli_close($conn); ?>
</body>
</html>