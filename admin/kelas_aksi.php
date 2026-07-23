<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

if ($aksi == 'tambah') {
    $nama_kelas   = mysqli_real_escape_string($koneksi, $_POST['nama_kelas']);
    $tahun_ajaran = mysqli_real_escape_string($koneksi, $_POST['tahun_ajaran']);

    $sql = "INSERT INTO kelas (nama_kelas, tahun_ajaran) VALUES ('$nama_kelas', '$tahun_ajaran')";
    if (mysqli_query($koneksi, $sql)) { header("location:kelas.php?pesan=tambah_sukses"); }

} elseif ($aksi == 'edit') {
    $id_kelas     = $_POST['id_kelas'];
    $nama_kelas   = mysqli_real_escape_string($koneksi, $_POST['nama_kelas']);
    $tahun_ajaran = mysqli_real_escape_string($koneksi, $_POST['tahun_ajaran']);

    $sql = "UPDATE kelas SET nama_kelas='$nama_kelas', tahun_ajaran='$tahun_ajaran' WHERE id_kelas='$id_kelas'";
    if (mysqli_query($koneksi, $sql)) { header("location:kelas.php?pesan=edit_sukses"); }

} elseif ($aksi == 'hapus') {
    $id_kelas = $_GET['id'];
    if (mysqli_query($koneksi, "DELETE FROM kelas WHERE id_kelas='$id_kelas'")) {
        header("location:kelas.php?pesan=hapus_sukses");
    }
}
?>