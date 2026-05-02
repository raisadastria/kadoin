<?php
// toko_online/login.php
require_once 'includes/config.php'; // Memuat config (termasuk session_start() dan $conn)

// Jika user sudah login, arahkan ke halaman utama atau profil
if (isset($_SESSION['user_id'])) {
    redirect(BASE_URL);
}

$email_input = '';
$login_error = '';
$notification_data = null;

// Ambil notifikasi dari session jika ada (misal dari registrasi)
if (isset($_SESSION['temp_notification'])) {
    $notification_data = $_SESSION['temp_notification'];
    unset($_SESSION['temp_notification']); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_input = isset($_POST["email"]) ? trim($_POST["email"]) : '';
    $password = isset($_POST["password"]) ? $_POST["password"] : '';

    if (empty($email_input) || empty($password)) {
        $login_error = "Email dan password tidak boleh kosong.";
    } else {
        $sql = "SELECT id_user, nama_user, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $email_input);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user["password"])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['user_nama'] = $user['nama_user'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    $redirect_to = BASE_URL;
                    if (isset($_SESSION['redirect_url'])) {
                        $redirect_to = $_SESSION['redirect_url'];
                        unset($_SESSION['redirect_url']); 
                    } elseif ($user["role"] === "admin") {
                        $redirect_to = BASE_URL . "admin/";
                    }

                    redirect($redirect_to);
                } else {
                    $login_error = "Password salah.";
                }
            } else {
                $login_error = "Akun dengan email tersebut tidak ditemukan.";
            }

            $stmt->close();
        } else {
            $login_error = "Terjadi kesalahan pada server. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KadoIn</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/img/CSS/style.css">
</head>
<body class="auth-page">

    <div class="left-panel">
        <img src="assets/img/bunga.png" alt="Bouquet Ilustrasi KadoIn">

        <div class="logo-text">
            <div class="top">Selamat Datang Kembali di</div>
            <div class="bottom">KADOIN.</div>
        </div>
    </div>

    <div class="right-panel">
        <h1>Welcome Back!</h1>
        <p class="subtitle">Silakan login untuk melanjutkan belanja.</p>

        <?php if ($notification_data && $notification_data['type'] == 'success'): ?>
            <p class="notification-message">
                <?php echo htmlspecialchars($notification_data['message']); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($login_error)): ?>
            <p class="login-error-message">
                <?php echo htmlspecialchars($login_error); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?php echo htmlspecialchars($email_input); ?>" 
                    placeholder="email@example.com"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Minimal 6 karakter"
                    required
                >
            </div>

            <button class="login-btn" type="submit">
                Login
            </button>

            <div class="create-account">
                Belum punya akun? 
                <a href="register.php">Buat akun baru</a>
            </div>

            <div class="back-to-home">
                <a href="<?php echo BASE_URL; ?>">
                    Kembali ke Beranda
                </a>
            </div>

        </form>
    </div>

</body>
</html>