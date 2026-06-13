<?php
include '../../config/koneksi.php';

if (!isset($_SESSION['pendonor_login']) || $_SESSION['pendonor_login'] !== true) {
    header("Location: ../../login.php");
    exit;
}

$pendonor_id = $_SESSION['pendonor_id'];

$q_pendonor = $conn->prepare("SELECT * FROM pendonor WHERE id = ?");
$q_pendonor->execute([$pendonor_id]);
$pendonor = $q_pendonor->fetch(PDO::FETCH_ASSOC);
$admin_username = $pendonor['nama'];

$st3 = $conn->prepare("SELECT COUNT(*) FROM notifikasi WHERE tujuan_tipe='pendonor' AND tujuan_id=? AND sudah_baca=0");
$st3->execute([$pendonor_id]);
$jml_notif_belum = $st3->fetchColumn();
$halaman_aktif = 'edukasi_donor';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edukasi Donor Darah — DonorIn</title>
    <link rel="stylesheet" href="../../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .edu-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .edu-grid-full { grid-column: span 2; }
        @media (max-width: 720px) {
            .edu-grid { grid-template-columns: 1fr; }
            .edu-grid-full { grid-column: span 1; }
        }

        .edu-card {
            background: var(--card);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 24px;
            transition: box-shadow .2s, transform .2s;
        }
        .edu-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.09); transform: translateY(-2px); }
        .edu-card-header {
            display: flex; align-items: center; gap: 14px;
            margin-bottom: 18px; padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
        }
        .edu-card-icon {
            width: 46px; height: 46px; border-radius: 12px;
            background: linear-gradient(135deg, var(--merah), #a01020);
            color: white; font-size: 1.2rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .edu-card-icon.green { background: linear-gradient(135deg, #1B8A4E, #145f36); }
        .edu-card-icon.blue  { background: linear-gradient(135deg, #2563eb, #1e40af); }
        .edu-card-icon.amber { background: linear-gradient(135deg, #D4900A, #92600a); }
        .edu-card-title { font-size: 1rem; font-weight: 700; color: var(--text); margin: 0; }
        .edu-card-sub   { font-size: 0.75rem; color: var(--text-muted); margin: 2px 0 0; }

        .edu-list { list-style: none; padding: 0; margin: 0; }
        .edu-list li {
            padding: 9px 0; border-bottom: 1px solid var(--border);
            font-size: 0.86rem; color: var(--text-muted);
            display: flex; align-items: flex-start; gap: 10px; line-height: 1.6;
        }
        .edu-list li:last-child { border-bottom: none; }
        .edu-list li::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--merah); flex-shrink: 0; margin-top: 7px; }

        .edu-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .edu-table th { background: var(--bg); color: var(--text-muted); font-size: 0.72rem; text-transform: uppercase; letter-spacing: .5px; padding: 10px 14px; text-align: left; font-weight: 700; }
        .edu-table td { padding: 11px 14px; border-top: 1px solid var(--border); color: var(--text); }
        .edu-table tr:hover td { background: var(--bg); }
        .goldar-td { font-weight: 800; color: var(--merah); }

        .interval-item {
            display: flex; align-items: center; gap: 14px;
            padding: 10px 0; border-bottom: 1px solid var(--border);
            font-size: 0.86rem; color: var(--text-muted);
        }
        .interval-item:last-child { border-bottom: none; }
        .interval-dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: var(--merah); flex-shrink: 0;
        }
        .interval-label { flex: 1; color: var(--text); font-weight: 500; }
        .interval-val { font-weight: 700; color: var(--merah); }
    </style>
</head>
<body>

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
                <div class="topbar-title">Edukasi Donor</div>
                <div class="topbar-breadcrumb">DonorIn / <span>Edukasi Donor Darah</span></div>
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
        <div class="edu-grid">

            <!-- Interval Donor -->
            <div class="edu-card">
                <div class="edu-card-header">
                    <div class="edu-card-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="edu-card-title">Kapan Bisa Donor Lagi?</div>
                        <div class="edu-card-sub">Interval minimum antar donor</div>
                    </div>
                </div>
                <div class="interval-item">
                    <div class="interval-dot"></div>
                    <div class="interval-label">Whole Blood</div>
                    <div class="interval-val">2.5–3 bulan</div>
                </div>
                <div class="interval-item">
                    <div class="interval-dot" style="background:#1B8A4E;"></div>
                    <div class="interval-label">Plasma Darah</div>
                    <div class="interval-val" style="color:#1B8A4E;">2 minggu</div>
                </div>
                <div class="interval-item">
                    <div class="interval-dot" style="background:#2563eb;"></div>
                    <div class="interval-label">Trombosit</div>
                    <div class="interval-val" style="color:#2563eb;">2 minggu</div>
                </div>
                <div class="interval-item">
                    <div class="interval-dot" style="background:#D4900A;"></div>
                    <div class="interval-label">Sel Darah Merah</div>
                    <div class="interval-val" style="color:#D4900A;">3 bulan</div>
                </div>
                <div class="interval-item">
                    <div class="interval-dot"></div>
                    <div class="interval-label">Maksimal per tahun</div>
                    <div class="interval-val">5 kali</div>
                </div>
            </div>

            <!-- Persiapan -->
            <div class="edu-card">
                <div class="edu-card-header">
                    <div class="edu-card-icon green"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="edu-card-title">Persiapan Sebelum Donor</div>
                        <div class="edu-card-sub">Tips agar donor berjalan lancar</div>
                    </div>
                </div>
                <ul class="edu-list">
                    <li>Tidur cukup minimal 7 jam malam sebelumnya</li>
                    <li>Makan makanan bergizi 3 jam sebelum donor</li>
                    <li>Minum air putih yang cukup (minimal 8 gelas)</li>
                    <li>Hindari alkohol 24 jam sebelumnya</li>
                    <li>Hindari olahraga berat 24 jam sebelumnya</li>
                    <li>Bawa identitas (KTP/SIM) saat ke PMI</li>
                </ul>
            </div>

            <!-- Setelah Donor -->
            <div class="edu-card">
                <div class="edu-card-header">
                    <div class="edu-card-icon blue"><i class="fas fa-heart-pulse"></i></div>
                    <div>
                        <div class="edu-card-title">Setelah Donor Darah</div>
                        <div class="edu-card-sub">Pemulihan yang optimal</div>
                    </div>
                </div>
                <ul class="edu-list">
                    <li>Istirahat 10–15 menit di tempat donor</li>
                    <li>Minum air putih dan jus buah yang disediakan</li>
                    <li>Hindari aktivitas berat 24 jam setelah donor</li>
                    <li>Tekan bekas jarum selama 3–5 menit</li>
                    <li>Konsumsi makanan kaya zat besi (daging, sayuran hijau)</li>
                    <li>Jika pusing, segera duduk atau berbaring</li>
                </ul>
            </div>

            <!-- Komponen Darah -->
            <div class="edu-card">
                <div class="edu-card-header">
                    <div class="edu-card-icon amber"><i class="fas fa-flask"></i></div>
                    <div>
                        <div class="edu-card-title">Komponen Darah &amp; Manfaat</div>
                        <div class="edu-card-sub">Setiap komponen menyelamatkan nyawa</div>
                    </div>
                </div>
                <table class="edu-table">
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

            <!-- Kompatibilitas Golongan Darah (full width) -->
            <div class="edu-card edu-grid-full">
                <div class="edu-card-header">
                    <div class="edu-card-icon"><i class="fas fa-heart"></i></div>
                    <div>
                        <div class="edu-card-title">Kompatibilitas Golongan Darah</div>
                        <div class="edu-card-sub">Siapa bisa donor ke siapa?</div>
                    </div>
                </div>
                <table class="edu-table">
                    <thead>
                        <tr><th>Golongan Darah</th><th>Bisa Donor ke</th><th>Bisa Terima dari</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="goldar-td">A</td>
                            <td>A, AB</td>
                            <td>A, O</td>
                        </tr>
                        <tr>
                            <td class="goldar-td">B</td>
                            <td>B, AB</td>
                            <td>B, O</td>
                        </tr>
                        <tr>
                            <td class="goldar-td">AB</td>
                            <td>AB</td>
                            <td>A, B, AB, O <em style="color:var(--text-muted);font-size:.8rem;">(Universal Receiver)</em></td>
                        </tr>
                        <tr>
                            <td class="goldar-td">O</td>
                            <td>A, B, AB, O <em style="color:var(--text-muted);font-size:.8rem;">(Universal Donor)</em></td>
                            <td>O</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div><!-- /edu-grid -->
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