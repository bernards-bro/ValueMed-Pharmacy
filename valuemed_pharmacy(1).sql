-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2026 at 07:35 AM
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
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `medicine_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`medicine_id`, `name`, `description`, `category`, `price`, `cost_price`, `stock`, `expiry_date`) VALUES
(1, 'Paracetamol', 'Used to relieve pain and reduce fever', 'Tablet', 5.00, 2.50, 142, '2028-06-30'),
(2, 'Biogesic', 'Paracetamol-based medicine for fever and pain', 'Tablet', 8.00, 4.00, 25, '2027-12-31'),
(3, 'Amoxicillin', 'Antibiotic used to treat bacterial infections', 'Capsule', 12.00, 7.00, 4, '2027-08-31'),
(4, 'Cetirizine', 'Antihistamine for allergy symptoms', 'Tablet', 10.00, 5.00, 26, '2028-02-28'),
(5, 'Ibuprofen', 'Used to relieve pain and inflammation', 'Tablet', 7.50, 3.50, 13, '2027-11-30'),
(6, 'Loperamide', 'Used to treat diarrhea', 'Capsule', 6.00, 2.75, 8, '2027-05-31'),
(7, 'Omeprazole', 'Used to reduce stomach acid', 'Capsule', 15.00, 8.00, 47, '2028-01-31'),
(8, 'Vitamin C', 'Vitamin supplement for immune support', 'Tablet', 10.00, 5.50, 84, '2028-09-30'),
(9, 'Mefenamic Acid', 'Used for pain relief', 'Capsule', 9.00, 4.50, 5, '2027-03-31'),
(10, 'Cough Syrup', 'Syrup used to relieve cough symptoms', 'Syrup', 85.00, 50.00, 0, '2027-10-31'),
(11, 'test drug 1', 'yayayayayaya', 'Capsul', 40.00, 32.00, 2, '2026-09-21'),
(12, 'lol', 'jgmhjjhykuhhj', 'yu', 30.00, 20.00, 22, '2027-11-19');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `date_created` date NOT NULL,
  `status` varchar(20) DEFAULT 'Unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `medicine_id`, `message`, `date_created`, `status`) VALUES
(1, 1, 3, 'Amoxicillin is running low. Current stock: 12 units.', '2026-09-11', 'Unread'),
(2, 1, 5, 'Ibuprofen is low on stock. Current stock: 20 units.', '2026-09-11', 'Unread'),
(3, 1, 6, 'Loperamide is running low. Current stock: 8 units.', '2026-09-11', 'Unread'),
(4, 1, 9, 'Mefenamic Acid is critically low. Current stock: 5 units.', '2026-09-11', 'Unread'),
(5, 1, 10, 'Cough Syrup is out of stock.', '2026-09-11', 'Unread'),
(6, 2, 3, 'Amoxicillin stock is low.', '2026-09-11', 'Read');

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
(54, 15, '2026-09-25 10:57:20', 41.00, NULL, 0.00, 0.00, NULL, NULL, 'Cash', 50.00, 9.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales_report`
--

CREATE TABLE `sales_report` (
  `report_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `total_sales` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_items` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_report`
--

INSERT INTO `sales_report` (`report_id`, `sale_id`, `report_date`, `total_sales`, `total_items`) VALUES
(1, 1, '2026-09-11', 120.00, 18),
(2, 2, '2026-09-11', 95.00, 12),
(3, 3, '2026-09-11', 250.00, 20),
(4, 4, '2026-09-11', 180.00, 18),
(5, 5, '2026-09-11', 140.00, 16),
(6, 6, '2026-09-10', 210.00, 30),
(7, 7, '2026-09-10', 165.00, 19),
(8, 8, '2026-09-10', 320.00, 27),
(9, 9, '2026-09-09', 275.00, 25),
(10, 10, '2026-09-09', 450.00, 35),
(11, 11, '2026-09-08', 190.00, 33),
(12, 12, '2026-09-22', 176.00, 6),
(13, 13, '2026-09-22', 47.50, 2),
(14, 14, '2026-09-22', 8.00, 1),
(15, 15, '2026-09-22', 40.00, 1),
(16, 16, '2026-09-23', 120.00, 3),
(17, 17, '2026-09-23', 54.00, 6),
(18, 18, '2026-09-23', 60.00, 6),
(19, 19, '2026-09-23', 34.00, 3),
(20, 20, '2026-09-23', 54.00, 6),
(21, 21, '2026-09-23', 16.00, 2),
(22, 22, '2026-09-23', 12.00, 1),
(23, 23, '2026-09-23', 48.00, 2),
(24, 24, '2026-09-23', 12.00, 1),
(25, 25, '2026-09-23', 12.00, 1),
(26, 26, '2026-09-23', 24.00, 2),
(27, 27, '2026-09-23', 50.00, 4),
(28, 28, '2026-09-24', 57.00, 6),
(29, 29, '2026-09-24', 50.00, 4),
(30, 30, '2026-09-24', 50.00, 3),
(31, 31, '2026-09-24', 32.00, 4),
(32, 32, '2026-09-24', 36.00, 4),
(33, 33, '2026-09-24', 13.00, 2),
(34, 34, '2026-09-24', 36.00, 4),
(35, 35, '2026-09-24', 40.00, 4),
(36, 36, '2026-09-24', 22.00, 2),
(37, 37, '2026-09-24', 20.00, 2),
(38, 38, '2026-09-24', 31.00, 4),
(39, 39, '2026-09-24', 26.00, 3),
(40, 40, '2026-09-24', 76.00, 4),
(41, 41, '2026-09-24', 36.00, 4),
(42, 42, '2026-09-24', 76.00, 4),
(43, 43, '2026-09-24', 76.00, 4),
(44, 44, '2026-09-24', 76.00, 4),
(45, 45, '2026-09-24', 116.00, 10),
(46, 46, '2026-09-24', 134.00, 8),
(47, 47, '2026-09-24', 195.00, 10),
(48, 48, '2026-09-24', 114.00, 6),
(49, 49, '2026-09-24', 152.00, 8),
(50, 50, '2026-09-24', 190.00, 13),
(51, 51, '2026-09-24', 8.00, 1),
(52, 52, '2026-09-24', 8.00, 1),
(53, 53, '2026-09-24', 8.00, 1),
(54, 54, '2026-09-25', 41.00, 6);

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `sale_item_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`sale_item_id`, `sale_id`, `medicine_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 10, 5.00, 50.00),
(2, 1, 2, 5, 8.00, 40.00),
(3, 1, 4, 3, 10.00, 30.00),
(4, 2, 1, 5, 5.00, 25.00),
(5, 2, 3, 5, 12.00, 60.00),
(6, 2, 6, 1, 6.00, 6.00),
(7, 2, 9, 1, 9.00, 9.00),
(8, 3, 7, 10, 15.00, 150.00),
(9, 3, 8, 10, 10.00, 100.00),
(10, 4, 3, 10, 12.00, 120.00),
(11, 4, 5, 8, 7.50, 60.00),
(12, 5, 2, 10, 8.00, 80.00),
(13, 5, 4, 6, 10.00, 60.00),
(14, 6, 1, 20, 5.00, 100.00),
(15, 6, 7, 5, 15.00, 75.00),
(16, 6, 6, 5, 6.00, 30.00),
(17, 7, 8, 10, 10.00, 100.00),
(18, 7, 9, 5, 9.00, 45.00),
(19, 7, 1, 4, 5.00, 20.00),
(20, 8, 7, 10, 15.00, 150.00),
(21, 8, 3, 10, 12.00, 120.00),
(22, 8, 2, 5, 8.00, 40.00),
(23, 8, 6, 1, 6.00, 6.00),
(24, 8, 9, 1, 9.00, 9.00),
(25, 9, 4, 10, 10.00, 100.00),
(26, 9, 8, 10, 10.00, 100.00),
(27, 9, 7, 5, 15.00, 75.00),
(28, 10, 7, 20, 15.00, 300.00),
(29, 10, 3, 10, 12.00, 120.00),
(30, 10, 6, 5, 6.00, 30.00),
(31, 11, 1, 20, 5.00, 100.00),
(32, 11, 5, 8, 7.50, 60.00),
(33, 11, 6, 5, 6.00, 30.00),
(34, 12, 11, 4, 40.00, 160.00),
(35, 12, 2, 2, 8.00, 16.00),
(36, 13, 11, 1, 40.00, 40.00),
(37, 13, 5, 1, 7.50, 7.50),
(38, 14, 2, 1, 8.00, 8.00),
(39, 15, 11, 1, 40.00, 40.00),
(40, 16, 11, 3, 40.00, 120.00),
(41, 17, 2, 3, 8.00, 24.00),
(42, 17, 4, 3, 10.00, 30.00),
(43, 18, 2, 3, 8.00, 24.00),
(44, 18, 3, 3, 12.00, 36.00),
(45, 19, 3, 2, 12.00, 24.00),
(46, 19, 4, 1, 10.00, 10.00),
(47, 20, 2, 3, 8.00, 24.00),
(48, 20, 4, 3, 10.00, 30.00),
(49, 21, 2, 2, 8.00, 16.00),
(50, 22, 3, 1, 12.00, 12.00),
(51, 23, 2, 1, 8.00, 8.00),
(52, 23, 11, 1, 40.00, 40.00),
(53, 24, 3, 1, 12.00, 12.00),
(54, 25, 3, 1, 12.00, 12.00),
(55, 26, 3, 2, 12.00, 24.00),
(56, 27, 7, 2, 15.00, 30.00),
(57, 27, 4, 2, 10.00, 20.00),
(58, 28, 3, 3, 12.00, 36.00),
(59, 28, 1, 1, 5.00, 5.00),
(60, 28, 2, 2, 8.00, 16.00),
(61, 29, 5, 2, 7.50, 15.00),
(62, 29, 12, 1, 30.00, 30.00),
(63, 29, 1, 1, 5.00, 5.00),
(64, 30, 12, 1, 30.00, 30.00),
(65, 30, 4, 2, 10.00, 20.00),
(66, 31, 2, 4, 8.00, 32.00),
(67, 32, 2, 2, 8.00, 16.00),
(68, 32, 4, 2, 10.00, 20.00),
(69, 33, 2, 1, 8.00, 8.00),
(70, 33, 1, 1, 5.00, 5.00),
(71, 34, 2, 2, 8.00, 16.00),
(72, 34, 8, 2, 10.00, 20.00),
(73, 35, 3, 2, 12.00, 24.00),
(74, 35, 2, 2, 8.00, 16.00),
(75, 36, 3, 1, 12.00, 12.00),
(76, 36, 4, 1, 10.00, 10.00),
(77, 37, 3, 1, 12.00, 12.00),
(78, 37, 2, 1, 8.00, 8.00),
(79, 38, 2, 2, 8.00, 16.00),
(80, 38, 5, 2, 7.50, 15.00),
(81, 39, 2, 2, 8.00, 16.00),
(82, 39, 4, 1, 10.00, 10.00),
(83, 40, 2, 2, 8.00, 16.00),
(84, 40, 12, 2, 30.00, 60.00),
(85, 41, 2, 2, 8.00, 16.00),
(86, 41, 4, 2, 10.00, 20.00),
(87, 42, 2, 2, 8.00, 16.00),
(88, 42, 12, 2, 30.00, 60.00),
(89, 43, 2, 2, 8.00, 16.00),
(90, 43, 12, 2, 30.00, 60.00),
(91, 44, 2, 2, 8.00, 16.00),
(92, 44, 12, 2, 30.00, 60.00),
(93, 45, 2, 2, 8.00, 16.00),
(94, 45, 7, 4, 15.00, 60.00),
(95, 45, 8, 4, 10.00, 40.00),
(96, 46, 12, 3, 30.00, 90.00),
(97, 46, 2, 3, 8.00, 24.00),
(98, 46, 4, 2, 10.00, 20.00),
(99, 47, 7, 7, 15.00, 105.00),
(100, 47, 12, 3, 30.00, 90.00),
(101, 48, 12, 3, 30.00, 90.00),
(102, 48, 2, 3, 8.00, 24.00),
(103, 49, 12, 4, 30.00, 120.00),
(104, 49, 2, 4, 8.00, 32.00),
(105, 50, 8, 10, 10.00, 100.00),
(106, 50, 12, 3, 30.00, 90.00),
(107, 51, 2, 1, 8.00, 8.00),
(108, 52, 2, 1, 8.00, 8.00),
(109, 53, 2, 1, 8.00, 8.00),
(110, 54, 2, 2, 8.00, 16.00),
(111, 54, 5, 2, 7.50, 15.00),
(112, 54, 1, 2, 5.00, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `stock_in`
--

CREATE TABLE `stock_in` (
  `stockin_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity_added` int(11) NOT NULL,
  `stockin_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_in`
--

INSERT INTO `stock_in` (`stockin_id`, `medicine_id`, `user_id`, `quantity_added`, `stockin_date`) VALUES
(1, 1, 1, 100, '2026-09-01'),
(2, 2, 2, 50, '2026-09-02'),
(3, 3, 1, 30, '2026-09-03'),
(4, 4, 2, 40, '2026-09-03'),
(5, 5, 3, 25, '2026-09-04'),
(6, 6, 2, 20, '2026-09-05'),
(7, 7, 1, 50, '2026-09-05'),
(8, 8, 3, 100, '2026-09-06'),
(9, 9, 2, 15, '2026-09-07'),
(10, 10, 1, 30, '2026-09-08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_no` varchar(30) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `username`, `password`, `role`, `email`, `contact_no`, `status`) VALUES
(1, 'Juan Dela Cruz', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCw5q7kY1YwW3mZ8J6a', 'Admin', 'admin@valuemeds.com', '09171234567', 'Active'),
(2, 'Maria Santos', 'maria', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCw5q7kY1YwW3mZ8J6a', 'Cashier', 'maria@valuemeds.com', '09181234567', 'Active'),
(3, 'Pedro Reyes', 'pedro', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCw5q7kY1YwW3mZ8J6a', 'Cashier', 'pedro@valuemeds.com', '09191234567', 'Active'),
(4, 'Ana Garcia', 'ana', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCw5q7kY1YwW3mZ8J6a', 'Cashier', 'ana@valuemeds.com', '09201234567', 'Inactive'),
(8, 'Juan Dela Cruz', 'admin2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCw5q7kY1Y', 'Admin', 'admin@valuemeds.com', '09171234567', 'Active'),
(9, 'Maria Santos', 'maria2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCw5q7kY1Y', 'Cashier', 'maria@valuemeds.com', '09181234567', 'Active'),
(10, 'Pedro Reyes', 'pedro2', '$2y$10$92IXUNpkO0rOQ5byMi.Ye4oKoEa3Ro9llCw5q7kY1Y', 'Cashier', 'pedro@valuemeds.com', '09191234567', 'Active'),
(11, 'Test Admin', 'testadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCw5q7kY1Y', 'Admin', 'testadmin@valuemeds.com', '09170000001', 'Active'),
(12, 'Test Cashier', 'testcashier', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCw5q7kY1Y', 'Cashier', 'testcashier@valuemeds.com', '09170000002', 'Active'),
(15, 'Juan Dela Cruz', 'testadmins', '$2y$12$KK0l6VBSqwDozUj4zZ6Jl.BZKx22OHfoNtC9sSB27eRhULMH8pdEa', 'Admin', 'admin@valuemeds.com', '09171234567', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`medicine_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notifications_user` (`user_id`),
  ADD KEY `fk_notifications_medicine` (`medicine_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `fk_sales_cashier` (`cashier_id`);

--
-- Indexes for table `sales_report`
--
ALTER TABLE `sales_report`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `fk_sales_report_sale` (`sale_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`sale_item_id`),
  ADD KEY `fk_sale_items_sale` (`sale_id`),
  ADD KEY `fk_sale_items_medicine` (`medicine_id`);

--
-- Indexes for table `stock_in`
--
ALTER TABLE `stock_in`
  ADD PRIMARY KEY (`stockin_id`),
  ADD KEY `fk_stockin_medicine` (`medicine_id`),
  ADD KEY `fk_stockin_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `unique_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `medicine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `sales_report`
--
ALTER TABLE `sales_report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `stock_in`
--
ALTER TABLE `stock_in`
  MODIFY `stockin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_medicine` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`medicine_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sales_cashier` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `sales_report`
--
ALTER TABLE `sales_report`
  ADD CONSTRAINT `fk_sales_report_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `fk_sale_items_medicine` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`medicine_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sale_items_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock_in`
--
ALTER TABLE `stock_in`
  ADD CONSTRAINT `fk_stockin_medicine` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`medicine_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stockin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
