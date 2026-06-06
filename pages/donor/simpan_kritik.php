<?php

include '../../config/koneksi.php';

if (!isset($_POST['kirim'])) {
    header("Location: kritik_saran.php");
    exit;
}

$nama     = mysqli_real_escape_string($conn, trim($_POST['nama']));
$email    = mysqli_real_escape_string($conn, trim($_POST['email']));
$kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
$pesan    = mysqli_real_escape_string($conn, trim($_POST['pesan']));

if ($nama == '' || $email == '' || $kategori == '' || $pesan == '') {
    echo "<script>
        alert('❌ Semua kolom harus diisi!');
        history.back();
    </script>";
    exit;
}

$query = "INSERT INTO kritik_saran (nama, email, kategori, pesan)
          VALUES ('$nama', '$email', '$kategori', '$pesan')";
$hasil = mysqli_query($conn, $query);

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

mysqli_close($conn);
?>
