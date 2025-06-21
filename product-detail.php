<?php
include 'inc_header.php';
include 'koneksi.php';

// Ambil ID produk dari URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query untuk mengambil detail produk
$sql = "SELECT p.*, pi.image_url,
        (SELECT AVG(rating) FROM ratings WHERE product_id = p.product_id) as avg_rating,
        (SELECT COUNT(*) FROM ratings WHERE product_id = p.product_id) as total_ratings
        FROM products p 
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
        WHERE p.product_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

// Jika produk tidak ditemukan, redirect ke halaman utama
if (!$product) {
  header("Location: index.php");
  exit();
}

// Query untuk mengambil semua gambar produk
$sql_images = "SELECT * FROM product_images WHERE product_id = ?";
$stmt_images = mysqli_prepare($conn, $sql_images);
mysqli_stmt_bind_param($stmt_images, "i", $product_id);
mysqli_stmt_execute($stmt_images);
$result_images = mysqli_stmt_get_result($stmt_images);
$images = mysqli_fetch_all($result_images, MYSQLI_ASSOC);

// Query untuk mengambil data rating/ulasan
$sql_ratings = "SELECT r.*, u.name 
                FROM ratings r
                JOIN users u ON r.user_id = u.user_id
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC
                LIMIT 5";
$stmt_ratings = mysqli_prepare($conn, $sql_ratings);
mysqli_stmt_bind_param($stmt_ratings, "i", $product_id);
mysqli_stmt_execute($stmt_ratings);
$result_ratings = mysqli_stmt_get_result($stmt_ratings);
$ratings = mysqli_fetch_all($result_ratings, MYSQLI_ASSOC);
?>

<body>
  <?php
  include 'inc_navbar.php';
  ?>
  <section class="mb-8 pt-24">
    <div class="max-w-screen-xl mx-auto px-4">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-gray-50 border border-gray-200 rounded-lg relative">
          <!-- Gambar Produk -->
          <div id="product-carousel" class="relative w-full" data-carousel="slide">
            <!-- Carousel wrapper -->
            <div class="relative h-96 overflow-hidden rounded-lg">
              <?php foreach ($images as $index => $image): ?>
                <div class="hidden duration-700 ease-in-out flex items-center justify-center h-full <?php echo $index === 0 ? 'active' : ''; ?>" data-carousel-item="<?php echo $index === 0 ? 'active' : ''; ?>">
                  <img src="<?php echo htmlspecialchars($image['image_url']); ?>" class="w-full h-full object-cover" alt="<?php echo htmlspecialchars($product['name']); ?>" />
                </div>
              <?php endforeach; ?>
            </div>
            <!-- Slider indicators -->
            <div class="absolute z-10 flex -translate-x-1/2 bottom-5 left-1/2 space-x-3 rtl:space-x-reverse">
              <?php foreach ($images as $index => $image): ?>
                <button type="button" class="w-3 h-3 rounded-full <?php echo $index === 0 ? 'bg-white' : 'bg-white/50'; ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-label="Slide <?php echo $index + 1; ?>" data-carousel-slide-to="<?php echo $index; ?>"></button>
              <?php endforeach; ?>
            </div>
            <!-- Slider controls -->
            <button type="button" class="absolute top-0 start-0 z-[999] flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none pointer-events-auto" data-carousel-prev>
              <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                <svg class="w-4 h-4 text-white dark:text-gray-800 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4" />
                </svg>
                <span class="sr-only">Previous</span>
              </span>
            </button>
            <button type="button" class="absolute top-0 end-0 z-[999] flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none pointer-events-auto" data-carousel-next>
              <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                <svg class="w-4 h-4 text-white dark:text-gray-800 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4" />
                </svg>
                <span class="sr-only">Next</span>
              </span>
            </button>
          </div>
        </div>
        <div class="px-4 bg-white rounded-lg border border-gray-200">
          <!-- Detail Produk -->
          <div class="">
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>

            <!-- Harga -->
            <div class="mb-2">
              <p class="text-2xl font-bold text-blue-600">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
            </div>

            <!-- Informasi Stok dan Penjualan -->
            <div class="grid grid-cols-2 gap-4 mb-4">
              <!-- Stok -->
              <div class="bg-blue-50 rounded-lg p-2 border border-blue-200">
                <div class="flex items-center space-x-2">
                  <span class="material-symbols-rounded text-blue-600">inventory_2</span>
                  <div>
                    <p class="text-sm text-gray-600">Stok Tersedia</p>
                    <p class="text-lg font-semibold text-blue-600"><?php echo $product['stock']; ?> pcs</p>
                  </div>
                </div>
              </div>

              <!-- Total Penjualan -->
              <div class="bg-green-50 rounded-lg p-2 border border-green-200">
                <div class="flex items-center space-x-2">
                  <span class="material-symbols-rounded text-green-600">trending_up</span>
                  <div>
                    <p class="text-sm text-gray-600">Total Terjual</p>
                    <p class="text-lg font-semibold text-green-600">
                      <?php
                      $sql_sales = "SELECT SUM(quantity) as total_sold 
                                  FROM order_items 
                                  WHERE product_id = ?";
                      $stmt = mysqli_prepare($conn, $sql_sales);
                      mysqli_stmt_bind_param($stmt, "i", $product_id);
                      mysqli_stmt_execute($stmt);
                      $result_sales = mysqli_stmt_get_result($stmt);
                      $sales = mysqli_fetch_assoc($result_sales);
                      echo ($sales['total_sold'] ?? 0) . " pcs";
                      ?>
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Deskripsi -->
            <div class="">
              <h3 class="text-lg font-semibold text-gray-900">Deskripsi Produk</h3>
              <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <!-- Rating -->
            <div class="">
              <h3 class="text-lg font-semibold text-gray-900">Rating Produk</h3>
              <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center">
                  <?php
                  $avg_rating = $product['avg_rating'] ?? 0;
                  for ($i = 1; $i <= 5; $i++):
                  ?>
                    <span class="material-symbols-rounded text-<?php echo $i <= round($avg_rating) ? 'amber-400' : 'gray-300'; ?>">
                      star
                    </span>
                  <?php endfor; ?>
                </div>
                <span class="text-gray-600">(<?php echo number_format($avg_rating, 1); ?>/5)</span>
                <span class="text-gray-600"><?php echo $product['total_ratings'] ?? 0; ?> ulasan</span>
              </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex flex-col sm:flex-row gap-3 sm:space-x-4">
              <button class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm px-3 py-2.5 text-center transition-all duration-200 self-start">
                <span class="material-symbols-rounded">
                  sms
                </span>
              </button>
              <button type="button" onclick="addToCart(<?php echo $product_id; ?>)"
                class="w-full sm:flex-1 text-blue-700 outline outline-2 outline-blue-700 hover:text-white hover:outline-none hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center transition-all duration-200">
                <span class="material-symbols-rounded text-sm mr-2">shopping_cart</span>
                Tambah ke Keranjang
              </button>
              <button type="button" onclick="buyNow(<?php echo $product_id; ?>)"
                class="w-full sm:flex-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center transition-all duration-200">
                <span class="material-symbols-rounded text-sm mr-2">shopping_bag</span>
                Beli Sekarang
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- end of content -->
  <!--penilaian -->
  <section class="mb-2">
    <div class="max-w-screen-xl mx-auto px-4">
      <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <h2 class="flex items-center gap-2 text-2xl font-bold text-blue-600 border-b border-gray-200 pb-4"><span class="material-symbols-rounded text-blue-600"> rate_review</span> Ulasan Pembeli</h2>

        <!-- Daftar Ulasan -->
        <?php
        if (empty($ratings)): ?>
          <div class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-400">Belum ada ulasan untuk produk ini</p>
          </div>
          <?php else:
          foreach ($ratings as $rating):
            $name_parts = explode(' ', $rating['name']);
            $sensitive_name = $name_parts[0] . ' ' . substr($name_parts[1] ?? '', 0, 1) . '***';
          ?>
            <div class="border-gray-200 bg-white ps-2 border-b pt-2">
              <div class="flex items-center mb-2">
                <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-700 font-semibold mr-3">
                  <?php echo strtoupper(substr($rating['name'], 0, 1)); ?>
                </div>
                <div>
                  <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($sensitive_name); ?></div>
                  <p class="text-sm text-gray-500">
                    <?php echo date('d F Y H:i', strtotime($rating['created_at'])); ?>
                  </p>
                  <div>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <span class="material-symbols-rounded text-base <?php echo $i <= $rating['rating'] ? 'text-amber-400' : 'text-gray-300'; ?>">
                        star
                      </span>
                    <?php endfor; ?>
                  </div>
                  <div class="text-gray-700 text-sm mt-1"><?php echo nl2br(htmlspecialchars($rating['comment'])); ?></div>
                </div>
              </div>
            </div>
        <?php
          endforeach;
        endif;
        ?>
      </div>
    </div>
  </section>
  <!-- end of penilaian -->

  <?php
  include 'inc_footer.php';
  ?>

  <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
  <script>
    function addToCart(productId) {
      fetch('add_to_cart.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `product_id=${productId}&quantity=1`
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            alert(data.message);
          } else {
            if (data.message === 'Silakan login terlebih dahulu') {
              window.location.href = 'login.php';
            } else {
              alert(data.message);
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan saat menambahkan ke keranjang');
        });
    }

    function buyNow(productId) {
      fetch('add_to_cart.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `product_id=${productId}&quantity=1`
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            window.location.href = 'checkout.php';
          } else {
            if (data.message === 'Silakan login terlebih dahulu') {
              window.location.href = 'login.php';
            } else {
              alert(data.message);
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan saat memproses pembelian');
        });
    }
  </script>
</body>

</html>