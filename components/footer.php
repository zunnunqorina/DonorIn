<?php
// footer.php — DonorIn Global Footer
// Dipanggil di akhir setiap halaman publik dengan: include 'path/to/components/footer.php';
?>
<!-- ══ FOOTER ══ -->
<footer class="footer">
    <div class="footer-grid">
        <div>
            <div class="footer-brand">🩸 DonorIn</div>
            <p class="footer-desc">Sistem Informasi Donor Darah yang menghubungkan pendonor, pasien, dan PMI dalam satu platform terintegrasi.</p>
        </div>
        <div>
            <div class="footer-title">Layanan</div>
            <ul class="footer-links">
                <li><a href="<?= $prefix ?? '../../' ?>pages/donor/cari_pendonor.php">Cari Pendonor</a></li>
                <li><a href="<?= $prefix ?? '../../' ?>pages/donor/ajukan_permintaan.php">Ajukan Permintaan</a></li>
                <li><a href="<?= $prefix ?? '../../' ?>pages/donor/stok_darah.php">Stok Darah</a></li>
                <li><a href="<?= $prefix ?? '../../' ?>pages/donor/edukasi_donor.php">Edukasi Donor</a></li>
            </ul>
        </div>
        <div>
            <div class="footer-title">Akun</div>
            <ul class="footer-links">
                <li><a href="<?= $prefix ?? '../../' ?>auth/login_pendonor.php">Masuk</a></li>
                <li><a href="<?= $prefix ?? '../../' ?>pages/donor/daftar_pendonor.php">Daftar Pendonor</a></li>
                <li><a href="<?= $prefix ?? '../../' ?>pages/donor/kritik_saran.php">Kritik & Saran</a></li>
                <li><a href="<?= $prefix ?? '../../' ?>auth/login_admin.php">Admin</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bot">
        <span>&copy; <?= date('Y') ?> DonorIn — Setetes darah sangat berarti</span>
        <span>Sistem Informasi Donor Darah</span>
    </div>
</footer>

<script>
// Tab event toggle — dipakai di index.php
function gantiTab(panel, btn) {
    document.querySelectorAll('.panel-event').forEach(function(el) { el.classList.remove('aktif'); });
    document.querySelectorAll('.tab-event button').forEach(function(el) { el.classList.remove('aktif'); });
    document.getElementById('panel-' + panel).classList.add('aktif');
    btn.classList.add('aktif');
}
</script>
</body>
</html>