<?php
// admin/produk_hapus.php

// 1. SERTAKAN KONFIGURASI DAN AUTENTIKASI
require_once '../includes/config.php';
require_once 'auth_check.php';

$id_produk = null;
$errors = [];
$target_dir = "../assets/img/"; // Direktori gambar

// 2. AMBIL DAN VALIDASI ID PRODUK DARI URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_produk = (int)$_GET['id'];
} else {
    $_SESSION['notification'] = "Error: ID produk tidak valid atau tidak ditemukan untuk dihapus.";
    redirect('produk_list.php');
    // exit; // redirect() sudah ada exit()
}

// 3. AMBIL NAMA FILE GAMBAR SEBELUM MENGHAPUS DARI DB
// Ini penting agar kita bisa menghapus file fisik setelah record DB dihapus.
$gambar_files_to_delete = [];
$sql_get_images = "SELECT gambar1, gambar2, gambar3, gambar4 FROM produk WHERE id_produk = ?";
$stmt_get_images = $conn->prepare($sql_get_images);

if ($stmt_get_images) {
    $stmt_get_images->bind_param("i", $id_produk);
    $stmt_get_images->execute();
    $result_images = $stmt_get_images->get_result();
    if ($result_images->num_rows == 1) {
        $images = $result_images->fetch_assoc();
        if (!empty($images['gambar1'])) $gambar_files_to_delete[] = $images['gambar1'];
        if (!empty($images['gambar2'])) $gambar_files_to_delete[] = $images['gambar2'];
        if (!empty($images['gambar3'])) $gambar_files_to_delete[] = $images['gambar3'];
        if (!empty($images['gambar4'])) $gambar_files_to_delete[] = $images['gambar4'];
    } else {
        // Produk tidak ditemukan, mungkin sudah dihapus
        $_SESSION['notification'] = "Error: Produk dengan ID " . $id_produk . " tidak ditemukan untuk dihapus.";
        redirect('produk_list.php');
        // exit;
    }
    $stmt_get_images->close();
} else {
    $_SESSION['notification'] = "Error: Gagal mempersiapkan query untuk mengambil data gambar. " . $conn->error;
    redirect('produk_list.php');
    // exit;
}


// 4. HAPUS PRODUK DARI DATABASE
// Kita lakukan ini setelah mendapatkan nama file gambar,
// karena jika gagal hapus dari DB, kita tidak ingin gambar terhapus duluan.
$sql_delete = "DELETE FROM produk WHERE id_produk = ?";
$stmt_delete = $conn->prepare($sql_delete);

if ($stmt_delete) {
    $stmt_delete->bind_param("i", $id_produk);
    if ($stmt_delete->execute()) {
        // Jika record DB berhasil dihapus, coba hapus file gambar fisik
        $deleted_files_count = 0;
        $file_deletion_errors = [];

        foreach ($gambar_files_to_delete as $filename) {
            $file_path = $target_dir . $filename;
            if (file_exists($file_path)) {
                if (unlink($file_path)) {
                    $deleted_files_count++;
                } else {
                    $file_deletion_errors[] = "Gagal menghapus file: " . htmlspecialchars($filename);
                }
            }
        }

        $notification_message = "Produk (ID: " . $id_produk . ") berhasil dihapus dari database.";
        if (count($gambar_files_to_delete) > 0) {
            if ($deleted_files_count == count($gambar_files_to_delete) && empty($file_deletion_errors)) {
                $notification_message .= " Semua (" . $deleted_files_count . ") file gambar terkait juga berhasil dihapus.";
            } elseif ($deleted_files_count > 0) {
                 $notification_message .= " " . $deleted_files_count . " dari " . count($gambar_files_to_delete) . " file gambar terkait berhasil dihapus.";
            }
            if (!empty($file_deletion_errors)) {
                $notification_message .= " Error penghapusan file: " . implode(", ", $file_deletion_errors);
            }
        }
        $_SESSION['notification'] = $notification_message;

    } else {
        $_SESSION['notification'] = "Error: Gagal menghapus produk dari database. " . $stmt_delete->error;
    }
    $stmt_delete->close();
} else {
    $_SESSION['notification'] = "Error: Gagal mempersiapkan statement delete. " . $conn->error;
}

$conn->close(); // Tutup koneksi setelah semua operasi selesai

// 5. REDIRECT KEMBALI KE DAFTAR PRODUK
redirect('produk_list.php');
// exit; // redirect() sudah ada exit()
?>
