<?php
include 'koneksi.php';

if (isset($_POST['kirim_relawan'])) {
    $nama   = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $email  = mysqli_real_escape_string($conn, trim($_POST['email']));
    $tgl    = mysqli_real_escape_string($conn, $_POST['tgl']);
    $goldar = mysqli_real_escape_string($conn, $_POST['goldar']);

    $lahir = new DateTime($tgl);
    $umur  = $lahir->diff(new DateTime())->y;

    if ($nama == '' || $email == '' || $tgl == '' || $goldar == '') {
        echo "<script>alert('❌ Semua kolom harus diisi!'); history.back();</script>";
        exit;
    }
    if ($umur < 17) {
        echo "<script>alert('❌ Maaf, usia kamu baru $umur tahun. Minimal 17 tahun.'); history.back();</script>";
        exit;
    }

    $query = "INSERT INTO relawan (nama, email, tgl_lahir, goldar) 
              VALUES ('$nama', '$email', '$tgl', '$goldar')";
    $hasil = mysqli_query($conn, $query);

    if ($hasil) {
        echo "<script>alert('✅ Terima kasih $nama! Pendaftaran relawan berhasil.\\nGolongan Darah: $goldar | Usia: $umur tahun'); 
              window.location='page2.php';</script>";
    } else {
        echo "<script>alert('❌ Gagal menyimpan data.'); history.back();</script>";
    }
    mysqli_close($conn);
}
?>