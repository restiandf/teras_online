<?php
include 'inc_header.php';
?>

<body class="bg-white" style="font-family: 'Inter', sans-serif">
  <!-- navbar -->
  <?php
  include 'inc_navbar.php';
  ?>
  <!-- end of navbar -->
  <section class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
    <div class="pt-20 text-blue-800 font-bold mb-2">
      <h1 class="text-3xl">Daftar</h1>
    </div>

    <hr class="w-full border-t border-gray-200 mb-5">

    <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
      <div class="w-full border border-gray-200 rounded-lg p-4">
        <?php
        if (isset($_GET['error'])) {
          $error = $_GET['error'];
          $errorMessage = '';
          if ($error == 'emptyfields') {
            $errorMessage = 'Mohon isi semua field.';
          } elseif ($error == 'passwordcheck') {
            $errorMessage = 'Konfirmasi kata sandi tidak cocok.';
          } elseif ($error == 'emailtaken') {
            $errorMessage = 'Email sudah terdaftar.';
          } elseif ($error == 'dberror') {
            $errorMessage = 'Terjadi kesalahan database. Mohon coba lagi.';
          }
          if ($errorMessage != '') {
            echo '<div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">' . $errorMessage . '</div>';
          }
        }
        ?>
        <form class="w-full" action="proses_register.php" method="POST">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="mb-2">
              <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
              <div class="relative">
                <div
                  class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                  <span class="material-symbols-rounded text-gray-400 w-5 h-5">mail</span>
                </div>
                <input
                  name="email"
                  type="email"
                  id="email"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-3"
                  placeholder="name@mail.com"
                  required />
              </div>
            </div>
            <div class="mb-2">
              <label for="nama" class="block mb-2 text-sm font-medium text-gray-900">Nama</label>
              <div class="relative">
                <div
                  class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                  <span class="material-symbols-rounded text-gray-400 w-5 h-5">
                    person
                  </span>
                </div>
                <input
                  type="text"
                  id="nama"
                  name="nama"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-3"
                  placeholder="Nama"
                  required="" />
              </div>
            </div>
            <div class="mb-2">
              <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Kata Sandi</label>
              <div class="relative">
                <div
                  class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                  <span class="material-symbols-rounded text-gray-500 dark:text-gray-400 w-5 h-5">lock</span>
                </div>
                <input
                  name="password"
                  type="password"
                  id="password"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-3"
                  placeholder="••••••••"
                  required="" />
              </div>
            </div>
            <div class="mb-6">
              <label for="konfirmasi_password" class="block mb-2 text-sm font-medium text-gray-900">Konfirmasi Kata Sandi</label>
              <div class="relative">
                <div
                  class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                  <span class="material-symbols-rounded text-gray-500 dark:text-gray-400 w-5 h-5">lock</span>
                </div>
                <input
                  type="password"
                  name="konfirmasi_password"
                  id="password"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-3"
                  placeholder="••••••••"
                  required="" />
              </div>
            </div>
          </div>
          <button
            type="submit"
            class="w-full p-3 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            Daftar
          </button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">
          Sudah punya akun?
          <a href="login.php" class="font-medium text-blue-700 hover:text-blue-800 hover:underline transition-colors duration-300">
            Masuk di sini
          </a>
        </p>
      </div>

      <div>
        <img src="img/registration.jpg" alt="" class="rounded-md" />
      </div>
    </div>
  </section>
  <!-- footer -->

  <?php
  include 'inc_footer.php';
  ?>

  <!-- End Of Footer -->
  <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>

</html>