/**
 * SIAKAD TK Al-Manaarussa'diyyah - Main Interface Script
 */

document.addEventListener("DOMContentLoaded", function () {

    // ==================== 1. FITUR TOGGLE SIDEBAR RESPONSIF ====================
    const toggleBtn = document.querySelector('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    if (toggleBtn && sidebar && mainContent) {
        toggleBtn.addEventListener('click', function () {
            // Toggle class 'collapsed' pada sidebar dan 'expanded' pada konten utama
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    }


    // ==================== 2. AUTO-DISMISS NOTIFIKASI PESAN ====================
    // Mencari text alert / pesan sukses yang dihasilkan oleh parameter URL (?pesan=...)
    const flashMessages = document.querySelectorAll('[style*="color: green"], [style*="color: green;"], .alert-success, .alert-danger');
    
    flashMessages.forEach(function (message) {
        // Beri jeda waktu 4 detik sebelum notifikasi menghilang secara halus
        setTimeout(function () {
            message.style.transition = "all 0.6s ease";
            message.style.opacity = "0";
            message.style.transform = "translateY(-10px)";
            
            // Hapus elemen sepenuhnya dari struktur HTML setelah animasi selesai
            setTimeout(function () {
                message.remove();
            }, 600);
        }, 4000);
    });


    // ==================== 3. VALIDASI FORM & KONFIRMASI GLOBAL ====================
    // Mencegah klik tidak sengaja pada tombol hapus yang tidak menggunakan inline 'onclick'
    const dynamicDeleteButtons = document.querySelectorAll('.btn-danger');
    
    dynamicDeleteButtons.forEach(function (button) {
        if (!button.hasAttribute('onclick')) {
            button.addEventListener('click', function (event) {
                const konfirmasi = confirm("Apakah Anda yakin ingin menghapus data ini secara permanen?");
                if (!konfirmasi) {
                    event.preventDefault(); // Batalkan aksi hapus jika memilih 'Cancel'
                }
            });
        }
    });

});