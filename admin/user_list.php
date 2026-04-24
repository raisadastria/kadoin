<?php
// admin/user_list.php
require_once 'includes/header_admin.php'; // Memuat header admin (termasuk auth check & config)

// Pesan notifikasi (jika ada, misal setelah edit atau hapus user nanti)
$notification = '';
if (isset($_SESSION['notification_user'])) { 
    $notification = $_SESSION['notification_user'];
    unset($_SESSION['notification_user']); 
}

// Ambil semua data pengguna dari database, kecuali password
// Diurutkan berdasarkan tanggal registrasi atau nama
$sql = "SELECT id_user, nama_user, email, role, alamat, telepon, created_at 
        FROM users 
        ORDER BY created_at DESC"; // Atau ORDER BY nama_user ASC
$result = $conn->query($sql);

?>

<h2>Manajemen Pengguna Terdaftar</h2>

<?php if ($notification): ?>
    <div class="alert <?php echo strpos(strtolower($notification), 'berhasil') !== false ? 'alert-success' : 'alert-danger'; ?>">
        <?php echo htmlspecialchars($notification); ?>
    </div>
<?php endif; ?>

<?php if ($result && $result->num_rows > 0): ?>
    <table class="table-users" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">ID User</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Nama Pengguna</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Email</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Role</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Alamat</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Telepon</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Tanggal Daftar</th>
                <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['id_user']); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['nama_user']); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center; text-transform: capitalize;">
                        <span class="role-badge role-<?php echo htmlspecialchars(strtolower($row['role'])); ?>"
                              style="padding: 4px 8px; border-radius: 12px; color: white; font-size: 0.8em;
                                     background-color: <?php echo (strtolower($row['role']) == 'admin') ? '#DC3545' : '#007BFF'; ?>;">
                            <?php echo htmlspecialchars($row['role']); ?>
                        </span>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['alamat'] ?: '-'); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['telepon'] ?: '-'); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 0.9em;"><?php echo date("d M Y, H:i", strtotime($row['created_at'])); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;" class="actions">
                        <a href="user_edit.php?id=<?php echo $row['id_user']; ?>" class="edit" 
                           style="background-color: #ffc107; color:black; padding: 6px 10px; border-radius:4px; text-decoration:none; font-size:0.9em; margin-right: 5px;">
                           Edit
                        </a>
                        <?php if ($row['role'] != 'admin' || $_SESSION['user_id'] != $row['id_user']): // Admin tidak bisa hapus diri sendiri atau admin lain (contoh sederhana) ?>
                        <a href="user_hapus.php?id=<?php echo $row['id_user']; ?>" class="delete" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini? Semua data terkait (pesanan, dll) mungkin akan terpengaruh atau perlu penanganan khusus.');"
                           style="background-color: #dc3545; color:white; padding: 6px 10px; border-radius:4px; text-decoration:none; font-size:0.9em;">
                           Hapus
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="margin-top: 20px; font-size: 1.1em; color: #777;">Belum ada pengguna yang terdaftar selain admin awal (jika ada).</p>
<?php endif; ?>

<?php
if (isset($stmt) && $stmt) $stmt->close(); // Jika menggunakan prepared statement di masa depan
// $conn->close(); // Koneksi akan ditutup di footer_admin.php
require_once 'includes/footer_admin.php';
?>
