<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';

// Mendeteksi jenis aksi (tambah, edit, atau hapus)
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

if ($aksi == 'tambah') {
    // Menangkap input form
    $nama_siswa = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tgl_lahir  = $_POST['tgl_lahir'];
    $nama_ortu  = mysqli_real_escape_string($koneksi, $_POST['nama_ortu']);
    $no_hp_ortu = mysqli_real_escape_string($koneksi, $_POST['no_hp_ortu']);
    $id_kelas   = $_POST['id_kelas'];

    // Query simpan data ke tabel siswa
    $sql = "INSERT INTO siswa (nama_siswa, jenis_kelamin, tgl_lahir, nama_ortu, no_hp_ortu, id_kelas) 
            VALUES ('$nama_siswa', '$jenis_kelamin', '$tgl_lahir', '$nama_ortu', '$no_hp_ortu', '$id_kelas')";
    
    if (mysqli_query($koneksi, $sql)) {
        header("location:siswa.php?pesan=tambah_sukses");
    } else {
        echo "Gagal menambahkan data: " . mysqli_error($koneksi);
    }

} elseif ($aksi == 'edit') {
    // Menangkap input form edit beserta primary key ID
    $id_siswa   = $_POST['id_siswa'];
    $nama_siswa = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tgl_lahir  = $_POST['tgl_lahir'];
    $nama_ortu  = mysqli_real_escape_string($koneksi, $_POST['nama_ortu']);
    $no_hp_ortu = mysqli_real_escape_string($koneksi, $_POST['no_hp_ortu']);
    $id_kelas   = $_POST['id_kelas'];

    // Query update data berdasarkan ID siswa
    $sql = "UPDATE siswa SET 
                nama_siswa='$nama_siswa', 
                jenis_kelamin='$jenis_kelamin', 
                tgl_lahir='$tgl_lahir', 
                nama_ortu='$nama_ortu', 
                no_hp_ortu='$no_hp_ortu', 
                id_kelas='$id_kelas' 
            WHERE id_siswa='$id_siswa'";

    if (mysqli_query($koneksi, $sql)) {
        header("location:siswa.php?pesan=edit_sukses");
    } else {
        echo "Gagal memperbarui data: " . mysqli_error($koneksi);
    }

} elseif ($aksi == 'hapus') {
    // Menangkap parameter ID dari URL
    $id_siswa = $_GET['id'];

    // Query hapus data
    $sql = "DELETE FROM siswa WHERE id_siswa='$id_siswa'";

    if (mysqli_query($koneksi, $sql)) {
        header("location:siswa.php?pesan=hapus_sukses");
    } else {
        echo "Gagal menghapus data: " . mysqli_error($koneksi);
    }
} else {
    // Jika akses tidak sah tanpa parameter aksi
    header("location:siswa.php");
}
?>