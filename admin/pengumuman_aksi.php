<?php
session_start();
include '../config/koneksi.php';

// Proteksi halaman: Pastikan hanya Admin yang bisa mengeksekusi
if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'Admin') {
    header("location:../login.php");
    exit();
}

// 1. PROSES TAMBAH PENGUMUMAN
if (isset($_POST['aksi']) && $_POST['aksi'] == 'tambah') {
    $judul          = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $isi_pengumuman = mysqli_real_escape_string($koneksi, $_POST['isi_pengumuman']);
    $tanggal_buat   = date('Y-m-d'); 

    $query_tambah = mysqli_query($koneksi, "INSERT INTO pengumuman (judul, isi_pengumuman, tanggal_buat) 
                                            VALUES ('$judul', '$isi_pengumuman', '$tanggal_buat')");

    if ($query_tambah) {
        header("location:pengumuman.php?pesan=sukses_tambah");
    } else {
        header("location:pengumuman.php?pesan=gagal_tambah");
    }
    exit();
}

// 2. PROSES EDIT PENGUMUMAN (BARU)
if (isset($_POST['aksi']) && $_POST['aksi'] == 'edit') {
    $id_pengumuman  = mysqli_real_escape_string($koneksi, $_POST['id_pengumuman']);
    $judul          = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $isi_pengumuman = mysqli_real_escape_string($koneksi, $_POST['isi_pengumuman']);

    $query_edit = mysqli_query($koneksi, "UPDATE pengumuman SET judul='$judul', isi_pengumuman='$isi_pengumuman' WHERE id_pengumuman='$id_pengumuman'");

    if ($query_edit) {
        header("location:pengumuman.php?pesan=sukses_edit");
    } else {
        header("location:pengumuman.php?pesan=gagal_edit");
    }
    exit();
}

// 3. PROSES HAPUS PENGUMUMAN
if (isset($_GET['hapus'])) {
    $id_pengumuman = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    $query_hapus = mysqli_query($koneksi, "DELETE FROM pengumuman WHERE id_pengumuman = '$id_pengumuman'");

    if ($query_hapus) {
        header("location:pengumuman.php?pesan=sukses_hapus");
    } else {
        header("location:pengumuman.php?pesan=gagal_hapus");
    }
    exit();
}

header("location:pengumuman.php");
exit();
?>