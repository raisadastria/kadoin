<?php
// admin/produk_tambah.php
require_once 'includes/header_admin.php'; // Memuat header admin (termasuk auth check & config)

// Ambil daftar kategori dari database untuk ditampilkan di dropdown
$sql_kategori = "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC";
$result_kategori = $conn->query($sql_kategori);
$daftar_kategori = [];
if ($result_kategori && $result_kategori->num_rows > 0) {
    while ($row_kat = $result_kategori->fetch_assoc()) {
        $daftar_kategori[] = $row_kat;
    }
}

// Inisialisasi variabel untuk form dan pesan
$nama_produk = '';
$deskripsi = '';
$harga = '';
$stok = '';
$id_kategori_terpilih = ''; // Untuk menyimpan ID kategori yang dipilih
// Nama file gambar yang akan disimpan di DB
$gambar_filenames = [
    'gambar1' => null, 'gambar2' => null, 'gambar3' => null, 'gambar4' => null
];

$errors = [];
// $success_message = ''; // Akan ditangani dengan $_SESSION['notification']

// Direktori target untuk upload gambar
$target_dir = "../assets/img/"; 

if (!is_dir($target_dir)) {
    if (!mkdir($target_dir, 0775, true)) {
        $errors[] = "Error: Gagal membuat direktori upload ('" . htmlspecialchars($target_dir) . "').";
    }
}
if (is_dir($target_dir) && !is_writable($target_dir)) {
    $errors[] = "Error: Direktori upload ('" . htmlspecialchars($target_dir) . "') tidak dapat ditulis.";
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($errors)) { 
    $nama_produk = trim($_POST['nama_produk']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = trim($_POST['harga']);
    $stok = trim($_POST['stok']);
    $id_kategori_terpilih = isset($_POST['id_kategori']) ? (int)$_POST['id_kategori'] : ''; // Ambil ID kategori

    // --- Validasi Input Dasar ---
    if (empty($nama_produk)) $errors[] = "Nama produk tidak boleh kosong.";
    if (empty($id_kategori_terpilih)) $errors[] = "Kategori produk harus dipilih."; // Validasi kategori
    if (empty($harga)) {
        $errors[] = "Harga tidak boleh kosong.";
    } elseif (!is_numeric($harga) || $harga < 0) {
        $errors[] = "Harga harus berupa angka positif.";
    }
    if (empty($stok)) {
        $errors[] = "Stok tidak boleh kosong.";
    } elseif (!is_numeric($stok) || $stok < 0 || floor($stok) != $stok) { 
        $errors[] = "Stok harus berupa angka bulat positif.";
    }

    // --- Proses Upload Gambar (sama seperti sebelumnya) ---
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $max_file_size = 5 * 1024 * 1024; // 5MB

    for ($i = 1; $i <= 4; $i++) {
        $file_input_name = 'gambar' . $i;
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == UPLOAD_ERR_OK) {
            $file_name = $_FILES[$file_input_name]['name'];
            $file_tmp_name = $_FILES[$file_input_name]['tmp_name'];
            $file_size = $_FILES[$file_input_name]['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!in_array($file_ext, $allowed_types)) { $errors[] = "Gambar " . $i . ": Format file tidak didukung."; continue; }
            if ($file_size > $max_file_size) { $errors[] = "Gambar " . $i . ": Ukuran file terlalu besar."; continue; }

            $new_file_name = uniqid('produk_', true) . '.' . $file_ext;
            $target_file_path = $target_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_name, $target_file_path)) {
                $gambar_filenames[$file_input_name] = $new_file_name; 
            } else {
                $errors[] = "Gambar " . $i . ": Gagal memindahkan file yang diupload.";
            }
        } elseif (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] != UPLOAD_ERR_NO_FILE) {
            $errors[] = "Gambar " . $i . ": Terjadi kesalahan saat upload (Error code: " . $_FILES[$file_input_name]['error'] . ").";
        }
    }

    // --- Jika tidak ada error, masukkan data ke database ---
    if (empty($errors)) {
        // Modifikasi query INSERT untuk menyertakan id_kategori
        $sql = "INSERT INTO produk (nama_produk, deskripsi, harga, stok, id_kategori, gambar1, gambar2, gambar3, gambar4) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"; // Tambah satu placeholder untuk id_kategori
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // Modifikasi bind_param: tambah 'i' untuk id_kategori
            $stmt->bind_param(
                "ssdiissss", // s(nama) s(desk) d(harga) i(stok) i(id_kategori) s(gbr1) s(gbr2) s(gbr3) s(gbr4)
                $nama_produk,
                $deskripsi,
                $harga,
                $stok,
                $id_kategori_terpilih, // Bind variabel id_kategori
                $gambar_filenames['gambar1'],
                $gambar_filenames['gambar2'],
                $gambar_filenames['gambar3'],
                $gambar_filenames['gambar4']
            );

            if ($stmt->execute()) {
                $_SESSION['notification'] = "Produk '" . htmlspecialchars($nama_produk) . "' berhasil ditambahkan!";
                redirect('produk_list.php'); 
                // exit; // redirect() sudah ada exit()
            } else {
                $errors[] = "Gagal menyimpan produk ke database: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Gagal mempersiapkan statement database: " . $conn->error;
        }
    }
}
?>

<h2>Tambah Produk Baru</h2>

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

<form action="produk_tambah.php" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="nama_produk">Nama Produk:</label>
        <input type="text" id="nama_produk" name="nama_produk" class="form-control" value="<?php echo htmlspecialchars($nama_produk); ?>" required>
    </div>

    <div class="form-group">
        <label for="id_kategori">Kategori Produk:</label>
        <select id="id_kategori" name="id_kategori" class="form-control" required>
            <option value="">-- Pilih Kategori --</option>
            <?php if (!empty($daftar_kategori)): ?>
                <?php foreach ($daftar_kategori as $kategori): ?>
                    <option value="<?php echo $kategori['id_kategori']; ?>" <?php echo ($id_kategori_terpilih == $kategori['id_kategori']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="" disabled>Belum ada kategori tersedia. Silakan tambahkan dulu.</option>
            <?php endif; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="deskripsi">Deskripsi:</label>
        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="5"><?php echo htmlspecialchars($deskripsi); ?></textarea>
    </div>
    <div class="form-group">
        <label for="harga">Harga (Rp):</label>
        <input type="number" id="harga" name="harga" class="form-control" step="any" min="0" value="<?php echo htmlspecialchars($harga); ?>" required>
    </div>
    <div class="form-group">
        <label for="stok">Stok:</label>
        <input type="number" id="stok" name="stok" class="form-control" min="0" value="<?php echo htmlspecialchars($stok); ?>" required>
    </div>

    <hr>
    <p><strong>Upload Gambar Produk (Maksimal 4 gambar, format: JPG, JPEG, PNG, GIF, maks 5MB/gambar):</strong></p>
    
    <div class="form-group"><label for="gambar1">Gambar 1 (Utama):</label><input type="file" id="gambar1" name="gambar1" class="form-control-file"></div>
    <div class="form-group"><label for="gambar2">Gambar 2:</label><input type="file" id="gambar2" name="gambar2" class="form-control-file"></div>
    <div class="form-group"><label for="gambar3">Gambar 3:</label><input type="file" id="gambar3" name="gambar3" class="form-control-file"></div>
    <div class="form-group"><label for="gambar4">Gambar 4:</label><input type="file" id="gambar4" name="gambar4" class="form-control-file"></div>
    <hr>

    <button type="submit" class="btn btn-primary">Simpan Produk</button>
    <a href="produk_list.php" class="btn btn-secondary">Batal</a>
</form>

<?php
require_once 'includes/footer_admin.php'; 
?>
