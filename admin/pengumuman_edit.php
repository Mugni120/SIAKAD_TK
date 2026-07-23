<?php
include 'template/header.php'; 
include '../config/koneksi.php';

// Ambil ID dari URL
$id = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM pengumuman WHERE id_pengumuman='$id'");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan, kembalikan ke halaman sebelumnya
if(!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='pengumuman.php';</script>";
    exit();
}
?>

<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="pengumuman.php" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <h2 class="fw-bold text-dark">Edit Pengumuman</h2>
        <p class="text-muted">Lakukan perubahan pada pengumuman di bawah ini.</p>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 text-dark fw-semibold">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Form Edit Pengumuman
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="pengumuman_aksi.php" method="POST">
                        <input type="hidden" name="aksi" value="edit">
                        <input type="hidden" name="id_pengumuman" value="<?= $data['id_pengumuman']; ?>">
                        
                        <div class="mb-3">
                            <label for="judul" class="form-label fw-medium text-secondary">Judul Pengumuman</label>
                            <input type="text" class="form-control py-2" id="judul" name="judul" value="<?= htmlspecialchars($data['judul']); ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="isi_pengumuman" class="form-label fw-medium text-secondary">Isi Pengumuman / Informasi</label>
                            <textarea class="form-control" id="isi_pengumuman" name="isi_pengumuman" rows="6" required><?= htmlspecialchars($data['isi_pengumuman']); ?></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-semibold">
                                <i class="bi bi-save me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'template/footer.php';
?>