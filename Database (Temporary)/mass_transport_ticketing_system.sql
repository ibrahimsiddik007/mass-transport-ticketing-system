-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2025 at 08:13 PM
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
-- Database: `mass transport ticketing system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`) VALUES
(1, '2211632', 'ibrahim');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `user_id`, `message`, `created_at`, `is_admin`, `is_read`, `name`) VALUES
(1, 19, 'hello', '2025-03-07 23:09:33', 0, 1, 'Faiyan Islam Swapnil'),
(2, 19, 'hi', '2025-03-07 23:09:49', 1, 0, 'system'),
(3, 22, 'hello', '2025-03-08 23:14:32', 0, 1, 'Ibrahim'),
(4, 22, 'hii', '2025-03-08 23:24:58', 1, 1, 'system'),
(5, 22, 'tell me what are you doing?', '2025-03-09 00:04:38', 1, 1, 'system'),
(6, 22, 'is everything okay on your end?', '2025-03-09 00:04:55', 0, 1, 'Ibrahim'),
(7, 22, 'yeah it is okay', '2025-03-09 00:05:02', 1, 1, 'system'),
(8, 22, 'thank you for saying that', '2025-03-09 00:05:11', 1, 1, 'system'),
(9, 22, 'i have successfully implemented the file', '2025-03-09 00:05:27', 0, 1, 'Ibrahim'),
(10, 22, 'yeahhhhhhhhh', '2025-03-09 00:05:34', 1, 1, 'system'),
(11, 22, 'kudos', '2025-03-09 00:05:41', 1, 1, 'system'),
(12, 22, 'wowww', '2025-03-09 00:07:02', 0, 1, 'Ibrahim'),
(13, 19, 'lalal', '2025-03-09 00:07:17', 1, 0, 'system'),
(14, 22, 'hehehe', '2025-03-09 00:07:25', 0, 1, 'Ibrahim'),
(15, 22, 'hello', '2025-03-24 00:26:56', 1, 1, 'system'),
(16, 22, 'hi', '2025-03-24 00:27:03', 0, 1, 'Ibrahim'),
(17, 22, 'how to do this', '2025-03-24 00:27:11', 0, 1, 'Ibrahim'),
(18, 22, 'test done', '2025-03-24 00:27:23', 1, 1, 'system'),
(19, 22, 'test done', '2025-03-24 00:35:42', 0, 1, 'Ibrahim'),
(20, 22, 'again', '2025-03-24 00:36:04', 1, 1, 'system'),
(21, 22, 'test', '2025-03-24 00:36:41', 1, 1, 'system'),
(22, 22, 'hi', '2025-03-24 00:37:00', 0, 1, 'Ibrahim'),
(23, 22, 'hi', '2025-03-24 00:53:39', 1, 1, 'system'),
(24, 22, 'test', '2025-03-24 00:53:56', 1, 1, 'system'),
(25, 22, 'test 2', '2025-03-24 00:54:14', 1, 1, 'system'),
(26, 22, 'test 3', '2025-03-24 00:55:05', 1, 1, 'system'),
(27, 22, 'thanks', '2025-03-24 00:56:06', 0, 1, 'Ibrahim'),
(28, 22, 'welcome', '2025-03-24 00:56:13', 1, 1, 'system'),
(29, 22, 'again welcome', '2025-03-24 00:56:29', 1, 1, 'system'),
(30, 22, 'hello', '2025-03-24 08:44:56', 1, 1, 'system'),
(31, 22, 'hi', '2025-03-24 08:45:08', 0, 1, 'Ibrahim'),
(32, 22, 'hello', '2025-03-24 08:45:21', 1, 1, 'system'),
(33, 25, 'hi', '2025-04-08 06:17:51', 0, 1, 'Oli Ahmed'),
(34, 25, 'hello', '2025-04-08 06:18:09', 1, 1, 'system'),
(35, 25, 'noti besssa', '2025-04-08 06:18:59', 0, 1, 'Oli Ahmed'),
(36, 25, 'tai naki?', '2025-04-08 06:19:42', 1, 1, 'system'),
(37, 25, 'hae re notir baccha', '2025-04-08 06:19:51', 0, 1, 'Oli Ahmed'),
(38, 22, 'hii', '2025-04-13 15:47:02', 0, 0, 'Ibrahim'),
(39, 22, 'chudi', '2025-04-13 18:12:41', 0, 0, 'Ibrahim');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `message`, `created_at`) VALUES
(1, 'MD Ibrahim Siddik', 'ibrahimsiddik007@gmail.com', '01601750278', 'How can I avail the service?', '2025-03-07 15:37:59'),
(2, 'MD Ibrahim Siddik', 'ibrahimsiddik007@gmail.com', '01601750278', 'Testing purpose', '2025-03-07 15:40:19'),
(3, 'MD Ibrahim Siddik', 'ibrahimsiddik007@gmail.com', '01601750278', 'test', '2025-03-07 15:48:09'),
(4, 'Arafat', 'arafat@gmail.com', '01711111111', 'Testing', '2025-03-08 18:23:49'),
(5, 'foysal ahmed', 'foysalhridoy@gmail.com', '23287647647683', 'fjhsiufsjfhsif', '2025-03-24 03:46:05'),
(6, 'Oli', 'oliahmedsarker@gmail.com', '01753927290', 'How can I avail this service?\r\n', '2025-04-08 02:20:54');

-- --------------------------------------------------------

--
-- Table structure for table `demo_accounts`
--

CREATE TABLE `demo_accounts` (
  `id` int(11) NOT NULL,
  `account_type` varchar(255) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `pin` varchar(10) NOT NULL,
  `balance` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `demo_accounts`
--

INSERT INTO `demo_accounts` (`id`, `account_type`, `account_number`, `pin`, `balance`) VALUES
(1, 'bkash', '01601750278', '1234', 3653.00),
(2, 'rocket', '01601750278', '1234', 17669.00),
(3, 'card', '12345678', '123', 19966.00);

-- --------------------------------------------------------

--
-- Table structure for table `metro_stations`
--

CREATE TABLE `metro_stations` (
  `id` int(11) NOT NULL,
  `s_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `metro_stations`
--

INSERT INTO `metro_stations` (`id`, `s_name`) VALUES
(1, 'Uttara_North'),
(2, 'Uttara_Center'),
(3, 'Uttara_South'),
(4, 'Pallabi'),
(5, 'Mirpur_11'),
(6, 'Mirpur_10'),
(7, 'Kazi_Para'),
(8, 'ShewraPara'),
(9, 'Agargaon'),
(10, 'Bijoy_Sharani'),
(11, 'Farmgate'),
(12, 'Kawran_Bazar'),
(13, 'Shahbagh'),
(14, 'Dhaka_University'),
(15, 'Secretariat'),
(16, 'Motijheel');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `rating`, `comment`, `created_at`, `display_order`) VALUES
(2, 22, 4, 'test', '2025-03-07 15:57:28', 0),
(4, 22, 5, 'The service was good!', '2025-04-08 06:25:19', 0),
(5, 22, 5, 'Best Case Test', '2025-04-13 17:42:36', 0);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_routes`
--

CREATE TABLE `ticket_routes` (
  `id` int(11) NOT NULL,
  `start_point` varchar(255) NOT NULL,
  `end_point` varchar(255) NOT NULL,
  `fare` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_routes`
--

INSERT INTO `ticket_routes` (`id`, `start_point`, `end_point`, `fare`) VALUES
(1, 'Uttara_North', 'Uttara_Center', 78.00),
(2, 'Uttara_North', 'Uttara_South', 11.00),
(3, 'Uttara_North', 'Pallabi', 57.00),
(4, 'Uttara_North', 'Mirpur_11', 15.00),
(5, 'Uttara_North', 'Mirpur_10', 57.00),
(6, 'Uttara_North', 'Kazi_Para', 33.00),
(7, 'Uttara_North', 'ShewraPara', 95.00),
(8, 'Uttara_North', 'Agargaon', 21.00),
(9, 'Uttara_North', 'Bijoy_Sharani', 43.00),
(10, 'Uttara_North', 'Farmgate', 67.00),
(11, 'Uttara_North', 'Kawran_Bazar', 31.00),
(12, 'Uttara_North', 'Shahbagh', 58.00),
(13, 'Uttara_North', 'Dhaka_University', 83.00),
(14, 'Uttara_North', 'Secretariat', 94.00),
(15, 'Uttara_North', 'Motijheel', 32.00),
(16, 'Uttara_Center', 'Uttara_North', 78.00),
(17, 'Uttara_Center', 'Uttara_South', 55.00),
(18, 'Uttara_Center', 'Pallabi', 78.00),
(19, 'Uttara_Center', 'Mirpur_11', 60.00),
(20, 'Uttara_Center', 'Mirpur_10', 22.00),
(21, 'Uttara_Center', 'Kazi_Para', 53.00),
(22, 'Uttara_Center', 'ShewraPara', 56.00),
(23, 'Uttara_Center', 'Agargaon', 29.00),
(24, 'Uttara_Center', 'Bijoy_Sharani', 16.00),
(25, 'Uttara_Center', 'Farmgate', 23.00),
(26, 'Uttara_Center', 'Kawran_Bazar', 55.00),
(27, 'Uttara_Center', 'Shahbagh', 58.00),
(28, 'Uttara_Center', 'Dhaka_University', 15.00),
(29, 'Uttara_Center', 'Secretariat', 68.00),
(30, 'Uttara_Center', 'Motijheel', 66.00),
(31, 'Uttara_South', 'Uttara_North', 11.00),
(32, 'Uttara_South', 'Uttara_Center', 55.00),
(33, 'Uttara_South', 'Pallabi', 60.00),
(34, 'Uttara_South', 'Mirpur_11', 57.00),
(35, 'Uttara_South', 'Mirpur_10', 52.00),
(36, 'Uttara_South', 'Kazi_Para', 55.00),
(37, 'Uttara_South', 'ShewraPara', 12.00),
(38, 'Uttara_South', 'Agargaon', 25.00),
(39, 'Uttara_South', 'Bijoy_Sharani', 68.00),
(40, 'Uttara_South', 'Farmgate', 44.00),
(41, 'Uttara_South', 'Kawran_Bazar', 47.00),
(42, 'Uttara_South', 'Shahbagh', 79.00),
(43, 'Uttara_South', 'Dhaka_University', 17.00),
(44, 'Uttara_South', 'Secretariat', 12.00),
(45, 'Uttara_South', 'Motijheel', 36.00),
(46, 'Pallabi', 'Uttara_North', 57.00),
(47, 'Pallabi', 'Uttara_Center', 78.00),
(48, 'Pallabi', 'Uttara_South', 60.00),
(49, 'Pallabi', 'Mirpur_11', 85.00),
(50, 'Pallabi', 'Mirpur_10', 55.00),
(51, 'Pallabi', 'Kazi_Para', 87.00),
(52, 'Pallabi', 'ShewraPara', 92.00),
(53, 'Pallabi', 'Agargaon', 91.00),
(54, 'Pallabi', 'Bijoy_Sharani', 86.00),
(55, 'Pallabi', 'Farmgate', 63.00),
(56, 'Pallabi', 'Kawran_Bazar', 98.00),
(57, 'Pallabi', 'Shahbagh', 54.00),
(58, 'Pallabi', 'Dhaka_University', 59.00),
(59, 'Pallabi', 'Secretariat', 54.00),
(60, 'Pallabi', 'Motijheel', 36.00),
(61, 'Mirpur_11', 'Uttara_North', 15.00),
(62, 'Mirpur_11', 'Uttara_Center', 60.00),
(63, 'Mirpur_11', 'Uttara_South', 57.00),
(64, 'Mirpur_11', 'Pallabi', 85.00),
(65, 'Mirpur_11', 'Mirpur_10', 75.00),
(66, 'Mirpur_11', 'Kazi_Para', 10.00),
(67, 'Mirpur_11', 'ShewraPara', 49.00),
(68, 'Mirpur_11', 'Agargaon', 41.00),
(69, 'Mirpur_11', 'Bijoy_Sharani', 24.00),
(70, 'Mirpur_11', 'Farmgate', 55.00),
(71, 'Mirpur_11', 'Kawran_Bazar', 69.00),
(72, 'Mirpur_11', 'Shahbagh', 26.00),
(73, 'Mirpur_11', 'Dhaka_University', 87.00),
(74, 'Mirpur_11', 'Secretariat', 27.00),
(75, 'Mirpur_11', 'Motijheel', 67.00),
(76, 'Mirpur_10', 'Uttara_North', 57.00),
(77, 'Mirpur_10', 'Uttara_Center', 22.00),
(78, 'Mirpur_10', 'Uttara_South', 52.00),
(79, 'Mirpur_10', 'Pallabi', 55.00),
(80, 'Mirpur_10', 'Mirpur_11', 75.00),
(81, 'Mirpur_10', 'Kazi_Para', 61.00),
(82, 'Mirpur_10', 'ShewraPara', 40.00),
(83, 'Mirpur_10', 'Agargaon', 82.00),
(84, 'Mirpur_10', 'Bijoy_Sharani', 94.00),
(85, 'Mirpur_10', 'Farmgate', 84.00),
(86, 'Mirpur_10', 'Kawran_Bazar', 58.00),
(87, 'Mirpur_10', 'Shahbagh', 32.00),
(88, 'Mirpur_10', 'Dhaka_University', 91.00),
(89, 'Mirpur_10', 'Secretariat', 91.00),
(90, 'Mirpur_10', 'Motijheel', 99.00),
(91, 'Kazi_Para', 'Uttara_North', 33.00),
(92, 'Kazi_Para', 'Uttara_Center', 53.00),
(93, 'Kazi_Para', 'Uttara_South', 55.00),
(94, 'Kazi_Para', 'Pallabi', 87.00),
(95, 'Kazi_Para', 'Mirpur_11', 10.00),
(96, 'Kazi_Para', 'Mirpur_10', 61.00),
(97, 'Kazi_Para', 'ShewraPara', 17.00),
(98, 'Kazi_Para', 'Agargaon', 36.00),
(99, 'Kazi_Para', 'Bijoy_Sharani', 73.00),
(100, 'Kazi_Para', 'Farmgate', 74.00),
(101, 'Kazi_Para', 'Kawran_Bazar', 10.00),
(102, 'Kazi_Para', 'Shahbagh', 20.00),
(103, 'Kazi_Para', 'Dhaka_University', 70.00),
(104, 'Kazi_Para', 'Secretariat', 98.00),
(105, 'Kazi_Para', 'Motijheel', 52.00),
(106, 'ShewraPara', 'Uttara_North', 95.00),
(107, 'ShewraPara', 'Uttara_Center', 56.00),
(108, 'ShewraPara', 'Uttara_South', 12.00),
(109, 'ShewraPara', 'Pallabi', 92.00),
(110, 'ShewraPara', 'Mirpur_11', 49.00),
(111, 'ShewraPara', 'Mirpur_10', 40.00),
(112, 'ShewraPara', 'Kazi_Para', 17.00),
(113, 'ShewraPara', 'Agargaon', 27.00),
(114, 'ShewraPara', 'Bijoy_Sharani', 15.00),
(115, 'ShewraPara', 'Farmgate', 94.00),
(116, 'ShewraPara', 'Kawran_Bazar', 21.00),
(117, 'ShewraPara', 'Shahbagh', 37.00),
(118, 'ShewraPara', 'Dhaka_University', 67.00),
(119, 'ShewraPara', 'Secretariat', 27.00),
(120, 'ShewraPara', 'Motijheel', 83.00),
(121, 'Agargaon', 'Uttara_North', 21.00),
(122, 'Agargaon', 'Uttara_Center', 29.00),
(123, 'Agargaon', 'Uttara_South', 25.00),
(124, 'Agargaon', 'Pallabi', 91.00),
(125, 'Agargaon', 'Mirpur_11', 41.00),
(126, 'Agargaon', 'Mirpur_10', 82.00),
(127, 'Agargaon', 'Kazi_Para', 36.00),
(128, 'Agargaon', 'ShewraPara', 27.00),
(129, 'Agargaon', 'Bijoy_Sharani', 21.00),
(130, 'Agargaon', 'Farmgate', 44.00),
(131, 'Agargaon', 'Kawran_Bazar', 79.00),
(132, 'Agargaon', 'Shahbagh', 97.00),
(133, 'Agargaon', 'Dhaka_University', 55.00),
(134, 'Agargaon', 'Secretariat', 15.00),
(135, 'Agargaon', 'Motijheel', 46.00),
(136, 'Bijoy_Sharani', 'Uttara_North', 43.00),
(137, 'Bijoy_Sharani', 'Uttara_Center', 16.00),
(138, 'Bijoy_Sharani', 'Uttara_South', 68.00),
(139, 'Bijoy_Sharani', 'Pallabi', 86.00),
(140, 'Bijoy_Sharani', 'Mirpur_11', 24.00),
(141, 'Bijoy_Sharani', 'Mirpur_10', 94.00),
(142, 'Bijoy_Sharani', 'Kazi_Para', 73.00),
(143, 'Bijoy_Sharani', 'ShewraPara', 15.00),
(144, 'Bijoy_Sharani', 'Agargaon', 21.00),
(145, 'Bijoy_Sharani', 'Farmgate', 31.00),
(146, 'Bijoy_Sharani', 'Kawran_Bazar', 88.00),
(147, 'Bijoy_Sharani', 'Shahbagh', 64.00),
(148, 'Bijoy_Sharani', 'Dhaka_University', 66.00),
(149, 'Bijoy_Sharani', 'Secretariat', 10.00),
(150, 'Bijoy_Sharani', 'Motijheel', 30.00),
(151, 'Farmgate', 'Uttara_North', 67.00),
(152, 'Farmgate', 'Uttara_Center', 23.00),
(153, 'Farmgate', 'Uttara_South', 44.00),
(154, 'Farmgate', 'Pallabi', 63.00),
(155, 'Farmgate', 'Mirpur_11', 55.00),
(156, 'Farmgate', 'Mirpur_10', 84.00),
(157, 'Farmgate', 'Kazi_Para', 74.00),
(158, 'Farmgate', 'ShewraPara', 94.00),
(159, 'Farmgate', 'Agargaon', 44.00),
(160, 'Farmgate', 'Bijoy_Sharani', 31.00),
(161, 'Farmgate', 'Kawran_Bazar', 38.00),
(162, 'Farmgate', 'Shahbagh', 47.00),
(163, 'Farmgate', 'Dhaka_University', 73.00),
(164, 'Farmgate', 'Secretariat', 98.00),
(165, 'Farmgate', 'Motijheel', 78.00),
(166, 'Kawran_Bazar', 'Uttara_North', 31.00),
(167, 'Kawran_Bazar', 'Uttara_Center', 55.00),
(168, 'Kawran_Bazar', 'Uttara_South', 47.00),
(169, 'Kawran_Bazar', 'Pallabi', 98.00),
(170, 'Kawran_Bazar', 'Mirpur_11', 69.00),
(171, 'Kawran_Bazar', 'Mirpur_10', 58.00),
(172, 'Kawran_Bazar', 'Kazi_Para', 10.00),
(173, 'Kawran_Bazar', 'ShewraPara', 21.00),
(174, 'Kawran_Bazar', 'Agargaon', 79.00),
(175, 'Kawran_Bazar', 'Bijoy_Sharani', 88.00),
(176, 'Kawran_Bazar', 'Farmgate', 38.00),
(177, 'Kawran_Bazar', 'Shahbagh', 83.00),
(178, 'Kawran_Bazar', 'Dhaka_University', 42.00),
(179, 'Kawran_Bazar', 'Secretariat', 81.00),
(180, 'Kawran_Bazar', 'Motijheel', 43.00),
(181, 'Shahbagh', 'Uttara_North', 58.00),
(182, 'Shahbagh', 'Uttara_Center', 58.00),
(183, 'Shahbagh', 'Uttara_South', 79.00),
(184, 'Shahbagh', 'Pallabi', 54.00),
(185, 'Shahbagh', 'Mirpur_11', 26.00),
(186, 'Shahbagh', 'Mirpur_10', 32.00),
(187, 'Shahbagh', 'Kazi_Para', 20.00),
(188, 'Shahbagh', 'ShewraPara', 37.00),
(189, 'Shahbagh', 'Agargaon', 97.00),
(190, 'Shahbagh', 'Bijoy_Sharani', 64.00),
(191, 'Shahbagh', 'Farmgate', 47.00),
(192, 'Shahbagh', 'Kawran_Bazar', 83.00),
(193, 'Shahbagh', 'Dhaka_University', 27.00),
(194, 'Shahbagh', 'Secretariat', 95.00),
(195, 'Shahbagh', 'Motijheel', 15.00),
(196, 'Dhaka_University', 'Uttara_North', 83.00),
(197, 'Dhaka_University', 'Uttara_Center', 15.00),
(198, 'Dhaka_University', 'Uttara_South', 17.00),
(199, 'Dhaka_University', 'Pallabi', 59.00),
(200, 'Dhaka_University', 'Mirpur_11', 87.00),
(201, 'Dhaka_University', 'Mirpur_10', 91.00),
(202, 'Dhaka_University', 'Kazi_Para', 70.00),
(203, 'Dhaka_University', 'ShewraPara', 67.00),
(204, 'Dhaka_University', 'Agargaon', 55.00),
(205, 'Dhaka_University', 'Bijoy_Sharani', 66.00),
(206, 'Dhaka_University', 'Farmgate', 73.00),
(207, 'Dhaka_University', 'Kawran_Bazar', 42.00),
(208, 'Dhaka_University', 'Shahbagh', 27.00),
(209, 'Dhaka_University', 'Secretariat', 31.00),
(210, 'Dhaka_University', 'Motijheel', 18.00),
(211, 'Secretariat', 'Uttara_North', 94.00),
(212, 'Secretariat', 'Uttara_Center', 68.00),
(213, 'Secretariat', 'Uttara_South', 12.00),
(214, 'Secretariat', 'Pallabi', 54.00),
(215, 'Secretariat', 'Mirpur_11', 27.00),
(216, 'Secretariat', 'Mirpur_10', 91.00),
(217, 'Secretariat', 'Kazi_Para', 98.00),
(218, 'Secretariat', 'ShewraPara', 27.00),
(219, 'Secretariat', 'Agargaon', 15.00),
(220, 'Secretariat', 'Bijoy_Sharani', 10.00),
(221, 'Secretariat', 'Farmgate', 98.00),
(222, 'Secretariat', 'Kawran_Bazar', 81.00),
(223, 'Secretariat', 'Shahbagh', 95.00),
(224, 'Secretariat', 'Dhaka_University', 31.00),
(225, 'Secretariat', 'Motijheel', 85.00),
(226, 'Motijheel', 'Uttara_North', 32.00),
(227, 'Motijheel', 'Uttara_Center', 66.00),
(228, 'Motijheel', 'Uttara_South', 36.00),
(229, 'Motijheel', 'Pallabi', 36.00),
(230, 'Motijheel', 'Mirpur_11', 67.00),
(231, 'Motijheel', 'Mirpur_10', 99.00),
(232, 'Motijheel', 'Kazi_Para', 52.00),
(233, 'Motijheel', 'ShewraPara', 83.00),
(234, 'Motijheel', 'Agargaon', 46.00),
(235, 'Motijheel', 'Bijoy_Sharani', 30.00),
(236, 'Motijheel', 'Farmgate', 78.00),
(237, 'Motijheel', 'Kawran_Bazar', 43.00),
(238, 'Motijheel', 'Shahbagh', 15.00),
(239, 'Motijheel', 'Dhaka_University', 18.00),
(240, 'Motijheel', 'Secretariat', 85.00);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` varchar(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_location` varchar(255) NOT NULL,
  `end_location` varchar(255) NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `valid_till` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `start_location`, `end_location`, `fare`, `created_at`, `valid_till`) VALUES
('txn_0XzJdWB', 22, 'Mirpur_11', 'Bijoy_Sharani', 24.00, '2025-03-24 00:22:35', NULL),
('txn_31FfJXN', 22, 'Pallabi', 'Farmgate', 63.00, '2025-03-04 17:47:59', NULL),
('txn_328AmuH', 22, 'Mirpur_10', 'Motijheel', 99.00, '2025-04-10 18:00:30', NULL),
('txn_3Bn6EVW', 22, 'Motijheel', 'ShewraPara', 83.00, '2025-03-23 23:50:14', NULL),
('txn_53qmAby', 20, 'Uttara_South', 'Dhaka_University', 17.00, '2025-02-24 17:15:59', NULL),
('txn_5pD37rO', 20, 'Uttara_South', 'Dhaka_University', 17.00, '2025-02-24 17:09:17', NULL),
('txn_6zVFCu3', 22, 'Motijheel', 'ShewraPara', 83.00, '2025-03-23 23:47:04', NULL),
('txn_7LHOiln', 24, 'Kazi_Para', 'ShewraPara', 17.00, '2025-03-09 21:17:10', NULL),
('txn_7WbkYxU', 22, 'Motijheel', 'ShewraPara', 83.00, '2025-03-23 23:48:43', NULL),
('txn_8Dp54us', 22, 'ShewraPara', 'Uttara_North', 95.00, '2025-04-10 18:03:59', NULL),
('txn_9cp2q4X', 22, 'Uttara_South', 'Secretariat', 12.00, '2025-04-10 17:51:41', NULL),
('txn_AgKetL9', 22, 'Pallabi', 'Dhaka_University', 59.00, '2025-03-07 20:56:48', NULL),
('txn_AGsaULu', 22, 'Uttara_Center', 'Mirpur_10', 22.00, '2025-04-08 06:25:04', NULL),
('txn_Bb6tRom', 22, 'Uttara_South', 'Mirpur_10', 52.00, '2025-03-04 12:34:34', NULL),
('txn_dwyFT6Q', 22, 'Uttara_South', 'Mirpur_11', 57.00, '2025-03-04 12:38:30', NULL),
('txn_Er1qnBH', 20, 'Uttara_Center', 'Shahbagh', 58.00, '2025-02-24 17:00:47', NULL),
('txn_fJNeZ1i', 22, 'Mirpur_10', 'Uttara_North', 57.00, '2025-03-23 22:45:56', NULL),
('txn_gbaWTJR', 22, 'Uttara_South', 'Secretariat', 12.00, '2025-04-10 17:51:17', NULL),
('txn_gtyiX96', 24, 'Kazi_Para', 'ShewraPara', 17.00, '2025-03-09 21:13:06', NULL),
('txn_gw9yfqT', 22, 'Kazi_Para', 'Secretariat', 98.00, '2025-03-07 21:00:55', NULL),
('txn_H3Jrtmh', 20, 'Uttara_South', 'Dhaka_University', 17.00, '2025-02-24 17:07:41', NULL),
('txn_HWQgXCF', 22, 'Kawran_Bazar', 'Uttara_North', 31.00, '2025-03-23 22:52:01', NULL),
('txn_HZVKjOm', 20, 'Uttara_Center', 'Dhaka_University', 15.00, '2025-02-24 17:06:35', NULL),
('txn_iAs6uY8', 22, 'Bijoy_Sharani', 'Farmgate', 31.00, '2025-03-24 00:25:32', NULL),
('txn_JoRL6rn', 22, 'Uttara_Center', 'Mirpur_11', 60.00, '2025-04-13 16:01:04', '2025-04-14 16:01:04'),
('txn_kGrmNI1', 20, 'Uttara_Center', 'Shahbagh', 58.00, '2025-02-24 17:06:05', NULL),
('txn_lBsywuN', 22, 'Pallabi', 'Motijheel', 36.00, '2025-04-10 17:54:59', NULL),
('txn_MGmYKTl', 20, 'Uttara_Center', 'Pallabi', 78.00, '2025-03-04 11:55:59', NULL),
('txn_OGNFBhk', 22, 'Motijheel', 'ShewraPara', 83.00, '2025-03-23 23:43:27', NULL),
('txn_OVYBxiv', 22, 'Agargaon', 'Bijoy_Sharani', 21.00, '2025-03-23 22:48:02', NULL),
('txn_OzjUuHV', 22, 'Motijheel', 'ShewraPara', 83.00, '2025-03-23 23:50:00', NULL),
('txn_PworgQF', 22, 'Mirpur_11', 'Farmgate', 55.00, '2025-03-24 00:19:37', NULL),
('txn_QF25JmM', 22, 'Kazi_Para', 'Uttara_North', 33.00, '2025-04-10 18:03:01', NULL),
('txn_qIeMSvn', 22, 'Uttara_Center', 'ShewraPara', 56.00, '2025-03-04 12:11:34', NULL),
('txn_qvQi8Ib', 20, 'Uttara_South', 'Dhaka_University', 17.00, '2025-02-24 17:10:52', NULL),
('txn_sHhundK', 22, 'Mirpur_11', 'Secretariat', 27.00, '2025-03-07 19:25:36', NULL),
('txn_TBfaZlD', 22, 'Pallabi', 'Farmgate', 63.00, '2025-03-24 08:39:29', NULL),
('txn_TcZH8bi', 22, 'Mirpur_10', 'Uttara_North', 57.00, '2025-03-23 22:09:07', NULL),
('txn_TZjKkpi', 22, 'Uttara_North', 'Bijoy_Sharani', 43.00, '2025-03-06 13:08:33', NULL),
('txn_UF9pRgM', 22, 'Uttara_South', 'Dhaka_University', 17.00, '2025-04-10 17:50:45', NULL),
('txn_UJnRz2w', 22, 'Uttara_South', 'Motijheel', 36.00, '2025-04-10 17:52:28', NULL),
('txn_wYj10Qu', 22, 'Bijoy_Sharani', 'Shahbagh', 64.00, '2025-03-04 12:47:19', NULL),
('txn_XaFpGi6', 22, 'Uttara_North', 'Kazi_Para', 33.00, '2025-03-06 13:07:44', NULL),
('txn_xP0vNjF', 22, 'Uttara_South', 'Kazi_Para', 55.00, '2025-04-10 17:36:11', NULL),
('txn_Xq5248H', 22, 'Uttara_South', 'Secretariat', 12.00, '2025-04-13 17:28:33', '2025-04-14 17:28:33'),
('txn_YQypz2A', 22, 'Motijheel', 'ShewraPara', 83.00, '2025-03-23 23:42:25', NULL),
('txn_ywWz8S4', 22, 'Agargaon', 'Dhaka_University', 55.00, '2025-03-04 12:45:28', NULL),
('txn_Zgk4ceb', 22, 'Uttara_South', 'Dhaka_University', 17.00, '2025-03-07 19:15:20', NULL),
('txn_zI3vgXf', 22, 'Pallabi', 'Secretariat', 54.00, '2025-04-10 17:47:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `Address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `google_id`, `name`, `profile_image`, `phone`, `Address`) VALUES
(19, 'faiyanswapnil@gmail.com', '$2y$10$D5ayv7Y7C.p5RCpkmIyc4Om7nbDBPZmQdybmoVuHR/zRHSpQHdaXO', NULL, 'Faiyan Islam Swapnil', 'uploaded_profile_images/1f0e3dad99908345f7439f8ffabdffc4.jpg', '01711111111', 'Thanar Mor'),
(22, 'ibrahimsiddik007@gmail.com', '', '114164509074598760418', 'Ibrahim', 'uploaded_profile_images/b6d767d2f8ed5d21a44b0e5886680cb9.jpg', '01700000000', 'Dhaka,Bashundhara R/A,House-28'),
(24, 'kalamama@gmail.com', '$2y$10$vZt2M0GZaqKEEi683NMvPeRu0VXNzpcgScT9tDko9HpALXLs/CjIa', NULL, 'Kala Mama', 'images\\default_profile_account_photo.jpg', '01711111111', 'Uttara'),
(25, 'oliahmedsarker@gmail.com', '$2y$10$XqoQIJM5NZRZqfr8kY3rLeHEfe4DYIoFO0TxViW/PKs0oZHeGv.WC', NULL, 'Oli Ahmed', 'images\\default_profile_account_photo.jpg', '01753927290', 'Mirpur');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `demo_accounts`
--
ALTER TABLE `demo_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `metro_stations`
--
ALTER TABLE `metro_stations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ticket_routes`
--
ALTER TABLE `ticket_routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `demo_accounts`
--
ALTER TABLE `demo_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `metro_stations`
--
ALTER TABLE `metro_stations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ticket_routes`
--
ALTER TABLE `ticket_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
