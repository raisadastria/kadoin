<?php
// admin/produk_edit.php
require_once 'includes/header_admin.php'; // Memuat header admin

$id_produk_edit = null;
$produk_data_lama = null; 
$errors = [];
$notification = ''; 

// Ambil daftar kategori
$sql_get_all_kategori = "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC";
$result_all_kategori = $conn->query($sql_get_all_kategori);
$daftar_semua_kategori = [];
if ($result_all_kategori && $result_all_kategori->num_rows > 0) {
    while ($row_kat = $result_all_kategori->fetch_assoc()) {
        $daftar_semua_kategori[] = $row_kat;
    }
}

// Daftar status produk yang diizinkan
$allowed_product_statuses = ['aktif', 'tidak aktif'];

// PERBAIKAN: Inisialisasi $target_dir di luar blok POST
$target_dir = "../assets/img/"; // Direktori target untuk upload gambar

// Pastikan direktori target ada dan bisa ditulis (dipindahkan ke sini juga agar dicek saat load halaman)
if (!is_dir($target_dir)) {
    if (!mkdir($target_dir, 0775, true)) { 
        $errors[] = "Error: Gagal membuat direktori upload ('" . htmlspecialchars($target_dir) . "').";
    }
}
// Cek is_writable hanya jika direktori sudah pasti ada dan tidak ada error sebelumnya
if (empty($errors) && is_dir($target_dir) && !is_writable($target_dir)) {
    $errors[] = "Error: Direktori upload ('" . htmlspecialchars($target_dir) . "') tidak dapat ditulis. Silakan periksa izin folder.";
}


// 1. Ambil ID Produk dari URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_produk_edit = (int)$_GET['id'];
} else {
    $_SESSION['notification'] = "Error: ID produk tidak valid atau tidak ditemukan untuk diedit.";
    redirect('produk_list.php');
}

// 2. Logika untuk Update Data Produk
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_produk'])) {
    // Jika ada error direktori dari pengecekan awal, jangan proses form
    if (!empty($errors)) {
        // Biarkan error ditampilkan di bawah
    } else {
        $nama_produk_baru = trim($_POST['nama_produk']);
        $id_kategori_baru = isset($_POST['id_kategori']) ? (int)$_POST['id_kategori'] : null;
        $deskripsi_baru = trim($_POST['deskripsi']);
        $harga_baru = trim($_POST['harga']);
        $stok_baru = trim($_POST['stok']);
        $status_produk_baru = trim($_POST['status_produk']);

        $gambar_filenames_lama = [
            'gambar1' => $_POST['gambar1_lama'] ?? null, 'gambar2' => $_POST['gambar2_lama'] ?? null,
            'gambar3' => $_POST['gambar3_lama'] ?? null, 'gambar4' => $_POST['gambar4_lama'] ?? null
        ];
        $gambar_filenames_update = $gambar_filenames_lama; 

        if (empty($nama_produk_baru)) $errors[] = "Nama produk tidak boleh kosong.";
        if (empty($id_kategori_baru)) $errors[] = "Kategori produk harus dipilih.";
        if (empty($harga_baru)) { $errors[] = "Harga tidak boleh kosong."; } 
        elseif (!is_numeric($harga_baru) || $harga_baru < 0) { $errors[] = "Harga harus berupa angka positif."; }
        if (empty($stok_baru) && $stok_baru !== '0') { $errors[] = "Stok tidak boleh kosong."; } 
        elseif (!is_numeric($stok_baru) || $stok_baru < 0 || floor($stok_baru) != $stok_baru) { $errors[] = "Stok harus berupa angka bulat positif."; }
        
        if (empty($status_produk_baru) || !in_array($status_produk_baru, $allowed_product_statuses)) {
            $errors[] = "Status produk tidak valid.";
        }
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_file_size = 5 * 1024 * 1024;

        for ($i = 1; $i <= 4; $i++) {
            $file_input_name = 'gambar' . $i;
            if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == UPLOAD_ERR_OK) {
                $file_name = $_FILES[$file_input_name]['name'];
                $file_tmp_name = $_FILES[$file_input_name]['tmp_name'];
                $file_size = $_FILES[$file_input_name]['size'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if (!in_array($file_ext, $allowed_types)) { $errors[] = "Gambar " . $i . " baru: Format file tidak didukung."; continue; }
                if ($file_size > $max_file_size) { $errors[] = "Gambar " . $i . " baru: Ukuran file terlalu besar."; continue; }

                $new_file_name = uniqid('produk_edit_', true) . '.' . $file_ext;
                $target_file_path = $target_dir . $new_file_name; // $target_dir sudah didefinisikan di atas

                if (move_uploaded_file($file_tmp_name, $target_file_path)) {
                    if (!empty($gambar_filenames_lama[$file_input_name]) && file_exists($target_dir . $gambar_filenames_lama[$file_input_name])) {
                        unlink($target_dir . $gambar_filenames_lama[$file_input_name]);
                    }
                    $gambar_filenames_update[$file_input_name] = $new_file_name;
                } else {
                    $errors[] = "Gambar " . $i . " baru: Gagal memindahkan file.";
                }
            } elseif (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] != UPLOAD_ERR_NO_FILE) {
                $errors[] = "Gambar " . $i . " baru: Terjadi kesalahan saat upload (Error code: " . $_FILES[$file_input_name]['error'] . ").";
            }
        }

        if (empty($errors)) {
            $sql_update = "UPDATE produk SET 
                           nama_produk = ?, deskripsi = ?, harga = ?, stok = ?, id_kategori = ?, 
                           status_produk = ?, gambar1 = ?, gambar2 = ?, gambar3 = ?, gambar4 = ? 
                           WHERE id_produk = ?";
            $stmt_update = $conn->prepare($sql_update);

            if ($stmt_update) {
                $stmt_update->bind_param("ssdiisssssi", 
                    $nama_produk_baru, $deskripsi_baru, $harga_baru, $stok_baru, $id_kategori_baru,
                    $status_produk_baru, 
                    $gambar_filenames_update['gambar1'], $gambar_filenames_update['gambar2'],
                    $gambar_filenames_update['gambar3'], $gambar_filenames_update['gambar4'],
                    $id_produk_edit
                );

                if ($stmt_update->execute()) {
                    $_SESSION['notification'] = "Produk '" . htmlspecialchars($nama_produk_baru) . "' berhasil diperbarui.";
                    redirect('produk_list.php');
                } else {
                    $errors[] = "Gagal memperbarui data produk: " . $stmt_update->error;
                }
                $stmt_update->close();
            } else {
                $errors[] = "Gagal mempersiapkan statement update: " . $conn->error;
            }
        }
    } // Akhir dari else (jika tidak ada error direktori awal)
    // Jika ada error validasi atau upload, isi ulang form dengan data yang baru diinput
    $produk_data_lama = [
        'nama_produk' => $nama_produk_baru, 'deskripsi' => $deskripsi_baru,
        'harga' => $harga_baru, 'stok' => $stok_baru, 'id_kategori' => $id_kategori_baru,
        'status_produk' => $status_produk_baru,
        'gambar1' => $gambar_filenames_update['gambar1'], 'gambar2' => $gambar_filenames_update['gambar2'],
        'gambar3' => $gambar_filenames_update['gambar3'], 'gambar4' => $gambar_filenames_update['gambar4']
    ];

} else if (empty($errors)) { // Jika bukan POST dan tidak ada error direktori awal
    $sql_select = "SELECT id_produk, nama_produk, deskripsi, harga, stok, id_kategori, status_produk, gambar1, gambar2, gambar3, gambar4 
                   FROM produk WHERE id_produk = ?";
    $stmt_select = $conn->prepare($sql_select);
    if ($stmt_select) {
        $stmt_select->bind_param("i", $id_produk_edit);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        if ($result_select->num_rows == 1) {
            $produk_data_lama = $result_select->fetch_assoc();
        } else {
            $errors[] = "Produk dengan ID #" . htmlspecialchars($id_produk_edit) . " tidak ditemukan.";
            $_SESSION['notification'] = "Error: Produk tidak ditemukan.";
        }
        $stmt_select->close();
    } else {
        $errors[] = "Gagal mengambil data produk: " . $conn->error;
    }
}
?>

<h2>Edit Produk <?php echo $produk_data_lama ? '- ' . htmlspecialchars($produk_data_lama['nama_produk']) : ''; ?></h2>

<a href="produk_list.php" class="btn btn-secondary" style="margin-bottom: 20px; display: inline-block; background-color: #6c757d; text-decoration:none;">&laquo; Kembali ke Daftar Produk</a>

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

<?php if ($produk_data_lama): ?>
<form action="produk_edit.php?id=<?php echo $id_produk_edit; ?>" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="nama_produk">Nama Produk:</label>
        <input type="text" id="nama_produk" name="nama_produk" class="form-control" value="<?php echo htmlspecialchars($produk_data_lama['nama_produk']); ?>" required>
    </div>

    <div class="form-group">
        <label for="id_kategori">Kategori Produk:</label>
        <select id="id_kategori" name="id_kategori" class="form-control" required>
            <option value="">-- Pilih Kategori --</option>
            <?php if (!empty($daftar_semua_kategori)): ?>
                <?php foreach ($daftar_semua_kategori as $kategori_option): ?>
                    <option value="<?php echo $kategori_option['id_kategori']; ?>" 
                            <?php echo (isset($produk_data_lama['id_kategori']) && $produk_data_lama['id_kategori'] == $kategori_option['id_kategori']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($kategori_option['nama_kategori']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="deskripsi">Deskripsi:</label>
        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="5"><?php echo htmlspecialchars($produk_data_lama['deskripsi'] ?? ''); // Tambah null coalescing ?></textarea>
    </div>
    <div class="form-group">
        <label for="harga">Harga (Rp):</label>
        <input type="number" id="harga" name="harga" class="form-control" step="any" min="0" value="<?php echo htmlspecialchars($produk_data_lama['harga']); ?>" required>
    </div>
    <div class="form-group">
        <label for="stok">Stok:</label>
        <input type="number" id="stok" name="stok" class="form-control" min="0" value="<?php echo htmlspecialchars($produk_data_lama['stok']); ?>" required>
    </div>

    <div class="form-group">
        <label for="status_produk">Status Produk:</label>
        <select id="status_produk" name="status_produk" class="form-control" required>
            <option value="aktif" <?php echo (isset($produk_data_lama['status_produk']) && $produk_data_lama['status_produk'] == 'aktif') ? 'selected' : ''; ?>>Aktif (Ditampilkan)</option>
            <option value="tidak aktif" <?php echo (isset($produk_data_lama['status_produk']) && $produk_data_lama['status_produk'] == 'tidak aktif') ? 'selected' : ''; ?>>Tidak Aktif (Disembunyikan)</option>
        </select>
    </div>
    
    <hr>
    <p><strong>Upload Gambar Baru (Kosongkan jika tidak ingin mengubah):</strong></p>
    <?php for ($i = 1; $i <= 4; $i++): 
        $input_file_name = 'gambar' . $i;
        $gambar_lama_file = $produk_data_lama[$input_file_name] ?? null;
    ?>
    <div class="form-group">
        <label for="<?php echo $input_file_name; ?>">Gambar <?php echo $i; ?>:</label>
        <input type="file" id="<?php echo $input_file_name; ?>" name="<?php echo $input_file_name; ?>" class="form-control-file">
        <input type="hidden" name="<?php echo $input_file_name; ?>_lama" value="<?php echo htmlspecialchars($gambar_lama_file); ?>">
        <?php if ($gambar_lama_file && !empty($target_dir) && file_exists($target_dir . $gambar_lama_file)): // Pastikan $target_dir terdefinisi ?>
            <p style="margin-top: 5px;"><img src="<?php echo BASE_URL . 'assets/img/' . htmlspecialchars($gambar_lama_file); ?>" alt="Gambar <?php echo $i; ?>" class="current-img"><small>(<?php echo htmlspecialchars($gambar_lama_file); ?>)</small></p>
        <?php elseif ($gambar_lama_file): ?>
            <p style="margin-top: 5px; color: #777;"><small>File (<?php echo htmlspecialchars($gambar_lama_file); ?>) tidak ditemukan di server.</small></p>
        <?php endif; ?>
    </div>
    <?php endfor; ?>
    <hr>

    <button type="submit" name="update_produk" class="btn btn-primary">Update Produk</button>
    <a href="produk_list.php" class="btn btn-secondary" style="margin-left:10px;">Batal</a>
</form>
<?php else: ?>
    <?php if (empty($errors)): ?>
        <p style="margin-top: 20px; font-size: 1.1em; color: #777;">Data produk tidak dapat ditampilkan.</p>
    <?php endif; ?>
<?php endif; ?>

<?php
require_once 'includes/footer_admin.php';
?>
