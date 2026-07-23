<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';
include 'template/header.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'list';
?>

<div class="table-header">
    <h2 class="page-title">Manajemen Data Rombel (Kelas)</h2>
    <?php if($page == 'list'): ?>
        <a href="kelas.php?page=tambah" class="btn btn-primary">+ Tambah Kelas</a>
    <?php else: ?>
        <a href="kelas.php" class="btn btn-secondary">⬅️ Kembali</a>
    <?php endif; ?>
</div>

<?php if($page == 'list'): ?>
    <div class="table-container" style="max-width: 700px;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID Kelas</th>
                    <th>Nama Kelas</th>
                    <th>Tahun Ajaran</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY id_kelas ASC");
                if(mysqli_num_rows($query) == 0){
                    echo "<tr><td colspan='4' style='text-align:center;'>Belum ada data kelas.</td></tr>";
                } else {
                    while($row = mysqli_fetch_assoc($query)){
                ?>
                <tr>
                    <td><code>#KL-0<?php echo $row['id_kelas']; ?></code></td>
                    <td><b><?php echo $row['nama_kelas']; ?></b></td>
                    <td><?php echo $row['tahun_ajaran']; ?></td>
                    <td style="text-align: center;">
                        <a href="kelas.php?page=edit&id=<?php echo $row['id_kelas']; ?>" class="btn btn-warning" style="padding: 4px 8px; font-size:12px;">✏️ Edit</a>
                        <a href="kelas_aksi.php?aksi=hapus&id=<?php echo $row['id_kelas']; ?>" class="btn btn-danger" style="padding: 4px 8px; font-size:12px;" onclick="return confirm('Hapus kelas ini?')">🗑️ Hapus</a>
                    </td>
                </tr>
                <?php 
                    }
                } 
                ?>
            </tbody>
        </table>
    </div>

<?php elseif($page == 'tambah'): ?>
    <div class="form-container">
        <h3>Buat Rombongan Belajar Baru</h3><br>
        <form action="kelas_aksi.php?aksi=tambah" method="POST">
            <div class="form-group-crud">
                <label>Nama Kelas</label>
                <input type="text" name="nama_kelas" required placeholder="Contoh: Kelas A-1 / Kelas B (Bulan)">
            </div>
            <div class="form-group-crud">
                <label>Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" required placeholder="Contoh: 2026/2027">
            </div>
            <button type="submit" class="btn btn-primary">Simpan Rombel</button>
        </form>
    </div>

<?php elseif($page == 'edit'): ?>
    <?php
    $id_kelas = $_GET['id'];
    $get_kelas = mysqli_query($koneksi, "SELECT * FROM kelas WHERE id_kelas='$id_kelas'");
    $data = mysqli_fetch_assoc($get_kelas);
    ?>
    <div class="form-container">
        <h3>Edit Detail Kelas</h3><br>
        <form action="kelas_aksi.php?aksi=edit" method="POST">
            <input type="hidden" name="id_kelas" value="<?php echo $data['id_kelas']; ?>">
            <div class="form-group-crud">
                <label>Nama Kelas</label>
                <input type="text" name="nama_kelas" required value="<?php echo $data['nama_kelas']; ?>">
            </div>
            <div class="form-group-crud">
                <label>Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" required value="<?php echo $data['tahun_ajaran']; ?>">
            </div>
            <button type="submit" class="btn btn-primary">Perbarui Kelas</button>
        </form>
    </div>
<?php endif; ?>

<?php include 'template/footer.php'; ?>