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


