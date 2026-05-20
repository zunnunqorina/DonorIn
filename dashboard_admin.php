<?php
// ============================================================
//  Dashboard Admin — dilindungi oleh session
// ============================================================

include 'koneksi.php';
include 'cek_session.php'; // redirect jika belum login
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Dashboard Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .header-admin {
            background: #1a1a1a;
            color: white;
            padding: 15px 0;
        }
        .flex-header { display:flex; justify-content:space-between; align-items:center; }
        .info-admin  { font-size: 0.9rem; color: #ccc; }
        .info-admin strong { color: white; }
        .tombol-logout {
            background: #8b0000;
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .tombol-logout:hover { background: #6b0000; }
        .grid-statistik {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .kartu-stat-admin {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border-top: 4px solid #8b0000;
            text-align: center;
        }
        .kartu-stat-admin .angka {
            font-size: 2.5rem;
            font-weight: bold;
            color: #8b0000;
        }
        .kartu-stat-admin .label {
            color: #777;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .tab-judul {
            color: #8b0000;
            font-size: 1.3rem;
            margin-bottom: 15px;
            border-bottom: 2px solid #8b0000;
            padding-bottom: 8px;
        }
    </style>
</head>
<body style="background:#f4f4f4;">

    <!-- Header Admin -->
    <header class="header-admin">
        <div class="wadah flex-header">
            <div class="logo"><strong>DonorIn</strong> &mdash; Panel Admin</div>
            <div class="info-admin">
                Login sebagai: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
            </div>
            <a href="logout_admin.php" class="tombol-logout">🚪 Logout</a>
        </div>
    </header>

    <main class="wadah" style="padding: 40px 20px;">

        <!-- Statistik -->
        <?php
        $jml_relawan = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM relawan"));
        $jml_kritik  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM kritik_saran"));
        $jml_kritik_cat  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM kritik_saran WHERE kategori='kritik'"));
        $jml_saran_cat   = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM kritik_saran WHERE kategori='saran'"));
        ?>
        <div class="grid-statistik">
            <div class="kartu-stat-admin">
                <div class="angka"><?php echo $jml_relawan; ?></div>
                <div class="label">Total Relawan Terdaftar</div>
            </div>
            <div class="kartu-stat-admin">
                <div class="angka"><?php echo $jml_kritik; ?></div>
                <div class="label">Total Pesan Masuk</div>
            </div>
            <div class="kartu-stat-admin">
                <div class="angka"><?php echo $jml_kritik_cat; ?></div>
                <div class="label">Kritik</div>
            </div>
            <div class="kartu-stat-admin">
                <div class="angka"><?php echo $jml_saran_cat; ?></div>
                <div class="label">Saran</div>
            </div>
        </div>

        <!-- Tabel Data Relawan -->
        <div class="blok-konten">
            <h3 class="tab-judul">👥 Data Relawan Terdaftar</h3>
            <?php
            $q_relawan = "SELECT * FROM relawan ORDER BY tanggal_daftar DESC";
            $r_relawan = mysqli_query($conn, $q_relawan);
            if (mysqli_num_rows($r_relawan) == 0):
            ?>
                <p class="kosong">Belum ada relawan terdaftar.</p>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="tabel-data">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th>Gol. Darah</th>
                            <th>Umur</th>
                            <th>Kota</th>
                            <th>Tanggal Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $no = 1; while ($baris = mysqli_fetch_assoc($r_relawan)): ?>
                        <tr>
                            <td style="text-align:center;"><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($baris['nama']); ?></td>
                            <td><?php echo htmlspecialchars($baris['email']); ?></td>
                            <td><?php echo htmlspecialchars($baris['no_hp']); ?></td>
                            <td style="text-align:center;font-weight:bold;color:#8b0000;">
                                <?php echo $baris['goldar']; ?>
                            </td>
                            <td style="text-align:center;"><?php echo $baris['umur']; ?> thn</td>
                            <td><?php echo htmlspecialchars($baris['kota']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($baris['tanggal_daftar'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tabel Data Kritik & Saran -->
        <div class="blok-konten">
            <h3 class="tab-judul">📋 Data Kritik & Saran</h3>
            <?php
            $q_kritik = "SELECT * FROM kritik_saran ORDER BY tanggal DESC";
            $r_kritik = mysqli_query($conn, $q_kritik);
            if (mysqli_num_rows($r_kritik) == 0):
            ?>
                <p class="kosong">Belum ada pesan masuk.</p>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="tabel-data">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Kategori</th>
                            <th>Pesan</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $no = 1; while ($baris = mysqli_fetch_assoc($r_kritik)):
                        $badge = 'badge-' . $baris['kategori'];
                        $tgl   = date('d/m/Y H:i', strtotime($baris['tanggal']));
                    ?>
                        <tr>
                            <td style="text-align:center;"><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($baris['nama']); ?></td>
                            <td><?php echo htmlspecialchars($baris['email']); ?></td>
                            <td style="text-align:center;">
                                <span class="badge-kategori <?php echo $badge; ?>">
                                    <?php echo $baris['kategori']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($baris['pesan']); ?></td>
                            <td style="white-space:nowrap;"><?php echo $tgl; ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
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
