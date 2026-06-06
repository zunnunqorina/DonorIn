<?php
include '../../config/koneksi.php';

$stmt    = $pdo->query("SELECT * FROM stok_darah ORDER BY FIELD(goldar,'A','B','O','AB')");
$stok    = [];

foreach ($stmt->fetchAll() as $s) { $stok[$s['goldar']] = $s; }

foreach (['A','B','O','AB'] as $g) {
    if (!isset($stok[$g])) {
        $stok[$g] = ['goldar'=>$g, 'jumlah_kantong'=>0, 'updated_at'=>null, 'updated_by'=>'-'];
    }
}

$row = $pdo->query("SELECT updated_at FROM stok_darah ORDER BY updated_at DESC LIMIT 1")->fetch();
$last_update = $row ? date('d M Y, H:i', strtotime($row['updated_at'])) : 'Belum diperbarui';


function statusStok($jumlah) {
    if ($jumlah == 0)  return ['label'=>'Habis',    'warna'=>'#dc3545', 'bg'=>'#f8d7da', 'ikon'=>'🔴'];
    if ($jumlah <= 5)  return ['label'=>'Kritis',   'warna'=>'#e65100', 'bg'=>'#ffe0b2', 'ikon'=>'🟠'];
    if ($jumlah <= 15) return ['label'=>'Terbatas', 'warna'=>'#f39c12', 'bg'=>'#fff3cd', 'ikon'=>'🟡'];
    return                    ['label'=>'Tersedia', 'warna'=>'#198754', 'bg'=>'#d1e7dd', 'ikon'=>'🟢'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn — Stok Darah</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <style>
        .grid-stok { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:20px; margin-bottom:32px; }
        .kartu-stok { background:white; border-radius:14px; padding:28px 24px; box-shadow:0 2px 12px rgba(139,0,0,0.08); border:1px solid #f0e0e0; text-align:center; transition:transform .2s; }
        .kartu-stok:hover { transform:translateY(-3px); }
        .goldar-label { font-size:3rem; font-weight:900; color:#8b0000; line-height:1; margin-bottom:6px; }
        .jumlah-kantong { font-size:2rem; font-weight:800; color:#1a1a1a; line-height:1; }
        .satuan { font-size:0.85rem; color:#888; margin-bottom:12px; }
        .badge-status { display:inline-block; padding:5px 16px; border-radius:20px; font-size:0.82rem; font-weight:700; margin-bottom:12px; }
        .update-time { font-size:0.75rem; color:#aaa; }
        .bar-wrap { background:#f0f0f0; border-radius:20px; height:8px; margin:10px 0 14px; overflow:hidden; }
        .bar-fill { height:100%; border-radius:20px; }
        .tabel-ringkas { width:100%; border-collapse:collapse; background:white; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(139,0,0,0.07); }
        .tabel-ringkas thead th { background:#8b0000; color:white; padding:12px 16px; text-align:left; font-size:0.85rem; }
        .tabel-ringkas tbody td { padding:12px 16px; border-bottom:1px solid #f5f5f5; font-size:0.9rem; }
        .tabel-ringkas tbody tr:last-child td { border-bottom:none; }
        .tabel-ringkas tbody tr:hover { background:#fff9f9; }
        .info-update { background:#fff3f3; border-left:4px solid #8b0000; padding:10px 16px; border-radius:0 8px 8px 0; font-size:0.85rem; color:#555; margin-bottom:24px; }
        @media(max-width:600px){ .grid-stok{grid-template-columns:1fr 1fr;} }
    </style>
</head>
<body style="background:#f4f6f9;">

<?php include '../../components/header.php'; ?>

<main class="wadah" style="padding:40px 20px;">

    <h2 style="color:#8b0000; margin-bottom:5px;">🩸 Ketersediaan Stok Darah</h2>
    <p style="color:#888; margin-bottom:20px;">Data stok darah PMI yang diperbarui secara berkala.</p>

    <div class="info-update">
        🕐 Terakhir diperbarui: <strong><?php echo $last_update; ?></strong>
        &nbsp;&bull;&nbsp; Data bersumber dari Unit Donor Darah PMI
    </div>

    <!-- KARTU STOK -->
    <div class="grid-stok">
        <?php foreach (['A','B','O','AB'] as $g):
            $s      = $stok[$g];
            $jml    = (int)$s['jumlah_kantong'];
            $status = statusStok($jml);
            $persen = min(100, ($jml / 50) * 100);
            $tgl_upd = $s['updated_at'] ? date('d M Y', strtotime($s['updated_at'])) : '-';
        ?>
        <div class="kartu-stok">
            <div class="goldar-label"><?php echo $g; ?></div>
            <div style="font-size:0.75rem;color:#aaa;margin-bottom:10px;">Golongan Darah</div>
            <div class="jumlah-kantong"><?php echo $jml; ?></div>
            <div class="satuan">kantong tersedia</div>
            <div class="bar-wrap">
                <div class="bar-fill" style="width:<?php echo $persen; ?>%;background:<?php echo $status['warna']; ?>;"></div>
            </div>
            <div class="badge-status" style="background:<?php echo $status['bg']; ?>;color:<?php echo $status['warna']; ?>;">
                <?php echo $status['ikon']; ?> <?php echo $status['label']; ?>
            </div>
            <div class="update-time">Diperbarui: <?php echo $tgl_upd; ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- TABEL RINGKAS -->
    <h3 style="color:#8b0000;margin-bottom:14px;">📋 Ringkasan Stok</h3>
    <div style="overflow-x:auto;margin-bottom:32px;">
        <table class="tabel-ringkas">
            <thead><tr><th>Golongan</th><th>Jumlah Kantong</th><th>Status</th><th>Diperbarui</th><th>Petugas</th></tr></thead>
            <tbody>
                <?php foreach (['A','B','O','AB'] as $g):
                    $s = $stok[$g]; $jml = (int)$s['jumlah_kantong'];
                    $status = statusStok($jml);
                    $tgl_upd = $s['updated_at'] ? date('d M Y, H:i', strtotime($s['updated_at'])) : '-';
                ?>
                <tr>
                    <td style="font-weight:700;color:#8b0000;font-size:1.1rem;"><?php echo $g; ?></td>
                    <td style="font-weight:700;"><?php echo $jml; ?> kantong</td>
                    <td><span style="background:<?php echo $status['bg']; ?>;color:<?php echo $status['warna']; ?>;padding:3px 12px;border-radius:20px;font-size:0.8rem;font-weight:700;"><?php echo $status['ikon'].' '.$status['label']; ?></span></td>
                    <td style="font-size:0.85rem;color:#888;"><?php echo $tgl_upd; ?></td>
                    <td style="font-size:0.85rem;color:#888;"><?php echo htmlspecialchars($s['updated_by']??'-'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- KETERANGAN -->
    <div class="blok-konten" style="margin-bottom:28px;">
        <h3 style="color:#8b0000;margin-top:0;margin-bottom:14px;">📌 Keterangan Status</h3>
        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <span style="font-size:0.9rem;">🟢 <strong>Tersedia</strong> — Lebih dari 15 kantong</span>
            <span style="font-size:0.9rem;">🟡 <strong>Terbatas</strong> — 6–15 kantong</span>
            <span style="font-size:0.9rem;">🟠 <strong>Kritis</strong> — 1–5 kantong</span>
            <span style="font-size:0.9rem;">🔴 <strong>Habis</strong> — 0 kantong</span>
        </div>
    </div>

    <!-- CTA -->
    <div style="background:linear-gradient(135deg,#8b0000,#c0001a);border-radius:14px;padding:28px 32px;color:white;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div>
            <h3 style="margin:0 0 6px;font-size:1.1rem;">Butuh darah segera?</h3>
            <p style="margin:0;opacity:0.85;font-size:0.9rem;">Ajukan permintaan darah online dan kami bantu carikan pendonor.</p>
        </div>
        <a href="ajukan_permintaan.php" style="background:white;color:#8b0000;padding:12px 28px;border-radius:8px;font-weight:700;text-decoration:none;font-size:0.95rem;">
            📋 Ajukan Permintaan Darah
        </a>
    </div>

</main>

<?php include '../../components/footer.php'; ?>
<?php mysqli_close($conn); ?>
</body>
</html>
