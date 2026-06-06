<?php

include 'koneksi.php';
$halaman_aktif = 'kritik';

$pesan_status = "";

if (isset($_POST['kirim'])) {
    $nama     = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $pesan    = mysqli_real_escape_string($conn, trim($_POST['pesan']));

    if ($nama == '' || $email == '' || $kategori == '' || $pesan == '') {
        $pesan_status = '<div class="pesan-error">❌ Semua kolom harus diisi!</div>';
    } else {
        $query = "INSERT INTO kritik_saran (nama, email, kategori, pesan)
                  VALUES ('$nama', '$email', '$kategori', '$pesan')";
        $hasil = mysqli_query($conn, $query);

        if ($hasil) {
            $pesan_status = '<div class="pesan-sukses">✅ Terima kasih! Pesan Anda berhasil dikirim.</div>';
        } else {
            $pesan_status = '<div class="pesan-error">❌ Gagal menyimpan. Coba lagi.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Kritik & Saran</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js" defer></script>
</head>
<body>

<?php include 'header.php'; ?>

<main class="wadah" style="padding: 50px 20px;">

    <?php echo $pesan_status; ?>

    <div class="dua-kolom">

        <div class="kolom-kiri">
            <h2 class="judul-seksi">Kirim Kritik & Saran</h2>

            <div style="background:#fff3f3; border-left:4px solid #8b0000; padding:15px 20px;
                        border-radius:0 8px 8px 0; margin-bottom:25px; color:#555; font-size:0.95rem;">
                💡 Pendapat Anda sangat berarti bagi kami! Silakan kirim kritik, saran,
                atau pertanyaan seputar layanan DonorIn.
            </div>

            <form method="POST" action="kritik_saran.php">

                <div class="grup-form">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama"
                           placeholder="Masukkan nama Anda" required>
                </div>

                <div class="grup-form">
                    <label for="email">Email Aktif</label>
                    <input type="email" id="email" name="email"
                           placeholder="nama@email.com" required>
                </div>

                <div class="grup-form">
                    <label>Kategori</label>
                    <div class="radio-grup">
                        <label class="radio-item">
                            <input type="radio" name="kategori" value="kritik" required> 🔴 Kritik
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="kategori" value="saran"> 🟢 Saran
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="kategori" value="pertanyaan"> 🔵 Pertanyaan
                        </label>
                    </div>
                </div>

                <div class="grup-form">
                    <label for="pesan">Pesan</label>
                    <textarea id="pesan" name="pesan" rows="5"
                        placeholder="Tulis kritik, saran, atau pertanyaan Anda di sini..."
                        style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;
                               resize:vertical; font-family:inherit; box-sizing:border-box;"></textarea>
                </div>

                <button type="submit" name="kirim" class="tombol-kirim"
                        style="width:100%; padding:12px; font-size:1rem;">
                    📨 KIRIM PESAN
                </button>

            </form>
        </div>

        <div class="kolom-kanan">
            <h2 class="judul-seksi">Pesan Masuk</h2>

            <?php
            $query_tampil = "SELECT * FROM kritik_saran ORDER BY tanggal DESC";
            $hasil_tampil = mysqli_query($conn, $query_tampil);
            $jumlah       = mysqli_num_rows($hasil_tampil);

            if ($jumlah == 0) {
                echo '<p class="kosong">Belum ada pesan masuk.</p>';
            } else {
                while ($baris = mysqli_fetch_assoc($hasil_tampil)) {
                    $badge_class = 'badge-' . $baris['kategori'];
                    $tanggal_fmt = date('d M Y, H:i', strtotime($baris['tanggal']));
                    echo "
                    <div class='kartu-pesan'>
                        <h4>{$baris['nama']}
                            <span class='badge-kategori {$badge_class}'>{$baris['kategori']}</span>
                        </h4>
                        <p>" . htmlspecialchars($baris['pesan']) . "</p>
                        <div class='kartu-meta'>
                            ✉️ {$baris['email']} &nbsp;|&nbsp; 🕐 {$tanggal_fmt}
                        </div>
                    </div>";
                }
            }
            ?>

            <br>
            <a href="tampil_kritik.php"
               style="display:inline-block; background:white; color:#8b0000; border:2px solid #8b0000;
                      padding:10px 25px; border-radius:5px; font-weight:bold; text-decoration:none;
                      font-size:0.9rem;">
                📋 Lihat Semua Pesan (Tabel)
            </a>
        </div>

    </div>
</main>

<?php include 'footer.php'; ?>
<?php mysqli_close($conn); ?>
</body>
</html>
