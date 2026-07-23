<?php
// Include template header bawaan admin kamu
include 'template/header.php'; 

// PENTING: Panggil koneksi database secara langsung
include '../config/koneksi.php';
?>

<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <h2 class="fw-bold text-dark">Kelola Pengumuman</h2>
        <p class="text-muted">Informasi yang dibuat di sini akan langsung tampil di Dashboard Guru & Orang Tua.</p>
    </div>

    <?php if (isset($_GET['pesan'])): ?>
        <?php if ($_GET['pesan'] == 'sukses_tambah'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Pengumuman baru berhasil diterbitkan!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($_GET['pesan'] == 'sukses_edit'): ?>
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> Pengumuman berhasil diperbarui!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($_GET['pesan'] == 'sukses_hapus'): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-trash-fill me-2"></i> Pengumuman berhasil dihapus dari sistem.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (strpos($_GET['pesan'], 'gagal') !== false): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Terjadi kesalahan internal database. Gagal memproses data.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 text-dark fw-semibold">
                        <i class="bi bi-plus-circle-fill me-2 text-primary"></i>Buat Pengumuman Baru
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="pengumuman_aksi.php" method="POST">
                        <input type="hidden" name="aksi" value="tambah">
                        
                        <div class="mb-3">
                            <label for="judul" class="form-label fw-medium text-secondary">Judul Pengumuman</label>
                            <input type="text" class="form-control py-2" id="judul" name="judul" placeholder="Contoh: Pemberitahuan Libur Semester" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="isi_pengumuman" class="form-label fw-medium text-secondary">Isi Pengumuman / Informasi</label>
                            <textarea class="form-control" id="isi_pengumuman" name="isi_pengumuman" rows="6" placeholder="Tuliskan detail pengumuman secara jelas di sini..." required></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success py-2 fw-semibold">
                                <i class="bi bi-send-fill me-2"></i> Terbitkan Pengumuman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 text-dark fw-semibold">
                        <i class="bi bi-megaphone-fill me-2 text-warning"></i>Riwayat Pengumuman Sekolah
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary fw-semibold">
                                <tr>
                                    <th class="ps-4 text-center" width="8%">No</th>
                                    <th width="22%">Tanggal Terbit</th>
                                    <th width="45%">Isi Pengumuman</th>
                                    <th class="pe-4 text-center" width="25%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($koneksi, "SELECT * FROM pengumuman ORDER BY id_pengumuman DESC");
                                
                                if (mysqli_num_rows($query) > 0) {
                                    while ($data = mysqli_fetch_assoc($query)) {
                                ?>
                                        <tr>
                                            <td class="ps-4 text-center fw-medium text-secondary"><?= $no++; ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border py-2 px-2" style="font-size: 0.85rem;">
                                                    <i class="bi bi-calendar3 me-1 text-primary"></i> 
                                                    <?= date('d M Y', strtotime($data['tanggal_buat'])); ?>
                                                </span>
                                            </td>
                                            <td class="py-3">
                                                <h6 class="mb-1 fw-bold text-dark"><?= htmlspecialchars($data['judul']); ?></h6>
                                                <div class="text-muted small" style="max-height: 4.5em; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
                                                    <?= nl2br(htmlspecialchars($data['isi_pengumuman'])); ?>
                                                </div>
                                            </td>
                                            <td class="pe-4 text-center">
                                                <a href="pengumuman_edit.php?id=<?= $data['id_pengumuman']; ?>" 
                                                   class="btn btn-outline-primary btn-sm rounded-pill px-3 mb-1">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>
                                                
                                                <a href="pengumuman_aksi.php?hapus=<?= $data['id_pengumuman']; ?>" 
                                                   class="btn btn-outline-danger btn-sm rounded-pill px-3 mb-1" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')">
                                                    <i class="bi bi-trash-fill"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                <?php 
                                    }
                                } else { 
                                ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-info-circle d-block display-6 mb-3 text-black-50"></i>
                                            Belum ada pengumuman yang diterbitkan sekolah.
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include template footer bawaan admin kamu
include 'template/footer.php';
?>