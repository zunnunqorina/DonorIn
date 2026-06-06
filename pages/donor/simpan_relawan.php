<?php
include 'config/koneksi.php';

if (!isset($_POST['kirim_relawan'])) {
    header("Location: page2.php");
    exit;
}

$nama          = mysqli_real_escape_string($conn, trim($_POST['nama']));
$email         = mysqli_real_escape_string($conn, trim($_POST['email']));
$no_hp         = mysqli_real_escape_string($conn, trim($_POST['no_hp']));
$tgl           = mysqli_real_escape_string($conn, $_POST['tgl']);
$jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
$goldar        = mysqli_real_escape_string($conn, $_POST['goldar']);
$berat_badan   = (int) $_POST['berat_badan'];
$kota          = mysqli_real_escape_string($conn, trim($_POST['kota']));
$pekerjaan     = mysqli_real_escape_string($conn, trim($_POST['pekerjaan'] ?? ''));
$alamat        = mysqli_real_escape_string($conn, trim($_POST['alamat'] ?? ''));
$pernah_donor  = mysqli_real_escape_string($conn, $_POST['pernah_donor']);

$terakhir_donor = (!empty($_POST['terakhir_donor']) && $pernah_donor === 'ya')
                  ? "'" . mysqli_real_escape_string($conn, $_POST['terakhir_donor']) . "'"
                  : "NULL";

$lahir = new DateTime($tgl);
$umur  = $lahir->diff(new DateTime())->y;

if ($nama=='' || $email=='' || $no_hp=='' || $tgl=='' || $jenis_kelamin=='' || $goldar=='' || $berat_badan==0 || $kota=='') {
    echo "<script>alert('❌ Semua kolom wajib harus diisi!'); history.back();</script>";
    exit;
}
if ($umur < 17) {
    echo "<script>alert('❌ Maaf, usia kamu baru $umur tahun.\\nMinimal usia donor adalah 17 tahun.'); history.back();</script>";
    exit;
}
if ($umur > 65) {
    echo "<script>alert('❌ Maaf, usia kamu sudah $umur tahun.\\nMaksimal usia donor adalah 65 tahun.'); history.back();</script>";
    exit;
}
if ($berat_badan < 45) {
    echo "<script>alert('❌ Berat badan kamu $berat_badan kg.\\nMinimal berat badan donor adalah 45 kg.'); history.back();</script>";
    exit;
}

$query = "INSERT INTO relawan
            (nama, email, no_hp, tgl_lahir, umur, jenis_kelamin, goldar,
             berat_badan, alamat, kota, pekerjaan, pernah_donor, terakhir_donor)
          VALUES
            ('$nama','$email','$no_hp','$tgl',$umur,'$jenis_kelamin','$goldar',
             $berat_badan,'$alamat','$kota','$pekerjaan','$pernah_donor',$terakhir_donor)";

$hasil = mysqli_query($conn, $query);

if ($hasil) {
    echo "<script>
        alert('✅ Terima kasih $nama!\\nPendaftaran relawan berhasil.\\nGolongan Darah: $goldar | Usia: $umur tahun');
        window.location = 'page2.php';
    </script>";
} else {
    echo "<script>
        alert('❌ Gagal menyimpan data: " . mysqli_error($conn) . "');
        history.back();
    </script>";
}

mysqli_close($conn);
?>
