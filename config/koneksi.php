<?php
// Konfigurasi Database
$host = "localhost";
$user = "root";      
$pass = "";           
$db   = "data_tk";

// Membuat koneksi
try {
    $koneksi = mysqli_connect($host, $user, $pass, $db);
} catch (mysqli_sql_exception $e) {
    die("Koneksi database gagal: " . $e->getMessage() . " Import database/data_tk.sql terlebih dahulu.");
}

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error() . " Import database/data_tk.sql terlebih dahulu.");
}

mysqli_set_charset($koneksi, 'utf8mb4');
?>