<?php
include 'session.php';

// Hitung jumlah item di keranjang
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $sql = "SELECT SUM(ci.quantity) as total_items 
            FROM cart c 
            JOIN cart_items ci ON c.cart_id = ci.cart_id 
            WHERE c.user_id = ? AND c.status = 'active'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $cart_count = $row['total_items'] ?? 0;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav
    class="bg-gray-100 fixed w-full z-[9999] top-0 start-0 shadow " style="font-family: Inter;">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
        <a href="index.php" class="flex items-center space-x-3 rtl:space-x-reverse">
            <img src="./img/icon.png" class="h-8" alt="Flowbite Logo" />
            <span class="self-center text-2xl font-bold whitespace-nowrap text-gray-800"></span>
        </a>
        <div class="flex md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Tampilkan nama pengguna, ikon keranjang dan tombol Keluar jika sudah login -->
                <div class="flex items-center">
                    <span class="self-center text-gray-900 mr-4 relative">
                        <button id="userMenuButton" class="flex items-center gap-2">
                            <span class="material-symbols-rounded">account_circle</span>
                            <span>Halo, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            <span class="material-symbols-rounded">expand_more</span>
                        </button>
                        <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden">
                            <a href="pembeli/dashboard.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                <span class="material-symbols-rounded text-sm mr-2">dashboard</span>
                                Dashboard
                            </a>
                            <a href="pembeli/orders.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                <span class="material-symbols-rounded text-sm mr-2">shopping_bag</span>
                                Pesanan Saya
                            </a>
                            <a href="pembeli/profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                <span class="material-symbols-rounded text-sm mr-2">person</span>
                                Profil
                            </a>
                            <a href="pembeli/laporan.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                <span class="material-symbols-rounded text-sm mr-2">contact_support</span>
                                Laporan Saya
                            </a>
                            <div class="border-t my-2"></div>
                            <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
                                <span class="material-symbols-rounded text-sm mr-2">logout</span>
                                Keluar
                            </a>
                        </div>
                    </span>
                    <a href="cart.php" class="relative mr-4">
                        <span class="material-symbols-rounded text-gray-800 hover:text-blue-700">
                            shopping_cart
                        </span>
                        <!-- Badge untuk jumlah item di keranjang -->
                        <?php if ($cart_count > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                <?php echo $cart_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>

            <?php else: ?>
                <!-- Tampilkan tombol Daftar dan Login jika belum login -->
                <a href="register.php">
                    <button
                        type="button"
                        class="text-white md:mr-4 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm px-4 py-2 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Daftar
                    </button>
                </a>
                <a href="login.php">
                    <button
                        type="button"
                        class="text-blue-700 border border-blue-700 hover:bg-blue-700 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm px-4 py-2 text-center transition-colors duration-300">
                        Login
                    </button>
                </a>
            <?php endif; ?>
            <button
                data-collapse-toggle="navbar-sticky"
                type="button"
                class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                aria-controls="navbar-sticky"
                aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg
                    class="w-5 h-5"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 17 14">
                    <path
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M1 1h15M1 7h15M1 13h15" />
                </svg>
            </button>
        </div>
        <div
            class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1"
            id="navbar-sticky">
            <ul
                class="flex flex-col p-4 md:p-0 mt-4 font-medium border border-gray-100 rounded-lg md:space-x-6 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:justify-around w-full">
                <li>
                    <a
                        href="index.php"
                        class="block py-2 <?php echo $current_page == 'index.php' ? 'text-white bg-blue-700 md:bg-transparent md:text-blue-700' : 'text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700'; ?> rounded-sm md:p-0"
                        <?php echo $current_page == 'index.php' ? 'aria-current="page"' : ''; ?>>Beranda</a>
                </li>
                <li>
                    <a
                        href="semua_produk.php"
                        class="block py-2 px-3 <?php echo $current_page == 'semua_produk.php' ? 'text-white bg-blue-700 md:bg-transparent md:text-blue-700' : 'text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700'; ?> rounded-sm md:p-0"
                        <?php echo $current_page == 'semua_produk.php' ? 'aria-current="page"' : ''; ?>>Semua Produk</a>
                </li>
                <li>
                    <a
                        href="contact.php"
                        class="block py-2 px-3 <?php echo $current_page == 'contact.php' ? 'text-white bg-blue-700 md:bg-transparent md:text-blue-700' : 'text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700'; ?> rounded-sm md:p-0"
                        <?php echo $current_page == 'contact.php' ? 'aria-current="page"' : ''; ?>>Kontak Kami</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userMenuButton = document.getElementById('userMenuButton');
        const userMenu = document.getElementById('userMenu');
        let isMenuOpen = false;
        let menuTimeout;

        // Fungsi untuk menampilkan menu
        function showMenu() {
            userMenu.classList.remove('hidden');
            isMenuOpen = true;
        }

        // Fungsi untuk menyembunyikan menu
        function hideMenu() {
            userMenu.classList.add('hidden');
            isMenuOpen = false;
        }

        // Event click pada tombol menu
        userMenuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            if (isMenuOpen) {
                hideMenu();
            } else {
                showMenu();
            }
        });

        // Event hover pada menu
        userMenu.addEventListener('mouseenter', function() {
            clearTimeout(menuTimeout);
        });

        userMenu.addEventListener('mouseleave', function() {
            menuTimeout = setTimeout(hideMenu, 300);
        });

        // Event click di luar menu untuk menutup menu
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target) && !userMenuButton.contains(e.target)) {
                hideMenu();
            }
        });
    });
</script>