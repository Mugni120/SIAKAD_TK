<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';
include 'template/header.php';

// Menangkap parameter halaman (tambah / edit / list)
$page = isset($_GET['page']) ? $_GET['page'] : 'list';
?>

<div class="table-header">
    <h2 class="page-title">Manajemen Data Master Siswa</h2>
    <?php if($page == 'list'): ?>
        <a href="siswa.php?page=tambah" class="btn btn-primary">+ Tambah Siswa</a>
    <?php else: ?>
        <a href="siswa.php" class="btn btn-secondary">⬅️ Kembali ke Daftar</a>
    <?php endif; ?>
</div>

<?php if($page == 'list'): ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Siswa</th>
                    <th>Jenis kelamin</th>
                    <th>Tanggal Lahir</th>
                    <th>Nama Orang Tua / Wali</th>
                    <th>No. HP Orang Tua</th>
                    <th>Kelas</th>
                    <th>Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query Join untuk memuat nama kelas siswa
                $sql = "SELECT siswa.*, kelas.nama_kelas FROM siswa 
                        LEFT JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
                        ORDER BY siswa.id_siswa DESC";
                $query = mysqli_query($koneksi, $sql);
                
                if(mysqli_num_rows($query) == 0){
                    echo "<tr><td colspan='8' style='text-align:center; color:#94a3b8;'>Belum ada data siswa.</td></tr>";
                } else {
                    while($row = mysqli_fetch_assoc($query)){
                        // Membuat inisial nama lingkaran (dua huruf pertama)
                        $inisial = strtoupper(substr($row['nama_siswa'], 0, 2));
                ?>
                <tr>
                    <td style="display: flex; align-items: center; border-bottom: none;">
                        <div class="avatar-circle"><?php echo $inisial; ?></div>
                        <div>
                            <b><?php echo $row['nama_siswa']; ?></b>
                        </div>
                    </td>
                    <td><?php echo $row['jenis_kelamin']; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($row['tgl_lahir'])); ?></td>
                    <td><?php echo $row['nama_ortu']; ?></td>
                    <td><?php echo $row['no_hp_ortu']; ?></td>
                    <td><?php echo $row['nama_kelas'] ? $row['nama_kelas'] : '<span style="color:#94a3b8;">Belum Diplot</span>'; ?></td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td style="text-align: center;">
                        <a href="siswa.php?page=edit&id=<?php echo $row['id_siswa']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size:12px;">✏️ Edit</a>
                        <a href="siswa_aksi.php?aksi=hapus&id=<?php echo $row['id_siswa']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size:12px;" onclick="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?')">🗑️ Hapus</a>
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
        <h3>Formulir Tambah Siswa Baru</h3><br>
        <form action="siswa_aksi.php?aksi=tambah" method="POST">
            <div class="form-group-crud">
                <label>Nama Lengkap Siswa</label>
                <input type="text" name="nama_siswa" required placeholder="Contoh: Ahmad Rizky Pratama">
            </div>
            <div class="form-group-crud">
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" required>
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>
            <div class="form-group-crud">
                <label>Tanggal Lahir</label>
                <input type="date" name="tgl_lahir" required>
            </div>
            <div class="form-group-crud">
                <label>Nama Lengkap Orang Tua / Wali</label>
                <input type="text" name="nama_ortu" required placeholder="Contoh: Rizal Al-Ghifari">
            </div>
            <div class="form-group-crud">
                <label>No. HP / WhatsApp Orang Tua</label>
                <input type="text" name="no_hp_ortu" required placeholder="Contoh: 081234567xxx">
            </div>
            <div class="form-group-crud">
                <label>Plotting Rombongan Belajar (Kelas)</label>
                <select name="id_kelas" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php
                    $get_kelas = mysqli_query($koneksi, "SELECT * FROM kelas");
                    while($k = mysqli_fetch_assoc($get_kelas)){
                        echo "<option value='".$k['id_kelas']."'>".$k['nama_kelas']."</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Data Siswa</button>
        </form>
    </div>

<?php elseif($page == 'edit'): ?>
    <?php
    $id_siswa = $_GET['id'];
    $get_siswa = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_siswa='$id_siswa'");
    $data = mysqli_fetch_assoc($get_siswa);
    ?>
    <div class="form-container">
        <h3>Modifikasi Data Siswa</h3><br>
        <form action="siswa_aksi.php?aksi=edit" method="POST">
            <input type="hidden" name="id_siswa" value="<?php echo $data['id_siswa']; ?>">
            
            <div class="form-group-crud">
                <label>Nama Lengkap Siswa</label>
                <input type="text" name="nama_siswa" required value="<?php echo $data['nama_siswa']; ?>">
            </div>
            <div class="form-group-crud">
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" required>
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="Laki-laki" <?php echo ($data['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="Perempuan" <?php echo ($data['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>
            <div class="form-group-crud">
                <label>Tanggal Lahir</label>
                <input type="date" name="tgl_lahir" required value="<?php echo $data['tgl_lahir']; ?>">
            </div>
            <div class="form-group-crud">
                <label>Nama Lengkap Orang Tua / Wali</label>
                <input type="text" name="nama_ortu" required value="<?php echo $data['nama_ortu']; ?>">
            </div>
            <div class="form-group-crud">
                <label>No. HP / WhatsApp Orang Tua</label>
                <input type="text" name="no_hp_ortu" required value="<?php echo $data['no_hp_ortu']; ?>">
            </div>
            <div class="form-group-crud">
                <label>Plotting Rombongan Belajar (Kelas)</label>
                <select name="id_kelas" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php
                    $get_kelas = mysqli_query($koneksi, "SELECT * FROM kelas");
                    while($k = mysqli_fetch_assoc($get_kelas)){
                        $selected = ($k['id_kelas'] == $data['id_kelas']) ? 'selected' : '';
                        echo "<option value='".$k['id_kelas']."' $selected>".$k['nama_kelas']."</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Perbarui Data</button>
        </form>
    </div>
<?php endif; ?>

<?php include 'template/footer.php'; ?>