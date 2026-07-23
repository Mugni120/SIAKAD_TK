<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

if ($aksi == 'tambah') {
    $nama_guru  = mysqli_real_escape_string($koneksi, $_POST['nama_guru']);
    $nip        = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $jabatan    = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $no_telepon = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);
    $email      = mysqli_real_escape_string($koneksi, $_POST['email']);
    $id_kelas   = !empty($_POST['id_kelas']) ? $_POST['id_kelas'] : "NULL";

    // 1. Simpan data profil ke tabel GURU (tanpa menyertakan password)
    $sql_guru = "INSERT INTO guru (nama_guru, nip, jabatan, no_telepon, email, id_kelas) 
                 VALUES ('$nama_guru', '$nip', '$jabatan', '$no_telepon', '$email', $id_kelas)";
    
    if (mysqli_query($koneksi, $sql_guru)) {
        // 2. Ambil ID guru yang baru saja ditambahkan
        $id_guru_baru = mysqli_insert_id($koneksi);
        
        // 3. Simpan data autentikasi ke tabel USERS
        // Pastikan di form tambah ada input 'username'. Jika tidak ada, kamu bisa gunakan $nip sebagai username default.
        $username = mysqli_real_escape_string($koneksi, $_POST['username']); 
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $sql_users = "INSERT INTO users (username, password, level, id_guru) 
                      VALUES ('$username', '$password', 'Guru', '$id_guru_baru')";
        mysqli_query($koneksi, $sql_users);

        header("location:guru.php?pesan=tambah_sukses");
    } else { 
        echo "Gagal: " . mysqli_error($koneksi); 
    }

} elseif ($aksi == 'edit') {
    $id_guru    = mysqli_real_escape_string($koneksi, $_POST['id_guru']);
    $nama_guru  = mysqli_real_escape_string($koneksi, $_POST['nama_guru']);
    $nip        = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $jabatan    = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $no_telepon = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);
    $email      = mysqli_real_escape_string($koneksi, $_POST['email']);
    $id_kelas   = !empty($_POST['id_kelas']) ? $_POST['id_kelas'] : "NULL";

    // 1. Selalu perbarui data profil di tabel GURU
    $sql_update_guru = "UPDATE guru SET nama_guru='$nama_guru', nip='$nip', jabatan='$jabatan', no_telepon='$no_telepon', email='$email', id_kelas=$id_kelas WHERE id_guru='$id_guru'";
    $eksekusi_guru = mysqli_query($koneksi, $sql_update_guru);

    // 2. Cek apakah admin mengisi form password
    // Jika password diisi, enkripsi dan perbarui khusus di tabel USERS
    if(!empty($_POST['password'])) {
        $pass_baru = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql_update_pass = "UPDATE users SET password='$pass_baru' WHERE id_guru='$id_guru'";
        mysqli_query($koneksi, $sql_update_pass);
    }

    if ($eksekusi_guru) {
        header("location:guru.php?pesan=edit_sukses");
    } else { 
        echo "Gagal: " . mysqli_error($koneksi); 
    }

} elseif ($aksi == 'hapus') {
    $id_guru = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Hapus akun dari tabel users terlebih dahulu
    mysqli_query($koneksi, "DELETE FROM users WHERE id_guru='$id_guru'");
    
    // Baru kemudian hapus data profilnya dari tabel guru
    if (mysqli_query($koneksi, "DELETE FROM guru WHERE id_guru='$id_guru'")) {
        header("location:guru.php?pesan=hapus_sukses");
    }
}
?>