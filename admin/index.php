<?php
// admin/index.php (Dashboard Admin)
// Pastikan file ini ada di toko_online/admin/index.php

// Memuat header admin (termasuk config.php dan auth_check.php)
// Path ke header_admin.php adalah relatif dari file ini (admin/index.php)
require_once 'includes/header_admin.php'; 
?>

<h2>Selamat Datang Min di Dashboard Kadoin </h2>
<p>Disini ente bisa mengelola produk, melihat transaksi, dan mengatur berbagai aspek toko online Anda.</p>

<div style="display:flex; gap: 20px; margin-top:30px;">
    <div style="background:#fff; padding:20px; border-radius:5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex:1;">
        <h3>Produk</h3>
        <?php
        // Pastikan $conn tersedia dari header_admin.php -> config.php
        if (isset($conn)) {
            // Ambil jumlah produk dari database
            $sql_produk_count = "SELECT COUNT(*) as total_produk FROM produk";
            $result_produk_count = $conn->query($sql_produk_count);
            $total_produk = 0;
            if ($result_produk_count && $result_produk_count->num_rows > 0) {
                $total_produk = $result_produk_count->fetch_assoc()['total_produk'];
            }
            echo "<p>Total Produk: <strong>" . $total_produk . "</strong></p>";
        } else {
            echo "<p>Koneksi database tidak tersedia.</p>";
        }
        ?>
        <a href="produk_list.php" class="btn">Kelola Produk</a>
    </div>

    <div style="background:#fff; padding:20px; border-radius:5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex:1;">
        <h3>Pesanan (Transaksi)</h3>
        <?php
        if (isset($conn)) {
            // Ambil jumlah pesanan dari database
            $sql_pesanan_count = "SELECT COUNT(*) as total_pesanan FROM pesanan";
            $result_pesanan_count = $conn->query($sql_pesanan_count);
            $total_pesanan = 0;
            if ($result_pesanan_count && $result_pesanan_count->num_rows > 0) {
                $total_pesanan = $result_pesanan_count->fetch_assoc()['total_pesanan'];
            }
            echo "<p>Total Pesanan: <strong>" . $total_pesanan . "</strong></p>";
        } else {
            echo "<p>Koneksi database tidak tersedia.</p>";
        }
        ?>
        <a href="transaksi_list.php" class="btn">Lihat Transaksi</a>
    </div>
</div>

<?php
// Memuat footer admin
// Path ke footer_admin.php adalah relatif dari file ini (admin/index.php)
require_once 'includes/footer_admin.php'; 
?>
