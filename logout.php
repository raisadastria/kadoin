<?php
// Pastikan file config.php di-include untuk memulai session
require_once 'includes/config.php';

// 1. Hancurkan semua data session
$_SESSION = array();

// 2. Hapus cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan session secara permanen
session_destroy();

// Alih-alih langsung redirect dengan PHP, kita akan tampilkan halaman HTML
// yang akan me-redirect secara otomatis setelah beberapa detik menggunakan meta tag.
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Logout - KadoIn</title>
    
    <meta http-equiv="refresh" content="3;url=<?php echo BASE_URL; ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/img/CSS/style.css">
</head>
<body>
    <div class="logout-container">
        <div class="logout-box">
            <div class="spinner"></div>
            <h2>Logout Berhasil</h2>
            <p>Anda telah berhasil keluar dari akun Anda.</p>
            <p class="redirect-info">Anda akan diarahkan kembali ke halaman utama dalam beberapa detik...</p>
            <a href="<?php echo BASE_URL; ?>" class="home-link">Kembali Sekarang</a>
        </div>
    </div>
</body>
</html>