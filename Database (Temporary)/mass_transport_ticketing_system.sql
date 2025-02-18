-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 18, 2025 at 04:06 PM
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
-- Table structure for table `stations`
--

CREATE TABLE `stations` (
  `id` int(11) NOT NULL,
  `s_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stations`
--

INSERT INTO `stations` (`id`, `s_name`) VALUES
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
  `status` enum('pending','completed','canceled') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `start_location`, `end_location`, `fare`, `status`, `created_at`) VALUES
('1', 12, 'Uttara_South', 'Secretariat', 12.00, '', '2025-02-06 12:40:30'),
('10', 12, 'Mirpur_10', 'Secretariat', 91.00, '', '2025-02-06 12:44:58'),
('11', 12, 'Mirpur_10', 'Secretariat', 91.00, '', '2025-02-06 12:45:01'),
('12', 12, 'Mirpur_10', 'Secretariat', 91.00, '', '2025-02-06 12:45:04'),
('13', 12, 'Mirpur_10', 'Secretariat', 91.00, '', '2025-02-06 12:45:07'),
('14', 12, 'Mirpur_10', 'Secretariat', 91.00, '', '2025-02-06 12:46:30'),
('15', 12, 'Mirpur_10', 'Secretariat', 91.00, '', '2025-02-06 12:46:36'),
('16', 12, 'Mirpur_10', 'Secretariat', 91.00, '', '2025-02-06 12:46:38'),
('17', 12, 'Mirpur_10', 'Secretariat', 91.00, '', '2025-02-06 12:46:40'),
('18', 12, 'Mirpur_10', 'Secretariat', 91.00, '', '2025-02-06 12:47:08'),
('19', 12, 'Mirpur_10', 'Secretariat', 91.00, '', '2025-02-06 12:47:10'),
('2', 12, 'Uttara_South', 'Secretariat', 12.00, '', '2025-02-06 12:41:16'),
('20', 12, 'Mirpur_10', 'Secretariat', 91.00, '', '2025-02-06 12:47:14'),
('21', 12, 'Mirpur_10', 'Secretariat', 91.00, '', '2025-02-06 12:47:19'),
('22', 12, 'Pallabi', 'Dhaka_University', 59.00, '', '2025-02-06 12:49:31'),
('23', 12, 'Pallabi', 'Dhaka_University', 59.00, '', '2025-02-06 12:49:34'),
('24', 12, 'Pallabi', 'Dhaka_University', 59.00, '', '2025-02-06 12:49:37'),
('25', 12, 'Pallabi', 'Dhaka_University', 59.00, '', '2025-02-06 12:49:42'),
('26', 12, 'Pallabi', 'Dhaka_University', 59.00, '', '2025-02-06 12:51:19'),
('27', 12, 'Pallabi', 'Dhaka_University', 59.00, '', '2025-02-06 12:51:23'),
('28', 12, 'Mirpur_10', 'Uttara_North', 57.00, '', '2025-02-06 12:57:06'),
('29', 12, 'Uttara_North', 'Dhaka_University', 83.00, '', '2025-02-06 12:57:48'),
('3', 12, 'Uttara_South', 'Secretariat', 12.00, '', '2025-02-06 12:41:26'),
('30', 12, 'Uttara_North', 'Dhaka_University', 83.00, '', '2025-02-06 12:58:46'),
('31', 12, 'Uttara_North', 'Motijheel', 32.00, '', '2025-02-06 12:59:08'),
('32', 12, 'Uttara_North', 'Motijheel', 32.00, '', '2025-02-06 12:59:46'),
('33', 12, 'Uttara_North', 'Motijheel', 32.00, '', '2025-02-06 13:01:16'),
('34', 12, 'Uttara_North', 'Motijheel', 32.00, '', '2025-02-06 13:01:32'),
('35', 12, 'Uttara_North', 'Motijheel', 32.00, '', '2025-02-06 13:02:37'),
('36', 12, 'Uttara_North', 'Motijheel', 32.00, '', '2025-02-06 13:03:15'),
('37', 12, 'Uttara_North', 'Motijheel', 32.00, '', '2025-02-06 13:03:36'),
('38', 12, 'Mirpur_11', 'Dhaka_University', 87.00, '', '2025-02-06 13:06:26'),
('39', 12, 'Uttara_South', 'Motijheel', 36.00, '', '2025-02-06 13:24:29'),
('4', 12, 'Uttara_South', 'Secretariat', 12.00, '', '2025-02-06 12:42:37'),
('40', 12, 'Mirpur_10', 'Agargaon', 82.00, '', '2025-02-06 13:26:20'),
('41', 12, 'Agargaon', 'Uttara_North', 21.00, '', '2025-02-06 13:28:51'),
('42', 12, 'Farmgate', 'Bijoy_Sharani', 31.00, '', '2025-02-06 13:31:28'),
('43', 12, 'Farmgate', 'Bijoy_Sharani', 31.00, '', '2025-02-06 13:32:58'),
('5', 12, 'Uttara_South', 'Secretariat', 12.00, '', '2025-02-06 12:42:40'),
('6', 12, 'Pallabi', 'Uttara_North', 57.00, '', '2025-02-06 12:42:51'),
('7', 12, 'Pallabi', 'Uttara_North', 57.00, '', '2025-02-06 12:42:55'),
('8', 12, 'Pallabi', 'Uttara_North', 57.00, '', '2025-02-06 12:42:58'),
('9', 12, 'Pallabi', 'Uttara_North', 57.00, '', '2025-02-06 12:44:46'),
('txn_2mgvspC', 12, 'Bijoy_Sharani', 'Uttara_North', 43.00, '', '2025-02-06 13:50:52'),
('txn_3s1mzSR', 12, 'ShewraPara', 'Dhaka_University', 67.00, '', '2025-02-06 14:01:00'),
('txn_67a500a', 12, 'Farmgate', 'Dhaka_University', 73.00, '', '2025-02-06 13:34:22'),
('txn_67a5010', 12, 'Kawran_Bazar', 'Shahbagh', 83.00, '', '2025-02-06 13:35:50'),
('txn_A9a7nBZ', 12, 'Mirpur_11', 'Dhaka_University', 87.00, '', '2025-02-06 14:13:13'),
('txn_BXnSTfP', 12, 'ShewraPara', 'Dhaka_University', 67.00, '', '2025-02-06 14:00:44'),
('txn_C6vFazY', 13, 'Uttara_North', 'Mirpur_10', 57.00, '', '2025-02-08 10:50:20'),
('txn_CGZrLAN', 13, 'Kazi_Para', 'Bijoy_Sharani', 73.00, '', '2025-02-09 10:54:48'),
('txn_DcBb1fx', 13, 'Pallabi', 'Farmgate', 63.00, '', '2025-02-08 09:46:23'),
('txn_dh0ngYm', 12, 'Agargaon', 'Dhaka_University', 55.00, '', '2025-02-06 13:46:38'),
('txn_DT4IJ0W', 19, 'Kazi_Para', 'Uttara_North', 33.00, '', '2025-02-08 14:09:41'),
('txn_EtgSkGZ', 13, 'Uttara_North', 'Pallabi', 57.00, '', '2025-02-08 07:19:34'),
('txn_fhPC1wt', 12, 'Mirpur_11', 'Dhaka_University', 87.00, '', '2025-02-06 14:06:15'),
('txn_gNkDp2b', 12, 'Dhaka_University', 'Kawran_Bazar', 42.00, '', '2025-02-06 13:41:36'),
('txn_HtAViQh', 12, 'Farmgate', 'Uttara_North', 67.00, '', '2025-02-06 13:59:00'),
('txn_i8x2cgV', 12, 'Agargaon', 'Uttara_North', 21.00, '', '2025-02-06 13:55:01'),
('txn_Mv4oNGA', 13, 'Uttara_North', 'Bijoy_Sharani', 43.00, '', '2025-02-08 07:20:24'),
('txn_P9LVYIq', 12, 'ShewraPara', 'Dhaka_University', 67.00, '', '2025-02-06 14:00:33'),
('txn_q2SDIsd', 13, 'Uttara_North', 'ShewraPara', 95.00, '', '2025-02-08 10:11:01'),
('txn_q4IKZw2', 13, 'Mirpur_11', 'Kazi_Para', 10.00, '', '2025-02-09 10:21:57'),
('txn_RNkm8fU', 12, 'Kazi_Para', 'Motijheel', 52.00, '', '2025-02-06 13:57:15'),
('txn_TiHAzve', 13, 'Uttara_North', 'Agargaon', 21.00, '', '2025-02-08 10:41:56'),
('txn_TjzXUl0', 12, 'Shahbagh', 'Uttara_North', 58.00, '', '2025-02-06 13:55:33'),
('txn_UZDsVGw', 12, 'Agargaon', 'Dhaka_University', 55.00, '', '2025-02-06 13:48:38'),
('txn_xpS7QMF', 12, 'Pallabi', 'Secretariat', 54.00, '', '2025-02-06 13:44:58'),
('txn_ZCsl8MI', 12, 'Agargaon', 'Dhaka_University', 55.00, '', '2025-02-06 13:48:54'),
('txn_zKJf6nZ', 12, 'Kawran_Bazar', 'Dhaka_University', 42.00, '', '2025-02-06 13:38:58');

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
(20, 'ibrahimsiddik007@gmail.com', '', '114164509074598760418', 'Ibrahim', 'uploaded_profile_images/98f13708210194c475687be6106a3b84.jpg', '01700000000', 'Dhaka,Bashundhara R/A,House-28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `stations`
--
ALTER TABLE `stations`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `stations`
--
ALTER TABLE `stations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `ticket_routes`
--
ALTER TABLE `ticket_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
