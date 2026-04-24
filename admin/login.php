<?php
// admin/login.php
// Mulai session dan sertakan config.php
// config.php sudah otomatis memulai session jika belum ada.
require_once '../includes/config.php';

// Jika admin sudah login, redirect ke dashboard admin
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    redirect('index.php'); // Pastikan fungsi redirect() ada di config.php
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pastikan variabel POST ada sebelum diakses
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        $error_message = "Email dan password tidak boleh kosong.";
    } else {
        // Ambil data user dari database berdasarkan email
        // Pastikan hanya mengambil dari role admin untuk halaman login admin ini
        $sql = "SELECT id_user, nama_user, email, password, role FROM users WHERE email = ? AND role = 'admin'";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    // Password cocok, set session
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['user_nama'] = $user['nama_user'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    redirect('index.php'); // Redirect ke dashboard admin (admin/index.php)
                    exit;
                } else {
                    $error_message = "Password salah.";
                }
            } else {
                // Bisa jadi email tidak ditemukan, atau role bukan admin, atau ada error query
                $error_message = "Email admin tidak ditemukan atau Anda bukan admin.";
            }
            $stmt->close();
        } else {
            // Error saat prepare statement
            $error_message = "Terjadi kesalahan pada database: " . $conn->error;
        }
    }
}

// Tutup koneksi jika $conn ada dan merupakan objek mysqli
// Sebaiknya koneksi ditutup di akhir skrip jika tidak ada include footer lagi.
// Namun, jika ada footer yang juga butuh $conn, penutupan bisa di sana.
// Untuk halaman login yang berdiri sendiri, aman untuk ditutup di sini.
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Toko Online KadoIn</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Pindahkan style ini ke admin_style.css jika memungkinkan */
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: #f0f4f8; /* Warna background lebih soft */
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            padding: 20px; 
            box-sizing: border-box;
        }
        .login-container { 
            background-color: #fff; 
            padding: 35px 40px; /* Padding lebih besar */
            border-radius: 10px; /* Sudut lebih bulat */
            box-shadow: 0 8px 25px rgba(0,0,0,0.1); /* Shadow lebih jelas */
            width: 100%; 
            max-width: 400px; /* Lebar maksimal form */
        }
        .login-container .logo-admin {
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.8em;
            font-weight: 700;
            color: #77569a; /* Warna ungu KadoIn */
        }
        .login-container h2 { 
            text-align: center; 
            margin-bottom: 25px; 
            color: #333; 
            font-size: 1.5em; /* Ukuran judul disesuaikan */
            font-weight: 600;
        }
        .login-container label { 
            display: block; 
            margin-bottom: 8px; 
            color: #555; 
            font-weight: 500;
            font-size: 0.95em;
        }
        .login-container input[type="email"],
        .login-container input[type="password"] { 
            width: 100%; 
            padding: 12px 15px; /* Padding input */
            margin-bottom: 20px; /* Jarak bawah input */
            border: 1px solid #ced4da; 
            border-radius: 6px; 
            box-sizing: border-box; 
            font-size: 1em;
            transition: border-color 0.2s;
        }
        .login-container input[type="email"]:focus,
        .login-container input[type="password"]:focus {
            border-color: #a076f9; /* Warna border saat focus */
            outline: none;
        }
        .login-container button { 
            background: linear-gradient(135deg, #C084FC 0%, #8B5CF6 100%); /* Gradasi tombol */
            color: white; 
            padding: 14px 20px; /* Padding tombol */
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            width: 100%; 
            font-size: 1.1em; /* Ukuran font tombol */
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.25);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .login-container button:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(139, 92, 246, 0.35);
        }
        .error-message { 
            background-color: #ffebee; /* Warna background error lebih soft */
            color: #c62828; /* Warna teks error */
            padding: 12px 15px; /* Padding pesan error */
            border: 1px solid #ffcdd2; 
            border-radius: 6px; 
            margin-bottom: 20px; 
            text-align: center; 
            font-size: 0.9em;
        }
        .back-to-site { 
            text-align: center; 
            margin-top: 25px; 
            font-size: 0.9em;
        }
        .back-to-site a { 
            color: #77569a; /* Warna link */
            text-decoration: none; 
            font-weight: 500;
        }
        .back-to-site a:hover { 
            text-decoration: underline; 
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-admin">KadoIn. Admin</div> <h2>Login Panel</h2>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST"> <div>
                <label for="email">Email Admin:</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="back-to-site">
            <a href="<?php echo defined('BASE_URL') ? BASE_URL : '../index.php'; ?>">Kembali ke Situs Utama</a>
        </div>
    </div>
</body>
</html>
