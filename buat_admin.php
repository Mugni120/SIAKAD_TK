<?php
include 'config/koneksi.php';

// Atur username dan password admin yang kamu inginkan di sini
$username = 'mugniAja';
$password_mentah = 'mugni123'; // Password mentah yang akan ditampilkan saat pembuatan akun admin
$level = 'Admin';

// Enkripsi password agar lolos verifikasi password_verify() saat login
$password_hashed = password_hash($password_mentah, PASSWORD_DEFAULT);

// Cek apakah username 'admin' sudah ada di database kamu
$cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND level='Admin'");

if (mysqli_num_rows($cek) > 0) {
    // Jika user admin sudah ada, kita update password-nya agar menggunakan versi hash
    $query_update = mysqli_query($koneksi, "UPDATE users SET password='$password_hashed' WHERE username='$username' AND level='Admin'");
    if ($query_update) {
        echo "<h3>Akun Admin lama BERHASIL diperbarui!</h3>";
        echo "Password baru kamu sekarang adalah: <b>$password_mentah</b> (sudah terenkripsi di database).";
    } else {
        echo "Gagal memperbarui akun admin: " . mysqli_error($koneksi);
    }
} else {
    // Jika belum ada akun admin sama sekali, kita buat baru
    $query_insert = mysqli_query($koneksi, "INSERT INTO users (username, password, level) VALUES ('$username', '$password_hashed', '$level')");
    if ($query_insert) {
        echo "<h3>Akun Admin baru BERHASIL dibuat!</h3>";
        echo "Username: <b>$username</b><br>Password: <b>$password_mentah</b>";
    } else {
        echo "Gagal membuat akun admin: " . mysqli_error($koneksi);
    }
}
?>

<!-- http://localhost/siakad_tk/buat_admin.php -->