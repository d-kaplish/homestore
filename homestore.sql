-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2026 at 09:06 AM
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
-- Database: `homestore`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `userid` int(11) DEFAULT NULL,
  `prod_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `variant_id` int(11) DEFAULT 0,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `price_at_time` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `userid`, `prod_id`, `quantity`, `variant_id`, `size`, `color`, `price_at_time`) VALUES
(15, 8, 12, 1, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Bedroom', NULL, '2026-04-01 21:46:02'),
(2, 'Kitchen', NULL, '2026-04-01 21:46:02'),
(3, 'Bathroom', NULL, '2026-04-01 21:46:02'),
(4, 'Electricals', NULL, '2026-04-01 21:46:02'),
(5, 'Dining', NULL, '2026-04-01 21:46:02'),
(6, 'Living Room', NULL, '2026-04-01 21:46:02'),
(7, 'Outdoor', NULL, '2026-04-01 21:46:02'),
(8, 'Decor', NULL, '2026-04-01 21:46:02'),
(9, 'Office', NULL, '2026-04-01 21:46:02'),
(10, 'Kids', NULL, '2026-04-01 21:46:02');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order` decimal(10,2) DEFAULT 0.00,
  `valid_until` date NOT NULL,
  `usage_limit` int(11) DEFAULT 1,
  `used_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`coupon_id`, `code`, `discount_type`, `discount_value`, `min_order`, `valid_until`, `usage_limit`, `used_count`, `created_at`) VALUES
(1, 'SAVE15', 'percentage', 15.00, 10000.00, '2026-05-31', 1, 0, '2026-04-06 08:24:00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Waiting to be shipped','Shipped out for delivery','Delivered','Cancelled') NOT NULL DEFAULT 'Waiting to be shipped',
  `coupon_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `order_date`, `status`, `coupon_code`, `discount_amount`) VALUES
(5, 6, 25000.00, '2026-03-31 19:56:57', 'Waiting to be shipped', NULL, 0.00),
(6, 6, 59000.00, '2026-03-31 19:59:26', 'Waiting to be shipped', NULL, 0.00),
(7, 6, 25000.00, '2026-04-01 18:21:50', 'Waiting to be shipped', '', 0.00),
(8, 3, 34000.00, '2026-04-02 04:58:44', 'Waiting to be shipped', '', 0.00),
(9, 7, 44600.00, '2026-04-04 16:11:26', 'Shipped out for delivery', '', 0.00),
(10, 7, 8950.00, '2026-04-04 16:36:58', 'Waiting to be shipped', '', 0.00),
(11, 8, 7000.00, '2026-04-04 16:40:47', 'Cancelled', '', 0.00),
(12, 8, 5000.00, '2026-04-04 16:44:54', 'Cancelled', '', 0.00),
(13, 9, 6550.00, '2026-04-04 16:49:19', 'Shipped out for delivery', '', 0.00),
(14, 9, 45000.00, '2026-04-04 16:49:54', 'Delivered', '', 0.00),
(15, 9, 5450.00, '2026-04-07 03:39:08', 'Shipped out for delivery', '', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `variant_id` int(11) DEFAULT 0,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `price_at_time` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `prod_id`, `quantity`, `variant_id`, `size`, `color`, `price_at_time`) VALUES
(2, 5, 2, 1, 0, NULL, NULL, NULL),
(3, 6, 2, 1, 0, NULL, NULL, NULL),
(4, 6, 3, 1, 0, NULL, NULL, NULL),
(5, 7, 2, 1, 0, 'Standard', 'Default', 25000.00),
(6, 8, 3, 1, 0, 'Standard', 'Default', 34000.00),
(7, 9, 33, 1, 0, 'Standard', 'Default', 1100.00),
(8, 9, 14, 1, 0, 'Standard', 'Default', 4500.00),
(9, 9, 10, 1, 0, 'Standard', 'Default', 15000.00),
(10, 9, 7, 1, 0, 'Standard', 'Default', 24000.00),
(11, 10, 32, 1, 0, 'Standard', 'Default', 950.00),
(12, 10, 28, 1, 0, 'Standard', 'Default', 8000.00),
(13, 11, 20, 1, 0, 'Standard', 'Default', 7000.00),
(14, 12, 19, 1, 0, 'Standard', 'Default', 5000.00),
(15, 13, 32, 1, 0, 'Standard', 'Default', 950.00),
(16, 13, 31, 1, 0, 'Standard', 'Default', 5600.00),
(17, 14, 8, 1, 0, 'Standard', 'Default', 45000.00),
(18, 15, 32, 1, 0, 'Standard', 'Default', 950.00),
(19, 15, 22, 1, 0, 'Standard', 'Default', 4500.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `prod_id` int(11) NOT NULL,
  `p_name` varchar(255) NOT NULL,
  `category` enum('Bedroom','Kitchen','Bathroom','Electricals','Dining','Living Room','Outdoor','Decor','Office','Kids') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `color` varchar(50) NOT NULL,
  `material` enum('Wood','Plastic','Metal','Glass','Fabric','Leather','Marble','Electronic','Other') NOT NULL,
  `brand` varchar(100) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `stock` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `rating` decimal(3,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`prod_id`, `p_name`, `category`, `price`, `color`, `material`, `brand`, `image`, `description`, `stock`, `created_at`, `rating`) VALUES
(2, 'Bed', 'Bedroom', 25000.00, 'Brown', 'Wood', 'Ferguson', '1774995110_bed1.jpg', 'Sheesham Wood bed with storage boxes. Queen Size Bed with sturdy base and 2 year warranty.', 18, '2026-04-01 03:41:50', 0.00),
(3, 'Dining Table', 'Dining', 34000.00, 'White', 'Marble', 'BottoChino', '1774995981_dining.jpg', 'Italian marble dining table with 6 chairs. 6X4 feet in dimensions, this marble table will increase your dining room\'s value.', 33, '2026-04-01 03:56:21', 0.00),
(4, 'Royal Sheesham Wood Bed', 'Bedroom', 45000.00, 'Brown', 'Wood', 'WoodenStreet', '1775317409_bed.jpg', 'Premium Sheesham wood bed with carved headboard and under-bed storage drawers. Queen size.', 23, '2026-04-04 21:13:29', 0.00),
(7, 'Scandinavian Wooden Bed', 'Bedroom', 24000.00, 'Natural Oak', 'Wood', 'IKEA', '1775319219_jhv.jpg', 'Minimalist design bed with solid oak frame. Fits standard mattress. Easy assembly.', 9, '2026-04-04 21:43:39', 0.00),
(8, '4-Door Wardrobe', 'Bedroom', 45000.00, 'Dark Brown', 'Wood', 'Godrej', '1775319578_wd.jpg', '4-Door wardrobe with a safe-lock drawer, hanging section, and matte finish.', 17, '2026-04-04 21:49:38', 0.00),
(10, 'Nightstand with drawers', 'Bedroom', 15000.00, 'Natural Wood', 'Wood', 'WoodenStreet', '1775325567_vzd.jpg', 'Compact nightstand with 2 drawers and open shelf. Perfect for bedside.', 13, '2026-04-04 23:29:27', 0.00),
(11, 'Leather Sofa Set (3+1+1)', 'Living Room', 85000.00, 'Brown', 'Leather', 'UrbanLadder', '1775326717_sofa.jpg', 'Premium genuine leather sofa set with cushioned seats. Includes 3-seater and 2 single seats.', 15, '2026-04-04 23:48:37', 0.00),
(12, 'Glass Dining Table', 'Dining', 35000.00, 'Clear', 'Glass', 'IKEA', '1775326882_din.jfif', 'Tempered glass top with wood finish legs. Seats 6.', 12, '2026-04-04 23:51:22', 0.00),
(13, 'Bar Stools (Set of 2)', 'Dining', 8000.00, 'Black', 'Metal', 'Wakefit', '1775327019_bst.jpg', 'Adjustable height bar stools with footrest and comfortable seating cushion. For kitchen counter.', 16, '2026-04-04 23:53:39', 0.00),
(14, 'Kitchen Storage Rack', 'Kitchen', 4500.00, 'Brown', 'Metal', 'Sparkenzy', '1775327342_large_kitchen_storage_rack_ava_1643957108_b4b3ec13_progressive.jpg', '4-tier adjustable rack for utensils and containers.', 13, '2026-04-04 23:59:02', 0.00),
(15, 'Dinner Set', 'Kitchen', 5500.00, 'Black', 'Metal', 'Borosil', '1775327495_71G1anciFQL._AC_UF1000,1000_QL80_.jpg', 'Microwave safe dinner set for 6 people.', 30, '2026-04-05 00:01:35', 0.00),
(16, 'Ergonomic Office Chair', 'Office', 15000.00, 'Brown', 'Leather', 'Featherlite', '1775327633_3_d7d0db9a-f369-4751-90b5-157ff9085cf0.webp', 'High back mesh office chair with lumbar support.', 13, '2026-04-05 00:03:53', 0.00),
(17, 'Bookshelf', 'Office', 13000.00, 'Brown', 'Wood', 'OrderWood', '1775327754_bookshelf.jpg', 'Criss-Cross wall mounted bookshelf made with pure sheesham wood.', 22, '2026-04-05 00:05:54', 0.00),
(18, 'Wall Mirror', 'Decor', 3500.00, 'Gold', 'Glass', 'The Arts Box', '1775327836_711x4O4Dq1L._AC_UF894,1000_QL80_.jpg', 'Round decorative mirror with gold frame. 24 inches.', 16, '2026-04-05 00:07:16', 0.00),
(19, 'Wall Clock', 'Decor', 5000.00, 'Black', 'Metal', 'Quartz', '1775327938_693bc6da9e28e7f48b080573.jpg', 'Silent quartz wall clock. 14 inches diameter.', 10, '2026-04-05 00:08:58', 0.00),
(20, 'Outdoor Swing', 'Outdoor', 7000.00, 'Black', 'Metal', 'Pigeon', '1775328052_61blod1c+ZL._AC_UF894,1000_QL80_.jpg', 'Hanging swing chair for balcony. With cushions.', 50, '2026-04-05 00:10:52', 4.00),
(21, 'Kids Bed with Storage', 'Kids', 22000.00, 'White', 'Wood', 'IKEA', '1775328153_WKEWB7836SCAPL_LS_1.avif', 'Single bed with 2 drawers underneath. For ages 5-12.', 24, '2026-04-05 00:12:33', 0.00),
(22, 'Storage Unit for Kids', 'Kids', 4500.00, 'White', 'Wood', 'Trove Kids', '1775328305_wefli.jpg', '6-bin storage unit for toys and books.', 23, '2026-04-05 00:15:06', 0.00),
(23, 'Bathroom Vanity Unit', 'Bathroom', 15000.00, 'Grey', 'Wood', 'WoodenStreet', '1775328377_shopping.webp', 'Wall-mounted vanity with basin and mirror.', 24, '2026-04-05 00:16:17', 0.00),
(24, 'Mushroom Lamp', 'Electricals', 2500.00, 'Black', 'Plastic', 'MyAppliance', '1775328587_MUSHROOMMARBLELAMP1.webp', 'Chargeable mushroom shaped table lamp with 27 hours battery life.', 22, '2026-04-05 00:19:47', 0.00),
(25, 'Wireless Speaker with Digital Clock', 'Electricals', 3000.00, 'White', 'Electronic', 'Desk Boss', '1775329017_Xech-Spe3.webp', 'Desk Boss Wireless Speaker with Digital Clock and phone holder. Comes with a charging cable and works up to 36 hours non-stop.', 32, '2026-04-05 00:26:57', 0.00),
(26, 'Bluetooth Speaker', 'Electricals', 3600.00, 'Dark Green', 'Electronic', 'XECH', '1775329106_XechSpeaker.webp', 'Waterproof bluetooth speaker with battery backup of 18 hours. perfect for pool parties with surround sound system.', 15, '2026-04-05 00:28:26', 0.00),
(27, 'Charging Disk', 'Electricals', 1800.00, 'White', 'Plastic', 'Chargo', '1775329186_XECHExtensionBoardwithUSBHeavyDutySurgeProtectorSPikeGuard_2000x2000_1_900x900_crop_center_334eb98c-bd16-4ace-a642-482f5b6717ba.webp', 'Charging Disk with 3 charging slots and phone holder with a cord length of 4 metres.', 54, '2026-04-05 00:29:46', 0.00),
(28, 'Coffee Table', 'Living Room', 8000.00, 'Chestnut Brown', 'Wood', 'IKEA', '1775329341_images.jfif', 'Coffee Table with Metal Legs 115x60x35 cm', 19, '2026-04-05 00:32:21', 0.00),
(29, '6-piece Knife Set', 'Kitchen', 1000.00, 'Black', 'Metal', 'Berlinger Haus', '1775329439_BH-2336_lifestyle_jt_1.jpg', '6-Piece Knife Set Berlinger Haus BH/2336 Black Rose', 35, '2026-04-05 00:33:59', 0.00),
(30, 'Storage Seat', 'Office', 4600.00, 'Black', 'Wood', 'Desker', '1775329530_vari-storage_42365_slate_drawers.jpg', 'Storage seat with 2 drawers for office storage.', 24, '2026-04-05 00:35:30', 0.00),
(31, 'Garden Bench', 'Outdoor', 5600.00, 'Natural Wood', 'Wood', 'Outwards', '1775329684_il_fullxfull.3218465210_l9xq.webp', 'Outdoor garden bench with metal-like look.\r\nCan seat upto 4 people.', 24, '2026-04-05 00:38:04', 0.00),
(32, 'Patio Umbrella', 'Outdoor', 950.00, 'Red', 'Metal', 'TheShelters', '1775329809_61ejIuIy-AL._AC_UY1100_.jpg', 'Patio umbrella with waterproof covering.', 16, '2026-04-05 00:40:09', 0.00),
(33, 'Towel Rack', 'Bathroom', 1100.00, 'Natural Wood', 'Metal', 'Ferguson', '1775329949_Artboard_1_297ceb88-0d53-40e9-9437-a7d69370deb8.jpg', 'Metal towel rack with wooden-like look and small shelf.', 13, '2026-04-05 00:42:29', 0.00),
(34, 'Bed', 'Bedroom', 10000.00, 'Brown', 'Wood', 'IKEA', '1775463695_images.jfif', 'hghhhhbhybhyggg', 1, '2026-04-06 13:51:35', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `additional_price` decimal(10,2) DEFAULT 0.00,
  `stock` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `prod_id`, `rating`, `comment`, `created_at`) VALUES
(1, 8, 20, 4, 'Good product', '2026-04-04 20:22:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `isadmin` enum('yes','no') NOT NULL DEFAULT 'no',
  `orders_made` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userid`, `name`, `email`, `contact`, `address`, `image`, `password`, `isadmin`, `orders_made`, `created_at`) VALUES
(5, 'Harsh Batham', 'harsh@gmail.com', '8983153065', 'Pune', '1775330825_Snapchat-1977957364.jpg', '$2y$10$HqtP5hIAAyIGTRcvNmvoQesWcMG7tdPVdiVHohp6zXuiatyb0wBj2', 'yes', 0, '2026-04-01 01:24:17'),
(7, 'Diya', 'diya@gmail.com', '9888807488', 'Pune', '1775332282_ecbadaf7fda415a71216524f8475a990.jpg', '$2y$10$QdLSEG4IY2O.vgfJiTawr.4vXmg/AGqoRSxXVflttb.lZLn1oFrsG', 'no', 0, '2026-04-05 01:01:48'),
(8, 'Om Sasane', 'om@gmail.com', '1234567890', 'Wanowrie', NULL, '$2y$10$ABJ5kk4aA1oRZzOSLXRr8Oix3Wjq1i9/K4NekUaq..hSEY9mZJvkW', 'no', 0, '2026-04-05 01:40:13'),
(9, 'Prathamesh', 'prathamesh@gmail.com', '9874563210', 'Hadapsar', NULL, '$2y$10$b6UMJ/9AyXCOmVsvFZ/EPOC7skLtE3RuASqQVk0kJpmdv4w210Gu6', 'no', 0, '2026-04-05 01:47:57'),
(10, 'Varun Panjabi', 'varun@gmail.com', '1236547890', 'Pune', NULL, '$2y$10$mktKF1S8h2cVCHVjC.4q7e.cEMkoTXox06rWOlTCSMzh/8WRCUinK', 'yes', 0, '2026-04-05 01:56:20');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `userid` int(11) DEFAULT NULL,
  `prod_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `userid`, `prod_id`) VALUES
(1, 6, 2),
(2, 7, 33),
(3, 7, 30),
(4, 7, 26),
(5, 7, 25),
(6, 8, 23),
(7, 8, 20),
(8, 8, 31),
(9, 9, 32),
(10, 9, 22),
(11, 9, 8),
(12, 9, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `prod_id` (`prod_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`prod_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `prod_id` (`prod_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_review` (`user_id`,`prod_id`),
  ADD KEY `prod_id` (`prod_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `prod_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`prod_id`) REFERENCES `products` (`prod_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`prod_id`) REFERENCES `products` (`prod_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`userid`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`prod_id`) REFERENCES `products` (`prod_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
