-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2025 at 12:59 PM
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
-- Database: `borrowing_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password_hash`, `email`, `full_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@system.com', 'System Administrator', '2025-09-21 08:28:32', '2025-10-12 10:58:03');

-- --------------------------------------------------------

--
-- Table structure for table `approvals`
--

CREATE TABLE `approvals` (
  `approval_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `approval_type` enum('issued_by','assessed_received_by','noted_by') NOT NULL,
  `admin_id` int(11) NOT NULL,
  `approval_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approvals`
--

INSERT INTO `approvals` (`approval_id`, `transaction_id`, `approval_type`, `admin_id`, `approval_date`, `remarks`, `created_at`) VALUES
(26, 13, 'issued_by', 1, '2025-10-03 01:55:49', NULL, '2025-10-03 01:55:49'),
(27, 13, 'assessed_received_by', 1, '2025-10-03 01:55:50', NULL, '2025-10-03 01:55:50'),
(28, 13, 'noted_by', 1, '2025-10-03 01:58:56', NULL, '2025-10-03 01:58:56'),
(29, 12, 'issued_by', 1, '2025-10-03 01:58:58', NULL, '2025-10-03 01:58:58'),
(30, 12, 'assessed_received_by', 1, '2025-10-03 01:59:00', NULL, '2025-10-03 01:59:00'),
(31, 12, 'noted_by', 1, '2025-10-03 01:59:10', NULL, '2025-10-03 01:59:10'),
(32, 16, 'issued_by', 1, '2025-10-08 12:13:36', NULL, '2025-10-08 12:13:36'),
(33, 16, 'assessed_received_by', 1, '2025-10-08 12:13:39', NULL, '2025-10-08 12:13:39'),
(34, 16, 'noted_by', 1, '2025-10-08 12:13:46', NULL, '2025-10-08 12:13:46'),
(35, 15, 'noted_by', 1, '2025-10-08 14:23:29', NULL, '2025-10-08 14:23:29'),
(36, 14, 'noted_by', 1, '2025-10-08 14:23:37', NULL, '2025-10-08 14:23:37'),
(37, 15, 'issued_by', 1, '2025-10-08 14:23:41', NULL, '2025-10-08 14:23:41'),
(38, 15, 'assessed_received_by', 1, '2025-10-08 14:23:46', NULL, '2025-10-08 14:23:46'),
(39, 17, 'noted_by', 1, '2025-10-08 23:29:54', NULL, '2025-10-08 23:29:54'),
(42, 17, 'issued_by', 1, '2025-10-09 00:56:45', NULL, '2025-10-09 00:56:45'),
(43, 17, 'assessed_received_by', 1, '2025-10-09 00:56:49', NULL, '2025-10-09 00:56:49'),
(44, 14, 'issued_by', 1, '2025-10-09 00:56:52', NULL, '2025-10-09 00:56:52'),
(45, 14, 'assessed_received_by', 1, '2025-10-09 00:56:59', NULL, '2025-10-09 00:56:59'),
(46, 19, 'noted_by', 1, '2025-10-09 00:57:47', NULL, '2025-10-09 00:57:47'),
(47, 19, 'issued_by', 1, '2025-10-09 01:00:41', NULL, '2025-10-09 01:00:41'),
(51, 19, 'assessed_received_by', 1, '2025-10-09 01:03:52', NULL, '2025-10-09 01:03:52');

-- --------------------------------------------------------

--
-- Table structure for table `borrowed_items`
--

CREATE TABLE `borrowed_items` (
  `borrowed_item_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_required` int(11) NOT NULL,
  `quantity_issued` int(11) NOT NULL DEFAULT 0,
  `quantity_returned` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowed_items`
--

INSERT INTO `borrowed_items` (`borrowed_item_id`, `transaction_id`, `item_id`, `quantity_required`, `quantity_issued`, `quantity_returned`, `created_at`, `updated_at`) VALUES
(3, 12, 1, 1, 1, 1, '2025-10-03 01:55:09', '2025-10-03 01:59:10'),
(4, 12, 3, 3, 3, 3, '2025-10-03 01:55:09', '2025-10-03 01:59:10'),
(5, 12, 5, 1, 1, 1, '2025-10-03 01:55:09', '2025-10-03 01:59:10'),
(6, 13, 1, 1, 1, 1, '2025-10-03 01:55:31', '2025-10-03 01:58:56'),
(7, 13, 3, 3, 3, 3, '2025-10-03 01:55:31', '2025-10-03 01:58:56'),
(8, 13, 5, 1, 1, 1, '2025-10-03 01:55:31', '2025-10-03 01:58:56'),
(9, 14, 4, 1, 1, 0, '2025-10-03 02:02:07', '2025-10-09 00:56:52'),
(10, 14, 5, 4, 4, 0, '2025-10-03 02:02:07', '2025-10-09 00:56:52'),
(11, 14, 1, 12, 12, 0, '2025-10-03 02:02:07', '2025-10-09 00:56:52'),
(12, 15, 4, 1, 1, 1, '2025-10-03 02:06:38', '2025-10-08 14:23:46'),
(13, 15, 5, 4, 4, 4, '2025-10-03 02:06:38', '2025-10-08 14:23:46'),
(14, 15, 1, 12, 12, 12, '2025-10-03 02:06:38', '2025-10-08 14:23:46'),
(15, 16, 3, 8, 8, 8, '2025-10-08 12:13:25', '2025-10-08 12:13:46'),
(16, 16, 1, 4, 4, 4, '2025-10-08 12:13:25', '2025-10-08 12:13:46'),
(17, 16, 5, 3, 3, 3, '2025-10-08 12:13:25', '2025-10-08 12:13:46'),
(18, 17, 2, 7, 7, 0, '2025-10-08 23:29:40', '2025-10-09 00:56:45'),
(19, 19, 2, 3, 3, 3, '2025-10-09 00:57:44', '2025-10-09 01:03:52');

--
-- Triggers `borrowed_items`
--
DELIMITER $$
CREATE TRIGGER `update_item_quantity_on_borrow` AFTER INSERT ON `borrowed_items` FOR EACH ROW BEGIN
    UPDATE items
    SET available_quantity = available_quantity - NEW.quantity_issued
    WHERE item_id = NEW.item_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_item_quantity_on_return` AFTER UPDATE ON `borrowed_items` FOR EACH ROW BEGIN
    IF NEW.quantity_returned > OLD.quantity_returned THEN
        UPDATE items
        SET available_quantity = available_quantity + (NEW.quantity_returned - OLD.quantity_returned)
        WHERE item_id = NEW.item_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `borrowers`
--

CREATE TABLE `borrowers` (
  `borrower_id` int(11) NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `department_course_office` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email_address` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowers`
--

INSERT INTO `borrowers` (`borrower_id`, `id_number`, `full_name`, `department_course_office`, `contact_number`, `email_address`, `created_at`, `updated_at`) VALUES
(1, 'ajhds', 'kdgakgdks', 'djak', '09674185880', 'aclo@gmail.com', '2025-09-21 09:35:37', '2025-09-21 09:35:37'),
(2, '2023300076', 'angel', 'mb', '0970970807', 'yehe@gmauk', '2025-10-08 23:25:52', '2025-10-08 23:26:16');

-- --------------------------------------------------------

--
-- Table structure for table `borrowing_transactions`
--

CREATE TABLE `borrowing_transactions` (
  `transaction_id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `activity_purpose` varchar(255) NOT NULL,
  `place_of_activity` varchar(100) NOT NULL,
  `date_requested` date NOT NULL,
  `date_needed` date NOT NULL,
  `date_of_return` date NOT NULL,
  `status` enum('pending','approved','issued','returned','overdue','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowing_transactions`
--

INSERT INTO `borrowing_transactions` (`transaction_id`, `borrower_id`, `activity_purpose`, `place_of_activity`, `date_requested`, `date_needed`, `date_of_return`, `status`, `created_at`, `updated_at`) VALUES
(12, 1, 'basta ', 'dagat', '2025-10-03', '2025-10-04', '2025-10-06', 'returned', '2025-10-03 01:55:09', '2025-10-03 01:59:10'),
(13, 1, 'basta ', 'dagat', '2025-10-03', '2025-10-04', '2025-10-06', 'returned', '2025-10-03 01:55:30', '2025-10-03 01:58:56'),
(14, 1, 'daagt ', 'dagat', '2025-10-04', '2025-10-06', '2025-10-16', 'returned', '2025-10-03 02:02:07', '2025-10-09 00:56:59'),
(15, 1, 'daagt ', 'dagat', '2025-10-04', '2025-10-06', '2025-10-16', 'returned', '2025-10-03 02:06:38', '2025-10-08 14:23:46'),
(16, 1, 'basta ', 'fkjdbsk', '2025-10-08', '2025-10-09', '2025-10-11', 'returned', '2025-10-08 12:13:25', '2025-10-08 12:13:46'),
(17, 2, 'basta ', 'panaon', '2025-10-11', '2025-10-13', '2025-10-15', 'returned', '2025-10-08 23:29:40', '2025-10-09 00:56:49'),
(19, 2, 'daagt ', 'dagat', '2025-10-10', '2025-10-13', '2025-10-15', 'returned', '2025-10-09 00:57:44', '2025-10-09 01:03:52');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`, `updated_at`) VALUES
(1, 'Field and Sampling Equipment', '2025-10-12 10:31:14', '2025-10-12 10:31:14'),
(2, 'Measuring and Testing', '2025-09-21 08:28:32', '2025-10-12 10:24:17'),
(3, 'Microscopes and Optical', '2025-09-21 08:28:32', '2025-10-12 10:24:55'),
(4, 'Heating and Supports', '2025-09-21 08:28:32', '2025-10-12 10:25:33'),
(10, 'Laboratory Glassware', '2025-09-21 08:28:32', '2025-10-12 10:34:58'),
(100, 'Diving Equipment', '2025-10-12 10:26:19', '2025-10-12 10:31:10'),
(101, 'Laboratory Furniture and Fixtures', '2025-10-12 10:39:12', '2025-10-12 10:40:14'),
(102, 'Safety and Support Equipment', '2025-10-12 10:40:32', '2025-10-12 10:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_description` text DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `total_quantity` int(11) NOT NULL DEFAULT 0,
  `available_quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_code`, `item_name`, `item_description`, `category_id`, `total_quantity`, `available_quantity`, `unit`, `created_at`, `updated_at`) VALUES
(1, 'Beaker001', 'Beakers(1000ml)', 'Cleaning', 10, 8, 4, 'pieces', '2025-09-21 08:28:32', '2025-10-12 10:45:31'),
(2, 'Beaker002', 'Beakers(500ml)', 'Cleaning', 10, 5, 0, 'pieces', '2025-09-21 08:28:32', '2025-10-12 10:47:13'),
(3, 'Beaker003', 'Beakers(400ml)', 'Cleaning', 10, 6, 23, 'pieces', '2025-09-21 08:28:32', '2025-10-12 10:48:26'),
(4, 'MARKER001', 'Whiteboard Markers', 'Set of colored whiteboard markers', 3, 50, 49, 'sets', '2025-09-21 08:28:32', '2025-10-09 00:56:52'),
(5, 'Diving knives005', 'Diving knives', 'Cleaning/Checking/Testing', 100, 2, 83, 'pcs', '2025-09-21 09:36:30', '2025-10-12 10:52:30'),
(6, 'Multimeter006', 'Multimeter', 'Cleaning/Checking/Testing', 2, 1, 0, 'pieces', '2025-10-12 10:54:26', '2025-10-12 10:54:26');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_description`, `created_at`, `updated_at`) VALUES
(1, 'max_items_per_transaction', '5', 'Maximum number of items a borrower can request per transaction', '2025-09-21 08:28:32', '2025-09-21 08:28:32'),
(2, 'max_borrowing_days', '7', 'Maximum number of days items can be borrowed', '2025-09-21 08:28:32', '2025-09-21 08:28:32'),
(3, 'overdue_notice_days', '3', 'Number of days before due date to send notification', '2025-09-21 08:28:32', '2025-09-21 08:28:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `approvals`
--
ALTER TABLE `approvals`
  ADD PRIMARY KEY (`approval_id`),
  ADD UNIQUE KEY `unique_transaction_approval_type` (`transaction_id`,`approval_type`),
  ADD KEY `idx_approvals_transaction` (`transaction_id`),
  ADD KEY `idx_approvals_admin` (`admin_id`);

--
-- Indexes for table `borrowed_items`
--
ALTER TABLE `borrowed_items`
  ADD PRIMARY KEY (`borrowed_item_id`),
  ADD UNIQUE KEY `unique_transaction_item` (`transaction_id`,`item_id`),
  ADD KEY `idx_borrowed_items_transaction` (`transaction_id`),
  ADD KEY `idx_borrowed_items_item` (`item_id`);

--
-- Indexes for table `borrowers`
--
ALTER TABLE `borrowers`
  ADD PRIMARY KEY (`borrower_id`),
  ADD UNIQUE KEY `id_number` (`id_number`),
  ADD UNIQUE KEY `email_address` (`email_address`),
  ADD KEY `idx_borrowers_id_number` (`id_number`),
  ADD KEY `idx_borrowers_email` (`email_address`),
  ADD KEY `idx_borrowers_department` (`department_course_office`);

--
-- Indexes for table `borrowing_transactions`
--
ALTER TABLE `borrowing_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_transactions_borrower` (`borrower_id`),
  ADD KEY `idx_transactions_dates` (`date_requested`,`date_needed`,`date_of_return`),
  ADD KEY `idx_transactions_status` (`status`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD UNIQUE KEY `item_code` (`item_code`),
  ADD KEY `idx_items_category_id` (`category_id`),
  ADD KEY `idx_items_available` (`available_quantity`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `approvals`
--
ALTER TABLE `approvals`
  MODIFY `approval_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `borrowed_items`
--
ALTER TABLE `borrowed_items`
  MODIFY `borrowed_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `borrowers`
--
ALTER TABLE `borrowers`
  MODIFY `borrower_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `borrowing_transactions`
--
ALTER TABLE `borrowing_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `approvals`
--
ALTER TABLE `approvals`
  ADD CONSTRAINT `approvals_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `borrowing_transactions` (`transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `approvals_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `borrowed_items`
--
ALTER TABLE `borrowed_items`
  ADD CONSTRAINT `borrowed_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `borrowing_transactions` (`transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrowed_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `borrowing_transactions`
--
ALTER TABLE `borrowing_transactions`
  ADD CONSTRAINT `borrowing_transactions_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`borrower_id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
