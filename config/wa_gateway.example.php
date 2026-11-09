<?php
/**
 * Contoh konfigurasi WhatsApp Gateway (contoh: Fonnte)
 *
 * Cara pakai:
 * 1. Salin file ini menjadi  config/wa_gateway.php  (tidak ikut ke git)
 * 2. Daftar di https://fonnte.com , connect device, ambil Token
 * 3. Isi token ke bawah ini
 */

// ===== GANTI DENGAN API KEY ANDA =====
define('WA_API_URL', 'https://api.fonnte.com/send');
define('WA_API_TOKEN', 'TOKEN_FONNTE_ANDA');  // <== GANTI
define('WA_ENABLED', true); // Set false untuk mode simulasi (tanpa kirim WA)

// Jam batas: siswa yang belum discan QR hingga jam ini dianggap belum hadir
define('WA_CUTOFF_TIME', '08:00'); // format HH:MM (24 jam)

/**
 * Kirim pesan WhatsApp
 *
 * @param string $nomor  Nomor tujuan (format: 628xxxxxxxxxx)
 * @param string $pesan  Isi pesan
 * @param string $tipe   Tipe pesan (absensi, laporan, dll)
 * @return array  ['success' => bool, 'message' => string]
 */
function wa_kirim($nomor, $pesan, $tipe = '') {
    global $koneksi;

    // Normalisasi nomor: pastikan format 62
    $nomor = preg_replace('/[^0-9]/', '', $nomor);
    if (substr($nomor, 0, 1) === '0') {
        $nomor = '62' . substr($nomor, 1);
    } elseif (substr($nomor, 0, 2) === '62') {
        // sudah benar
    } elseif (substr($nomor, 0, 3) === '+62') {
        $nomor = '62' . substr($nomor, 3);
    }

    // Simpan ke log
    $nomor_esc = mysqli_real_escape_string($koneksi, $nomor);
    $pesan_esc = mysqli_real_escape_string($koneksi, $pesan);
    $tipe_esc  = mysqli_real_escape_string($koneksi, $tipe);

    if (!WA_ENABLED) {
        // Mode simulasi - hanya simpan di database
        mysqli_query($koneksi, "INSERT INTO wa_logs (nomor_tujuan, pesan, status, tipe) 
            VALUES ('$nomor_esc', '$pesan_esc', 'simulated', '$tipe_esc')");
        return ['success' => true, 'message' => 'Simulasi: Pesan tersimpan di log'];
    }

    // Kirim via Fonnte API
    $data = [
        'target'  => $nomor,
        'message' => $pesan,
    ];

    $ch = curl_init(WA_API_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . WA_API_TOKEN,
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);
    $status = ($http_code == 200 && isset($result['status']) && $result['status'] === true) ? 'success' : 'failed';
    $response_str = mysqli_real_escape_string($koneksi, $response);

    mysqli_query($koneksi, "INSERT INTO wa_logs (nomor_tujuan, pesan, status, tipe, response) 
        VALUES ('$nomor_esc', '$pesan_esc', '$status', '$tipe_esc', '$response_str')");

    return [
        'success' => ($status === 'success'),
        'message' => $status === 'success' ? 'Pesan terkirim' : 'Gagal: ' . ($result['message'] ?? 'Unknown error')
    ];
}

/**
 * Kirim notifikasi absensi ke orang tua
 */
function wa_notif_absensi($id_siswa, $status, $tanggal, $keterangan = '') {
    global $koneksi;

    $q = mysqli_query($koneksi, "SELECT s.nama_siswa, s.nama_ortu, s.no_hp_ortu, k.nama_kelas 
        FROM siswa s LEFT JOIN kelas k ON s.id_kelas = k.id_kelas 
        WHERE s.id_siswa = '$id_siswa'");
    $d = mysqli_fetch_assoc($q);
    if (!$d) return ['success' => false, 'message' => 'Siswa tidak ditemukan'];

    $nama = $d['nama_siswa'];
    $ortu = $d['nama_ortu'];
    $kelas = $d['nama_kelas'];
    $hp = $d['no_hp_ortu'];

    $pesan = "Assalamu'alaikum Bapak/Ibu {$ortu},\n\n"
        . "Putra/Putri Anda *{$nama}* (Kelas {$kelas}) telah dicatat absensinya:\n\n"
        . "📅 Tanggal: {$tanggal}\n"
        . "📋 Status: *{$status}*\n";
    if ($keterangan) {
        $pesan .= "📝 Keterangan: {$keterangan}\n";
    }
    $pesan .= "\n"
        . "Terima kasih.\n"
        . "- SIAKAD TK Al-Manaussa'diyyah";

    return wa_kirim($hp, $pesan, 'absensi');
}

/**
 * Kirim notifikasi laporan perkembangan ke orang tua
 */
function wa_notif_laporan($id_siswa, $semester) {
    global $koneksi;

    $q = mysqli_query($koneksi, "SELECT s.nama_siswa, s.nama_ortu, s.no_hp_ortu, k.nama_kelas,
        l.kognitif, l.motorik, l.sosial_emosional, l.bahasa, l.seni, l.agama
        FROM siswa s 
        LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
        LEFT JOIN laporan_siswa l ON s.id_siswa = l.id_siswa AND l.semester = '$semester'
        WHERE s.id_siswa = '$id_siswa'");
    $d = mysqli_fetch_assoc($q);
    if (!$d) return ['success' => false, 'message' => 'Siswa tidak ditemukan'];

    $nama = $d['nama_siswa'];
    $ortu = $d['nama_ortu'];
    $kelas = $d['nama_kelas'];
    $hp = $d['no_hp_ortu'];

    $pesan = "Assalamu'alaikum Bapak/Ibu {$ortu},\n\n"
        . "Laporan perkembangan *{$nama}* (Kelas {$kelas}) untuk semester *{$semester}* telah diinput oleh guru.\n\n"
        . "📊 Ringkasan Nilai:\n"
        . "• Kognitif: {$d['kognitif']}\n"
        . "• Motorik: {$d['motorik']}\n"
        . "• Sosial-Emosional: {$d['sosial_emosional']}\n"
        . "• Bahasa: {$d['bahasa']}\n"
        . "• Seni: {$d['seni']}\n"
        . "• Agama: {$d['agama']}\n\n"
        . "Silakan login ke SIAKAD untuk melihat detail lengkap.\n\n"
        . "Terima kasih.\n"
        . "- SIAKAD TK Al-Manaussa'diyyah";

    return wa_kirim($hp, $pesan, 'laporan');
}

/**
 * Kirim notifikasi "belum hadir" ke orang tua untuk siswa yang
 * belum discan QR sampai jam tertentu (WA_CUTOFF_TIME).
 *
 * Anti-duplikat: setiap siswa hanya dinotifikasi 1x per tanggal,
 * dicek dari log wa_logs dengan tipe 'absen_tidak_hadir'.
 *
 * @param string $tanggal  Tanggal pengecekan (Y-m-d)
 * @param bool   $kirim    false = mode cek (hanya laporan, tanpa kirim WA)
 * @return array ['total_belum' => int, 'terkirim' => int, 'dilewati' => int, 'gagal' => int, 'rincian' => [...]]
 */
function wa_notif_belum_absen($tanggal, $kirim = true) {
    global $koneksi;

    $tanggal_esc = mysqli_real_escape_string($koneksi, $tanggal);

    // Siswa aktif (punya kartu QR) yang BELUM punya catatan absensi pada tanggal tsb
    $q = mysqli_query($koneksi, "SELECT s.id_siswa, s.nama_siswa, s.nama_ortu, s.no_hp_ortu, k.nama_kelas
        FROM siswa_qr qr
        JOIN siswa s ON qr.id_siswa = s.id_siswa
        LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
        WHERE s.no_hp_ortu <> ''
          AND NOT EXISTS (
              SELECT 1 FROM absensi a
              WHERE a.id_siswa = s.id_siswa AND a.tanggal = '$tanggal'
          )
        ORDER BY k.nama_kelas, s.nama_siswa");

    $hasil = [
        'total_belum' => 0,
        'terkirim'    => 0,
        'dilewati'    => 0,
        'gagal'       => 0,
        'rincian'     => [],
    ];

    while ($d = mysqli_fetch_assoc($q)) {
        $hasil['total_belum']++;

        $hp_esc = mysqli_real_escape_string($koneksi, $d['no_hp_ortu']);

        // Anti-duplikat: sudah dinotifikasi tanggal yang sama?
        $cek = mysqli_query($koneksi, "SELECT id_wa FROM wa_logs
            WHERE nomor_tujuan = '$hp_esc'
              AND tipe = 'absen_tidak_hadir'
              AND DATE(waktu_kirim) = '$tanggal'");
        if (mysqli_num_rows($cek) > 0) {
            $hasil['dilewati']++;
            $hasil['rincian'][] = [
                'nama'   => $d['nama_siswa'],
                'kelas'  => $d['nama_kelas'],
                'status' => 'dilewati (sudah dinotifikasi)',
            ];
            continue;
        }

        if (!$kirim) {
            $hasil['rincian'][] = [
                'nama'   => $d['nama_siswa'],
                'kelas'  => $d['nama_kelas'],
                'status' => 'belum hadir (menunggu jam cutoff)',
            ];
            continue;
        }

        $pesan = "Assalamu'alaikum Bapak/Ibu {$d['nama_ortu']},\n\n"
            . "Mohon maaf, hingga pukul *" . WA_CUTOFF_TIME . "* putra/putri Anda *{$d['nama_siswa']}* "
            . "(Kelas {$d['nama_kelas']}) belum tercatat hadir di sekolah hari ini ({$tanggal}).\n\n"
            . "Ini terjadi karena kartu QR absensinya belum discan oleh guru.\n"
            . "Apabila anak Anda izin/sakit, silakan hubungi wali kelas.\n\n"
            . "Terima kasih.\n"
            . "- SIAKAD TK Al-Manaussa'diyyah";

        $res = wa_kirim($d['no_hp_ortu'], $pesan, 'absen_tidak_hadir');

        if ($res['success']) {
            $hasil['terkirim']++;
            $status = 'terkirim';
        } else {
            $hasil['gagal']++;
            $status = 'gagal: ' . $res['message'];
        }
        $hasil['rincian'][] = [
            'nama'   => $d['nama_siswa'],
            'kelas'  => $d['nama_kelas'],
            'status' => $status,
        ];
    }

    return $hasil;
}
