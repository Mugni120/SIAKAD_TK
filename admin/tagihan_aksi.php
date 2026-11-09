<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

if($aksi == 'tambah') {
    $id_siswa = intval($_POST['id_siswa']);
    $bulan = mysqli_real_escape_string($koneksi, $_POST['bulan']);
    $tahun = intval($_POST['tahun']);
    $jumlah = floatval($_POST['jumlah']);

    // Cek duplikat
    $cek = mysqli_query($koneksi, "SELECT id_tagihan FROM tagihan WHERE id_siswa='$id_siswa' AND bulan='$bulan' AND tahun='$tahun'");
    if(mysqli_num_rows($cek) > 0) {
        header("location:tagihan.php?pesan=Tagihan+untuk+siswa+ini+pada+bulan+tersebut+sudah+ada");
        exit();
    }

    $query = "INSERT INTO tagihan (id_siswa, bulan, tahun, jumlah) VALUES ('$id_siswa', '$bulan', '$tahun', '$jumlah')";
    if(mysqli_query($koneksi, $query)) {
        header("location:tagihan.php?pesan=Tagihan+berhasil+dibuat");
    } else {
        header("location:tagihan.php?pesan=Gagal+membuat+tagihan");
    }

} elseif($aksi == 'edit') {
    $id = intval($_GET['id']);
    $id_siswa = intval($_POST['id_siswa']);
    $bulan = mysqli_real_escape_string($koneksi, $_POST['bulan']);
    $tahun = intval($_POST['tahun']);
    $jumlah = floatval($_POST['jumlah']);

    $query = "UPDATE tagihan SET id_siswa='$id_siswa', bulan='$bulan', tahun='$tahun', jumlah='$jumlah' WHERE id_tagihan='$id'";
    if(mysqli_query($koneksi, $query)) {
        header("location:tagihan.php?pesan=Tagihan+berhasil+diupdate");
    } else {
        header("location:tagihan.php?pesan=Gagal+update+tagihan");
    }

} elseif($aksi == 'hapus') {
    $id = intval($_GET['id']);
    $query = "DELETE FROM tagihan WHERE id_tagihan='$id'";
    if(mysqli_query($koneksi, $query)) {
        header("location:tagihan.php?pesan=Tagihan+berhasil+dihapus");
    } else {
        header("location:tagihan.php?pesan=Gagal+hapus+tagihan");
    }
}
