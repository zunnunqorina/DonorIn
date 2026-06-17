const formPendaftaran = document.querySelector('.form-pendaftaran');

if (formPendaftaran) {
    
    formPendaftaran.addEventListener('submit', function(event) {
        event.preventDefault(); 
        console.log("Form divalidasi dulu!");


        const nama = document.getElementById('full-name').value.trim();
        const email = document.getElementById('user-email').value.trim();
        const tanggalLahir = document.getElementById('birth-date').value;
        const golonganDarah = document.getElementById('blood-type').value;

        if (nama === '' || email === '' || tanggalLahir === '' || golonganDarah === '') {
            alert("❌ Semua kolom harus diisi!");
            return;
        }

        const umur = hitungUmur(tanggalLahir);

        if (umur < 17) {
            alert(`❌ Maaf ${nama},\nUsia kamu baru ${umur} tahun.\nMinimal usia untuk donor darah adalah 17 tahun.`);
            return;
        }

        alert(`✅ Terima kasih ${nama}!\n\nPendaftaran relawan berhasil.\nUsia: ${umur} tahun\nGolongan Darah: ${golonganDarah}`);

        formPendaftaran.reset();
    });
}


function hitungUmur(tanggalLahir) {
    const lahir = new Date(tanggalLahir);
    const hariIni = new Date();
    
    let umur = hariIni.getFullYear() - lahir.getFullYear();
    
    if (hariIni.getMonth() < lahir.getMonth() || 
       (hariIni.getMonth() === lahir.getMonth() && hariIni.getDate() < lahir.getDate())) {
        umur--;
    }
    
    return umur;
}

// ── Modal helpers ──
function bukaModal(id)  { document.getElementById(id).classList.add('show'); document.body.style.overflow='hidden'; }
function tutupModal(id) { document.getElementById(id).classList.remove('show'); document.body.style.overflow=''; }

document.querySelectorAll('.modal-overlay').forEach(function(el) {
    el.addEventListener('click', function(e) { if (e.target === el) tutupModal(el.id); });
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.show').forEach(function(el){ tutupModal(el.id); });
});

// Data reminder saat ini
var currentData = null;

// ── Buka modal detail ──
function bukaModalDetail(data) {
    currentData = data;
    document.getElementById('d_id').value            = data.id;
    document.getElementById('d_nama_pasien').textContent = data.nama_pasien || ('Pasien #' + data.pasien_id);
    document.getElementById('d_hp').textContent      = data.hp_pasien  || '—';
    document.getElementById('d_email').textContent   = data.email_pasien || '—';
    document.getElementById('d_goldar').textContent  = 'Golongan ' + data.goldar;
    document.getElementById('d_kantong').textContent = data.jumlah_kantong + ' kantong';
    document.getElementById('d_rs').textContent      = data.nama_rs   || '—';
    document.getElementById('d_kota').textContent    = data.kota      || '—';
    document.getElementById('d_alamat').textContent  = data.alamat_rs || '—';
    document.getElementById('d_ket').textContent     = data.keterangan || '—';
    document.getElementById('d_tgl').textContent     = new Date(data.tanggal).toLocaleDateString('id-ID', {day:'numeric',month:'long',year:'numeric'});

    var statusLabel = {
        'menunggu'  : '⏳ Menunggu',
        'diproses'  : '🔄 Diproses PMI',
        'terpenuhi' : '✅ Terpenuhi',
        'dibatalkan': '❌ Dibatalkan'
    };
    document.getElementById('d_status_badge').textContent = statusLabel[data.status] || data.status;
    document.getElementById('d_status_select').value      = data.status;

    bukaModal('modalDetail');
}

// ── Buka modal reminder dari tombol baris tabel ──
function bukaModalReminder(data) {
    currentData = data;
    isiReminder(data);
    bukaModal('modalReminder');
}

// ── Buka reminder dari dalam modal detail ──
function bukaReminderDariDetail() {
    if (!currentData) return;
    tutupModal('modalDetail');
    isiReminder(currentData);
    bukaModal('modalReminder');
}

// ── Isi konten reminder ──
function isiReminder(data) {
    var tgl = new Date(data.tanggal).toLocaleDateString('id-ID', {day:'numeric',month:'long',year:'numeric'});
    var nama_pasien = data.nama_pasien || ('Pasien #' + data.pasien_id);
    var pesan =
        '🩸 *PENGINGAT PERMINTAAN DONOR DARAH*\n' +
        'Dari: Admin DonorIn\n' +
        '─────────────────────\n' +
        '📋 *Detail Permintaan:*\n' +
        '• Pasien     : ' + nama_pasien + '\n' +
        '• Gol. Darah : ' + data.goldar + '\n' +
        '• Jumlah     : ' + data.jumlah_kantong + ' kantong\n' +
        '• RS/Klinik  : ' + data.nama_rs + '\n' +
        '• Kota       : ' + data.kota + '\n' +
        (data.alamat_rs ? '• Alamat     : ' + data.alamat_rs + '\n' : '') +
        (data.keterangan ? '• Ket.       : ' + data.keterangan + '\n' : '') +
        '• Tgl. Masuk : ' + tgl + '\n' +
        '• Status     : ' + data.status.toUpperCase() + '\n' +
        '─────────────────────\n' +
        '⚠️ Permintaan ini ' + (data.status === 'menunggu' ? 'BELUM diproses' : 'sedang diproses') + '.\n' +
        'Mohon segera ditindaklanjuti.\n\n' +
        'Terima kasih 🙏\n' +
        '— Admin DonorIn';

    document.getElementById('reminder_text').textContent = pesan;

    // Link WhatsApp (encode pesan)
    var waMsg = encodeURIComponent(pesan);
    document.getElementById('wa_link').href = 'https://wa.me/?text=' + waMsg;
}

// ── Salin pesan ──
function salinPesan() {
    var teks = document.getElementById('reminder_text').textContent;
    navigator.clipboard.writeText(teks).then(function() {
        var btn = event.target.closest('button');
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Tersalin!';
        btn.style.background = '#1B8A4E';
        setTimeout(function() { btn.innerHTML = orig; btn.style.background = ''; }, 2000);
    });
}

// ── Konfirmasi hapus ──
function konfirmasiHapus(id, nama) {
    document.getElementById('hapus_nama').textContent = nama;
    document.getElementById('hapus_link').href = 'permintaan_darah_admin.php?hapus=' + id;
    bukaModal('modalHapus');
}

// ── Auto hilangkan notifikasi ──
setTimeout(function() {
    var notif = document.querySelector('.notif');
    if (notif) {
        notif.style.opacity = '0';
        notif.style.transform = 'translateY(-8px)';
        notif.style.transition = 'all .4s ease';
        setTimeout(function(){ notif.remove(); }, 400);
    }
}, 4000);
