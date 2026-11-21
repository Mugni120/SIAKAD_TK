-- Seed data untuk pengujian semua fitur SIAKAD TK
USE `data_tk`;

-- Kelas
INSERT INTO `kelas` (`nama_kelas`, `tahun_ajaran`) VALUES
('Kelompok A', '2024/2025'),
('Kelompok B', '2024/2025');

-- Guru
INSERT INTO `guru` (`nama_guru`, `id_kelas`, `nip`, `jabatan`, `no_telepon`, `email`) VALUES
('Bu Siti', 1, '198501012010012001', 'Guru Kelas', '081234567890', 'siti@tkalmanar.sch.id'),
('Bu Imas', 2, '198602022011012002', 'Guru Kelas', '081234567891', 'dewi@tkalmanar.sch.id');

-- Siswa
INSERT INTO `siswa` (`nama_siswa`, `jenis_kelamin`, `tgl_lahir`, `nama_ortu`, `no_hp_ortu`, `id_kelas`) VALUES
('Ahmad Fauzi', 'Laki-laki', '2019-03-15', 'Budi Santoso', '081234567892', 1),
('Aisyah Putri', 'Perempuan', '2019-05-20', 'Sari Wulandari', '081234567893', 1),
('Bilal Ramadhan', 'Laki-laki', '2019-01-10', 'Ahmad Hidayat', '081234567894', 1),
('Fatimah Zahra', 'Perempuan', '2019-07-25', 'Rina Kartika', '081234567895', 1),
('Umar Faruq', 'Laki-laki', '2019-04-12', 'Hendra Gunawan', '081234567896', 2),
('Khadijah Nur', 'Perempuan', '2019-09-08', 'Maya Anggraini', '081234567897', 2);

-- Users (password: password123)
INSERT INTO `users` (`username`, `password`, `level`, `id_guru`, `id_siswa`) VALUES
('admin', '$2y$10$3ciFFso.6AAz8ggZw8DIlelpnLnZxJ/wcc3nmB64.RpynIveZ0/kS', 'Admin', NULL, NULL),
('guru_siti', '$2y$10$3ciFFso.6AAz8ggZw8DIlelpnLnZxJ/wcc3nmB64.RpynIveZ0/kS', 'Guru', 1, NULL),
('guru_dewi', '$2y$10$3ciFFso.6AAz8ggZw8DIlelpnLnZxJ/wcc3nmB64.RpynIveZ0/kS', 'Guru', 2, NULL),
('ortu_budi', '$2y$10$3ciFFso.6AAz8ggZw8DIlelpnLnZxJ/wcc3nmB64.RpynIveZ0/kS', 'Orang Tua', NULL, 1),
('ortu_sari', '$2y$10$3ciFFso.6AAz8ggZw8DIlelpnLnZxJ/wcc3nmB64.RpynIveZ0/kS', 'Orang Tua', NULL, 2);

-- Tagihan SPP
INSERT INTO `tagihan` (`id_siswa`, `bulan`, `tahun`, `jumlah`, `status`) VALUES
(1, 'Januari', 2025, 150000, 'lunas'),
(1, 'Februari', 2025, 150000, 'belum_bayar'),
(1, 'Maret', 2025, 150000, 'belum_bayar'),
(2, 'Januari', 2025, 150000, 'lunas'),
(2, 'Februari', 2025, 150000, 'belum_bayar'),
(3, 'Januari', 2025, 150000, 'belum_bayar'),
(4, 'Januari', 2025, 150000, 'lunas'),
(5, 'Januari', 2025, 150000, 'belum_bayar'),
(6, 'Januari', 2025, 150000, 'belum_bayar');

-- Laporan Perkembangan (untuk clustering)
INSERT INTO `laporan_siswa` (`id_siswa`, `id_guru`, `semester`, `kognitif`, `motorik`, `sosial_emosional`, `bahasa`, `seni`, `agama`, `catatan_guru`) VALUES
(1, 1, 'Ganjil', 'BSB', 'BSH', 'BSB', 'BSH', 'BSB', 'BSH', 'Ahmad sangat aktif dan kreatif'),
(2, 1, 'Ganjil', 'BSH', 'BSB', 'BSH', 'BSB', 'BSH', 'BSH', 'Aisyah baik dalam semua aspek'),
(3, 1, 'Ganjil', 'MB', 'MB', 'MB', 'BB', 'MB', 'MB', 'Bilal perlu stimulasi lebih di bahasa'),
(4, 1, 'Ganjil', 'BSH', 'BSH', 'BSB', 'BSH', 'BSB', 'BSB', 'Fatimah sangat baik secara sosial'),
(5, 2, 'Ganjil', 'BB', 'MB', 'MB', 'BB', 'MB', 'MB', 'Umar perlu perhatian khusus di kognitif'),
(6, 2, 'Ganjil', 'BSH', 'BSH', 'BSH', 'BSH', 'MB', 'BSH', 'Khadijah cukup baik, perlu stimulasi seni');

-- QR Code untuk siswa
INSERT INTO `siswa_qr` (`id_siswa`, `kode_qr`) VALUES
(1, 'TK-QR-AHMAD-001'),
(2, 'TK-QR-AISYAH-002'),
(3, 'TK-QR-BILAL-003'),
(4, 'TK-QR-FATIMAH-004'),
(5, 'TK-QR-UMAR-005'),
(6, 'TK-QR-KHADIJAH-006');

-- Absensi contoh
INSERT INTO `absensi` (`id_siswa`, `id_guru`, `tanggal`, `status`, `keterangan`) VALUES
(1, 1, '2025-01-06', 'Hadir', ''),
(2, 1, '2025-01-06', 'Hadir', ''),
(3, 1, '2025-01-06', 'Sakit', 'Demam'),
(4, 1, '2025-01-06', 'Hadir', ''),
(5, 2, '2025-01-06', 'Hadir', ''),
(6, 2, '2025-01-06', 'Izin', 'Urusan keluarga');

-- Pengumuman
INSERT INTO `pengumuman` (`judul`, `isi_pengumuman`, `tanggal_buat`) VALUES
('Selamat Tahun Ajaran Baru', 'Selamat datang di tahun ajaran 2024/2025. Semoga tahun ini membawa berkah dan kemajuan untuk semua anak-anak kita.', '2025-01-01'),
('Pembayaran SPP', 'Pembayaran SPP bulan Januari 2025 dapat dilakukan melalui sistem pembayaran online. Hubungi admin jika ada kendala.', '2025-01-05'),
('Kegiatan Outing Class', 'Outing class akan dilaksanakan pada tanggal 25 Januari 2025 ke Taman Mini Indonesia Indah. Mohon persiapan anak-anak.', '2025-01-10');
