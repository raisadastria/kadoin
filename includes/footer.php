</main>
<footer class="site-footer">
    <div class="container">
        <!-- <p>&copy; <?php echo date('Y'); ?> KadoIn. All Rights Reserved.</p> -->
    </div>
</footer>

<!-- MODAL LOGOUT PEMBELI -->
<div id="modalLogoutPembeli" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:16px; padding:2rem; width:320px; text-align:center; box-shadow:0 8px 30px rgba(0,0,0,0.15);">
        <div style="width:64px; height:64px; border-radius:50%; background:#f2dede; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; font-size:28px;">🚪</div>
        <h2 style="font-size:18px; font-weight:600; margin-bottom:8px; color:#333;">Yakin mau logout?</h2>
        <p style="font-size:13px; color:#666; margin-bottom:1.5rem;">Kamu akan keluar dari akun KadoIn.</p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <button onclick="tutupModalLogout()" style="padding:10px; border:1px solid #ddd; border-radius:8px; background:#f5f5f5; color:#333; font-size:14px; cursor:pointer; font-family:'Poppins',sans-serif;">Batal</button>
            <a href="<?php echo BASE_URL; ?>logout.php" style="padding:10px; background:#7B2CBF; color:white; border-radius:8px; text-decoration:none; font-size:14px; font-weight:500; display:flex; align-items:center; justify-content:center;">Ya, logout</a>
        </div>
    </div>
</div>

<script>
    const navToggle = document.getElementById('mobile-nav-toggle');
    const mainNav = document.getElementById('main-nav');

    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function() {
            mainNav.classList.toggle('mobile-active');
            const isExpanded = mainNav.classList.contains('mobile-active');
            navToggle.setAttribute('aria-expanded', isExpanded);
        });
    }

    function bukaModalLogout() {
        const modal = document.getElementById('modalLogoutPembeli');
        modal.style.display = 'flex';
    }
    function tutupModalLogout() {
        document.getElementById('modalLogoutPembeli').style.display = 'none';
    }
    document.getElementById('modalLogoutPembeli').addEventListener('click', function(e) {
        if (e.target === this) tutupModalLogout();
    });
</script>
</body>
</html>
<?php
if (isset($conn)) {
    $conn->close();
}
?>