<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

// Ambil daftar produk
$sql_products = "SELECT product_id, name FROM products ORDER BY name ASC";
$result_products = mysqli_query($conn, $sql_products);

// Ambil daftar pengguna
$sql_users = "SELECT user_id, name FROM users WHERE role = 'pembeli' ORDER BY name ASC";
$result_users = mysqli_query($conn, $sql_users);

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)$_POST['product_id'];
    $user_id = (int)$_POST['user_id'];
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // Validasi input
    $errors = [];
    if ($rating < 1 || $rating > 5) {
        $errors[] = "Rating harus antara 1-5";
    }
    if (empty($comment)) {
        $errors[] = "Komentar tidak boleh kosong";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO ratings (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iiis", $product_id, $user_id, $rating, $comment);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: rates.php?success=3");
            exit();
        } else {
            $error = "Gagal menambahkan rating";
        }
    }
}

include 'inc_header.php';
?>

<body class="bg-gray-50" style="font-family: 'Inter', sans-serif">
    <!-- Navbar -->
    <?php include 'inc_navbar.php'; ?>
    <!-- End Of Navbar -->

    <!-- Sidebar -->
    <?php include 'inc_sidebar.php'; ?>
    <!-- End Of Sidebar -->

    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="rounded-lg mt-14">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-semibold text-gray-900">Tambah Rating</h1>
                <a href="rates.php" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
                    <span class="material-symbols-rounded">arrow_back</span>
                    Kembali
                </a>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="product_id" class="block text-sm font-medium text-gray-700">Produk</label>
                        <select id="product_id" name="product_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Produk</option>
                            <?php while ($product = mysqli_fetch_assoc($result_products)): ?>
                                <option value="<?php echo $product['product_id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700">Pengguna</label>
                        <select id="user_id" name="user_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Pengguna</option>
                            <?php while ($user = mysqli_fetch_assoc($result_users)): ?>
                                <option value="<?php echo $user['user_id']; ?>">
                                    <?php echo htmlspecialchars($user['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label for="rating" class="block text-sm font-medium text-gray-700">Rating</label>
                        <div class="mt-1 flex items-center gap-1" id="star-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="rating" value="<?php echo $i; ?>"
                                    id="rating<?php echo $i; ?>"
                                    class="hidden">
                                <label for="rating<?php echo $i; ?>"
                                    class="material-symbols-rounded cursor-pointer text-2xl text-gray-300 hover:text-amber-400"
                                    data-rating="<?php echo $i; ?>">
                                    star
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div>
                        <label for="comment" class="block text-sm font-medium text-gray-700">Komentar</label>
                        <textarea id="comment" name="comment" rows="4" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Simpan Rating
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const starContainer = document.getElementById('star-rating');
            const stars = starContainer.querySelectorAll('label');
            const inputs = starContainer.querySelectorAll('input[type="radio"]');

            // Set initial state based on checked input
            const checkedInput = starContainer.querySelector('input[type="radio"]:checked');
            if (checkedInput) {
                updateStars(parseInt(checkedInput.value));
            }

            stars.forEach((star, index) => {
                star.addEventListener('click', function() {
                    const rating = parseInt(this.dataset.rating);
                    updateStars(rating);
                });

                star.addEventListener('mouseenter', function() {
                    const rating = parseInt(this.dataset.rating);
                    highlightStars(rating);
                });
            });

            starContainer.addEventListener('mouseleave', function() {
                const checkedInput = starContainer.querySelector('input[type="radio"]:checked');
                const rating = checkedInput ? parseInt(checkedInput.value) : 0;
                updateStars(rating);
            });

            function updateStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.remove('text-gray-300');
                        star.classList.add('text-amber-400');
                    } else {
                        star.classList.remove('text-amber-400');
                        star.classList.add('text-gray-300');
                    }
                });
            }

            function highlightStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.remove('text-gray-300');
                        star.classList.add('text-amber-400');
                    } else {
                        star.classList.remove('text-amber-400');
                        star.classList.add('text-gray-300');
                    }
                });
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>

</html>