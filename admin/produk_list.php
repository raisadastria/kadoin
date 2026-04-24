<?php
// admin/produk_list.php
require_once 'includes/header_admin.php'; // Header admin (termasuk auth check & config)

// Pesan notifikasi (jika ada dari operasi tambah/edit/hapus)
$notification = '';
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    unset($_SESSION['notification']); // Hapus setelah ditampilkan
}

// Ambil semua produk dari database
$sql = "SELECT id_produk, nama_produk, harga, stok, gambar1 FROM produk ORDER BY created_at DESC";
$result = $conn->query($sql);

?>

<h2>Manajemen Produk</h2>

<?php if ($notification): ?>
    <div class="alert <?php echo strpos(strtolower($notification), 'berhasil') !== false ? 'alert-success' : 'alert-danger'; ?>">
        <?php echo htmlspecialchars($notification); ?>
    </div>
<?php endif; ?>

<a href="produk_tambah.php" class="actions add-new" style="margin-bottom: 20px; display: inline-block;">+ Tambah Produk Baru</a>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Gambar</th>
                <th>Nama Produk</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php 
                        $gambar_url = (!empty($row['gambar1']) && file_exists('../assets/img/' . $row['gambar1']))
                                      ? BASE_URL . 'assets/img/' . $row['gambar1']
                                      : 'https://via.placeholder.com/60x60.png?text=N/A';
                        ?>
                        <img src="<?php echo $gambar_url; ?>" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" class="product-thumb">
                    </td>
                    <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                    <td><?php echo format_rupiah($row['harga']); ?></td>
                    <td><?php echo $row['stok']; ?></td>
                    <td class="actions">
                        <a href="produk_edit.php?id=<?php echo $row['id_produk']; ?>" class="edit">Edit</a>
                        <a href="produk_hapus.php?id=<?php echo $row['id_produk']; ?>" class="delete" onclick="return confirm('Bang Yakin Pengen dihapus?');">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Belum ada produk. Silakan <a href="produk_tambah.php">tambahkan produk baru</a>.</p>
<?php endif; ?>

<?php
$conn->close(); // Tutup koneksi setelah selesai
require_once 'includes/footer_admin.php';
?>
