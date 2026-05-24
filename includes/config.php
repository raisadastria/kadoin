<?php
// --- Pengaturan Database ---
define('DB_HOST', 'sql113.infinityfree.com');
define('DB_USER', 'if0_41742573');
define('DB_PASS', 'k4do1nh4mpers');
define('DB_NAME', 'if0_41742573_db_kadoin1');

// --- Membuat Koneksi ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// --- Cek Koneksi ---
if ($conn->connect_error) {
    die("Koneksi ke database GAGAL: " . $conn->connect_error);
}

// --- Pengaturan Dasar Lain (Opsional) ---
define('BASE_URL', 'http://hamperskadoin.rf.gd/');

// --- Memulai Session ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Fungsi Bantuan ---
function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        echo "<script type='text/javascript'>window.location.href='" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "' /></noscript>";
        echo "Output sudah dimulai, tidak bisa redirect dengan header PHP. Jika tidak dialihkan otomatis, silakan klik <a href='" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "'>di sini</a>.";
        exit();
    }
}

function format_rupiah($angka){
    $hasil_rupiah = "Rp " . number_format($angka, 0, ',', '.');
    return $hasil_rupiah;
}
?>