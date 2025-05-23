<?php
include 'inc_header.php';
include 'koneksi.php';

// Ambil produk 3 hari terakhir
$sql_popular = "SELECT p.*, pi.image_url 
                FROM products p 
                LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
                WHERE p.stock > 0 
                AND p.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)
                ORDER BY p.created_at DESC";
$result_popular = mysqli_query($conn, $sql_popular);

// Ambil produk yang sudah pernah terjual
$sql_sold = "SELECT p.*, pi.image_url, 
             COALESCE(SUM(oi.quantity), 0) as total_sold
             FROM products p 
             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
             LEFT JOIN order_items oi ON p.product_id = oi.product_id
             LEFT JOIN orders o ON oi.order_id = o.order_id
             WHERE p.stock > 0 
             AND o.status != 'cancelled'
             GROUP BY p.product_id, p.name, p.description, p.price, p.stock, p.created_at, p.updated_at, pi.image_url
             HAVING total_sold > 0
             ORDER BY total_sold DESC";
$result_sold = mysqli_query($conn, $sql_sold);
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teras Online</title>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .font-pacifico {
      font-family: 'Pacifico', cursive;
    }
  </style>
</head>

<body>
  <!-- Navbar -->

  <?php
  include 'inc_navbar.php';
  ?>
  <!-- End Of Navbar -->

  <!-- Jumbotron -->

  <section class="bg-white lg:py-16 dark:bg-gray-900 relative">
    <div class="absolute inset-0">
      <img src="img/banner_index.jpg" alt="Banner" class="w-full h-full object-cover opacity-20">
    </div>
    <div class="px-4 mx-auto max-w-screen-xl text-center z-10 relative pt-24 pb-8">
      <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none text-gray-900 md:text-5xl lg:text-6xl dark:text-white">
        Teras Online
      </h1>
      <p class="mb-8 text-lg font-normal text-gray-500 lg:text-xl sm:px-16 lg:px-48 dark:text-gray-200">
        Temukan berbagai produk berkualitas dengan harga terbaik
      </p>
      <p class="mb-8 text-lg font-normal text-gray-600 lg:text-xl sm:px-16 lg:px-48 dark:text-gray-200 flex items-center justify-center gap-2">
        Yuk, Belanja Sekarang!
        <span class="text-2xl">🛍️</span>
      </p>
  </section>

  <!-- End Of Jumbotron -->
  <!-- content -->

  <section class="py-8">
    <div class="max-w-screen-xl mx-auto px-4">
      <div class="flex justify-between items-center mb-6 bg-blue-200 py-6 px-8 rounded-full">
        <h2 class="text-3xl font-pacifico text-blue-900">Produk Terbaru</h2>
        <div class="flex space-x-2">
          <button onclick="slideLeft()" class="pt-2 pb-1 px-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white transition-colors duration-300">
            <span class="material-symbols-rounded">chevron_left</span>
          </button>
          <button onclick="slideRight()" class="pt-2 pb-1 px-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white transition-colors duration-300">
            <span class="material-symbols-rounded">chevron_right</span>
          </button>
        </div>
      </div>

      <div class="relative overflow-hidden">
        <div id="productCarousel" class="flex flex-col md:flex-row transition-transform duration-500 ease-in-out">
          <?php
          $total_items = 0;
          while ($row = mysqli_fetch_assoc($result_popular)):
            $total_items++;
          ?>
            <div class="w-full md:w-1/2 lg:w-1/3 xl:w-1/4 flex-shrink-0 px-2 mb-4 md:mb-0">
              <a href="product-detail.php?id=<?php echo $row['product_id']; ?>">
                <div class="bg-blue-50 border border-blue-200 rounded-lg overflow-hidden hover:shadow-lg relative">
                  <div class="absolute top-2 right-2 bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-medium transform rotate-12">
                    Baru
                  </div>
                  <img src="<?php echo $row['image_url']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="h-48 mx-auto object-cover w-full" />
                  <div class="p-4">
                    <h3 class="text-lg font-semibold text-blue-600">
                      <?php echo htmlspecialchars($row['name']); ?>
                    </h3>
                    <div class="flex items-center justify-between mt-2 bg-blue-100 p-2 rounded-lg">
                      <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="text-sm text-blue-600">Stok: <span class="font-semibold"><?php echo $row['stock']; ?></span></span>
                      </div>
                      <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="text-sm text-blue-600">
                          <?php
                          $sql_sales = "SELECT SUM(quantity) as total_sold 
                                      FROM order_items 
                                      WHERE product_id = ?";
                          $stmt = mysqli_prepare($conn, $sql_sales);
                          mysqli_stmt_bind_param($stmt, "i", $row['product_id']);
                          mysqli_stmt_execute($stmt);
                          $result_sales = mysqli_stmt_get_result($stmt);
                          $sales = mysqli_fetch_assoc($result_sales);
                          echo "Terjual: <span class='font-semibold'>" . ($sales['total_sold'] ?? 0) . "</span>";
                          ?>
                        </span>
                      </div>
                    </div>
                    <button class="mt-4 w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">
                      Beli Sekarang
                    </button>
                  </div>
                </div>
              </a>
            </div>
          <?php endwhile; ?>
        </div>
        <!-- Indikator Scroll - hanya tampil di desktop -->
        <div class="hidden md:flex justify-center items-center gap-2 mt-4">
          <?php
          $items_per_view = 4; // Jumlah item per view untuk desktop
          $total_pages = ceil($total_items / $items_per_view);

          for ($i = 0; $i < $total_pages; $i++):
          ?>
            <button
              onclick="goToSlide(<?php echo $i; ?>)"
              class="w-2.5 h-2.5 rounded-full transition-all duration-300 <?php echo $i === 0 ? 'bg-blue-600 w-4' : 'bg-gray-300 hover:bg-gray-400'; ?>"
              aria-label="Go to slide <?php echo $i + 1; ?>">
            </button>
          <?php endfor; ?>
        </div>
      </div>

      <script>
        let currentPosition = 0;
        const carousel = document.getElementById('productCarousel');
        const items = carousel.children;
        const itemWidth = items[0].offsetWidth;
        const itemsPerView = window.innerWidth < 768 ? 1 : window.innerWidth < 1024 ? 2 : window.innerWidth < 1280 ? 3 : 4;
        const maxPosition = Math.ceil(items.length / itemsPerView) - 1;
        const indicators = document.querySelectorAll('.hidden.md\\:flex button');

        function updateIndicators() {
          indicators.forEach((indicator, index) => {
            if (index === currentPosition) {
              indicator.classList.add('bg-blue-600', 'w-4');
              indicator.classList.remove('bg-gray-300', 'w-2.5');
            } else {
              indicator.classList.remove('bg-blue-600', 'w-4');
              indicator.classList.add('bg-gray-300', 'w-2.5');
            }
          });
        }

        function slideLeft() {
          if (currentPosition > 0) {
            currentPosition--;
            updateCarousel();
            updateIndicators();
          }
        }

        function slideRight() {
          if (currentPosition < maxPosition) {
            currentPosition++;
            updateCarousel();
            updateIndicators();
          }
        }

        function goToSlide(position) {
          currentPosition = position;
          updateCarousel();
          updateIndicators();
        }

        function updateCarousel() {
          if (window.innerWidth >= 768) {
            carousel.style.transform = `translateX(-${currentPosition * itemWidth * itemsPerView}px)`;
          } else {
            carousel.style.transform = 'none';
          }
        }

        // Update on window resize
        window.addEventListener('resize', () => {
          const newItemsPerView = window.innerWidth < 768 ? 1 : window.innerWidth < 1024 ? 2 : window.innerWidth < 1280 ? 3 : 4;
          if (newItemsPerView !== itemsPerView) {
            currentPosition = 0;
            updateCarousel();
            updateIndicators();
          }
        });

        // Initial setup
        updateCarousel();
      </script>
    </div>
  </section>

  <section class="py-8">
    <div class="max-w-screen-xl mx-auto px-4">
      <div class="flex justify-between items-center mb-6 bg-blue-200 py-6 px-8 rounded-full">
        <h2 class="text-3xl font-pacifico text-blue-900">Produk Terlaris</h2>
        <div class="flex space-x-2">
          <button onclick="slideLeftBestseller()" class="pt-2 pb-1 px-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white transition-colors duration-300">
            <span class="material-symbols-rounded">chevron_left</span>
          </button>
          <button onclick="slideRightBestseller()" class="pt-2 pb-1 px-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white transition-colors duration-300">
            <span class="material-symbols-rounded">chevron_right</span>
          </button>
        </div>
      </div>

      <div class="relative overflow-hidden">
        <div id="bestsellerCarousel" class="flex flex-col md:flex-row transition-transform duration-500 ease-in-out">
          <?php while ($row = mysqli_fetch_assoc($result_sold)): ?>
            <div class="w-full md:w-1/2 lg:w-1/3 xl:w-1/4 flex-shrink-0 px-2 mb-4 md:mb-0">
              <a href="product-detail.php?id=<?php echo $row['product_id']; ?>">
                <div class="bg-blue-50 border border-blue-200 rounded-lg overflow-hidden hover:shadow-lg relative">
                  <img src="<?php echo $row['image_url']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="h-48 mx-auto object-cover w-full" />
                  <?php if ($row['stock'] <= 5): ?>
                    <div class="absolute top-2 ms-2 bg-yellow-400 text-gray-900 text-xs font-bold px-2 py-1 rounded-full">
                      Stok Terbatas
                    </div>
                  <?php endif; ?>
                  <div class="p-4">
                    <h3 class="text-lg font-semibold text-blue-600">
                      <?php echo htmlspecialchars($row['name']); ?>
                    </h3>
                    <div class="flex items-center justify-between mt-2 bg-blue-100 p-2 rounded-lg">
                      <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="text-sm text-blue-600">Stok: <span class="font-semibold"><?php echo $row['stock']; ?></span></span>
                      </div>
                      <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="text-sm text-blue-600">
                          <?php
                          $sql_sales = "SELECT SUM(quantity) as total_sold 
                                      FROM order_items 
                                      WHERE product_id = ?";
                          $stmt = mysqli_prepare($conn, $sql_sales);
                          mysqli_stmt_bind_param($stmt, "i", $row['product_id']);
                          mysqli_stmt_execute($stmt);
                          $result_sales = mysqli_stmt_get_result($stmt);
                          $sales = mysqli_fetch_assoc($result_sales);
                          echo "Terjual: <span class='font-semibold'>" . ($sales['total_sold'] ?? 0) . "</span>";
                          ?>
                        </span>
                      </div>
                    </div>
                    <button class="mt-4 w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">
                      Beli Sekarang
                    </button>
                  </div>
                </div>
              </a>
            </div>
          <?php endwhile; ?>
        </div>
        <!-- Indikator Scroll - hanya tampil di desktop -->
        <div class="hidden md:flex justify-center items-center gap-2 mt-4">
          <?php
          $total_items = mysqli_num_rows($result_sold);
          $items_per_view = 4; // Jumlah item per view untuk desktop
          $total_pages = ceil($total_items / $items_per_view);

          for ($i = 0; $i < $total_pages; $i++):
          ?>
            <button
              onclick="goToSlideBestseller(<?php echo $i; ?>)"
              class="w-2.5 h-2.5 rounded-full transition-all duration-300 <?php echo $i === 0 ? 'bg-blue-600 w-4' : 'bg-gray-300 hover:bg-gray-400'; ?>"
              aria-label="Go to slide <?php echo $i + 1; ?>">
            </button>
          <?php endfor; ?>
        </div>
      </div>

      <script>
        // Bestseller Carousel
        let currentPositionBestseller = 0;
        const bestsellerCarousel = document.getElementById('bestsellerCarousel');
        const bestsellerItems = bestsellerCarousel.children;
        const bestsellerItemWidth = bestsellerItems[0].offsetWidth;
        const bestsellerItemsPerView = window.innerWidth < 768 ? 1 : window.innerWidth < 1024 ? 2 : window.innerWidth < 1280 ? 3 : 4;
        const bestsellerMaxPosition = Math.ceil(bestsellerItems.length / bestsellerItemsPerView) - 1;
        const bestsellerIndicators = document.querySelectorAll('#bestsellerCarousel + div button');

        function updateBestsellerIndicators() {
          bestsellerIndicators.forEach((indicator, index) => {
            if (index === currentPositionBestseller) {
              indicator.classList.add('bg-blue-600', 'w-4');
              indicator.classList.remove('bg-gray-300', 'w-2.5');
            } else {
              indicator.classList.remove('bg-blue-600', 'w-4');
              indicator.classList.add('bg-gray-300', 'w-2.5');
            }
          });
        }

        function slideLeftBestseller() {
          if (currentPositionBestseller > 0) {
            currentPositionBestseller--;
            updateBestsellerCarousel();
            updateBestsellerIndicators();
          }
        }

        function slideRightBestseller() {
          if (currentPositionBestseller < bestsellerMaxPosition) {
            currentPositionBestseller++;
            updateBestsellerCarousel();
            updateBestsellerIndicators();
          }
        }

        function goToSlideBestseller(position) {
          currentPositionBestseller = position;
          updateBestsellerCarousel();
          updateBestsellerIndicators();
        }

        function updateBestsellerCarousel() {
          if (window.innerWidth >= 768) {
            bestsellerCarousel.style.transform = `translateX(-${currentPositionBestseller * bestsellerItemWidth * bestsellerItemsPerView}px)`;
          } else {
            bestsellerCarousel.style.transform = 'none';
          }
        }

        // Update on window resize
        window.addEventListener('resize', () => {
          const newItemsPerView = window.innerWidth < 768 ? 1 : window.innerWidth < 1024 ? 2 : window.innerWidth < 1280 ? 3 : 4;
          if (newItemsPerView !== bestsellerItemsPerView) {
            currentPositionBestseller = 0;
            updateBestsellerCarousel();
            updateBestsellerIndicators();
          }
        });

        // Initial setup
        updateBestsellerCarousel();
      </script>
    </div>
  </section>
  <!-- End Of Content -->
  <!-- footer -->
  <?php
  include 'inc_footer.php';
  ?>

  <!-- End Of Footer -->
</body>
<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

</html>