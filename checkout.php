<?php
// toko_online/checkout.php
require_once 'includes/config.php'; // Memuat config (termasuk session_start())

// 1. PERIKSA APAKAH USER SUDAH LOGIN
// Jika belum ada sistem login user, bagian ini bisa di-skip atau dimodifikasi
// Untuk sekarang, kita asumsikan user harus login.
if (!isset($_SESSION['user_id'])) {
    // Simpan URL checkout agar bisa kembali setelah login
    $_SESSION['redirect_url'] = BASE_URL . 'checkout.php';
    $_SESSION['temp_notification'] = ['type' => 'error', 'message' => 'Anda harus login terlebih dahulu untuk melanjutkan ke checkout.'];
    redirect('login.php'); // Arahkan ke halaman login user (perlu dibuat)
    // exit;
}

// 2. PERIKSA APAKAH KERANJANG ADA ISINYA
if (empty($_SESSION['keranjang'])) {
    $_SESSION['temp_notification'] = ['type' => 'info', 'message' => 'Keranjang belanja Anda kosong. Silakan pilih produk terlebih dahulu.'];
    redirect('produk.php');
    // exit;
}

// Inisialisasi variabel form dan error
$nama_penerima = isset($_SESSION['user_nama']) ? $_SESSION['user_nama'] : ''; // Ambil dari session jika ada
$email_penerima = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''; // Ambil dari session jika ada
$telepon_penerima = '';
$alamat_pengiriman = '';
$catatan_pesanan_user = ''; // Catatan umum untuk pesanan
$errors = [];

// Fungsi untuk menghitung subtotal keranjang (bisa dipindah ke file fungsi jika sering dipakai)
if (!function_exists('hitungSubtotalKeranjang')) { // Cek jika fungsi belum ada
    function hitungSubtotalKeranjang() {
        $subtotal = 0;
        if (!empty($_SESSION['keranjang'])) {
            foreach ($_SESSION['keranjang'] as $item) {
                $subtotal += $item['harga'] * $item['quantity'];
            }
        }
        return $subtotal;
    }
}
$subtotal_keranjang = hitungSubtotalKeranjang();
$total_harga_pesanan = $subtotal_keranjang; // Untuk saat ini, total harga = subtotal

// 3. PROSES FORM JIKA DISUBMIT
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_penerima = trim($_POST['nama_penerima']);
    $email_penerima = trim($_POST['email_penerima']); // Email bisa diambil dari form atau session user
    $telepon_penerima = trim($_POST['telepon_penerima']);
    $alamat_pengiriman = trim($_POST['alamat_pengiriman']);
    $catatan_pesanan_user = trim($_POST['catatan_pesanan']);

    // Validasi dasar
    if (empty($nama_penerima)) $errors[] = "Nama penerima tidak boleh kosong.";
    if (empty($email_penerima)) {
        $errors[] = "Email penerima tidak boleh kosong.";
    } elseif (!filter_var($email_penerima, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email penerima tidak valid.";
    }
    if (empty($telepon_penerima)) $errors[] = "Nomor telepon tidak boleh kosong.";
    if (empty($alamat_pengiriman)) $errors[] = "Alamat pengiriman tidak boleh kosong.";

    // Jika tidak ada error, proses penyimpanan pesanan
    if (empty($errors)) {
        $conn->begin_transaction(); // Mulai transaksi database

        try {
            // A. Simpan ke tabel 'pesanan'
            $sql_pesanan = "INSERT INTO pesanan (id_user, nama_penerima, alamat_pengiriman, telepon_penerima, total_harga, status_pesanan, catatan_pesanan) 
                            VALUES (?, ?, ?, ?, ?, 'pending', ?)";
            $stmt_pesanan = $conn->prepare($sql_pesanan);
            if (!$stmt_pesanan) {
                throw new Exception("Gagal mempersiapkan statement pesanan: " . $conn->error);
            }
            $id_user_db = $_SESSION['user_id']; // Pastikan ini ID user yang valid dari tabel users
            
            $stmt_pesanan->bind_param("isssds", 
                $id_user_db, 
                $nama_penerima, 
                $alamat_pengiriman, 
                $telepon_penerima, 
                $total_harga_pesanan,
                $catatan_pesanan_user
            );

            if (!$stmt_pesanan->execute()) {
                throw new Exception("Gagal menyimpan data pesanan: " . $stmt_pesanan->error);
            }
            $id_pesanan_baru = $stmt_pesanan->insert_id; // Dapatkan ID pesanan yang baru saja dibuat
            $stmt_pesanan->close();

            // B. Simpan setiap item di keranjang ke tabel 'detail_pesanan'
            $sql_detail = "INSERT INTO detail_pesanan (id_pesanan, id_produk, nama_produk_saat_pesan, harga_saat_pesan, quantity, subtotal) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_detail = $conn->prepare($sql_detail);
            if (!$stmt_detail) {
                throw new Exception("Gagal mempersiapkan statement detail pesanan: " . $conn->error);
            }

            foreach ($_SESSION['keranjang'] as $id_produk_keranjang => $item) {
                $subtotal_item = $item['harga'] * $item['quantity'];
                // Ambil nama produk terbaru dari DB untuk disimpan (atau gunakan nama dari session jika sudah cukup)
                // Untuk konsistensi, harga dan nama produk saat pesan disimpan
                $nama_produk_saat_pesan = $item['nama']; // Ambil dari data keranjang
                $harga_saat_pesan = $item['harga'];     // Ambil dari data keranjang

                $stmt_detail->bind_param("iisdis", 
                    $id_pesanan_baru, 
                    $id_produk_keranjang, 
                    $nama_produk_saat_pesan, 
                    $harga_saat_pesan, 
                    $item['quantity'], 
                    $subtotal_item
                );
                if (!$stmt_detail->execute()) {
                    throw new Exception("Gagal menyimpan detail item pesanan (Produk ID: " . $id_produk_keranjang . "): " . $stmt_detail->error);
                }
                
                // C. Kurangi stok produk (Opsional, tapi penting)
                $sql_update_stok = "UPDATE produk SET stok = stok - ? WHERE id_produk = ? AND stok >= ?";
                $stmt_update_stok = $conn->prepare($sql_update_stok);
                if (!$stmt_update_stok) {
                    throw new Exception("Gagal mempersiapkan statement update stok: " . $conn->error);
                }
                $stmt_update_stok->bind_param("iii", $item['quantity'], $id_produk_keranjang, $item['quantity']);
                if (!$stmt_update_stok->execute() || $stmt_update_stok->affected_rows == 0) {
                    // Jika affected_rows == 0, berarti stok tidak cukup atau produk tidak ada (seharusnya sudah dicek)
                    throw new Exception("Gagal mengurangi stok produk (Produk ID: " . $id_produk_keranjang . "). Mungkin stok berubah.");
                }
                $stmt_update_stok->close();
            }
            $stmt_detail->close();

            // Jika semua berhasil, commit transaksi
            $conn->commit();

            // Kosongkan keranjang belanja
            $_SESSION['keranjang'] = [];

            // Simpan notifikasi sukses dan redirect ke halaman konfirmasi/terima kasih
            $_SESSION['temp_notification'] = ['type' => 'success', 'message' => 'Pesanan Anda (ID: #' . $id_pesanan_baru . ') berhasil dibuat! Terima kasih telah berbelanja.'];
            // Buat halaman order_success.php atau tampilkan detail pesanan
            redirect('order_success.php?order_id=' . $id_pesanan_baru); 
            // exit;

        } catch (Exception $e) {
            $conn->rollback(); // Batalkan semua perubahan jika ada error
            $errors[] = "Terjadi kesalahan saat memproses pesanan: " . $e->getMessage();
            // Sebaiknya log error ini untuk admin
        }
    }
}


// Ambil notifikasi dari session jika ada (misal dari redirect login)
if (isset($_SESSION['temp_notification'])) {
    $notification_data_checkout = $_SESSION['temp_notification'];
    unset($_SESSION['temp_notification']); 
}

// Panggil header SETELAH semua logika PHP selesai
require_once 'includes/header.php';
?>

<div class="container" style="max-width: 800px; margin: 30px auto; padding: 25px; background-color: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); font-family: 'Poppins', sans-serif;">
    <h1 style="text-align: center; margin-bottom: 30px; color: #58375F; border-bottom: 2px solid #f0e6ff; padding-bottom:15px; font-weight: 600;">Checkout</h1>

    <?php if (isset($notification_data_checkout)): ?>
        <div class="alert <?php echo ($notification_data_checkout['type'] == 'success') ? 'alert-success' : 'alert-danger'; ?>" 
             style="padding: 15px; margin-bottom: 20px; border-radius: 8px; font-size: 0.95em;
                    background-color: <?php echo ($notification_data_checkout['type'] == 'success') ? '#e6ffed' : '#ffebee'; ?>;
                    color: <?php echo ($notification_data_checkout['type'] == 'success') ? '#2E7D32' : '#C62828'; ?>;
                    border: 1px solid <?php echo ($notification_data_checkout['type'] == 'success') ? '#B9F6CA' : '#FFCDD2'; ?>;">
            <?php echo htmlspecialchars($notification_data_checkout['message']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="background-color: #ffebee; color: #C62828; padding: 15px; border: 1px solid #FFCDD2; border-radius: 8px; margin-bottom: 20px;">
            <strong>Mohon perbaiki error berikut:</strong>
            <ul style="margin-top: 10px; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="checkout.php" method="POST">
        <h2 style="font-size: 1.5em; color: #77569a; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Informasi Penerima</h2>
        
        <div class="form-group" style="margin-bottom: 18px;">
            <label for="nama_penerima" style="display: block; margin-bottom: 6px; font-weight: 500; color: #4A4A4A;">Nama Lengkap Penerima:</label>
            <input type="text" id="nama_penerima" name="nama_penerima" value="<?php echo htmlspecialchars($nama_penerima); ?>" required 
                   style="width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 1em;">
        </div>

        <div class="form-group" style="margin-bottom: 18px;">
            <label for="email_penerima" style="display: block; margin-bottom: 6px; font-weight: 500; color: #4A4A4A;">Email Penerima:</label>
            <input type="email" id="email_penerima" name="email_penerima" value="<?php echo htmlspecialchars($email_penerima); ?>" required 
                   style="width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 1em;">
        </div>

        <div class="form-group" style="margin-bottom: 18px;">
            <label for="telepon_penerima" style="display: block; margin-bottom: 6px; font-weight: 500; color: #4A4A4A;">Nomor Telepon (WhatsApp):</label>
            <input type="tel" id="telepon_penerima" name="telepon_penerima" value="<?php echo htmlspecialchars($telepon_penerima); ?>" required 
                   placeholder="Contoh: 081234567890"
                   style="width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 1em;">
        </div>

        <div class="form-group" style="margin-bottom: 25px;">
            <label for="alamat_pengiriman" style="display: block; margin-bottom: 6px; font-weight: 500; color: #4A4A4A;">Alamat Lengkap Pengiriman:</label>
            <textarea id="alamat_pengiriman" name="alamat_pengiriman" rows="4" required 
                      placeholder="Nama Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan, Kota, Kode Pos"
                      style="width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; resize: vertical; font-size: 1em;"><?php echo htmlspecialchars($alamat_pengiriman); ?></textarea>
        </div>

        <h2 style="font-size: 1.5em; color: #77569a; margin-top: 30px; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Ringkasan Pesanan</h2>
        
        <div class="order-summary" style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
            <?php if (!empty($_SESSION['keranjang'])): ?>
                <?php foreach ($_SESSION['keranjang'] as $id_sum => $item_sum): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px dashed #e0e0e0;">
                        <span style="color: #555;"><?php echo htmlspecialchars($item_sum['nama']); ?> (x<?php echo $item_sum['quantity']; ?>)</span>
                        <span style="color: #555; font-weight: 500;"><?php echo format_rupiah($item_sum['harga'] * $item_sum['quantity']); ?></span>
                    </div>
                <?php endforeach; ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 15px; margin-top:10px; border-top: 2px solid #d1c4e9;">
                    <strong style="font-size: 1.2em; color: #4A4A4A;">Total Pembayaran:</strong>
                    <strong style="font-size: 1.4em; color: #77569a;"><?php echo format_rupiah($total_harga_pesanan); ?></strong>
                </div>
            <?php endif; ?>
        </div>

         <div class="form-group" style="margin-bottom: 30px;">
            <label for="catatan_pesanan" style="display: block; font-weight: 500; margin-bottom: 8px; color: #4A4A4A;">Catatan Tambahan untuk Pesanan (Opsional):</label>
            <textarea id="catatan_pesanan" name="catatan_pesanan" rows="3" 
                      placeholder="Misal: Preferensi waktu pengiriman, dll."
                      style="width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; resize: vertical; font-size: 1em;"><?php echo htmlspecialchars($catatan_pesanan_user); ?></textarea>
        </div>

        <button type="submit" 
                style="display: block; width: 100%; padding: 18px 20px; 
                       background: linear-gradient(135deg, #C084FC 0%, #8B5CF6 100%); 
                       color: #fff; border: none; border-radius: 8px; 
                       font-size: 1.25em; font-weight: 600; cursor: pointer; 
                       box-shadow: 0 4px 15px rgba(139, 92, 246, 0.35);
                       transition: transform 0.2s, box-shadow 0.2s;">
            Buat Pesanan & Proses Pembayaran
        </button>
    </form>

     <div style="text-align: center; margin-top: 30px;">
        <a href="keranjang.php" style="color: #77569a; text-decoration: none; font-weight:500;">&laquo; Kembali ke Keranjang</a>
    </div>
</div>

<?php
require_once 'includes/footer.php'; // Memuat footer umum
?>
