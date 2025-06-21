<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

// Ambil ID rating dari URL
$rating_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    $sql = "UPDATE ratings SET rating = ?, comment = ? WHERE rating_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isi", $rating, $comment, $rating_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: rates.php?success=1");
        exit();
    } else {
        $error = "Gagal mengupdate rating";
    }
}

// Ambil data rating
$sql = "SELECT r.*, p.name as product_name, u.name as user_name 
        FROM ratings r 
        JOIN products p ON r.product_id = p.product_id 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.rating_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $rating_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rating = mysqli_fetch_assoc($result);

// Jika rating tidak ditemukan
if (!$rating) {
    header("Location: rates.php");
    exit();
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
                <h1 class="text-2xl font-semibold text-gray-900">Edit Rating</h1>
                <a href="rates.php" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
                    <span class="material-symbols-rounded">arrow_back</span>
                    Kembali
                </a>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Produk</label>
                        <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($rating['product_name']); ?></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pengguna</label>
                        <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($rating['user_name']); ?></p>
                    </div>

                    <div>
                        <label for="rating" class="block text-sm font-medium text-gray-700">Rating</label>
                        <div class="mt-1 flex items-center gap-1" id="star-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="rating" value="<?php echo $i; ?>"
                                    id="rating<?php echo $i; ?>"
                                    class="hidden"
                                    <?php echo $i == $rating['rating'] ? 'checked' : ''; ?>>
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
                        <textarea id="comment" name="comment" rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($rating['comment']); ?></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Simpan Perubahan
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