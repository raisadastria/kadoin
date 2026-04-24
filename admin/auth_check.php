<?php
// admin/includes/auth_check.php
// File ini akan disertakan di awal setiap halaman admin yang memerlukan otentikasi.

// Pastikan config.php sudah di-include sebelumnya (biasanya dari header_admin.php)
// dan session_start() sudah dipanggil oleh config.php.
if (session_status() == PHP_SESSION_NONE) {
    // echo "";
    session_start();
}

// Cek apakah user sudah login dan apakah rolenya admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    // Jika belum login atau bukan admin, redirect ke halaman login admin.
    
    // Simpan URL yang sedang diakses agar bisa kembali setelah login (opsional)
    // $_SESSION['redirect_url_admin'] = $_SERVER['REQUEST_URI']; 
    
    if (defined('BASE_URL') && function_exists('redirect')) {
        redirect(BASE_URL . 'admin/login.php'); 
        // Fungsi redirect() sudah mengandung exit(), jadi tidak perlu exit tambahan setelah ini.
    } else {
        // Fallback jika BASE_URL atau fungsi redirect() tidak ada
        die("Error: BASE_URL tidak terdefinisi atau fungsi redirect() tidak ditemukan. Tidak bisa mengarahkan ke halaman login admin.");
        // die() juga akan menghentikan eksekusi skrip.
    }
    // Tidak perlu 'exit;' lagi di sini karena path di atas sudah menghentikan skrip.
}

// Jika sudah login sebagai admin, tidak melakukan apa-apa, skrip pemanggil akan lanjut.
// echo ""; 
?>
