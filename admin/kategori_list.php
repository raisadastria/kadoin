<?php
// admin/kategori_list.php
require_once 'includes/header_admin.php'; // Memuat header admin

// Pesan notifikasi
$notification = '';
if (isset($_SESSION['notification_kategori'])) {
    $notification = $_SESSION['notification_kategori'];
    unset($_SESSION['notification_kategori']); 
}

// Ambil semua data kategori dari database
$sql = "SELECT id_kategori, nama_kategori, slug_kategori, deskripsi_kategori, created_at 
        FROM kategori 
        ORDER BY nama_kategori ASC";
$result = $conn->query($sql);

?>

<h2>Manajemen Kategori Produk</h2>

<?php if ($notification): ?>
    <div class="alert <?php echo strpos(strtolower($notification), 'berhasil') !== false ? 'alert-success' : 'alert-danger'; ?>">
        <?php echo htmlspecialchars($notification); ?>
    </div>
<?php endif; ?>

<a href="kategori_tambah.php" class="actions add-new" style="margin-bottom: 20px; display: inline-block;">+ Tambah Kategori Baru</a>

<?php if ($result && $result->num_rows > 0): ?>
    <table class="table-kategori" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">ID</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Nama Kategori</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Slug</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Deskripsi</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Tanggal Dibuat</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['id_kategori']); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['slug_kategori']); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd; font-size:0.9em;">
                        <?php 
                        $deskripsi = htmlspecialchars($row['deskripsi_kategori'] ?: '-');
                        echo strlen($deskripsi) > 100 ? substr($deskripsi, 0, 100) . '...' : $deskripsi;
                        ?>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 0.9em;"><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;" class="actions">
                        <a href="kategori_edit.php?id=<?php echo $row['id_kategori']; ?>" class="edit" 
                           style="background-color: #ffc107; color:black; padding: 6px 10px; border-radius:4px; text-decoration:none; font-size:0.9em; margin-right: 5px;">
                           Edit
                        </a>
                        <a href="kategori_hapus.php?id=<?php echo $row['id_kategori']; ?>" class="delete" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Produk yang menggunakan kategori ini akan memiliki kategori NULL.');"
                           style="background-color: #dc3545; color:white; padding: 6px 10px; border-radius:4px; text-decoration:none; font-size:0.9em;">
                           Hapus
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="margin-top: 20px; font-size: 1.1em; color: #777;">Belum ada kategori yang ditambahkan.</p>
<?php endif; ?>

<?php
if (isset($stmt) && $stmt) $stmt->close();
require_once 'includes/footer_admin.php';
?>
