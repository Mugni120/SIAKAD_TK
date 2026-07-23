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
    <h2 class="page-title">Manajemen Data Master Guru</h2>
    <?php if($page == 'list'): ?>
        <a href="guru.php?page=tambah" class="btn btn-primary">+ Tambah Guru</a>
    <?php else: ?>
        <a href="guru.php" class="btn btn-secondary">⬅️ Kembali</a>
    <?php endif; ?>
</div>

<!-- ==================== ROUTING HALAMAN GURU ==================== -->

<?php if($page == 'list'): ?>
    <!-- READ DATA GURU -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Guru</th>
                    <th>NIP</th>
                    <th>Jabatan</th>
                    <th>No. Telepon</th>
                    <th>Email</th>
                    <th>Mengajar Kelas</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT guru.*, kelas.nama_kelas FROM guru 
                        LEFT JOIN kelas ON guru.id_kelas = kelas.id_kelas 
                        ORDER BY guru.id_guru DESC";
                $query = mysqli_query($koneksi, $sql);
                
                if(mysqli_num_rows($query) == 0){
                    echo "<tr><td colspan='7' style='text-align:center; color:#94a3b8;'>Belum ada data guru.</td></tr>";
                } else {
                    while($row = mysqli_fetch_assoc($query)){
                        $inisial = strtoupper(substr($row['nama_guru'], 0, 2));
                ?>
                <tr>
                    <td style="display: flex; align-items: center; border-bottom: none;">
                        <div class="avatar-circle" style="background-color: #e0f2fe; color: #0369a1;"><?php echo $inisial; ?></div>
                        <div><b><?php echo $row['nama_guru']; ?></b></div>
                    </td>
                    <td><?php echo $row['nip'] ? $row['nip'] : '-'; ?></td>
                    <td><?php echo $row['jabatan']; ?></td>
                    <td><?php echo $row['no_telepon']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['nama_kelas'] ? $row['nama_kelas'] : '<span style="color:#e74c3c;">Belum Memilih Kelas</span>'; ?></td>
                    <td style="text-align: center;">
                        <a href="guru.php?page=edit&id=<?php echo $row['id_guru']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size:12px;">✏️ Edit</a>
                        <a href="guru_aksi.php?aksi=hapus&id=<?php echo $row['id_guru']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size:12px;" onclick="return confirm('Hapus data guru ini?')">🗑️ Hapus</a>
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
    <!-- CREATE DATA GURU -->
    <div class="form-container">
        <h3>Formulir Tambah Guru Baru</h3><br>
        <form action="guru_aksi.php?aksi=tambah" method="POST">
            <div class="form-group-crud">
                <label>Nama Lengkap Guru</label>
                <input type="text" name="nama_guru" required placeholder="Nama Lengkap beserta Gelar">
            </div>
            <div class="form-group-crud">
                <label>NIP</label>
                <input type="text" name="nip" required placeholder="Masukkan NIP Resmi">
            </div>
            <!-- TAMBAHAN INPUT USERNAME DI SINI -->
            <div class="form-group-crud">
                <label>Username (Untuk Login Guru)</label>
                <input type="text" name="username" required placeholder="Contoh: dindariski">
            </div>
            <div class="form-group-crud">
                <label>Password Akun Guru</label>
                <input type="password" name="password" required placeholder="Kata Sandi Awal">
            </div>
            <div class="form-group-crud">
                <label>Jabatan</label>
                <input type="text" name="jabatan" value="Guru Kelas" placeholder="Contoh: Guru Utama / Wali Kelas">
            </div>
            <div class="form-group-crud">
                <label>No. Telepon / WA</label>
                <input type="text" name="no_telepon" required placeholder="Contoh: 0857xxxx">
            </div>
            <div class="form-group-crud">
                <label>Email</label>
                <input type="email" name="email" placeholder="alamat@email.com">
            </div>
            <div class="form-group-crud">
                <label>Kelas yang Diampu</label>
                <select name="id_kelas">
                    <option value="">-- Pilih Kelas --</option>
                    <?php
                    $get_kelas = mysqli_query($koneksi, "SELECT * FROM kelas");
                    while($k = mysqli_fetch_assoc($get_kelas)){
                        echo "<option value='".$k['id_kelas']."'>".$k['nama_kelas']."</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Data Guru</button>
        </form>
    </div>

<?php elseif($page == 'edit'): ?>
    <!-- UPDATE DATA GURU -->
    <?php
    $id_guru = $_GET['id'];
    $get_guru = mysqli_query($koneksi, "SELECT * FROM guru WHERE id_guru='$id_guru'");
    $data = mysqli_fetch_assoc($get_guru);
    ?>
    <div class="form-container">
        <h3>Modifikasi Data Guru</h3><br>
        <form action="guru_aksi.php?aksi=edit" method="POST">
            <input type="hidden" name="id_guru" value="<?php echo $data['id_guru']; ?>">
            
            <div class="form-group-crud">
                <label>Nama Lengkap Guru</label>
                <input type="text" name="nama_guru" required value="<?php echo $data['nama_guru']; ?>">
            </div>
            <div class="form-group-crud">
                <label>NIP</label>
                <input type="text" name="nip" required value="<?php echo $data['nip']; ?>">
            </div>
            <!-- Password baru (tanpa username karena ganti username biasanya jarang dilakukan) -->
            <div class="form-group-crud">
                <label>Password Baru (Kosongkan jika tidak ingin diubah)</label>
                <input type="password" name="password" placeholder="Masukkan password baru jika ingin diganti">
            </div>
            <div class="form-group-crud">
                <label>Jabatan</label>
                <input type="text" name="jabatan" value="<?php echo $data['jabatan']; ?>">
            </div>
            <div class="form-group-crud">
                <label>No. Telepon / WA</label>
                <input type="text" name="no_telepon" required value="<?php echo $data['no_telepon']; ?>">
            </div>
            <div class="form-group-crud">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo $data['email']; ?>">
            </div>
            <div class="form-group-crud">
                <label>Kelas yang Diampu</label>
                <select name="id_kelas">
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