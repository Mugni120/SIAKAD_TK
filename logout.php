<?php 
session_start();
// Hancurkan semua sesi terdaftar
session_destroy();
// Kembalikan ke halaman login dengan keterangan sukses keluar
header("location:login.php?pesan=logout");
exit();
?>