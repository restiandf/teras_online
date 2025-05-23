<?php
// Mendapatkan nama file saat ini
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside
    id="logo-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-gray-800 border-r border-gray-700 sm:translate-x-0"
    aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-gray-800">
        <ul class="space-y-2 font-medium">
            <li>
                <a
                    href="index.php"
                    class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700 group <?php echo $current_page == 'index.php' ? 'bg-gray-700 text-white' : ''; ?>">
                    <span class="material-symbols-rounded">analytics</span>
                    <span class="ms-3">Dashboard</span>
                </a>
            </li>
            <li>
                <a
                    href="products.php"
                    class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700 group <?php echo in_array($current_page, ['products.php', 'view_product.php', 'add_product.php', 'edit_product.php']) ? 'bg-gray-700 text-white' : ''; ?>">
                    <span class="material-symbols-rounded">shopping_bag</span>
                    <span class="flex-1 ms-3 whitespace-nowrap">Produk</span>
                </a>
            </li>
            <li>
                <a
                    href="transactions.php"
                    class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700 group <?php echo in_array($current_page, ['transactions.php', 'order_detail.php', 'print_invoice.php']) ? 'bg-gray-700 text-white' : ''; ?>">
                    <span class="material-symbols-rounded">receipt_long</span>
                    <span class="flex-1 ms-3 whitespace-nowrap">Pesanan</span>
                </a>
            </li>
            <li>
                <a
                    href="pengguna.php"
                    class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700 group <?php echo $current_page == 'pengguna.php' ? 'bg-gray-700 text-white' : ''; ?>">
                    <span class="material-symbols-rounded">group</span>
                    <span class="flex-1 ms-3 whitespace-nowrap">Pengguna</span>
                </a>
            </li>
            <li>
                <a
                    href="kontak.php"
                    class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700 group <?php echo $current_page == 'kontak.php' ? 'bg-gray-700 text-white' : ''; ?>">
                    <span class="material-symbols-rounded">contact_support</span>
                    <span class="flex-1 ms-3 whitespace-nowrap">Laporan</span>
                </a>
            </li>
        </ul>
    </div>
</aside>