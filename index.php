<?php
session_start();

// Jika pengguna sudah memiliki sesi login aktif, langsung alihkan ke panel yang sesuai
if (isset($_SESSION['level'])) {
    if ($_SESSION['level'] == "Admin") {
        header("location: admin/dashboard.php");
        exit();
    } elseif ($_SESSION['level'] == "Guru") {
        header("location: guru/dashboard.php");
        exit();
    } elseif ($_SESSION['level'] == "Orang Tua") {
        header("location: orang_tua/dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAKAD TK Al-Manaarussa'diyyah</title>
    <style>
        /* Reset dasar agar tidak bentrok dengan konfigurasi stylesheet lain */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Memastikan wrapper memenuhi 100% tinggi dan lebar browser */
        .landing-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            width: 100%;
            background: linear-gradient(135deg, #0f172a 0%, #0d9488 100%);
            padding: 20px;
        }
        
        /* Desain Kotak Utama */
        .landing-card {
            background: #ffffff;
            padding: 45px 35px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .landing-icon {
            margin-bottom: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 14px;
            background-color: #f0fdfa;
            color: #0d9488;
        }

        .landing-eyebrow {
            color: #0d9488;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .landing-card h1 {
            color: #1e293b;
            font-size: 30px;
            margin: 0;
            font-weight: 800;
            letter-spacing: 1px;
        }
        
        .landing-card h2 {
            color: #0d9488;
            font-size: 18px;
            margin: 5px 0 20px 0;
            font-weight: 600;
        }
        
        .landing-card p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 35px;
        }
        
        /* Desain Tombol "Masuk" */
        .btn-portal {
            display: inline-block;
            background-color: #0d9488;
            color: #ffffff;
            padding: 12px 60px; /* Padding kanan-kiri diperlebar agar tombol tetap proporsional */
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.3);
        }
        
        .btn-portal:hover {
            background-color: #115e59;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(13, 148, 136, 0.4);
        }
    </style>
    <link rel="stylesheet" href="assets/css/cards.css?v=12">
</head>
<body>

    <div class="landing-wrapper">
        <div class="landing-card">
            <div class="landing-icon">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9.5 12 4l9 5.5"/>
                    <path d="M5 10v9h14v-9"/>
                    <path d="M9 19v-5h6v5"/>
                </svg>
            </div>
            <div class="landing-eyebrow">Sistem Informasi Akademik</div>
            <h1>SIAKAD</h1>
            <h2>TK Al-Manaarussa'diyyah</h2>
            <p>
                Sistem Informasi Akademik Terintegrasi. Memfasilitasi manajemen data master, 
                pencatatan absensi harian kelas, serta pemantauan bimbingan 6 aspek 
                lingkup perkembangan anak didik secara transparan.
            </p>
            <!-- Teks tombol diubah sesuai permintaan menjadi "Masuk" -->
            <a href="login.php" class="btn-portal">Masuk</a>
        </div>
    </div>

</body>
</html>