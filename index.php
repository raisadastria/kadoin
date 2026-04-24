<?php
// toko_online/index.php
require_once 'includes/header.php'; // Memuat header utama (navigasi, logo, dll.)
?>

<section class="hero-section" 
         style="background-color: #F8F0FF; /* Warna ungu muda dari desain */
                padding: 60px 20px; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                text-align: left;
                min-height: 70vh; /* Agar hero section cukup tinggi */
                ">
    <div class="container" style="display: flex; align-items: center; justify-content: space-between; max-width: 1100px; width:100%;">
        <div class="hero-text" style="max-width: 50%; padding-right: 30px;">
            <span style="font-size: 3em; color: #a076f9; display: block; margin-bottom: 5px; font-weight: 700;">
                KadoIn. </span>
            <h1 style="font-size: 70px; /* Ukuran font besar */
                       margin-bottom: 20px; 
                       line-height: 1.1; 
                       color: #333;
                       font-weight: 900;">
                BLOOM WITH<br>FEELING
            </h1>
            <p style="margin-bottom: 35px; color: #555; font-size: 1.1em; line-height: 1.6;">
            Bring happiness through a selection of unique and memorable gifts for every special moment in your life.
            </p>
            <a href="produk.php" class="cta-button" 
               style="background: linear-gradient(to right, #C084FC, #8B5CF6); /* Gradasi dari desain checkout */
                      color: white; 
                      padding: 15px 35px; 
                      text-decoration: none; 
                      border-radius: 8px; /* Sedikit lebih besar radiusnya */
                      font-weight: 600;
                      font-size: 1.1em;
                      box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
                      transition: transform 0.2s, box-shadow 0.2s;">
                Find Your Gift
            </a>
        </div>
        <div class="hero-image" style="max-width: 45%;">
            <img src="assets/img/Logo/1.png" alt="Buket Bunga Cantik" 
                 style="max-width: 100%; height: auto; border-radius: 10px; 
                        box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        </div>
    </div>
</section>

<section class="featured-products-section" style="padding: 60px 20px; text-align: center; background-color: #fff;">
    <div class="container" style="max-width: 1200px; margin:auto;">
        <h2 style="font-size: 50px; margin-bottom: 25px; color: #333333; text-align: center; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px;">
            Gift For Every Moment
        </h2>
        
        <?php
        $kategori_filters_home = [
            'Combination' => 'combination', 'Artificial Flower' => 'artificial',
            // 'Money Bouquet' => 'money', 'Polaroid Bouquet' => 'polaroid',
            'Man Bouquet' => 'pipe', 'Butterfly Bouquet' => 'butterfly'
        ];
        $filter_kategori_home_aktif = isset($_GET['kategori_home']) ? $_GET['kategori_home'] : '';
        ?>
        <div class="category-filters" style="margin-bottom: 40px; text-align: center; padding-bottom: 25px; border-bottom: 1px solid #eeeeee;">
            <a href="<?php echo BASE_URL; ?>produk.php" style="margin: 0 5px 10px 5px; padding: 7px 16px; text-decoration: none; 
                      color: #585858; 
                      background-color: #f5f5f5; 
                      border-radius: 16px; font-size: 13px; font-weight: 500; display: inline-block;">
                All Products
            </a>
            <?php foreach ($kategori_filters_home as $nama => $slug): ?>
                <a href="<?php echo BASE_URL; ?>produk.php?kategori=<?php echo $slug; ?>" 
                   style="margin: 0 5px 10px 5px; padding: 7px 16px; text-decoration: none; 
                          color: #585858; 
                          background-color: #f5f5f5; 
                          border-radius: 16px; font-size: 13px; font-weight: 500; display: inline-block;">
                    <?php echo htmlspecialchars($nama); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="produk-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px;">
            <?php
            // Ambil beberapa produk terbaru atau unggulan (misalnya 8 produk)
            $sql_home_produk = "SELECT id_produk, nama_produk, harga, gambar1 FROM produk ORDER BY created_at DESC LIMIT 8";
            $result_home_produk = $conn->query($sql_home_produk);

            if ($result_home_produk && $result_home_produk->num_rows > 0) {
                while($row_home = $result_home_produk->fetch_assoc()) {
                    // Kode untuk menampilkan item produk (sama seperti di produk.php)
                    echo '<div class="produk-item" 
                                 style="border: 1px solid #f0f0f0; border-radius: 10px; overflow: hidden;
                                        background-color: #ffffff; display: flex; flex-direction: column; 
                                        transition: box-shadow 0.3s ease-in-out, transform 0.3s ease-in-out;
                                        box-shadow: 0 2px 5px rgba(0,0,0,0.05);">';
                    echo '  <a href="detail_produk.php?id=' . $row_home["id_produk"] . '" style="text-decoration: none; color: inherit; display: block;">';
                    $gambar_url_home = (!empty($row_home['gambar1']) && file_exists('assets/img/' . $row_home['gambar1']))
                                  ? BASE_URL . 'assets/img/' . $row_home['gambar1']
                                  : 'https://placehold.co/280x280/F8F8F8/CDCDCD?text=KadoIn&font=poppins';
                    echo '      <img src="' . $gambar_url_home . '" alt="' . htmlspecialchars($row_home["nama_produk"]) . '" 
                                 style="width: 100%; height: 260px; object-fit: cover; display: block;">';
                    echo '  </a>';
                    echo '  <div class="produk-info" 
                                 style="padding: 12px 15px 15px 15px; text-align: left; flex-grow: 1; 
                                        display: flex; flex-direction: column;">';
                    echo '      <h4 style="font-size: 15px; margin: 0 0 6px 0; color: #3F3F3F; 
                                           font-weight: 500; line-height: 1.4; height: 42px; 
                                           overflow: hidden; text-overflow: ellipsis; display: -webkit-box;
                                           -webkit-line-clamp: 2; -webkit-box-orient: vertical;">' 
                                . htmlspecialchars($row_home["nama_produk"]) . 
                           '</h4>';
                    echo '      <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 8px;">';
                    echo '          <p style="font-weight: 600; color: #333333; font-size: 16px; margin: 0;">' 
                                . format_rupiah($row_home["harga"]) . 
                               '</p>';
                    echo '          <a href="keranjang.php?action=add&id=' . $row_home["id_produk"] . '&qty=1" title="Tambah ke Keranjang"
                                   style="color: #D1B5FF; font-size: 20px; text-decoration:none;
                                          padding: 5px; display:inline-block;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16">
                                      <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.49.402H3.21l.94 4.705A.5.5 0 0 0 4.5 14h7a.5.5 0 0 0 .5-.5.5.5 0 0 0-.5-.5h-7a.5.5 0 0 0-.485-.621L3.166 6.621 2.5 2.5H.5a.5.5 0 0 1-.5-.5zM3.14 4l.75 3.75h9.02l.75-3.75H3.14zM5 13a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0z"/>
                                    </svg>
                                  </a>';
                    echo '      </div>';
                    echo '  </div>';
                    echo '</div>';
                }
            } else {
                echo "<p>Belum ada produk unggulan untuk ditampilkan.</p>";
            }
            ?>
        </div>
         <div style="margin-top: 40px;">
            <a href="produk.php" class="cta-button-outline" 
               style="border: 2px solid #D1B5FF; 
                      color: #77569a; 
                      padding: 12px 30px; 
                      text-decoration: none; 
                      border-radius: 8px; 
                      font-weight: 600;
                      font-size: 1em;
                      transition: background-color 0.2s, color 0.2s;">
                Lihat Semua Produk
            </a>
        </div>
    </div>
</section>

<section class="stats-section" style="background-color: #F8F0FF; padding: 50px 20px; text-align: center;">
    <div class="container" style="max-width:900px; margin:auto; display: flex; justify-content: space-around; align-items: center; flex-wrap:wrap; gap:20px;">
        <div class="stat-item" style="min-width: 200px;">
            <h3 style="font-size: 2.8em; color: #77569a; margin: 0 0 5px 0; font-weight: 700;">8.500+</h3>
            <p style="margin: 0; color: #555; font-size: 1.1em;">Happy Customers</p>
        </div>
        <div class="stat-item" style="min-width: 200px;">
            <h3 style="font-size: 2.8em; color: #77569a; margin: 0 0 5px 0; font-weight: 700;">2.000+</h3>
            <p style="margin: 0; color: #555; font-size: 1.1em;">Products Delivered</p>
        </div>
        <div class="stat-item" style="min-width: 200px;">
            <h3 style="font-size: 2.8em; color: #77569a; margin: 0 0 5px 0; font-weight: 700;">7+</h3>
            <p style="margin: 0; color: #555; font-size: 1.1em;">Years Experience</p>
        </div>
    </div>
</section>

<section class="featured-banners-section" style="padding: 60px 20px; background-color: #fff;">
    <div class="container" style="max-width:1100px; margin:auto; display: flex; justify-content: center; gap: 30px; flex-wrap:wrap;">
    <a href="produk.php?kategori=for-her" class="banner-item" 
            style="background-color: #E0CFFC; /* Warna peach soft */
                   padding: 40px; 
                   border-radius: 12px; 
                   text-align: center; 
                   flex: 1; 
                   min-width: 300px; 
                   max-width: 450px;
                   text-decoration:none; color:inherit;
                   box-shadow: 0 5px 15px rgba(0,0,0,0.07); transition: transform 0.3s;">
            <h3 style="margin-top: 0; font-size: 1.8em; color: #58375F; margin-bottom:15px;">FOR HIM</h3>
            <img src="assets/img/Logo/man.jpg" alt="For Her" 
                 style="margin: 15px 0; border-radius: 8px; max-width:300px;">
            <p style="color: #58375F; font-size:0.95em; margin-bottom:20px;">Temukan kado terbaik untuk pria spesial dalam hidup Anda.</p>
            <span style="color: #77569a; text-decoration: none; font-weight: 600; border-bottom: 2px solid #D1B5FF; padding-bottom:3px;">Shop Now &rarr;</span>
        </a>
         <a href="produk.php?kategori=for-her" class="banner-item" 
            style="background-color: #FCE8D5; /* Warna peach soft */
                   padding: 40px; 
                   border-radius: 12px; 
                   text-align: center; 
                   flex: 1; 
                   min-width: 300px; 
                   max-width: 450px;
                   text-decoration:none; color:inherit;
                   box-shadow: 0 5px 15px rgba(0,0,0,0.07); transition: transform 0.3s;">
            <h3 style="margin-top: 0; font-size: 1.8em; color: #D95B43; margin-bottom:15px;">FOR HER</h3>
            <img src="assets/img/Logo/Hr.jpg" alt="For Her" 
                 style="margin: 15px 0; border-radius: 8px; max-width:300px;">
            <p style="color: #D95B43; font-size:0.95em; margin-bottom:20px;">Berikan kejutan manis untuk wanita terkasih dengan pilihan kado dari kami.</p>
            <span style="color: #D95B43; text-decoration: none; font-weight: 600; border-bottom: 2px solid #FBCAB8; padding-bottom:3px;">Shop Now &rarr;</span>
        </a>
    </div>
</section>

<section class="why-choose-us-section" style="padding: 70px 20px; background-color: #F8F0FF;">
     <div class="container" style="max-width:1100px; margin:auto; text-align: center;">
        <h2 style="font-size: 40px; margin-bottom: 50px; color: #333333; font-weight: 500; text-transform: uppercase; letter-spacing: 1.5px;">
            Why Choose Us?
        </h2>
         <div class="features-grid" style="display: flex; justify-content: space-around; align-items:flex-start; flex-wrap:wrap; gap: 0px;">
             <div class="feature-item" style="width: 200%; min-width:280px; text-align: center; padding: 20px;">
                <img src="https://placehold.co/80x80/D1B5FF/FFFFFF?text=Quality&font=poppins" alt="Kualitas Terbaik" 
                     style="margin-bottom: 20px; border-radius:50%;">
                <h4 style="margin-bottom: 10px; font-size: 1.5em; color: #58375F; font-weight:600;">Kualitas Terbaik</h4>
                <p style="color: #555; font-size: 1em; line-height: 1.6;">Kami hanya menggunakan bunga segar dan bahan  <br>berkualitas tinggi untuk setiap rangkaian.</p>
             </div>
             <div class="feature-item" style="width: 30%; min-width:280px; text-align: center; padding: 20px;">
                <img src="https://placehold.co/80x80/D1B5FF/FFFFFF?text=Delivery&font=poppins" alt="Pengiriman Cepat" 
                     style="margin-bottom: 20px; border-radius:50%;">
                <h4 style="margin-bottom: 10px; font-size: 1.5em; color: #58375F; font-weight:600;">Pengiriman Cepat</h4>
                <p style="color: #555; font-size: 1em; line-height: 1.6;">Pesanan Anda akan kami antar dengan cepat dan aman sampai ke tujuan.</p>
             </div>
              <div class="feature-item" style="width: 30%; min-width:280px; text-align: center; padding: 20px;">
                <img src="https://placehold.co/80x80/D1B5FF/FFFFFF?text=Service&font=poppins" alt="Layanan Pelanggan" 
                     style="margin-bottom: 20px; border-radius:50%;">
                <h4 style="margin-bottom: 10px; font-size: 1.5em; color: #58375F; font-weight:600;">Layanan Pelanggan</h4>
                <p style="color: #555; font-size: 1em; line-height: 1.6;">Tim kami siap membantu Anda memilih hadiah yang sempurna untuk setiap momen.</p>
             </div>
         </div>
     </div>
</section>

<?php 
require_once 'includes/footer.php'; // Memuat footer utama
?>
