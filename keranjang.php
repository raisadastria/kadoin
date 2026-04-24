<?php
// toko_online/keranjang.php
require_once 'includes/config.php'; 

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Fungsi (tetap sama atau bisa dipindah ke file functions.php)
if (!function_exists('hitungHargaTambahanOpsi')) {
    function hitungHargaTambahanOpsi($ukuran, $addon) {
        $harga_tambahan = 0;
        if ($ukuran === 'Medium') $harga_tambahan += 20000;
        elseif ($ukuran === 'Large') $harga_tambahan += 50000;
        if ($addon === 'Coklat') $harga_tambahan += 15000;
        elseif ($addon === 'Boneka Kecil') $harga_tambahan += 30000;
        elseif ($addon === 'Kartu Ucapan Premium') $harga_tambahan += 10000;
        return $harga_tambahan;
    }
}

// --- LOGIKA AKSI KERANJANG (ADD, REMOVE, UPDATE_QTY, CLEAR_CART) ---
// (Logika PHP untuk aksi ini tetap sama seperti sebelumnya, tidak diubah signifikan)
// ... (Pastikan logika PHP dari versi sebelumnya ada di sini) ...
// Aksi dari form detail produk atau halaman produk
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id_produk = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : null;

    if ($action == 'add' && $id_produk) {
        $quantity = isset($_POST['quantity']) && is_numeric($_POST['quantity']) && $_POST['quantity'] > 0 ? (int)$_POST['quantity'] : 1;
        $ukuran_bouquet = isset($_POST['bouquet_size']) ? trim($_POST['bouquet_size']) : 'Standard';
        $add_on_item = isset($_POST['add_on_item']) ? trim($_POST['add_on_item']) : '';
        $catatan_item = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';

        $sql_produk = "SELECT nama_produk, harga, stok, gambar1 FROM produk WHERE id_produk = ?";
        $stmt = $conn->prepare($sql_produk);
        if ($stmt) {
            $stmt->bind_param("i", $id_produk);
            $stmt->execute();
            $result_produk = $stmt->get_result();
            if ($result_produk->num_rows == 1) {
                $produk_db = $result_produk->fetch_assoc();
                $qty_di_keranjang = isset($_SESSION['keranjang'][$id_produk]['quantity']) ? $_SESSION['keranjang'][$id_produk]['quantity'] : 0;
                
                if (($qty_di_keranjang + $quantity) > $produk_db['stok']) {
                    $_SESSION['temp_notification'] = ['type' => 'error', 'message' => "Maaf, stok produk '" . htmlspecialchars($produk_db['nama_produk']) . "' tidak mencukupi."];
                } else {
                    $cart_item_id = $id_produk; 

                    if (isset($_SESSION['keranjang'][$cart_item_id])) {
                        $_SESSION['keranjang'][$cart_item_id]['quantity'] += $quantity;
                        $_SESSION['keranjang'][$cart_item_id]['ukuran'] = $ukuran_bouquet;
                        $_SESSION['keranjang'][$cart_item_id]['add_on'] = $add_on_item;
                        $_SESSION['keranjang'][$cart_item_id]['catatan_item'] = $catatan_item;
                        $_SESSION['temp_notification'] = ['type' => 'success', 'message' => "Quantity produk '" . htmlspecialchars($produk_db['nama_produk']) . "' diperbarui."];
                    } else {
                        $_SESSION['keranjang'][$cart_item_id] = [
                            'id_produk' => $id_produk,
                            'nama' => $produk_db['nama_produk'],
                            'harga' => $produk_db['harga'], 
                            'quantity' => $quantity,
                            'gambar' => $produk_db['gambar1'],
                            'ukuran' => $ukuran_bouquet,
                            'add_on' => $add_on_item,
                            'catatan_item' => $catatan_item
                        ];
                        $_SESSION['temp_notification'] = ['type' => 'success', 'message' => "Produk '" . htmlspecialchars($produk_db['nama_produk']) . "' ditambahkan."];
                    }
                }
            } else { $_SESSION['temp_notification'] = ['type' => 'error', 'message' => "Produk tidak ditemukan."]; }
            $stmt->close();
        } else { $_SESSION['temp_notification'] = ['type' => 'error', 'message' => "Gagal mengambil data produk."]; }
        
        if (isset($_POST['submit_action']) && $_POST['submit_action'] == 'buy_now' && (!isset($_SESSION['temp_notification']['type']) || $_SESSION['temp_notification']['type'] != 'error')) {
            redirect('checkout.php');
        } else {
            redirect('keranjang.php');
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action'])) {
    $action_get = $_GET['action'];
    $id_item_cart_get = isset($_GET['id']) ? (int)$_GET['id'] : null; 

    if ($action_get == 'remove' && $id_item_cart_get && isset($_SESSION['keranjang'][$id_item_cart_get])) {
        $nama_produk_dihapus = $_SESSION['keranjang'][$id_item_cart_get]['nama'];
        unset($_SESSION['keranjang'][$id_item_cart_get]);
        $_SESSION['temp_notification'] = ['type' => 'success', 'message' => "Produk '" . htmlspecialchars($nama_produk_dihapus) . "' dihapus."];
        redirect('keranjang.php');
    }
    if ($action_get == 'update_qty' && $id_item_cart_get && isset($_SESSION['keranjang'][$id_item_cart_get])) {
        $new_quantity = isset($_GET['qty']) && is_numeric($_GET['qty']) && $_GET['qty'] > 0 ? (int)$_GET['qty'] : 1;
        $produk_id_asli = $_SESSION['keranjang'][$id_item_cart_get]['id_produk'];
        $sql_stok = "SELECT stok, nama_produk FROM produk WHERE id_produk = ?";
        $stmt_stok = $conn->prepare($sql_stok);
        if ($stmt_stok) {
            $stmt_stok->bind_param("i", $produk_id_asli);
            $stmt_stok->execute();
            $result_stok = $stmt_stok->get_result();
            if ($result_stok->num_rows == 1) {
                $produk_db_stok = $result_stok->fetch_assoc();
                if ($new_quantity > $produk_db_stok['stok']) {
                     $_SESSION['temp_notification'] = ['type' => 'error', 'message' => "Stok '" . htmlspecialchars($produk_db_stok['nama_produk']) . "' tidak cukup."];
                } else {
                    $_SESSION['keranjang'][$id_item_cart_get]['quantity'] = $new_quantity;
                    $_SESSION['temp_notification'] = ['type' => 'success', 'message' => "Quantity '" . htmlspecialchars($_SESSION['keranjang'][$id_item_cart_get]['nama']) . "' diperbarui."];
                }
            }
            $stmt_stok->close();
        }
        redirect('keranjang.php');
    }
    if ($action_get == 'clear_cart') {
        $_SESSION['keranjang'] = [];
        $_SESSION['temp_notification'] = ['type' => 'success', 'message' => "Keranjang dikosongkan."];
        redirect('keranjang.php');
    }
}


$notification_data = null;
if (isset($_SESSION['temp_notification'])) {
    $notification_data = $_SESSION['temp_notification'];
    unset($_SESSION['temp_notification']);
}

require_once 'includes/header.php'; 
?>

<div class="cart-page-container" style="background: linear-gradient(135deg, #fdeff9 0%, #EAD6EE 100%); padding: 30px 15px; min-height: 80vh; font-family: 'Poppins', sans-serif; color: #4A4A4A;">
    <div class="cart-wrapper" style="max-width: 1000px; margin: 0 auto; background-color: rgba(255,255,255,0.85); border-radius: 12px; padding: 25px; box-shadow: 0 8px 25px rgba(0,0,0,0.1);">
        
        <div class="cart-header-nav" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #e0cde3;">
            <div><a href="<?php echo BASE_URL; ?>produk.php" style="text-decoration: none; color: #77569a; font-weight: 500;">&laquo; Lanjutkan Belanja</a></div>
            <h1 style="text-align: center; color: #58375F; font-size: 1.8em; font-weight: 600; margin:0;">Keranjang Saya</h1>
            <div><?php if (!empty($_SESSION['keranjang'])): ?><a href="keranjang.php?action=clear_cart" onclick="return confirm('Kosongkan keranjang?');" style="text-decoration: none; color: #D85C5C; font-size: 0.9em;">Kosongkan Keranjang</a><?php endif; ?></div>
        </div>

        <?php if (isset($notification_data)): ?>
            <div class="alert <?php echo ($notification_data['type'] == 'success') ? 'alert-success' : 'alert-danger'; ?>" style="padding: 12px 18px; margin-bottom: 20px; border-radius: 8px; font-size: 0.95em; background-color: <?php echo ($notification_data['type'] == 'success') ? '#e6ffed' : '#ffebee'; ?>; color: <?php echo ($notification_data['type'] == 'success') ? '#2E7D32' : '#C62828'; ?>; border: 1px solid <?php echo ($notification_data['type'] == 'success') ? '#B9F6CA' : '#FFCDD2'; ?>;">
                <?php echo htmlspecialchars($notification_data['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($_SESSION['keranjang'])): ?>
            <p style="text-align: center; font-size: 1.1em; color: #665; padding: 50px 0;">Keranjang belanja Anda masih kosong.</p>
        <?php else: ?>
            <div class="cart-table-header" style="display: grid; grid-template-columns: 50px 1fr 120px 130px 120px 80px; gap: 15px; padding: 10px 15px; margin-bottom: 10px; font-weight: 600; font-size: 0.9em; color: #604E6A; border-bottom: 1px solid #e0cde3;">
                <span style="display:flex; align-items:center; justify-content:center;"><input type="checkbox" id="pilihSemuaHeader" onchange="togglePilihSemua(this.checked); updateRingkasanTerpilih();" style="transform: scale(1.2);"></span>
                <span>Produk</span>
                <span style="text-align: right;">Harga Satuan</span>
                <span style="text-align: center;">Kuantitas</span>
                <span style="text-align: right;">Total Harga</span>
                <span style="text-align: center;">Aksi</span>
            </div>

            <?php foreach ($_SESSION['keranjang'] as $id_item_cart => $item): ?>
                <?php
                    $harga_dasar_item_display = $item['harga'];
                    $harga_tambahan_item_display = hitungHargaTambahanOpsi(
                        isset($item['ukuran']) ? $item['ukuran'] : null,
                        isset($item['add_on']) ? $item['add_on'] : null
                    );
                    $harga_final_per_unit_display = $harga_dasar_item_display + $harga_tambahan_item_display;
                    $subtotal_item_display = $harga_final_per_unit_display * $item['quantity'];
                ?>
                <div class="cart-item-card" 
                     data-item-id="<?php echo $id_item_cart; ?>" 
                     data-harga-final-unit="<?php echo $harga_final_per_unit_display; ?>"
                     data-quantity="<?php echo $item['quantity']; ?>"
                     style="display: grid; grid-template-columns: 50px 1fr 120px 130px 120px 80px; gap: 15px; align-items: center; padding: 20px 15px; background-color: #fff; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
                    <span style="display:flex; align-items:center; justify-content:center;">
                        <input type="checkbox" name="item_selected[]" class="item-checkbox" value="<?php echo $id_item_cart; ?>" onchange="updateRingkasanTerpilih()" style="transform: scale(1.2);">
                    </span>
                    <div class="product-details" style="display: flex; align-items: center; gap: 15px;">
                        <?php
                        $gambar_url_cart = (!empty($item['gambar']) && file_exists('assets/img/' . $item['gambar']))
                                          ? BASE_URL . 'assets/img/' . $item['gambar']
                                          : 'https://placehold.co/90x90/F8F0FC/D6B7E5?text=KadoIn&font=lora';
                        ?>
                        <a href="detail_produk.php?id=<?php echo $item['id_produk']; ?>"><img src="<?php echo $gambar_url_cart; ?>" alt="<?php echo htmlspecialchars($item['nama']); ?>" style="width: 75px; height: 75px; object-fit: cover; border-radius: 8px;"></a>
                        <div>
                            <span style="display: block; font-size: 0.75em; color: #BE97E0; margin-bottom: 2px; font-weight: 500;">Kado In</span>
                            <a href="detail_produk.php?id=<?php echo $item['id_produk']; ?>" style="text-decoration: none; color: #4A4A4A; font-weight: 600; font-size: 1em; display: block; line-height: 1.3;"><?php echo htmlspecialchars($item['nama']); ?></a>
                            <?php if (!empty($item['ukuran']) && $item['ukuran'] !== 'Standard'): ?>
                                <p style="font-size: 0.8em; color: #777; margin: 3px 0 0 0;">Ukuran: <?php echo htmlspecialchars($item['ukuran']); ?> (+<?php echo format_rupiah(hitungHargaTambahanOpsi($item['ukuran'], null)); ?>)</p>
                            <?php endif; ?>
                            <?php if (!empty($item['add_on'])): ?>
                                <p style="font-size: 0.8em; color: #777; margin: 3px 0 0 0;">Add-on: <?php echo htmlspecialchars($item['add_on']); ?> (+<?php echo format_rupiah(hitungHargaTambahanOpsi(null, $item['add_on'])); ?>)</p>
                            <?php endif; ?>
                            <?php if (!empty($item['catatan_item'])): ?>
                                <p style="font-size: 0.8em; color: #777; margin-top: 4px; white-space: pre-wrap; max-height: 30px; overflow:hidden; text-overflow:ellipsis;"><em>Catatan: <?php echo htmlspecialchars($item['catatan_item']); ?></em></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span style="text-align: right; font-size: 0.95em;"><?php echo format_rupiah($harga_final_per_unit_display); ?></span>
                    <div class="quantity-adjuster" style="display: flex; align-items: center; justify-content: center;">
                        <a href="keranjang.php?action=update_qty&id=<?php echo $id_item_cart; ?>&qty=<?php echo max(1, $item['quantity'] - 1); ?>" style="background-color: #f0e6ff; color: #77569a; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; border-radius: 50%; font-weight: bold; font-size: 1.1em; border: 1px solid #e0cde3;">-</a>
                        <input type="text" value="<?php echo $item['quantity']; ?>" readonly style="width: 35px; text-align: center; font-size: 1em; border: none; background: transparent; margin: 0 5px; font-weight:500; color: #4A4A4A;">
                        <a href="keranjang.php?action=update_qty&id=<?php echo $id_item_cart; ?>&qty=<?php echo $item['quantity'] + 1; ?>" style="background-color: #f0e6ff; color: #77569a; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; border-radius: 50%; font-weight: bold; font-size: 1.1em; border: 1px solid #e0cde3;">+</a>
                    </div>
                    <span style="text-align: right; font-weight: 600; color: #77569a; font-size: 1em;"><?php echo format_rupiah($subtotal_item_display); ?></span>
                    <span style="text-align: center;"><a href="keranjang.php?action=remove&id=<?php echo $id_item_cart; ?>" onclick="return confirm('Hapus item ini?');" style="color: #E57373; text-decoration: none; font-size: 0.9em; font-weight:500;">Hapus</a></span>
                </div>
            <?php endforeach; ?>

            <div class="cart-summary-footer" style="margin-top: 30px; padding: 20px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div style="display: flex; align-items: center;">
                    <input type="checkbox" id="pilihSemuaFooter" onchange="togglePilihSemua(this.checked); updateRingkasanTerpilih();" style="transform: scale(1.2); margin-right: 10px;">
                    <label for="pilihSemuaFooter" style="font-size: 0.95em; font-weight: 500; cursor:pointer;">Pilih Semua</label>
                    <a href="#" onclick="hapusTerpilih(); return false;" style="margin-left: 20px; color: #E57373; text-decoration: none; font-size: 0.9em;">Hapus (Terpilih)</a> 
                </div>
                <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
                    <div style="font-size: 1em; color: #4A4A4A;">
                        Total (<span id="jumlahProdukTerpilih">0</span> Produk): 
                        <strong id="hargaTotalTerpilih" style="font-size: 1.3em; color: #77569a; margin-left: 5px;">Rp 0</strong>
                    </div>
                    <a href="#" id="checkoutButton" onclick="prosesCheckoutTerpilih(); return false;" 
                       style="padding: 12px 25px; background: linear-gradient(135deg, #C084FC 0%, #8B5CF6 100%); color: #fff; text-decoration: none; border-radius: 8px; font-size: 1.1em; font-weight: 600; box-shadow: 0 4px 10px rgba(139, 92, 246, 0.3);">
                       Checkout
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Fungsi format Rupiah di JavaScript (pastikan sudah ada atau di-include)
    if (typeof formatRupiahJS !== 'function') {
        function formatRupiahJS(angka) {
            var number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return 'Rp ' + rupiah;
        }
    }

    function updateRingkasanTerpilih() {
        var itemCheckboxes = document.querySelectorAll('.item-checkbox:checked');
        var totalProdukTerpilih = 0;
        var hargaTotalTerpilih = 0;

        itemCheckboxes.forEach(function(checkbox) {
            var itemCard = checkbox.closest('.cart-item-card');
            var hargaUnit = parseFloat(itemCard.dataset.hargaFinalUnit);
            var quantity = parseInt(itemCard.dataset.quantity);
            
            totalProdukTerpilih += quantity;
            hargaTotalTerpilih += hargaUnit * quantity;
        });

        document.getElementById('jumlahProdukTerpilih').innerText = totalProdukTerpilih;
        document.getElementById('hargaTotalTerpilih').innerText = formatRupiahJS(hargaTotalTerpilih);

        // Update status checkbox "Pilih Semua"
        var semuaItem = document.querySelectorAll('.item-checkbox');
        var semuaTerpilih = semuaItem.length > 0 && itemCheckboxes.length === semuaItem.length;
        document.getElementById('pilihSemuaHeader').checked = semuaTerpilih;
        document.getElementById('pilihSemuaFooter').checked = semuaTerpilih;
    }

    function togglePilihSemua(checked) {
        var itemCheckboxes = document.querySelectorAll('.item-checkbox');
        itemCheckboxes.forEach(function(checkbox) {
            checkbox.checked = checked;
        });
        updateRingkasanTerpilih();
    }

    function hapusTerpilih() {
        var selectedIds = [];
        document.querySelectorAll('.item-checkbox:checked').forEach(function(checkbox) {
            selectedIds.push(checkbox.value);
        });

        if (selectedIds.length === 0) {
            alert("Pilih dulu item yang ingin dihapus.");
            return;
        }
        if (confirm("Yakin ingin menghapus " + selectedIds.length + " item terpilih?")) {
            // Untuk penghapusan multiple item, cara paling mudah adalah redirect dengan parameter array
            // atau kirim via POST menggunakan form tersembunyi, atau AJAX.
            // Contoh redirect dengan parameter (perlu penanganan khusus di PHP untuk array GET):
            window.location.href = 'keranjang.php?action=remove_selected&ids=' + selectedIds.join(',');
            // Di PHP: $_GET['ids'] akan berisi string "id1,id2,id3", perlu di-explode.
            // Atau, lebih baik loop dan redirect untuk setiap item (kurang efisien tapi simpel):
            // selectedIds.forEach(id => { window.location.href = 'keranjang.php?action=remove&id=' + id; }); // Ini akan redirect berkali-kali
            // Pilihan terbaik biasanya AJAX atau form POST. Untuk sekarang, kita buat alert.
            // alert("Fungsi hapus item terpilih akan mengarahkan ke URL dengan ID terpilih.");
        }
    }
    
    function prosesCheckoutTerpilih() {
        var selectedIds = [];
        document.querySelectorAll('.item-checkbox:checked').forEach(function(checkbox) {
            selectedIds.push(checkbox.value);
        });

        if (selectedIds.length === 0) {
            alert("Pilih dulu item yang ingin Anda checkout.");
            return false; // Mencegah navigasi jika tidak ada yang dipilih
        }
        // Arahkan ke checkout.php dengan membawa ID item yang terpilih
        window.location.href = 'checkout.php?selected_items=' + selectedIds.join(',');
    }


    // Panggil update saat halaman dimuat untuk inisialisasi
    document.addEventListener('DOMContentLoaded', function() {
        updateRingkasanTerpilih(); // Hitung total awal (jika ada yang sudah tercentang dari state sebelumnya)
        
        // Tambahkan event listener ke semua checkbox item
        document.querySelectorAll('.item-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', updateRingkasanTerpilih);
        });
    });
</script>

<?php
require_once 'includes/footer.php';
?>
