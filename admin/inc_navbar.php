<nav class="fixed top-0 z-50 w-full bg-gray-900">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-start">
                <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                    <span class="sr-only">Open sidebar</span>
                    <span class="material-symbols-rounded">menu</span>
                </button>
                <a href="dashboard.php" class="flex ml-2 md:mr-24">
                    <img src="../img/icon-white.png" class="h-8 mr-3" alt="Teras Logo" />
                </a>
            </div>
            <div class="flex items-center">
                <div class="flex items-center ml-3">
                    <div>
                        <button type="button" class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                            <span class="sr-only">Open user menu</span>
                            <span class="material-symbols-rounded text-white">account_circle</span>
                        </button>
                    </div>
                    <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded shadow" id="dropdown-user">
                        <div class="px-4 py-3" role="none">
                            <p class="text-sm text-gray-900" role="none">
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </p>
                            <p class="text-sm font-medium text-gray-900 truncate" role="none">
                                <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                            </p>
                        </div>
                        <ul class="py-1" role="none">
                            <li>
                                <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Keluar</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>