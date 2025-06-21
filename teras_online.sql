-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 21 Jun 2025 pada 14.06
-- Versi server: 8.0.30
-- Versi PHP: 8.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `teras_online`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `cart_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','checkout','cancelled') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `created_at`, `updated_at`, `status`) VALUES
(1, 1, '2025-05-21 10:23:46', '2025-05-21 13:28:46', 'checkout'),
(2, 2, '2025-05-21 10:28:20', '2025-05-21 10:52:52', 'checkout'),
(3, 2, '2025-05-21 13:15:08', '2025-05-21 13:15:27', 'checkout'),
(4, 1, '2025-05-21 13:32:22', '2025-05-21 13:32:34', 'checkout'),
(5, 2, '2025-05-21 13:53:24', '2025-05-21 13:53:51', 'checkout'),
(6, 2, '2025-05-21 14:14:23', '2025-05-21 14:14:42', 'checkout'),
(7, 2, '2025-05-21 14:37:04', '2025-05-21 14:37:21', 'checkout'),
(8, 2, '2025-05-21 15:02:28', '2025-05-21 15:02:52', 'checkout'),
(9, 1, '2025-05-21 15:37:24', '2025-05-21 15:37:51', 'checkout'),
(10, 2, '2025-05-21 16:18:35', '2025-05-22 04:36:55', 'checkout'),
(11, 2, '2025-05-22 05:37:05', '2025-05-22 05:37:38', 'checkout'),
(12, 2, '2025-05-22 05:39:38', '2025-05-22 12:02:36', 'checkout'),
(13, 1, '2025-05-22 06:11:27', '2025-05-22 06:11:56', 'checkout'),
(14, 1, '2025-05-22 06:24:23', '2025-05-22 06:24:55', 'checkout'),
(15, 1, '2025-05-22 06:28:40', '2025-05-22 06:32:44', 'checkout'),
(16, 1, '2025-05-22 06:56:43', '2025-05-22 06:57:59', 'checkout'),
(17, 2, '2025-05-22 12:04:33', '2025-05-22 12:06:30', 'checkout'),
(18, 2, '2025-05-22 14:06:24', '2025-05-22 14:06:49', 'checkout'),
(20, 1, '2025-05-22 16:41:32', '2025-05-22 16:42:18', 'checkout'),
(21, 1, '2025-05-22 17:09:22', '2025-05-23 06:53:43', 'checkout'),
(22, 1, '2025-05-23 07:12:14', '2025-05-23 07:12:49', 'checkout'),
(23, 2, '2025-05-23 16:04:24', '2025-05-23 16:05:10', 'checkout'),
(24, 1, '2025-05-28 09:44:28', '2025-05-28 09:45:10', 'checkout'),
(25, 5, '2025-05-30 02:42:40', '2025-05-30 02:43:52', 'checkout'),
(26, 5, '2025-05-30 02:45:59', '2025-05-30 02:46:27', 'checkout'),
(27, 5, '2025-05-30 10:56:54', '2025-05-30 10:57:46', 'checkout');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart_items`
--

CREATE TABLE `cart_items` (
  `item_id` int NOT NULL,
  `cart_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `cart_items`
--

INSERT INTO `cart_items` (`item_id`, `cart_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2025-05-21 10:23:46', '2025-05-21 10:28:00'),
(2, 2, 1, 1, '2025-05-21 10:28:20', '2025-05-21 10:29:28'),
(3, 3, 1, 1, '2025-05-21 13:15:08', '2025-05-21 13:15:08'),
(4, 4, 1, 1, '2025-05-21 13:32:22', '2025-05-21 13:32:22'),
(5, 5, 1, 1, '2025-05-21 13:53:24', '2025-05-21 13:53:24'),
(6, 6, 1, 2, '2025-05-21 14:14:23', '2025-05-21 14:14:25'),
(7, 7, 1, 1, '2025-05-21 14:37:04', '2025-05-21 14:37:04'),
(8, 8, 1, 1, '2025-05-21 15:02:28', '2025-05-21 15:02:28'),
(9, 9, 2, 1, '2025-05-21 15:37:24', '2025-05-21 15:37:24'),
(10, 9, 1, 1, '2025-05-21 15:37:30', '2025-05-21 15:37:30'),
(11, 10, 3, 1, '2025-05-21 16:18:35', '2025-05-21 16:18:35'),
(12, 11, 2, 1, '2025-05-22 05:37:05', '2025-05-22 05:37:05'),
(14, 13, 2, 1, '2025-05-22 06:11:27', '2025-05-22 06:11:27'),
(15, 14, 1, 1, '2025-05-22 06:24:23', '2025-05-22 06:24:23'),
(16, 15, 2, 1, '2025-05-22 06:28:40', '2025-05-22 06:28:40'),
(17, 16, 2, 1, '2025-05-22 06:56:43', '2025-05-22 06:56:43'),
(18, 12, 2, 1, '2025-05-22 11:57:24', '2025-05-22 11:57:24'),
(19, 17, 3, 1, '2025-05-22 12:04:33', '2025-05-22 12:04:33'),
(20, 18, 1, 1, '2025-05-22 14:06:24', '2025-05-22 14:06:24'),
(21, 20, 2, 1, '2025-05-22 16:41:32', '2025-05-22 16:41:32'),
(23, 21, 3, 1, '2025-05-23 06:53:11', '2025-05-23 06:53:11'),
(24, 22, 3, 1, '2025-05-23 07:12:14', '2025-05-23 07:12:14'),
(25, 22, 1, 1, '2025-05-23 07:12:20', '2025-05-23 07:12:20'),
(26, 23, 2, 1, '2025-05-23 16:04:24', '2025-05-23 16:04:24'),
(27, 24, 7, 1, '2025-05-28 09:44:28', '2025-05-28 09:44:28'),
(28, 25, 7, 1, '2025-05-30 02:42:40', '2025-05-30 02:42:40'),
(29, 26, 5, 1, '2025-05-30 02:45:59', '2025-05-30 02:45:59'),
(30, 27, 6, 2, '2025-05-30 10:56:54', '2025-05-30 10:57:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cities`
--

CREATE TABLE `cities` (
  `city_id` int NOT NULL,
  `province_id` int DEFAULT NULL,
  `city_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `cities`
--

INSERT INTO `cities` (`city_id`, `province_id`, `city_name`) VALUES
(96, 16, 'Bulungan (Bulongan)'),
(232, 3, 'Lebak');

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `message_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`message_id`, `user_id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 1, 'Restian Baru', 'rdf@gmail.com', 'Pesanan gagal', 'haha', 'unread', '2025-05-22 14:43:43'),
(2, 1, 'Restian Baru', 'rdf@gmail.com', 'Pesanan gagal', 'haha', 'unread', '2025-05-22 14:45:57'),
(7, NULL, 'Sepatu', 'rdf@gmail.com', 'Pesanan gagal', 'lupa', 'read', '2025-05-22 15:55:44'),
(8, 1, 'Sepatu', 'rudi@gmail.com', 'Pesanan gagal', 'jasjak', 'read', '2025-05-22 15:57:32'),
(10, 1, 'Restian Dwi Friwaldi', 'rdf@gmail.com', 'Informasi', 'Saya mencoba memasukan informasi', 'read', '2025-05-23 10:13:23'),
(11, 1, 'Restian Dwi Friwaldi', 'restian.dwi.friwaldi@gmail.com', 'stoknya ga jadi', 'gagalin', 'unread', '2025-05-30 02:52:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `message_replies`
--

CREATE TABLE `message_replies` (
  `reply_id` int NOT NULL,
  `message_id` int NOT NULL,
  `admin_id` int NOT NULL,
  `reply` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `message_replies`
--

INSERT INTO `message_replies` (`reply_id`, `message_id`, `admin_id`, `reply`, `created_at`) VALUES
(1, 7, 3, 'Iya maaf ya', '2025-05-23 06:22:37'),
(2, 8, 3, 'nanti dicek ya', '2025-05-23 06:22:58'),
(3, 8, 3, 'iya sabar ya', '2025-05-23 06:23:57'),
(5, 10, 3, 'Baik Informasinya masuk', '2025-05-23 10:13:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `order_id` int NOT NULL,
  `user_id` int NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `payment_method` enum('transfer','ewallet','cod') NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) NOT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `payment_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expired_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tracking_number` varchar(50) DEFAULT NULL,
  `province_id` varchar(10) DEFAULT NULL,
  `city_id` varchar(10) DEFAULT NULL,
  `province_name` varchar(100) DEFAULT NULL,
  `city_name` varchar(100) DEFAULT NULL,
  `courier` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `recipient_name`, `phone`, `address`, `payment_method`, `total_amount`, `shipping_cost`, `grand_total`, `status`, `payment_token`, `created_at`, `expired_at`, `updated_at`, `tracking_number`, `province_id`, `city_id`, `province_name`, `city_name`, `courier`) VALUES
(25, 1, 'Restian Baru', '12345678', 'taktakan', 'transfer', 13000.00, 15000.00, 28000.00, 'processing', 'a20d6fd3-7e11-4d16-bf01-6fd96d7e0d8d', '2025-05-22 06:11:56', '2025-05-23 06:12:23', '2025-05-22 06:54:46', '122ii33', NULL, NULL, NULL, NULL, 'jnk'),
(26, 1, 'Restian Baru', '0812345678', 'Tebo Jadi', 'transfer', 120000.00, 100000.00, 220000.00, 'cancelled', '05baafa6-3320-49e4-9952-e65349ed2f03', '2025-05-22 06:24:55', '2025-05-23 06:26:13', '2025-05-22 06:48:15', '', '8', '471', 'Jambi', 'Kabupaten Tebo', 'jne'),
(27, 1, 'Restian Baru', '0812345678', 'Jambayi', 'transfer', 13000.00, 65000.00, 78000.00, 'delivered', 'fc70a4ff-247f-46c1-8b34-2c8566e513af', '2025-05-22 06:32:44', '2025-05-23 06:38:28', '2025-05-22 11:49:49', '12233', '8', '156', 'Jambi', 'Kota Jambi', 'jne'),
(28, 1, 'Sepatu', '0812345678', 'Pesan', 'transfer', 13000.00, 215000.00, 228000.00, 'processing', '0cf76885-0a63-4173-8402-a1bd0ab4b095', '2025-05-22 06:57:59', '2025-05-23 07:07:29', '2025-05-22 11:16:07', '890890', '14', '405', 'Kalimantan Tengah', 'Kabupaten Seruyan', 'jne'),
(29, 2, 'Rudi Baru', '5647801', 'Baru aja', 'transfer', 13000.00, 55000.00, 68000.00, 'pending', '65db3b2a-8607-4674-8cf1-f8f18b0124e9', '2025-05-22 12:02:36', '2025-05-23 12:06:37', '2025-05-22 12:06:37', NULL, '3', '232', 'Banten', 'Kabupaten Lebak', 'jne'),
(30, 2, 'Restian Dwi Friwaldi', '085210450391', 'PERUM. RANAU ESTATE 2 BLOK G NO.1 RT.003/RW.004 KEL. PANGGUNGJATI, KEC. TAKTAKAN, KOTA SERANG, BANTEN', 'transfer', 500000.00, 110000.00, 610000.00, 'pending', '68dff24c-c040-487f-b4cc-48fe2295aa6b', '2025-05-22 12:06:30', '2025-05-23 12:06:47', '2025-05-22 12:06:47', NULL, '2', '28', 'Bangka Belitung', 'Kabupaten Bangka Barat', 'jne'),
(31, 2, 'Restian Dwi Friwaldi', '085210450391', 'PERUM. RANAU ESTATE 2 BLOK G NO.1 RT.003/RW.004 KEL. PANGGUNGJATI, KEC. TAKTAKAN, KOTA SERANG, BANTEN', 'transfer', 120000.00, 100000.00, 220000.00, 'pending', NULL, '2025-05-22 14:06:49', NULL, '2025-05-22 14:06:49', NULL, '8', '460', 'Jambi', 'Kabupaten Tanjung Jabung Barat', 'jne'),
(32, 1, 'Restian Baru', '0999', 'Yahahwhayu', 'transfer', 13000.00, 380000.00, 393000.00, 'processing', 'ae0db2ec-585e-4a66-8252-e61b81f15a28', '2025-05-22 16:42:18', '2025-05-23 16:42:23', '2025-05-22 16:43:37', '89899', '7', '88', 'Gorontalo', 'Kabupaten Bone Bolango', 'jne'),
(33, 1, 'Siapa saya', 'saya adalah', 'manusia', 'transfer', 500000.00, 95000.00, 595000.00, 'cancelled', '3ef13778-5e93-4e02-8410-cb76f4d7e7cf', '2025-05-23 06:53:43', '2025-05-24 07:05:31', '2025-05-28 09:45:13', NULL, '1', '32', 'Bali', 'Kabupaten Bangli', 'jne'),
(34, 1, 'Sepatu', '12', 'hahaha', 'transfer', 1000000.00, 110000.00, 1110000.00, 'processing', '8ddae239-375c-4108-acca-32a3882cc8dc', '2025-05-23 07:12:49', '2025-05-24 10:14:39', '2025-05-23 10:21:06', '', '2', '28', 'Bangka Belitung', 'Kabupaten Bangka Barat', 'jne'),
(35, 2, 'Rudi Hartono', '08999', 'Serang', 'transfer', 65000.00, 45000.00, 110000.00, 'delivered', '0d455f50-1100-4d1a-9ec8-6cf5686c7b3a', '2025-05-23 16:05:10', '2025-05-24 16:05:29', '2025-06-21 14:04:21', '', '3', '403', 'Banten', 'Kota Serang', 'jne'),
(36, 1, 'Restian Dwi Friwaldi', '085210450391', 'PERUM. RANAU ESTATE 2 BLOK G NO.1 RT.003/RW.004 KEL. PANGGUNGJATI, KEC. TAKTAKAN, KOTA SERANG, BANTEN', 'transfer', 25000.00, 10000.00, 35000.00, 'processing', 'f7500eb0-ef6f-4a11-8079-97fe345417ad', '2025-05-28 09:45:10', '2025-05-29 09:45:27', '2025-05-28 09:46:22', '890890', '6', '151', 'DKI Jakarta', 'Kota Jakarta Barat', 'jne'),
(37, 5, 'Restian DF Baru', '081291298212', 'Serang, Banten', 'transfer', 25000.00, 10000.00, 35000.00, 'processing', '9527bcfe-6335-4664-9a2d-82fd05a21fb2', '2025-05-30 02:43:52', '2025-05-31 02:44:31', '2025-05-30 02:49:14', '890890', '6', '151', 'DKI Jakarta', 'Kota Jakarta Barat', 'jne'),
(38, 5, 'Rudi', '0999', 'SerangLagi', 'transfer', 30000.00, 235000.00, 265000.00, 'pending', '4ed9e406-ab8b-4256-83db-c62736ca3e96', '2025-05-30 02:46:27', '2025-05-31 02:46:32', '2025-05-30 02:46:33', NULL, '12', '228', 'Kalimantan Barat', 'Kabupaten Landak', 'jne'),
(39, 5, 'Restian Dwi Friwaldi', '085210450391', 'PERUM. RANAU ESTATE 2 BLOK G NO.1 RT.003/RW.004 KEL. PANGGUNGJATI, KEC. TAKTAKAN, KOTA SERANG, BANTEN', 'transfer', 140000.00, 100000.00, 240000.00, 'pending', '93c283fe-21c1-4d77-9529-956f4f3e06bb', '2025-05-30 10:57:46', '2025-05-31 10:57:51', '2025-05-30 10:57:51', NULL, '8', '194', 'Jambi', 'Kabupaten Kerinci', 'jne');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`) VALUES
(21, 25, 2, 1, 13000.00, '2025-05-22 06:11:56'),
(22, 26, 1, 1, 120000.00, '2025-05-22 06:24:55'),
(23, 27, 2, 1, 13000.00, '2025-05-22 06:32:44'),
(24, 28, 2, 1, 13000.00, '2025-05-22 06:57:59'),
(25, 29, 2, 1, 13000.00, '2025-05-22 12:02:36'),
(26, 30, 3, 1, 500000.00, '2025-05-22 12:06:30'),
(27, 31, 1, 1, 120000.00, '2025-05-22 14:06:49'),
(28, 32, 2, 1, 13000.00, '2025-05-22 16:42:18'),
(29, 33, 3, 1, 500000.00, '2025-05-23 06:53:43'),
(30, 34, 3, 1, 500000.00, '2025-05-23 07:12:49'),
(31, 34, 1, 1, 500000.00, '2025-05-23 07:12:49'),
(32, 35, 2, 1, 65000.00, '2025-05-23 16:05:10'),
(33, 36, 7, 1, 25000.00, '2025-05-28 09:45:10'),
(34, 37, 7, 1, 25000.00, '2025-05-30 02:43:52'),
(35, 38, 5, 1, 30000.00, '2025-05-30 02:46:27'),
(36, 39, 6, 2, 70000.00, '2025-05-30 10:57:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `product_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `stock`, `created_at`, `updated_at`) VALUES
(1, 'Monstera Deliciosa', 'Tanaman favorit untuk dekorasi rumah kekinian! Daunnya besar dan berlubang alami, cocok untuk ruang tamu atau sudut baca. Tahan naungan, mudah dirawat.', 95000.00, 10, '2025-05-21 08:45:59', '2025-05-23 08:21:00'),
(2, 'Philodendron Lemon Lime', 'Warna daunnya segar seperti jeruk lemon! Cocok diletakkan di meja kerja atau rak buku. Tumbuh cepat dan bikin ruangan lebih hidup.', 65000.00, 14, '2025-05-21 15:35:20', '2025-05-23 16:05:10'),
(3, 'Calathea Orbifolia', 'Calathea dengan corak garis-garis perak yang elegan. Cocok untuk dekorasi ruang indoor dan menyukai kelembapan. Cantik dan fotogenik!', 75000.00, 6, '2025-05-21 15:58:54', '2025-05-23 08:22:55'),
(4, 'Pilea Peperomioides', 'Tanaman unik dengan daun bundar seperti koin. Simbol keberuntungan dan kemakmuran dari Asia Timur. Pas untuk meja kerja atau dekor minimalis.', 45000.00, 20, '2025-05-23 08:45:03', '2025-05-23 08:45:03'),
(5, 'Syngonium ', 'Tanaman merambat dengan daun berbentuk panah yang cantik. Cocok digantung di dapur atau balkon. Tumbuh cepat dan mudah dirawat.\r\n\r\n', 30000.00, 14, '2025-05-23 08:45:31', '2025-05-30 02:46:27'),
(6, 'Zamioculcas Zamiifolia', 'Tahan segala cuaca dan tetap tumbuh subur. Cocok untuk pemula yang sibuk! Memberikan kesan elegan dan rapi di ruang kerja atau ruang tamu.\r\n\r\n', 70000.00, 13, '2025-05-23 08:47:44', '2025-05-30 10:57:46'),
(7, 'Sansevieria Trifasciata', 'Tumbuhan tahan banting dengan fungsi menyaring udara. Sering jadi pilihan untuk dekor meja atau rak. Perawatan sangat mudah, bahkan tanpa disiram tiap hari.', 25000.00, 11, '2025-05-23 08:54:18', '2025-05-30 02:43:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int NOT NULL,
  `product_id` int NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_url`, `is_primary`, `created_at`) VALUES
(12, 1, 'img/products/68302fecf38a3.jpg', 1, '2025-05-23 08:21:00'),
(13, 2, 'img/products/683030314beed.png', 1, '2025-05-23 08:22:09'),
(14, 3, 'img/products/6830305fe7891.jpg', 1, '2025-05-23 08:22:55'),
(15, 4, 'img/products/6830358f804ee.jpg', 1, '2025-05-23 08:45:03'),
(16, 5, 'img/products/683035aba03ca.jpg', 1, '2025-05-23 08:45:31'),
(17, 6, 'img/products/68303630c749d.png', 1, '2025-05-23 08:47:44'),
(19, 7, 'img/products/683037ba2eab6.png', 1, '2025-05-23 08:54:18'),
(20, 4, 'img/products/6830a2e23776b.jpg', 0, '2025-05-23 16:31:30'),
(21, 7, 'img/products/68391c6ae45c4.jpg', 0, '2025-05-30 02:48:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `provinces`
--

CREATE TABLE `provinces` (
  `province_id` int NOT NULL,
  `province` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `provinces`
--

INSERT INTO `provinces` (`province_id`, `province`) VALUES
(3, 'Banten'),
(16, 'Kalimantan Utara');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ratings`
--

CREATE TABLE `ratings` (
  `rating_id` int NOT NULL,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data untuk tabel `ratings`
--

INSERT INTO `ratings` (`rating_id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 4, 'Mantap', '2025-06-03 04:07:31', '2025-06-03 04:26:56'),
(2, 1, 4, 5, 'bagus', '2025-06-03 04:24:32', '2025-06-03 04:46:20'),
(3, 2, 1, 5, 'Bagus Banget', '2025-06-03 04:28:44', '2025-06-03 04:28:44'),
(4, 2, 2, 5, 'Good Lah', '2025-06-03 04:29:25', '2025-06-03 04:42:22'),
(5, 1, 4, 5, 'Keren update', '2025-06-03 04:33:41', '2025-06-03 04:46:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pembeli','admin') NOT NULL DEFAULT 'pembeli',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Restian DF', 'rdf@gmail.com', '$2y$10$phWcfrRjydE1BqRT28dy5eo83D7R6.Kgj17CbX5o0k0EUgW722dy2', 'pembeli', '2025-05-21 07:41:26'),
(2, 'Rudi', 'rudi@gmail.com', '$2y$10$67P.JxiJ8N1XxjM0PBSXsOrIV.hcjj5BlrdOSw1avOi1sIzko7vmW', 'pembeli', '2025-05-21 07:48:33'),
(3, 'Admin Teras', 'admin@mail.com', '$2y$10$phWcfrRjydE1BqRT28dy5eo83D7R6.Kgj17CbX5o0k0EUgW722dy2', 'admin', '2025-05-21 08:30:32'),
(4, 'Restian Dwi Friwaldi Update', 'res@gmail.com', '$2y$10$j3m8.VQCZTsLoNIC2Dxmwu1kjd/TQteWs3g/LEtysKMdsINvz7DJa', 'pembeli', '2025-05-23 06:11:27'),
(5, 'testing', 'testing@gmail.com', '$2y$10$2L9rife/CFyLPXhIaOQs2e3x.dGKNuU9NMdvqhij4GnwI.TCjJT76', 'pembeli', '2025-05-30 02:42:11');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`city_id`),
  ADD KEY `province_id` (`province_id`);

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `message_replies`
--
ALTER TABLE `message_replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `message_id` (`message_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `province_id` (`province_id`),
  ADD KEY `city_id` (`city_id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indeks untuk tabel `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `provinces`
--
ALTER TABLE `provinces`
  ADD PRIMARY KEY (`province_id`);

--
-- Indeks untuk tabel `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `item_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `message_replies`
--
ALTER TABLE `message_replies`
  MODIFY `reply_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`province_id`);

--
-- Ketidakleluasaan untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `message_replies`
--
ALTER TABLE `message_replies`
  ADD CONSTRAINT `message_replies_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`message_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_replies_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
