<?php
// toko_online/order_success.php
require_once 'includes/config.php'; // Memuat config (termasuk session_start())

// Ambil notifikasi dari session jika ada (misal dari proses checkout)
$notification_data = null;
if (isset($_SESSION['temp_notification'])) {
    $notification_data = $_SESSION['temp_notification'];
    unset($_SESSION['temp_notification']); // Hapus setelah diambil
}

$order_id = null;
$pesanan = null;
$detail_pesanan_items = [];
$errors = [];

// 1. Ambil Order ID dari URL
if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
} else {
    if (!$notification_data) {
        $errors[] = "ID Pesanan tidak valid atau tidak ditemukan.";
    }
}

// 2. Jika Order ID valid, ambil data pesanan dari database
if ($order_id && empty($errors)) {
    $sql_pesanan = "SELECT p.id_pesanan, p.nama_penerima, p.total_harga, p.waktu_pesanan, p.status_pesanan,
                           p.id_user as id_user_pesanan, u.email as email_user 
                     FROM pesanan p 
                     LEFT JOIN users u ON p.id_user = u.id_user
                     WHERE p.id_pesanan = ?";
    
    $user_id_session = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $user_role_session = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

    if ($user_id_session && $user_role_session != 'admin') {
        $sql_pesanan .= " AND p.id_user = ?";
    }
    
    $stmt_pesanan = $conn->prepare($sql_pesanan);
    if ($stmt_pesanan) {
        if ($user_id_session && $user_role_session != 'admin') {
            $stmt_pesanan->bind_param("ii", $order_id, $user_id_session);
        } else {
            $stmt_pesanan->bind_param("i", $order_id);
        }
        $stmt_pesanan->execute();
        $result_pesanan = $stmt_pesanan->get_result();

        if ($result_pesanan->num_rows == 1) {
            $pesanan = $result_pesanan->fetch_assoc();

            $sql_detail = "SELECT nama_produk_saat_pesan, quantity, harga_saat_pesan, subtotal 
                           FROM detail_pesanan WHERE id_pesanan = ?";
            $stmt_detail = $conn->prepare($sql_detail);
            if ($stmt_detail) {
                $stmt_detail->bind_param("i", $order_id);
                $stmt_detail->execute();
                $result_detail = $stmt_detail->get_result();
                while ($item = $result_detail->fetch_assoc()) {
                    $detail_pesanan_items[] = $item;
                }
                $stmt_detail->close();
            } else {
                $errors[] = "Gagal mengambil detail item pesanan: " . $conn->error;
            }
        } else {
            $errors[] = "Pesanan dengan ID #" . htmlspecialchars($order_id) . " tidak ditemukan atau Anda tidak memiliki akses.";
            $order_id = null; 
        }
        $stmt_pesanan->close();
    } else {
        $errors[] = "Gagal mempersiapkan query pesanan: " . $conn->error;
    }
}

if (!function_exists('format_rupiah')) {
    function format_rupiah($angka){
        return "Rp " . number_format($angka,0,',','.');
    }
}

require_once 'includes/header.php';
?>

<div class="container" style="max-width: 700px; margin: 40px auto; padding: 30px; background-color: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); font-family: 'Poppins', sans-serif; text-align: center;">

    <?php if ($notification_data && $notification_data['type'] == 'success'): ?>
        <div class="icon-success" style="margin-bottom: 20px;" data-aos="zoom-in">
            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#4CAF50" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </svg>
        </div>
        <h1 style="color: #4CAF50; font-size: 2.2em; margin-bottom: 15px; font-weight: 600;" data-aos="fade-up">Pesanan Berhasil!</h1>
        <p style="font-size: 1.1em; color: #555; line-height: 1.6; margin-bottom: 25px;" data-aos="fade-up" data-aos-delay="100">
            <?php echo htmlspecialchars($notification_data['message']); ?>
        </p>
    <?php elseif (!empty($errors)): ?>
        <div class="icon-error" style="margin-bottom: 20px;" data-aos="zoom-in">
             <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#D32F2F" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                 <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
            </svg>
        </div>
        <h2 style="color: #D32F2F; font-size: 1.8em; margin-bottom: 15px;" data-aos="fade-up">Terjadi Kesalahan</h2>
        <div class="alert alert-danger" style="background-color: #ffebee; color: #C62828; padding: 15px; border: 1px solid #FFCDD2; border-radius: 8px; margin-bottom: 20px; text-align:left;" data-aos="fade-up" data-aos-delay="100">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: 
        if (!$pesanan && $order_id):
        elseif (!$pesanan): ?>
            <h1 style="color: #58375F; font-size: 2em; margin-bottom: 20px;" data-aos="fade-up">Status Pesanan</h1>
            <p style="font-size: 1.1em; color: #555; line-height: 1.6; margin-bottom: 25px;" data-aos="fade-up" data-aos-delay="100">
                Tidak ada informasi pesanan yang dapat ditampilkan saat ini. Silakan periksa kembali ID Pesanan Anda atau hubungi kami.
            </p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($pesanan): ?>
        <div class="order-details-summary" data-aos="fade-up" data-aos-delay="200" style="text-align: left; border: 1px solid #e0cde3; border-radius: 8px; padding: 20px; margin-bottom: 20px; background-color: #fdfaff;">
            <h3 style="font-size: 1.3em; color: #58375F; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #e0cde3; padding-bottom: 10px;">Detail Pesanan Anda:</h3>
            <p><strong>Nomor Pesanan:</strong> #<?php echo htmlspecialchars($pesanan['id_pesanan']); ?></p>
            <p><strong>Tanggal Pesanan:</strong> <?php echo date("d F Y, H:i", strtotime($pesanan['waktu_pesanan'])); ?></p>
            <p><strong>Nama Penerima:</strong> <?php echo htmlspecialchars($pesanan['nama_penerima']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($pesanan['email_user'] ? $pesanan['email_user'] : '- (Tamu)'); ?></p>
            <p><strong>Total Pembayaran:</strong> <strong style="color: #77569a;"><?php echo format_rupiah($pesanan['total_harga']); ?></strong></p>
            <p><strong>Status Pesanan:</strong> <span style="font-weight: bold; color: <?php 
                $status_color = '#555'; 
                if ($pesanan['status_pesanan'] == 'pending') $status_color = '#FF8F00'; 
                else if ($pesanan['status_pesanan'] == 'paid' || $pesanan['status_pesanan'] == 'processing') $status_color = '#1976D2';
                else if ($pesanan['status_pesanan'] == 'completed' || $pesanan['status_pesanan'] == 'shipped') $status_color = '#4CAF50'; 
                else if ($pesanan['status_pesanan'] == 'cancelled' || $pesanan['status_pesanan'] == 'refunded') $status_color = '#D32F2F'; 
                echo $status_color; 
            ?>; text-transform: capitalize;"><?php echo htmlspecialchars($pesanan['status_pesanan']); ?></span></p>
            
            <?php if (!empty($detail_pesanan_items)): ?>
                <h4 style="font-size: 1.1em; color: #58375F; margin-top: 20px; margin-bottom: 10px;">Item yang Dipesan:</h4>
                <ul style="list-style: none; padding-left: 0; margin-bottom: 0;">
                    <?php foreach ($detail_pesanan_items as $item_detail): ?>
                        <li style="padding: 8px 0; border-bottom: 1px dashed #e0e0e0; display:flex; justify-content:space-between; align-items:center;">
                            <span style="flex-grow: 1; padding-right:10px;"><?php echo htmlspecialchars($item_detail['nama_produk_saat_pesan']); ?> (x<?php echo $item_detail['quantity']; ?>)</span>
                            <span style="white-space:nowrap;"><?php echo format_rupiah($item_detail['subtotal']); ?></span>
                        </li>
                    <?php endforeach; ?>
                     <li style="padding: 8px 0; display:flex; justify-content:space-between; align-items:center; font-weight:bold; margin-top:5px; border-top: 1px solid #ccc;">
                        <span>TOTAL</span>
                        <span><?php echo format_rupiah($pesanan['total_harga']); ?></span>
                    </li>
                </ul>
            <?php endif; ?>
        </div>

        <?php if ($pesanan['status_pesanan'] == 'pending'): ?>
            <div class="payment-info-section" data-aos="fade-up" data-aos-delay="250" style="text-align: left; border: 1px solid #e0cde3; border-radius: 8px; padding: 20px; margin-bottom: 30px; background-color: #fff;">
                <h3 style="font-size: 1.3em; color: #58375F; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #e0cde3; padding-bottom: 10px;">Informasi Pembayaran</h3>
                <p style="margin-bottom: 15px; font-size: 1.05em;">Silakan lakukan pembayaran sejumlah <strong><?php echo format_rupiah($pesanan['total_harga']); ?></strong> ke salah satu rekening/e-wallet berikut:</p>
                
                <h4 style="font-size: 1.1em; color: #333; margin-top: 20px; margin-bottom: 10px;">Transfer Bank:</h4>
                <ul style="list-style: none; padding-left: 0; margin-bottom: 20px;">
                    <li style="margin-bottom: 8px;"><strong>BCA:</strong> 123-456-7890 <span style="color: #777;">(a/n: Raisa )</span></li>
                    </ul>

                <h4 style="font-size: 1.1em; color: #333; margin-top: 20px; margin-bottom: 10px;">E-Wallet:</h4>
                <ul style="list-style: none; padding-left: 0; margin-bottom: 10px;">
                    <li style="margin-bottom: 8px;"><strong>ShopeePay:</strong> 0812-3456-0001 <span style="color: #777;">(a/n: Raisa)</span></li>
                    <li style="margin-bottom: 8px;"><strong>DANA:</strong> 0812-3456-0002 <span style="color: #777;">(a/n: Raisa)</span></li>
                    <li style="margin-bottom: 8px;"><strong>GoPay:</strong> 0812-3456-0003 <span style="color: #777;">(a/n: KRaisa)</span></li>
                </ul>
                <p style="margin-top: 20px; font-size: 0.95em; color: #555;">Setelah melakukan pembayaran, mohon segera lakukan konfirmasi melalui WhatsApp agar pesanan Anda dapat segera kami proses.</p>
            </div>
        <?php else: ?>
            <p style="font-size: 1em; color: #555; line-height: 1.6; margin-bottom: 25px;" data-aos="fade-up" data-aos-delay="250">
                Kami akan segera memproses pesanan Anda. Anda akan menerima notifikasi di riwayat pesanan untuk setiap pembaruan status pesanan.
            </p>
        <?php endif; ?>
    <?php endif; ?>

    <div class="main-cta-actions" style="margin-top: 10px; display: flex; flex-direction: column; gap: 15px;" data-aos="fade-up" data-aos-delay="300">
        

        <?php
        $admin_wa_number = "6288223482475"; // GANTI DENGAN NOMOR WA ADMIN YANG BENAR

        if ($pesanan && $pesanan['status_pesanan'] == 'pending'):
            $wa_message = "Halo Admin KadoIn,\n\nSaya ingin konfirmasi pembayaran untuk Pesanan #" . htmlspecialchars($pesanan['id_pesanan']);
            $wa_message .= "\nAtas nama: " . htmlspecialchars($pesanan['nama_penerima']);
            $wa_message .= "\nTotal: " . format_rupiah($pesanan['total_harga']);
            $wa_message .= "\n\nMohon bantuannya untuk memeriksa pembayaran saya. Terima kasih.";
            $whatsapp_link = "https://wa.me/6288223482475" . $admin_wa_number . "?text=" . urlencode($wa_message);
        ?>
            <a href="https://wa.link/rj8vvb" target="_blank"
               style="display: flex; align-items:center; justify-content:center; width: 100%; padding: 14px 20px; background-color: #25D366; color: #fff; text-decoration: none; border-radius: 6px; font-size: 1.1em; font-weight: 600; transition: background-color 0.2s; box-sizing: border-box;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16" style="margin-right: 10px;">
                    <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                </svg>
                Konfirmasi Pembayaran via WhatsApp
            </a>
        <?php endif; ?>

        <div class="bottom-actions" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 15px; width: 100%;">
            <a href="<?php echo BASE_URL; ?>" 
               style="flex-grow: 1; padding: 12px 20px; background-color: #f0f0f0; color: #555; text-decoration: none; border-radius: 6px; font-size: 1em; font-weight: 500; transition: background-color 0.2s; min-width: 180px; box-sizing: border-box;">
                Kembali ke Halaman Utama
            </a>
            <?php 
            if (isset($_SESSION['user_id'])) {
                echo '<a href="'.BASE_URL.'riwayat_pesanan.php" style="flex-grow: 1; padding: 12px 20px; background-color: #6c757d; color: #fff; text-decoration: none; border-radius: 6px; font-size: 1em; font-weight: 500; transition: background-color 0.2s; min-width: 180px; box-sizing: border-box;">Riwayat Pesanan</a>';
            }
            ?>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>