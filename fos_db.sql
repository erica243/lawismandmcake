-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2024 at 05:10 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `f`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(30) NOT NULL,
  `client_ip` varchar(20) NOT NULL,
  `user_id` int(30) NOT NULL,
  `product_id` int(30) NOT NULL,
  `qty` int(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `client_ip`, `user_id`, `product_id`, `qty`) VALUES
(11, '', 2, 6, 2),
(16, '', 3, 9, 1),
(17, '', 3, 8, 1),
(28, '', 4, 12, 1),
(115, '', 14, 15, 1),
(118, '', 15, 14, 1),
(119, '', 26, 11, 1),
(130, '', 27, 15, 1);

-- --------------------------------------------------------

--
-- Table structure for table `category_list`
--

CREATE TABLE `category_list` (
  `id` int(30) NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category_list`
--

INSERT INTO `category_list` (`id`, `name`) VALUES
(8, 'Baptismal Cake '),
(10, 'Fathers/Mothers Day Cake'),
(19, 'Wedding Cake'),
(23, 'Birthday Cake');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `order_number` int(50) NOT NULL,
  `message` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_reply` text DEFAULT NULL,
  `user_reply` text DEFAULT NULL,
  `reply_date` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(30) NOT NULL,
  `order_number` int(11) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `name` text NOT NULL,
  `address` text NOT NULL,
  `mobile` text NOT NULL,
  `email` text NOT NULL,
  `delivery_method` varchar(100) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `payment_method` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `shipping` int(11) NOT NULL,
  `pickup_date` date DEFAULT NULL,
  `pickup_time` time DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `delivery_status` varchar(100) DEFAULT NULL,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `order_date`, `name`, `address`, `mobile`, `email`, `delivery_method`, `transaction_id`, `payment_method`, `created_at`, `shipping`, `pickup_date`, `pickup_time`, `payment_proof`, `delivery_status`, `status`) VALUES
(58, 9859, '2024-10-16 14:57:00', 'erica adlit', 'wd', '0915829634', 'erica204chavez@gmail.com', 'Delivery', 0, 'gcash', '0000-00-00 00:00:00', 0, '0000-00-00', '00:00:00', NULL, 'arrived', '1'),
(59, 7295, '2024-10-16 15:45:02', 'erica adlit', 'wd', '0915829634', 'erica204chavez@gmail.com', 'Delivery', 0, 'gcash', '0000-00-00 00:00:00', 0, '0000-00-00', '00:00:00', NULL, NULL, '1');

-- --------------------------------------------------------

--
-- Table structure for table `order_list`
--

CREATE TABLE `order_list` (
  `id` int(30) NOT NULL,
  `order_id` int(30) NOT NULL,
  `product_id` int(30) NOT NULL,
  `qty` int(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_list`
--

INSERT INTO `order_list` (`id`, `order_id`, `product_id`, `qty`) VALUES
(54, 0, 12, 1),
(55, 0, 15, 1),
(56, 5, 10, 1),
(57, 6, 11, 1),
(58, 7, 15, 1),
(59, 8, 15, 1),
(60, 9, 12, 1),
(61, 10, 11, 1),
(62, 11, 15, 1),
(63, 12, 19, 1),
(64, 13, 16, 1),
(65, 14, 10, 1),
(66, 15, 10, 1),
(67, 15, 18, 1),
(68, 16, 16, 6),
(69, 17, 16, 1),
(70, 18, 14, 1),
(71, 19, 18, 1),
(72, 20, 14, 1),
(73, 20, 14, 1),
(74, 21, 16, 1),
(75, 22, 14, 1),
(76, 23, 14, 1),
(77, 24, 14, 1),
(78, 25, 16, 1),
(79, 26, 16, 1),
(80, 27, 14, 1),
(81, 28, 16, 1),
(82, 29, 18, 1),
(83, 30, 16, 1),
(84, 31, 18, 1),
(85, 32, 14, 1),
(86, 33, 14, 1),
(87, 34, 14, 1),
(88, 35, 14, 1),
(89, 36, 11, 1),
(90, 37, 11, 1),
(91, 38, 11, 1),
(92, 39, 11, 1),
(93, 40, 15, 1),
(94, 41, 11, 1),
(95, 42, 11, 1),
(96, 43, 11, 1),
(97, 44, 11, 1),
(98, 45, 11, 1),
(99, 45, 15, 1),
(100, 46, 11, 1),
(101, 47, 15, 1),
(102, 48, 11, 1),
(103, 49, 11, 1),
(104, 50, 11, 1),
(105, 51, 11, 1),
(106, 52, 15, 1),
(107, 53, 15, 1),
(108, 53, 11, 1),
(109, 54, 15, 1),
(110, 55, 11, 1),
(111, 55, 15, 1),
(112, 56, 11, 2),
(113, 57, 11, 1),
(114, 58, 11, 1),
(115, 58, 11, 1),
(116, 58, 34, 1),
(117, 58, 34, 1),
(118, 58, 15, 1),
(119, 58, 11, 1),
(120, 58, 11, 1),
(121, 58, 11, 1),
(122, 58, 11, 1),
(123, 59, 15, 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_list`
--

CREATE TABLE `product_list` (
  `id` int(30) NOT NULL,
  `category_id` int(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` float NOT NULL DEFAULT 0,
  `img_path` text NOT NULL,
  `status` varchar(100) NOT NULL,
  `size` varchar(100) NOT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_list`
--

INSERT INTO `product_list` (`id`, `category_id`, `name`, `description`, `price`, `img_path`, `status`, `size`, `stock`) VALUES
(11, 7, 'Wedding Cake', 'fsdf', 55543, '', 'Unavailable', '0', 0),
(15, 8, 'qq', 'as', 1, '', 'Available', '0', 1),
(34, 23, 'jdkjs', 'jgdsagd', 2324, '', 'Unavailable', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_ratings`
--

CREATE TABLE `product_ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `review_date` datetime DEFAULT current_timestamp(),
  `feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_ratings`
--

INSERT INTO `product_ratings` (`id`, `user_id`, `product_id`, `rating`, `review_date`, `feedback`) VALUES
(3, 30, 11, 1, '2024-10-12 20:09:43', 'ali'),
(4, 30, 15, 5, '2024-10-12 23:28:11', 'lami siya');

-- --------------------------------------------------------

--
-- Table structure for table `replies`
--

CREATE TABLE `replies` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `reply` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_info`
--

CREATE TABLE `shipping_info` (
  `id` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `shipping_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_info`
--

INSERT INTO `shipping_info` (`id`, `address`, `municipality`, `email`, `shipping_amount`, `created_at`) VALUES
(152, 'Malbago', NULL, '', 53.00, '2024-10-15 13:54:41'),
(153, 'Tarong', NULL, '', 4.00, '2024-10-15 13:54:49'),
(154, 'Kaongkod', NULL, '', 4.00, '2024-10-15 13:54:59');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_info_bantayan`
--

CREATE TABLE `shipping_info_bantayan` (
  `id` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `shipping_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_info_bantayan`
--

INSERT INTO `shipping_info_bantayan` (`id`, `address`, `municipality`, `shipping_amount`, `created_at`) VALUES
(0, 'Kaongkod', 'Bantayan', 56.00, '2024-10-15 11:46:04');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_info_madridejos`
--

CREATE TABLE `shipping_info_madridejos` (
  `id` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `shipping_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_info_madridejos`
--

INSERT INTO `shipping_info_madridejos` (`id`, `address`, `municipality`, `shipping_amount`, `created_at`) VALUES
(0, 'kangwayan', 'madridejos', 2.00, '2024-10-15 13:51:48');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_info_santa_fe`
--

CREATE TABLE `shipping_info_santa_fe` (
  `id` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `shipping_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_info_santa_fe`
--

INSERT INTO `shipping_info_santa_fe` (`id`, `address`, `municipality`, `shipping_amount`, `created_at`) VALUES
(0, 'Malbago', 'Santa Fe', 53.00, '2024-10-15 11:48:11');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(30) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(200) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `cover_img` text NOT NULL,
  `about_content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `name`, `email`, `contact`, `cover_img`, `about_content`) VALUES
(1, 'M&M Cake Ordering System', 'erica204chavez@gmail.com', '+639158259643', '', '&lt;h1 style=&quot;text-align: center; background: transparent; position: relative;&quot;&gt;&lt;span style=&quot;color:rgb(68,68,68);text-align: center; background: transparent; position: relative;&quot;&gt;&lt;h1&gt;&lt;span style=&quot;text-align: center; background: transparent; position: relative; color: rgb(68, 68, 68);&quot;&gt;&lt;sup style=&quot;text-align: center; background: transparent; position: relative; color: rgb(68, 68, 68);&quot;&gt;&lt;b style=&quot;text-align: center; background: transparent; position: relative; color: rgb(68, 68, 68);&quot;&gt;ABOUT US&lt;/b&gt;&lt;/sup&gt;&lt;/span&gt;&lt;/h1&gt;&lt;h1&gt;&lt;span style=&quot;text-align: center; background: transparent; position: relative; color: rgb(68, 68, 68);&quot;&gt;&lt;sup style=&quot;text-align: center; background: transparent; position: relative; color: rgb(68, 68, 68);&quot;&gt;&amp;nbsp;&lt;/sup&gt;&lt;/span&gt;&lt;/h1&gt;&lt;/span&gt;&lt;span style=&quot;font-size:20px;text-align: center; background: transparent; position: relative; color: rgb(68, 68, 68);&quot;&gt;&lt;span style=&quot;color: rgb(68, 68, 68); text-align: center; background: transparent; position: relative; font-size: 20px;&quot;&gt;&lt;h1 style=&quot;font-size: 20px;&quot;&gt;&lt;span style=&quot;text-align: center; background: transparent; position: relative; color: rgb(68, 68, 68); font-size: 20px;&quot;&gt;&lt;sup style=&quot;text-align: center; background: transparent; position: relative; color: rgb(68, 68, 68); font-size: 20px;&quot;&gt;&lt;/sup&gt;&lt;/span&gt;&lt;/h1&gt;&lt;/span&gt;&lt;span style=&quot;font-size: 24px; text-align: center; background: transparent; position: relative; color: rgb(68, 68, 68);&quot;&gt;&lt;span style=&quot;color: rgb(68, 68, 68); text-align: center; background: transparent; position: relative; font-size: 24px;&quot;&gt;&lt;h1 style=&quot;font-size: 24px;&quot;&gt;&lt;span style=&quot;text-align: center; background: transparent; position: relative; color: rgb(68, 68, 68); font-size: 24px;&quot;&gt;&lt;sup style=&quot;text-align: center; background: transparent; position: relative; color: rgb(68, 68, 68); font-size: 24px;&quot;&gt;&lt;b style=&quot;text-align: center; background: transparent; position: relative; color: rgb(68, 68, 68); font-size: 24px;&quot;&gt;Welcome to the M&amp;amp;M Cake Ordering System, home of beautifully tasty cakes, an unforgettable cake for every one! We at M&amp;amp;M believe that every occasion is of the highest importance: Celebrate with a cake as exceptional and unique as you. Our selection of beautifully crafted cakes is perfect for your special occasions, whether it&rsquo;s celebrating a birthday, wedding, anniversary - you name it.&lt;/b&gt;&lt;/sup&gt;&lt;/span&gt;&lt;/h1&gt;&lt;h3 style=&quot;font-size: 24px;&quot;&gt;&lt;b style=&quot;font-size: 24px;&quot;&gt;&lt;sup style=&quot;color: rgb(68, 68, 68); font-size: 24px;&quot;&gt;&amp;nbsp; &amp;nbsp;&amp;nbsp;&lt;br style=&quot;font-size: 24px;&quot;&gt;&lt;/sup&gt;&lt;sup style=&quot;color: rgb(68, 68, 68); font-size: 24px;&quot;&gt;&amp;nbsp; &amp;nbsp;&lt;sup style=&quot;color: rgb(68, 68, 68); font-size: 24px;&quot;&gt;&lt;/sup&gt;&lt;/sup&gt;&lt;/b&gt;&lt;/h3&gt;&lt;/span&gt;&lt;span style=&quot;color: rgb(68, 68, 68); font-size: 24px;&quot;&gt;&lt;span style=&quot;color: rgb(68, 68, 68); font-size: 24px;&quot;&gt;&lt;span style=&quot;font-size: 24px; color: rgb(68, 68, 68);&quot;&gt;&lt;span style=&quot;color: rgb(68, 68, 68); text-align: center; background: transparent; position: relative; font-size: 24px;&quot;&gt;&lt;h3 style=&quot;font-size: 24px;&quot;&gt;&lt;b style=&quot;font-size: 24px;&quot;&gt;&lt;sup style=&quot;color: rgb(68, 68, 68); font-size: 24px;&quot;&gt;&lt;sup style=&quot;color: rgb(68, 68, 68); font-size: 24px;&quot;&gt; The story of M&amp;amp;M started in the 1980s with a love of baking and a dedication to perfection. The name &quot;M&amp;amp;M&quot; stands for &quot;Money and Millions,&quot; symbolizing our commitment to delivering value and abundance in every creation we make.&lt;br style=&quot;color: rgb(68, 68, 68); font-size: 24px;&quot;&gt;&lt;/sup&gt;&lt;/sup&gt;&lt;sup style=&quot;font-size: 24px;&quot;&gt;&lt;span style=&quot;color: rgb(68, 68, 68); font-size: 24px;&quot;&gt;&amp;nbsp; &amp;nbsp;&amp;nbsp;&lt;br style=&quot;font-size: 24px;&quot;&gt;&lt;/span&gt;&lt;span style=&quot;color: rgb(68, 68, 68); font-size: 24px;&quot;&gt;&amp;nbsp; &amp;nbsp; We pride ourselves on selecting only the best ingredients, meaning that every cake we make not only looks amazing but tastes delicious too. Our talented bakers and decorators bring some of your favorite classic flavors to new heights, as well as one-of-a-kind creations inspired by your sweetest visions.&lt;/span&gt;&lt;/sup&gt;&lt;/b&gt;&lt;/h3&gt;&lt;/span&gt;&lt;p style=&quot;text-align: center; font-size: 24px;&quot;&gt;&lt;/p&gt;&lt;/span&gt;&lt;p style=&quot;text-align: center; font-size: 24px;&quot;&gt;&lt;/p&gt;&lt;/span&gt;&lt;span style=&quot;color: rgb(68, 68, 68); font-size: 24px;&quot;&gt;&lt;p style=&quot;text-align: center; font-size: 24px;&quot;&gt;&lt;/p&gt;&lt;/span&gt;&lt;span style=&quot;color: rgb(68, 68, 68); font-size: 16px;&quot;&gt;&lt;p style=&quot;text-align: center;&quot;&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;/p&gt;&lt;/span&gt;&lt;/h1&gt;');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(30) NOT NULL,
  `name` varchar(200) NOT NULL,
  `username` text NOT NULL,
  `password` varchar(200) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 2 COMMENT '1=admin , 2 = staff',
  `profile_picture` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `type`, `profile_picture`, `email`, `reset_token`) VALUES
(1, 'Erica Adlit', 'erica204chavez@gmail.com', '$2y$10$If7IBsUX7uApBJV4Pe7m9OnIio4Ajfhczq5xGZTYiw3FmQlZnXNae', 1, NULL, NULL, '99d0aa709c3ca060898710246a416c1ddaa61c94da5eda959b9f786adff095388b70442dcc2ce411437f3ee7d6d5ad52774d');

-- --------------------------------------------------------

--
-- Table structure for table `user_info`
--

CREATE TABLE `user_info` (
  `user_id` int(10) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(300) NOT NULL,
  `password` varchar(300) NOT NULL,
  `mobile` varchar(10) NOT NULL,
  `address` varchar(300) NOT NULL,
  `municipality` varchar(100) NOT NULL,
  `active` tinyint(1) DEFAULT 0,
  `otp` int(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_info`
--

INSERT INTO `user_info` (`user_id`, `first_name`, `last_name`, `email`, `password`, `mobile`, `address`, `municipality`, `active`, `otp`) VALUES
(30, 'erica', 'adlit', 'erica204chavez@gmail.com', '$2y$10$0Czy6LzrjXbtOeolOzvWweD6wwRFCNVxfm24lf0c0wu47yODwXEha', '0915829634', 'wd', '', 0, NULL),
(31, 'mani', 'demsa', 'manilyndemesa87@gmail.com', '$2y$10$BoGT8kLbpEQhjN4LhgQYOeIPycDM75PiQyoZwvGGriFa8I8jp3W0a', '0915829634', 'malbago', '', 0, NULL),
(32, 'ken', 'duc', 'kenethducay12@gmail.com', '$2y$10$dUkYq0H.jj/m0Bbndv1AhuvnvWcFQuinEp2JKkEHATuiE2TfwVj1O', '0915829634', 'atop', '', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_messages`
--

CREATE TABLE `user_messages` (
  `message_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `message_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_reply` text DEFAULT NULL,
  `reply_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_otp`
--

CREATE TABLE `user_otp` (
  `id` int(11) NOT NULL,
  `otp` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category_list`
--
ALTER TABLE `category_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_list`
--
ALTER TABLE `order_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_list`
--
ALTER TABLE `product_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_ratings`
--
ALTER TABLE `product_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `shipping_info`
--
ALTER TABLE `shipping_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `address` (`address`);

--
-- Indexes for table `shipping_info_bantayan`
--
ALTER TABLE `shipping_info_bantayan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipping_info_madridejos`
--
ALTER TABLE `shipping_info_madridejos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipping_info_santa_fe`
--
ALTER TABLE `shipping_info_santa_fe`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_info`
--
ALTER TABLE `user_info`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_messages`
--
ALTER TABLE `user_messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `user_otp`
--
ALTER TABLE `user_otp`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `category_list`
--
ALTER TABLE `category_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `order_list`
--
ALTER TABLE `order_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `product_list`
--
ALTER TABLE `product_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `product_ratings`
--
ALTER TABLE `product_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_info`
--
ALTER TABLE `shipping_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_info`
--
ALTER TABLE `user_info`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `user_messages`
--
ALTER TABLE `user_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_otp`
--
ALTER TABLE `user_otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product_ratings`
--
ALTER TABLE `product_ratings`
  ADD CONSTRAINT `product_ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_info` (`user_id`),
  ADD CONSTRAINT `product_ratings_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product_list` (`id`);

--
-- Constraints for table `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
