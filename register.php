<?php 
// WAJIB DISISIPKAN DI BARIS PALING ATAS AGAR KONEKSI DATABASE BERFUNGSI
include 'config/koneksi.php'; 
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun - SIAKAD TK Al-Manaarussa'diyyah</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #0d9488 100%);
            padding: 20px;
        }

        .register-card {
            background: #ffffff;
            padding: 35px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
        }

        .register-card h2 {
            text-align: center;
            color: #1e293b;
            margin-bottom: 5px;
            font-size: 24px;
        }

        .register-card p {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #334155;
            font-size: 14px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #0d9488;
        }

        .btn-register {
            width: 100%;
            background: #0d9488;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }

        .btn-register:hover {
            background: #115e59;
        }

        .alert {
            color: #b91c1c;
            background: #fee2e2;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="register-card">
        <h2>Daftar Akun Baru</h2>
        <p>Silakan isi formulir di bawah ini dengan data yang valid</p>

        <?php
        if (isset($_GET['pesan'])) {
            if ($_GET['pesan'] == "username_kembar") {
                echo "<div class='alert'>Username sudah terdaftar! Gunakan nama lain.</div>";
            } else if ($_GET['pesan'] == "gagal") {
                echo "<div class='alert'>Terjadi kesalahan sistem pendaftaran.</div>";
            }
        }
        ?>

        <form action="proses_register.php" method="POST">
            <div class="form-group">
                <label>Hak Akses Sistem</label>
                <select name="level" id="level" onchange="aturFormulir()" required>
                    <option value="Guru">Guru / Tenaga Pendidik</option>
                    <option value="Orang Tua">Orang Tua / Wali Murid</option>
                </select>
            </div>

            <div class="form-group">
                <label>Username Baru</label>
                <input type="text" name="username" placeholder="Contoh: bun_anisa" required autocomplete="off">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>

            <div class="form-group">
                <label id="label_nama_pendaftar">Nama Lengkap Guru</label>
                <input type="text" name="nama_pendaftar" placeholder="Masukkan nama lengkap Anda" required>
            </div>

            <div id="form_khusus_guru" style="display: none; border-top: 1px dashed #cbd5e1; padding-top: 15px; margin-top: 15px;">
                <div class="form-group">
                    <label>Pilih Kelas Ajar</label>
                    <select name="id_kelas" id="id_kelas">
                        <option value="">-- Pilih Kelas --</option>
                        <?php
                        $query_kelas = mysqli_query($koneksi, "SELECT id_kelas, nama_kelas FROM kelas");
                        while ($k = mysqli_fetch_array($query_kelas)) {
                            echo "<option value='" . $k['id_kelas'] . "'>" . $k['nama_kelas'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div id="form_khusus_ortu" style="display: none; border-top: 1px dashed #cbd5e1; padding-top: 15px; margin-top: 15px;">
                <div class="form-group">
                    <label>Pilih Anak Anda</label>
                    <select name="id_siswa" id="id_siswa" class="form-control" style="width:100%; padding:10px;">
                        <option value="">-- Pilih Anak --</option>
                        <?php
                        $query_siswa = mysqli_query($koneksi, "SELECT id_siswa, nama_siswa FROM siswa");
                        while ($s = mysqli_fetch_array($query_siswa)) {
                            echo "<option value='" . $s['id_siswa'] . "'>" . $s['nama_siswa'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-register">Registrasi</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <p style="font-size: 14px; color: #64748b;">Sudah punya akun? <a href="login.php" style="color: #0d9488; font-weight: bold; text-decoration: none;">Login</a></p>
        </div>
    </div>

    <script>
        function aturFormulir() {
            var level = document.getElementById("level").value;
            var labelNama = document.getElementById("label_nama_pendaftar");
            var formOrtu = document.getElementById("form_khusus_ortu");
            var formGuru = document.getElementById("form_khusus_guru");
            var selectSiswa = document.getElementById("id_siswa");
            var selectKelas = document.getElementById("id_kelas");

            if (level === "Orang Tua") {
                labelNama.innerText = "Nama Lengkap Orang Tua / Wali";
                formOrtu.style.display = "block";
                formGuru.style.display = "none";
                selectSiswa.setAttribute("required", "required");
                selectKelas.removeAttribute("required");
            } else {
                labelNama.innerText = "Nama Lengkap Guru";
                formOrtu.style.display = "none";
                formGuru.style.display = "block";
                selectKelas.setAttribute("required", "required");
                selectSiswa.removeAttribute("required");
            }
        }

        // Jalankan fungsi ini saat pertama kali halaman selesai dimuat agar inputan otomatis muncul sesuai default select
        document.addEventListener("DOMContentLoaded", aturFormulir);
    </script>
</body>

</html>