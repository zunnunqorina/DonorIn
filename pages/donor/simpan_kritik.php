<?php

include '../../config/koneksi.php';

if (!isset($_POST['kirim'])) {
    header("Location: kritik_saran.php");
    exit;
}

$nama     = trim($_POST['nama']);
$email    = trim($_POST['email']);
$kategori = $_POST['kategori'];
$pesan    = trim($_POST['pesan']);

if ($nama == '' || $email == '' || $kategori == '' || $pesan == '') {
    echo "<script>
        alert('❌ Semua kolom harus diisi!');
        history.back();
    </script>";
    exit;
}

$stmt = $conn->prepare("INSERT INTO kritik_saran (nama, email, kategori, pesan) VALUES (?, ?, ?, ?)");
$hasil = $stmt->execute([$nama, $email, $kategori, $pesan]);

if ($hasil) {
    echo "<script>
        alert('✅ Terima kasih $nama!\\nPesan Anda berhasil dikirim.');
        window.location = 'kritik_saran.php';
    </script>";
} else {
    echo "<script>
        alert('❌ Gagal menyimpan pesan. Silakan coba lagi.');
        history.back();
    </script>";
}

$conn = null;
?>