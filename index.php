<?php
include 'config/koneksi.php';
$halaman_aktif = 'home';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonorIn</title>
    <link rel="stylesheet" href="assets/styles.css">
    <script src="script.js" defer></script>
</head>
<body>

<?php include 'components/header.php'; ?>

<main>

    <section class="bagian-hero">
        <div class="wadah text-tengah">
            <div class="badge">AYO DONOR DARAH</div>
            <h1 class="judul-hero">Karena Setiap Tetes <br><span>Sangat Berarti</span></h1>
            <p class="deskripsi-hero">
                Selamat datang di <strong>DonorIn</strong>. Aplikasi ini membantu
                menghubungkan pasien yang membutuhkan darah dengan relawan pendonor secara cepat.
            </p>
            <div class="tombol-hero">
                <a href="pages/donor/page2.php" class="tombol-utama">PASIEN BUTUH DARAH</a>
                <a href="pages/donor/page2.php#daftar-relawan" class="tombol-sekunder">DAFTAR SEBAGAI RELAWAN</a>
            </div>
        </div>
    </section>

    <section class="bagian-apa-itu">
        <div class="wadah">
            <div class="apa-itu-layout">
                <div class="apa-itu-teks">
                    <span class="label-seksi">APA ITU DONOR DARAH?</span>
                    <h2>Memahami Donor Darah <br>Lebih Dalam</h2>
                    <p>Donor Darah adalah proses pemberian darah secara sukarela oleh seseorang kepada pasien
                       yang membutuhkan darah, dengan tujuan untuk penyembuhan penyakit dan pemulihan kesehatan.</p>

                    <div class="dua-jenis">
                        <div class="jenis-item">
                            <div class="jenis-ikon">❤️</div>
                            <div>
                                <strong>Donor Darah Sukarela</strong>
                                <p>Seseorang yang mendonorkan darahnya untuk keluarga, teman, atau orang yang
                                   tidak dikenal tanpa pamrih.</p>
                            </div>
                        </div>
                        <div class="jenis-item">
                            <div class="jenis-ikon">🤝</div>
                            <div>
                                <strong>Donor Darah Pengganti</strong>
                                <p>Orang yang mendonorkan darahnya untuk orang yang dikenal, seperti anggota
                                   keluarga yang membutuhkan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="syarat-ringkas">
                        <h4>⚠️ Siapa yang TIDAK boleh donor?</h4>
                        <ul>
                            <li>Penderita penyakit Jantung, Liver, Ginjal, dan Paru-paru</li>
                            <li>Pecandu Alkohol, Obat-obatan terlarang</li>
                            <li>Tekanan darah di luar rentang 100–160 / 60–100</li>
                            <li>Berat badan di bawah 45 kg</li>
                        </ul>
                    </div>
                </div>

                <div class="apa-itu-gambar">
                    <img src="https://images.unsplash.com/photo-1615461066841-6116e61058f4?w=500&q=80"
                         alt="Donor Darah" loading="lazy">
                    <div class="kartu-stat">
                        <div class="stat-item">
                            <span class="stat-angka">17</span>
                            <span class="stat-label">Usia Minimal (Tahun)</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-angka">60</span>
                            <span class="stat-label">Usia Maksimal (Tahun)</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-angka">45kg</span>
                            <span class="stat-label">Berat Badan Minimal</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bagian-manfaat">
        <div class="wadah">
            <div class="blok-teks text-tengah">
                <span class="label-seksi-putih">MANFAAT DONOR DARAH</span>
                <h2 style="color:white; margin-top:10px;">Setetes Darah Anda, <br>Sejuta Manfaat</h2>
                <p style="color:rgba(255,255,255,0.8); max-width:600px; margin:0 auto 40px;">
                    Selain menyelamatkan nyawa orang lain, donor darah secara rutin memberikan
                    banyak manfaat bagi tubuh Anda sendiri.
                </p>
            </div>
            <div class="grid-manfaat">
                <div class="manfaat-kartu">
                    <div class="manfaat-ikon">🩺</div>
                    <h3>Deteksi Kesehatan Dini</h3>
                    <p>Sebelum donor, kondisi kesehatan Anda diperiksa terlebih dahulu termasuk
                       kadar HB, tekanan darah, dan golongan darah — sehingga Anda mengetahui
                       kondisi kesehatan secara gratis.</p>
                </div>
                <div class="manfaat-kartu">
                    <div class="manfaat-ikon">🔄</div>
                    <h3>Produksi Sel Darah Baru</h3>
                    <p>Secara normal sel darah merah hanya bertahan 60–100 hari. Donor darah
                       merangsang tubuh memproduksi sel darah merah baru yang lebih segar dan sehat.</p>
                </div>
                <div class="manfaat-kartu">
                    <div class="manfaat-ikon">💪</div>
                    <h3>Meningkatkan Organ Tubuh</h3>
                    <p>Meningkatnya suplai oksigen ke seluruh organ membuat hati, usus, ginjal,
                       dan paru-paru bekerja lebih optimal dan bersih dari racun.</p>
                </div>
                <div class="manfaat-kartu">
                    <div class="manfaat-ikon">🌍</div>
                    <h3>Kepedulian Sosial</h3>
                    <p>Dengan mendonorkan darah, Anda meningkatkan rasa kepedulian sosial
                       terhadap sesama dan memberikan harapan hidup bagi mereka yang membutuhkan.</p>
                </div>
                <div class="manfaat-kartu">
                    <div class="manfaat-ikon">⚖️</div>
                    <h3>Jaga Kadar Zat Besi</h3>
                    <p>Donor darah rutin membantu menjaga keseimbangan kadar zat besi dalam
                       tubuh sehingga mengurangi risiko penyakit kardiovaskular.</p>
                </div>
                <div class="manfaat-kartu">
                    <div class="manfaat-ikon">🏆</div>
                    <h3>Amal Jariah Besar</h3>
                    <p>Donor darah adalah salah satu bentuk amal jariah yang tidak ternilai
                       harganya — menyelamatkan nyawa manusia tanpa biaya sepeser pun.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bagian-syarat">
        <div class="wadah">
            <div class="blok-teks text-tengah">
                <span class="label-seksi">SYARAT & KETENTUAN</span>
                <h2>Sebelum Anda Donor Darah</h2>
                <p>Pastikan Anda memenuhi syarat-syarat berikut agar proses donor berjalan aman dan lancar.</p>
            </div>
            <div class="syarat-grid">
                <div class="syarat-kartu syarat-boleh">
                    <h3>✅ Syarat Pendonor</h3>
                    <ul>
                        <li>Usia 17–60 tahun (maksimal 65 tahun bagi donor rutin)</li>
                        <li>Berat badan minimal 45 kg</li>
                        <li>Hemoglobin (HB) lebih dari 12,5 gr/dL</li>
                        <li>Tekanan darah: 100–160 (sistolik) / 60–100 (diastolik)</li>
                        <li>Tidak sedang haid, hamil, atau menyusui</li>
                        <li>Jarak penyumbangan minimal 2,5–3 bulan (5x/tahun)</li>
                    </ul>
                </div>
                <div class="syarat-kartu syarat-jangan">
                    <h3>❌ Tidak Boleh Donor Jika:</h3>
                    <ul>
                        <li>Jangan minum alkohol 1 jam sebelum donor</li>
                        <li>Jangan olahraga berat 1 hari sebelum donor</li>
                        <li>Tidak boleh donor sampai 12 bulan setelah transfusi</li>
                        <li>3 tahun setelah bebas penyakit malaria</li>
                        <li>6 bulan setelah sembuh dari obat-obatan</li>
                        <li>Menderita Hepatitis B/C, HIV, atau Syphilis</li>
                    </ul>
                </div>
                <div class="syarat-kartu syarat-proses">
                    <h3>ℹ️ Proses Donor</h3>
                    <ul>
                        <li>Pemeriksaan golongan darah & tekanan darah</li>
                        <li>Uji slang cocok serasi dengan darah pasien</li>
                        <li>Proses donor berlangsung 7–15 menit</li>
                        <li>Volume darah: 250cc atau 350cc (max 10.5cc/kg BB)</li>
                        <li>Istirahat sejenak setelah donor</li>
                        <li>Tekan bekas tusukan beberapa saat setelah selesai</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="bagian-galeri">
        <div class="wadah">
            <div class="blok-teks text-tengah">
                <h2>Kegiatan Donor Darah</h2>
                <p>Ribuan relawan telah berpartisipasi dalam kegiatan donor darah</p>
            </div>
            <div class="grid-galeri">
                <div class="galeri-item galeri-besar">
                    <img src="https://asset-2.tstatic.net/medan/foto/bank/images/Suasana-Kegiatan-Donor-Darah-Amal-I-PMVB-Tahun-2024-di-Vihara-Borobudur.jpg"
                         alt="Kegiatan Donor Darah" loading="lazy">
                    <div class="galeri-overlay"><p>Relawan mendonorkan darahnya dengan penuh semangat</p></div>
                </div>
                <div class="galeri-item">
                    <img src="https://cloud.jpnn.com/photo/jatim/news/normal/2023/06/08/kegiatan-donor-darah-yang-dilakukan-pt-karabha-digdaya-foto-sjgr.jpg"
                         alt="Pemeriksaan Darah" loading="lazy">
                    <div class="galeri-overlay"><p>Pemeriksaan darah sebelum donor</p></div>
                </div>
                <div class="galeri-item">
                    <img src="https://th.bing.com/th/id/OIP.QAMwcHdT18Fz9fm8tgrBXwHaE7?w=257&h=180&c=7&r=0&o=7&dpr=1.2&pid=1.7&rm=3"
                         alt="Kantong Darah" loading="lazy">
                    <div class="galeri-overlay"><p>Darah yang berhasil terkumpul</p></div>
                </div>
                <div class="galeri-item">
                    <img src="https://uns.ac.id/id/wp-content/uploads/dwp-uns-gandeng-pmi-kota-surakarta-selenggarakan-kegiatan-donor-darah-3-1024x682.jpeg"
                         alt="Tim Medis" loading="lazy">
                    <div class="galeri-overlay"><p>Tim medis profesional siap melayani</p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="bagian-cta">
        <div class="wadah text-tengah">
            <h2>"Setetes Darah Anda <span style="color:#ffcccc">Berarti Hidupku</span>"</h2>
            <p>Bergabunglah dengan ribuan relawan DonorIn dan jadilah pahlawan bagi mereka yang membutuhkan.</p>
            <div class="tombol-hero" style="margin-top:30px;">
                <a href="page2.php#daftar-relawan" class="tombol-utama"
                   style="background:white; color:#8b0000;">DAFTAR SEKARANG</a>
                <a href="page2.php" class="tombol-sekunder"
                   style="border-color:white; color:white; background:transparent;">CEK STOK DARAH</a>
            </div>
        </div>
    </section>

</main>

<?php include 'components/footer.php'; ?>
<?php $conn = null; ?>
</body>
</html>
