<?php

include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn - Data Kritik & Saran</title>
    <link rel="stylesheet" href="styles.css">
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
        .badge-kritik     { background: #f8d7da; color: #721c24; }
        .badge-saran      { background: #d4edda; color: #155724; }
        .badge-pertanyaan { background: #cce5ff; color: #004085; }

        .info-total {
            background: #fff3f3;
            border-left: 4px solid #8b0000;
            padding: 12px 18px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 20px;
            font-size: 0.95rem;
            color: #555;
        }
        .info-total strong {
            color: #8b0000;
        }
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
        .tombol-kembali:hover {
            background: #6b0000;
        }
        .kosong {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
            font-size: 1rem;
        }
    </style>
</head>
<body>

    <header class="header-utama">
        <div class="wadah flex-header">
            <div class="logo">
                <strong>DonorIn</strong>
            </div>
            <nav class="navigasi-utama">
                <a href="index.html">Home</a>
                <a href="page2.html">Butuh Donor</a>
                <a href="page2.html#stok-darah">Stok Darah</a>
                <a href="page2.html#daftar-relawan">Daftar Relawan</a>
                <a href="kritik_saran.html" class="aktif">Kritik & Saran</a>
            </nav>
            <button class="tombol-admin">LOGIN ADMIN</button>
        </div>
    </header>

    <main class="wadah konten-halaman">
        <section class="blok-konten">
            <h2>📋 Data Kritik & Saran Masuk</h2>

            <a href="kritik_saran.html" class="tombol-kembali">← Kirim Pesan Baru</a>

            <?php
            $query = "SELECT * FROM kritik_saran ORDER BY tanggal DESC";
            $hasil = mysqli_query($conn, $query);
            $jumlah = mysqli_num_rows($hasil);
            ?>

            <div class="info-total">
                Total pesan masuk: <strong><?php echo $jumlah; ?> pesan</strong>
            </div>

            <?php if ($jumlah == 0): ?>
                <p class="kosong">Belum ada pesan masuk.</p>
            <?php else: ?>

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
                        while ($baris = mysqli_fetch_assoc($hasil)):
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

            <?php endif; ?>

        </section>
    </main>

    <footer class="footer-utama">
        <div class="wadah">
            <p>&copy; 2026 DonorIn System. Dibuat oleh: ZUNNUN QORINA (F1D02410030)</p>
        </div>
    </footer>

<?php mysqli_close($conn); ?>
</body>
</html>
