<?php
// toko_online/register.php
require_once 'includes/config.php'; // Memuat config

// Jika user sudah login, arahkan ke halaman utama
if (isset($_SESSION['user_id'])) {
    redirect(BASE_URL);
}

$nama_user = '';
$email = '';
$alamat = '';
$telepon = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_user = trim($_POST['nama_user']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; 
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['telepon']);

    // Validasi
    if (empty($nama_user)) $errors[] = "Nama lengkap tidak boleh kosong.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
    if (strlen($password) < 6) $errors[] = "Password minimal harus 6 karakter.";
    if ($password !== $konfirmasi_password) $errors[] = "Konfirmasi password tidak cocok.";

    // Cek duplikasi email jika tidak ada error validasi dasar
    if (empty($errors)) {
        $sql_cek_email = "SELECT id_user FROM users WHERE email = ?";
        $stmt_cek = $conn->prepare($sql_cek_email);
        $stmt_cek->bind_param("s", $email);
        $stmt_cek->execute();
        $result_cek = $stmt_cek->get_result();
        if ($result_cek->num_rows > 0) {
            $errors[] = "Email ini sudah terdaftar. Silakan login.";
        }
        $stmt_cek->close();
    }

    // Insert user jika tidak ada error sama sekali
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role_user = 'user';

        $sql_insert = "INSERT INTO users (nama_user, email, password, alamat, telepon, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ssssss", $nama_user, $email, $hashed_password, $alamat, $telepon, $role_user);

        if ($stmt_insert->execute()) {
            $_SESSION['temp_notification'] = ['type' => 'success', 'message' => 'Registrasi berhasil! Silakan login dengan akun Anda.'];
            redirect('login.php');
        } else {
            $errors[] = "Gagal mendaftarkan pengguna: " . $stmt_insert->error;
        }
        $stmt_insert->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Buat Akun - KadoIn</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <!-- Link ke Stylesheet Utama -->
    <link rel="stylesheet" href="assets/img/CSS/style.css">
</head>
<body class="auth-page">
    <div class="left-panel">
        <img src="assets/img/bunga.png" alt="Ilustrasi Bunga KadoIn" />
        <div class="logo-text">
            <div class="top">Bergabunglah Bersama Kami!</div>
            <div class="bottom">KADOIN.</div>
        </div>
    </div>
    <div class="right-panel">
        <h1>Buat Akun Baru</h1>
        <p class="subtitle">Daftar dan temukan kado spesialmu!</p>

        <?php if (!empty($errors)): ?>
            <div class="form-error-message">
                <strong>Mohon perbaiki kesalahan berikut:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="nama_user">Nama Lengkap</label>
                <input type="text" id="nama_user" name="nama_user" value="<?php echo htmlspecialchars($nama_user); ?>" required />
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required />
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required aria-describedby="passwordHelp" />
                    <span class="toggle-password" onclick="togglePass('password', 'icon-show-pw', 'icon-hide-pw')">
                        <svg id="icon-show-pw" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg id="icon-hide-pw" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </span>
                </div>
                <small id="passwordHelp">Minimal 6 karakter.</small>
            </div>
            <div class="form-group">
                <label for="konfirmasi_password">Konfirmasi Password</label>
                <div class="password-wrapper">
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" required />
                    <span class="toggle-password" onclick="togglePass('konfirmasi_password', 'icon-show-kpw', 'icon-hide-kpw')">
                        <svg id="icon-show-kpw" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg id="icon-hide-kpw" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <label for="telepon">Nomor Telepon (Opsional)</label>
                <input type="tel" id="telepon" name="telepon" value="<?php echo htmlspecialchars($telepon); ?>" placeholder="Contoh: 08123456xxxx" />
            </div>
            <div class="form-group">
                <label for="alamat">Alamat Lengkap (Opsional)</label>
                <textarea id="alamat" name="alamat" rows="3" placeholder="Jalan, No. Rumah, RT/RW, Kelurahan, dll."><?php echo htmlspecialchars($alamat); ?></textarea>
            </div>
            <button class="create-btn" type="submit">Buat Akun</button>
            <div class="terms">
                Dengan membuat akun, Anda menyetujui <b>Ketentuan Layanan</b> kami.
            </div>
            <div class="login-link">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </div>
             <div class="back-to-home">
                <a href="<?php echo BASE_URL; ?>">Kembali ke Beranda</a>
            </div>
        </form>
    </div>

    <script>
    function togglePass(inputId, showIconId, hideIconId) {
        const input = document.getElementById(inputId);
        const iconShow = document.getElementById(showIconId);
        const iconHide = document.getElementById(hideIconId);
        if (input.type === 'password') {
            input.type = 'text';
            iconShow.style.display = 'none';
            iconHide.style.display = 'block';
        } else {
            input.type = 'password';
            iconShow.style.display = 'block';
            iconHide.style.display = 'none';
        }
    }
    </script>
</body>
</html>