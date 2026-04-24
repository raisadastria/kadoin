</main>
<footer>
    <p style="text-align: center;">&copy; <?php echo date("Y"); ?> Kadoin. All rights reserved.</p>
</footer>
</main> <footer class="site-footer">
        <div class="container">
            <!-- <p>&copy; <?php echo date('Y'); ?> KadoIn. All Rights Reserved.</p> -->
        </div>
    </footer>

    <script>
        const navToggle = document.getElementById('mobile-nav-toggle');
        const mainNav = document.getElementById('main-nav');

        if (navToggle && mainNav) {
            navToggle.addEventListener('click', function() {
                // Toggle class 'mobile-active' pada navigasi
                mainNav.classList.toggle('mobile-active');

                // Toggle atribut aria-expanded untuk aksesibilitas
                const isExpanded = mainNav.classList.contains('mobile-active');
                navToggle.setAttribute('aria-expanded', isExpanded);
            });
        }
    </script>
</body>
</html>
</body>
</html>
<?php
// Tutup koneksi database jika sudah selesai
if (isset($conn)) {
    $conn->close();
}
?>