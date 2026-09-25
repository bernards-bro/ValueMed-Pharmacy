-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2026 at 04:42 PM
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
-- Database: `valuemed_pharmacy`
--

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `cashier_id` int(11) NOT NULL,
  `sale_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_type` varchar(30) DEFAULT NULL,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_id_number` varchar(50) DEFAULT NULL,
  `payment_method` varchar(30) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `cashier_id`, `sale_date`, `total_amount`, `discount_type`, `discount_percentage`, `discount_amount`, `customer_name`, `customer_id_number`, `payment_method`, `amount_paid`, `change_amount`) VALUES
(1, 2, '2026-09-11 09:15:00', 120.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 200.00, 80.00),
(2, 3, '2026-09-11 10:30:00', 95.00, NULL, 0.00, 0.00, NULL, NULL, 'GCash', 95.00, 0.00),
(3, 2, '2026-09-11 11:45:00', 250.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 300.00, 50.00),
(4, 3, '2026-09-11 13:20:00', 180.00, NULL, 0.00, 0.00, NULL, NULL, 'GCash', 180.00, 0.00),
(5, 2, '2026-09-11 14:10:00', 140.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 200.00, 60.00),
(6, 3, '2026-09-10 09:30:00', 210.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 250.00, 40.00),
(7, 2, '2026-09-10 11:15:00', 165.00, NULL, 0.00, 0.00, NULL, NULL, 'GCash', 165.00, 0.00),
(8, 3, '2026-09-10 15:40:00', 320.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 500.00, 180.00),
(9, 2, '2026-09-09 10:00:00', 275.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 300.00, 25.00),
(10, 3, '2026-09-09 16:20:00', 450.00, NULL, 0.00, 0.00, NULL, NULL, 'GCash', 450.00, 0.00),
(11, 2, '2026-09-08 09:45:00', 190.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 200.00, 10.00),
(12, 15, '2026-09-22 20:31:58', 176.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 200.00, 24.00),
(13, 15, '2026-09-23 00:22:14', 47.50, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 100.00, 52.50),
(14, 15, '2026-09-23 00:23:08', 8.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 8.00, 0.00),
(15, 15, '2026-09-23 03:14:54', 40.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 50.00, 10.00),
(16, 15, '2026-09-23 20:22:31', 120.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 200.00, 80.00),
(17, 15, '2026-09-24 01:06:40', 54.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 100.00, 46.00),
(18, 15, '2026-09-24 01:48:39', 60.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 70.00, 10.00),
(19, 15, '2026-09-24 01:51:22', 34.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 40.00, 6.00),
(20, 15, '2026-09-24 01:52:18', 54.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 60.00, 6.00),
(21, 15, '2026-09-24 02:22:19', 16.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 16.00, 0.00),
(22, 15, '2026-09-24 02:28:54', 12.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 12.00, 0.00),
(23, 15, '2026-09-24 02:30:16', 48.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 50.00, 2.00),
(24, 15, '2026-09-24 02:31:58', 12.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 12.00, 0.00),
(25, 15, '2026-09-24 02:33:07', 12.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 12.00, 0.00),
(26, 15, '2026-09-24 02:51:15', 24.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 30.00, 6.00),
(27, 15, '2026-09-24 03:14:07', 50.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 50.00, 0.00),
(28, 15, '2026-09-24 13:11:17', 57.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 60.00, 3.00),
(29, 15, '2026-09-24 13:14:47', 50.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 50.00, 0.00),
(30, 15, '2026-09-24 13:31:59', 50.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 60.00, 10.00),
(31, 15, '2026-09-24 13:44:15', 32.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 40.00, 8.00),
(32, 15, '2026-09-24 13:49:21', 36.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 40.00, 4.00),
(33, 15, '2026-09-24 13:52:29', 13.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 20.00, 7.00),
(34, 15, '2026-09-24 13:56:51', 36.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 40.00, 4.00),
(35, 15, '2026-09-24 13:58:40', 40.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 41.00, 1.00),
(36, 15, '2026-09-24 14:04:11', 22.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 25.00, 3.00),
(37, 15, '2026-09-24 14:07:18', 20.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 20.00, 0.00),
(38, 15, '2026-09-24 14:10:12', 31.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 40.00, 9.00),
(39, 15, '2026-09-24 14:12:16', 26.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 30.00, 4.00),
(40, 15, '2026-09-24 14:15:51', 76.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 80.00, 4.00),
(41, 15, '2026-09-24 14:16:51', 36.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 40.00, 4.00),
(42, 15, '2026-09-24 14:28:10', 76.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 80.00, 4.00),
(43, 15, '2026-09-24 18:56:05', 76.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 80.00, 4.00),
(44, 15, '2026-09-24 19:09:38', 76.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 80.00, 4.00),
(45, 15, '2026-09-24 19:45:51', 116.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 200.00, 84.00),
(46, 15, '2026-09-24 19:56:35', 134.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 150.00, 16.00),
(47, 15, '2026-09-24 19:57:25', 195.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 200.00, 5.00),
(48, 15, '2026-09-24 20:15:42', 114.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 120.00, 6.00),
(49, 15, '2026-09-24 20:51:27', 152.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 155.00, 3.00),
(50, 15, '2026-09-24 20:55:29', 190.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 200.00, 10.00),
(51, 15, '2026-09-24 20:56:41', 8.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 10.00, 2.00),
(52, 15, '2026-09-24 21:04:59', 8.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 10.00, 2.00),
(53, 15, '2026-09-24 21:09:50', 8.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 10.00, 2.00),
(54, 15, '2026-09-25 10:57:20', 41.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 50.00, 9.00),
(55, 15, '2026-09-25 16:00:57', 36.00, 'None', 0.00, 0.00, '', '', 'Cash', 40.00, 4.00),
(56, 15, '2026-09-25 16:03:07', 36.80, 'Senior Citizen', 20.00, 9.20, 'gavin', '123123123', 'Cash', 40.00, 3.20);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `fk_sales_cashier` (`cashier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sales_cashier` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
