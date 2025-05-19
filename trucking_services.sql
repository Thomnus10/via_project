-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2025 at 07:37 PM
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
-- Database: `trucking_services`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_requests`
--

CREATE TABLE `account_requests` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `request_date` datetime DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_requests`
--

INSERT INTO `account_requests` (`id`, `full_name`, `email`, `contact_number`, `username`, `request_date`, `status`) VALUES
(1, 'Owin Heart Albacite', 'thomnuszzz@gmail.com', '0192312341', 'test', '2025-04-19 05:30:29', 'approved'),
(2, 'Christian Jose', 'christianski666@gmail.com', '1231230123', 'zenra', '2025-04-21 08:38:40', 'approved'),
(4, 'afasfq3asfa', 'rocky.adaya101@gmail.com', '1231231241', 'rocky211', '2025-04-21 09:31:33', 'approved'),
(7, 'owin', 'o.albacite.546189@umindanao.edu.ph', '019231313131', 'sample', '2025-04-24 04:26:43', 'approved'),
(8, 'ivycarl', 'ivycarl.benjamin01@gmail.com', '09096570733', 'ivy', '2025-04-24 16:19:38', 'approved'),
(9, 'anaia zureriel', 'judithkimmarie_marbascias@sjp2cd.edu.ph', '00403435453', 'anya', '2025-05-09 22:21:04', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_no` varchar(20) DEFAULT '09123456789',
  `address` text DEFAULT 'Sample Address'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `full_name`, `user_id`, `email`, `contact_no`, `address`) VALUES
(1, 'Admin User', 4, 'admin@trucking.com', '09112223344', '123 Admin Street, Malolos City, Bulacan');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `contact_no` varchar(20) DEFAULT '09123456789',
  `address` text DEFAULT 'Sample Address',
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `full_name`, `contact_no`, `address`, `user_id`, `email`) VALUES
(1, 'ABC Manufacturing', '09123456789', '123 Industrial Park, Davao City', 11, 'abc@customer.com'),
(2, 'XYZ Distributors', '09987654321', '456 Trade Ave, Tagum City', 12, 'xyz@customer.com'),
(3, 'Owin Heart Albacite', '09123456789', 'Sample Address', 17, 'thomnuszzz@gmail.com'),
(10, 'Christian Jose', '09123456789', 'Sample Address', 41, 'christianski666@gmail.com'),
(14, 'afasfq3asfa', '09123456789', 'Sample Address', 46, 'rocky.adaya101@gmail.com'),
(16, 'owin albacite', '09123456789', 'Sample Address', 48, 'oalbacite@gmail.com'),
(17, 'owin', '09123456789', 'Sample Address', 49, 'o.albacite.546189@umindanao.edu.ph'),
(18, 'ivycarl', '09123456789', 'Sample Address', 60, 'ivycarl.benjamin01@gmail.com'),
(19, 'anaia zureriel', '09123456789', 'Sample Address', 62, 'judithkimmarie_marbascias@sjp2cd.edu.ph');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `delivery_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `delivery_status` enum('Pending','Accepted','In Transit','Delivered','Completed','Cancelled','Received') NOT NULL DEFAULT 'Pending',
  `delivery_datetime` datetime DEFAULT current_timestamp(),
  `received_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`delivery_id`, `schedule_id`, `delivery_status`, `delivery_datetime`, `received_date`) VALUES
(1, 1, 'Received', '2025-04-24 02:36:19', NULL),
(2, 2, 'Received', '2025-04-24 03:14:27', NULL),
(3, 3, 'Received', '2025-04-24 03:29:11', NULL),
(4, 4, 'Received', '2025-04-24 03:43:10', NULL),
(5, 5, 'Received', '2025-04-24 03:48:13', NULL),
(6, 6, 'Received', '2025-04-24 04:28:30', NULL),
(7, 6, 'Received', '2025-05-01 08:00:00', '2025-05-01 12:15:00'),
(8, 7, 'Received', '2025-05-02 09:00:00', '2025-05-02 13:10:00'),
(9, 8, 'Received', '2025-05-03 07:00:00', '2025-05-03 15:20:00'),
(10, 9, 'Received', '2025-05-04 10:00:00', '2025-05-04 14:05:00'),
(11, 10, 'Received', '2025-05-05 08:00:00', '2025-05-05 11:30:00'),
(12, 11, 'Received', '2025-05-01 07:00:00', '2025-05-01 16:30:00'),
(13, 12, 'Received', '2025-05-02 08:00:00', '2025-05-02 12:20:00'),
(14, 13, 'Received', '2025-05-03 09:00:00', '2025-05-03 13:15:00'),
(15, 14, 'Received', '2025-05-04 06:00:00', '2025-05-04 10:10:00'),
(16, 15, 'Received', '2025-05-05 07:00:00', '2025-05-05 17:45:00'),
(17, 16, 'Received', '2025-05-01 08:00:00', '2025-05-01 12:05:00'),
(18, 17, 'Received', '2025-05-02 09:00:00', '2025-05-02 14:15:00'),
(19, 18, 'Received', '2025-05-03 07:00:00', '2025-05-03 11:20:00'),
(20, 19, 'Received', '2025-05-04 06:00:00', '2025-05-04 18:30:00'),
(21, 20, 'Received', '2025-05-05 08:00:00', '2025-05-05 12:10:00'),
(22, 21, 'Received', '2025-05-01 07:00:00', '2025-05-01 11:15:00'),
(23, 22, 'Received', '2025-05-02 08:00:00', '2025-05-02 16:45:00'),
(24, 23, 'Received', '2025-05-03 09:00:00', '2025-05-03 13:10:00'),
(25, 24, 'Received', '2025-05-04 06:00:00', '2025-05-04 10:05:00'),
(26, 25, 'Received', '2025-05-05 08:00:00', '2025-05-05 12:20:00'),
(27, 26, 'Received', '2025-05-01 08:00:00', '2025-05-01 12:45:00'),
(28, 27, 'Received', '2025-05-02 09:00:00', '2025-05-02 13:10:00'),
(29, 28, 'Received', '2025-05-03 07:00:00', '2025-05-03 11:15:00'),
(30, 29, 'Received', '2025-05-04 06:00:00', '2025-05-04 10:05:00'),
(31, 30, 'Received', '2025-05-05 08:00:00', '2025-05-05 12:30:00'),
(32, 32, 'Received', '2025-05-01 03:19:35', NULL),
(33, 33, 'Completed', '2025-05-24 21:46:32', NULL),
(34, 34, 'Received', '2025-05-24 20:30:13', '2025-05-25 01:19:19'),
(35, 35, 'Received', '2025-05-24 20:30:24', '2025-05-25 01:19:34'),
(36, 36, 'Pending', '2025-05-24 17:57:44', NULL),
(37, 36, 'Cancelled', '2025-05-24 17:57:54', NULL),
(38, 37, 'Pending', '2025-05-24 18:04:18', NULL),
(39, 37, 'Cancelled', '2025-05-24 18:04:25', NULL),
(40, 38, 'Pending', '2025-05-25 01:25:57', NULL),
(41, 39, 'Pending', '2025-05-25 01:28:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `driver_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `contact_no` varchar(20) DEFAULT '09123456789',
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text DEFAULT 'Sample Address'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`driver_id`, `full_name`, `contact_no`, `user_id`, `email`, `address`) VALUES
(1, 'Juan Dela Cruz', '09112223333', 15, 'juan@driver.com', '456 Driver Lane, Guiguinto, Bulacan'),
(2, 'Pedro Santos', '09123334444', 16, 'pedro@driver.com', '789 Driver Road, Plaridel, Bulacan'),
(3, 'Carlos Mendoza', '09123456789', 20, 'carlos@driver.com', '321 Trucker Ave, Bocaue, Bulacan'),
(4, 'Ramon Garcia', '09123456789', 21, 'ramon@driver.com', '654 Hauler Blvd, Marilao, Bulacan'),
(5, 'Andres Rivera', '09123456789', 22, 'andres@driver.com', '987 Cargo Street, Balagtas, Bulacan');

-- --------------------------------------------------------

--
-- Table structure for table `helpers`
--

CREATE TABLE `helpers` (
  `helper_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `contact_no` varchar(20) DEFAULT '09123456789',
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text DEFAULT 'Sample Address'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `helpers`
--

INSERT INTO `helpers` (`helper_id`, `full_name`, `contact_no`, `user_id`, `email`, `address`) VALUES
(1, 'Javier Lopez', '09123456789', 18, 'javier@trucking.com', '111 Helper Lane, Meycauayan City, Bulacan'),
(2, 'Luis Fernandez', '09123456789', 19, 'luis@trucking.com', '222 Assistant Road, San Jose del Monte City, Bulacan'),
(3, 'Mario Lopez', '09123456789', 23, 'mario@trucking.com', '333 Support Ave, Santa Maria, Bulacan'),
(4, 'Diego Santos', '09123456789', 24, 'diego@trucking.com', '444 Aid Blvd, Norzagaray, Bulacan'),
(5, 'Miguel Reyes', '09123456789', 25, 'miguel@trucking.com', '555 Crew Street, San Rafael, Bulacan');

-- --------------------------------------------------------

--
-- Table structure for table `helper_payroll`
--

CREATE TABLE `helper_payroll` (
  `payroll_id` int(11) NOT NULL,
  `helper_id` int(11) NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `total_deliveries` int(11) DEFAULT 0,
  `base_salary` decimal(10,2) NOT NULL DEFAULT 5000.00,
  `bonuses` decimal(10,2) DEFAULT 0.00,
  `sss_deduction` decimal(10,2) DEFAULT 581.30,
  `philhealth_deduction` decimal(10,2) DEFAULT 450.00,
  `pagibig_deduction` decimal(10,2) DEFAULT 100.00,
  `deductions` decimal(10,2) DEFAULT 1131.30,
  `net_pay` decimal(10,2) DEFAULT 3868.70,
  `payment_status` enum('Pending','Paid') DEFAULT 'Pending',
  `payment_date` date DEFAULT NULL,
  `date_generated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `helper_payroll`
--

INSERT INTO `helper_payroll` (`payroll_id`, `helper_id`, `pay_period_start`, `pay_period_end`, `total_deliveries`, `base_salary`, `bonuses`, `sss_deduction`, `philhealth_deduction`, `pagibig_deduction`, `deductions`, `net_pay`, `payment_status`, `payment_date`, `date_generated`) VALUES
(46, 1, '2025-04-01', '2025-04-30', 2, 5000.00, 10750.00, 450.00, 450.00, 100.00, 1000.00, 14750.00, 'Pending', NULL, '2025-05-01 03:33:17'),
(47, 2, '2025-04-01', '2025-04-30', 1, 5000.00, 8625.00, 450.00, 450.00, 100.00, 1000.00, 12625.00, 'Pending', NULL, '2025-05-01 03:33:17'),
(48, 3, '2025-04-01', '2025-04-30', 3, 5000.00, 10750.00, 450.00, 450.00, 100.00, 1000.00, 14750.00, 'Pending', NULL, '2025-05-01 03:33:17'),
(49, 4, '2025-04-01', '2025-04-30', 0, 5000.00, 11875.00, 450.00, 450.00, 100.00, 1000.00, 15875.00, 'Pending', NULL, '2025-05-01 03:33:17'),
(50, 5, '2025-04-01', '2025-04-30', 1, 5000.00, 5750.00, 450.00, 450.00, 100.00, 1000.00, 9750.00, 'Pending', NULL, '2025-05-01 03:33:17');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 5000.00,
  `status` enum('Pending','Paid','Refunded','Cancelled') DEFAULT 'Paid',
  `date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `schedule_id`, `total_amount`, `status`, `date`) VALUES
(1, 1, 25000.00, 'Paid', '2025-04-24 02:35:50'),
(2, 2, 30000.00, 'Paid', '2025-04-24 03:13:31'),
(3, 3, 25000.00, 'Paid', '2025-04-24 03:28:51'),
(4, 4, 25000.00, 'Paid', '2025-04-24 03:42:59'),
(5, 5, 15000.00, 'Paid', '2025-04-24 03:48:03'),
(6, 6, 30000.00, 'Paid', '2025-04-24 04:28:14'),
(7, 6, 20000.00, 'Paid', '2025-05-01 07:30:00'),
(8, 7, 17500.00, 'Paid', '2025-05-02 08:30:00'),
(9, 8, 37500.00, 'Paid', '2025-05-03 06:30:00'),
(10, 9, 25000.00, 'Paid', '2025-05-04 09:30:00'),
(11, 10, 10000.00, 'Paid', '2025-05-05 07:30:00'),
(12, 11, 55000.00, 'Paid', '2025-05-01 06:30:00'),
(13, 12, 22500.00, 'Paid', '2025-05-02 07:30:00'),
(14, 13, 27500.00, 'Paid', '2025-05-03 08:30:00'),
(15, 14, 12500.00, 'Paid', '2025-05-04 05:30:00'),
(16, 15, 70000.00, 'Paid', '2025-05-05 06:30:00'),
(17, 16, 15000.00, 'Paid', '2025-05-01 07:30:00'),
(18, 17, 32500.00, 'Paid', '2025-05-02 08:30:00'),
(19, 18, 10000.00, 'Paid', '2025-05-03 06:30:00'),
(20, 19, 65000.00, 'Paid', '2025-05-04 05:30:00'),
(21, 20, 20000.00, 'Paid', '2025-05-05 07:30:00'),
(22, 21, 17500.00, 'Paid', '2025-05-01 06:30:00'),
(23, 22, 75000.00, 'Paid', '2025-05-02 07:30:00'),
(24, 23, 22500.00, 'Paid', '2025-05-03 08:30:00'),
(25, 24, 15000.00, 'Paid', '2025-05-04 05:30:00'),
(26, 25, 30000.00, 'Paid', '2025-05-05 07:30:00'),
(27, 26, 95000.00, 'Paid', '2025-05-01 07:30:00'),
(28, 27, 12500.00, 'Paid', '2025-05-02 08:30:00'),
(29, 28, 20000.00, 'Paid', '2025-05-03 06:30:00'),
(30, 29, 17500.00, 'Paid', '2025-05-04 05:30:00'),
(31, 30, 40000.00, 'Paid', '2025-05-05 07:30:00'),
(32, 32, 20000.00, 'Paid', '2025-05-01 03:46:07'),
(33, 33, 20000.00, 'Paid', '2025-05-24 21:45:21'),
(34, 34, 25000.00, 'Pending', '2025-05-24 17:50:42'),
(35, 35, 15000.00, 'Pending', '2025-05-24 17:57:01'),
(36, 36, 10000.00, 'Cancelled', '2025-05-24 17:57:44'),
(37, 37, 25000.00, 'Cancelled', '2025-05-24 18:04:18'),
(38, 38, 25000.00, 'Pending', '2025-05-25 01:25:57'),
(39, 39, 20000.00, 'Pending', '2025-05-25 01:28:37');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `payroll_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `total_deliveries` int(11) DEFAULT 2,
  `total_earnings` decimal(10,2) DEFAULT 1500.00,
  `date_generated` datetime DEFAULT current_timestamp(),
  `base_salary` decimal(10,2) NOT NULL DEFAULT 8000.00,
  `bonuses` decimal(10,2) DEFAULT 0.00,
  `sss_deduction` decimal(10,2) DEFAULT 581.30,
  `philhealth_deduction` decimal(10,2) DEFAULT 450.00,
  `pagibig_deduction` decimal(10,2) DEFAULT 100.00,
  `truck_maintenance` decimal(10,2) DEFAULT 500.00,
  `deductions` decimal(10,2) DEFAULT 1631.30,
  `net_pay` decimal(10,2) DEFAULT 12368.70,
  `payment_status` enum('Pending','Paid') DEFAULT 'Paid',
  `payment_date` date DEFAULT curdate(),
  `tax_deduction` decimal(10,2) DEFAULT 0.00,
  `delivery_revenue` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`payroll_id`, `driver_id`, `pay_period_start`, `pay_period_end`, `total_deliveries`, `total_earnings`, `date_generated`, `base_salary`, `bonuses`, `sss_deduction`, `philhealth_deduction`, `pagibig_deduction`, `truck_maintenance`, `deductions`, `net_pay`, `payment_status`, `payment_date`, `tax_deduction`, `delivery_revenue`) VALUES
(46, 1, '2025-04-01', '2025-04-30', 1, 1500.00, '2025-05-01 03:33:17', 8000.00, 9625.00, 450.00, 450.00, 100.00, 500.00, 1500.00, 16125.00, 'Pending', '2025-05-01', 0.00, 9625.00),
(47, 2, '2025-04-01', '2025-04-30', 1, 1500.00, '2025-05-01 03:33:17', 8000.00, 14550.00, 450.00, 450.00, 100.00, 500.00, 1757.50, 20792.50, 'Pending', '2025-05-01', 257.50, 14550.00),
(48, 3, '2025-04-01', '2025-04-30', 3, 1500.00, '2025-05-01 03:33:17', 8000.00, 9740.00, 450.00, 450.00, 100.00, 1500.00, 2500.00, 15240.00, 'Pending', '2025-05-01', 0.00, 9740.00),
(49, 4, '2025-04-01', '2025-04-30', 0, 1500.00, '2025-05-01 03:33:17', 8000.00, 9250.00, 450.00, 450.00, 100.00, 0.00, 1000.00, 16250.00, 'Pending', '2025-05-01', 0.00, 9250.00),
(50, 5, '2025-04-01', '2025-04-30', 1, 1500.00, '2025-05-01 03:33:17', 8000.00, 5850.00, 450.00, 450.00, 100.00, 500.00, 1500.00, 12350.00, 'Pending', '2025-05-01', 0.00, 5850.00);

-- --------------------------------------------------------

--
-- Table structure for table `payroll_settings`
--

CREATE TABLE `payroll_settings` (
  `id` int(11) NOT NULL,
  `pay_period` varchar(7) DEFAULT NULL,
  `driver_base_salary` decimal(10,2) DEFAULT 8000.00,
  `helper_base_salary` decimal(10,2) DEFAULT 5000.00,
  `sss_rate` decimal(10,2) DEFAULT 581.30,
  `philhealth_rate` decimal(10,2) DEFAULT 450.00,
  `pagibig_rate` decimal(10,2) DEFAULT 100.00,
  `truck_maintenance_deduction` decimal(10,2) DEFAULT 500.00,
  `delivery_bonus_per_delivery` decimal(10,2) DEFAULT 50.00,
  `tax_rate_percentage` decimal(5,2) DEFAULT 0.00,
  `rate_6w` decimal(10,2) DEFAULT 0.00,
  `rate_8w` decimal(10,2) DEFAULT 0.00,
  `rate_10w` decimal(10,2) DEFAULT 0.00,
  `rate_12w` decimal(10,2) DEFAULT 0.00,
  `effective_date` date DEFAULT curdate(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_settings`
--

INSERT INTO `payroll_settings` (`id`, `pay_period`, `driver_base_salary`, `helper_base_salary`, `sss_rate`, `philhealth_rate`, `pagibig_rate`, `truck_maintenance_deduction`, `delivery_bonus_per_delivery`, `tax_rate_percentage`, `rate_6w`, `rate_8w`, `rate_10w`, `rate_12w`, `effective_date`, `created_at`) VALUES
(1, '2025-04', 8000.00, 5000.00, 450.00, 450.00, 100.00, 500.00, 50.00, 0.00, 15.00, 20.00, 25.00, 30.00, '2025-04-30', '2025-04-29 20:42:04'),
(2, '2025-05', 8000.00, 5000.00, 450.00, 450.00, 100.00, 500.00, 50.00, 0.00, 15.00, 20.00, 25.00, 30.00, '2025-04-30', '2025-04-29 21:43:26');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL DEFAULT current_timestamp(),
  `end_time` datetime NOT NULL DEFAULT current_timestamp(),
  `destination` varchar(255) NOT NULL,
  `pick_up` varchar(255) NOT NULL,
  `truck_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `helper_id` int(11) DEFAULT NULL,
  `distance_km` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `start_time`, `end_time`, `destination`, `pick_up`, `truck_id`, `customer_id`, `driver_id`, `helper_id`, `distance_km`) VALUES
(1, '2025-04-24 06:00:00', '2025-04-24 18:00:00', 'sample', 'sample', 2, 1, 2, 2, 60.00),
(2, '2025-04-24 06:00:00', '2025-04-24 18:00:00', 'last sample', 'last sample', 3, 1, 3, 3, 70.00),
(3, '2025-04-24 06:00:00', '2025-04-24 18:00:00', 'eto', 'eto', 5, 1, 5, 5, 60.00),
(4, '2025-04-24 06:00:00', '2025-04-24 18:00:00', 'test', 'test', 3, 1, 3, 3, 60.00),
(5, '2025-04-24 06:00:00', '2025-04-24 18:00:00', '132', '123', 3, 1, 3, 3, 32.00),
(6, '2025-04-24 06:00:00', '2025-04-24 18:00:00', 'sample destination', 'sample pick up location', 1, 17, 1, 1, 70.00),
(7, '2025-05-01 08:00:00', '2025-05-01 12:00:00', 'Quezon City, Metro Manila', 'Malolos, Bulacan', 1, 1, 1, 1, 50.00),
(8, '2025-05-02 09:00:00', '2025-05-02 13:00:00', 'Angeles City, Pampanga', 'Meycauayan, Bulacan', 1, 1, 1, 1, 45.00),
(9, '2025-05-03 07:00:00', '2025-05-03 15:00:00', 'Baguio City, Benguet', 'San Jose del Monte, Bulacan', 1, 1, 1, 1, 85.00),
(10, '2025-05-04 10:00:00', '2025-05-04 14:00:00', 'Tarlac City, Tarlac', 'Baliuag, Bulacan', 1, 1, 1, 1, 60.00),
(11, '2025-05-05 08:00:00', '2025-05-05 11:00:00', 'Valenzuela City, Metro Manila', 'Guiguinto, Bulacan', 1, 1, 1, 1, 25.00),
(12, '2025-05-01 07:00:00', '2025-05-01 16:00:00', 'Legazpi City, Albay', 'Plaridel, Bulacan', 2, 1, 2, 2, 120.00),
(13, '2025-05-02 08:00:00', '2025-05-02 12:00:00', 'Cabanatuan City, Nueva Ecija', 'Pulilan, Bulacan', 2, 1, 2, 2, 55.00),
(14, '2025-05-03 09:00:00', '2025-05-03 13:00:00', 'Olongapo City, Zambales', 'Marilao, Bulacan', 2, 1, 2, 2, 65.00),
(15, '2025-05-04 06:00:00', '2025-05-04 10:00:00', 'Manila City, Metro Manila', 'Bocaue, Bulacan', 2, 1, 2, 2, 35.00),
(16, '2025-05-05 07:00:00', '2025-05-05 17:00:00', 'Vigan City, Ilocos Sur', 'San Miguel, Bulacan', 2, 1, 2, 2, 150.00),
(17, '2025-05-01 08:00:00', '2025-05-01 12:00:00', 'San Fernando, Pampanga', 'Balagtas, Bulacan', 3, 1, 3, 3, 40.00),
(18, '2025-05-02 09:00:00', '2025-05-02 14:00:00', 'Dagupan City, Pangasinan', 'Santa Maria, Bulacan', 3, 1, 3, 3, 75.00),
(19, '2025-05-03 07:00:00', '2025-05-03 11:00:00', 'Malolos, Bulacan', 'Norzagaray, Bulacan', 3, 1, 3, 3, 20.00),
(20, '2025-05-04 06:00:00', '2025-05-04 18:00:00', 'Naga City, Camarines Sur', 'San Rafael, Bulacan', 3, 1, 3, 3, 140.00),
(21, '2025-05-05 08:00:00', '2025-05-05 12:00:00', 'Calamba City, Laguna', 'Hagonoy, Bulacan', 3, 1, 3, 3, 50.00),
(22, '2025-05-01 07:00:00', '2025-05-01 11:00:00', 'Antipolo City, Rizal', 'San Ildefonso, Bulacan', 4, 1, 4, 4, 45.00),
(23, '2025-05-02 08:00:00', '2025-05-02 16:00:00', 'Laoag City, Ilocos Norte', 'Doña Remedios Trinidad, Bulacan', 4, 1, 4, 4, 160.00),
(24, '2025-05-03 09:00:00', '2025-05-03 13:00:00', 'San Pablo City, Laguna', 'Obando, Bulacan', 4, 1, 4, 4, 55.00),
(25, '2025-05-04 06:00:00', '2025-05-04 10:00:00', 'Mabalacat City, Pampanga', 'Paombong, Bulacan', 4, 1, 4, 4, 40.00),
(26, '2025-05-05 08:00:00', '2025-05-05 12:00:00', 'Batangas City, Batangas', 'Calumpit, Bulacan', 4, 1, 4, 4, 70.00),
(27, '2025-05-01 08:00:00', '2025-05-01 12:00:00', 'Tuguegarao City, Cagayan', 'Angat, Bulacan', 5, 1, 5, 5, 200.00),
(28, '2025-05-02 09:00:00', '2025-05-02 13:00:00', 'Balanga City, Bataan', 'Pandi, Bulacan', 5, 1, 5, 5, 35.00),
(29, '2025-05-03 07:00:00', '2025-05-03 11:00:00', 'San Jose City, Nueva Ecija', 'Bulakan, Bulacan', 5, 1, 5, 5, 50.00),
(30, '2025-05-04 06:00:00', '2025-05-04 10:00:00', 'Biñan City, Laguna', 'Bustos, Bulacan', 5, 1, 5, 5, 45.00),
(32, '2025-05-05 06:00:00', '2025-05-05 18:00:00', 'sample', 'sample', 1, 1, 1, 1, 50.00),
(33, '2025-05-24 06:00:00', '2025-05-24 18:00:00', 'doon', 'dito ', 1, 1, 1, 1, 50.00),
(34, '2025-05-24 06:00:00', '2025-05-24 18:00:00', 'doon', 'dito ', 2, 1, 2, 2, 56.00),
(35, '2025-05-24 06:00:00', '2025-05-24 18:00:00', 'saan', 'dito ', 3, 1, 3, 3, 40.00),
(36, '2025-06-07 06:00:00', '2025-06-07 18:00:00', 'fafafafaf', 'afafaf', 4, 1, 4, 4, 20.00),
(37, '2025-06-06 06:00:00', '2025-06-06 18:00:00', 'last test', 'test last', 5, 1, 5, 5, 60.00),
(38, '2025-05-28 06:00:00', '2025-05-28 18:00:00', 'book na sana', 'sana ma book', 1, 1, 1, 1, 58.00),
(39, '2025-05-28 06:00:00', '2025-05-28 18:00:00', 'last na sana', 'sana last n', 2, 1, 2, 2, 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `trucks`
--

CREATE TABLE `trucks` (
  `truck_id` int(11) NOT NULL,
  `truck_no` varchar(50) NOT NULL,
  `truck_type` varchar(50) DEFAULT NULL,
  `status` enum('Available','Booked','Maintenance') NOT NULL DEFAULT 'Available',
  `driver_id` int(11) DEFAULT NULL,
  `helper_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trucks`
--

INSERT INTO `trucks` (`truck_id`, `truck_no`, `truck_type`, `status`, `driver_id`, `helper_id`) VALUES
(1, 'TRK-2024-01', '10 wheelers', 'Booked', 1, 1),
(2, 'TRK-2024-02', '12 wheelers', 'Booked', 2, 2),
(3, 'TRK-2024-03', '8 wheelers', 'Available', 3, 3),
(4, 'TRK-2024-04', '10 wheelers', 'Booked', 4, 4),
(5, 'TRK-2024-05', '6 wheelers', 'Booked', 5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer','driver','helper') NOT NULL,
  `account_status` enum('Active','Inactive','Disabled') DEFAULT 'Active',
  `last_activity` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `account_status`, `last_activity`) VALUES
(4, 'admin', '$2y$10$20ETT6CiIF7MZkCQEj71c.g0QPMoyV5Fs.KTF8SgfTEL2zaZfVxhS', 'admin', 'Active', '2025-05-25 01:28:45'),
(11, 'client', '$2y$10$M891800fSQhN/cIvA7bRD.eHdO0vppdBMNu3WDz.17YWi/l9vqcF6', 'customer', 'Active', '2025-05-25 01:28:37'),
(12, 'client2', '$2y$10$M891800fSQhN/cIvA7bRD.eHdO0vppdBMNu3WDz.17YWi/l9vqcF6', 'customer', 'Active', NULL),
(15, 'driver', '$2y$10$VI0UvUzMrExjNSv37E7GXOTzL2caSUCgj4Mt.aS0U1LItLXoaX62C', 'driver', 'Active', '2025-05-24 20:29:33'),
(16, 'driver2', '$2y$10$gQNXpo7Zlu22fJz/nRfobe6y2eISpZdsP5TEfG67uo45gqgkUoSuy', 'driver', 'Active', NULL),
(17, 'test', '$2y$10$3PGkL6yBH5ZMmtnsaSfxVuWiAL5nbT/s8n69WxMB.W.M/TL/gPKNu', 'customer', 'Active', NULL),
(18, 'helper', '$2y$10$5xzVaTuka17WqMCT/au8I.4T023ag4.RAIoi2uErKPq1sxxVVjQQC', 'helper', 'Active', '2025-05-24 21:45:16'),
(19, 'helper2', '$2y$10$5xzVaTuka17WqMCT/au8I.4T023ag4.RAIoi2uErKPq1sxxVVjQQC', 'helper', 'Active', NULL),
(20, 'driver3', '$2y$10$zO0tBCm4jilKwTZQwzZ3yO96qxJEg64BlbLLJ9SsK5/5hbv5JarJi', 'driver', 'Active', NULL),
(21, 'driver4', '$2y$10$Ta.PaS1stoiXA2ddUYosheIp0P5Ugd576JTlkVEIurn4Cm6knD30S', 'driver', 'Active', NULL),
(22, 'driver5', '$2y$10$SLsPFs3oNn/rTCBl.6JOMuxZX/Z.ttkkCLkyO5v7gex6i2yVY9v92', 'driver', 'Active', NULL),
(23, 'helper3', '$2y$10$10Gc5eQzDi/UYdIyXyZ8KORahuWy1K2ThZR9wsN4DZIsmBIrJ7tty', 'helper', 'Active', NULL),
(24, 'helper4', '$2y$10$PU3VqlQh6zXPv9wjGThj3OCjgE8PUXawrbwS/V9y7mKhLcDrODY/.', 'helper', 'Active', NULL),
(25, 'helper5', '$2y$10$ShFaUqVyyMDi37mPX0Oj1ubdzKRELtgs1L5qIkZWspqpJffBycKdi', 'helper', 'Active', NULL),
(41, 'zenra', '$2y$10$Kq9fnlPQ1xmMi1WwIULTUuSrxkbGNz7gjSNEvjGZpL/wpqnp3H3yi', 'customer', 'Active', NULL),
(46, 'rocky211', '$2y$10$ftPFp4hqPV5ZQh9W/iIRZuvMjUqifk8aidxsJ.8DaD/22zJopuRBe', 'customer', 'Active', NULL),
(48, 'owin', '$2y$10$RVpF.foscWj.7emKsXzAMuCgXJhDLpf8/YQRAgJh0wkO4bMjqp2K2', 'customer', 'Active', NULL),
(49, 'sample', '$2y$10$I8lxS/O92DFhvzNK7nSsWuzevjT4Ktgz.CpZIftqVajOWlsbb2aia', 'customer', 'Active', '2025-04-24 06:00:00'),
(60, 'ivy', '$2y$10$vbiyTwYFCeNN1poY/IXV0u8WWMLeCitdRGm0Op2.rGH7udacoEjCW', 'customer', 'Active', NULL),
(62, 'anya', '$2y$10$.WYnkRiNuhxaIUUwi/Mb4uoZJKIViTYTvwQT5bdWQxnPGm0VpFBZG', 'customer', 'Active', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_requests`
--
ALTER TABLE `account_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`driver_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `helpers`
--
ALTER TABLE `helpers`
  ADD PRIMARY KEY (`helper_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `helper_payroll`
--
ALTER TABLE `helper_payroll`
  ADD PRIMARY KEY (`payroll_id`),
  ADD KEY `helper_id` (`helper_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`payroll_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `payroll_settings`
--
ALTER TABLE `payroll_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pay_period` (`pay_period`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `truck_id` (`truck_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `fk_schedule_helper` (`helper_id`);

--
-- Indexes for table `trucks`
--
ALTER TABLE `trucks`
  ADD PRIMARY KEY (`truck_id`),
  ADD KEY `fk_driver_id` (`driver_id`),
  ADD KEY `fk_helper_id` (`helper_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_requests`
--
ALTER TABLE `account_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `helpers`
--
ALTER TABLE `helpers`
  MODIFY `helper_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `helper_payroll`
--
ALTER TABLE `helper_payroll`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `payroll_settings`
--
ALTER TABLE `payroll_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `trucks`
--
ALTER TABLE `trucks`
  MODIFY `truck_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`);

--
-- Constraints for table `drivers`
--
ALTER TABLE `drivers`
  ADD CONSTRAINT `drivers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `helpers`
--
ALTER TABLE `helpers`
  ADD CONSTRAINT `helpers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `helper_payroll`
--
ALTER TABLE `helper_payroll`
  ADD CONSTRAINT `fk_helper_payroll` FOREIGN KEY (`helper_id`) REFERENCES `helpers` (`helper_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`);

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`);

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedule_helper` FOREIGN KEY (`helper_id`) REFERENCES `helpers` (`helper_id`),
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`truck_id`) REFERENCES `trucks` (`truck_id`),
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `schedules_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`);

--
-- Constraints for table `trucks`
--
ALTER TABLE `trucks`
  ADD CONSTRAINT `fk_driver_id` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`),
  ADD CONSTRAINT `fk_helper_id` FOREIGN KEY (`helper_id`) REFERENCES `helpers` (`helper_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
