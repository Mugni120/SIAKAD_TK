<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Orang Tua"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';
include '../config/midtrans.php';

$id_siswa = $_SESSION['id_siswa'];

// Ambil data siswa
$q_siswa = mysqli_query($koneksi, "SELECT s.nama_siswa, k.nama_kelas FROM siswa s 
    LEFT JOIN kelas k ON s.id_kelas = k.id_kelas WHERE s.id_siswa = '$id_siswa'");
$d_siswa = mysqli_fetch_assoc($q_siswa);

// Ambil tagihan
$q_tagihan = mysqli_query($koneksi, "SELECT * FROM tagihan WHERE id_siswa = '$id_siswa' ORDER BY tahun DESC, bulan DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan SPP - SIAKAD TK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <style>
        body { background: #f8f9fa; }
        .navbar-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-pay { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-pay:hover { opacity: 0.9; }
        .status-lunas { color: #28a745; font-weight: bold; }
        .status-belum { color: #dc3545; font-weight: bold; }
    </style>
    <link rel="stylesheet" href="../assets/css/cards.css?v=14">
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <span class="navbar-text text-white">
                <i class="bi bi-cash-stack"></i> Tagihan SPP
            </span>
        </div>
    </nav>

    <div class="container">
        <!-- Info Siswa -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($d_siswa['nama_siswa']) ?>
                </h5>
                <p class="card-text text-muted">Kelas: <?= htmlspecialchars($d_siswa['nama_kelas']) ?></p>
            </div>
        </div>

        <!-- Flash Message -->
        <?php if(isset($_GET['pesan'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_GET['pesan']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Daftar Tagihan -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-receipt"></i> Daftar Tagihan SPP
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Tahun</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $total_belum = 0;
                        while($d = mysqli_fetch_assoc($q_tagihan)): 
                            $is_lunas = $d['status'] == 'lunas';
                            if(!$is_lunas) $total_belum += $d['jumlah'];
                        ?>
                            <tr>
                                <td><?= $d['bulan'] ?></td>
                                <td><?= $d['tahun'] ?></td>
                                <td>Rp <?= number_format($d['jumlah'], 0, ',', '.') ?></td>
                                <td class="<?= $is_lunas ? 'status-lunas' : 'status-belum' ?>">
                                    <?= $is_lunas ? 'Lunas' : 'Belum Bayar' ?>
                                </td>
                                <td>
                                    <?php if(!$is_lunas): ?>
                                    <button class="btn btn-pay btn-sm text-white" 
                                            onclick="bayar(<?= $d['id_tagihan'] ?>, '<?= $d['bulan'] ?> <?= $d['tahun'] ?>', <?= $d['jumlah'] ?>)">
                                        <i class="bi bi-credit-card"></i> Bayar
                                    </button>
                                    <?php else: ?>
                                    <a href="riwayat_pembayaran.php?id=<?= $d['id_tagihan'] ?>" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($q_tagihan) == 0): ?>
                            <tr><td colspan="5" class="text-center text-muted">Belum ada tagihan</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Total Belum Bayar -->
        <?php if($total_belum > 0): ?>
        <div class="card mt-4 border-warning">
            <div class="card-body text-center">
                <h5 class="text-warning">Total Tagihan Belum Lunas</h5>
                <h3 class="text-danger">Rp <?= number_format($total_belum, 0, ',', '.') ?></h3>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal Pembayaran -->
    <div class="modal fade" id="modalBayar" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-credit-card"></i> Pembayaran SPP</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Snap token akan di-load di sini -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= MIDTRANS_SNAP_URL ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
    <script>
    function bayar(id_tagihan, periode, jumlah) {
        const modal = new bootstrap.Modal(document.getElementById('modalBayar'));
        const modalBody = document.getElementById('modalBody');
        modalBody.innerHTML = '<div class="text-center"><div class="spinner-border"></div><p>Memproses pembayaran...</p></div>';
        modal.show();

        // Request snap token dari server
        fetch('payment_ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id_tagihan=' + id_tagihan
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                if(data.simulation) {
                    // Mode simulasi - langsung sukses
                    modalBody.innerHTML = `
                        <div class="text-center">
                            <i class="bi bi-check-circle-fill" style="font-size: 60px; color: #28a745;"></i>
                            <h4 class="text-success mt-3">Pembayaran Berhasil!</h4>
                            <p>Tagihan SPP ${periode} telah lunas.</p>
                            <p><strong>Order ID:</strong> ${data.order_id}</p>
                            <p><strong>Metode:</strong> Bank Transfer (BCA)</p>
                            <p><strong>VA Number:</strong> 8808xxxxx</p>
                            <button class="btn btn-primary" onclick="window.location.href='tagihan.php'">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </button>
                        </div>
                    `;
                } else {
                    // Mode live - gunakan Midtrans Snap
                    modalBody.innerHTML = `
                        <div class="text-center mb-3">
                            <h5>Pembayaran SPP ${periode}</h5>
                            <h3 class="text-primary">Rp ${parseInt(jumlah).toLocaleString('id-ID')}</h3>
                        </div>
                        <div id="snap-container"></div>
                    `;
                    snap.pay(data.token, {
                        onSuccess: function(result) {
                            window.location.href = 'payment_sukses.php?order_id=' + result.order_id;
                        },
                        onPending: function(result) {
                            window.location.href = 'payment_pending.php?order_id=' + result.order_id;
                        },
                        onError: function(result) {
                            modalBody.innerHTML = '<div class="alert alert-danger">Pembayaran gagal. Silakan coba lagi.</div>';
                        }
                    });
                }
            } else {
                modalBody.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
        })
        .catch(err => {
            modalBody.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan. Silakan coba lagi.</div>';
        });
    }
    </script>
</body>
</html>
