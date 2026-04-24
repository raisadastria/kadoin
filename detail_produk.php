<?php
// toko_online/detail_produk.php
require_once 'includes/header.php'; // Memuat header umum

$id_produk = null;
$produk = null;
$errors = [];
$rekomendasi_produk = []; 

// 1. Ambil ID Produk dari URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_produk = (int)$_GET['id'];
} else {
    $errors[] = "ID produk tidak valid atau tidak ditemukan.";
}

// 2. Jika ID valid, ambil data produk dari database
if ($id_produk && empty($errors)) {
    $sql = "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.id_kategori = k.id_kategori WHERE p.id_produk = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $id_produk);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $produk = $result->fetch_assoc();
            $produk['gambar_all'] = [];
            // Mengisi array gambar
            for ($i = 1; $i <= 4; $i++) {
                if (!empty($produk['gambar'.$i]) && file_exists('assets/img/' . $produk['gambar'.$i])) {
                    $produk['gambar_all'][] = BASE_URL . 'assets/img/' . $produk['gambar'.$i];
                }
            }
            if (empty($produk['gambar_all'])) {
                $produk['gambar_all'][] = 'https://placehold.co/700x700/F9F3FF/D6B7E5?text=KadoIn&font=lora';
            }

            // Ambil produk rekomendasi
            $sql_rekomendasi = "SELECT id_produk, nama_produk, harga, gambar1 FROM produk WHERE id_produk != ? ORDER BY RAND() LIMIT 3";
            $stmt_rekomendasi = $conn->prepare($sql_rekomendasi);
            if ($stmt_rekomendasi) {
                $stmt_rekomendasi->bind_param("i", $id_produk);
                $stmt_rekomendasi->execute();
                $result_rekomendasi = $stmt_rekomendasi->get_result();
                while ($row_rek = $result_rekomendasi->fetch_assoc()) {
                    $rekomendasi_produk[] = $row_rek;
                }
                $stmt_rekomendasi->close();
            }
        } else {
            $errors[] = "Produk dengan ID " . htmlspecialchars($id_produk) . " tidak ditemukan.";
        }
        $stmt->close();
    } else {
        $errors[] = "Gagal mempersiapkan query produk: " . $conn->error;
    }
}
?>

<div class="page-background">
    <div class="container product-detail-container">

        <a href="javascript:history.back()" class="back-to-home-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z"/></svg>
            Kembali
        </a>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Error!</strong> <ul><?php foreach ($errors as $error) echo "<li>".htmlspecialchars($error)."</li>"; ?></ul>
                <p style="margin-top:15px;"><a href="<?php echo BASE_URL; ?>produk.php" style="color: #721c24; font-weight:bold;">Lihat semua produk</a></p>
            </div>
        <?php elseif ($produk): ?>
            <div class="product-detail-main-layout">
                <div class="product-gallery">
                    <?php if (count($produk['gambar_all']) > 1): ?>
                    <div class="thumbnail-column">
                        <?php foreach ($produk['gambar_all'] as $index_thumb => $gambar_url_thumb): ?>
                            <img src="<?php echo $gambar_url_thumb; ?>" alt="Thumbnail <?php echo $index_thumb + 1; ?>" onclick="changeMainImageDetail('<?php echo $gambar_url_thumb; ?>', this)">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="main-image-container">
                        <span class="brand-tag">Kado-In</span>
                        <img id="mainProductImageDetail" src="<?php echo $produk['gambar_all'][0]; ?>" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
                    </div>
                </div>

                <div class="product-info-action-detail">
                    <p class="category-tag"><?php echo htmlspecialchars($produk['nama_kategori'] ?? 'Produk'); ?></p>
                    <h1><?php echo htmlspecialchars($produk['nama_produk']); ?></h1>
                    <p id="productPriceDisplay"><?php echo format_rupiah($produk['harga']); ?></p>
                    <div class="product-short-description"><?php echo nl2br(htmlspecialchars($produk['deskripsi'])); ?></div>

                    <form action="keranjang.php" method="POST" id="addToCartFormDetail">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id_produk" value="<?php echo $produk['id_produk']; ?>">
                        
                        <div class="form-group quantity-control">
                            <label for="quantity_detail">Kuantitas:</label>
                            <div class="quantity-input-group">
                                <button type="button" onclick="changeQtyDetailSimple(-1)">-</button>
                                <input type="number" id="quantity_detail" name="quantity" value="1" min="1" 
                                       max="<?php echo $produk['stok'] > 0 ? $produk['stok'] : 1; ?>" 
                                       <?php echo $produk['stok'] <= 0 ? 'disabled' : ''; ?>
                                       onchange="validateQtyDetailSimple(this)">
                                <button type="button" onclick="changeQtyDetailSimple(1)">+</button>
                            </div>
                            <span class="quantity-stock-info">(Stok: <?php echo $produk['stok']; ?>)</span>
                        </div>
                        
                        <div class="form-group item-note-group">
                            <label for="catatan_item">Catatan untuk item ini (Opsional):</label>
                            <textarea id="catatan_item" name="catatan" rows="2" placeholder="Misal: Warna pita khusus, dll."></textarea>
                        </div>

                        <div class="action-buttons-detail">
                            <?php if ($produk['stok'] > 0): ?>
                                <button type="submit" name="submit_action" value="add_to_cart" class="add-to-cart-btn-detail">
                                    Tambahkan ke Keranjang
                                </button>
                            <?php else: ?>
                                <button type="button" class="stock-out-btn-detail">Stok Habis</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($rekomendasi_produk)): ?>
            <div class="rekomendasi-section">
                <h2>Rekomendasi Untukmu</h2>
                <div class="produk-grid">
                    <?php foreach ($rekomendasi_produk as $rek_item): ?>
                        <div class="produk-item">
                            <a href="detail_produk.php?id=<?php echo $rek_item["id_produk"]; ?>" class="produk-item-img-link">
                                <?php
                                $gambar_url_rek = (!empty($rek_item['gambar1']) && file_exists('assets/img/' . $rek_item['gambar1']))
                                    ? BASE_URL . 'assets/img/' . $rek_item['gambar1']
                                    : 'https://placehold.co/250x250/F8F0FC/D6B7E5?text=KadoIn&font=poppins';
                                ?>
                                <img src="<?php echo $gambar_url_rek; ?>" alt="<?php echo htmlspecialchars($rek_item["nama_produk"]); ?>" style="height: 230px;">
                            </a>
                            <div class="produk-info">
                                <a href="detail_produk.php?id=<?php echo $rek_item['id_produk']; ?>" style="text-decoration:none; color:inherit;"><h4 style="font-size: 1em; height: 38px;"><?php echo htmlspecialchars($rek_item["nama_produk"]); ?></h4></a>
                                <div class="produk-meta">
                                    <p style="font-size: 1.1em;"><?php echo format_rupiah($rek_item["harga"]); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <script>
                // ... FUNGSI JAVASCRIPT ANDA TETAP DI SINI, TIDAK DIUBAH ...
                function changeMainImageDetail(newSrc, thumbElement) { /* ... */ }
                document.addEventListener('DOMContentLoaded', function() { /* ... */ });
                function changeQtyDetailSimple(amount) { /* ... */ }
                function validateQtyDetailSimple(input) { /* ... */ }
            </script>
        <?php endif; ?>
    </div> 
</div> 

<?php
require_once 'includes/footer.php';
?>