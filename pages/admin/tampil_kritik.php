<?php
include '../../config/koneksi.php';
$halaman_aktif = 'kritik';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Data Kritik & Saran</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <style>
        .badge-kategori {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-kritik     { background:#f8d7da; color:#721c24; }
        .badge-saran      { background:#d4edda; color:#155724; }
        .badge-pertanyaan { background:#cce5ff; color:#004085; }
        .info-total {
            background: #fff3f3;
            border-left: 4px solid #8b0000;
            padding: 12px 18px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 20px;
            font-size: 0.95rem;
            color: #555;
        }
        .info-total strong { color: #8b0000; }
        .tombol-kembali {
            display: inline-block;
            background: #8b0000;
            color: white;
            padding: 10px 22px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }
        .tombol-kembali:hover { background: #6b0000; }
    </style>
</head>
<body>

<?php include '../../components/header.php'; ?>

<main class="wadah konten-halaman">
    <section class="blok-konten">
        <h2>📋 Data Kritik & Saran Masuk</h2>

        <a href="../../login.php" class="tombol-kembali" style="display:inline-block; margin-top:10px;">← Kirim Pesan Baru</a>

        <?php
        $stmt   = $conn->query("SELECT * FROM kritik_saran ORDER BY tanggal DESC");
        $data   = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $jumlah = count($data);
        ?>

        <div class="info-total">
            Total pesan masuk: <strong><?php echo $jumlah; ?> pesan</strong>
        </div>

        <?php if ($jumlah == 0): ?>
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
                    <?php
                    $no = 1;
                    foreach ($data as $baris):
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
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </section>
</main>

<?php include '../../components/footer.php'; ?>
<?php $conn = null; ?>
</body>
</html>