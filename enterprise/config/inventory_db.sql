-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 09, 2026 at 03:08 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 5,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `sku`, `category`, `price`, `cost_price`, `quantity`, `reorder_level`, `description`, `created_at`) VALUES
(17, 'zinc', 'zs', 'metals', 1000.00, 9000.00, 0, 5, 'metals', '2026-01-27 23:22:38'),
(18, 'nails', 'ns', 'metals', 2000.00, 1500.00, 182, 5, 'mai hula', '2026-01-27 23:23:26'),
(19, 'mbf', 'wd', 'wood', 12000.00, 10000.00, 84, 5, 'mbf', '2026-01-27 23:24:27'),
(20, 'ply wood', 'pwd', 'wood', 12000.00, 10000.00, 5, 5, 'oly wood', '2026-01-27 23:25:32'),
(21, 'hinges', 'hg', 'metals', 100.00, 70.00, 99919, 5, 'hinges kanana', '2026-01-27 23:26:43');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(12,2) NOT NULL,
  `received_at` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `supplier_id`, `user_id`, `product_id`, `quantity`, `unit_cost`, `total_cost`, `received_at`, `created_at`) VALUES
(1, 1, NULL, 18, 56, 20.00, 1120.00, '2026-08-05', '2026-08-05 21:54:58');

--
-- Triggers `purchases`
--
DELIMITER $$
CREATE TRIGGER `trg_purchases_after_insert` AFTER INSERT ON `purchases` FOR EACH ROW BEGIN
    UPDATE products
    SET quantity = quantity + NEW.quantity
    WHERE id = NEW.product_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `paid_amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sale_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `user_id`, `total_amount`, `paid_amount`, `payment_method`, `customer_name`, `created_at`, `sale_date`) VALUES
(1, NULL, 15000.00, 15000.00, 'Cash', 'John Doe', '2025-09-10 06:42:35', '2025-11-18 14:55:29'),
(2, NULL, 22000.00, 20000.00, 'Bank Transfer', 'Mary Johnson', '2025-09-10 06:42:35', '2025-11-18 14:55:29'),
(3, NULL, 7500.00, 5000.00, 'POS', 'Abdul Rahman', '2025-09-10 06:42:35', '2025-11-18 14:55:29'),
(4, NULL, 12000.00, 12000.00, 'Cash', 'Grace Williams', '2025-09-10 06:42:35', '2025-11-18 14:55:29'),
(5, NULL, 18500.00, 10000.00, 'Mobile Money', 'Peter Obi', '2025-09-10 06:42:35', '2025-11-18 14:55:29'),
(6, NULL, 840000.00, 120000.00, 'Cash', 'abdulrazak', '2025-11-18 13:28:52', '2025-11-18 14:55:29'),
(7, NULL, 240000.00, 120000.00, 'Cash', 'musa', '2025-11-18 13:48:29', '2025-11-18 14:55:29'),
(8, NULL, 120000.00, 120000.00, 'Cash', '', '2025-12-17 13:48:57', '2025-12-17 14:48:57'),
(9, NULL, 2160000.00, 2160000.00, 'Cash', 'aliyu', '2026-01-27 18:27:28', '2026-01-27 19:27:28'),
(10, NULL, 24000.00, 12000.00, 'Cash', '', '2026-01-27 23:46:21', '2026-01-28 00:46:21'),
(11, NULL, 10300.00, 10300.00, 'Card', 'abdulrazak', '2026-01-27 23:57:26', '2026-01-28 00:57:26'),
(12, NULL, 5940000.00, 5940000.00, 'Cash', 'musa', '2026-01-27 23:59:20', '2026-01-28 00:59:20'),
(13, 4, 85400.00, 100.00, 'Cash', '', '2026-08-05 22:00:24', '2026-08-05 23:00:24');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(13, 2, 2, 3, 5000.00, 15000.00),
(14, 2, 3, 1, 3000.00, 3000.00),
(16, 3, 2, 1, 5000.00, 5000.00),
(20, 5, 2, 2, 5000.00, 10000.00),
(21, 5, 3, 1, 3000.00, 3000.00),
(24, 2, 2, 4, 5500.00, 22000.00),
(25, 3, 3, 3, 2500.00, 7500.00),
(32, 10, 19, 2, 12000.00, 24000.00),
(33, 11, 21, 53, 100.00, 5300.00),
(34, 11, 17, 5, 1000.00, 5000.00),
(35, 12, 20, 495, 12000.00, 5940000.00),
(36, 13, 21, 14, 100.00, 1400.00),
(37, 13, 19, 7, 12000.00, 84000.00);

--
-- Triggers `sale_items`
--
DELIMITER $$
CREATE TRIGGER `trg_sale_items_after_insert` AFTER INSERT ON `sale_items` FOR EACH ROW BEGIN
    UPDATE products
    SET quantity = quantity - NEW.quantity
    WHERE id = NEW.product_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_address', '123 Business Road, City, Country'),
('store_name', 'ERP'),
('store_phone', '08109446520');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact`, `email`, `address`, `created_at`) VALUES
(1, 'hadiza', '0901774774', 'hadiza@gmail.com', 'lagos', '2026-08-05 21:17:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(3, 'admin', '', 'admin123', 'admin', '2025-09-10 05:58:45'),
(4, 'abdul', 'abdulrazakm368@gmail.com', '123456', 'staff', '2025-12-18 13:34:24'),
(5, 'abdulrazakmuhammad', 'abdul@gmail.com', '$2y$10$4dsojbTupASyQujotPVaeOqaP/wgkQ7aZL3SjMFX37lQWO1HKZHmK', 'staff', '2026-08-05 20:08:57'),
(6, 'muhammad', 'muhammad@gmail.com', '$2y$10$rONO1uxpZ.mbAMBVb33YY.gWODsMcWxfN109h9yfaGZjMFpNFIqPy', 'staff', '2026-08-09 08:43:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_purchases_user` (`user_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sales_user` (`user_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `fk_purchases_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sales_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
