<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Guru"){
    header("location:../login.php?pesan=belum_login");
    exit();
}

include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data identitas siswa, guru, dan semester
    $id_siswa         = mysqli_real_escape_string($koneksi, $_POST['id_siswa']);
    $id_guru          = isset($_SESSION['id_guru']) ? $_SESSION['id_guru'] : 0;
    $semester         = mysqli_real_escape_string($koneksi, $_POST['semester']);
    
    // Menangkap data sesuai dengan atribut name baru yang dikirim dari form perkembangan.php
    $agama            = mysqli_real_escape_string($koneksi, $_POST['agama']);
    $motorik          = mysqli_real_escape_string($koneksi, $_POST['motorik']);
    $kognitif         = mysqli_real_escape_string($koneksi, $_POST['kognitif']);
    $bahasa           = mysqli_real_escape_string($koneksi, $_POST['bahasa']);
    $sosial_emosional = mysqli_real_escape_string($koneksi, $_POST['sosial_emosional']);
    $seni             = mysqli_real_escape_string($koneksi, $_POST['seni']);
    $catatan_guru     = mysqli_real_escape_string($koneksi, $_POST['catatan_guru']);

    // PERBAIKAN UTAMA: Mengubah pencarian ke tabel asli 'laporan_siswa'
    $cek = mysqli_query($koneksi, "SELECT id_laporan FROM laporan_siswa WHERE id_siswa='$id_siswa' AND semester='$semester'");

    if (mysqli_num_rows($cek) > 0) {
        // Proses UPDATE jika data rapor siswa tersebut pada semester terpilih sudah ada
        $query = "UPDATE laporan_siswa SET 
                    id_guru='$id_guru',
                    agama='$agama', 
                    motorik='$motorik', 
                    kognitif='$kognitif', 
                    bahasa='$bahasa', 
                    sosial_emosional='$sosial_emosional', 
                    seni='$seni', 
                    catatan_guru='$catatan_guru' 
                  WHERE id_siswa='$id_siswa' AND semester='$semester'";
    } else {
        // Proses INSERT jika data penilaian siswa baru pertama kali dimasukkan
        $query = "INSERT INTO laporan_siswa (id_siswa, id_guru, semester, kognitif, motorik, sosial_emosional, bahasa, seni, agama, catatan_guru) 
                  VALUES ('$id_siswa', '$id_guru', '$semester', '$kognitif', '$motorik', '$sosial_emosional', '$bahasa', '$seni', '$agama', '$catatan_guru')";
    }

    if (mysqli_query($koneksi, $query)) {
        // Jika berhasil, kembali ke halaman utama perkembangan siswa
        header("location:perkembangan.php?semester=" . $semester . "&pesan=sukses");
    } else {
        echo "Gagal menyimpan data ke tabel laporan_siswa: " . mysqli_error($koneksi);
    }
}
?>