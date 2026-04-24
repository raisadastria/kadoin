<?php
// admin/transaksi_detail.php
require_once 'includes/header_admin.php'; // Memuat header admin (termasuk auth check & config)

$id_pesanan = null;
$pesanan = null;
$detail_items = [];
$errors = [];
$notification = '';

// Ambil notifikasi jika ada (misal setelah update status)
if (isset($_SESSION['notification_transaksi_detail'])) {
    $notification = $_SESSION['notification_transaksi_detail'];
    unset($_SESSION['notification_transaksi_detail']);
}

// 1. Ambil ID Pesanan dari URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_pesanan = (int)$_GET['id'];
} else {
    $_SESSION['notification_transaksi'] = "Error: ID pesanan tidak valid atau tidak ditemukan.";
    redirect('transaksi_list.php');
    // exit;
}

// 2. Logika untuk Update Status Pesanan (jika form disubmit)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status_pesanan'])) {
    if (isset($_POST['status_pesanan_baru']) && !empty($_POST['status_pesanan_baru'])) {
        $status_baru = trim($_POST['status_pesanan_baru']);
        // Validasi status baru (opsional, tergantung daftar status yang diizinkan)
        $allowed_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
        if (in_array($status_baru, $allowed_statuses)) {
            $sql_update_status = "UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?";
            $stmt_update = $conn->prepare($sql_update_status);
            if ($stmt_update) {
                $stmt_update->bind_param("si", $status_baru, $id_pesanan);
                if ($stmt_update->execute()) {
                    $_SESSION['notification_transaksi_detail'] = "Status pesanan #" . $id_pesanan . " berhasil diperbarui menjadi '" . htmlspecialchars($status_baru) . "'.";
                } else {
                    $_SESSION['notification_transaksi_detail'] = "Error: Gagal memperbarui status pesanan. " . $stmt_update->error;
                }
                $stmt_update->close();
            } else {
                $_SESSION['notification_transaksi_detail'] = "Error: Gagal mempersiapkan statement update status. " . $conn->error;
            }
        } else {
            $_SESSION['notification_transaksi_detail'] = "Error: Status pesanan tidak valid.";
        }
    } else {
        $_SESSION['notification_transaksi_detail'] = "Error: Status baru tidak boleh kosong.";
    }
    // Redirect ke halaman ini sendiri untuk menampilkan notifikasi dan data terbaru
    redirect('transaksi_detail.php?id=' . $id_pesanan);
    // exit;
}


// 3. Ambil Data Pesanan Utama dari Database
$sql_pesanan = "SELECT p.*, u.email AS email_pelanggan, u.nama_user AS nama_pelanggan 
                FROM pesanan p 
                LEFT JOIN users u ON p.id_user = u.id_user 
                WHERE p.id_pesanan = ?";
$stmt_pesanan = $conn->prepare($sql_pesanan);

if ($stmt_pesanan) {
    $stmt_pesanan->bind_param("i", $id_pesanan);
    $stmt_pesanan->execute();
    $result_pesanan = $stmt_pesanan->get_result();

    if ($result_pesanan->num_rows == 1) {
        $pesanan = $result_pesanan->fetch_assoc();

        // 4. Ambil Detail Item Pesanan
        $sql_detail = "SELECT dp.*, pr.gambar1 AS gambar_produk_utama 
                       FROM detail_pesanan dp
                       LEFT JOIN produk pr ON dp.id_produk = pr.id_produk
                       WHERE dp.id_pesanan = ?";
        $stmt_detail = $conn->prepare($sql_detail);
        if ($stmt_detail) {
            $stmt_detail->bind_param("i", $id_pesanan);
            $stmt_detail->execute();
            $result_detail = $stmt_detail->get_result();
            while ($item = $result_detail->fetch_assoc()) {
                $detail_items[] = $item;
            }
            $stmt_detail->close();
        } else {
            $errors[] = "Gagal mengambil detail item pesanan: " . $conn->error;
        }
    } else {
        $errors[] = "Pesanan dengan ID #" . htmlspecialchars($id_pesanan) . " tidak ditemukan.";
    }
    $stmt_pesanan->close();
} else {
    $errors[] = "Gagal mempersiapkan query untuk mengambil data pesanan: " . $conn->error;
}

// Daftar status pesanan yang mungkin
$daftar_status_pesanan = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

?>

<h2>Detail Transaksi Pesanan #<?php echo htmlspecialchars($id_pesanan); ?></h2>

<a href="transaksi_list.php" class="btn btn-secondary" style="margin-bottom: 20px; display: inline-block; background-color: #6c757d; text-decoration:none;">&laquo; Kembali ke Daftar Transaksi</a>

<?php if ($notification): ?>
    <div class="alert <?php echo strpos(strtolower($notification), 'berhasil') !== false ? 'alert-success' : 'alert-danger'; ?>">
        <?php echo htmlspecialchars($notification); ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Error!</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php elseif ($pesanan): ?>
    <div class="transaksi-detail-container" style="display: flex; gap: 30px; flex-wrap: wrap;">
        
        <div class="info-pesanan" style="flex: 1; min-width: 300px; background-color: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
            <h3 style="margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 15px;">Informasi Pesanan</h3>
            <p><strong>ID Pesanan:</strong> #<?php echo htmlspecialchars($pesanan['id_pesanan']); ?></p>
            <p><strong>Waktu Pesanan:</strong> <?php echo date("d F Y, H:i:s", strtotime($pesanan['waktu_pesanan'])); ?></p>
            <p><strong>Total Pembayaran:</strong> <strong style="color: #28a745;"><?php echo format_rupiah($pesanan['total_harga']); ?></strong></p>
            <p><strong>Status Saat Ini:</strong> 
                <span class="status-badge status-<?php echo htmlspecialchars(strtolower($pesanan['status_pesanan'])); ?>"
                      style="padding: 5px 10px; border-radius: 15px; color: white; font-size: 0.9em; text-transform: capitalize;
                             background-color: <?php 
                                switch(strtolower($pesanan['status_pesanan'])) {
                                    case 'pending': echo '#FFC107'; break;
                                    case 'processing': echo '#17A2B8'; break;
                                    case 'shipped': echo '#28A745'; break;
                                    case 'completed': echo '#6F42C1'; break;
                                    case 'cancelled': echo '#DC3545'; break;
                                    default: echo '#6C757D';
                                }
                             ?>;">
                    <?php echo htmlspecialchars($pesanan['status_pesanan']); ?>
                </span>
            </p>
            <?php if (!empty($pesanan['catatan_pesanan'])): ?>
                <p><strong>Catatan dari Pelanggan:</strong><br><em style="white-space: pre-wrap;"><?php echo htmlspecialchars($pesanan['catatan_pesanan']); ?></em></p>
            <?php endif; ?>

            <hr style="margin: 20px 0;">
            <h4 style="margin-bottom: 10px;">Update Status Pesanan:</h4>
            <form action="transaksi_detail.php?id=<?php echo $id_pesanan; ?>" method="POST">
                <div class="form-group" style="display:flex; gap:10px; align-items:center;">
                    <select name="status_pesanan_baru" class="form-control" style="flex-grow:1; padding: 8px;">
                        <?php foreach ($daftar_status_pesanan as $status_option): ?>
                            <option value="<?php echo $status_option; ?>" <?php echo ($pesanan['status_pesanan'] == $status_option) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($status_option); // Huruf awal besar ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="update_status_pesanan" class="btn btn-primary" style="padding: 8px 15px;">Update Status</button>
                </div>
            </form>
            <hr style="margin: 20px 0;">

            <h3 style="border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 15px;">Informasi Pelanggan & Pengiriman</h3>
            <p><strong>Nama Pelanggan (Akun):</strong> <?php echo htmlspecialchars($pesanan['nama_pelanggan'] ?: '-'); ?></p>
            <p><strong>Email Pelanggan:</strong> <?php echo htmlspecialchars($pesanan['email_pelanggan'] ?: '-'); ?></p>
            <p><strong>Nama Penerima:</strong> <?php echo htmlspecialchars($pesanan['nama_penerima']); ?></p>
            <p><strong>Telepon Penerima:</strong> <?php echo htmlspecialchars($pesanan['telepon_penerima']); ?></p>
            <p><strong>Alamat Pengiriman:</strong><br><span style="white-space: pre-wrap;"><?php echo htmlspecialchars($pesanan['alamat_pengiriman']); ?></span></p>
        </div>

        <div class="detail-item-pesanan" style="flex: 2; min-width: 400px;">
            <h3 style="margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 15px;">Item yang Dipesan</h3>
            <?php if (!empty($detail_items)): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: left; width:15%;">Gambar</th>
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Nama Produk (Saat Pesan)</th>
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Harga Satuan (Saat Pesan)</th>
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Qty</th>
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detail_items as $item): ?>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;">
                                    <?php 
                                    $gambar_url_item = (!empty($item['gambar_produk_utama']) && file_exists('../assets/img/' . $item['gambar_produk_utama']))
                                                       ? BASE_URL . 'assets/img/' . $item['gambar_produk_utama']
                                                       : 'https://placehold.co/60x60/F0F0F0/CCC?text=N/A';
                                    ?>
                                    <img src="<?php echo $gambar_url_item; ?>" alt="<?php echo htmlspecialchars($item['nama_produk_saat_pesan']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($item['nama_produk_saat_pesan']); ?></td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;"><?php echo format_rupiah($item['harga_saat_pesan']); ?></td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo $item['quantity']; ?></td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;"><?php echo format_rupiah($item['subtotal']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Tidak ada detail item untuk pesanan ini.</p>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <p style="margin-top: 20px; font-size: 1.1em; color: #777;">Data pesanan tidak dapat ditampilkan.</p>
<?php endif; ?>


<?php
// $conn->close(); // Koneksi akan ditutup di footer_admin.php
require_once 'includes/footer_admin.php';
?>
