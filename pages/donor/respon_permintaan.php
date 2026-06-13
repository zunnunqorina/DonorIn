<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['pendonor_login']) || $_SESSION['pendonor_login'] !== true) {
    header("Location: ../../login.php");
    exit;
}

$pendonor_id = $_SESSION['pendonor_id'];
$pm_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$pm_id) { header("Location: cari_permintaan.php"); exit; }

$q_pendonor = $conn->prepare("SELECT * FROM pendonor WHERE id = ?");
$q_pendonor->execute([$pendonor_id]);
$pendonor = $q_pendonor->fetch(PDO::FETCH_ASSOC);
$admin_username = $pendonor['nama'];

$st3 = $conn->prepare("SELECT COUNT(*) FROM notifikasi WHERE tujuan_tipe='pendonor' AND tujuan_id=? AND sudah_baca=0");
$st3->execute([$pendonor_id]);
$jml_notif_belum = $st3->fetchColumn();
$halaman_aktif = 'cari_permintaan';

// Ambil detail permintaan
$q = $conn->prepare("SELECT pd.*, p.nama AS nama_pasien, p.no_hp AS hp_pasien, p.email AS email_pasien
     FROM permintaan_darah pd JOIN pasien p ON pd.pasien_id=p.id WHERE pd.id=?");
$q->execute([$pm_id]);
if ($q->rowCount() == 0) { header("Location: cari_permintaan.php"); exit; }
$pm = $q->fetch(PDO::FETCH_ASSOC);

$pesan_sukses = '';
$pesan_error  = '';

if (isset($_POST['kirim_respon'])) {
    $status_respon = $_POST['status_respon'];
    $pesan_respon  = trim($_POST['pesan'] ?? '');

    $cek = $conn->prepare("SELECT id FROM respon_donor WHERE permintaan_id=? AND pendonor_id=?");
    $cek->execute([$pm_id, $pendonor_id]);
    if ($cek->rowCount() > 0) {
        $pesan_error = 'Anda sudah merespon permintaan ini sebelumnya.';
    } else {
        $insert = $conn->prepare("INSERT INTO respon_donor (permintaan_id, pendonor_id, pesan, status) VALUES (?, ?, ?, ?)");
        $insert->execute([$pm_id, $pendonor_id, $pesan_respon, $status_respon]);
        if ($insert->rowCount() > 0) {
            if ($status_respon == 'bersedia') {
                $upd = $conn->prepare("UPDATE permintaan_darah SET status='diproses' WHERE id=?");
                $upd->execute([$pm_id]);
            }
            $nama_pendonor   = htmlspecialchars($_SESSION['pendonor_nama'] ?? $admin_username);
            $goldar_pendonor = $_SESSION['pendonor_goldar'] ?? $pendonor['goldar'];
            $pesan_notif     = "Pendonor $nama_pendonor (Gol. $goldar_pendonor) menyatakan bersedia mendonorkan darah untuk permintaan Anda di {$pm['nama_rs']}. Segera hubungi mereka.";
            $notif = $conn->prepare("INSERT INTO notifikasi (tujuan_tipe, tujuan_id, judul, pesan) VALUES ('pasien', ?, 'Ada Pendonor Bersedia!', ?)");
            $notif->execute([$pm['pasien_id'], $pesan_notif]);
            $pesan_sukses = 'Respon Anda berhasil dikirim! Pasien akan mendapat notifikasi.';
        } else {
            $pesan_error = 'Gagal menyimpan respon.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respon Permintaan — DonorIn</title>
    <link rel="stylesheet" href="../../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .respon-layout { display: grid; grid-template-columns: 1fr 1.1fr; gap: 24px; }
        @media (max-width: 800px) { .respon-layout { grid-template-columns: 1fr; } }

        .detail-card {
            background: var(--card);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            border-left: 4px solid var(--merah);
            padding: 24px;
        }
        .detail-card-title {
            font-size: 1rem; font-weight: 700; color: var(--text);
            margin: 0 0 18px; padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .detail-card-title i { color: var(--merah); }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .detail-item .label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing:.5px; color: var(--text-muted); margin-bottom: 4px; }
        .detail-item .val   { font-size: 0.9rem; color: var(--text); font-weight: 500; }
        .goldar-big {
            font-size: 2rem; font-weight: 900; color: var(--merah);
            background: rgba(190,30,45,.08); border-radius: 8px;
            padding: 4px 14px; display: inline-block;
        }
        .keterangan-box {
            margin-top: 16px; padding: 12px 16px;
            background: rgba(190,30,45,.05); border-radius: 8px;
            font-size: 0.85rem; color: var(--text-muted); line-height: 1.6;
        }
        .btn-tel {
            display: inline-flex; align-items: center; gap: 6px;
            color: #1B8A4E; font-weight: 700; text-decoration: none;
            font-size: 0.9rem;
        }
        .btn-tel:hover { text-decoration: underline; }

        .form-card {
            background: var(--card);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 24px;
        }
        .form-card-title {
            font-size: 1rem; font-weight: 700; color: var(--text);
            margin: 0 0 20px; padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .form-card-title i { color: var(--merah); }
        .radio-group { display: flex; flex-direction: column; gap: 10px; margin-bottom: 18px; }
        .radio-option {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 16px; border-radius: 10px;
            border: 2px solid var(--border); cursor: pointer;
            transition: border-color .2s, background .2s;
            font-size: 0.9rem; color: var(--text);
        }
        .radio-option:has(input:checked) { border-color: var(--merah); background: rgba(190,30,45,.05); }
        .radio-option input[type="radio"] { accent-color: var(--merah); width: 16px; height: 16px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .5px; }
        .form-group textarea {
            width: 100%; border: 1px solid var(--border); border-radius: 8px;
            padding: 10px 12px; background: var(--bg); color: var(--text);
            font-size: 0.875rem; resize: vertical; box-sizing: border-box;
            font-family: inherit; transition: border-color .2s;
        }
        .form-group textarea:focus { outline: none; border-color: var(--merah); }
        .btn-kirim {
            width: 100%; height: 44px; border: none; border-radius: 10px;
            background: var(--merah); color: white;
            font-size: 0.9rem; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: opacity .2s;
        }
        .btn-kirim:hover { opacity: .88; }
    </style>
</head>
<body>

<?php if ($pesan_sukses): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({ icon:'success', title:'Berhasil!', text:'<?= addslashes($pesan_sukses) ?>', timer:3000, showConfirmButton:false })
        .then(() => { window.location.href = 'cari_permintaan.php'; });
});
</script>
<?php endif; ?>
<?php if ($pesan_error): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({ icon:'error', title:'Gagal!', text:'<?= addslashes($pesan_error) ?>' });
});
</script>
<?php endif; ?>

<!-- ══════════════ SIDEBAR ══════════════ -->
<?php include '../../components/sidebar_pendonor.php'; ?>

<!-- ══════════════ MAIN ══════════════ -->
<main class="main">

    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <div class="topbar-title">Respon Permintaan</div>
                <div class="topbar-breadcrumb">DonorIn / <a href="cari_permintaan.php" style="color:var(--merah);">Cari Permintaan</a> / <span>Beri Respon</span></div>
            </div>
        </div>
        <div class="topbar-right">
            <div class="date-chip">
                <i class="fas fa-calendar-day"></i>
                <?= date('d M Y') ?>
            </div>
        </div>
    </header>

    <div class="content">
        <div class="respon-layout">

            <!-- DETAIL PERMINTAAN -->
            <div class="detail-card">
                <div class="detail-card-title">
                    <i class="fas fa-file-medical"></i> Detail Permintaan Darah
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="label">Rumah Sakit</div>
                        <div class="val"><?= htmlspecialchars($pm['nama_rs']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="label">Kota</div>
                        <div class="val"><?= htmlspecialchars($pm['kota']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="label">Golongan Darah</div>
                        <div class="val"><span class="goldar-big"><?= htmlspecialchars($pm['goldar']) ?></span></div>
                    </div>
                    <div class="detail-item">
                        <div class="label">Jumlah Dibutuhkan</div>
                        <div class="val"><?= $pm['jumlah_kantong'] ?> kantong</div>
                    </div>
                    <div class="detail-item">
                        <div class="label">Nama Pasien</div>
                        <div class="val"><?= htmlspecialchars($pm['nama_pasien']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="label">Kontak Pasien</div>
                        <div class="val">
                            <a href="tel:<?= $pm['hp_pasien'] ?>" class="btn-tel">
                                <i class="fas fa-phone"></i> <?= htmlspecialchars($pm['hp_pasien']) ?>
                            </a>
                        </div>
                    </div>
                    <?php if ($pm['alamat_rs'] ?? ''): ?>
                    <div class="detail-item" style="grid-column: span 2;">
                        <div class="label">Alamat RS</div>
                        <div class="val"><?= htmlspecialchars($pm['alamat_rs']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($pm['keterangan'] ?? ''): ?>
                <div class="keterangan-box">
                    <i class="fas fa-info-circle" style="color:var(--merah);"></i>
                    <strong>Keterangan:</strong> <?= htmlspecialchars($pm['keterangan']) ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- FORM RESPON -->
            <div class="form-card">
                <div class="form-card-title">
                    <i class="fas fa-reply"></i> Kirim Respon Anda
                </div>
                <form method="POST" action="respon_permintaan.php?id=<?= $pm_id ?>">
                    <div class="form-group">
                        <label>Status Respon *</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="status_respon" value="bersedia" required checked>
                                <span>
                                    <span style="font-size:1.1rem;">✅</span>
                                    <strong> Saya Bersedia Donor</strong><br>
                                    <small style="color:var(--text-muted);">Saya siap datang dan mendonorkan darah</small>
                                </span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="status_respon" value="tidak_bisa">
                                <span>
                                    <span style="font-size:1.1rem;">❌</span>
                                    <strong> Tidak Bisa Saat Ini</strong><br>
                                    <small style="color:var(--text-muted);">Saya belum bisa donor saat ini</small>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Pesan / Keterangan <span style="font-weight:400;">(Opsional)</span></label>
                        <textarea name="pesan" rows="4" placeholder="Contoh: Saya bisa datang besok pagi ke RSUP NTB sekitar pukul 09.00..."></textarea>
                    </div>
                    <button type="submit" name="kirim_respon" class="btn-kirim">
                        <i class="fas fa-paper-plane"></i> Kirim Respon
                    </button>
                </form>
            </div>

        </div><!-- /respon-layout -->
    </div><!-- /content -->
</main>

<script src="../../assets/admin.js"></script>
<script>
document.getElementById('btnToggleSidebar').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.add('open');
});
document.getElementById('btnCloseSidebar').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.remove('open');
});
</script>
</body>
</html>
<?php $conn = null; ?>
