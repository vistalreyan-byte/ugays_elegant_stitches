-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2026 at 02:08 PM
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
-- Database: `tailoring_rental_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `appointment_type` enum('Fitting','Measurement','Pickup','Return','Consultation') NOT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `contact_number`, `email`, `address`, `created_at`) VALUES
(1, 'Maria', 'Santos', '09171234567', 'maria@gmail.com', 'Cebu City', '2026-08-18 13:10:50'),
(2, 'John', 'Dela Cruz', '09281234567', 'john@gmail.com', 'Mandaue City', '2026-08-18 13:10:50'),
(3, 'Angela', 'Garcia', '09391234567', 'angela@gmail.com', 'Lapu-Lapu City', '2026-08-18 13:10:50'),
(4, 'Jovel', 'Ugay', '09456726488', 'jovel@gmail.com', 'abaca', '2026-08-19 11:08:03'),
(5, 'Donita', 'Ugay', '09500216751', 'ugaydonitarose@gmail.com', 'Abaca', '2026-08-19 11:10:01');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `tailoring_id` int(11) DEFAULT NULL,
  `rental_id` int(11) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','GCash','Bank Transfer','Card') DEFAULT 'Cash',
  `payment_status` enum('Paid','Partial','Refunded') DEFAULT 'Paid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rental_items`
--

CREATE TABLE `rental_items` (
  `item_id` int(11) NOT NULL,
  `item_code` varchar(30) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `rental_price` decimal(10,2) NOT NULL,
  `status` enum('Available','Rented','Reserved','Maintenance') DEFAULT 'Available',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rental_items`
--

INSERT INTO `rental_items` (`item_id`, `item_code`, `item_name`, `category`, `size`, `color`, `rental_price`, `status`, `description`) VALUES
(1, 'GWN001', 'Elegant Evening Gown', 'Gown', 'M', 'Red', 1500.00, 'Available', 'Long formal evening gown'),
(2, 'GWN002', 'Debut Gown', 'Gown', 'S', 'Blue', 1800.00, 'Available', 'Formal debut gown'),
(3, 'SUT001', 'Classic Suit', 'Suit', 'L', 'Black', 1200.00, 'Available', 'Black formal suit'),
(4, 'BAR001', 'Formal Barong', 'Barong', 'L', 'Cream', 800.00, 'Available', 'Traditional Filipino barong');

-- --------------------------------------------------------

--
-- Table structure for table `rental_orders`
--

CREATE TABLE `rental_orders` (
  `rental_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `rental_date` date NOT NULL,
  `return_date` date NOT NULL,
  `actual_return_date` date DEFAULT NULL,
  `status` enum('Reserved','Rented','Returned','Late','Cancelled') DEFAULT 'Reserved',
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `deposit` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rental_order_items`
--

CREATE TABLE `rental_order_items` (
  `rental_order_item_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_settings`
--

CREATE TABLE `shop_settings` (
  `setting_id` int(11) NOT NULL,
  `shop_name` varchar(100) NOT NULL,
  `shop_address` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shop_settings`
--

INSERT INTO `shop_settings` (`setting_id`, `shop_name`, `shop_address`, `contact_number`, `email`, `updated_at`) VALUES
(1, 'Ugay\'s Elegant Tailoring & Rental Shop', '123 Fashion Ave, Manila, Philippines', '09123456789', 'info@ugayselegant.com', '2026-08-19 01:46:03');

-- --------------------------------------------------------

--
-- Table structure for table `tailoring_orders`
--

CREATE TABLE `tailoring_orders` (
  `tailoring_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `service_type` varchar(100) NOT NULL,
  `clothing_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `order_date` date NOT NULL,
  `fitting_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('Pending','In Progress','For Fitting','Completed','Cancelled') DEFAULT 'Pending',
  `total_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$A8/JY1OAaw7zcG.tri2N5O7d2b.IDJdZkjTm8bllTTOmie..Eeo5a', 'admin', '2026-08-19 01:46:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `tailoring_id` (`tailoring_id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `rental_items`
--
ALTER TABLE `rental_items`
  ADD PRIMARY KEY (`item_id`),
  ADD UNIQUE KEY `item_code` (`item_code`);

--
-- Indexes for table `rental_orders`
--
ALTER TABLE `rental_orders`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `rental_order_items`
--
ALTER TABLE `rental_order_items`
  ADD PRIMARY KEY (`rental_order_item_id`),
  ADD KEY `rental_id` (`rental_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `shop_settings`
--
ALTER TABLE `shop_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `tailoring_orders`
--
ALTER TABLE `tailoring_orders`
  ADD PRIMARY KEY (`tailoring_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `employee_id` (`employee_id`);

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
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rental_items`
--
ALTER TABLE `rental_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rental_orders`
--
ALTER TABLE `rental_orders`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rental_order_items`
--
ALTER TABLE `rental_order_items`
  MODIFY `rental_order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shop_settings`
--
ALTER TABLE `shop_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tailoring_orders`
--
ALTER TABLE `tailoring_orders`
  MODIFY `tailoring_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`tailoring_id`) REFERENCES `tailoring_orders` (`tailoring_id`),
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`rental_id`) REFERENCES `rental_orders` (`rental_id`);

--
-- Constraints for table `rental_orders`
--
ALTER TABLE `rental_orders`
  ADD CONSTRAINT `rental_orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `rental_orders_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `rental_order_items`
--
ALTER TABLE `rental_order_items`
  ADD CONSTRAINT `rental_order_items_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rental_orders` (`rental_id`),
  ADD CONSTRAINT `rental_order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `rental_items` (`item_id`);

--
-- Constraints for table `tailoring_orders`
--
ALTER TABLE `tailoring_orders`
  ADD CONSTRAINT `tailoring_orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `tailoring_orders_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
