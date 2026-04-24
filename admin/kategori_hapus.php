<?php
// admin/kategori_hapus.php
require_once '../includes/config.php';
require_once 'includes/auth_check.php'; 

$id_kategori_hapus = null;

// 1. Ambil dan Validasi ID Kategori dari URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_kategori_hapus = (int)$_GET['id'];
} else {
    $_SESSION['notification_kategori'] = "Error: ID kategori tidak valid atau tidak ditemukan untuk dihapus.";
    redirect('kategori_list.php');
}

// Ambil nama kategori untuk notifikasi (sebelum dihapus)
$nama_kategori_dihapus = "Kategori (ID: ".$id_kategori_hapus.")"; // Default
$sql_get_nama = "SELECT nama_kategori FROM kategori WHERE id_kategori = ?";
$stmt_get_nama = $conn->prepare($sql_get_nama);
if ($stmt_get_nama) {
    $stmt_get_nama->bind_param("i", $id_kategori_hapus);
    $stmt_get_nama->execute();
    $result_nama = $stmt_get_nama->get_result();
    if ($result_nama->num_rows == 1) {
        $nama_kategori_dihapus = $result_nama->fetch_assoc()['nama_kategori'];
    }
    $stmt_get_nama->close();
}


// 2. Hapus Kategori dari Database
// PERHATIAN: Foreign key di tabel 'produk' untuk 'id_kategori' sudah kita set 'ON DELETE SET NULL'.
// Artinya, jika kategori ini dihapus, semua produk yang menggunakan kategori ini akan memiliki 'id_kategori' menjadi NULL.
// Jika Mas Dimas ingin perilaku berbeda (misalnya mencegah penghapusan jika masih ada produk, atau menghapus produk terkait),
// logika ini perlu disesuaikan, atau aturan foreign key di database diubah.

$sql_delete = "DELETE FROM kategori WHERE id_kategori = ?";
$stmt_delete = $conn->prepare($sql_delete);

if ($stmt_delete) {
    $stmt_delete->bind_param("i", $id_kategori_hapus);
    if ($stmt_delete->execute()) {
        if ($stmt_delete->affected_rows > 0) {
            $_SESSION['notification_kategori'] = "Kategori '" . htmlspecialchars($nama_kategori_dihapus) . "' berhasil dihapus. Produk yang terkait kini tidak memiliki kategori.";
        } else {
            $_SESSION['notification_kategori'] = "Kategori tidak ditemukan atau sudah dihapus sebelumnya.";
        }
    } else {
        // Cek apakah error disebabkan oleh foreign key constraint lain (jika ada)
        if ($conn->errno == 1451) { // Error code untuk foreign key constraint violation
             $_SESSION['notification_kategori'] = "Error: Kategori '" . htmlspecialchars($nama_kategori_dihapus) . "' tidak dapat dihapus karena masih digunakan oleh data lain (selain produk yang sudah di-handle ON DELETE SET NULL).";
        } else {
            $_SESSION['notification_kategori'] = "Error: Gagal menghapus kategori. " . $stmt_delete->error;
        }
    }
    $stmt_delete->close();
} else {
    $_SESSION['notification_kategori'] = "Error: Gagal mempersiapkan statement delete. " . $conn->error;
}

$conn->close(); 
redirect('kategori_list.php');
?>
