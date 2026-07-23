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

// Panggil template header
include 'template/header.php';
?>

<h2 class="page-title">Dashboard Analisis</h2>

<div class="card-grid">
    <div class="card">
        <h3>Total Peserta Didik</h3>
        <p><?php echo $data_siswa['total']; ?> Siswa</p>
    </div>
    
    <div class="card card-green">
        <h3>Tenaga Pendidik (Guru)</h3>
        <p><?php echo $data_guru['total']; ?> Orang</p>
    </div>
    
    <div class="card card-yellow">
        <h3>Rombongan Belajar (Kelas)</h3>
        <p><?php echo $data_kelas['total']; ?> Kelas</p>
    </div>
</div>

<div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
    <h3>Selamat Datang di Panel Administrasi</h3>
    <p style="margin-top: 10px; color: #666; line-height: 1.6;">
        Sistem ini dirancang untuk mempermudah pengelolaan manajemen data master sekolah secara terpusat. 
        Anda dapat memantau data guru, melakukan pembaruan status kelas, serta memvalidasi pendaftaran siswa secara efisien. 
        Gunakan menu navigasi di bilah samping kiri untuk memulai pengolahan data.
    </p>
</div>

<?php 
// Panggil template footer
include 'template/footer.php'; 
?>