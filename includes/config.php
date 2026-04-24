<?php
// --- Pengaturan Database ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_toko_online');

// --- Membuat Koneksi ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// --- Cek Koneksi ---
if ($conn->connect_error) {
    die("Koneksi ke database GAGAL: " . $conn->connect_error);
}

// --- Pengaturan Dasar Lain (Opsional) ---
define('BASE_URL', 'http://localhost/toko_online/');

// --- Memulai Session ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Fungsi Bantuan ---
/**
 * Mengarahkan pengguna ke URL lain.
 * @param string $url URL tujuan.
 */
function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        // Fallback jika header sudah terkirim (seharusnya tidak terjadi jika logika benar)
        // Ini akan mencoba redirect menggunakan JavaScript atau meta tag.
        echo "<script type='text/javascript'>window.location.href='" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "' /></noscript>";
        // Tampilkan pesan manual jika JavaScript dan meta refresh gagal.
        echo "Output sudah dimulai, tidak bisa redirect dengan header PHP. Jika tidak dialihkan otomatis, silakan klik <a href='" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "'>di sini</a>.";
        exit(); // Tetap penting untuk menghentikan eksekusi skrip.
    }
}

/**
 * Memformat angka menjadi format mata uang Rupiah.
 * @param float|int $angka Angka yang akan diformat.
 * @return string Angka dalam format Rupiah.
 */
function format_rupiah($angka){
    $hasil_rupiah = "Rp " . number_format($angka, 0, ',', '.');
    return $hasil_rupiah;
}

// echo "";
?>
