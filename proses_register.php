<?php
include 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_raw = $_POST['password']; 
    $level    = mysqli_real_escape_string($koneksi, $_POST['level']);
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_pendaftar']);

    // Hash password agar aman saat disimpan ke database
    $password = password_hash($password_raw, PASSWORD_DEFAULT);

    // Cek ketersediaan username
    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        header("location:register.php?pesan=username_kembar");
        exit();
    }

    if ($level == "Guru") {
        $id_kelas = $_POST['id_kelas'];
        
        // Simpan data ke tabel guru terlebih dahulu
        $query_guru = mysqli_query($koneksi, "INSERT INTO guru (nama_guru, id_kelas) VALUES ('$nama', '$id_kelas')");
        
        if($query_guru){
            $id_guru = mysqli_insert_id($koneksi);
            // Hubungkan user baru dengan id_guru yang baru dibuat
            $sql = "INSERT INTO users (username, password, level, id_guru, id_siswa) VALUES ('$username', '$password', 'Guru', '$id_guru', NULL)";
        } else {
            header("location:register.php?pesan=gagal");
            exit();
        }
    } else {
        $id_siswa = mysqli_real_escape_string($koneksi, $_POST['id_siswa']);
        $no_hp_ortu = mysqli_real_escape_string($koneksi, $_POST['no_hp_ortu'] ?? '');

        // Cegah satu anak memiliki lebih dari satu akun orang tua
        $cek_anak = mysqli_query($koneksi, "SELECT id_user FROM users WHERE id_siswa = '$id_siswa' AND level = 'Orang Tua'");
        if (mysqli_num_rows($cek_anak) > 0) {
            header("location:register.php?pesan=anak_sudah_ada");
            exit();
        }

        // Simpan nama dan nomor WhatsApp orang tua ke data siswa
        mysqli_query($koneksi, "UPDATE siswa SET nama_ortu = '$nama', no_hp_ortu = '$no_hp_ortu' WHERE id_siswa = '$id_siswa'");

        // Hubungkan user baru dengan id_siswa yang dipilih
        $sql = "INSERT INTO users (username, password, level, id_guru, id_siswa) VALUES ('$username', '$password', 'Orang Tua', NULL, '$id_siswa')";
    }

    // Eksekusi penyimpanan ke tabel users
    if (mysqli_query($koneksi, $sql)) {
        header("location:login.php?pesan=berhasil");
    } else {
        header("location:register.php?pesan=gagal");
    }
}
?>