document.addEventListener("DOMContentLoaded", function () {
    // 1. Deteksi dan Ubah Notifikasi PHP ke SweetAlert2 Toast
    var notifElement = document.querySelector('.notif');
    if (notifElement) {
        var tipe = 'info';
        if (notifElement.classList.contains('notif-sukses')) {
            tipe = 'success';
        } else if (notifElement.classList.contains('notif-error')) {
            tipe = 'error';
        }

        // Bersihkan emoji/ikon dari text
        var teks = notifElement.innerText.replace(/[\u2700-\u27BF]|[\uE000-\uF8FF]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|[\u2011-\u26FF]|\uD83E[\uDD00-\uDFFF]/g, '').trim();

        Swal.fire({
            icon: tipe,
            title: teks,
            showConfirmButton: false,
            timer: 3500,
            toast: true,
            position: 'top-end',
            timerProgressBar: true,
            customClass: {
                popup: 'swal2-toast-custom'
            }
        });

        // Sembunyikan elemen notif HTML asli
        notifElement.style.display = 'none';
    }

    // 2. Intercept Tombol Logout Admin
    document.querySelectorAll('.btn-logout').forEach(function (btn) {
        btn.removeAttribute('onclick'); // Hapus confirm() bawaan
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var href = this.getAttribute('href');
            Swal.fire({
                title: 'Yakin ingin keluar?',
                text: "Anda akan keluar dari akun Anda.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Keluar!',
                cancelButtonText: 'Batal',
                background: '#ffffff',
                customClass: {
                    confirmButton: 'btn-swal-confirm',
                    cancelButton: 'btn-swal-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });
});

// 3. Fungsi Global Hapus dengan SweetAlert2 (dipanggil dari onclick tombol hapus)
function hapusDataSweet(id, nama, urlHapusParam) {
    Swal.fire({
        title: 'Hapus Data?',
        html: 'Apakah Anda yakin ingin menghapus <b>' + nama + '</b>?<br><span style="font-size: 13px; color: #DC2626;">Tindakan ini tidak dapat dibatalkan!</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = urlHapusParam + id;
        }
    });
}
