-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2025 at 06:46 AM
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
-- Database: `greentrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `registeredacc`
--

CREATE TABLE `registeredacc` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(20) NOT NULL,
  `role` varchar(50) DEFAULT 'Volunteer',
  `is_active` tinyint(1) DEFAULT 1,
  `account_type` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `group_members` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registeredacc`
--

INSERT INTO `registeredacc` (`user_id`, `username`, `password`, `role`, `is_active`, `account_type`, `email`, `group_members`) VALUES
(1, 'Admin1', 'admin123', 'Admin', 1, ' ', 'admin123@gmail.com', ' '),
(2, 'Volunteer1', 'volunteer123', 'Volunteer', 1, 'individual', 'volunteer123@gmail.com', ''),
(3, 'Volunteer2', '$2y$10$THcMn86l0OT0.', 'Volunteer', 1, 'Individual', 'Newvolunteer@gmail.com', ''),
(4, 'AD24001', '$2y$10$3MJw/zk7cgy47', 'Volunteer', 1, 'Individual', 'jasmine_suarez38@yahoo.com', ''),
(5, 'VO24001', '$2y$10$QZQbYwgctqp.g', 'Volunteer', 1, 'Individual', 'suarezjohnmark65@gmail.com', ''),
(6, 'VO24002', '$2y$10$mkTNMBP/oNwR7', 'Volunteer', 1, 'Individual', 'suarezjohnmark65@gmail.com', ''),
(7, 'EM24002', '$2y$10$LbODILF9Q11Cp', 'Volunteer', 1, 'Individual', 'Newvolunteer@gmail.com', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `registeredacc`
--
ALTER TABLE `registeredacc`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `registeredacc`
--
ALTER TABLE `registeredacc`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
