<?php
    // admin/includes/footer_admin.php
    ?>
        </main> <footer class="admin-footer">
            <p>&copy; <?php echo date("Y"); ?> Kadoin.</p>
        </footer>

        </body>
    </html>
    <?php
    // Tutup koneksi database jika masih terbuka dan $conn ada (seharusnya sudah ditutup oleh skrip utama jika ada operasi DB)
    // Namun, config.php tidak menutup koneksi secara default, jadi kita bisa cek dan tutup di sini jika perlu
    // atau biarkan PHP menutupnya otomatis di akhir skrip.
    // Untuk konsistensi, jika skrip utama (seperti produk_list.php) membuka dan tidak menutup,
    // footer bisa jadi tempat yang baik untuk memastikan penutupan.
    // if (isset($conn) && $conn instanceof mysqli) {
    //    $conn->close();
    // }
    ?>
    