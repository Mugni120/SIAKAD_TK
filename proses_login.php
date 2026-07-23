<?php
session_start();
include 'config/koneksi.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password']; // Ambil password mentah
    $level    = mysqli_real_escape_string($koneksi, $_POST['level']);

    // 1. Cari user berdasarkan username dan level saja (jangan cek password di SQL)
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND level='$level'");
    $data  = mysqli_fetch_assoc($query);

    // 2. Jika user ditemukan, baru verifikasi passwordnya
    if($data && password_verify($password, $data['password'])){
        
        // Daftarkan session
        $_SESSION['username'] = $data['username'];
        $_SESSION['level']    = $level;

        // Jika GURU
        if($level == "Guru"){
            $_SESSION['id_guru'] = $data['id_guru'];
            
            // Ambil id_kelas
            $id_g = $data['id_guru'];
            $get_guru = mysqli_query($koneksi, "SELECT id_kelas FROM guru WHERE id_guru='$id_g'");
            $data_guru = mysqli_fetch_assoc($get_guru);
            $_SESSION['id_kelas'] = $data_guru ? $data_guru['id_kelas'] : 0;

            header("location:guru/dashboard.php");
            exit();
        } 
        // Jika ORANG TUA
        elseif($level == "Orang Tua"){
            $_SESSION['id_siswa'] = $data['id_siswa'];
            header("location:orang_tua/dashboard.php");
            exit();
        } 
        // Jika ADMIN
        elseif($level == "Admin"){
            header("location:admin/dashboard.php");
            exit();
        }
    } else {
        // Jika username tidak ada atau password salah
        header("location:login.php?pesan=gagal");
        exit();
    }
} else {
    header("location:login.php");
}
?>