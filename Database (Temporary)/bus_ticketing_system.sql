-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2025 at 12:13 AM
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
-- Database: `bus_ticketing_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bus_transactions`
--

CREATE TABLE `bus_transactions` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `user_id` int(255) NOT NULL,
  `amount` int(255) NOT NULL,
  `seats` int(255) NOT NULL,
  `payment_time` datetime NOT NULL,
  `origin` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `bus_name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `payment_method` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bus_transactions`
--

INSERT INTO `bus_transactions` (`id`, `transaction_id`, `user_id`, `amount`, `seats`, `payment_time`, `origin`, `destination`, `bus_name`, `type`, `payment_method`) VALUES
(1, 'txn_8bmi3hl', 22, 25, 0, '2025-03-08 22:48:52', 'Farmgate', 'Uttara', 'Shyamoli Paribahan', 'local', 'bkash'),
(2, 'txn_olhp4q6', 22, 30, 0, '2025-03-09 03:51:03', 'Mohammadpur', 'Gulistan', 'BRTC', 'local', 'bkash'),
(3, 'txn_cenu6id', 22, 35, 0, '2025-03-09 03:56:28', 'Jatrabari', 'Azimpur', 'Anabil Super', 'local', 'bkash'),
(4, 'txn_15b58nc', 22, 10, 0, '2025-03-09 04:03:47', 'Gulistan', 'Motijheel', 'Super Sheba', 'local', 'bkash'),
(5, 'txn_mvfrh32', 22, 35, 0, '2025-03-09 04:12:21', 'Jatrabari', 'Azimpur', 'Anabil Super', 'local', 'bkash'),
(6, 'txn_8ridr2f', 22, 20, 0, '2025-03-09 05:10:07', 'Gabtoli', 'Savar', 'Soukhin Paribahan', 'local', 'rocket'),
(7, 'txn_9o89uz3', 22, 38, 0, '2025-03-09 05:17:33', 'Motijheel', 'Mirpur-10', 'Mirpur Super Link', 'local', 'bkash'),
(8, 'txn_pwel728', 22, 10, 0, '2025-03-09 05:21:46', 'Malibagh', 'Rampura', 'Salsabil Paribahan', 'local', 'card'),
(9, 'txn_ir44wsl', 22, 25, 0, '2025-03-24 06:38:38', 'Uttara', 'Farmgate', 'Shyamoli Paribahan', 'local', 'bkash'),
(10, 'txn_8s4t2x2', 22, 25, 0, '2025-03-24 14:43:14', 'Farmgate', 'Uttara', 'Shyamoli Paribahan', 'local', 'rocket'),
(11, 'txn_ccyccle', 22, 38, 0, '2025-04-07 15:49:01', 'Motijheel', 'Mirpur-10', 'Mirpur Super Link', 'local', 'rocket'),
(12, 'txn_86zct38', 22, 12, 0, '2025-04-07 15:51:32', 'Mirpur-10', 'Mirpur-1', 'Victor Paribahan', 'local', 'rocket'),
(13, 'txn_1bzit1c', 22, 20, 0, '2025-04-07 16:31:39', 'Gabtoli', 'Savar', 'Soukhin Paribahan', 'local', 'bkash'),
(14, 'txn_c1u3y8g', 22, 38, 0, '2025-04-07 16:55:15', 'Motijheel', 'Mirpur-10', 'Mirpur Super Link', 'local', 'bkash'),
(15, 'txn_2nm38sp', 25, 20, 0, '2025-04-08 12:14:36', 'Uttara', 'Kuril', 'Suvastu Paribahan', 'local', 'bkash'),
(16, 'txn_dznu1hi', 22, 38, 0, '2025-04-16 03:48:59', 'Mirpur-10', 'Motijheel', 'Mirpur Super Link', 'local', 'rocket');

-- --------------------------------------------------------

--
-- Table structure for table `local_buses`
--

CREATE TABLE `local_buses` (
  `id` int(11) NOT NULL,
  `bus_name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `origin` varchar(50) DEFAULT NULL,
  `destination` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `local_buses`
--

INSERT INTO `local_buses` (`id`, `bus_name`, `capacity`, `origin`, `destination`) VALUES
(4, 'Mirpur Super Link', 50, 'Mirpur-10', 'Motijheel'),
(5, 'Mirpur Super Link', 50, 'Motijheel', 'Mirpur-10'),
(6, 'Shyamoli Paribahan', 50, 'Uttara', 'Farmgate'),
(7, 'Shyamoli Paribahan', 50, 'Farmgate', 'Uttara'),
(8, 'BRTC', 60, 'Mohammadpur', 'Gulistan'),
(9, 'BRTC', 60, 'Gulistan', 'Mohammadpur'),
(10, 'Anabil Super', 45, 'Jatrabari', 'Azimpur'),
(11, 'Anabil Super', 45, 'Azimpur', 'Jatrabari'),
(12, 'Soukhin Paribahan', 50, 'Savar', 'Gabtoli'),
(13, 'Soukhin Paribahan', 50, 'Gabtoli', 'Savar'),
(14, 'Turag Paribahan', 55, 'Dhanmondi', 'Shahbagh'),
(15, 'Turag Paribahan', 55, 'Shahbagh', 'Dhanmondi'),
(16, 'Rajdhani Express', 50, 'Mohakhali', 'Gulshan-1'),
(17, 'Rajdhani Express', 50, 'Gulshan-1', 'Mohakhali'),
(18, 'Shikhor Paribahan', 50, 'Banani', 'Kakoli'),
(19, 'Shikhor Paribahan', 50, 'Kakoli', 'Banani'),
(20, 'Salsabil Paribahan', 45, 'Rampura', 'Malibagh'),
(21, 'Salsabil Paribahan', 45, 'Malibagh', 'Rampura'),
(24, 'Mirpur Link', 45, 'Kuril', 'Bashundhara'),
(25, 'Mirpur Link', 45, 'Bashundhara', 'Kuril'),
(26, 'Super Sheba', 50, 'Gulistan', 'Motijheel'),
(27, 'Super Sheba', 50, 'Motijheel', 'Gulistan'),
(28, 'Victor Paribahan', 55, 'Mirpur-1', 'Mirpur-10'),
(29, 'Victor Paribahan', 55, 'Mirpur-10', 'Mirpur-1'),
(30, 'Al-Makkah Paribahan', 50, 'Shahbagh', 'Farmgate'),
(31, 'Al-Makkah Paribahan', 50, 'Farmgate', 'Shahbagh'),
(32, 'Suvastu Paribahan', 50, 'Uttara', 'Kuril'),
(33, 'Suvastu Paribahan', 50, 'Kuril', 'Uttara'),
(34, 'Savar Paribahan', 40, 'Savar', 'Gulistan');

-- --------------------------------------------------------

--
-- Table structure for table `local_routes`
--

CREATE TABLE `local_routes` (
  `id` int(11) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `distance` int(11) NOT NULL,
  `fare` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `local_routes`
--

INSERT INTO `local_routes` (`id`, `origin`, `destination`, `distance`, `fare`) VALUES
(1, 'Mirpur-10', 'Motijheel', 15, 37.50),
(2, 'Motijheel', 'Mirpur-10', 15, 37.50),
(3, 'Uttara', 'Farmgate', 10, 25.00),
(4, 'Farmgate', 'Uttara', 10, 25.00),
(5, 'Mohammadpur', 'Gulistan', 12, 30.00),
(6, 'Gulistan', 'Mohammadpur', 12, 30.00),
(7, 'Jatrabari', 'Azimpur', 14, 35.00),
(8, 'Azimpur', 'Jatrabari', 14, 35.00),
(9, 'Savar', 'Gabtoli', 8, 20.00),
(10, 'Gabtoli', 'Savar', 8, 20.00),
(11, 'Mirpur-10', 'Motijheel', 15, 37.50),
(12, 'Motijheel', 'Mirpur-10', 15, 37.50),
(13, 'Uttara', 'Farmgate', 10, 25.00),
(14, 'Farmgate', 'Uttara', 10, 25.00),
(15, 'Mohammadpur', 'Gulistan', 12, 30.00),
(16, 'Gulistan', 'Mohammadpur', 12, 30.00),
(17, 'Jatrabari', 'Azimpur', 14, 35.00),
(18, 'Azimpur', 'Jatrabari', 14, 35.00),
(19, 'Savar', 'Gabtoli', 8, 20.00),
(20, 'Gabtoli', 'Savar', 8, 20.00),
(21, 'Dhanmondi', 'Shahbagh', 5, 15.00),
(22, 'Shahbagh', 'Dhanmondi', 5, 15.00),
(23, 'Mohakhali', 'Gulshan-1', 6, 18.00),
(24, 'Gulshan-1', 'Mohakhali', 6, 18.00),
(25, 'Banani', 'Kakoli', 4, 12.00),
(26, 'Kakoli', 'Banani', 4, 12.00),
(27, 'Rampura', 'Malibagh', 3, 10.00),
(28, 'Malibagh', 'Rampura', 3, 10.00),
(31, 'Kuril', 'Bashundhara', 2, 8.00),
(32, 'Bashundhara', 'Kuril', 2, 8.00),
(33, 'Gulistan', 'Motijheel', 2, 10.00),
(34, 'Motijheel', 'Gulistan', 2, 10.00),
(35, 'Mirpur-1', 'Mirpur-10', 4, 12.00),
(36, 'Mirpur-10', 'Mirpur-1', 4, 12.00),
(37, 'Shahbagh', 'Farmgate', 5, 15.00),
(38, 'Farmgate', 'Shahbagh', 5, 15.00),
(39, 'Uttara', 'Kuril', 7, 20.00),
(40, 'Kuril', 'Uttara', 7, 20.00),
(41, 'Savar', 'Gulistan', 10, 50.00),
(42, 'Gulistan', 'Savar', 10, 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `long_route_buses`
--

CREATE TABLE `long_route_buses` (
  `bus_id` int(11) NOT NULL,
  `bus_name` varchar(100) NOT NULL,
  `from_location` varchar(100) NOT NULL,
  `to_location` varchar(100) NOT NULL,
  `departure_time` time NOT NULL,
  `journey_date` date NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `total_seats` int(11) NOT NULL DEFAULT 40,
  `bus_type` varchar(50) DEFAULT 'AC'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `long_route_buses`
--

INSERT INTO `long_route_buses` (`bus_id`, `bus_name`, `from_location`, `to_location`, `departure_time`, `journey_date`, `fare`, `total_seats`, `bus_type`) VALUES
(6, 'Zenin', 'Sirajganj', 'Dhaka', '17:00:00', '0000-00-00', 350.00, 40, 'Non-AC');

-- --------------------------------------------------------

--
-- Table structure for table `long_route_seats`
--

CREATE TABLE `long_route_seats` (
  `seat_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `seat_number` varchar(10) NOT NULL,
  `status` enum('available','booked') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `long_route_seats`
--

INSERT INTO `long_route_seats` (`seat_id`, `bus_id`, `seat_number`, `status`) VALUES
(41, 6, 'A1', 'available'),
(42, 6, 'A2', 'available'),
(43, 6, 'A3', 'available'),
(44, 6, 'A4', 'available'),
(45, 6, 'A5', 'available'),
(46, 6, 'A6', 'available'),
(47, 6, 'A7', 'available'),
(48, 6, 'A8', 'available'),
(49, 6, 'A9', 'available'),
(50, 6, 'A10', 'available'),
(51, 6, 'B1', 'available'),
(52, 6, 'B2', 'available'),
(53, 6, 'B3', 'available'),
(54, 6, 'B4', 'available'),
(55, 6, 'B5', 'available'),
(56, 6, 'B6', 'available'),
(57, 6, 'B7', 'available'),
(58, 6, 'B8', 'available'),
(59, 6, 'B9', 'available'),
(60, 6, 'B10', 'available'),
(61, 6, 'C1', 'available'),
(62, 6, 'C2', 'available'),
(63, 6, 'C3', 'available'),
(64, 6, 'C4', 'available'),
(65, 6, 'C5', 'available'),
(66, 6, 'C6', 'available'),
(67, 6, 'C7', 'available'),
(68, 6, 'C8', 'available'),
(69, 6, 'C9', 'available'),
(70, 6, 'C10', 'available'),
(71, 6, 'D1', 'available'),
(72, 6, 'D2', 'available'),
(73, 6, 'D3', 'available'),
(74, 6, 'D4', 'available'),
(75, 6, 'D5', 'available'),
(76, 6, 'D6', 'available'),
(77, 6, 'D7', 'available'),
(78, 6, 'D8', 'available'),
(79, 6, 'D9', 'available'),
(80, 6, 'D10', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `long_route_transactions`
--

CREATE TABLE `long_route_transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `seat_numbers` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `payment_time` datetime DEFAULT current_timestamp(),
  `payment_status` varchar(20) DEFAULT '''completed''',
  `payment_transaction_id` varchar(100) DEFAULT NULL,
  `journey_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `long_route_transactions`
--

INSERT INTO `long_route_transactions` (`transaction_id`, `user_id`, `bus_id`, `seat_numbers`, `amount`, `payment_method`, `payment_time`, `payment_status`, `payment_transaction_id`, `journey_date`) VALUES
(19, 22, 6, '\n                                    A7                                ,\n                                    B7                                ', 700.00, 'bkash', '2025-04-13 20:03:03', 'completed', 'txn-CCB9FB', NULL),
(20, 22, 6, '\n                                    A6                                ,\n                                    B6                                ', 700.00, 'bkash', '2025-04-13 20:13:53', 'completed', 'txn-263A5E', NULL),
(21, 22, 6, '\n                                    A7                                ,\n                                    B7                                ', 700.00, 'bkash', '2025-04-13 20:17:57', 'completed', 'txn-65CF07', '2025-04-15'),
(22, 22, 6, '\n                                    C7                                ,\n                                    D7                                ', 700.00, 'bkash', '2025-04-13 20:29:19', 'completed', 'txn-065D15', '2025-04-15'),
(23, 22, 6, '\n                                    A8                                ,\n                                    B8                                ', 700.00, 'bkash', '2025-04-13 21:31:34', 'completed', 'txn-DB099B', '2025-04-15'),
(24, 22, 6, '\n                                    C8                                ,\n                                    D8                                ', 700.00, 'bkash', '2025-04-13 23:33:46', 'completed', 'txn-6FA315', '2025-04-15'),
(25, 22, 6, '\n                                    C9                                ,\n                                    D9                                ', 700.00, 'bkash', '2025-04-14 00:11:16', 'completed', 'txn-CD4390', '2025-04-15'),
(26, 22, 6, '\n                                    B9                                ,\n                                    A9                                ', 700.00, 'bkash', '2025-04-14 00:12:11', 'completed', 'txn-0DA8B8', '2025-04-15'),
(27, 22, 6, '\n                                    C3                                ,\n                                    D3                                ', 700.00, 'rocket', '2025-04-16 03:47:33', 'completed', 'txn-668351', '2025-04-16'),
(28, 22, 6, '\n                                    C5                                ,\n                                    D5                                ', 700.00, 'rocket', '2025-04-16 03:51:25', 'completed', 'txn-9BF591', '2025-04-16');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `route_id` int(11) DEFAULT NULL,
  `seat_number` int(11) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `reserved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_confirmed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bus_transactions`
--
ALTER TABLE `bus_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `local_buses`
--
ALTER TABLE `local_buses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `local_routes`
--
ALTER TABLE `local_routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `long_route_buses`
--
ALTER TABLE `long_route_buses`
  ADD PRIMARY KEY (`bus_id`);

--
-- Indexes for table `long_route_seats`
--
ALTER TABLE `long_route_seats`
  ADD PRIMARY KEY (`seat_id`),
  ADD KEY `bus_id` (`bus_id`);

--
-- Indexes for table `long_route_transactions`
--
ALTER TABLE `long_route_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `bus_id` (`bus_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `route_id` (`route_id`,`seat_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bus_transactions`
--
ALTER TABLE `bus_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `local_buses`
--
ALTER TABLE `local_buses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `local_routes`
--
ALTER TABLE `local_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `long_route_buses`
--
ALTER TABLE `long_route_buses`
  MODIFY `bus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `long_route_seats`
--
ALTER TABLE `long_route_seats`
  MODIFY `seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `long_route_transactions`
--
ALTER TABLE `long_route_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `long_route_transactions`
--
ALTER TABLE `long_route_transactions`
  ADD CONSTRAINT `long_route_transactions_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `long_route_buses` (`bus_id`);

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `local_routes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
