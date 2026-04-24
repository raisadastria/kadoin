<?php
// admin/user_edit.php
require_once 'includes/header_admin.php'; // Memuat header admin

$id_user_edit = null;
$user_data = null; // Untuk menyimpan data user yang akan diedit
$errors = [];
$notification = '';

// Ambil notifikasi jika ada (misal setelah update sukses dari halaman ini sendiri)
if (isset($_SESSION['notification_user_edit'])) {
    $notification = $_SESSION['notification_user_edit'];
    unset($_SESSION['notification_user_edit']);
}

// 1. Ambil ID User dari URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_user_edit = (int)$_GET['id'];
} else {
    $_SESSION['notification_user'] = "Error: ID pengguna tidak valid atau tidak ditemukan.";
    redirect('user_list.php');
    // exit;
}

// 2. Logika untuk Update Data Pengguna (jika form disubmit)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $nama_user_baru = trim($_POST['nama_user']);
    $email_baru = trim($_POST['email']);
    $role_baru = trim($_POST['role']);
    $alamat_baru = trim($_POST['alamat']);
    $telepon_baru = trim($_POST['telepon']);
    $password_baru = $_POST['password_baru']; // Tidak di-trim
    $konfirmasi_password_baru = $_POST['konfirmasi_password_baru'];

    // Validasi dasar
    if (empty($nama_user_baru)) $errors[] = "Nama pengguna tidak boleh kosong.";
    if (empty($email_baru)) {
        $errors[] = "Email tidak boleh kosong.";
    } elseif (!filter_var($email_baru, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    $allowed_roles = ['user', 'admin']; // Daftar role yang diizinkan
    if (empty($role_baru) || !in_array($role_baru, $allowed_roles)) {
        $errors[] = "Role tidak valid.";
    }

    // Validasi password baru jika diisi
    if (!empty($password_baru)) {
        if (strlen($password_baru) < 6) {
            $errors[] = "Password baru minimal harus 6 karakter.";
        }
        if ($password_baru !== $konfirmasi_password_baru) {
            $errors[] = "Konfirmasi password baru tidak cocok.";
        }
    }

    // Cek apakah email baru sudah digunakan oleh user lain (jika email diubah)
    if (empty($errors)) {
        $sql_cek_email_lama = "SELECT email FROM users WHERE id_user = ?";
        $stmt_cek_email_lama = $conn->prepare($sql_cek_email_lama);
        $stmt_cek_email_lama->bind_param("i", $id_user_edit);
        $stmt_cek_email_lama->execute();
        $result_email_lama = $stmt_cek_email_lama->get_result();
        $email_lama_db = $result_email_lama->fetch_assoc()['email'];
        $stmt_cek_email_lama->close();

        if (strtolower($email_baru) !== strtolower($email_lama_db)) { // Email diubah
            $sql_cek_email_exist = "SELECT id_user FROM users WHERE email = ? AND id_user != ?";
            $stmt_cek_exist = $conn->prepare($sql_cek_email_exist);
            $stmt_cek_exist->bind_param("si", $email_baru, $id_user_edit);
            $stmt_cek_exist->execute();
            if ($stmt_cek_exist->get_result()->num_rows > 0) {
                $errors[] = "Email '" . htmlspecialchars($email_baru) . "' sudah digunakan oleh pengguna lain.";
            }
            $stmt_cek_exist->close();
        }
    }

    // Jika tidak ada error, update data pengguna
    if (empty($errors)) {
        $params_to_update = [];
        $types_update = "";
        $sql_set_parts = [];

        // Data dasar
        $sql_set_parts[] = "nama_user = ?"; $params_to_update[] = $nama_user_baru; $types_update .= "s";
        $sql_set_parts[] = "email = ?"; $params_to_update[] = $email_baru; $types_update .= "s";
        $sql_set_parts[] = "role = ?"; $params_to_update[] = $role_baru; $types_update .= "s";
        $sql_set_parts[] = "alamat = ?"; $params_to_update[] = $alamat_baru; $types_update .= "s";
        $sql_set_parts[] = "telepon = ?"; $params_to_update[] = $telepon_baru; $types_update .= "s";

        // Jika password baru diisi, hash dan tambahkan ke query update
        if (!empty($password_baru)) {
            $hashed_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
            $sql_set_parts[] = "password = ?";
            $params_to_update[] = $hashed_password_baru;
            $types_update .= "s";
        }

        $params_to_update[] = $id_user_edit; // Untuk klausa WHERE
        $types_update .= "i";

        $sql_update = "UPDATE users SET " . implode(", ", $sql_set_parts) . " WHERE id_user = ?";
        $stmt_update = $conn->prepare($sql_update);

        if ($stmt_update) {
            $stmt_update->bind_param($types_update, ...$params_to_update);
            if ($stmt_update->execute()) {
                $_SESSION['notification_user'] = "Data pengguna '" . htmlspecialchars($nama_user_baru) . "' (ID: " . $id_user_edit . ") berhasil diperbarui.";
                redirect('user_list.php');
                // exit;
            } else {
                $errors[] = "Gagal memperbarui data pengguna: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            $errors[] = "Gagal mempersiapkan statement update: " . $conn->error;
        }
    }
    // Jika ada error, data yang diinput akan digunakan untuk mengisi ulang form
    $user_data = [
        'nama_user' => $nama_user_baru, 'email' => $email_baru, 'role' => $role_baru,
        'alamat' => $alamat_baru, 'telepon' => $telepon_baru
    ];

} else if (empty($errors)) { // Jika bukan POST (pertama kali load halaman) dan tidak ada error ID
    // Ambil data pengguna dari database untuk ditampilkan di form
    $sql_select = "SELECT id_user, nama_user, email, role, alamat, telepon FROM users WHERE id_user = ?";
    $stmt_select = $conn->prepare($sql_select);
    if ($stmt_select) {
        $stmt_select->bind_param("i", $id_user_edit);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        if ($result_select->num_rows == 1) {
            $user_data = $result_select->fetch_assoc();
        } else {
            $errors[] = "Pengguna dengan ID #" . htmlspecialchars($id_user_edit) . " tidak ditemukan.";
            $_SESSION['notification_user'] = "Error: Pengguna tidak ditemukan.";
            // redirect('user_list.php'); // Bisa redirect jika user tidak ada
        }
        $stmt_select->close();
    } else {
        $errors[] = "Gagal mengambil data pengguna: " . $conn->error;
    }
}

// 3. SEKARANG BARU INCLUDE HEADER_ADMIN.PHP (jika belum di atas)
// Jika header_admin.php sudah di atas, ini tidak perlu.
// require_once 'includes/header_admin.php'; 
// Untuk kasus ini, header sudah di atas, jadi kita langsung ke HTML.

?>

<h2>Edit Pengguna <?php echo $user_data ? '- ' . htmlspecialchars($user_data['nama_user']) : ''; ?></h2>

<a href="user_list.php" class="btn btn-secondary" style="margin-bottom: 20px; display: inline-block; background-color: #6c757d; text-decoration:none;">&laquo; Kembali ke Daftar Pengguna</a>

<?php if ($notification): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($notification); ?></div>
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
<?php endif; ?>

<?php if ($user_data): // Tampilkan form hanya jika data user berhasil diambil ?>
<form action="user_edit.php?id=<?php echo $id_user_edit; ?>" method="POST">
    <div class="form-group">
        <label for="nama_user">Nama Pengguna:</label>
        <input type="text" id="nama_user" name="nama_user" class="form-control" value="<?php echo htmlspecialchars($user_data['nama_user']); ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
    </div>
    <div class="form-group">
        <label for="role">Role:</label>
        <select id="role" name="role" class="form-control" required>
            <option value="user" <?php echo ($user_data['role'] == 'user') ? 'selected' : ''; ?>>User</option>
            <option value="admin" <?php echo ($user_data['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select>
    </div>
    <div class="form-group">
        <label for="alamat">Alamat:</label>
        <textarea id="alamat" name="alamat" class="form-control" rows="3"><?php echo htmlspecialchars($user_data['alamat'] ?: ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="telepon">Telepon:</label>
        <input type="text" id="telepon" name="telepon" class="form-control" value="<?php echo htmlspecialchars($user_data['telepon'] ?: ''); ?>">
    </div>
    
    <hr style="margin: 25px 0;">
    <p><strong>Ganti Password (Kosongkan jika tidak ingin mengubah password):</strong></p>
    <div class="form-group">
        <label for="password_baru">Password Baru:</label>
        <input type="password" id="password_baru" name="password_baru" class="form-control" aria-describedby="passwordHelp">
        <small id="passwordHelp" style="font-size: 0.8em; color: #6c757d;">Minimal 6 karakter.</small>
    </div>
    <div class="form-group">
        <label for="konfirmasi_password_baru">Konfirmasi Password Baru:</label>
        <input type="password" id="konfirmasi_password_baru" name="konfirmasi_password_baru" class="form-control">
    </div>
    <hr style="margin: 25px 0;">

    <button type="submit" name="update_user" class="btn btn-primary">Update Pengguna</button>
    <a href="user_list.php" class="btn btn-secondary" style="margin-left:10px;">Batal</a>
</form>
<?php else: ?>
    <?php if (empty($errors)): // Jika tidak ada error spesifik tapi user_data juga kosong (misal ID tidak valid dari awal) ?>
        <p style="margin-top: 20px; font-size: 1.1em; color: #777;">Data pengguna tidak dapat ditampilkan.</p>
    <?php endif; ?>
<?php endif; ?>

<?php
require_once 'includes/footer_admin.php';
?>
