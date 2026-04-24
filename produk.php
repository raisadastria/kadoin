<?php
// toko_online/produk.php
require_once 'includes/header.php'; 

// Ambil daftar kategori dari database untuk ditampilkan sebagai filter
$sql_get_kategori = "SELECT id_kategori, nama_kategori, slug_kategori FROM kategori ORDER BY nama_kategori ASC";
$result_kategori = $conn->query($sql_get_kategori);
$kategori_filters_db = [];
if ($result_kategori && $result_kategori->num_rows > 0) {
    while ($row_kat = $result_kategori->fetch_assoc()) {
        $kategori_filters_db[] = $row_kat;
    }
}

// Ambil filter kategori dari URL jika ada (berdasarkan slug)
$filter_slug_kategori_aktif = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$id_kategori_aktif = null;
$nama_kategori_aktif_display = "Semua Produk"; // Default jika tidak ada filter

// Cari id_kategori berdasarkan slug_kategori yang aktif
if (!empty($filter_slug_kategori_aktif)) {
    foreach ($kategori_filters_db as $kat_filter) {
        if ($kat_filter['slug_kategori'] == $filter_slug_kategori_aktif) {
            $id_kategori_aktif = $kat_filter['id_kategori'];
            $nama_kategori_aktif_display = $kat_filter['nama_kategori'];
            break;
        }
    }
    // Jika slug tidak ditemukan, mungkin tampilkan semua atau error
    if ($id_kategori_aktif === null && !empty($filter_slug_kategori_aktif)) {
        // Opsi: redirect ke produk.php tanpa filter, atau tampilkan pesan "kategori tidak ditemukan"
        // Untuk sekarang, kita akan tetap menampilkan semua jika slug tidak valid, tapi judul akan "Semua Produk"
        // Atau bisa juga set $filter_slug_kategori_aktif = '' agar tidak ada filter aktif.
    }
}


$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// --- Logika Paginasi ---
$item_per_page = 12; 
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $item_per_page;

// --- Query untuk mengambil produk ---
$sql_base = "FROM produk p "; // Alias 'p' untuk tabel produk
$where_clauses = [];
$params = [];
$types = ""; 

// Tambahkan filter kategori jika ada ID kategori yang aktif
if ($id_kategori_aktif !== null) {
    $where_clauses[] = "p.id_kategori = ?"; // Gunakan alias tabel
    $params[] = $id_kategori_aktif;
    $types .= "i";
}

// Tambahkan filter pencarian jika ada
if (!empty($search_query)) {
    $search_term_like = "%" . $search_query . "%";
    $where_clauses[] = "(p.nama_produk LIKE ? OR p.deskripsi LIKE ?)"; 
    $params[] = $search_term_like;
    $params[] = $search_term_like;
    $types .= "ss";
}

$sql_where = "";
if (!empty($where_clauses)) {
    $sql_where = "WHERE " . implode(" AND ", $where_clauses);
}

// Query untuk total item
$sql_total_items = "SELECT COUNT(p.id_produk) as total " . $sql_base . $sql_where;
$stmt_total = $conn->prepare($sql_total_items);
if ($stmt_total) {
    if (!empty($types)) { 
        $stmt_total->bind_param($types, ...$params);
    }
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total_items = $result_total->fetch_assoc()['total'];
    $stmt_total->close();
} else {
    $total_items = 0; 
}
$total_pages = $total_items > 0 ? ceil($total_items / $item_per_page) : 0;


// Query untuk mengambil produk
$sql_produk = "SELECT p.id_produk, p.nama_produk, p.harga, p.gambar1 " . $sql_base . $sql_where . " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$stmt_produk = $conn->prepare($sql_produk);

$final_params_produk = $params; 
$final_types_produk = $types;  
$final_params_produk[] = $item_per_page; 
$final_types_produk .= "i";
$final_params_produk[] = $offset;        
$final_types_produk .= "i";

if ($stmt_produk) {
    if (!empty($final_types_produk)) { // Hanya bind jika ada parameter
        $stmt_produk->bind_param($final_types_produk, ...$final_params_produk);
    }
    $stmt_produk->execute();
    $result = $stmt_produk->get_result();
} else {
    echo "<p style='text-align:center; color:red;'>Terjadi kesalahan dalam mempersiapkan query produk: " . $conn->error . "</p>";
    $result = false;
}

?>

<div class="container" style="max-width: 1200px; margin: 20px auto; padding: 0 15px; font-family: 'Poppins', sans-serif;">
    
    <?php if (!empty($search_query)): ?>
        <h2 style="font-size: 1.8em; margin-bottom: 20px; color: #333; text-align: left; font-weight: 500;">
            Hasil pencarian untuk: "<?php echo htmlspecialchars($search_query); ?>"
            <?php if ($id_kategori_aktif !== null) echo " dalam kategori '" . htmlspecialchars($nama_kategori_aktif_display) . "'"; ?>
        </h2>
    <?php elseif ($id_kategori_aktif !== null): ?>
         <h2 style="font-size: 28px; margin-bottom: 25px; color: #333333; text-align: center; font-weight: 500; text-transform: uppercase; letter-spacing: 1.5px;">
            <?php echo htmlspecialchars($nama_kategori_aktif_display); ?>
        </h2>
    <?php else: ?>
        <h2 style="font-size: 28px; margin-bottom: 25px; color: #333333; text-align: center; font-weight: 500; text-transform: uppercase; letter-spacing: 1.5px;">
            Gift For Every Occasion
        </h2>
    <?php endif; ?>

    <?php if (!empty($kategori_filters_db)): ?>
    <div class="category-filters" style="margin-bottom: 35px; text-align: center; padding-bottom: 25px; border-bottom: 1px solid #eeeeee;">
        <a href="produk.php?<?php echo !empty($search_query) ? 'q='.urlencode($search_query) : ''; ?>" 
           style="margin: 0 5px 10px 5px; padding: 7px 16px; text-decoration: none; 
                  color: <?php echo (empty($filter_slug_kategori_aktif)) ? '#ffffff' : '#585858'; ?>; 
                  background-color: <?php echo (empty($filter_slug_kategori_aktif)) ? '#D1B5FF' : '#f5f5f5'; ?>; 
                  border-radius: 16px; font-size: 13px; font-weight: 500; display: inline-block;">
            All
        </a>
        <?php foreach ($kategori_filters_db as $kategori): ?>
            <a href="produk.php?kategori=<?php echo $kategori['slug_kategori']; ?><?php echo !empty($search_query) ? '&q='.urlencode($search_query) : ''; ?>" 
               style="margin: 0 5px 10px 5px; padding: 7px 16px; text-decoration: none; 
                      color: <?php echo ($filter_slug_kategori_aktif == $kategori['slug_kategori']) ? '#ffffff' : '#585858'; ?>; 
                      background-color: <?php echo ($filter_slug_kategori_aktif == $kategori['slug_kategori']) ? '#D1B5FF' : '#f5f5f5'; ?>; 
                      border-radius: 16px; font-size: 13px; font-weight: 500; display: inline-block;">
                <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>


    <?php if ($result && $result->num_rows > 0): ?>
        <p style="text-align:left; font-size:0.9em; color:#777; margin-bottom:20px;">Menampilkan <?php echo $result->num_rows; ?> dari <?php echo $total_items; ?> produk.</p>
        <div class="produk-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px;">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="produk-item" style="border: 1px solid #f0f0f0; border-radius: 10px; overflow: hidden; background-color: #ffffff; display: flex; flex-direction: column; transition: box-shadow 0.3s ease-in-out, transform 0.3s ease-in-out; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <a href="detail_produk.php?id=<?php echo $row["id_produk"]; ?>" style="text-decoration: none; color: inherit; display: block;">
                        <?php
                        $gambar_url = (!empty($row['gambar1']) && file_exists('assets/img/' . $row['gambar1']))
                                      ? BASE_URL . 'assets/img/' . $row['gambar1']
                                      : 'https://placehold.co/280x280/F8F8F8/CDCDCD?text=KadoIn&font=poppins';
                        ?>
                        <img src="<?php echo $gambar_url; ?>" alt="<?php echo htmlspecialchars($row["nama_produk"]); ?>" style="width: 100%; height: 260px; object-fit: cover; display: block;">
                    </a>
                    <div class="produk-info" style="padding: 12px 15px 15px 15px; text-align: left; flex-grow: 1; display: flex; flex-direction: column;">
                        <h4 style="font-size: 15px; margin: 0 0 6px 0; color: #3F3F3F; font-weight: 500; line-height: 1.4; height: 42px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"><?php echo htmlspecialchars($row["nama_produk"]); ?></h4>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 8px;">
                            <p style="font-weight: 600; color: #333333; font-size: 16px; margin: 0;"><?php echo format_rupiah($row["harga"]); ?></p>
                            <a href="keranjang.php?action=add&id=<?php echo $row["id_produk"]; ?>&qty=1" title="Tambah ke Keranjang" style="color: #D1B5FF; font-size: 20px; text-decoration:none; padding: 5px; display:inline-block;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.49.402H3.21l.94 4.705A.5.5 0 0 0 4.5 14h7a.5.5 0 0 0 .5-.5.5.5 0 0 0-.5-.5h-7a.5.5 0 0 0-.485-.621L3.166 6.621 2.5 2.5H.5a.5.5 0 0 1-.5-.5zM3.14 4l.75 3.75h9.02l.75-3.75H3.14zM5 13a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination" style="margin-top: 50px; padding-top: 25px; text-align: center; border-top: 1px solid #f0f0f0;">
                <?php
                function build_pagination_query_string_produk($search_query, $filter_slug_kategori_aktif) {
                    $query_params = [];
                    if (!empty($search_query)) $query_params['q'] = urlencode($search_query);
                    if (!empty($filter_slug_kategori_aktif)) $query_params['kategori'] = $filter_slug_kategori_aktif;
                    return http_build_query($query_params);
                }
                $pagination_base_query_produk = build_pagination_query_string_produk($search_query, $filter_slug_kategori_aktif);
                $separator_produk = !empty($pagination_base_query_produk) ? '&' : '';

                if ($current_page > 1) {
                    echo '<a href="produk.php?'.$pagination_base_query_produk.$separator_produk.'page='.($current_page - 1).'" style="padding: 8px 12px; margin: 0 3px; text-decoration: none; color: #777; border-radius: 50%; background-color: #f5f5f5; font-size:14px;">&laquo;</a>';
                }
                
                $num_links_around_current = 1; 
                $show_first_last = true;

                if ($show_first_last && $current_page > ($num_links_around_current + 2)) {
                    echo '<a href="produk.php?'.$pagination_base_query_produk.$separator_produk.'page=1" style="padding: 8px 12px; margin: 0 3px; text-decoration: none; color: #777; border-radius: 50%; background-color: #f5f5f5; font-size:14px;">1</a>';
                    if ($current_page > ($num_links_around_current + 3)) {
                         echo '<span style="padding: 8px 0px; margin: 0 3px; color: #aaa;">...</span>';
                    }
                }
                for ($i = max(1, $current_page - $num_links_around_current); $i <= min($total_pages, $current_page + $num_links_around_current); $i++) {
                    echo '<a href="produk.php?'.$pagination_base_query_produk.$separator_produk.'page='.$i.'" 
                           style="padding: 8px 12px; margin: 0 3px; text-decoration: none; 
                                  color: '.($i == $current_page ? '#fff' : '#777').'; 
                                  background-color: '.($i == $current_page ? '#D1B5FF' : '#f5f5f5').'; 
                                  border-radius: 50%; font-weight: '.($i == $current_page ? 'bold' : 'normal').'; font-size:14px;">'.$i.'</a>';
                }
                if ($show_first_last && $current_page < ($total_pages - $num_links_around_current - 1)) {
                    if ($current_page < ($total_pages - $num_links_around_current - 2)) {
                        echo '<span style="padding: 8px 0px; margin: 0 3px; color: #aaa;">...</span>';
                    }
                    echo '<a href="produk.php?'.$pagination_base_query_produk.$separator_produk.'page='.$total_pages.'" style="padding: 8px 12px; margin: 0 3px; text-decoration: none; color: #777; border-radius: 50%; background-color: #f5f5f5; font-size:14px;">'.$total_pages.'</a>';
                }
                if ($current_page < $total_pages) {
                    echo '<a href="produk.php?'.$pagination_base_query_produk.$separator_produk.'page='.($current_page + 1).'" style="padding: 8px 12px; margin: 0 3px; text-decoration: none; color: #777; border-radius: 50%; background-color: #f5f5f5; font-size:14px;">&raquo;</a>';
                }
                ?>
            </div>
        <?php endif; ?>

    <?php elseif ($total_items == 0 && (!empty($search_query) || !empty($filter_slug_kategori_aktif))): ?>
         <p style="font-size: 1.1em; color: #666; text-align: center; padding: 40px 20px;">
            Oops! Tidak ada produk yang cocok dengan
            <?php echo !empty($search_query) ? "pencarian \"<strong>".htmlspecialchars($search_query)."\"</strong>" : ""; ?>
            <?php echo (!empty($search_query) && !empty($filter_slug_kategori_aktif)) ? " dan " : ""; ?>
            <?php echo !empty($filter_slug_kategori_aktif) ? "kategori \"<strong>".htmlspecialchars($nama_kategori_aktif_display)."\"</strong>" : ""; ?>.
            <br>Coba kata kunci atau filter kategori lainnya.
        </p>
    <?php else: ?>
        <p style="font-size: 1.1em; color: #666; text-align: center; padding: 40px 20px;">Belum ada produk yang tersedia saat ini. Silakan cek kembali nanti.</p>
    <?php endif; ?>
    <?php if ($stmt_produk) $stmt_produk->close(); ?>
</div>

<?php
require_once 'includes/footer.php';
?>
