<?php
session_start();
// Proteksi halaman: pastikan hanya Guru yang bisa masuk
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Guru"){
    header("location:../login.php?pesan=belum_login");
    exit();
}

include '../config/koneksi.php';

// Ambil data kelas dan nama guru yang sedang login
$id_guru  = $_SESSION['id_guru'];
$id_kelas = $_SESSION['id_kelas'];

// Cari detail nama kelas
$query_kelas = mysqli_query($koneksi, "SELECT * FROM kelas WHERE id_kelas='$id_kelas'");
$data_kelas  = mysqli_fetch_assoc($query_kelas);
$nama_kelas  = $data_kelas ? $data_kelas['nama_kelas'] : 'Belum Ditentukan';

// Hitung jumlah siswa di kelas ini saja
$query_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM siswa WHERE id_kelas='$id_kelas'");
$data_siswa  = mysqli_fetch_assoc($query_siswa);

// BARU: Ambil daftar nama siswa yang ada di kelas ini untuk tabel aksi
$query_daftar_siswa = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_kelas='$id_kelas' ORDER BY nama_siswa ASC");

include 'template/header.php';
?>

<style>
    .table-container {
        background: white; 
        padding: 25px; 
        border-radius: 8px; 
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        margin-top: 20px;
    }
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-size: 14px;
    }
    .custom-table th {
        background-color: #f8f9fa;
        color: #475569;
        text-align: left;
        padding: 12px;
        border-bottom: 2px solid #edf2f7;
        font-weight: 600;
    }
    .custom-table td {
        padding: 12px;
        border-bottom: 1px solid #edf2f7;
        color: #334155;
    }
    .custom-table tr:hover {
        background-color: #f8fafc;
    }
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #0f5132;
        color: white;
        text-decoration: none;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        transition: background 0.2s;
    }
    .btn-action:hover {
        background: #157347;
    }
</style>

<h2 class="page-title">Selamat Datang, Guru Pengampu</h2>

<div class="card-grid">
    <div class="card" style="border-left-color: #8b5cf6;">
        <h3>Kelas Yang Diampu</h3>
        <p><?php echo $nama_kelas; ?></p>
    </div>
    
    <div class="card card-green">
        <h3>Jumlah Anak Didik</h3>
        <p><?php echo $data_siswa['total']; ?> Siswa</p>
    </div>
    
    <div class="card card-yellow">
        <h3>Tanggal Hari Ini</h3>
        <p style="font-size: 20px; margin-top: 5px;"><?php echo date('d F Y'); ?></p>
    </div>
</div>

<div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 20px;">
    <h3>Menu Cepat Pengelolaan Akademik</h3>
    <p style="margin-top: 10px; color: #666; line-height: 1.6;">
        Silakan lakukan pengisian <b>Absensi Harian</b> setiap hari sebelum kelas dimulai atau selesai. 
        Nilai capaian perkembangan siswa (6 aspek penilaian) dapat diangsur atau diisi menjelang akhir pengerjaan raport semester pada daftar tabel anak didik di bawah ini.
    </p>
</div>

<div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 20px;">
    <h3 style="margin-bottom: 15px; color: #1e293b; display: flex; align-items: center; gap: 8px;">
        <i class="bi bi-megaphone-fill" style="color: #f59e0b;"></i> Pengumuman Sekolah Terbaru
    </h3>
    <?php
    // Mengambil 3 pengumuman terbaru
    $query_pengumuman_guru = mysqli_query($koneksi, "SELECT * FROM pengumuman ORDER BY id_pengumuman DESC LIMIT 3");
    if (mysqli_num_rows($query_pengumuman_guru) > 0) {
        while ($p_guru = mysqli_fetch_assoc($query_pengumuman_guru)) {
    ?>
            <div style="border-bottom: 1px solid #edf2f7; padding: 15px 0; margin-bottom: 5px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <h4 style="margin: 0; color: #0f5132; font-size: 16px; font-weight: 700;"><?php echo htmlspecialchars($p_guru['judul']); ?></h4>
                    <span style="font-size: 12px; color: #64748b; background: #f1f5f9; padding: 4px 10px; border-radius: 12px;">
                        <i class="bi bi-calendar3"></i> <?php echo date('d M Y', strtotime($p_guru['tanggal_buat'])); ?>
                    </span>
                </div>
                <p style="margin: 0; color: #475569; font-size: 14px; line-height: 1.5;">
                    <?php echo nl2br(htmlspecialchars($p_guru['isi_pengumuman'])); ?>
                </p>
            </div>
    <?php
        }
    } else {
        echo "<p style='color: #94a3b8; font-size: 14px; text-align: center; padding: 10px 0;'>Belum ada informasi atau pengumuman dari admin sekolah.</p>";
    }
    ?>
</div>

<div class="table-container">
    <h3>Daftar Siswa Kelas <?php echo $nama_kelas; ?></h3>
    <p style="color: #64748b; font-size: 13px; margin-top: 4px;">Pilih salah satu siswa di bawah untuk mulai mengisi atau mengubah laporan penilaian perkembangan.</p>
    
    <table class="custom-table">
        <thead>
            <tr>
                <th style="width: 60px;">No</th>
                <th>Nama Lengkap Siswa</th>
                <th style="text-align: center; width: 200px;">Aksi Evaluasi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($query_daftar_siswa) > 0) {
                while($siswa = mysqli_fetch_assoc($query_daftar_siswa)) {
            ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><b><?php echo $siswa['nama_siswa']; ?></b></td>
                    <td style="text-align: center;">
                        <a href="perkembangan.php?id_siswa=<?php echo $siswa['id_siswa']; ?>&semester=Ganjil" class="btn-action">
                            <i class="bi bi-pencil-square"></i> Isi Perkembangan
                        </a>
                    </td>
                </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='3' style='text-align:center; color:#94a3b8; padding: 20px;'>Belum ada data siswa di kelas ini.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'template/footer.php'; ?>