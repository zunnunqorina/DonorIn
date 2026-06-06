<?php
include 'config/koneksi.php';

if (!isset($_POST['kirim_relawan'])) {
    header("Location: page2.php");
    exit;
}

$nama          = trim($_POST['nama']);
$email         = trim($_POST['email']);
$no_hp         = trim($_POST['no_hp']);
$tgl           = $_POST['tgl'];
$jenis_kelamin = $_POST['jenis_kelamin'];
$goldar        = $_POST['goldar'];
$berat_badan   = (int) $_POST['berat_badan'];
$kota          = trim($_POST['kota']);
$pekerjaan     = trim($_POST['pekerjaan'] ?? '');
$alamat        = trim($_POST['alamat'] ?? '');
$pernah_donor  = $_POST['pernah_donor'];

$terakhir_donor = (!empty($_POST['terakhir_donor']) && $pernah_donor === 'ya')
                  ? $_POST['terakhir_donor']
                  : null;

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

$stmt = $conn->prepare("INSERT INTO relawan
            (nama, email, no_hp, tgl_lahir, umur, jenis_kelamin, goldar,
             berat_badan, alamat, kota, pekerjaan, pernah_donor, terakhir_donor)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$hasil = $stmt->execute([$nama, $email, $no_hp, $tgl, $umur, $jenis_kelamin, $goldar,
                          $berat_badan, $alamat, $kota, $pekerjaan, $pernah_donor, $terakhir_donor]);

if ($hasil) {
    echo "<script>
        alert('✅ Terima kasih $nama!\\nPendaftaran relawan berhasil.\\nGolongan Darah: $goldar | Usia: $umur tahun');
        window.location = 'page2.php';
    </script>";
} else {
    echo "<script>
        alert('❌ Gagal menyimpan data. Silakan coba lagi.');
        history.back();
    </script>";
}

$conn = null;
?>