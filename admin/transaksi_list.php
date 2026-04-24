<?php
// admin/transaksi_list.php
require_once 'includes/header_admin.php'; // Memuat header admin (termasuk auth check & config)

// Pesan notifikasi (jika ada, misal setelah update status nanti)
$notification = '';
if (isset($_SESSION['notification_transaksi'])) { // Gunakan nama session yang unik
    $notification = $_SESSION['notification_transaksi'];
    unset($_SESSION['notification_transaksi']); // Hapus setelah ditampilkan
}

// Ambil semua data pesanan dari database
// Kita join dengan tabel users untuk mendapatkan email pelanggan (opsional, karena nama penerima sudah ada)
$sql = "SELECT p.id_pesanan, p.nama_penerima, p.total_harga, p.status_pesanan, p.waktu_pesanan, u.email AS email_pelanggan
        FROM pesanan p
        LEFT JOIN users u ON p.id_user = u.id_user
        ORDER BY p.waktu_pesanan DESC";
$result = $conn->query($sql);

?>

<h2>Manajemen Transaksi Pesanan</h2>

<?php if ($notification): ?>
    <div class="alert <?php echo strpos(strtolower($notification), 'berhasil') !== false ? 'alert-success' : 'alert-danger'; ?>">
        <?php echo htmlspecialchars($notification); ?>
    </div>
<?php endif; ?>

<?php if ($result && $result->num_rows > 0): ?>
    <table class="table-transaksi" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">ID Pesanan</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Nama Penerima</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Email Pelanggan</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Total Harga</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Status</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Waktu Pesanan</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">#<?php echo htmlspecialchars($row['id_pesanan']); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['nama_penerima']); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['email_pelanggan'] ?: '-'); // Tampilkan '-' jika email null ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><?php echo format_rupiah($row['total_harga']); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                        <span class="status-badge status-<?php echo htmlspecialchars(strtolower($row['status_pesanan'])); ?>"
                              style="padding: 5px 10px; border-radius: 15px; color: white; font-size: 0.85em; text-transform: capitalize;
                                     background-color: <?php 
                                        switch(strtolower($row['status_pesanan'])) {
                                            case 'pending': echo '#FFC107'; break; // Kuning
                                            case 'processing': echo '#17A2B8'; break; // Biru Info
                                            case 'shipped': echo '#28A745'; break; // Hijau Sukses
                                            case 'completed': echo '#6F42C1'; break; // Ungu
                                            case 'cancelled': echo '#DC3545'; break; // Merah Bahaya
                                            default: echo '#6C757D'; // Abu-abu
                                        }
                                     ?>;">
                            <?php echo htmlspecialchars($row['status_pesanan']); ?>
                        </span>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 0.9em;"><?php echo date("d M Y, H:i", strtotime($row['waktu_pesanan'])); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;" class="actions">
                        <a href="transaksi_detail.php?id=<?php echo $row['id_pesanan']; ?>" class="edit" 
                           style="background-color: #007bff; color:white; padding: 6px 10px; border-radius:4px; text-decoration:none; font-size:0.9em;">
                           Lihat Detail
                        </a>
                        </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="margin-top: 20px; font-size: 1.1em; color: #777;">Belum ada transaksi pesanan yang masuk.</p>
<?php endif; ?>

<?php
if (isset($stmt) && $stmt) $stmt->close(); // Jika menggunakan prepared statement di masa depan
// $conn->close(); // Koneksi akan ditutup di footer_admin.php
require_once 'includes/footer_admin.php';
?>
