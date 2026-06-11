<?php
include '../../config/koneksi.php';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Edukasi Donor Darah</title>
    <link rel="stylesheet" href="../../assets/styles.css">
</head>
<body style="background:#f4f4f4;">

<?php include '../../components/header.php'; ?>

<main class="wadah" style="padding:40px 20px;">
    <h2 style="color:#8b0000; margin-bottom:5px;">📖 Edukasi Donor Darah</h2>
    <p style="color:#888; margin-bottom:25px;"><a href="dashboard_pendonor.php" style="color:#8b0000;">← Dashboard</a></p>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

        <div class="blok-konten">
            <h3 style="color:#8b0000; margin-top:0;">⏱️ Kapan Bisa Donor Lagi?</h3>
            <p style="color:#555; line-height:1.8;">
                Donor darah lengkap (whole blood) dapat dilakukan setiap <strong>2,5–3 bulan</strong> sekali
                atau maksimal <strong>5 kali dalam setahun</strong>. Tubuh membutuhkan waktu ini untuk
                meregenerasi sel darah merah yang telah didonorkan.
            </p>
            <ul style="color:#555; line-height:2;">
                <li>Plasma darah: setiap 2 minggu</li>
                <li>Trombosit: setiap 2 minggu</li>
                <li>Sel darah merah: setiap 3 bulan</li>
                <li>Whole blood: setiap 2,5–3 bulan</li>
            </ul>
        </div>

        <div class="blok-konten">
            <h3 style="color:#8b0000; margin-top:0;">✅ Persiapan Sebelum Donor</h3>
            <ul style="color:#555; line-height:2;">
                <li>Tidur cukup minimal 7 jam malam sebelumnya</li>
                <li>Makan makanan bergizi 3 jam sebelum donor</li>
                <li>Minum air putih yang cukup (minimal 8 gelas)</li>
                <li>Hindari alkohol 24 jam sebelumnya</li>
                <li>Hindari olahraga berat 24 jam sebelumnya</li>
                <li>Bawa identitas (KTP/SIM) saat ke PMI</li>
            </ul>
        </div>

        <div class="blok-konten">
            <h3 style="color:#8b0000; margin-top:0;">💪 Setelah Donor Darah</h3>
            <ul style="color:#555; line-height:2;">
                <li>Istirahat 10–15 menit di tempat donor</li>
                <li>Minum air putih dan jus buah yang disediakan</li>
                <li>Hindari aktivitas berat 24 jam setelah donor</li>
                <li>Tekan bekas jarum selama 3–5 menit</li>
                <li>Konsumsi makanan kaya zat besi (daging, sayuran hijau)</li>
                <li>Jika pusing, segera duduk atau berbaring</li>
            </ul>
        </div>

        <div class="blok-konten">
            <h3 style="color:#8b0000; margin-top:0;">🏥 Komponen Darah & Manfaatnya</h3>
            <div style="overflow-x:auto;">
                <table class="tabel-data" style="margin-top:0;">
                    <thead>
                        <tr><th>Komponen</th><th>Digunakan Untuk</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Sel Darah Merah</td><td>Anemia, operasi besar</td></tr>
                        <tr><td>Trombosit</td><td>Kanker, leukemia, dengue</td></tr>
                        <tr><td>Plasma</td><td>Luka bakar, gangguan pembekuan</td></tr>
                        <tr><td>Whole Blood</td><td>Kecelakaan, perdarahan akut</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="blok-konten" style="grid-column:span 2;">
            <h3 style="color:#8b0000; margin-top:0;">❤️ Kompatibilitas Golongan Darah</h3>
            <div style="overflow-x:auto;">
                <table class="tabel-data" style="margin-top:0;">
                    <thead>
                        <tr><th>Golongan Darah</th><th>Bisa Donor ke</th><th>Bisa Terima dari</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight:bold; color:#8b0000; text-align:center;">A</td>
                            <td>A, AB</td>
                            <td>A, O</td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold; color:#8b0000; text-align:center;">B</td>
                            <td>B, AB</td>
                            <td>B, O</td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold; color:#8b0000; text-align:center;">AB</td>
                            <td>AB</td>
                            <td>A, B, AB, O (Universal Receiver)</td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold; color:#8b0000; text-align:center;">O</td>
                            <td>A, B, AB, O (Universal Donor)</td>
                            <td>O</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<?php include '../../components/footer.php'; ?>
<?php $conn = null; ?>
</body>
</html>