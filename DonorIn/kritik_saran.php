<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn - Kritik & Saran</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .pesan-sukses {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .pesan-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .badge-kategori {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-kritik    { background: #f8d7da; color: #721c24; }
        .badge-saran     { background: #d4edda; color: #155724; }
        .badge-pertanyaan{ background: #cce5ff; color: #004085; }
        .kartu-pesan {
            background: white;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 18px 20px;
            margin-bottom: 15px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .kartu-pesan h4 { margin: 0 0 6px; color: #1a1a1a; font-size: 1rem; }
        .kartu-pesan p  { margin: 8px 0 0; color: #444; line-height: 1.6; }
        .kartu-meta     { font-size: 12px; color: #999; margin-top: 8px; }
        .dua-kolom { display: flex; gap: 40px; flex-wrap: wrap; }
        .kolom-kiri  { flex: 1; min-width: 300px; }
        .kolom-kanan { flex: 1.2; min-width: 300px; }
        .judul-seksi { color: #8b0000; font-size: 1.4rem; margin-bottom: 20px; border-bottom: 2px solid #8b0000; padding-bottom: 8px; }
        .radio-grup { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 6px; }
        .radio-item { display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.95rem; }
        .kosong { color: #999; text-align: center; padding: 30px; font-style: italic; }
    </style>
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
            <h2 class="judul-seksi">💬 Kirim Kritik & Saran</h2>
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
            <h2 class="judul-seksi">📋 Pesan Masuk</h2>

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