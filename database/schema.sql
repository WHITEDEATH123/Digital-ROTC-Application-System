-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2025 at 05:54 AM
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
-- Database: `rotc_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `place_of_birth` varchar(100) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `complexion` varchar(50) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `block` varchar(10) DEFAULT NULL,
  `course` varchar(50) DEFAULT NULL,
  `army_nstp` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `emergency_contact_person` varchar(100) DEFAULT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `emergency_contact_number` varchar(20) DEFAULT NULL,
  `ms` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `ms_remarks` text DEFAULT NULL,
  `advance_course` tinyint(1) DEFAULT 0,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('not_enrolled','submitted','resubmitted','approved','rejected') DEFAULT 'not_enrolled',
  `admin_remarks` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `resubmitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `first_name`, `middle_name`, `last_name`, `age`, `religion`, `date_of_birth`, `place_of_birth`, `height`, `weight`, `complexion`, `blood_type`, `block`, `course`, `army_nstp`, `address`, `phone`, `email`, `father_name`, `father_occupation`, `mother_name`, `mother_occupation`, `emergency_contact_person`, `relationship`, `emergency_contact_number`, `ms`, `semester`, `school_year`, `grade`, `ms_remarks`, `advance_course`, `photo_path`, `status`, `admin_remarks`, `submitted_at`, `resubmitted_at`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, '2025-1200', 'Jonh', 'Saga', 'Q Lopez', 23, 'catholic', '2001-10-16', 'sfsdfssf', 6.00, 23.00, '23', 'B', 'A', 'BSIT', 'ROTC', 'San Francisco Dist Pagadian City Zamboanga Del Sur Philippines', '09632441878', 'leojohnpro4@gmail.com', 'fdfgdfgdggh', 'ghhiuiuuoiuij', 'ugjhbjkjj', 'hjuhjhijii', 'Jonh Q Lopez', 'father', '09632441878', 'fjhjkll', '1', '2023', '89', 'pass', 1, '../uploads/photos/10a6dfd5f75c470937ea2b6c7881a2b898c8c6cd.png', 'approved', NULL, '2025-10-18 00:24:09', NULL, '2025-10-18 00:29:13', '2025-10-18 00:24:09', '2025-10-18 00:29:13');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `data` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student') NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `password`, `role`, `name`, `email`, `created_at`, `updated_at`) VALUES
('2025-1200', '$2y$10$b9kVcQC6s6CTrxyWjwsGquH1HRwH0vJcbBgdlL7C3LibTHxivwoYy', 'student', '2025-1200', NULL, '2025-10-17 15:49:13', '2025-10-17 15:49:13'),
('admin', '$2y$10$eNq1ux5eQLXvWWVeXe4R7OVrb0mH0lrBvjLxNIfXm7Wsbh4MSDfem', 'admin', 'Administrator', 'admin@rotc.edu', '2025-10-17 14:53:24', '2025-10-17 15:05:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enrollments_student_id` (`student_id`),
  ADD KEY `idx_enrollments_status` (`status`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sessions_user_id` (`user_id`),
  ADD KEY `idx_sessions_expires` (`expires_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
