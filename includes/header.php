<?php
// Pastikan config.php di-include
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} else if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else {
    die("FATAL ERROR: config.php tidak dapat ditemukan.");
}

// Mulai session jika belum
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$search_query_display = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';

// Mendapatkan nama file saat ini untuk menandai link navigasi yang aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KadoIn. - Toko Kado Online</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/img/CSS/style.css">
</head>
<body>
    <div class="main-header-container">
        <header class="main-header">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>">KadoIn.</a>
            </div>
            
            <nav class="main-nav" id="main-nav">
                <div class="main-nav-links">
                    <a href="<?php echo BASE_URL; ?>" class="<?php echo ($current_page == 'index.php' || $current_page == '') ? 'active' : ''; ?>">Home</a>
                    <a href="<?php echo BASE_URL; ?>produk.php" class="<?php echo ($current_page == 'produk.php') ? 'active' : ''; ?>">Shop</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>riwayat_pesanan.php" class="<?php echo ($current_page == 'riwayat_pesanan.php') ? 'active' : ''; ?>">Riwayat Pesanan</a>
                    <?php endif; ?>
                </div>
                <form action="<?php echo BASE_URL; ?>produk.php" method="GET" class="search-form">
                    <input type="text" name="q" placeholder="Cari produk..." value="<?php echo $search_query_display; ?>">
                    <button type="submit" title="Cari">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                    </button>
                </form>
            </nav>

            <div class="user-actions">
                <?php 
                $total_item_di_keranjang = 0;
                if (!empty($_SESSION['keranjang'])) {
                    foreach ($_SESSION['keranjang'] as $item_nav) {
                        $total_item_di_keranjang += $item_nav['quantity'];
                    }
                }
                ?>
                <a href="<?php echo BASE_URL; ?>keranjang.php" class="nav-icon" title="Keranjang Belanja">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.49.402H3.21l.94 4.705A.5.5 0 0 0 4.5 14h7a.5.5 0 0 0 .5-.5.5.5 0 0 0-.5-.5h-7a.5.5 0 0 0-.485-.621L3.166 6.621 2.5 2.5H.5a.5.5 0 0 1-.5-.5zM3.14 4l.75 3.75h9.02l.75-3.75H3.14zM5 13a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0z"/></svg>
                    <?php if ($total_item_di_keranjang > 0): ?>
                        <span class="cart-count-badge"><?php echo $total_item_di_keranjang; ?></span>
                    <?php endif; ?>
                </a>

                <div class="auth-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="#" title="Profil Saya" class="nav-icon"> 
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/></svg>
                        </a>
                        <a href="<?php echo BASE_URL; ?>logout.php">Logout</a>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>admin/">Admin</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>login.php" class="<?php echo ($current_page == 'login.php') ? 'active' : ''; ?>">Login</a>
                        <a href="<?php echo BASE_URL; ?>register.php" class="btn-primary">Daftar</a>
                    <?php endif; ?>
                </div>

                <button class="mobile-nav-toggle" id="mobile-nav-toggle" aria-controls="main-nav" aria-expanded="false">
    <span class="sr-only"></span>
    
    <svg class="icon-menu" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="4" y1="12" x2="20" y2="12"></line>
        <line x1="4" y1="6" x2="20" y2="6"></line>
        <line x1="4" y1="18" x2="20" y2="18"></line>
    </svg>

    <svg class="icon-close" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
    </svg>
</button>
            </div>
        </header>
    </div>
    <main class="site-main-content">