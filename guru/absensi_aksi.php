<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Guru"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil ID Guru (sesuaikan dengan nama session saat login)
    $id_guru = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : (isset($_SESSION['id_guru']) ? $_SESSION['id_guru'] : 1);
    $tanggal = $_POST['tanggal'];
    
    // Menangkap array status dan keterangan
    $status_array     = $_POST['status']; // Key-nya adalah id_siswa
    $keterangan_array = $_POST['keterangan'];

    // Lakukan looping untuk memproses setiap siswa
    foreach ($status_array as $id_siswa => $status) {
        $keterangan = mysqli_real_escape_string($koneksi, $keterangan_array[$id_siswa]);
        
        // 1. Periksa apakah absensi siswa ini di tanggal tersebut sudah ada di database
        $cek = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_siswa='$id_siswa' AND tanggal='$tanggal'");
        
        if (mysqli_num_rows($cek) > 0) {
            // Jika sudah ada, lakukan update status kehadiran terbaru
            $sql_aksi = "UPDATE absensi SET status='$status', keterangan='$keterangan', id_guru='$id_guru' 
                         WHERE id_siswa='$id_siswa' AND tanggal='$tanggal'";
        } else {
            // Jika belum ada, buat record baru
            $sql_aksi = "INSERT INTO absensi (id_siswa, id_guru, tanggal, status, keterangan) 
                         VALUES ('$id_siswa', '$id_guru', '$tanggal', '$status', '$keterangan')";
        }
        
        // Jalankan eksekusi query
        mysqli_query($koneksi, $sql_aksi);
    }
    
    // Kembalikan ke halaman absen dengan pesan sukses
    header("location:absensi.php?tanggal=$tanggal&pesan=sukses");
    exit();
} else {
    header("location:absensi.php");
}
?>