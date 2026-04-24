<?php
// admin/user_hapus.php

require_once '../includes/config.php';
require_once 'auth_check.php'; 

$id_user_hapus = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_user_hapus = (int)$_GET['id'];
} else {
    $_SESSION['notification_user'] = "Error: ID pengguna tidak valid atau tidak ditemukan untuk dihapus.";
    redirect('user_list.php');
}

if (isset($_SESSION['user_id']) && $id_user_hapus == $_SESSION['user_id']) {
    $_SESSION['notification_user'] = "Error: Anda tidak dapat menghapus akun Anda sendiri.";
    redirect('user_list.php');
}

// Ambil nama user untuk notifikasi
$user_to_delete_name = "Pengguna (ID: ".$id_user_hapus.")"; 
$sql_get_user_info = "SELECT nama_user, role FROM users WHERE id_user = ?";
$stmt_get_user_info = $conn->prepare($sql_get_user_info);
if ($stmt_get_user_info) {
    $stmt_get_user_info->bind_param("i", $id_user_hapus);
    $stmt_get_user_info->execute();
    $result_user_info = $stmt_get_user_info->get_result();
    if ($result_user_info->num_rows == 1) {
        $user_info = $result_user_info->fetch_assoc();
        $user_to_delete_name = $user_info['nama_user'];
        // Opsional: Tambahan validasi untuk role admin
        // if ($user_info['role'] == 'admin') {
        //     $_SESSION['notification_user'] = "Error: Admin tidak dapat dihapus melalui fitur ini.";
        //     redirect('user_list.php');
        // }
    } else {
        $_SESSION['notification_user'] = "Error: Pengguna yang akan dihapus tidak ditemukan.";
        redirect('user_list.php');
    }
    $stmt_get_user_info->close();
} else {
    $_SESSION['notification_user'] = "Error: Gagal memeriksa info pengguna. ".$conn->error;
    redirect('user_list.php');
}


// *** PENAMBAHAN: Cek apakah user memiliki pesanan terkait ***
$sql_cek_pesanan = "SELECT COUNT(*) as total_pesanan FROM pesanan WHERE id_user = ?";
$stmt_cek_pesanan = $conn->prepare($sql_cek_pesanan);
$can_delete_user = true; // Asumsi awal bisa dihapus

if ($stmt_cek_pesanan) {
    $stmt_cek_pesanan->bind_param("i", $id_user_hapus);
    $stmt_cek_pesanan->execute();
    $result_cek_pesanan = $stmt_cek_pesanan->get_result();
    $data_pesanan = $result_cek_pesanan->fetch_assoc();
    
    if ($data_pesanan && $data_pesanan['total_pesanan'] > 0) {
        $can_delete_user = false; // User punya pesanan, tidak bisa dihapus
        $_SESSION['notification_user'] = "Error: Pengguna '" . htmlspecialchars($user_to_delete_name) . "' tidak dapat dihapus karena masih memiliki riwayat " . $data_pesanan['total_pesanan'] . " pesanan.";
    }
    $stmt_cek_pesanan->close();
} else {
    // Gagal cek pesanan, anggap tidak bisa dihapus untuk keamanan
    $can_delete_user = false; 
    $_SESSION['notification_user'] = "Error: Gagal memeriksa data pesanan pengguna. Penghapusan dibatalkan. " . $conn->error;
}


if ($can_delete_user) {
    // Jika user tidak memiliki pesanan, baru lanjutkan proses hapus
    $sql_delete = "DELETE FROM users WHERE id_user = ?";
    $stmt_delete = $conn->prepare($sql_delete);

    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $id_user_hapus);
        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                $_SESSION['notification_user'] = "Pengguna '" . htmlspecialchars($user_to_delete_name) . "' (ID: " . $id_user_hapus . ") berhasil dihapus.";
                // Di sini bisa ditambahkan logika untuk menghapus file gambar profil user jika ada
            } else {
                 $_SESSION['notification_user'] = "Pengguna (ID: " . $id_user_hapus . ") tidak ditemukan atau sudah dihapus sebelumnya.";
            }
        } else {
            $_SESSION['notification_user'] = "Error: Gagal menghapus pengguna. " . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
        $_SESSION['notification_user'] = "Error: Gagal mempersiapkan statement delete. " . $conn->error;
    }
}
// Jika $can_delete_user false, notifikasi sudah di-set di atas.

$conn->close(); 
redirect('user_list.php');
?>
