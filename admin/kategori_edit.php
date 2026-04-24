<?php
// admin/kategori_tambah.php
require_once 'includes/header_admin.php'; // Memuat header admin

$nama_kategori = '';
$slug_kategori = '';
$deskripsi_kategori = '';
$errors = [];

// Fungsi sederhana untuk membuat slug
function buatSlug($teks) {
    // Ubah ke huruf kecil
    $teks = strtolower($teks);
    // Ganti spasi dan karakter non-alfanumerik dengan strip
    $teks = preg_replace('/[^a-z0-9]+/', '-', $teks);
    // Hapus strip di awal atau akhir
    $teks = trim($teks, '-');
    return $teks;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kategori = trim($_POST['nama_kategori']);
    $slug_kategori_input = trim($_POST['slug_kategori']);
    $deskripsi_kategori = trim($_POST['deskripsi_kategori']);

    // Validasi
    if (empty($nama_kategori)) {
        $errors[] = "Nama kategori tidak boleh kosong.";
    }

    // Buat slug otomatis jika slug input kosong, atau gunakan inputan user
    if (empty($slug_kategori_input) && !empty($nama_kategori)) {
        $slug_kategori = buatSlug($nama_kategori);
    } else {
        $slug_kategori = buatSlug($slug_kategori_input); // Bersihkan juga slug inputan user
    }
    
    if (empty($slug_kategori)) { // Jika setelah diproses slug tetap kosong (misal nama kategori hanya simbol)
        $errors[] = "Slug kategori tidak valid atau tidak bisa dibuat otomatis dari nama kategori.";
    }

    // Cek keunikan slug (jika tidak ada error sebelumnya)
    if (empty($errors)) {
        $sql_cek_slug = "SELECT id_kategori FROM kategori WHERE slug_kategori = ?";
        $stmt_cek_slug = $conn->prepare($sql_cek_slug);
        if ($stmt_cek_slug) {
            $stmt_cek_slug->bind_param("s", $slug_kategori);
            $stmt_cek_slug->execute();
            $result_cek_slug = $stmt_cek_slug->get_result();
            if ($result_cek_slug->num_rows > 0) {
                $errors[] = "Slug kategori '" . htmlspecialchars($slug_kategori) . "' sudah ada. Silakan gunakan slug lain atau biarkan kosong untuk dibuat otomatis.";
            }
            $stmt_cek_slug->close();
        } else {
            $errors[] = "Gagal memeriksa keunikan slug: " . $conn->error;
        }
    }

    // Jika tidak ada error, simpan kategori baru
    if (empty($errors)) {
        $sql_insert = "INSERT INTO kategori (nama_kategori, slug_kategori, deskripsi_kategori) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        if ($stmt_insert) {
            $stmt_insert->bind_param("sss", $nama_kategori, $slug_kategori, $deskripsi_kategori);
            if ($stmt_insert->execute()) {
                $_SESSION['notification_kategori'] = "Kategori '" . htmlspecialchars($nama_kategori) . "' berhasil ditambahkan.";
                redirect('kategori_list.php');
            } else {
                $errors[] = "Gagal menyimpan kategori: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        } else {
            $errors[] = "Gagal mempersiapkan statement: " . $conn->error;
        }
    }
}
?>

<h2>Tambah Kategori Baru</h2>

<a href="kategori_list.php" class="btn btn-secondary" style="margin-bottom: 20px; display: inline-block; background-color: #6c757d; text-decoration:none;">&laquo; Kembali ke Daftar Kategori</a>

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

<form action="kategori_tambah.php" method="POST">
    <div class="form-group">
        <label for="nama_kategori">Nama Kategori:</label>
        <input type="text" id="nama_kategori" name="nama_kategori" class="form-control" value="<?php echo htmlspecialchars($nama_kategori); ?>" required onkeyup="generateSlug(this.value)">
    </div>
    <div class="form-group">
        <label for="slug_kategori">Slug Kategori:</label>
        <input type="text" id="slug_kategori" name="slug_kategori" class="form-control" value="<?php echo htmlspecialchars($slug_kategori); ?>" placeholder="Kosongkan untuk dibuat otomatis">
        <small>Slug adalah versi URL-friendly dari nama (misal: bunga-mawar-merah). Hanya huruf kecil, angka, dan strip (-).</small>
    </div>
    <div class="form-group">
        <label for="deskripsi_kategori">Deskripsi (Opsional):</label>
        <textarea id="deskripsi_kategori" name="deskripsi_kategori" class="form-control" rows="4"><?php echo htmlspecialchars($deskripsi_kategori); ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Simpan Kategori</button>
</form>

<script>
function generateSlug(text) {
    // Jika admin belum mengetik slug manual, generate otomatis
    var slugInput = document.getElementById('slug_kategori');
    // Cek apakah slugInput masih kosong atau isinya adalah hasil generate sebelumnya dari teks yang sama (kurang lebih)
    // Ini untuk menghindari override jika admin sudah mengetik slug manual.
    // Untuk simplifikasi, kita bisa saja selalu update jika admin tidak mengetik manual.
    // Untuk sekarang, kita biarkan admin bisa override. Jika dikosongkan, PHP akan generate.
    
    // slugInput.value = text
    //     .toLowerCase()
    //     .replace(/\s+/g, '-')           // Ganti spasi dengan -
    //     .replace(/[^\w\-]+/g, '')       // Hapus karakter non-alfanumerik kecuali -
    //     .replace(/\-\-+/g, '-')         // Ganti multiple - dengan satu -
    //     .replace(/^-+/, '')             // Hapus - di awal
    //     .replace(/-+$/, '');            // Hapus - di akhir
    // Logika slug di sisi klien ini hanya untuk bantuan visual, validasi utama tetap di PHP.
}
</script>

<?php
require_once 'includes/footer_admin.php';
?>
