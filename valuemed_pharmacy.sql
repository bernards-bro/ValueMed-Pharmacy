-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2026 at 06:31 AM
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
-- Database: `valuemed_pharmacy`
--

-- --------------------------------------------------------

--
-- Table structure for table `exchanges`
--

CREATE TABLE `exchanges` (
  `exchange_id` int(11) NOT NULL,
  `original_sale_id` int(11) NOT NULL,
  `cashier_id` int(11) NOT NULL,
  `exchange_date` datetime DEFAULT current_timestamp(),
  `returned_amount` decimal(10,2) NOT NULL,
  `replacement_amount` decimal(10,2) NOT NULL,
  `additional_payment` decimal(10,2) DEFAULT 0.00,
  `reason` varchar(255) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Completed',
  `cancelled_by` int(11) DEFAULT NULL,
  `cancelled_date` datetime DEFAULT NULL,
  `cancellation_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exchanges`
--

INSERT INTO `exchanges` (`exchange_id`, `original_sale_id`, `cashier_id`, `exchange_date`, `returned_amount`, `replacement_amount`, `additional_payment`, `reason`, `status`, `cancelled_by`, `cancelled_date`, `cancellation_reason`) VALUES
(1, 58, 15, '2026-09-26 07:37:31', 90.00, 50.00, 0.00, 'MALI', 'Cancelled', 15, '2026-09-26 07:50:05', 'Exchange cancelled'),
(2, 58, 15, '2026-09-26 07:43:40', 50.00, 90.00, 40.00, 'MALI', 'Cancelled', 15, '2026-09-26 07:50:02', 'Exchange cancelled'),
(3, 58, 15, '2026-09-26 07:45:55', 90.00, 50.00, 0.00, 'MALI', 'Cancelled', 15, '2026-09-26 07:49:58', 'Exchange cancelled'),
(4, 59, 15, '2026-09-26 07:52:57', 50.00, 90.00, 40.00, 'mali', 'Cancelled', 15, '2026-09-26 08:27:49', 'Exchange cancelled'),
(5, 59, 15, '2026-09-26 07:55:39', 50.00, 50.00, 0.00, '', 'Cancelled', 15, '2026-09-26 08:27:45', 'Exchange cancelled'),
(6, 60, 15, '2026-09-26 08:23:59', 90.00, 50.00, 0.00, '', 'Cancelled', 15, '2026-09-26 08:24:32', 'Exchange cancelled'),
(7, 60, 15, '2026-09-26 08:24:46', 90.00, 0.00, 0.00, '', 'Cancelled', 15, '2026-09-26 08:26:11', 'Exchange cancelled'),
(8, 60, 15, '2026-09-26 08:26:41', 90.00, 50.00, 0.00, '', 'Cancelled', 15, '2026-09-26 08:27:41', 'Exchange cancelled'),
(9, 60, 15, '2026-09-26 08:30:57', 90.00, 50.00, 0.00, '', 'Cancelled', 15, '2026-09-26 08:39:44', 'Exchange cancelled'),
(10, 61, 15, '2026-09-26 08:40:24', 50.00, 50.00, 0.00, '', 'Completed', NULL, NULL, NULL),
(11, 61, 15, '2026-09-26 08:44:16', 50.00, 50.00, 0.00, '', 'Completed', NULL, NULL, NULL),
(12, 61, 15, '2026-09-26 08:45:26', 50.00, 0.00, 0.00, '', 'Completed', NULL, NULL, NULL),
(13, 61, 15, '2026-09-26 08:46:59', 50.00, 90.00, 40.00, '', 'Completed', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `exchange_items`
--

CREATE TABLE `exchange_items` (
  `exchange_item_id` int(11) NOT NULL,
  `exchange_id` int(11) DEFAULT NULL,
  `medicine_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `item_type` enum('Returned','Replacement') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exchange_items`
--

INSERT INTO `exchange_items` (`exchange_item_id`, `exchange_id`, `medicine_id`, `quantity`, `item_type`) VALUES
(1, 1, 14, 1, 'Returned'),
(2, 1, 15, 1, 'Replacement'),
(3, 2, 15, 1, 'Returned'),
(4, 2, 14, 1, 'Replacement'),
(5, 3, 14, 1, 'Returned'),
(6, 3, 15, 1, 'Replacement'),
(7, 4, 16, 1, 'Returned'),
(8, 4, 14, 1, 'Replacement'),
(9, 5, 16, 1, 'Returned'),
(10, 5, 15, 1, 'Replacement'),
(11, 6, 14, 1, 'Returned'),
(12, 6, 16, 1, 'Replacement'),
(13, 7, 14, 1, 'Returned'),
(14, 7, 20, 1, 'Replacement'),
(15, 8, 14, 1, 'Returned'),
(16, 8, 15, 1, 'Replacement'),
(17, 9, 14, 1, 'Returned'),
(18, 9, 15, 1, 'Replacement'),
(19, 10, 15, 1, 'Returned'),
(20, 10, 16, 1, 'Replacement'),
(21, 11, 15, 1, 'Returned'),
(22, 11, 16, 1, 'Replacement'),
(23, 12, 15, 1, 'Returned'),
(24, 12, 17, 1, 'Replacement'),
(25, 13, 15, 1, 'Returned'),
(26, 13, 14, 1, 'Replacement');

-- --------------------------------------------------------

--
-- Table structure for table `exchange_logs`
--

CREATE TABLE `exchange_logs` (
  `log_id` int(11) NOT NULL,
  `exchange_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `performed_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exchange_logs`
--

INSERT INTO `exchange_logs` (`log_id`, `exchange_id`, `action`, `performed_by`, `created_at`) VALUES
(1, 1, 'Exchange Completed', 15, '2026-09-26 07:37:31'),
(2, 2, 'Exchange Completed', 15, '2026-09-26 07:43:40'),
(3, 3, 'Exchange Completed', 15, '2026-09-26 07:45:55'),
(4, 3, 'Exchange Cancelled', 15, '2026-09-26 07:49:58'),
(5, 2, 'Exchange Cancelled', 15, '2026-09-26 07:50:02'),
(6, 1, 'Exchange Cancelled', 15, '2026-09-26 07:50:05'),
(7, 4, 'Exchange Completed', 15, '2026-09-26 07:52:57'),
(8, 5, 'Exchange Completed', 15, '2026-09-26 07:55:39'),
(9, 6, 'Exchange Completed', 15, '2026-09-26 08:23:59'),
(10, 6, 'Exchange Cancelled', 15, '2026-09-26 08:24:32'),
(11, 7, 'Exchange Completed', 15, '2026-09-26 08:24:46'),
(12, 7, 'Exchange Cancelled', 15, '2026-09-26 08:26:11'),
(13, 8, 'Exchange Completed', 15, '2026-09-26 08:26:41'),
(14, 8, 'Exchange Cancelled', 15, '2026-09-26 08:27:41'),
(15, 5, 'Exchange Cancelled', 15, '2026-09-26 08:27:45'),
(16, 4, 'Exchange Cancelled', 15, '2026-09-26 08:27:49'),
(17, 9, 'Exchange Completed', 15, '2026-09-26 08:30:57'),
(18, 9, 'Exchange Cancelled', 15, '2026-09-26 08:39:44'),
(19, 10, 'Exchange Completed', 15, '2026-09-26 08:40:24'),
(20, 11, 'Exchange Completed', 15, '2026-09-26 08:44:16'),
(21, 12, 'Exchange Completed', 15, '2026-09-26 08:45:26'),
(22, 13, 'Exchange Completed', 15, '2026-09-26 08:46:59');

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
(14, 'Acetylcysteine', '200mg', 'Tablet', 0.00, 0.00, 10, '2028-10-26'),
(15, 'Acetylcysteine', '600mg', 'Tablet', 50.00, 0.00, 5, '2028-11-26'),
(16, 'Aciclovir', '200mg', 'Tablet', 50.00, 0.00, 169, '2028-10-26'),
(17, 'Aciclovir', '400mg', 'Tablet', 0.00, 0.00, 59, NULL),
(18, 'Allopurinol', '100mg', 'Tablet', 0.00, 0.00, 88, NULL),
(19, 'Allopurinol', '300mg', 'Tablet', 0.00, 0.00, 149, NULL),
(20, 'Al+Mg OH (antacid)', '200/200mg', 'Tablet', 0.00, 0.00, 220, NULL),
(21, 'Ambroxol', '30mg', 'Tablet', 0.00, 0.00, 194, NULL),
(22, 'Amlodipine', '5mg', 'Tablet', 0.00, 0.00, 24, NULL),
(23, 'Amlodipine', '10mg', 'Tablet', 0.00, 0.00, 142, NULL),
(24, 'Amlo+Losartan', '5mg/50mg', 'Tablet', 0.00, 0.00, 4, NULL),
(25, 'Ampalaya', '500', 'Tablet', 0.00, 0.00, 50, NULL),
(26, 'Amoxicillin', '250mg', 'Tablet', 0.00, 0.00, 148, NULL),
(27, 'Amoxicillin', '500mg', 'Tablet', 0.00, 0.00, 411, NULL),
(28, 'Aspirin', '80g', 'Tablet', 0.00, 0.00, 56, NULL),
(29, 'Ascorbic Acid Enocee', '100mg', 'Tablet', 0.00, 0.00, 200, NULL),
(30, 'Ascorbic Acid Enocee', '500mg', 'Tablet', 0.00, 0.00, 374, NULL),
(31, 'Atenolol', '50mg', 'Tablet', 0.00, 0.00, 173, NULL),
(32, 'Atorvastatin', '10mg', 'Tablet', 0.00, 0.00, 0, NULL),
(33, 'Atorvastatin', '20mg', 'Tablet', 0.00, 0.00, 275, NULL),
(34, 'Atorvastatin', '40mg', 'Tablet', 0.00, 0.00, 45, NULL),
(35, 'Atorvastatin', '80mg', 'Tablet', 0.00, 0.00, 206, NULL),
(36, 'Azithromycin', '500mg', 'Tablet', 0.00, 0.00, 0, NULL),
(37, 'Bacillus Clausii', '', 'Tablet', 0.00, 0.00, 40, NULL),
(38, 'Betahistine', '8mg', 'Tablet', 0.00, 0.00, 165, NULL),
(39, 'Betahistine', '16mg', 'Tablet', 0.00, 0.00, 147, NULL),
(40, 'Betahistine', '24mg', 'Tablet', 0.00, 0.00, 116, NULL),
(41, 'Bisacodyl', '5mg', 'Tablet', 0.00, 0.00, 160, NULL),
(42, 'Butamirate Citrate', '50mg', 'Tablet', 0.00, 0.00, 81, NULL),
(43, 'Calcium Carbonate', '500mg', 'Tablet', 0.00, 0.00, 100, NULL),
(44, 'Calcium + Vit D', '3 500mg/200IU', 'Tablet', 0.00, 0.00, 287, NULL),
(45, 'Candesartan', '8mg', 'Tablet', 0.00, 0.00, 23, NULL),
(46, 'Candesartan', '16mg', 'Tablet', 0.00, 0.00, 9, NULL),
(47, 'Captopril', '25mg', 'Tablet', 0.00, 0.00, 200, NULL),
(48, 'Carvedilol', '6.25mg', 'Tablet', 0.00, 0.00, 20, NULL),
(49, 'Carvedilol', '12.5mg', 'Tablet', 0.00, 0.00, 141, NULL),
(50, 'Carvedilol', '25mg', 'Tablet', 0.00, 0.00, 36, NULL),
(51, 'Carbamazepine', '200mg', 'Tablet', 0.00, 0.00, 200, NULL),
(52, 'Carbocisteine', '500mg', 'Tablet', 0.00, 0.00, 459, NULL),
(53, 'Cefalexin', '500mg', 'Tablet', 0.00, 0.00, 178, NULL),
(54, 'Cefixime', '200mg', 'Tablet', 0.00, 0.00, 48, NULL),
(55, 'Cefixime', '400mg', 'Tablet', 0.00, 0.00, 81, NULL),
(56, 'Cefuroxime', '500mg', 'Tablet', 0.00, 0.00, 100, NULL),
(57, 'Celecoxib', '200mg', 'Tablet', 0.00, 0.00, 89, NULL),
(58, 'Celecoxib', '400mg', 'Tablet', 0.00, 0.00, 209, NULL),
(59, 'Cetirizine', '10mg', 'Tablet', 0.00, 0.00, 372, NULL),
(60, 'Cinnarizine', '25mg', 'Tablet', 0.00, 0.00, 145, NULL),
(61, 'Ciprofloxacin', '500mg', 'Tablet', 0.00, 0.00, 88, NULL),
(62, 'Citicoline', '500mg', 'Tablet', 0.00, 0.00, 66, NULL),
(63, 'Chlorphenamine', '4mg', 'Tablet', 0.00, 0.00, 200, NULL),
(64, 'Clarithromycin', '500mg', 'Tablet', 0.00, 0.00, 50, NULL),
(65, 'Clindamycin', '300mg', 'Tablet', 0.00, 0.00, 46, NULL),
(66, 'Clonidine', '75mcg', 'Tablet', 0.00, 0.00, 258, NULL),
(67, 'Clonidine', '150mcg', 'Tablet', 0.00, 0.00, 173, NULL),
(68, 'Cloxacillin', '500mg', 'Tablet', 0.00, 0.00, 109, NULL),
(69, 'Colchicine', '500mcg', 'Tablet', 0.00, 0.00, 153, NULL),
(70, 'Co-amoxiclav', '625mg', 'Tablet', 0.00, 0.00, 74, NULL),
(71, 'Cotrimoxazole', '800mg/160mg', 'Tablet', 0.00, 0.00, 156, NULL),
(72, 'Dexamethasone', '500mcg', 'Tablet', 0.00, 0.00, 200, NULL),
(73, 'Dextro+Guai+PCM+PPA+Dextro+CPM', '', 'Tablet', 0.00, 0.00, 50, NULL),
(74, 'Diclofenac', '50mg', 'Tablet', 0.00, 0.00, 105, NULL),
(75, 'Diclofenac', '100mg', 'Tablet', 0.00, 0.00, 0, NULL),
(76, 'Dicyloverine', '10mg', 'Tablet', 0.00, 0.00, 200, NULL),
(77, 'Digoxin', '250mcg', 'Tablet', 0.00, 0.00, 0, NULL),
(78, 'Diphenhydramine', '50mg', 'Tablet', 0.00, 0.00, 111, NULL),
(79, 'Diosmin+Hesperidine', '450mg/50mg', 'Tablet', 0.00, 0.00, 30, NULL),
(80, 'Domperidone', '10mg', 'Tablet', 0.00, 0.00, 161, NULL),
(81, 'Doxofylline', '400mg', 'Tablet', 0.00, 0.00, 46, NULL),
(82, 'Doxycycline', '100mg', 'Tablet', 0.00, 0.00, 156, NULL),
(83, 'Enalapril', '5mg', 'Tablet', 0.00, 0.00, 185, NULL),
(84, 'Enalapril', '10mg', 'Tablet', 0.00, 0.00, 121, NULL),
(85, 'Enalapril', '20mg', 'Tablet', 0.00, 0.00, 100, NULL),
(86, 'Erythromycin', '500mg', 'Tablet', 0.00, 0.00, 100, NULL),
(87, 'Esomeprazole', '40mg', 'Tablet', 0.00, 0.00, 11, NULL),
(88, 'Etoricoxib', '90mg', 'Tablet', 0.00, 0.00, 16, NULL),
(89, 'Etoricoxib', '120mg', 'Tablet', 0.00, 0.00, 46, NULL),
(90, 'Evening Primrose', '1000mg', 'Tablet', 0.00, 0.00, 12, NULL),
(91, 'Febuxostat', '40mg', 'Tablet', 0.00, 0.00, 0, NULL),
(92, 'Febuxostat', '80mg', 'Tablet', 0.00, 0.00, 33, NULL),
(93, 'Felodipine', '5mg', 'Tablet', 0.00, 0.00, 129, NULL),
(94, 'FeSO', '4 BRISOFER 250mg', 'Tablet', 0.00, 0.00, 69, NULL),
(95, 'FeSO', '4 HEMOVIT 325mg', 'Tablet', 0.00, 0.00, 110, NULL),
(96, 'FeSO', '4 COM-FEMIC 500mg', 'Tablet', 0.00, 0.00, 200, NULL),
(97, 'FeSO', '4+Folic Acid 60mg/250mg', 'Tablet', 0.00, 0.00, 103, NULL),
(98, 'Finasteride', '5mg', 'Tablet', 0.00, 0.00, 50, NULL),
(99, 'Fluconazole', '150mg', 'Tablet', 0.00, 0.00, 4, NULL),
(100, 'Folic Acid', '5mg', 'Tablet', 0.00, 0.00, 95, NULL),
(101, 'Furosemide', '20mg', 'Tablet', 0.00, 0.00, 160, NULL),
(102, 'Furosemide', '40mg', 'Tablet', 0.00, 0.00, 260, NULL),
(103, 'Glibenclamide', '5mg', 'Tablet', 0.00, 0.00, 190, NULL),
(104, 'Gliclazide', '30mg', 'Tablet', 0.00, 0.00, 0, NULL),
(105, 'Gliclazide', '60mg', 'Tablet', 0.00, 0.00, 0, NULL),
(106, 'Gliclazide', '80mg', 'Tablet', 0.00, 0.00, 137, NULL),
(107, 'Glimepiride', '2mg', 'Tablet', 0.00, 0.00, 71, NULL),
(108, 'Glimepiride', '3mg', 'Tablet', 0.00, 0.00, 158, NULL),
(109, 'Glimepiride', '4mg', 'Tablet', 0.00, 0.00, 200, NULL),
(110, 'HNBB', '10mg', 'Tablet', 0.00, 0.00, 71, NULL),
(111, 'Ibuprofen', '200mg', 'Tablet', 0.00, 0.00, 164, NULL),
(112, 'Ibuprofen', '400mg', 'Tablet', 0.00, 0.00, 85, NULL),
(113, 'Irbesartan', '150mg', 'Tablet', 0.00, 0.00, 58, NULL),
(114, 'Irbesartan', '300mg', 'Tablet', 0.00, 0.00, 21, NULL),
(115, 'Isosorbide', '5mg', 'Tablet', 0.00, 0.00, 54, NULL),
(116, 'Isosorbide Dinitrate', '10mg', 'Tablet', 0.00, 0.00, 100, NULL),
(117, 'Isoxsuprine', '10mg', 'Tablet', 0.00, 0.00, 200, NULL),
(118, 'Lagundi', '300mg', 'Tablet', 0.00, 0.00, 133, NULL),
(119, 'Lagundi', '600mg', 'Tablet', 0.00, 0.00, 73, NULL),
(120, 'Levocetirizine', '5mg', 'Tablet', 0.00, 0.00, 100, NULL),
(121, 'Levofloxacin', '500mg', 'Tablet', 0.00, 0.00, 15, NULL),
(122, 'Levothyroxine', '50mcg', 'Tablet', 0.00, 0.00, 185, NULL),
(123, 'Loperamide', '2mg', 'Tablet', 0.00, 0.00, 65, NULL),
(124, 'Loratadine', '10mg', 'Tablet', 0.00, 0.00, 182, NULL),
(125, 'Losartan', '50mg', 'Tablet', 0.00, 0.00, 164, NULL),
(126, 'Losartan', '100mg', 'Tablet', 0.00, 0.00, 537, NULL),
(127, 'Losartan+hctz', '50mg/12.5mg', 'Tablet', 0.00, 0.00, 154, NULL),
(128, 'Losartan+hctz', '100mg/25mg', 'Tablet', 0.00, 0.00, 200, NULL),
(129, 'Malunggay', '500mg', 'Tablet', 0.00, 0.00, 60, NULL),
(130, 'Meclizine', '25mg', 'Tablet', 0.00, 0.00, 38, NULL),
(131, 'Mefenamic', '500mg', 'Tablet', 0.00, 0.00, 140, NULL),
(132, 'Metformin', '500mg', 'Tablet', 0.00, 0.00, 245, NULL),
(133, 'Metformin', '850mg', 'Tablet', 0.00, 0.00, 100, NULL),
(134, 'Methylprednisolone', '16mg', 'Tablet', 0.00, 0.00, 53, NULL),
(135, 'Metoclopramide', '10mg', 'Tablet', 0.00, 0.00, 100, NULL),
(136, 'Metoprolol', '50mg', 'Tablet', 0.00, 0.00, 138, NULL),
(137, 'Metoprolol', '100mg', 'Tablet', 0.00, 0.00, 400, NULL),
(138, 'Metronidazole', '500mg', 'Tablet', 0.00, 0.00, 115, NULL),
(139, 'Montelukast', '5mg', 'Tablet', 0.00, 0.00, 122, NULL),
(140, 'Montelukast', '10mg', 'Tablet', 0.00, 0.00, 22, NULL),
(141, 'Monte+Levo', '10mg/5mg', 'Tablet', 0.00, 0.00, 31, NULL),
(142, 'Multi+Iron FORALIVIT', '', 'Tablet', 0.00, 0.00, 131, NULL),
(143, 'Multi+ Minerals EURIVIT', '', 'Tablet', 0.00, 0.00, 59, NULL),
(144, 'Naproxen', '500mg', 'Tablet', 0.00, 0.00, 107, NULL),
(145, 'Nebivolol', '2.5mg', 'Tablet', 0.00, 0.00, 30, NULL),
(146, 'Nebivolol', '5mg', 'Tablet', 0.00, 0.00, 68, NULL),
(147, 'Nifedipine', '10mg', 'Tablet', 0.00, 0.00, 100, NULL),
(148, 'Nitrofurantoin', '100mg', 'Tablet', 0.00, 0.00, 90, NULL),
(149, 'Olanzapine', '10mg', 'Tablet', 0.00, 0.00, 35, NULL),
(150, 'Omega Fish oil', '1000mg', 'Tablet', 0.00, 0.00, 57, NULL),
(151, 'Omeprazole', '20mg', 'Tablet', 0.00, 0.00, 94, NULL),
(152, 'Omeprazole', '40mg', 'Tablet', 0.00, 0.00, 68, NULL),
(153, 'ORS (Oral Rehydration Salts)', '', 'Tablet', 0.00, 0.00, 4, NULL),
(154, 'Pantoprazole', '40mg', 'Tablet', 0.00, 0.00, 138, NULL),
(155, 'Paracetamol', '500mg', 'Tablet', 0.00, 0.00, 376, NULL),
(156, 'Paracetamol+Ibuprofen', '325mg/200mg', 'Tablet', 0.00, 0.00, 173, NULL),
(157, 'Paracetamol+ Tramadol', '325mg/37.5mg', 'Tablet', 0.00, 0.00, 0, NULL),
(158, 'PCM+ vitamin B complex', '', 'Tablet', 0.00, 0.00, 90, NULL),
(159, 'PCM+PPA+CPM symdex-d', '', 'Tablet', 0.00, 0.00, 123, NULL),
(160, 'PCM+PPA+CPM symdex forte', '', 'Tablet', 0.00, 0.00, 204, NULL),
(161, 'Piroxicam', '10mg', 'Tablet', 0.00, 0.00, 41, NULL),
(162, 'Potassium Chloride', '600mg', 'Tablet', 0.00, 0.00, 47, NULL),
(163, 'Potassium Citrate', '1.080g', 'Tablet', 0.00, 0.00, 57, NULL),
(164, 'Prednisone', '5mg', 'Tablet', 0.00, 0.00, 120, NULL),
(165, 'Prednisone', '10mg', 'Tablet', 0.00, 0.00, 127, NULL),
(166, 'Prednisone', '20mg', 'Tablet', 0.00, 0.00, 110, NULL),
(167, 'Pregabalin', '75mg', 'Tablet', 0.00, 0.00, 73, NULL),
(168, 'Propranolol', '10mg', 'Tablet', 0.00, 0.00, 190, NULL),
(169, 'Propranolol', '40mg', 'Tablet', 0.00, 0.00, 186, NULL),
(170, 'Ranitidine', '150mg', 'Tablet', 0.00, 0.00, 168, NULL),
(171, 'Rifampicin', '450mg', 'Tablet', 0.00, 0.00, 50, NULL),
(172, 'Rifampicin+INH (DUOMAX)', '', 'Tablet', 0.00, 0.00, 157, NULL),
(173, 'Rifam+INH+PZA+EMB (QUADMAX)', '', 'Tablet', 0.00, 0.00, 163, NULL),
(174, 'Rosuvastatin', '10mg', 'Tablet', 0.00, 0.00, 207, NULL),
(175, 'Rosuvastatin', '20mg', 'Tablet', 0.00, 0.00, 39, NULL),
(176, 'Salbutamol', '2mg', 'Tablet', 0.00, 0.00, 242, NULL),
(177, 'Salbutamol', '4mg', 'Tablet', 0.00, 0.00, 141, NULL),
(178, 'Salbutamol nebule', '', 'Tablet', 0.00, 0.00, 8, NULL),
(179, 'Salbutamol+Guaifenesin', '2mg/100mg', 'Tablet', 0.00, 0.00, 265, NULL),
(180, 'Salbu+Guai+Bromphe', '2mg/100/8mg', 'Tablet', 0.00, 0.00, 264, NULL),
(181, 'Salbutamol+ipratropium nebule', '', 'Tablet', 0.00, 0.00, 0, NULL),
(182, 'Salmeterol+Fluticasone inhaler', '', 'Tablet', 0.00, 0.00, 2, NULL),
(183, 'Sambong', '500mg', 'Tablet', 0.00, 0.00, 90, NULL),
(184, 'Sildenafil', '50mg', 'Tablet', 0.00, 0.00, 14, NULL),
(185, 'Sildenafil', '100mg', 'Tablet', 0.00, 0.00, 33, NULL),
(186, 'Silymarin+bcomplex', '', 'Tablet', 0.00, 0.00, 32, NULL),
(187, 'Simvastatin', '10mg', 'Tablet', 0.00, 0.00, 110, NULL),
(188, 'Simvastatin', '20mg', 'Tablet', 0.00, 0.00, 300, NULL),
(189, 'Simvastatin', '40mg', 'Tablet', 0.00, 0.00, 200, NULL),
(190, 'Sodium Ascorbate+Zinc', '', 'Tablet', 0.00, 0.00, 35, NULL),
(191, 'Spironolactone', '25mg', 'Tablet', 0.00, 0.00, 100, NULL),
(192, 'Tamsulosin', '400mcg', 'Tablet', 0.00, 0.00, 9, NULL),
(193, 'Telmisartan', '40mg', 'Tablet', 0.00, 0.00, 0, NULL),
(194, 'Telmisartan', '80mg', 'Tablet', 0.00, 0.00, 205, NULL),
(195, 'Telmisartan+hctz', '40mg/12.5mg', 'Tablet', 0.00, 0.00, 200, NULL),
(196, 'Tramadol', '50mg', 'Tablet', 0.00, 0.00, 145, NULL),
(197, 'Tranexamic', '500mg', 'Tablet', 0.00, 0.00, 86, NULL),
(198, 'Trimetazidine', '35mg', 'Tablet', 0.00, 0.00, 100, NULL),
(199, 'Vit+Min+Ginseng activcon', '', 'Tablet', 0.00, 0.00, 74, NULL),
(200, 'Vit bcomplex+Iron+Buclizine', '', 'Tablet', 0.00, 0.00, 110, NULL),
(201, 'Vitamin E', '400IU', 'Tablet', 0.00, 0.00, 30, NULL),
(202, 'Vitamins multivita', '', 'Tablet', 0.00, 0.00, 80, NULL),
(203, 'Vitamins+ Minerals Reprogen', '', 'Tablet', 0.00, 0.00, 43, NULL),
(204, 'Al+MgOH', '60ml 200mg/100ml', 'Syrup', 0.00, 0.00, 5, NULL),
(205, 'Al+MgOH', '120ml 200mg/100ml', 'Syrup', 0.00, 0.00, 2, NULL),
(206, 'Ambroxol', '6mg/ml', 'Syrup', 0.00, 0.00, 5, NULL),
(207, 'Ambroxol', '15mg/5ml', 'Syrup', 0.00, 0.00, 5, NULL),
(208, 'Ambroxol', '30mg/5ml', 'Syrup', 0.00, 0.00, 4, NULL),
(209, 'Amoxicillin', '100mg/ml', 'Syrup', 0.00, 0.00, 5, NULL),
(210, 'Amoxicillin', '125mg/5ml', 'Syrup', 0.00, 0.00, 5, NULL),
(211, 'Amoxicillin', '250mg/5ml', 'Syrup', 0.00, 0.00, 2, NULL),
(212, 'Ascorbic Acid myrevit drops', '100mg/ml', 'Syrup', 0.00, 0.00, 5, NULL),
(213, 'Ascorbic Acid myrevit', '120ml 100mg/5ml', 'Syrup', 0.00, 0.00, 2, NULL),
(214, 'Ascorbic Acid raph c', '60ml', 'Syrup', 0.00, 0.00, 4, NULL),
(215, 'Ascorbic Acid raph c', '120ml', 'Syrup', 0.00, 0.00, 0, NULL),
(216, 'Ascorbic Acid +zinc Marlum C Plu', '6s0ml', 'Syrup', 0.00, 0.00, 4, NULL),
(217, 'Ascorbic Acid + zinc Marlum C Pl', '1u2s0ml', 'Syrup', 0.00, 0.00, 1, NULL),
(218, 'Azithromycin', '200mg/5ml', 'Syrup', 0.00, 0.00, 4, NULL),
(219, 'Butamirate', '60ml 7.5mg/5ml', 'Syrup', 0.00, 0.00, 0, NULL),
(220, 'Butamirate', '120ml 7.5mg/5ml', 'Syrup', 0.00, 0.00, 2, NULL),
(221, 'Carbocisteine', '50mg/5ml', 'Syrup', 0.00, 0.00, 5, NULL),
(222, 'Carbocisteine', '100mg/ml', 'Syrup', 0.00, 0.00, 4, NULL),
(223, 'Carbocisteine', '125mg/5ml', 'Syrup', 0.00, 0.00, 5, NULL),
(224, 'Carbocisteine', '250mg/5ml', 'Syrup', 0.00, 0.00, 5, NULL),
(225, 'Cefaclor', '50mg/ml', 'Syrup', 0.00, 0.00, 5, NULL),
(226, 'Cefaclor', '250mg/5ml', 'Syrup', 0.00, 0.00, 6, NULL),
(227, 'Cefalexin', '100mg/ml', 'Syrup', 0.00, 0.00, 5, NULL),
(228, 'Cefalexin', '125mg/5ml', 'Syrup', 0.00, 0.00, 4, NULL),
(229, 'Cefalexin', '250mg/5ml', 'Syrup', 0.00, 0.00, 5, NULL),
(230, 'Cefixime', '20mg/ml', 'Syrup', 0.00, 0.00, 6, NULL),
(231, 'Cefixime', '100mg/5ml', 'Syrup', 0.00, 0.00, 6, NULL),
(232, 'Cefuroxime', '125mg/5ml', 'Syrup', 0.00, 0.00, 4, NULL),
(233, 'Cefuroxime', '250mg/5ml', 'Syrup', 0.00, 0.00, 4, NULL),
(234, 'Cetirizine', '2.5mg/ml', 'Syrup', 0.00, 0.00, 3, NULL),
(235, 'Cetirizine', '5mg/5ml', 'Syrup', 0.00, 0.00, 0, NULL),
(236, 'Chlorphenamine', '125mg/5ml', 'Syrup', 0.00, 0.00, 2, NULL),
(237, 'Chloramphenicol', '125mg5ml', 'Syrup', 0.00, 0.00, 5, NULL),
(238, 'Clarithromycin', '125mg/5ml', 'Syrup', 0.00, 0.00, 4, NULL),
(239, 'Clarithromycin', '250mg/5ml', 'Syrup', 0.00, 0.00, 5, NULL),
(240, 'Cloxacillin', '125mg/5ml', 'Syrup', 0.00, 0.00, 5, NULL),
(241, 'Cloxacillin', '250mg/5ml', 'Syrup', 0.00, 0.00, 3, NULL),
(242, 'Co-amoxiclav', '156.25mg/5ml', 'Syrup', 0.00, 0.00, 0, NULL),
(243, 'Co-amoxiclav', '312.5mg/5ml', 'Syrup', 0.00, 0.00, 4, NULL),
(244, 'Co-amoxiclav', '457mg/5ml', 'Syrup', 0.00, 0.00, 5, NULL),
(245, 'Cotrimoxazole', '200mg/40mg', 'Syrup', 0.00, 0.00, 4, NULL),
(246, 'Cotrimoxazole', '400mg/80mg', 'Syrup', 0.00, 0.00, 4, NULL),
(247, 'Dicycloverine', '10mg/5ml', 'Syrup', 0.00, 0.00, 3, NULL),
(248, 'Diphenhydramine', '12.5mg/5ml', 'Syrup', 0.00, 0.00, 4, NULL),
(249, 'Domperidone', '1ml/ml', 'Syrup', 0.00, 0.00, 2, NULL),
(250, 'Erythromycin', '200mg/5ml', 'Syrup', 0.00, 0.00, 5, NULL),
(251, 'FeSO', '4 drops 15mg/ml', 'Syrup', 0.00, 0.00, 5, NULL),
(252, 'FeSO', '4 syrup 220mg/5ml', 'Syrup', 0.00, 0.00, 4, NULL),
(253, 'Guai+PPA+CPM', '60ml', 'Syrup', 0.00, 0.00, 2, NULL),
(254, 'Guai+PPA+CPM', '120ml', 'Syrup', 0.00, 0.00, 3, NULL),
(255, 'HNBB', '5mg/5ml', 'Syrup', 0.00, 0.00, 1, NULL),
(256, 'Ibuprofen', '200mg/5ml', 'Syrup', 0.00, 0.00, 4, NULL),
(257, 'Lactulose', '', 'Syrup', 0.00, 0.00, 4, NULL),
(258, 'Lagundi', '60ml 300mg/5ml', 'Syrup', 0.00, 0.00, 2, NULL),
(259, 'Lagundi', '120ml 300mg/5ml', 'Syrup', 0.00, 0.00, 3, NULL),
(260, 'Loratadine', '5mg/5ml', 'Syrup', 0.00, 0.00, 5, NULL),
(261, 'Mebendazole', '100mg/5ml', 'Syrup', 0.00, 0.00, 4, NULL),
(262, 'Mefenamic', '50mg/5ml', 'Syrup', 0.00, 0.00, 5, NULL),
(263, 'Metoclopramide', '5mg/5ml', 'Syrup', 0.00, 0.00, 1, NULL),
(264, 'Metronidazole', '125mg/5ml', 'Syrup', 0.00, 0.00, 3, NULL),
(265, 'Multivitamins regivit', '', 'Syrup', 0.00, 0.00, 5, NULL),
(266, 'Multivitamins multilem', '120ml', 'Syrup', 0.00, 0.00, 1, NULL),
(267, 'Multivitamins+iron Multilem Plus', '60ml', 'Syrup', 0.00, 0.00, 2, NULL),
(268, 'Multivitamins+minerals eurivit', '', 'Syrup', 0.00, 0.00, 2, NULL),
(269, 'Nystatin', '', 'Syrup', 0.00, 0.00, 2, NULL),
(270, 'Paracetamol drops', '', 'Syrup', 0.00, 0.00, 7, NULL),
(271, 'Paracetamol', '120mg/5ml', 'Syrup', 0.00, 0.00, 3, NULL),
(272, 'Paracetamol', '250mg/5ml', 'Syrup', 0.00, 0.00, 6, NULL),
(273, 'PCM+PPA+CPM symdex drops', '', 'Syrup', 0.00, 0.00, 2, NULL),
(274, 'Pizotifen', '', 'Syrup', 0.00, 0.00, 0, NULL),
(275, 'Phenylpropanolamine drops', '', 'Syrup', 0.00, 0.00, 3, NULL),
(276, 'Phenylpropanolamine syrup', '', 'Syrup', 0.00, 0.00, 1, NULL),
(277, 'Prednisone syrup', '', 'Syrup', 0.00, 0.00, 2, NULL),
(278, 'Salbutamol syrup', '', 'Syrup', 0.00, 0.00, 2, NULL),
(279, 'Salbutamol+Guaifenesin', '', 'Syrup', 0.00, 0.00, 3, NULL),
(280, 'Zinc Sulfate syrup', '', 'Syrup', 0.00, 0.00, 3, NULL),
(281, 'Betamethasone', '1mg/g', 'Cream/Ointment', 0.00, 0.00, 3, NULL),
(282, 'Calamine+Zinc Oxide', '40mg/30mg/g', 'Cream/Ointment', 0.00, 0.00, 24, NULL),
(283, 'Clobetasol', '500mcg/g', 'Cream/Ointment', 0.00, 0.00, 2, NULL),
(284, 'Clotrimazole', '10mg/g', 'Cream/Ointment', 0.00, 0.00, 0, NULL),
(285, 'Erythromycin', '5mg/5g', 'Cream/Ointment', 0.00, 0.00, 1, NULL),
(286, 'Hydrocortisone', '10mg/g', 'Cream/Ointment', 0.00, 0.00, 3, NULL),
(287, 'Ketoconazole', '20mg/g', 'Cream/Ointment', 0.00, 0.00, 0, NULL),
(288, 'Miconazole', '20mg/g', 'Cream/Ointment', 0.00, 0.00, 1, NULL),
(289, 'Mometasone', '1mg/g', 'Cream/Ointment', 0.00, 0.00, 0, NULL),
(290, 'Mupirocin', '20mg/g', 'Cream/Ointment', 0.00, 0.00, 3, NULL),
(291, 'Silver Sulfadiazine', '10mg/g', 'Cream/Ointment', 0.00, 0.00, 4, NULL),
(292, 'Boric+Borax Equisine reg', '', 'Ophthalmic Drops', 0.00, 0.00, 0, NULL),
(293, 'Gentamycin', '3mg/ml', 'Ophthalmic Drops', 0.00, 0.00, 0, NULL),
(294, 'Hypomellose Equisine moist', '', 'Ophthalmic Drops', 0.00, 0.00, 0, NULL),
(295, 'Poly+Neo+Dexa', '6,000IU/5/1/ml', 'Ophthalmic Drops', 0.00, 0.00, 5, NULL),
(296, 'Tobramycin', '3mg', 'Ophthalmic Drops', 0.00, 0.00, 4, NULL),
(297, 'Tobra+Dexa', '3mg/1ml', 'Ophthalmic Drops', 0.00, 0.00, 2, NULL),
(298, 'Ofloxacin', '', 'Otic Drops', 0.00, 0.00, 1, NULL),
(299, 'Poly+Neo+Dexa', '10,000mg/5/1ml', 'Otic Drops', 0.00, 0.00, 2, NULL);

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
(58, 15, '2026-09-26 07:33:50', 330.00, 'None', 0.00, 0.00, '', '', 'Cash', 500.00, 170.00),
(59, 15, '2026-09-26 07:51:54', 152.00, 'Senior Citizen', 20.00, 38.00, '', '', 'Cash', 200.00, 48.00),
(60, 15, '2026-09-26 08:13:21', 90.00, 'None', 0.00, 0.00, '', '', 'Cash', 700.00, 610.00),
(61, 15, '2026-09-26 08:23:28', 550.00, 'None', 0.00, 0.00, '', '', 'Cash', 550.00, 0.00),
(62, 15, '2026-09-26 10:22:33', 100.00, 'None', 0.00, 0.00, '', '', 'Cash', 119.98, 19.98),
(63, 16, '2026-09-26 12:14:42', 600.00, 'Senior Citizen', 20.00, 150.00, 'William maslkdjsakdlp;', '123456', 'Cash', 610.00, 10.00),
(64, 16, '2026-09-26 12:28:45', 50.00, 'None', 0.00, 0.00, '', '', 'Cash', 50.00, 0.00);

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
(58, 58, '2026-09-26', 330.00, 5),
(59, 59, '2026-09-26', 152.00, 3),
(60, 60, '2026-09-26', 90.00, 1),
(61, 61, '2026-09-26', 550.00, 11),
(62, 62, '2026-09-26', 100.00, 2),
(63, 63, '2026-09-26', 600.00, 20),
(64, 64, '2026-09-26', 50.00, 8);

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
(118, 58, 14, 2, 90.00, 180.00),
(119, 58, 15, 3, 50.00, 150.00),
(120, 59, 16, 2, 50.00, 100.00),
(121, 59, 14, 1, 90.00, 90.00),
(122, 60, 14, 1, 90.00, 90.00),
(123, 61, 15, 11, 50.00, 550.00),
(124, 62, 15, 2, 50.00, 100.00),
(125, 63, 14, 5, 0.00, 0.00),
(126, 63, 15, 9, 50.00, 450.00),
(127, 63, 16, 6, 50.00, 300.00),
(128, 64, 21, 4, 0.00, 0.00),
(129, 64, 24, 3, 0.00, 0.00),
(130, 64, 16, 1, 50.00, 50.00);

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
(11, 'Test Admin', 'testadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCw5q7kY1Y', 'Admin', 'testadmin@valuemeds.com', '09170000001', 'Active'),
(15, 'Juan Dela Cruz', 'testadmins', '$2y$12$KK0l6VBSqwDozUj4zZ6Jl.BZKx22OHfoNtC9sSB27eRhULMH8pdEa', 'Admin', 'admin@valuemeds.com', '09171234567', 'Active'),
(16, 'Laarni Billones', 'Laarni', '$2y$10$Tfi9HGsg./7gxZ5Eabbsu.ppXMVSpDMT2VOLR9EKlrkIGffJn/.4m', 'Pharmacist', '', '', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `z_readings`
--

CREATE TABLE `z_readings` (
  `id` int(11) NOT NULL,
  `reading_number` int(11) NOT NULL,
  `cashier` varchar(255) NOT NULL,
  `total_transactions` int(11) NOT NULL DEFAULT 0,
  `gross_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discounts` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cash_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `gcash_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `card_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `refunds` decimal(12,2) NOT NULL DEFAULT 0.00,
  `generated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `exchanges`
--
ALTER TABLE `exchanges`
  ADD PRIMARY KEY (`exchange_id`),
  ADD KEY `idx_sale_id` (`original_sale_id`);

--
-- Indexes for table `exchange_items`
--
ALTER TABLE `exchange_items`
  ADD PRIMARY KEY (`exchange_item_id`),
  ADD KEY `idx_exchange_id` (`exchange_id`);

--
-- Indexes for table `exchange_logs`
--
ALTER TABLE `exchange_logs`
  ADD PRIMARY KEY (`log_id`);

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
-- Indexes for table `z_readings`
--
ALTER TABLE `z_readings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `exchanges`
--
ALTER TABLE `exchanges`
  MODIFY `exchange_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `exchange_items`
--
ALTER TABLE `exchange_items`
  MODIFY `exchange_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `exchange_logs`
--
ALTER TABLE `exchange_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `medicine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=300;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `sales_report`
--
ALTER TABLE `sales_report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `stock_in`
--
ALTER TABLE `stock_in`
  MODIFY `stockin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `z_readings`
--
ALTER TABLE `z_readings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
