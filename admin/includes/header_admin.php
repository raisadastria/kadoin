<?php // INI HARUS BARIS PERTAMA, TANPA SPASI SEBELUMNYA
// admin/includes/header_admin.php

// Path ke config.php: relatif dari admin/includes/ ke toko_online/includes/
require_once dirname(__DIR__) . '/../includes/config.php'; 

// Path ke auth_check.php: relatif dari admin/includes/ ke admin/includes/ (atau admin/ jika auth_check.php ada di sana)
// Jika auth_check.php ada di folder yang sama (admin/includes/):
// require_once 'auth_check.php'; 
// Jika auth_check.php ada di folder admin/ (satu level di atas includes/):
require_once dirname(__DIR__) . '/auth_check.php'; // Menggunakan auth_check.php yang kamu unggah (ada di admin/)

// TIDAK BOLEH ADA ECHO ATAU HTML APAPUN SEBELUM INI JIKA ADA POTENSI REDIRECT DARI AUTH_CHECK
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Toko Online</title>
    <link rel="stylesheet" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>assets/css/admin_style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Coral+Pixels&family=DynaPuff:wght@400..700&family=Irish+Grover&family=Pacifico&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <style>
        /* ... (CSS kamu yang sudah ada) ... */
        body { font-family: poppins, sans-serif; margin: 0; background-color: #f9f9f9; }
        .admin-header { background-color: #333; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .admin-header h1 { margin: 0; font-size: 1.5em; }
        .admin-header a { color: white; text-decoration: none; }
        .admin-nav { list-style-type: none; margin: 0; padding: 0; display: flex; }
        .admin-nav li { margin-left: 20px; }
        .admin-nav a:hover { text-decoration: underline; }

        .admin-sidebar { width: 220px; background-color: #444; color: white; position: fixed; top:0; left:0; height: 100%; padding-top: 70px; /* Sesuaikan jika header admin fixed */ box-sizing: border-box; z-index:100;}
        .admin-sidebar a { display: block; color: white; padding: 12px 15px; text-decoration: none; border-bottom: 1px solid #555; }
        .admin-sidebar a:hover { background-color: #555; }
        .admin-sidebar a.active { background-color: #2a9fd6; } 
        
        .admin-main-content { margin-left: 230px; /* Sesuaikan dengan lebar sidebar + padding */ padding: 20px; margin-top: 60px; /* Sesuaikan dengan tinggi header admin jika fixed */ }
        .admin-main-content h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0; }

        .admin-footer { text-align: center; padding: 15px; background-color: #ddd; color: #333; margin-left: 220px; margin-top: 30px; font-size: 0.9em; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        table th { background-color: #f2f2f2; }
        table tr:nth-child(even) { background-color: #f9f9f9; }
        table img.product-thumb { max-width: 60px; height: auto; border-radius: 4px; }
        .actions a { margin-right: 8px; text-decoration: none; padding: 5px 8px; border-radius: 3px; display:inline-block; margin-bottom:5px;}
        .actions a.edit { background-color: #f0ad4e; color: white; }
        .actions a.delete { background-color: #d9534f; color: white; }
        .actions a.add-new { background-color: #5cb85c; color: white; padding: 8px 12px; display: inline-block; margin-bottom:15px; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="file"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; 
        }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .form-group .current-img { max-width: 100px; display: block; margin-top: 5px;}
        button[type="submit"], .btn {
            background-color: #5cb85c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            text-decoration: none;
        }
        button[type="submit"]:hover, .btn:hover { background-color: #4cae4c; }
        .btn-secondary { background-color: #777; }
        .btn-secondary:hover { background-color: #666; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
        .alert-danger { color: #a94442; background-color: #f2dede; border-color: #ebccd1; }
    </style>
</head>
<body>
    <header class="admin-header" style="position:fixed; top:0; left:0; width:100%; z-index:101;">
        <h1><a href="<?php echo defined('BASE_URL') ? BASE_URL . 'admin/' : ''; ?>">Mimin Kadoin</a></h1>
        <nav>
            <ul class="admin-nav">
                <?php if(isset($_SESSION['user_nama'])): ?>
                    <li>Halo, <?php echo htmlspecialchars($_SESSION['user_nama']); ?>!</li>
                <?php endif; ?>
                <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>" target="_blank">Lihat Situs</a></li>
                <li><a href="<?php echo defined('BASE_URL') ? BASE_URL . 'admin/logout.php' : 'logout.php'; ?>">Logout</a></li>
            </ul>
        </nav>
    </header>

    <aside class="admin-sidebar">
        <a href="<?php echo defined('BASE_URL') ? BASE_URL . 'admin/index.php' : 'index.php'; ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Dashboard</a>
        <a href="<?php echo defined('BASE_URL') ? BASE_URL . 'admin/produk_list.php' : 'produk_list.php'; ?>" class="<?php echo (strpos(basename($_SERVER['PHP_SELF']), 'produk_') !== false) ? 'active' : ''; ?>">Manajemen Produk</a>
        <a href="<?php echo defined('BASE_URL') ? BASE_URL . 'admin/transaksi_list.php' : 'transaksi_list.php'; ?>" class="<?php echo (strpos(basename($_SERVER['PHP_SELF']), 'transaksi_') !== false) ? 'active' : ''; ?>">Manajemen Transaksi</a>
        <a href="<?php echo BASE_URL; ?>admin/user_list.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'user_list.php') ? 'active' : ''; ?>">Manajemen Pengguna</a>
        <a href="<?php echo BASE_URL; ?>admin/kategori_list.php" class="<?php echo (strpos($currentPage, 'kategori_') !== false) ? 'active' : ''; ?>">Manajemen Kategori</a>
    </aside>

    <main class="admin-main-content">
