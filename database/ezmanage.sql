-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 23, 2025 at 12:58 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ezmanage`
--

-- --------------------------------------------------------

--
-- Table structure for table `calculations`
--

CREATE TABLE `calculations` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `calculation` text NOT NULL,
  `result` decimal(50,4) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `calculations`
--

INSERT INTO `calculations` (`id`, `user_id`, `calculation`, `result`, `created_at`) VALUES
(258, 2, '100+100', '200.0000', '2025-06-23 00:02:16'),
(259, 2, '10%', '0.1000', '2025-06-23 16:58:53'),
(260, 2, '100%', '1.0000', '2025-06-23 16:59:04'),
(261, 2, '100000*20%', '20000.0000', '2025-06-23 16:59:39');

-- --------------------------------------------------------

--
-- Table structure for table `categories_expenses`
--

CREATE TABLE `categories_expenses` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories_expenses`
--

INSERT INTO `categories_expenses` (`id`, `user_id`, `name`, `created_at`) VALUES
(15, 2, 'Makanan', '2025-06-10 01:33:13'),
(24, 2, 'Internet', '2025-06-20 15:39:11'),
(25, 2, 'Top Up', '2025-06-20 16:10:53'),
(26, 2, 'Pakaian', '2025-06-23 00:01:34');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(5) NOT NULL,
  `date` date NOT NULL,
  `category_id` int DEFAULT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `user_id`, `amount`, `currency`, `date`, `category_id`, `description`, `created_at`) VALUES
(51, 2, '10000.00', 'IDR', '2025-06-23', 15, 'beli nasi goreng', '2025-06-23 00:00:58');

-- --------------------------------------------------------

--
-- Table structure for table `todos`
--

CREATE TABLE `todos` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `priority` enum('Tinggi','Sedang','Rendah') NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('Belum','Proses','Selesai') DEFAULT 'Belum',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `todos`
--

INSERT INTO `todos` (`id`, `user_id`, `title`, `description`, `priority`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(22, 2, 'Belajar OOP', 'belajar oop lanjutan belajar php', 'Tinggi', '2025-06-10', 'Selesai', '2025-06-10 16:41:55', '2025-06-12 21:54:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `city` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_pic` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `fullname`, `email`, `city`, `password_hash`, `profile_pic`, `created_at`) VALUES
(2, 'anggara', 'Anggara Putra', 'kadekanggaraptra@gmail.com', 'Denpasar', '$2y$10$tQ6P7fkTBcSv1QzLO6Z3beb6kGiQA6EAI2ALzCk1Rj05itqYOm5zi', '', '2025-05-31 00:10:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calculations`
--
ALTER TABLE `calculations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories_expenses`
--
ALTER TABLE `categories_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `todos`
--
ALTER TABLE `todos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `calculations`
--
ALTER TABLE `calculations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- AUTO_INCREMENT for table `categories_expenses`
--
ALTER TABLE `categories_expenses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `todos`
--
ALTER TABLE `todos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `calculations`
--
ALTER TABLE `calculations`
  ADD CONSTRAINT `calculations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories_expenses`
--
ALTER TABLE `categories_expenses`
  ADD CONSTRAINT `categories_expenses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories_expenses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `todos`
--
ALTER TABLE `todos`
  ADD CONSTRAINT `todos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
