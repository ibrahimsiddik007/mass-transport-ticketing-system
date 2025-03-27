-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2025 at 04:32 AM
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
(9, 'txn_ir44wsl', 22, 25, 0, '2025-03-24 06:38:38', 'Uttara', 'Farmgate', 'Shyamoli Paribahan', 'local', 'bkash');

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
(22, 'Savar Paribahan', 50, 'Shyamoli', 'Agargaon'),
(23, 'Savar Paribahan', 50, 'Agargaon', 'Shyamoli'),
(24, 'Mirpur Link', 45, 'Kuril', 'Bashundhara'),
(25, 'Mirpur Link', 45, 'Bashundhara', 'Kuril'),
(26, 'Super Sheba', 50, 'Gulistan', 'Motijheel'),
(27, 'Super Sheba', 50, 'Motijheel', 'Gulistan'),
(28, 'Victor Paribahan', 55, 'Mirpur-1', 'Mirpur-10'),
(29, 'Victor Paribahan', 55, 'Mirpur-10', 'Mirpur-1'),
(30, 'Al-Makkah Paribahan', 50, 'Shahbagh', 'Farmgate'),
(31, 'Al-Makkah Paribahan', 50, 'Farmgate', 'Shahbagh'),
(32, 'Suvastu Paribahan', 50, 'Uttara', 'Kuril'),
(33, 'Suvastu Paribahan', 50, 'Kuril', 'Uttara');

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
(29, 'Shyamoli', 'Agargaon', 5, 15.00),
(30, 'Agargaon', 'Shyamoli', 5, 15.00),
(31, 'Kuril', 'Bashundhara', 2, 8.00),
(32, 'Bashundhara', 'Kuril', 2, 8.00),
(33, 'Gulistan', 'Motijheel', 2, 10.00),
(34, 'Motijheel', 'Gulistan', 2, 10.00),
(35, 'Mirpur-1', 'Mirpur-10', 4, 12.00),
(36, 'Mirpur-10', 'Mirpur-1', 4, 12.00),
(37, 'Shahbagh', 'Farmgate', 5, 15.00),
(38, 'Farmgate', 'Shahbagh', 5, 15.00),
(39, 'Uttara', 'Kuril', 7, 20.00),
(40, 'Kuril', 'Uttara', 7, 20.00);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `local_buses`
--
ALTER TABLE `local_buses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `local_routes`
--
ALTER TABLE `local_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `local_routes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
