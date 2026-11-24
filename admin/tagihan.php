<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'list';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan SPP - Admin SIAKAD TK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link href="../assets/css/style.css?v=4" rel="stylesheet">

    <style>
        /* Netralisir aturan .sidebar/body dari style.css agar grid Bootstrap berfungsi normal */
        body { display: block !important; }
        .sidebar {
            position: static !important;
            width: auto !important;
            height: auto !important;
            background-color: #0f172a !important;
        }
        .sidebar .nav-link { color: rgba(255,255,255,.85) !important; white-space: nowrap; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #ffffff !important; background-color: rgba(13,148,136,.25) !important; border-radius: 6px; }
    </style>
    <link href="../assets/css/cards.css?v=12" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-none d-md-block bg-dark sidebar min-vh-100">
                <div class="sidebar-sticky pt-3">
                    <h4 class="text-white text-center mb-4">SIAKAD TK</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="dashboard.php"> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="guru.php"> Data Guru
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="siswa.php"> Data Siswa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="kelas.php"> Data Kelas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="tagihan.php"> Tagihan SPP
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="clustering.php"> Clustering
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="qr_generator.php"> QR Generator
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="wa_logs.php"> WA Logs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="pengumuman.php"> Pengumuman
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="../logout.php"> Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-cash-stack"></i> Tagihan SPP</h2>
                    <?php if($page == 'list'): ?>
                    <a href="tagihan.php?page=tambah" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Buat Tagihan
                    </a>
                    <?php endif; ?>
                </div>

                <?php if(isset($_GET['pesan'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_GET['pesan']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if($page == 'tambah'): ?>
                    <!-- Form Buat Tagihan -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-plus-circle"></i> Buat Tagihan SPP
                        </div>
                        <div class="card-body">
                            <form action="tagihan_aksi.php?aksi=tambah" method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Siswa</label>
                                        <select name="id_siswa" class="form-select" required>
                                            <option value="">-- Pilih Siswa --</option>
                                            <?php
                                            $siswa = mysqli_query($koneksi, "SELECT s.id_siswa, s.nama_siswa, k.nama_kelas 
                                                FROM siswa s LEFT JOIN kelas k ON s.id_kelas = k.id_kelas 
                                                ORDER BY s.nama_siswa");
                                            while($s = mysqli_fetch_assoc($siswa)):
                                            ?>
                                            <option value="<?= $s['id_siswa'] ?>">
                                                <?= htmlspecialchars($s['nama_siswa']) ?> - <?= htmlspecialchars($s['nama_kelas']) ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Bulan</label>
                                        <select name="bulan" class="form-select" required>
                                            <?php 
                                            $bulan = ['Januari','Februari','Maret','April','Mei','Juni',
                                                      'Juli','Agustus','September','Oktober','November','Desember'];
                                            foreach($bulan as $b): 
                                            ?>
                                            <option value="<?= $b ?>"><?= $b ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Tahun</label>
                                        <select name="tahun" class="form-select" required>
                                            <?php for($y = date('Y'); $y >= date('Y')-2; $y--): ?>
                                            <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jumlah (Rp)</label>
                                    <input type="number" name="jumlah" class="form-control" placeholder="Contoh: 150000" required>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> Simpan
                                </button>
                                <a href="tagihan.php" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>

                <?php elseif($page == 'edit'): ?>
                    <!-- Form Edit Tagihan -->
                    <?php
                    $id = intval($_GET['id']);
                    $q = mysqli_query($koneksi, "SELECT * FROM tagihan WHERE id_tagihan = $id");
                    $d = mysqli_fetch_assoc($q);
                    if(!$d) { echo "<div class='alert alert-danger'>Tagihan tidak ditemukan</div>"; exit; }
                    ?>
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <i class="bi bi-pencil"></i> Edit Tagihan SPP
                        </div>
                        <div class="card-body">
                            <form action="tagihan_aksi.php?aksi=edit&id=<?= $id ?>" method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Siswa</label>
                                        <select name="id_siswa" class="form-select" required>
                                            <?php
                                            $siswa = mysqli_query($koneksi, "SELECT s.id_siswa, s.nama_siswa, k.nama_kelas 
                                                FROM siswa s LEFT JOIN kelas k ON s.id_kelas = k.id_kelas 
                                                ORDER BY s.nama_siswa");
                                            while($s = mysqli_fetch_assoc($siswa)):
                                                $selected = $s['id_siswa'] == $d['id_siswa'] ? 'selected' : '';
                                            ?>
                                            <option value="<?= $s['id_siswa'] ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($s['nama_siswa']) ?> - <?= htmlspecialchars($s['nama_kelas']) ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Bulan</label>
                                        <select name="bulan" class="form-select" required>
                                            <?php 
                                            $bulan = ['Januari','Februari','Maret','April','Mei','Juni',
                                                      'Juli','Agustus','September','Oktober','November','Desember'];
                                            foreach($bulan as $b): 
                                                $selected = $b == $d['bulan'] ? 'selected' : '';
                                            ?>
                                            <option value="<?= $b ?>" <?= $selected ?>><?= $b ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Tahun</label>
                                        <select name="tahun" class="form-select" required>
                                            <?php for($y = date('Y'); $y >= date('Y')-2; $y--): 
                                                $selected = $y == $d['tahun'] ? 'selected' : '';
                                            ?>
                                            <option value="<?= $y ?>" <?= $selected ?>><?= $y ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jumlah (Rp)</label>
                                    <input type="number" name="jumlah" class="form-control" value="<?= $d['jumlah'] ?>" required>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> Update
                                </button>
                                <a href="tagihan.php" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Daftar Tagihan -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Siswa</th>
                                            <th>Kelas</th>
                                            <th>Bulan</th>
                                            <th>Tahun</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $query = mysqli_query($koneksi, "SELECT t.*, s.nama_siswa, k.nama_kelas 
                                        FROM tagihan t 
                                        JOIN siswa s ON t.id_siswa = s.id_siswa 
                                        LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
                                        ORDER BY t.tahun DESC, t.bulan DESC");
                                    $no = 1;
                                    while($d = mysqli_fetch_assoc($query)):
                                        $status_class = $d['status'] == 'lunas' ? 'success' : 'warning';
                                        $status_text = $d['status'] == 'lunas' ? 'Lunas' : 'Belum Bayar';
                                    ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($d['nama_siswa']) ?></td>
                                            <td><?= htmlspecialchars($d['nama_kelas']) ?></td>
                                            <td><?= $d['bulan'] ?></td>
                                            <td><?= $d['tahun'] ?></td>
                                            <td>Rp <?= number_format($d['jumlah'], 0, ',', '.') ?></td>
                                            <td><span class="badge bg-<?= $status_class ?>"><?= $status_text ?></span></td>
                                            <td>
                                                <a href="tagihan.php?page=edit&id=<?= $d['id_tagihan'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="tagihan_aksi.php?aksi=hapus&id=<?= $d['id_tagihan'] ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Yakin hapus tagihan ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    <?php if(mysqli_num_rows($query) == 0): ?>
                                        <tr><td colspan="8" class="text-center">Belum ada tagihan</td></tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
