<?php
session_start();
// Jika sudah login, kembalikan ke halaman utama agar diredirect otomatis sesuai hak akses
if (isset($_SESSION['level'])) {
    header("location:index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIAKAD TK Al-Manaarussa'diyyah</title>
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

        .login-card {
            background: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
        }

        .login-card h2 {
            text-align: center;
            color: #1e293b;
            margin-bottom: 5px;
            font-size: 24px;
        }

        .login-card p {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
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

        .btn-login {
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
        }

        .btn-login:hover {
            background: #115e59;
        }

        .alert {
            color: #b91c1c;
            background: #fee2e2;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <h2>Selamat Datang</h2>
        <p>Silakan masuk untuk mengakses sistem akademik TK Al-Manaarussa'diyyah</p>

        <?php
        if (isset($_GET['pesan'])) {
            if ($_GET['pesan'] == "gagal") {
                echo "<div class='alert'>Username, password, atau hak akses salah!</div>";
            } else if ($_GET['pesan'] == "belum_login") {
                echo "<div class='alert'>Anda harus login untuk mengakses halaman tersebut.</div>";
            } else if ($_GET['pesan'] == "logout") {
                echo "<div class='alert' style='color:#065f46; background:#d1fae5;'>Anda telah berhasil keluar.</div>";
            }
        }
        ?>

        <form action="proses_login.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username Anda" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password Anda" required>
            </div>
            <div class="form-group">
                <label>Hak Akses / Sesi Ruang</label>
                <select name="level" required>
                    <option value="Admin">Admin Utama Sekolah</option>
                    <option value="Guru">Guru / Tenaga Pendidik</option>
                    <option value="Orang Tua">Orang Tua / Wali Murid</option>
                </select>
            </div>
            <button type="submit" class="btn-login">Masuk Aplikasi</button>
        </form>
        </form>
        <div style="text-align: center; margin-top: 20px;">
            <p style="font-size: 14px; color: #64748b;">Belum memiliki akun? <a href="register.php" style="color: #0d9488; font-weight: bold; text-decoration: none;">Daftar Sekarang</a></p>
        </div>
    </div>

</body>

</html>