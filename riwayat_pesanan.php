<?php
// toko_online/riwayat_pesanan.php
require_once 'includes/config.php'; // Memuat config (termasuk session_start())

// 1. PERIKSA APAKAH USER SUDAH LOGIN
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url']  = BASE_URL. 'riwayat_pesanan.php';
    $_SESSION['temp_notification'] = ['type' => 'error', 'message' => 'Anda harus login untuk melihat riwayat pesanan.'];
    redirect('login.php');
    // exit;
}

$id_user_saat_ini = $_SESSION['user_id'];
$daftar_pesanan_user = [];
$errors = [];

// 2. AMBIL DATA SEMUA PESANAN MILIK USER YANG SEDANG LOGIN
$sql_pesanan = "SELECT id_pesanan, nama_penerima, total_harga, status_pesanan, waktu_pesanan 
                FROM pesanan 
                WHERE id_user = ? 
                ORDER BY waktu_pesanan DESC";
$stmt_pesanan = $conn->prepare($sql_pesanan);

if ($stmt_pesanan) {
    $stmt_pesanan->bind_param("i", $id_user_saat_ini);
    $stmt_pesanan->execute();
    $result_pesanan = $stmt_pesanan->get_result();

    while ($row = $result_pesanan->fetch_assoc()) {
        $daftar_pesanan_user[] = $row;
    }
    $stmt_pesanan->close();
} else {
    $errors[] = "Gagal mengambil data pesanan Anda: " . $conn->error;
}

// Panggil header SETELAH semua logika PHP selesai
require_once 'includes/header.php';
?>

<div class="container" style="max-width: 900px; margin: 30px auto; padding: 25px; background-color: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); font-family: 'Poppins', sans-serif;">
    <h1 style="text-align: center; margin-bottom: 30px; color: #58375F; border-bottom: 2px solid #f0e6ff; padding-bottom:15px; font-weight: 600;">Riwayat Pesanan Saya</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="background-color: #ffebee; color: #C62828; padding: 15px; border: 1px solid #FFCDD2; border-radius: 8px; margin-bottom: 20px;">
            <strong>Error!</strong>
            <ul style="margin-top: 10px; padding-left: 20px; margin-bottom:0;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (empty($daftar_pesanan_user) && empty($errors)): ?>
        <p style="text-align: center; font-size: 1.1em; color: #665; padding: 40px 0;">
            Anda belum memiliki riwayat pesanan. <br>
            <a href="produk.php" style="color: #a076f9; text-decoration: none; font-weight: bold;">Mulai belanja sekarang!</a>
        </p>
    <?php elseif (!empty($daftar_pesanan_user)): ?>
        <div class="order-history-list">
            <?php foreach ($daftar_pesanan_user as $pesanan_item): ?>
                <div class="order-card" 
                     style="background-color: #fdfaff; border: 1px solid #e0cde3; border-radius: 8px; margin-bottom: 20px; padding: 20px; box-shadow: 0 3px 8px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; padding-bottom:10px; border-bottom: 1px dashed #e0cde3;">
                        <div>
                            <h3 style="margin: 0 0 5px 0; font-size: 1.3em; color: #77569a;">
                                Pesanan #<?php echo htmlspecialchars($pesanan_item['id_pesanan']); ?>
                            </h3>
                            <p style="font-size: 0.9em; color: #666; margin: 0;">
                                Tanggal: <?php echo date("d F Y, H:i", strtotime($pesanan_item['waktu_pesanan'])); ?>
                            </p>
                        </div>
                        <span class="status-badge status-<?php echo htmlspecialchars(strtolower($pesanan_item['status_pesanan'])); ?>"
                              style="padding: 6px 12px; border-radius: 15px; color: white; font-size: 0.85em; text-transform: capitalize; white-space:nowrap;
                                     background-color: <?php 
                                        switch(strtolower($pesanan_item['status_pesanan'])) {
                                            case 'pending': echo '#FFC107'; break; 
                                            case 'processing': echo '#17A2B8'; break; 
                                            case 'shipped': echo '#28A745'; break; 
                                            case 'completed': echo '#6F42C1'; break; 
                                            case 'cancelled': echo '#DC3545'; break; 
                                            default: echo '#6C757D'; 
                                        }
                                     ?>;">
                            <?php echo htmlspecialchars($pesanan_item['status_pesanan']); ?>
                        </span>
                    </div>
                    
                    <p style="margin: 5px 0;"><strong>Penerima:</strong> <?php echo htmlspecialchars($pesanan_item['nama_penerima']); ?></p>
                    <p style="margin: 5px 0 15px 0;"><strong>Total Pembayaran:</strong> <strong style="color: #58375F;"><?php echo format_rupiah($pesanan_item['total_harga']); ?></strong></p>
                    
                    <a href="riwayat_pesanan.php?lihat_detail=<?php echo $pesanan_item['id_pesanan']; ?>" 
                       style="display: inline-block; padding: 8px 15px; background-color: #f0e6ff; color: #77569a; 
                              text-decoration: none; border-radius: 5px; font-size: 0.9em; border: 1px solid #d1b5ff; transition: background-color 0.2s;">
                        Lihat Detail Item
                    </a>

                    <?php // Jika parameter lihat_detail ada dan cocok dengan ID pesanan ini, tampilkan itemnya
                    if (isset($_GET['lihat_detail']) && $_GET['lihat_detail'] == $pesanan_item['id_pesanan']):
                        $detail_items_current_order = [];
                        $sql_items = "SELECT nama_produk_saat_pesan, quantity, harga_saat_pesan, subtotal 
                                      FROM detail_pesanan WHERE id_pesanan = ?";
                        $stmt_items = $conn->prepare($sql_items);
                        if ($stmt_items) {
                            $stmt_items->bind_param("i", $pesanan_item['id_pesanan']);
                            $stmt_items->execute();
                            $result_items = $stmt_items->get_result();
                            while ($item_row = $result_items->fetch_assoc()) {
                                $detail_items_current_order[] = $item_row;
                            }
                            $stmt_items->close();
                        }
                    ?>
                        <?php if (!empty($detail_items_current_order)): ?>
                        <div class="order-items-detail" style="margin-top: 15px; padding-top:15px; border-top: 1px solid #e0cde3;">
                            <h4 style="font-size: 1em; margin-bottom: 10px; color: #4A4A4A;">Rincian Item:</h4>
                            <ul style="list-style: none; padding-left: 0; font-size: 0.9em;">
                                <?php foreach ($detail_items_current_order as $item_detail): ?>
                                    <li style="padding: 6px 0; display:flex; justify-content:space-between; border-bottom: 1px dashed #eee;">
                                        <span><?php echo htmlspecialchars($item_detail['nama_produk_saat_pesan']); ?> (x<?php echo $item_detail['quantity']; ?>)</span>
                                        <span><?php echo format_rupiah($item_detail['subtotal']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php else: ?>
                            <p style="font-size:0.9em; color:#777; margin-top:10px;"><em>Detail item tidak dapat dimuat.</em></p>
                        <?php endif; ?>
                    <?php endif; // Akhir dari if lihat_detail ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

     <div style="text-align: center; margin-top: 30px;">
        <a href="<?php echo BASE_URL; ?>produk.php" style="color: #77569a; text-decoration: none; font-weight:500;">&laquo; Kembali Belanja</a>
    </div>
</div>

<?php
require_once 'includes/footer.php'; // Memuat footer umum
?>
