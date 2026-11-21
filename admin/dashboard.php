<?php
session_start();
// Proteksi halaman: pastikan hanya admin yang bisa masuk
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}

// Hubungkan koneksi database
include '../config/koneksi.php';

// Ambil jumlah data dari database untuk statistik ringkas dashboard
$query_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM siswa");
$data_siswa  = mysqli_fetch_assoc($query_siswa);

$query_guru  = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM guru");
$data_guru   = mysqli_fetch_assoc($query_guru);

$query_kelas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kelas");
$data_kelas  = mysqli_fetch_assoc($query_kelas);

// Statistik fitur baru
$query_tagihan = mysqli_query($koneksi, "SELECT COUNT(*) as total, 
    SUM(CASE WHEN status='belum_bayar' THEN 1 ELSE 0 END) as belum_bayar,
    SUM(CASE WHEN status='lunas' THEN 1 ELSE 0 END) as lunas
    FROM tagihan");
$data_tagihan = mysqli_fetch_assoc($query_tagihan);

$query_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi WHERE transaction_status='settlement'");
$data_transaksi = mysqli_fetch_assoc($query_transaksi);

$query_wa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM wa_logs");
$data_wa = mysqli_fetch_assoc($query_wa);

$query_qr = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM siswa_qr");
$data_qr = mysqli_fetch_assoc($query_qr);

// Panggil template header
include 'template/header.php';
?>

<h2 class="page-title">Dashboard Analisis</h2>

<div class="card-grid">
    <div class="card card-teal has-visual">
        <div class="card-visual">
            <span class="card-visual-icon"><i class="bi bi-people-fill"></i></span>
            <span class="card-visual-label">Data Master</span>
        </div>
        <div class="card-content">
            <span class="card-eyebrow">Total Peserta Didik</span>
            <p class="card-value"><?php echo $data_siswa['total']; ?> <small>Siswa</small></p>
            <p class="card-desc">Peserta didik aktif yang terdaftar di sekolah.</p>
            <a href="siswa.php" class="card-action">Lihat data <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

    <div class="card card-green has-visual">
        <div class="card-visual">
            <span class="card-visual-icon"><i class="bi bi-person-badge-fill"></i></span>
            <span class="card-visual-label">Tenaga Pendidik</span>
        </div>
        <div class="card-content">
            <span class="card-eyebrow">Guru</span>
            <p class="card-value"><?php echo $data_guru['total']; ?> <small>Orang</small></p>
            <p class="card-desc">Guru yang mendampingi anak didik setiap hari.</p>
            <a href="guru.php" class="card-action">Kelola guru <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

    <div class="card card-yellow has-visual">
        <div class="card-visual">
            <span class="card-visual-icon"><i class="bi bi-door-open-fill"></i></span>
            <span class="card-visual-label">Rombongan Belajar</span>
        </div>
        <div class="card-content">
            <span class="card-eyebrow">Kelas</span>
            <p class="card-value"><?php echo $data_kelas['total']; ?> <small>Kelas</small></p>
            <p class="card-desc">Kelas aktif yang tersedia pada tahun ajaran ini.</p>
            <a href="kelas.php" class="card-action">Lihat kelas <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>

<div class="card-grid">
    <div class="card card-blue has-visual">
        <div class="card-visual">
            <span class="card-visual-icon"><i class="bi bi-receipt-cutoff"></i></span>
            <span class="card-visual-label">Keuangan</span>
        </div>
        <div class="card-content">
            <span class="card-eyebrow">Tagihan SPP</span>
            <p class="card-value"><?php echo $data_tagihan['total']; ?> <small>Tagihan</small></p>
            <p class="card-desc"><?php echo $data_tagihan['lunas']; ?> lunas / <?php echo $data_tagihan['belum_bayar']; ?> belum bayar.</p>
            <a href="tagihan.php" class="card-action">Buka tagihan <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

    <div class="card card-purple has-visual">
        <div class="card-visual">
            <span class="card-visual-icon"><i class="bi bi-wallet2"></i></span>
            <span class="card-visual-label">Pembayaran</span>
        </div>
        <div class="card-content">
            <span class="card-eyebrow">Transaksi Berhasil</span>
            <p class="card-value"><?php echo $data_transaksi['total']; ?> <small>Transaksi</small></p>
            <p class="card-desc">Pembayaran SPP yang sudah berhasil diproses.</p>
            <a href="tagihan.php" class="card-action">Lihat transaksi <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

    <div class="card card-orange has-visual">
        <div class="card-visual">
            <span class="card-visual-icon"><i class="bi bi-whatsapp"></i></span>
            <span class="card-visual-label">Notifikasi</span>
        </div>
        <div class="card-content">
            <span class="card-eyebrow">WA Terkirim</span>
            <p class="card-value"><?php echo $data_wa['total']; ?> <small>Pesan</small></p>
            <p class="card-desc">Notifikasi absensi yang terkirim ke orang tua.</p>
            <a href="wa_logs.php" class="card-action">Lihat log <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

    <div class="card card-rose has-visual">
        <div class="card-visual">
            <span class="card-visual-icon"><i class="bi bi-qr-code-scan"></i></span>
            <span class="card-visual-label">Absensi</span>
        </div>
        <div class="card-content">
            <span class="card-eyebrow">QR Code Aktif</span>
            <p class="card-value"><?php echo $data_qr['total']; ?> <small>Kartu</small></p>
            <p class="card-desc">Kartu QR siswa siap dipakai untuk absensi.</p>
            <a href="qr_generator.php" class="card-action">Kelola QR <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>

<div class="card info-card">
    <div class="info-card-heading">
        <span class="info-icon"><i class="bi bi-stars"></i></span>
        <div>
            <h3>Selamat Datang di Panel Administrasi</h3>
            <p>
                Sistem ini dirancang untuk mempermudah pengelolaan manajemen data master sekolah secara terpusat.
                Anda dapat memantau data guru, melakukan pembaruan status kelas, serta memvalidasi pendaftaran siswa secara efisien.
                Gunakan menu navigasi di bilah samping kiri untuk memulai pengolahan data.
            </p>
        </div>
    </div>
</div>

<?php 
// Panggil template footer
include 'template/footer.php'; 
?>