-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2025 at 05:04 PM
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
-- Database: `train_reservation_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `train_id` int(11) DEFAULT NULL,
  `seat_number` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `reservation_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('reserved','paid','cancelled') DEFAULT 'reserved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `train_id`, `seat_number`, `customer_name`, `customer_email`, `reservation_time`, `status`) VALUES
(25, 1, 1, 'ibrahim', 'ibrahim@gmail.com', '2025-01-21 14:18:41', 'paid'),
(26, 1, 2, 'ibrahim', 'ibrahim@gmail.com', '2025-01-21 14:18:41', 'paid'),
(27, 1, 3, 'saff', 'fsdfdf@gdsf.com', '2025-01-21 14:20:40', 'paid'),
(28, 1, 4, 'saff', 'fsdfdf@gdsf.com', '2025-01-21 14:20:41', 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `id` int(11) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `origin`, `destination`, `departure_time`, `arrival_time`) VALUES
(1, 'Dhaka', 'Chittagong', '08:00:00', '12:00:00'),
(2, 'Dhaka', 'Sylhet', '09:00:00', '13:00:00'),
(3, 'Dhaka', 'Rajshahi', '10:00:00', '14:00:00'),
(4, 'Dhaka', 'Khulna', '11:00:00', '15:00:00'),
(5, 'Dhaka', 'Rangpur', '12:00:00', '16:00:00'),
(6, 'Chittagong', 'Sylhet', '13:00:00', '17:00:00'),
(7, 'Chittagong', 'Rajshahi', '14:00:00', '18:00:00'),
(8, 'Chittagong', 'Khulna', '15:00:00', '19:00:00'),
(9, 'Sylhet', 'Rajshahi', '16:00:00', '20:00:00'),
(10, 'Sylhet', 'Khulna', '17:00:00', '21:00:00'),
(11, 'Dhaka', 'Chittagong', '08:00:00', '12:00:00'),
(12, 'Dhaka', 'Sylhet', '09:00:00', '13:00:00'),
(13, 'Dhaka', 'Rajshahi', '10:00:00', '14:00:00'),
(14, 'Dhaka', 'Khulna', '11:00:00', '15:00:00'),
(15, 'Dhaka', 'Rangpur', '12:00:00', '16:00:00'),
(16, 'Chittagong', 'Sylhet', '13:00:00', '17:00:00'),
(17, 'Chittagong', 'Rajshahi', '14:00:00', '18:00:00'),
(18, 'Chittagong', 'Khulna', '15:00:00', '19:00:00'),
(19, 'Sylhet', 'Rajshahi', '16:00:00', '20:00:00'),
(20, 'Sylhet', 'Khulna', '17:00:00', '21:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `trains`
--

CREATE TABLE `trains` (
  `id` int(11) NOT NULL,
  `route_id` int(11) DEFAULT NULL,
  `train_name` varchar(100) NOT NULL,
  `total_seats` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trains`
--

INSERT INTO `trains` (`id`, `route_id`, `train_name`, `total_seats`) VALUES
(1, 1, 'Dhaka to Chittagong Express', 96),
(2, 2, 'Dhaka to Sylhet Express', 100),
(3, 3, 'Dhaka to Rajshahi Express', 100),
(4, 4, 'Dhaka to Khulna Express', 100),
(5, 5, 'Dhaka to Rangpur Express', 100),
(6, 6, 'Chittagong to Sylhet Express', 100),
(7, 7, 'Chittagong to Rajshahi Express', 100),
(8, 8, 'Chittagong to Khulna Express', 100),
(9, 9, 'Sylhet to Rajshahi Express', 100),
(10, 10, 'Sylhet to Khulna Express', 100),
(11, 11, 'Dhaka to Chittagong Express', 100),
(12, 12, 'Dhaka to Sylhet Express', 100),
(13, 13, 'Dhaka to Rajshahi Express', 100),
(14, 14, 'Dhaka to Khulna Express', 100),
(15, 15, 'Dhaka to Rangpur Express', 100),
(16, 16, 'Chittagong to Sylhet Express', 100),
(17, 17, 'Chittagong to Rajshahi Express', 100),
(18, 18, 'Chittagong to Khulna Express', 100),
(19, 19, 'Sylhet to Rajshahi Express', 100),
(20, 20, 'Sylhet to Khulna Express', 100);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `train_id` (`train_id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trains`
--
ALTER TABLE `trains`
  ADD PRIMARY KEY (`id`),
  ADD KEY `route_id` (`route_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `trains`
--
ALTER TABLE `trains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`train_id`) REFERENCES `trains` (`id`);

--
-- Constraints for table `trains`
--
ALTER TABLE `trains`
  ADD CONSTRAINT `trains_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
