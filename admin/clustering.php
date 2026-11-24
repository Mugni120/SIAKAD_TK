<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';

// Ambil data laporan untuk clustering
$semester = isset($_GET['semester']) ? mysqli_real_escape_string($koneksi, $_GET['semester']) : 'Ganjil';

// Konversi nilai BB/MB/BSH/BSB ke skor numerik
$skor_map = ['BB' => 1, 'MB' => 2, 'BSH' => 3, 'BSB' => 4];

$q = mysqli_query($koneksi, "SELECT l.*, s.nama_siswa, k.nama_kelas 
    FROM laporan_siswa l
    JOIN siswa s ON l.id_siswa = s.id_siswa
    LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
    WHERE l.semester = '$semester'");

$data = [];
while($d = mysqli_fetch_assoc($q)) {
    $data[] = [
        'id_siswa' => $d['id_siswa'],
        'nama' => $d['nama_siswa'],
        'kelas' => $d['nama_kelas'],
        'kognitif' => $skor_map[$d['kognitif']] ?? 0,
        'motorik' => $skor_map[$d['motorik']] ?? 0,
        'sosial_emosional' => $skor_map[$d['sosial_emosional']] ?? 0,
        'bahasa' => $skor_map[$d['bahasa']] ?? 0,
        'seni' => $skor_map[$d['seni']] ?? 0,
        'agama' => $skor_map[$d['agama']] ?? 0,
    ];
}

// ===== ALGORITMA K-MEANS =====
function kmeans($data, $k = 3, $max_iter = 100) {
    $n = count($data);
    if($n == 0) return [];
    
    $dims = ['kognitif', 'motorik', 'sosial_emosional', 'bahasa', 'seni', 'agama'];
    
    // Inisialisasi centroid secara acak
    $centroids = [];
    $indices = array_rand($data, min($k, $n));
    if(!is_array($indices)) $indices = [$indices];
    foreach($indices as $i) {
        $centroid = [];
        foreach($dims as $d) {
            $centroid[$d] = $data[$i][$d];
        }
        $centroids[] = $centroid;
    }
    
    $assignments = array_fill(0, $n, -1);
    
    for($iter = 0; $iter < $max_iter; $iter++) {
        $changed = false;
        
        // Assign setiap data ke centroid terdekat
        for($i = 0; $i < $n; $i++) {
            $min_dist = PHP_FLOAT_MAX;
            $best_cluster = 0;
            for($c = 0; $c < $k; $c++) {
                $dist = 0;
                foreach($dims as $d) {
                    $dist += pow($data[$i][$d] - $centroids[$c][$d], 2);
                }
                if($dist < $min_dist) {
                    $min_dist = $dist;
                    $best_cluster = $c;
                }
            }
            if($assignments[$i] != $best_cluster) {
                $assignments[$i] = $best_cluster;
                $changed = true;
            }
        }
        
        if(!$changed) break;
        
        // Update centroid
        $new_centroids = array_fill(0, $k, array_fill_keys($dims, 0));
        $counts = array_fill(0, $k, 0);
        for($i = 0; $i < $n; $i++) {
            $c = $assignments[$i];
            $counts[$c]++;
            foreach($dims as $d) {
                $new_centroids[$c][$d] += $data[$i][$d];
            }
        }
        for($c = 0; $c < $k; $c++) {
            if($counts[$c] > 0) {
                foreach($dims as $d) {
                    $centroids[$c][$d] = $new_centroids[$c][$d] / $counts[$c];
                }
            }
        }
    }
    
    return ['assignments' => $assignments, 'centroids' => $centroids];
}

// Jalankan K-Means
$k = 3; // Jumlah cluster
$result = kmeans($data, $k);

// Label cluster berdasarkan karakteristik
$cluster_labels = [];
$cluster_counts = array_fill(0, $k, 0);
foreach($result['assignments'] as $c) {
    $cluster_counts[$c]++;
}

// Tentukan label berdasarkan skor rata-rata
$cluster_avg = [];
for($c = 0; $c < $k; $c++) {
    $avg = 0;
    $count = 0;
    for($i = 0; $i < count($data); $i++) {
        if($result['assignments'][$i] == $c) {
            $avg += array_sum([$data[$i]['kognitif'], $data[$i]['motorik'], $data[$i]['sosial_emosional'], 
                              $data[$i]['bahasa'], $data[$i]['seni'], $data[$i]['agama']]) / 6;
            $count++;
        }
    }
    $cluster_avg[$c] = $count > 0 ? $avg / $count : 0;
}

// Urutkan cluster berdasarkan rata-rata (tinggi ke rendah)
arsort($cluster_avg);
$rank = 0;
$cluster_names = [];
$cluster_colors = [];
$cluster_icons = [];
foreach($cluster_avg as $c => $avg) {
    $rank++;
    if($rank == 1) {
        $cluster_names[$c] = "Cluster A - Unggul";
        $cluster_colors[$c] = "success";
        $cluster_icons[$c] = "star";
    } elseif($rank == 2) {
        $cluster_names[$c] = "Cluster B - Berkembang";
        $cluster_colors[$c] = "warning";
        $cluster_icons[$c] = "arrow-up-circle";
    } else {
        $cluster_names[$c] = "Cluster C - Perlu Perhatian";
        $cluster_colors[$c] = "danger";
        $cluster_icons[$c] = "exclamation-triangle";
    }
}

// Urutan tampil: Unggul, Berkembang, Perlu Perhatian
$cluster_order = array_keys($cluster_avg);

// Simpan hasil ke database
if(!empty($data)) {
    mysqli_query($koneksi, "DELETE FROM clustering_results WHERE semester = '$semester'");
    for($i = 0; $i < count($data); $i++) {
        $cluster = $result['assignments'][$i];
        $skor_rerata = array_sum([$data[$i]['kognitif'], $data[$i]['motorik'], $data[$i]['sosial_emosional'], 
                                  $data[$i]['bahasa'], $data[$i]['seni'], $data[$i]['agama']]) / 6;
        $label = $cluster_names[$cluster];
        mysqli_query($koneksi, "INSERT INTO clustering_results (id_siswa, semester, cluster, label_cluster, skor_rerata) 
            VALUES ('{$data[$i]['id_siswa']}', '$semester', '$cluster', '$label', '$skor_rerata')");
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>K-Means Clustering - Admin SIAKAD TK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link href="../assets/css/style.css?v=4" rel="stylesheet">
    <style>
        .cluster-card { border-left: 5px solid; }
        .cluster-a { border-color: #28a745; }
        .cluster-b { border-color: #ffc107; }
        .cluster-c { border-color: #dc3545; }
        .stat-box { padding: 15px; border-radius: 10px; color: white; }
    </style>

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
                        <li class="nav-item"><a class="nav-link text-white" href="dashboard.php"> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="guru.php"> Data Guru</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="siswa.php"> Data Siswa</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="kelas.php"> Data Kelas</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="tagihan.php"> Tagihan SPP</a></li>
                        <li class="nav-item"><a class="nav-link text-white active" href="clustering.php"> Clustering</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="qr_generator.php"> QR Generator</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="wa_logs.php"> WA Logs</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="pengumuman.php"> Pengumuman</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="../logout.php"> Keluar</a></li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <h2 class="mb-4"><i class="bi bi-diagram-3"></i> K-Means Clustering</h2>
                
                <!-- Filter Semester -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Semester</label>
                                <select name="semester" class="form-select" onchange="this.form.submit()">
                                    <option value="Ganjil" <?= $semester == 'Ganjil' ? 'selected' : '' ?>>Ganjil</option>
                                    <option value="Genap" <?= $semester == 'Genap' ? 'selected' : '' ?>>Genap</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if(empty($data)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Belum ada data laporan untuk semester <?= $semester ?>.
                        Silakan input nilai terlebih dahulu di menu Guru.
                    </div>
                <?php else: ?>
                    
                    <!-- Statistik Cluster -->
                    <div class="row mb-4">
                        <?php foreach($cluster_order as $c): ?>
                        <div class="col-md-4">
                            <div class="stat-box bg-<?= $cluster_colors[$c] ?>">
                                <h5><i class="bi bi-<?= $cluster_icons[$c] ?>"></i> <?= $cluster_names[$c] ?></h5>
                                <h3><?= $cluster_counts[$c] ?> Siswa</h3>
                                <small>Rata-rata: <?= number_format($cluster_avg[$c], 2) ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Detail per Cluster -->
                    <?php foreach($cluster_order as $c): ?>
                    <div class="card cluster-card cluster-<?= strtolower(substr($cluster_names[$c], 8, 1)) ?> mb-4">
                        <div class="card-header">
                            <h5><i class="bi bi-<?= $cluster_icons[$c] ?>"></i> <?= $cluster_names[$c] ?> (<?= $cluster_counts[$c] ?> siswa)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Kelas</th>
                                            <th>Kognitif</th>
                                            <th>Motorik</th>
                                            <th>Sosial-Emosional</th>
                                            <th>Bahasa</th>
                                            <th>Seni</th>
                                            <th>Agama</th>
                                            <th>Rata-rata</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                    for($i = 0; $i < count($data); $i++): 
                                        if($result['assignments'][$i] != $c) continue;
                                        $skor_rerata = array_sum([$data[$i]['kognitif'], $data[$i]['motorik'], $data[$i]['sosial_emosional'], 
                                                                  $data[$i]['bahasa'], $data[$i]['seni'], $data[$i]['agama']]) / 6;
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($data[$i]['nama']) ?></td>
                                            <td><?= htmlspecialchars($data[$i]['kelas']) ?></td>
                                            <td><?= $data[$i]['kognitif'] ?></td>
                                            <td><?= $data[$i]['motorik'] ?></td>
                                            <td><?= $data[$i]['sosial_emosional'] ?></td>
                                            <td><?= $data[$i]['bahasa'] ?></td>
                                            <td><?= $data[$i]['seni'] ?></td>
                                            <td><?= $data[$i]['agama'] ?></td>
                                            <td><strong><?= number_format($skor_rerata, 2) ?></strong></td>
                                        </tr>
                                    <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Visualisasi Centroid -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-bar-chart"></i> Centroid per Cluster (Rata-rata per Aspek)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Cluster</th>
                                            <th>Kognitif</th>
                                            <th>Motorik</th>
                                            <th>Sosial-Emosional</th>
                                            <th>Bahasa</th>
                                            <th>Seni</th>
                                            <th>Agama</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($cluster_order as $c): ?>
                                        <tr class="table-<?= $cluster_colors[$c] ?>">
                                            <td><strong><?= $cluster_names[$c] ?></strong></td>
                                            <td><?= number_format($result['centroids'][$c]['kognitif'], 2) ?></td>
                                            <td><?= number_format($result['centroids'][$c]['motorik'], 2) ?></td>
                                            <td><?= number_format($result['centroids'][$c]['sosial_emosional'], 2) ?></td>
                                            <td><?= number_format($result['centroids'][$c]['bahasa'], 2) ?></td>
                                            <td><?= number_format($result['centroids'][$c]['seni'], 2) ?></td>
                                            <td><?= number_format($result['centroids'][$c]['agama'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
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
