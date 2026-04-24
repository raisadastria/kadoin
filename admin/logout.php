<?php
    // admin/logout.php
    require_once '../includes/config.php'; // Untuk memulai session dan akses BASE_URL

    // Hancurkan semua data session
    $_SESSION = array(); // Kosongkan array session

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy(); // Hancurkan session

    // Redirect ke halaman login admin
    redirect(BASE_URL . 'admin/login.php');
    ?>
    