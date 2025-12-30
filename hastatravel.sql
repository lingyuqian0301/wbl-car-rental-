-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 30, 2025 at 03:50 AM
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
-- Database: `hastatravel`
--

-- --------------------------------------------------------

--
-- Table structure for table `blacklist`
--

CREATE TABLE `blacklist` (
  `blacklistID` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `customerID` int(11) DEFAULT NULL,
  `staffID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `bookingID` int(11) NOT NULL,
  `creationDate` datetime DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `pickup_point` varchar(100) DEFAULT NULL,
  `return_point` varchar(100) DEFAULT NULL,
  `number_of_days` int(11) DEFAULT NULL,
  `addOns_item` varchar(100) DEFAULT NULL,
  `addOns_charge` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `booking_status` varchar(20) DEFAULT NULL,
  `late_return_fees` decimal(10,2) DEFAULT NULL,
  `damage_fee` decimal(10,2) DEFAULT NULL,
  `cancellation_type` varchar(50) DEFAULT NULL,
  `status_update_date_time` datetime DEFAULT NULL,
  `duration_days` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `customerID` int(11) DEFAULT NULL,
  `vehicleID` int(11) DEFAULT NULL,
  `staffID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`bookingID`, `creationDate`, `start_date`, `end_date`, `pickup_point`, `return_point`, `number_of_days`, `addOns_item`, `addOns_charge`, `total_amount`, `booking_status`, `late_return_fees`, `damage_fee`, `cancellation_type`, `status_update_date_time`, `duration_days`, `updated_at`, `created_at`, `customerID`, `vehicleID`, `staffID`) VALUES
(4, NULL, '2025-12-29', '2025-12-30', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, 1, '2025-12-29 01:26:13', '2025-12-29 01:26:13', NULL, 3, NULL),
(5, NULL, '2025-12-31', '2026-01-01', NULL, NULL, NULL, NULL, NULL, 244.30, 'pending', NULL, NULL, NULL, NULL, 1, '2025-12-29 08:00:58', '2025-12-29 08:00:58', 3, 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bookingconditionreport`
--

CREATE TABLE `bookingconditionreport` (
  `reportID` int(11) NOT NULL,
  `report_type` varchar(20) DEFAULT NULL,
  `condition_image` text DEFAULT NULL,
  `odometer_reading` int(11) DEFAULT NULL,
  `fuel_level` varchar(20) DEFAULT NULL,
  `scratches_notes` text DEFAULT NULL,
  `reported_date_time` datetime DEFAULT NULL,
  `bookingID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `duration_days` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled','Completed') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `vehicleID` int(11) NOT NULL,
  `vehicle_number` varchar(50) DEFAULT NULL,
  `vehicle_model` varchar(50) DEFAULT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `plate_number` varchar(20) DEFAULT NULL,
  `seating_capacity` int(11) DEFAULT NULL,
  `transmission` varchar(20) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `rental_price` decimal(10,2) DEFAULT NULL,
  `availability_status` varchar(20) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `car_browse_image` varchar(255) DEFAULT NULL,
  `isActive` varchar(5) DEFAULT NULL,
  `vehicle_brand` varchar(30) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `ownerID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`vehicleID`, `vehicle_number`, `vehicle_model`, `vehicle_type`, `plate_number`, `seating_capacity`, `transmission`, `color`, `rental_price`, `availability_status`, `created_date`, `car_browse_image`, `isActive`, `vehicle_brand`, `status`, `ownerID`) VALUES
(2, '1', 'Alza', 'Hatchback', '12nsk', 3, 'auto', 'Red', 234.30, 'available', '2025-12-09', '', 'true', 'Perodua', NULL, 1),
(3, '2', 'CR-V', 'Compact', 'fnfn45', 4, 'auto', 'Black', 66.40, 'available', NULL, NULL, 'true', 'Honda', NULL, 1),
(4, '3', 'CIVIC', 'Hatchback', 'hhh888', 4, 'auto', 'Black', 234.30, 'available', '2025-12-01', NULL, 'true', 'Honda', NULL, 1),
(5, '4', 'Axia', 'Hatchback', '12wd4', 4, 'auto', 'White', 234.30, 'available', '2025-12-01', NULL, 'true', 'Perodua', NULL, 1),
(6, '5', 'Vios', 'Sedan', 'ABC1234', 5, 'auto', 'Silver', 260.00, 'available', '2025-12-10', NULL, '1', 'Toyota', NULL, 1),
(7, '6', 'Corolla Cross', 'SUV', 'DEF5678', 5, 'auto', 'White', 320.00, 'available', '2025-12-10', NULL, '1', 'Toyota', NULL, 1),
(8, '7', 'Saga', 'Sedan', 'GHI9012', 5, 'manual', 'Red', 180.00, 'available', '2025-12-10', NULL, '1', 'Proton', NULL, 1),
(9, '8', 'X50', 'SUV', 'JKL3456', 5, 'auto', 'Blue', 350.00, 'available', '2025-12-10', NULL, '1', 'Proton', NULL, 1),
(10, '9', 'Myvi', 'Hatchback', 'MNO7890', 5, 'auto', 'Grey', 220.00, 'available', '2025-12-10', NULL, '1', 'Perodua', NULL, 1),
(11, '10', 'Ativa', 'SUV', 'PQR1122', 5, 'auto', 'Green', 300.00, 'available', '2025-12-10', NULL, '1', 'Perodua', NULL, 1),
(12, '11', 'City', 'Sedan', 'STU3344', 5, 'auto', 'Black', 280.00, 'available', '2025-12-10', NULL, '1', 'Honda', NULL, 1),
(13, '12', 'HR-V', 'SUV', 'VWX5566', 5, 'auto', 'White', 340.00, 'available', '2025-12-10', NULL, '1', 'Honda', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customerID` int(11) NOT NULL,
  `matric_number` varchar(20) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `ic_number` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `college` varchar(100) DEFAULT NULL,
  `faculty` varchar(100) DEFAULT NULL,
  `customer_type` varchar(20) DEFAULT NULL,
  `registration_date` date DEFAULT NULL,
  `emergency_contact` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `customer_license` varchar(50) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customerID`, `matric_number`, `fullname`, `ic_number`, `phone`, `email`, `college`, `faculty`, `customer_type`, `registration_date`, `emergency_contact`, `country`, `customer_license`, `user_id`) VALUES
(1, NULL, 'aa', NULL, NULL, 'aa@gmail.com', NULL, NULL, 'regular', '2025-12-29', NULL, NULL, NULL, 4),
(2, NULL, 'heh', NULL, NULL, 'ww@gmail.com', NULL, NULL, 'regular', '2025-12-29', NULL, NULL, NULL, 5),
(3, NULL, 'hh', NULL, NULL, 'nn@gmail.com', NULL, NULL, 'regular', '2025-12-29', NULL, NULL, NULL, 6),
(4, NULL, 'hafiz', NULL, NULL, 'mm@gmail.com', NULL, NULL, 'regular', '2025-12-30', NULL, NULL, NULL, 7);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `invoiceID` int(11) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `totalAmount` decimal(10,2) DEFAULT NULL,
  `bookingID` int(11) DEFAULT NULL,
  `staffID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_categories`
--

CREATE TABLE `item_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyaltycard`
--

CREATE TABLE `loyaltycard` (
  `loyaltyCardID` int(11) NOT NULL,
  `total_stamps` int(11) DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  `customerID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_01_15_000000_create_item_categories_table', 2),
(5, '2025_12_07_112910_create_vehicles_table', 3),
(6, '2025_12_07_112911_create_bookings_table', 3),
(7, '2025_12_07_112920_create_payments_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notificationID` int(11) NOT NULL,
  `recipientType` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `sent_date` datetime DEFAULT NULL,
  `customerID` int(11) DEFAULT NULL,
  `staffID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ownercar`
--

CREATE TABLE `ownercar` (
  `ownerID` int(11) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `ic_number` varchar(20) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `bankname` varchar(50) DEFAULT NULL,
  `bank_acc_number` varchar(50) DEFAULT NULL,
  `registration_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ownercar`
--

INSERT INTO `ownercar` (`ownerID`, `fullname`, `ic_number`, `contact_number`, `email`, `bankname`, `bank_acc_number`, `registration_date`) VALUES
(1, 'hafiz', '837569', '238572', 'hafiz@gmail.com', 'yeehaw', '6789098765', '2025-12-10');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `paymentID` int(11) NOT NULL,
  `payment_purpose` varchar(50) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `receiptURL` text DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `refund_date` date DEFAULT NULL,
  `deposit_bank_name` varchar(50) DEFAULT NULL,
  `deposit_bank_number` varchar(50) DEFAULT NULL,
  `deposit_returned` tinyint(1) DEFAULT NULL,
  `isPayment_complete` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bookingID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_type` enum('Deposit','Full Payment','Balance') NOT NULL,
  `payment_method` enum('Bank Transfer','Cash') NOT NULL,
  `proof_of_payment` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Verified','Rejected') NOT NULL DEFAULT 'Pending',
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejected_reason` text DEFAULT NULL,
  `payment_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paymentverification`
--

CREATE TABLE `paymentverification` (
  `verificationID` int(11) NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `verification_date_time` datetime DEFAULT NULL,
  `paymentID` int(11) DEFAULT NULL,
  `staffID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `reviewID` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `review_date` date DEFAULT NULL,
  `customerID` int(11) DEFAULT NULL,
  `vehicleID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('xIjvxGqvahE7FdnveYTXdrVBbmkvWYJQTIkr1Y9O', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTTVZRmhMSGhmYWtwVnBrQTJxeU8wNFNnQk1Ub0NyeFFuamN4ajhJVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3RlciI7czo1OiJyb3V0ZSI7czo4OiJyZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6MzI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC92ZWhpY2xlcy8zIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NTt9', 1767013993),
('ZYqEhCoHuu3kzI1l3oghSlqic9xYn2Q1F3ADIVwg', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMFU5SHVxSDBZaWZmWkxHTUZsTkdvZ0pYMEllVU5ab2c3dzkzZHpHTCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3RlciI7czo1OiJyb3V0ZSI7czo4OiJyZWdpc3RlciI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ7fQ==', 1767003063);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staffID` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `permissions` text DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `systemlog`
--

CREATE TABLE `systemlog` (
  `logID` int(11) NOT NULL,
  `userType` varchar(20) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `customerID` int(11) DEFAULT NULL,
  `staffID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`) VALUES
(1, 'hafiz', 'mhafizreepei05@gmail.com', NULL, '$2y$12$zJulsrYXAFMgOlH6atj75eyqVNnXfPt7AWRBuaIOK9E8sJ1ZZg/g6', NULL, '2025-12-28 19:46:22', '2025-12-28 19:46:22', 'customer'),
(2, 'MMM', 'mmm@gmail.com', NULL, '$2y$12$MTzVZKmVYcbONK2d6RRLreaV08q5GVX9ic07pMugmxsmfbpbRk2D6', NULL, '2025-12-29 01:38:14', '2025-12-29 01:38:14', 'customer'),
(3, 'zz', 'zz@gmail.com', NULL, '$2y$12$Eti2hsKXZ6rMa/cNRekDA.Rw510Z7XE3OvuLh87YPE4aOI9UqRTXS', NULL, '2025-12-29 01:49:38', '2025-12-29 01:49:38', 'customer'),
(4, 'aa', 'aa@gmail.com', NULL, '$2y$12$a7aainaeXMcU0wg6torW.uJNwWDjYCgO0TXzX8p3MHUaB28RRPd.S', NULL, '2025-12-29 01:57:40', '2025-12-29 01:57:40', 'customer'),
(5, 'heh', 'ww@gmail.com', NULL, '$2y$12$vAxOOa5UljpyawpW.rDJKepJ4M8UrxIO2gQ0B6hs1bmlwNRBMktXK', NULL, '2025-12-29 05:08:04', '2025-12-29 05:08:04', 'customer'),
(6, 'hh', 'nn@gmail.com', NULL, '$2y$12$96YVz.FzEvs9rC0aJRi0s.LEvo16TbJP/SaVdHI4xyorrhRVUn4jm', NULL, '2025-12-29 05:18:44', '2025-12-29 05:18:44', 'customer'),
(7, 'hafiz', 'mm@gmail.com', NULL, '$2y$12$1yc/CmENV96P3sJPDjJJGOuIMgXx2ZNT8TPrV1rNoo.TRoXXIsz2.', NULL, '2025-12-29 17:48:33', '2025-12-29 17:48:33', 'admin'),
(8, 'Admin User', 'admin@hasta.com', NULL, '$2y$12$yQwsy/P6s5P5rXCLc9Ug3OWceVB5v2lAIwOSdhwQHwVIAJK1M7K9m', NULL, '2025-12-29 18:36:12', '2025-12-29 18:36:12', NULL),
(9, 'Customer User', 'customer@hasta.com', NULL, '$2y$12$6msOzTgLhc/NK/VTuovV3.5fj1gxhqPOPXzGdcjQjyBImAa6eP1tK', NULL, '2025-12-29 18:36:13', '2025-12-29 18:36:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vehicledocument`
--

CREATE TABLE `vehicledocument` (
  `documentID` int(11) NOT NULL,
  `document_type` varchar(50) DEFAULT NULL,
  `upload_date` date DEFAULT NULL,
  `verification_date` date DEFAULT NULL,
  `fileurl` text DEFAULT NULL,
  `policyNo` varchar(50) DEFAULT NULL,
  `insurance_company` varchar(100) DEFAULT NULL,
  `insurance_expirydate` date DEFAULT NULL,
  `roadtax_no` varchar(50) DEFAULT NULL,
  `roadtax_expirydate` date DEFAULT NULL,
  `front_image` text DEFAULT NULL,
  `side_image` text DEFAULT NULL,
  `interior_image` text DEFAULT NULL,
  `vehicleID` int(11) DEFAULT NULL,
  `staffID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehiclemaintenance`
--

CREATE TABLE `vehiclemaintenance` (
  `maintenanceID` int(11) NOT NULL,
  `mileage` int(11) DEFAULT NULL,
  `service_date` date DEFAULT NULL,
  `service_type` varchar(50) DEFAULT NULL,
  `next_due_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `vehicleID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `brand` varchar(255) NOT NULL,
  `model` varchar(255) NOT NULL,
  `registration_number` varchar(255) NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `status` enum('Available','Rented','Maintenance') NOT NULL DEFAULT 'Available',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `voucher`
--

CREATE TABLE `voucher` (
  `voucherID` int(11) NOT NULL,
  `discount_type` varchar(50) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `loyaltyCardID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `walletaccount`
--

CREATE TABLE `walletaccount` (
  `walletAccountID` int(11) NOT NULL,
  `virtual_balance` decimal(10,2) DEFAULT NULL,
  `hold_amount` decimal(10,2) DEFAULT NULL,
  `available_balance` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `customerID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallettransaction`
--

CREATE TABLE `wallettransaction` (
  `transactionID` int(11) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `transaction_type` varchar(50) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `walletAccountID` int(11) DEFAULT NULL,
  `paymentID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blacklist`
--
ALTER TABLE `blacklist`
  ADD PRIMARY KEY (`blacklistID`),
  ADD KEY `customerID` (`customerID`),
  ADD KEY `staffID` (`staffID`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`bookingID`),
  ADD KEY `customerID` (`customerID`),
  ADD KEY `vehicleID` (`vehicleID`),
  ADD KEY `staffID` (`staffID`);

--
-- Indexes for table `bookingconditionreport`
--
ALTER TABLE `bookingconditionreport`
  ADD PRIMARY KEY (`reportID`),
  ADD KEY `bookingID` (`bookingID`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_user_id_foreign` (`user_id`),
  ADD KEY `bookings_vehicle_id_foreign` (`vehicle_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`vehicleID`),
  ADD KEY `ownerID` (`ownerID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customerID`),
  ADD KEY `customer_user_fk` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`invoiceID`),
  ADD UNIQUE KEY `bookingID` (`bookingID`),
  ADD KEY `staffID` (`staffID`);

--
-- Indexes for table `item_categories`
--
ALTER TABLE `item_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_categories_name_unique` (`name`),
  ADD UNIQUE KEY `item_categories_slug_unique` (`slug`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loyaltycard`
--
ALTER TABLE `loyaltycard`
  ADD PRIMARY KEY (`loyaltyCardID`),
  ADD UNIQUE KEY `customerID` (`customerID`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notificationID`),
  ADD KEY `customerID` (`customerID`),
  ADD KEY `staffID` (`staffID`);

--
-- Indexes for table `ownercar`
--
ALTER TABLE `ownercar`
  ADD PRIMARY KEY (`ownerID`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `bookingID` (`bookingID`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_booking_id_foreign` (`booking_id`),
  ADD KEY `payments_verified_by_foreign` (`verified_by`);

--
-- Indexes for table `paymentverification`
--
ALTER TABLE `paymentverification`
  ADD PRIMARY KEY (`verificationID`),
  ADD KEY `paymentID` (`paymentID`),
  ADD KEY `staffID` (`staffID`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`reviewID`),
  ADD KEY `customerID` (`customerID`),
  ADD KEY `vehicleID` (`vehicleID`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staffID`);

--
-- Indexes for table `systemlog`
--
ALTER TABLE `systemlog`
  ADD PRIMARY KEY (`logID`),
  ADD KEY `customerID` (`customerID`),
  ADD KEY `staffID` (`staffID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `vehicledocument`
--
ALTER TABLE `vehicledocument`
  ADD PRIMARY KEY (`documentID`),
  ADD KEY `vehicleID` (`vehicleID`),
  ADD KEY `staffID` (`staffID`);

--
-- Indexes for table `vehiclemaintenance`
--
ALTER TABLE `vehiclemaintenance`
  ADD PRIMARY KEY (`maintenanceID`),
  ADD KEY `vehicleID` (`vehicleID`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vehicles_registration_number_unique` (`registration_number`);

--
-- Indexes for table `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`voucherID`),
  ADD UNIQUE KEY `loyaltyCardID` (`loyaltyCardID`);

--
-- Indexes for table `walletaccount`
--
ALTER TABLE `walletaccount`
  ADD PRIMARY KEY (`walletAccountID`),
  ADD UNIQUE KEY `customerID` (`customerID`);

--
-- Indexes for table `wallettransaction`
--
ALTER TABLE `wallettransaction`
  ADD PRIMARY KEY (`transactionID`),
  ADD KEY `walletAccountID` (`walletAccountID`),
  ADD KEY `paymentID` (`paymentID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blacklist`
--
ALTER TABLE `blacklist`
  MODIFY `blacklistID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `bookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bookingconditionreport`
--
ALTER TABLE `bookingconditionreport`
  MODIFY `reportID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `vehicleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `invoiceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_categories`
--
ALTER TABLE `item_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyaltycard`
--
ALTER TABLE `loyaltycard`
  MODIFY `loyaltyCardID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notificationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ownercar`
--
ALTER TABLE `ownercar`
  MODIFY `ownerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paymentverification`
--
ALTER TABLE `paymentverification`
  MODIFY `verificationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `reviewID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staffID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `systemlog`
--
ALTER TABLE `systemlog`
  MODIFY `logID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `vehicledocument`
--
ALTER TABLE `vehicledocument`
  MODIFY `documentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehiclemaintenance`
--
ALTER TABLE `vehiclemaintenance`
  MODIFY `maintenanceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voucher`
--
ALTER TABLE `voucher`
  MODIFY `voucherID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `walletaccount`
--
ALTER TABLE `walletaccount`
  MODIFY `walletAccountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallettransaction`
--
ALTER TABLE `wallettransaction`
  MODIFY `transactionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blacklist`
--
ALTER TABLE `blacklist`
  ADD CONSTRAINT `blacklist_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customer` (`customerID`),
  ADD CONSTRAINT `blacklist_ibfk_2` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`);

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customer` (`customerID`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`vehicleID`) REFERENCES `cars` (`vehicleID`),
  ADD CONSTRAINT `booking_ibfk_3` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`);

--
-- Constraints for table `bookingconditionreport`
--
ALTER TABLE `bookingconditionreport`
  ADD CONSTRAINT `bookingconditionreport_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `cars_ibfk_1` FOREIGN KEY (`ownerID`) REFERENCES `ownercar` (`ownerID`);

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`),
  ADD CONSTRAINT `invoice_ibfk_2` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`);

--
-- Constraints for table `loyaltycard`
--
ALTER TABLE `loyaltycard`
  ADD CONSTRAINT `loyaltycard_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customer` (`customerID`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customer` (`customerID`),
  ADD CONSTRAINT `notification_ibfk_2` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `paymentverification`
--
ALTER TABLE `paymentverification`
  ADD CONSTRAINT `paymentverification_ibfk_1` FOREIGN KEY (`paymentID`) REFERENCES `payment` (`paymentID`),
  ADD CONSTRAINT `paymentverification_ibfk_2` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`);

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customer` (`customerID`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`vehicleID`) REFERENCES `cars` (`vehicleID`);

--
-- Constraints for table `systemlog`
--
ALTER TABLE `systemlog`
  ADD CONSTRAINT `systemlog_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customer` (`customerID`),
  ADD CONSTRAINT `systemlog_ibfk_2` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`);

--
-- Constraints for table `vehicledocument`
--
ALTER TABLE `vehicledocument`
  ADD CONSTRAINT `vehicledocument_ibfk_1` FOREIGN KEY (`vehicleID`) REFERENCES `cars` (`vehicleID`),
  ADD CONSTRAINT `vehicledocument_ibfk_2` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`);

--
-- Constraints for table `vehiclemaintenance`
--
ALTER TABLE `vehiclemaintenance`
  ADD CONSTRAINT `vehiclemaintenance_ibfk_1` FOREIGN KEY (`vehicleID`) REFERENCES `cars` (`vehicleID`);

--
-- Constraints for table `voucher`
--
ALTER TABLE `voucher`
  ADD CONSTRAINT `voucher_ibfk_1` FOREIGN KEY (`loyaltyCardID`) REFERENCES `loyaltycard` (`loyaltyCardID`);

--
-- Constraints for table `walletaccount`
--
ALTER TABLE `walletaccount`
  ADD CONSTRAINT `walletaccount_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customer` (`customerID`);

--
-- Constraints for table `wallettransaction`
--
ALTER TABLE `wallettransaction`
  ADD CONSTRAINT `wallettransaction_ibfk_1` FOREIGN KEY (`walletAccountID`) REFERENCES `walletaccount` (`walletAccountID`),
  ADD CONSTRAINT `wallettransaction_ibfk_2` FOREIGN KEY (`paymentID`) REFERENCES `payment` (`paymentID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
