-- Schema for SIAKAD TK.
-- Import this file into the local MySQL server before starting the application.
-- The database name must match config/koneksi.php.

CREATE DATABASE IF NOT EXISTS `data_tk`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `data_tk`;

CREATE TABLE IF NOT EXISTS `kelas` (
    `id_kelas` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama_kelas` VARCHAR(100) NOT NULL,
    `tahun_ajaran` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`id_kelas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `guru` (
    `id_guru` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama_guru` VARCHAR(150) NOT NULL,
    `id_kelas` INT UNSIGNED DEFAULT NULL,
    `nip` VARCHAR(50) DEFAULT NULL,
    `jabatan` VARCHAR(100) NOT NULL DEFAULT 'Guru Kelas',
    `no_telepon` VARCHAR(30) DEFAULT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    PRIMARY KEY (`id_guru`),
    KEY `idx_guru_kelas` (`id_kelas`),
    CONSTRAINT `fk_guru_kelas`
        FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `siswa` (
    `id_siswa` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama_siswa` VARCHAR(150) NOT NULL,
    `jenis_kelamin` VARCHAR(20) NOT NULL,
    `tgl_lahir` DATE NOT NULL,
    `nama_ortu` VARCHAR(150) NOT NULL,
    `no_hp_ortu` VARCHAR(30) NOT NULL,
    `id_kelas` INT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id_siswa`),
    KEY `idx_siswa_kelas` (`id_kelas`),
    CONSTRAINT `fk_siswa_kelas`
        FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
    `id_user` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `level` VARCHAR(20) NOT NULL,
    `id_guru` INT UNSIGNED DEFAULT NULL,
    `id_siswa` INT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id_user`),
    UNIQUE KEY `uq_users_username` (`username`),
    KEY `idx_users_guru` (`id_guru`),
    KEY `idx_users_siswa` (`id_siswa`),
    CONSTRAINT `fk_users_guru`
        FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_users_siswa`
        FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `absensi` (
    `id_absensi` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_siswa` INT UNSIGNED NOT NULL,
    `id_guru` INT UNSIGNED NOT NULL,
    `tanggal` DATE NOT NULL,
    `status` VARCHAR(20) NOT NULL,
    `keterangan` TEXT NULL,
    PRIMARY KEY (`id_absensi`),
    UNIQUE KEY `uq_absensi_siswa_tanggal` (`id_siswa`, `tanggal`),
    KEY `idx_absensi_guru` (`id_guru`),
    CONSTRAINT `fk_absensi_siswa`
        FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_absensi_guru`
        FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laporan_siswa` (
    `id_laporan` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_siswa` INT UNSIGNED NOT NULL,
    `id_guru` INT UNSIGNED DEFAULT NULL,
    `semester` VARCHAR(20) NOT NULL,
    `kognitif` VARCHAR(3) NOT NULL,
    `motorik` VARCHAR(3) NOT NULL,
    `sosial_emosional` VARCHAR(3) NOT NULL,
    `bahasa` VARCHAR(3) NOT NULL,
    `seni` VARCHAR(3) NOT NULL,
    `agama` VARCHAR(3) NOT NULL,
    `catatan_guru` TEXT NULL,
    PRIMARY KEY (`id_laporan`),
    UNIQUE KEY `uq_laporan_siswa_semester` (`id_siswa`, `semester`),
    KEY `idx_laporan_guru` (`id_guru`),
    CONSTRAINT `fk_laporan_siswa`
        FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_laporan_guru`
        FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pengumuman` (
    `id_pengumuman` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `judul` VARCHAR(255) NOT NULL,
    `isi_pengumuman` TEXT NOT NULL,
    `tanggal_buat` DATE NOT NULL,
    PRIMARY KEY (`id_pengumuman`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL UNTUK FITUR WHATSAPP GATEWAY
-- ============================================================

CREATE TABLE IF NOT EXISTS `wa_logs` (
    `id_wa` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nomor_tujuan` VARCHAR(30) NOT NULL,
    `pesan` TEXT NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
    `waktu_kirim` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `tipe` VARCHAR(50) DEFAULT NULL,
    `response` TEXT NULL,
    PRIMARY KEY (`id_wa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL UNTUK FITUR PAYMENT GATEWAY (SPP)
-- ============================================================

CREATE TABLE IF NOT EXISTS `tagihan` (
    `id_tagihan` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_siswa` INT UNSIGNED NOT NULL,
    `bulan` VARCHAR(20) NOT NULL,
    `tahun` YEAR NOT NULL,
    `jumlah` DECIMAL(12,2) NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'belum_bayar',
    `tanggal_buat` DATE NOT NULL DEFAULT (CURRENT_DATE),
    PRIMARY KEY (`id_tagihan`),
    UNIQUE KEY `uq_tagihan_siswa_bulan_tahun` (`id_siswa`, `bulan`, `tahun`),
    KEY `idx_tagihan_siswa` (`id_siswa`),
    CONSTRAINT `fk_tagihan_siswa`
        FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transaksi` (
    `id_transaksi` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_tagihan` INT UNSIGNED NOT NULL,
    `order_id` VARCHAR(100) NOT NULL,
    `payment_type` VARCHAR(50) DEFAULT NULL,
    `transaction_status` VARCHAR(30) NOT NULL DEFAULT 'pending',
    `gross_amount` DECIMAL(12,2) NOT NULL,
    `transaction_time` DATETIME DEFAULT NULL,
    `settlement_time` DATETIME DEFAULT NULL,
    `va_number` VARCHAR(50) DEFAULT NULL,
    `bank` VARCHAR(20) DEFAULT NULL,
    `pdf_url` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_transaksi`),
    UNIQUE KEY `uq_transaksi_order_id` (`order_id`),
    KEY `idx_transaksi_tagihan` (`id_tagihan`),
    CONSTRAINT `fk_transaksi_tagihan`
        FOREIGN KEY (`id_tagihan`) REFERENCES `tagihan` (`id_tagihan`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL UNTUK FITUR QR CODE ABSENSI
-- ============================================================

CREATE TABLE IF NOT EXISTS `siswa_qr` (
    `id_qr` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_siswa` INT UNSIGNED NOT NULL,
    `kode_qr` VARCHAR(100) NOT NULL,
    `tanggal_buat` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_qr`),
    UNIQUE KEY `uq_siswa_qr_siswa` (`id_siswa`),
    UNIQUE KEY `uq_siswa_qr_kode` (`kode_qr`),
    CONSTRAINT `fk_siswa_qr_siswa`
        FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL UNTUK FITUR K-MEANS CLUSTERING
-- ============================================================

CREATE TABLE IF NOT EXISTS `clustering_results` (
    `id_clustering` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_siswa` INT UNSIGNED NOT NULL,
    `semester` VARCHAR(20) NOT NULL,
    `cluster` INT NOT NULL,
    `label_cluster` VARCHAR(100) DEFAULT NULL,
    `skor_rerata` DECIMAL(5,2) DEFAULT NULL,
    `tanggal_proses` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_clustering`),
    UNIQUE KEY `uq_clustering_siswa_semester` (`id_siswa`, `semester`),
    KEY `idx_clustering_cluster` (`cluster`),
    CONSTRAINT `fk_clustering_siswa`
        FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
