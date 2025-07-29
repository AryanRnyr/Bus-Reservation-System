-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2025 at 09:01 PM
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
-- Database: `rn_bus_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bus`
--

CREATE TABLE `bus` (
  `id` int(30) NOT NULL,
  `name` varchar(250) NOT NULL,
  `bus_number` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 = inactive, 1 = active',
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bus`
--

INSERT INTO `bus` (`id`, `name`, `bus_number`, `capacity`, `status`, `date_updated`) VALUES
(1, 'RN Deluxe AC Bus', 'BA 1 PA 4031', 45, 1, '2024-12-02 20:19:23'),
(12, 'RN Deluxe Non-AC Bus', 'BA 1 PA 4030', 45, 1, '2024-12-02 20:21:35');

-- --------------------------------------------------------

--
-- Table structure for table `cancel_request`
--

CREATE TABLE `cancel_request` (
  `id` int(11) NOT NULL,
  `ticket_no` varchar(255) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `fare` varchar(30) NOT NULL,
  `seats` varchar(255) NOT NULL,
  `bus_name` varchar(255) NOT NULL,
  `departure_city` varchar(50) NOT NULL,
  `departure_time` varchar(255) NOT NULL,
  `arrival_city` varchar(50) NOT NULL,
  `arrival_time` varchar(255) NOT NULL,
  `requested_time` varchar(255) NOT NULL,
  `status` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cancel_request`
--

INSERT INTO `cancel_request` (`id`, `ticket_no`, `schedule_id`, `user_id`, `order_id`, `name`, `phone`, `fare`, `seats`, `bus_name`, `departure_city`, `departure_time`, `arrival_city`, `arrival_time`, `requested_time`, `status`) VALUES
(1, '1711739643249', 1, 17, 1, 'Aryan Rauniyar', '9840594031', '4', '1, 2', 'RN Deluxe AC Bus : BA 1 PA 4031', 'Kathmandu', '2025-02-17 06:30:00', 'Pokhara', '2025-02-17 15:30:00', '2025-02-16 00:15:18', 'CANCELED'),
(2, '1711739643818', 1, 17, 2, 'Ashok Rauniyar', '9840594031', '10', '41, 42, 43, 44, 45', 'RN Deluxe AC Bus : BA 1 PA 4031', 'Kathmandu', '2025-02-24 06:30:00', 'Pokhara', '2025-02-24 15:30:00', '2025-02-23 19:08:11', 'CANCELED'),
(3, '111740324854', 1, 1, 3, 'aryan', '-9840594031', '4', '3, 4', 'RN Deluxe AC Bus : BA 1 PA 4031', 'Kathmandu', '2025-02-24 06:30:00', 'Pokhara', '2025-02-24 15:30:00', '2025-02-23 21:22:59', 'CANCELED');

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `id` int(30) NOT NULL,
  `city` varchar(250) NOT NULL,
  `state` varchar(250) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0= inactive , 1= active',
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`id`, `city`, `state`, `status`, `date_updated`) VALUES
(13, 'Kathmandu', 'Bagmati', 1, '2024-11-22 15:05:37'),
(15, 'Birgunj', 'Parsa', 1, '2024-11-22 17:09:15'),
(16, 'Pokhara', 'Gandaki', 1, '2024-11-22 23:28:53'),
(19, 'Bhaktapur', 'Bagmati', 1, '2024-11-24 15:12:33');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `description`, `date`) VALUES
(1, 'Online Ticket Booking System Initiated', 'RN Bus Pvt. Ltd. has started online ticket booking system where customers can directly book tickets through online payment gateway.', '2024-12-01'),
(2, 'New Kathmandu-Pokhara Route Now Available', 'We are excited to announce our new daily service between Kathmandu and Pokhara. Book your seat today!', '2024-11-12'),
(3, 'New Bus Fleet Addition', 'We have added 5 new luxury buses to our fleet, offering enhanced comfort and safety on all routes.', '2024-11-01'),
(4, 'Ensuring Your Safety: New Health Guidelines', 'We have implemented new health and safety measures, including sanitization of buses after each trip, to ensure your comfort and well-being.', '2024-12-01'),
(5, 'Going Green: Eco-Friendly Buses', 'We’re proud to announce that all our new buses are now eco-friendly, reducing emissions and contributing to a cleaner environment.', '2024-12-01'),
(6, 'Best Travel Operator Award 2024', 'RN Bus Pvt. Ltd. was recognized as the Best Travel Operator of the Year at the 2024 Tourism Awards! Thank you for your continued support.', '2024-11-20');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `ticket_no` varchar(255) NOT NULL,
  `seats` varchar(255) NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `name` varchar(255) NOT NULL,
  `age` int(10) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `id_proof` varchar(255) NOT NULL,
  `id_details` text NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `ticket_no`, `seats`, `fare`, `name`, `age`, `gender`, `id_proof`, `id_details`, `payment_method`, `email`, `phone`, `status`, `user_id`, `schedule_id`) VALUES
(1, '1711739643249', '1, 2', 40000.00, 'Aryan Rauniyar', 20, 'male', 'Driving License', '987654123', 'Khalti', 'aryan.rauniyar12@gmail.com', '9840594031', 'CANCELED', 17, 1),
(2, '1711739643818', '41, 42, 43, 44, 45', 100000.00, 'Ashok Rauniyar', 56, 'male', 'National Identity Card', '984615', 'Esewa', 'aryan.rauniyar12@gmail.com', '9840594031', 'CANCELED', 17, 1),
(3, '111740324854', '3, 4', 40000.00, 'aryan', 10, 'male', 'Driving License', '123', 'Cash', 'aryan.rauniyar15@gmail.com', '-9840594031', 'CANCELED', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `schedule_list`
--

CREATE TABLE `schedule_list` (
  `id` int(30) NOT NULL,
  `bus_id` int(30) NOT NULL,
  `from_location` int(30) NOT NULL,
  `to_location` int(30) NOT NULL,
  `departure_time` datetime NOT NULL,
  `eta` datetime NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `availability` int(11) NOT NULL,
  `price` text NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_list`
--

INSERT INTO `schedule_list` (`id`, `bus_id`, `from_location`, `to_location`, `departure_time`, `eta`, `status`, `availability`, `price`, `date_updated`) VALUES
(1, 1, 13, 16, '2025-02-24 06:30:00', '2025-02-24 15:30:00', 1, 45, '200', '2025-02-23 15:38:41'),
(2, 12, 13, 16, '2025-02-17 06:00:00', '2025-02-17 15:00:00', 1, 45, '100', '2025-02-15 17:52:16'),
(3, 1, 13, 15, '2025-02-17 18:00:00', '2025-02-18 04:30:00', 1, 45, '300', '2025-02-15 17:53:11'),
(4, 12, 13, 15, '2025-02-17 18:30:00', '2025-02-18 05:00:00', 1, 45, '150', '2025-02-15 19:35:39');

-- --------------------------------------------------------

--
-- Table structure for table `seat_reservation`
--

CREATE TABLE `seat_reservation` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `seat_number` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ticket_no` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `Age` int(11) NOT NULL,
  `Gender` varchar(255) NOT NULL,
  `id_proof` varchar(255) NOT NULL,
  `id_details` varchar(255) NOT NULL,
  `total_fare` varchar(255) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `temporary_booking_time` datetime NOT NULL,
  `date_reserved` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `phoneno` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_role` varchar(255) NOT NULL,
  `user_image` text NOT NULL,
  `status` int(11) NOT NULL,
  `activation_code` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `firstname`, `lastname`, `phoneno`, `email`, `user_role`, `user_image`, `status`, `activation_code`) VALUES
(1, 'admin', '$2y$10$vgMJ8U4WYA9a7E8Y2t9cBeBkcchCrwRyTgvHXSlYvhiVlaAoGn0dC', 'Admin', 'Admin', '', 'admin@admin.com', 'admin', 'ppr.png', 1, ''),
(17, 'aryan', '$2y$10$a3930W/1eQA5TqMy3SUyg.eSIll4Xmn46/oEsZUUvf/DcG9AzoGkS', 'Aryan', 'Rauniyar', '9840594031', 'aryan.rauniyar12@gmail.com', 'customer', '1689407385229.png', 1, 'activated'),
(40, 'aryantest', '$2y$10$bXbZb8vURUtMU0PSZDBANusCbgvfc7PAoBu9NIVyhTqL2iiSGo3l2', 'Aryan', 'Rauniyar', '9840594031', 'aryan.rauniyar15@gmail.com', 'customer', 'user_default.jpg', 2, 'activated');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bus`
--
ALTER TABLE `bus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cancel_request`
--
ALTER TABLE `cancel_request`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedule_list`
--
ALTER TABLE `schedule_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `seat_reservation`
--
ALTER TABLE `seat_reservation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bus`
--
ALTER TABLE `bus`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `cancel_request`
--
ALTER TABLE `cancel_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `schedule_list`
--
ALTER TABLE `schedule_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `seat_reservation`
--
ALTER TABLE `seat_reservation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `seat_reservation`
--
ALTER TABLE `seat_reservation`
  ADD CONSTRAINT `seat_reservation_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedule_list` (`id`),
  ADD CONSTRAINT `seat_reservation_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
