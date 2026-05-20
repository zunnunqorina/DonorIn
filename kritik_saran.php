<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn - Kritik & Saran</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="header-utama">
    <div class="wadah flex-header">
        <div class="logo"><strong>DonorIn</strong></div>
        <nav class="navigasi-utama">
            <a href="index.html">Home</a>
            <a href="page2.html">Butuh Donor</a>
            <a href="page2.html#stok-darah">Stok Darah</a>
            <a href="page2.html#daftar-relawan">Daftar Relawan</a>
            <a href="kritik_saran.php" class="aktif">Kritik & Saran</a>
        </nav>
        <button class="tombol-admin" onclick="loginAdmin()">LOGIN ADMIN</button>
    </div>
</header>

<main class="wadah" style="padding: 50px 20px;">

    <?php
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
    echo $pesan_status;
    ?>

    <div class="dua-kolom">

        <div class="kolom-kiri">
            <h2 class="judul-seksi">Kirim Kritik & Saran</h2>
            <form method="POST" action="kritik_saran.php">

                <div class="grup-form">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" placeholder="Masukkan nama Anda" required>
                </div>

                <div class="grup-form">
                    <label for="email">Email Aktif</label>
                    <input type="email" id="email" name="email" placeholder="nama@email.com" required>
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
                        style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc; resize:vertical; font-family:inherit;"></textarea>
                </div>

                <button type="submit" name="kirim" class="tombol-kirim" style="width:100%; padding:12px; font-size:1rem;">
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
                        <p>{$baris['pesan']}</p>
                        <div class='kartu-meta'>✉️ {$baris['email']} &nbsp;|&nbsp; 🕐 {$tanggal_fmt}</div>
                    </div>";
                }
            }
            mysqli_close($conn);
            ?>
        </div>

    </div>
</main>

<footer class="footer-utama">
    <div class="wadah">
        <p>&copy; 2026 DonorIn System. Dibuat oleh: ZUNNUN QORINA (F1D02410030)</p>
    </div>
</footer>

<script>
function loginAdmin() {
    const username = prompt("Masukkan Username Admin:");
    const password = prompt("Masukkan Password Admin:");
    if (username === "karin" && password === "karincantik") {
        alert("✅ Login Admin Berhasil!\nSelamat datang di panel admin.");
    } else {
        alert("❌ Username atau Password salah!");
    }
}
</script>
</body>
</html>