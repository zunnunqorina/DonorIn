<?php
include 'config/koneksi.php';
include 'components/header.php';

// Data event
$ev_donor = $conn->query(
    "SELECT * FROM event_donor
     WHERE status = 'aktif' AND tanggal >= CURDATE()
     ORDER BY tanggal ASC LIMIT 3"
)->fetchAll(PDO::FETCH_ASSOC);

$ev_sosial = $conn->query(
    "SELECT * FROM event_sosialisasi
     WHERE status = 'aktif' AND tanggal >= CURDATE()
     ORDER BY tanggal ASC LIMIT 3"
)->fetchAll(PDO::FETCH_ASSOC);

// Statistik user
$jml_pendonor   = $conn->query("SELECT COUNT(*) FROM pendonor WHERE status_aktif='aktif'")->fetchColumn() ?? 0;
$jml_terselesai = $conn->query("SELECT COUNT(*) FROM permintaan_darah WHERE status='selesai'")->fetchColumn() ?? 0;
$jml_event      = $conn->query("SELECT COUNT(*) FROM event_donor WHERE status='aktif' AND tanggal >= CURDATE()")->fetchColumn() ?? 0;
?>

<!-- ══ User ══ -->
<section style="
    background: linear-gradient(135deg, #7a0000 0%, #8B0000 40%, #a50010 70%, #8B0000 100%);
    padding: 80px 24px 100px; position: relative; overflow: hidden;">
    <div style="content:'';position:absolute;inset:0;background:url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\");"></div>
    <div class="hero-grid" style="max-width:1160px;margin:0 auto;position:relative;display:grid;gap:40px;align-items:center;">
        <div>
            <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);border-radius:20px;padding:5px 14px;font-size:0.78rem;font-weight:600;color:rgba(255,255,255,0.9);margin-bottom:20px;">
                <i class="fas fa-tint" style="font-size:10px;"></i> Sistem Informasi Donor Darah
            </div>
            <h1 style="font-size:2.2rem;font-weight:900;color:white;line-height:1.2;letter-spacing:-0.5px;margin-bottom:18px;">
                Karena Setiap<br>Tetes Darah<br><span style="color:#FFCCCC;">Sangat Berarti</span>
            </h1>
            <p style="font-size:1rem;color:rgba(255,255,255,0.78);line-height:1.7;margin-bottom:32px;max-width:480px;">
                DonorIn menghubungkan pendonor dengan pasien yang membutuhkan darah secara cepat dan mudah.
                Bersama kita selamatkan lebih banyak nyawa.
            </p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <a href="pages/donor/ajukan_permintaan.php"
                   style="padding:13px 28px;background:white;color:#8B0000;border-radius:10px;font-size:0.9rem;font-weight:700;text-decoration:none;box-shadow:0 4px 16px rgba(0,0,0,0.15);transition:all .2s;">
                    <i class="fas fa-clipboard-list" style="margin-right:7px;"></i>Butuh Darah
                </a>
                <a href="pages/donor/cari_pendonor.php"
                   style="padding:13px 28px;background:rgba(255,255,255,0.12);color:white;border:1.5px solid rgba(255,255,255,0.3);border-radius:10px;font-size:0.9rem;font-weight:600;text-decoration:none;transition:all .2s;">
                    <i class="fas fa-search" style="margin-right:7px;"></i>Cari Pendonor
                </a>
            </div>
            <div class="hero-stats" style="display:flex;gap:20px;flex-wrap:wrap;margin-top:30px;padding-top:20px;border-top:1px solid rgba(255,255,255,0.15);">
                <div>
                    <div style="font-size:1.6rem;font-weight:900;color:white;line-height:1;"><?= number_format($jml_pendonor) ?>+</div>
                    <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);margin-top:4px;font-weight:500;">Pendonor Aktif</div>
                </div>
                <div>
                    <div style="font-size:1.6rem;font-weight:900;color:white;line-height:1;"><?= number_format($jml_terselesai) ?>+</div>
                    <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);margin-top:4px;font-weight:500;">Permintaan Terpenuhi</div>
                </div>
                <div>
                    <div style="font-size:1.6rem;font-weight:900;color:white;line-height:1;"><?= $jml_event ?></div>
                    <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);margin-top:4px;font-weight:500;">Event</div>
                </div>
            </div>
        </div>
        <!-- Visual cards -->
        <div style="display:flex;flex-direction:column;gap:14px;">
            <div style="background:rgba(255,255,255,0.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.15);border-radius:14px;padding:18px 20px;color:white;">
                <div style="font-size:0.78rem;font-weight:600;opacity:.7;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Stok Darah PMI</div>
                <div style="font-size:1.4rem;font-weight:800;">🩸 Cek Ketersediaan</div>
                <div style="font-size:0.8rem;opacity:.7;margin-top:3px;">Update real-time dari PMI</div>
            </div>
            <div class="hero-visual-row" style="display:flex;gap:10px;">
                <div style="flex:1;background:rgba(255,255,255,0.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.15);border-radius:14px;padding:18px 20px;color:white;">
                    <div style="font-size:0.78rem;font-weight:600;opacity:.7;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Donor Berikutnya</div>
                    <?php if (!empty($ev_donor)): ?>
                    <div style="font-size:1rem;font-weight:800;"><?= htmlspecialchars($ev_donor[0]['judul']) ?></div>
                    <div style="font-size:0.8rem;opacity:.7;margin-top:3px;"><?= date('d M Y', strtotime($ev_donor[0]['tanggal'])) ?></div>
                    <?php else: ?>
                    <div style="font-size:0.9rem;font-weight:800;">Belum ada event</div>
                    <?php endif; ?>
                </div>
                <div style="flex:1;background:rgba(255,255,255,0.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.15);border-radius:14px;padding:18px 20px;color:white;">
                    <div style="font-size:0.78rem;font-weight:600;opacity:.7;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Akun Saya</div>
                    <div style="font-size:0.95rem;font-weight:800;">
                        <?php if ($pendonor_login): ?>Dashboard →<?php else: ?>Login / Daftar<?php endif; ?>
                    </div>
                    <div style="font-size:0.8rem;opacity:.7;margin-top:3px;">Portal pendonor</div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.hero-grid {
    grid-template-columns: 1fr;
}
@media (min-width: 850px) {
    .hero-grid {
        grid-template-columns: 1.2fr 0.8fr;
    }
}
@media (max-width: 576px) {
    .hero-visual-row {
        flex-direction: column;
    }
    .hero-stats {
        justify-content: space-between;
        gap: 12px !important;
    }
    .hero-stats > div {
        flex: 1 1 auto;
        min-width: 80px;
    }
}
</style>

<!-- ══ LAYANAN ══ -->
<section class="section" id="layanan">
    <div class="wadah">
        <div class="section-head">
            <span class="section-label">Layanan Kami</span>
            <h2 class="section-judul">Semua yang Anda Butuhkan<br>Ada di DonorIn</h2>
            <p class="section-sub">Dari mencari pendonor, mengecek stok darah, hingga mengikuti event — semuanya dalam satu platform.</p>
        </div>
        <div class="grid-layanan">
            <a href="pages/donor/cari_pendonor.php" class="kartu-layanan">
                <div class="layanan-ikon" style="background:#FFF3F3;color:#8B0000;"><i class="fas fa-search"></i></div>
                <div class="layanan-judul">Cari Pendonor</div>
                <div class="layanan-desc">Temukan pendonor aktif berdasarkan golongan darah dan kota terdekat.</div>
                <div class="layanan-link">Cari sekarang <i class="fas fa-arrow-right" style="font-size:10px;"></i></div>
            </a>
            <a href="pages/donor/ajukan_permintaan.php" class="kartu-layanan">
                <div class="layanan-ikon" style="background:#EFF6FF;color:#1D4ED8;"><i class="fas fa-clipboard-list"></i></div>
                <div class="layanan-judul">Ajukan Permintaan</div>
                <div class="layanan-desc">Ajukan kebutuhan darah secara online. Pendonor cocok akan mendapat notifikasi.</div>
                <div class="layanan-link">Ajukan <i class="fas fa-arrow-right" style="font-size:10px;"></i></div>
            </a>
            <a href="pages/donor/stok_darah.php" class="kartu-layanan">
                <div class="layanan-ikon" style="background:#F0FFF6;color:#1B8A4E;"><i class="fas fa-tint"></i></div>
                <div class="layanan-judul">Cek Stok Darah</div>
                <div class="layanan-desc">Pantau ketersediaan stok darah PMI secara real-time per golongan darah.</div>
                <div class="layanan-link">Lihat stok <i class="fas fa-arrow-right" style="font-size:10px;"></i></div>
            </a>
            <a href="pages/admin/daftar_pendonor.php" class="kartu-layanan">
                <div class="layanan-ikon" style="background:#FFF8E6;color:#D4900A;"><i class="fas fa-user-plus"></i></div>
                <div class="layanan-judul">Daftar Pendonor</div>
                <div class="layanan-desc">Bergabung sebagai pendonor aktif dan bantu mereka yang membutuhkan darah.</div>
                <div class="layanan-link">Daftar <i class="fas fa-arrow-right" style="font-size:10px;"></i></div>
            </a>
            <a href="pages/donor/edukasi_donor.php" class="kartu-layanan">
                <div class="layanan-ikon" style="background:#F5F3FF;color:#7C3AED;"><i class="fas fa-book-open"></i></div>
                <div class="layanan-judul">Edukasi Donor</div>
                <div class="layanan-desc">Pelajari cara persiapan donor, jadwal ideal, dan kompatibilitas golongan darah.</div>
                <div class="layanan-link">Baca panduan <i class="fas fa-arrow-right" style="font-size:10px;"></i></div>
            </a>
            <a href="pages/donor/kritik_saran.php" class="kartu-layanan">
                <div class="layanan-ikon" style="background:#FFF0F9;color:#BE185D;"><i class="fas fa-comments"></i></div>
                <div class="layanan-judul">Kritik & Saran</div>
                <div class="layanan-desc">Sampaikan masukan Anda untuk membantu kami meningkatkan layanan DonorIn.</div>
                <div class="layanan-link">Kirim pesan <i class="fas fa-arrow-right" style="font-size:10px;"></i></div>
            </a>
        </div>
    </div>
</section>

<!-- ══ EVENT ══ -->
<section class="section section-bg" id="event">
    <div class="wadah">
        <div class="section-head" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;">
            <div>
                <span class="section-label">Jadwal Kegiatan</span>
                <h2 class="section-judul" style="margin-bottom:0;">Event Mendatang</h2>
            </div>
            <div class="tab-event">
                <button class="aktif" onclick="gantiTab('donor', this)">
                    <i class="fas fa-tint" style="font-size:11px;color:#8B0000;margin-right:5px;"></i>Donor Darah
                </button>
                <button onclick="gantiTab('sosial', this)">
                    <i class="fas fa-bullhorn" style="font-size:11px;color:#1B8A4E;margin-right:5px;"></i>Sosialisasi
                </button>
            </div>
        </div>

        <!-- Panel Event Donor -->
        <div id="panel-donor" class="panel-event aktif">
            <?php if (empty($ev_donor)): ?>
            <div class="kosong-event">
                <i class="fas fa-calendar-times"></i>
                <p>Belum ada event donor darah mendatang.</p>
            </div>
            <?php else: ?>
            <div class="grid-event">
                <?php foreach ($ev_donor as $ev): $ts = strtotime($ev['tanggal']); ?>
                <div class="kartu-event">
                    <div class="event-top">
                        <div class="event-tgl">
                            <div class="event-tgl-hari"><?= date('d', $ts) ?></div>
                            <div class="event-tgl-bln"><?= date('M', $ts) ?></div>
                            <div class="event-tgl-thn"><?= date('Y', $ts) ?></div>
                        </div>
                        <div class="event-body">
                            <div class="event-tipe">🩸 Donor Darah</div>
                            <div class="event-judul"><?= htmlspecialchars($ev['judul']) ?></div>
                            <div class="event-meta">
                                <span><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($ev['lokasi']) ?>, <?= htmlspecialchars($ev['kota']) ?></span>
                                <span><i class="fas fa-clock"></i><?= substr($ev['jam_mulai'],0,5) ?> – <?= substr($ev['jam_selesai'],0,5) ?> WITA</span>
                                <?php if (!empty($ev['penyelenggara'])): ?>
                                <span><i class="fas fa-building"></i><?= htmlspecialchars($ev['penyelenggara']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="event-bot">
                        <div class="event-kuota"><i class="fas fa-users" style="font-size:11px;"></i> Kuota: <?= $ev['kuota'] > 0 ? $ev['kuota'].' orang' : 'Tidak terbatas' ?></div>
                        <span class="event-badge merah">Terbuka</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Panel Event Sosialisasi -->
        <div id="panel-sosial" class="panel-event">
            <?php if (empty($ev_sosial)): ?>
            <div class="kosong-event">
                <i class="fas fa-bullhorn"></i>
                <p>Belum ada event sosialisasi mendatang.</p>
            </div>
            <?php else: ?>
            <div class="grid-event">
                <?php foreach ($ev_sosial as $ev): $ts = strtotime($ev['tanggal']); ?>
                <div class="kartu-event">
                    <div class="event-top">
                        <div class="event-tgl hijau">
                            <div class="event-tgl-hari"><?= date('d', $ts) ?></div>
                            <div class="event-tgl-bln"><?= date('M', $ts) ?></div>
                            <div class="event-tgl-thn"><?= date('Y', $ts) ?></div>
                        </div>
                        <div class="event-body">
                            <div class="event-tipe hijau">📢 Sosialisasi</div>
                            <div class="event-judul"><?= htmlspecialchars($ev['judul']) ?></div>
                            <div class="event-meta">
                                <span><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($ev['lokasi']) ?>, <?= htmlspecialchars($ev['kota']) ?></span>
                                <span><i class="fas fa-clock"></i><?= substr($ev['jam_mulai'],0,5) ?> – <?= substr($ev['jam_selesai'],0,5) ?> WITA</span>
                                <?php if (!empty($ev['pembicara'])): ?>
                                <span><i class="fas fa-user-tie"></i><?= htmlspecialchars($ev['pembicara']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="event-bot">
                        <div class="event-kuota"><i class="fas fa-users" style="font-size:11px;"></i> Target: <?= $ev['target_peserta'] > 0 ? $ev['target_peserta'].' orang' : 'Semua kalangan' ?></div>
                        <span class="event-badge">Terbuka</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ══ MANFAAT ══ -->
<section class="section" id="manfaat">
    <div class="wadah">
        <div class="section-head tengah">
            <span class="section-label">Manfaat</span>
            <h2 class="section-judul">Setetes Darah, Sejuta Manfaat</h2>
            <p class="section-sub">Donor darah bukan hanya menyelamatkan orang lain — tubuh Anda pun mendapat banyak keuntungan.</p>
        </div>
        <div class="grid-manfaat">
            <div class="kartu-manfaat"><div class="manfaat-ikon">🩺</div><div class="manfaat-judul">Deteksi Kesehatan Dini</div><div class="manfaat-desc">Sebelum donor, kesehatan Anda diperiksa gratis — kadar HB, tekanan darah, hingga golongan darah.</div></div>
            <div class="kartu-manfaat"><div class="manfaat-ikon">🔄</div><div class="manfaat-judul">Regenerasi Sel Darah</div><div class="manfaat-desc">Donor merangsang tubuh memproduksi sel darah merah baru yang lebih segar dan sehat.</div></div>
            <div class="kartu-manfaat"><div class="manfaat-ikon">💪</div><div class="manfaat-judul">Organ Lebih Sehat</div><div class="manfaat-desc">Suplai oksigen meningkat ke seluruh organ — hati, ginjal, dan paru-paru bekerja optimal.</div></div>
            <div class="kartu-manfaat"><div class="manfaat-ikon">⚖️</div><div class="manfaat-judul">Jaga Kadar Zat Besi</div><div class="manfaat-desc">Donor rutin menjaga keseimbangan zat besi dan mengurangi risiko penyakit jantung.</div></div>
            <div class="kartu-manfaat"><div class="manfaat-ikon">🌍</div><div class="manfaat-judul">Kepedulian Sosial</div><div class="manfaat-desc">Anda berkontribusi nyata pada sesama — satu kantong darah bisa menyelamatkan 3 nyawa.</div></div>
            <div class="kartu-manfaat"><div class="manfaat-ikon">🏆</div><div class="manfaat-judul">Amal Tanpa Batas</div><div class="manfaat-desc">Menyelamatkan nyawa manusia tanpa biaya — sebuah kebaikan yang terus mengalir.</div></div>
        </div>
    </div>
</section>

<!-- ══ SYARAT ══ -->
<section class="section section-bg" id="syarat">
    <div class="wadah">
        <div class="section-head tengah">
            <span class="section-label">Syarat & Ketentuan</span>
            <h2 class="section-judul">Sebelum Anda Donor Darah</h2>
            <p class="section-sub">Pastikan Anda memenuhi syarat berikut agar proses donor aman dan lancar.</p>
        </div>
        <div class="grid-syarat">
            <div class="kartu-syarat">
                <div class="syarat-head hijau"><i class="fas fa-check-circle"></i> Syarat Pendonor</div>
                <ul class="syarat-body">
                    <li>Usia 17–60 tahun (maks. 65 bagi donor rutin)</li>
                    <li>Berat badan minimal 45 kg</li>
                    <li>Hemoglobin (HB) lebih dari 12,5 gr/dL</li>
                    <li>Tekanan darah 100–160 / 60–100 mmHg</li>
                    <li>Tidak sedang haid, hamil, atau menyusui</li>
                    <li>Jarak donor minimal 2,5–3 bulan (5x/tahun)</li>
                </ul>
            </div>
            <div class="kartu-syarat">
                <div class="syarat-head merah"><i class="fas fa-times-circle"></i> Tidak Boleh Donor Jika:</div>
                <ul class="syarat-body">
                    <li>Minum alkohol 1 jam sebelum donor</li>
                    <li>Olahraga berat 1 hari sebelumnya</li>
                    <li>Dalam 12 bulan setelah transfusi darah</li>
                    <li>3 tahun setelah sembuh dari malaria</li>
                    <li>6 bulan setelah lepas dari obat-obatan</li>
                    <li>Menderita Hepatitis B/C, HIV, atau Syphilis</li>
                </ul>
            </div>
            <div class="kartu-syarat">
                <div class="syarat-head biru"><i class="fas fa-info-circle"></i> Proses Donor</div>
                <ul class="syarat-body">
                    <li>Pemeriksaan golongan darah & tekanan darah</li>
                    <li>Uji kecocokan dengan darah pasien</li>
                    <li>Proses berlangsung 7–15 menit</li>
                    <li>Volume darah 250cc atau 350cc</li>
                    <li>Istirahat sejenak setelah selesai</li>
                    <li>Konsumsi makanan bergizi setelah donor</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ══ CTA ══ -->
<section class="cta-section">
    <div class="cta-inner">
        <h2 class="cta-judul">"Setetes Darah Anda<br>Berarti Hidupku"</h2>
        <p class="cta-sub">Bergabunglah dengan ribuan pendonor aktif DonorIn. Satu langkah kecil Anda bisa menyelamatkan nyawa seseorang hari ini.</p>
        <div class="cta-btns">
            <a href="pages/admin/daftar_pendonor.php" class="cta-btn-putih">
                <i class="fas fa-user-plus" style="margin-right:7px;"></i>Daftar Sekarang
            </a>
            <a href="pages/donor/stok_darah.php" class="cta-btn-transparan">
                <i class="fas fa-tint" style="margin-right:7px;"></i>Cek Stok Darah
            </a>
        </div>
    </div>
</section>

<?php
$conn = null;
include 'components/footer.php';
?>