-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2025 at 09:29 AM
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
-- Database: `mdm`
--

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `car_code` varchar(10) NOT NULL,
  `engine_number` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `initial_km` decimal(10,1) DEFAULT 0.0,
  `initial_fuel` int(11) DEFAULT 100,
  `status` enum('standby','cleaning','cleaned','pod_lineup','on_drive','returned','hotel','out_of_service','under_inspection') DEFAULT 'standby',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `event_id`, `car_code`, `engine_number`, `name`, `model`, `color`, `image_url`, `initial_km`, `initial_fuel`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'A1', 'ENG001234', 'Tata Punch', 'Creative 1.2', 'White', NULL, 0.0, 100, 'standby', 1, '2025-12-26 08:27:57', '2025-12-26 08:27:57'),
(2, 1, 'A2', 'ENG001235', 'Tata Punch', 'Creative 1.2', 'Red', NULL, 0.0, 100, 'standby', 1, '2025-12-26 08:27:57', '2025-12-26 08:27:57'),
(3, 1, 'B1', 'ENG002001', 'Tata Nexon', 'EV Max', 'Blue', NULL, 0.0, 100, 'standby', 1, '2025-12-26 08:27:57', '2025-12-26 08:27:57'),
(4, 1, 'B2', 'ENG002002', 'Tata Nexon', 'EV Max', 'White', NULL, 0.0, 100, 'standby', 1, '2025-12-26 08:27:57', '2025-12-26 08:27:57'),
(5, 1, 'C1', 'ENG003001', 'Tata Harrier', 'XZ+', 'Black', NULL, 0.0, 100, 'standby', 1, '2025-12-26 08:27:57', '2025-12-26 08:27:57'),
(6, 1, 'C2', 'ENG003002', 'Tata Harrier', 'XZ+', 'Grey', NULL, 0.0, 100, 'standby', 1, '2025-12-26 08:27:57', '2025-12-26 08:27:57');

-- --------------------------------------------------------

--
-- Table structure for table `car_assignments`
--

CREATE TABLE `car_assignments` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `influencer_id` int(11) DEFAULT NULL,
  `promoter_id` int(11) DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL,
  `scheduled_time` time DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_assignments`
--

INSERT INTO `car_assignments` (`id`, `event_id`, `car_id`, `influencer_id`, `promoter_id`, `scheduled_date`, `scheduled_time`, `is_completed`, `created_at`) VALUES
(1, 1, 1, 1, 3, '2024-12-21', NULL, 0, '2025-12-26 08:27:57'),
(2, 1, 2, 2, 3, '2024-12-21', NULL, 0, '2025-12-26 08:27:57'),
(3, 1, 3, 3, 4, '2024-12-22', NULL, 0, '2025-12-26 08:27:57'),
(4, 1, 4, 4, 4, '2024-12-22', NULL, 0, '2025-12-26 08:27:57'),
(5, 1, 5, 5, 5, '2024-12-23', NULL, 0, '2025-12-26 08:27:57'),
(6, 1, 6, 6, 5, '2024-12-23', NULL, 0, '2025-12-26 08:27:57');

-- --------------------------------------------------------

--
-- Table structure for table `car_logs`
--

CREATE TABLE `car_logs` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `promoter_id` int(11) DEFAULT NULL,
  `influencer_id` int(11) DEFAULT NULL,
  `pr_firm_id` int(11) DEFAULT NULL,
  `log_type` enum('status_change','exit','return','emergency','damage','note') NOT NULL,
  `previous_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `journalist_name` varchar(150) DEFAULT NULL,
  `journalist_outlet` varchar(150) DEFAULT NULL,
  `journalist_phone` varchar(20) DEFAULT NULL,
  `km_reading` decimal(10,1) DEFAULT NULL,
  `fuel_level` int(11) DEFAULT NULL,
  `exit_time` datetime DEFAULT NULL,
  `return_time` datetime DEFAULT NULL,
  `has_damage` tinyint(1) DEFAULT 0,
  `damage_description` text DEFAULT NULL,
  `photo_urls` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `client_name` varchar(150) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('upcoming','active','completed') DEFAULT 'upcoming',
  `logo_url` varchar(255) DEFAULT NULL,
  `theme_color` varchar(7) DEFAULT '#080808',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `name`, `client_name`, `location`, `start_date`, `end_date`, `status`, `logo_url`, `theme_color`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Tata Punch Launch', 'Tata Motors', 'Mumbai', '2024-12-20', '2024-12-25', 'active', NULL, '#080808', NULL, '2025-12-26 08:27:57', '2025-12-26 08:27:57');

-- --------------------------------------------------------

--
-- Table structure for table `event_promoters`
--

CREATE TABLE `event_promoters` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `promoter_id` int(11) NOT NULL,
  `assigned_cars` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `car_log_id` int(11) DEFAULT NULL,
  `event_id` int(11) NOT NULL,
  `promoter_id` int(11) DEFAULT NULL,
  `journalist_name` varchar(150) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `experience` text DEFAULT NULL,
  `strong_points` text DEFAULT NULL,
  `weak_points` text DEFAULT NULL,
  `concerns` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback_forms`
--

CREATE TABLE `feedback_forms` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `form_name` varchar(150) NOT NULL,
  `fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fields`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `influencers`
--

CREATE TABLE `influencers` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `pr_firm_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `outlet` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `influencers`
--

INSERT INTO `influencers` (`id`, `event_id`, `pr_firm_id`, `name`, `outlet`, `phone`, `email`, `created_at`) VALUES
(1, 1, 1, 'Ravi Auto Review', 'YouTube', '9800000001', NULL, '2025-12-26 08:27:57'),
(2, 1, 1, 'Neha Car Tips', 'Instagram', '9800000002', NULL, '2025-12-26 08:27:57'),
(3, 1, 2, 'AutoCar India', 'Magazine', '9800000003', NULL, '2025-12-26 08:27:57'),
(4, 1, 2, 'Car Dekho Team', 'Website', '9800000004', NULL, '2025-12-26 08:27:57'),
(5, 1, 3, 'MotorOctane', 'YouTube', '9800000005', NULL, '2025-12-26 08:27:57'),
(6, 1, 3, 'ZigWheels Reporter', 'Website', '9800000006', NULL, '2025-12-26 08:27:57');

-- --------------------------------------------------------

--
-- Table structure for table `promoter_attendance`
--

CREATE TABLE `promoter_attendance` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `promoter_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `status` enum('present','absent','late','half_day') DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promoter_pr_firms`
--

CREATE TABLE `promoter_pr_firms` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `promoter_id` int(11) NOT NULL,
  `pr_firm_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promoter_pr_firms`
--

INSERT INTO `promoter_pr_firms` (`id`, `event_id`, `promoter_id`, `pr_firm_id`, `created_at`) VALUES
(1, 1, 3, 1, '2025-12-26 08:27:57'),
(2, 1, 4, 2, '2025-12-26 08:27:57'),
(3, 1, 5, 3, '2025-12-26 08:27:57');

-- --------------------------------------------------------

--
-- Table structure for table `pr_firms`
--

CREATE TABLE `pr_firms` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pr_firms`
--

INSERT INTO `pr_firms` (`id`, `name`, `contact_person`, `phone`, `email`, `is_active`, `created_at`) VALUES
(1, 'MediaWorks PR', 'Rahul Sharma', '9876543210', 'rahul@mediaworks.com', 1, '2025-12-26 08:27:57'),
(2, 'AutoPR Agency', 'Priya Patel', '9876543211', 'priya@autopr.com', 1, '2025-12-26 08:27:57'),
(3, 'Velocity Communications', 'Amit Kumar', '9876543212', 'amit@velocity.com', 1, '2025-12-26 08:27:57');

-- --------------------------------------------------------

--
-- Table structure for table `pr_firm_cars`
--

CREATE TABLE `pr_firm_cars` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `pr_firm_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pr_firm_cars`
--

INSERT INTO `pr_firm_cars` (`id`, `event_id`, `pr_firm_id`, `car_id`, `created_at`) VALUES
(1, 1, 1, 1, '2025-12-26 08:27:57'),
(2, 1, 1, 2, '2025-12-26 08:27:57'),
(3, 1, 2, 3, '2025-12-26 08:27:57'),
(4, 1, 2, 4, '2025-12-26 08:27:57'),
(5, 1, 3, 5, '2025-12-26 08:27:57'),
(6, 1, 3, 6, '2025-12-26 08:27:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','client','promoter','cleaning_staff') NOT NULL DEFAULT 'promoter',
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `avatar`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@cloudplay.com', 'admin123', 'superadmin', NULL, NULL, 1, '2025-12-26 08:27:57', '2025-12-26 08:27:57'),
(2, 'Demo Client', 'client@demo.com', 'client123', 'client', NULL, NULL, 1, '2025-12-26 08:27:57', '2025-12-26 08:27:57'),
(3, 'Demo Promoter', 'promoter@demo.com', 'promoter123', 'promoter', NULL, NULL, 1, '2025-12-26 08:27:57', '2025-12-26 08:27:57'),
(4, 'John Promoter', 'john@demo.com', 'john123', 'promoter', NULL, NULL, 1, '2025-12-26 08:27:57', '2025-12-26 08:27:57'),
(5, 'Sarah Promoter', 'sarah@demo.com', 'sarah123', 'promoter', NULL, NULL, 1, '2025-12-26 08:27:57', '2025-12-26 08:27:57'),
(6, 'Cleaning Staff', 'cleaning@demo.com', 'cleaning123', 'cleaning_staff', NULL, NULL, 1, '2025-12-26 08:27:57', '2025-12-26 08:27:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_car_code_per_event` (`event_id`,`car_code`),
  ADD KEY `idx_cars_event` (`event_id`),
  ADD KEY `idx_cars_status` (`status`),
  ADD KEY `idx_cars_code` (`car_code`);

--
-- Indexes for table `car_assignments`
--
ALTER TABLE `car_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `influencer_id` (`influencer_id`),
  ADD KEY `promoter_id` (`promoter_id`),
  ADD KEY `idx_car_assignments_event` (`event_id`),
  ADD KEY `idx_car_assignments_car` (`car_id`);

--
-- Indexes for table `car_logs`
--
ALTER TABLE `car_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `promoter_id` (`promoter_id`),
  ADD KEY `influencer_id` (`influencer_id`),
  ADD KEY `pr_firm_id` (`pr_firm_id`),
  ADD KEY `idx_car_logs_car` (`car_id`),
  ADD KEY `idx_car_logs_event` (`event_id`),
  ADD KEY `idx_car_logs_type` (`log_type`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `event_promoters`
--
ALTER TABLE `event_promoters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`event_id`,`promoter_id`),
  ADD KEY `promoter_id` (`promoter_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_log_id` (`car_log_id`),
  ADD KEY `promoter_id` (`promoter_id`),
  ADD KEY `idx_feedback_event` (`event_id`);

--
-- Indexes for table `feedback_forms`
--
ALTER TABLE `feedback_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `influencers`
--
ALTER TABLE `influencers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_influencers_event` (`event_id`),
  ADD KEY `idx_influencers_pr_firm` (`pr_firm_id`);

--
-- Indexes for table `promoter_attendance`
--
ALTER TABLE `promoter_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`event_id`,`promoter_id`,`date`),
  ADD KEY `promoter_id` (`promoter_id`),
  ADD KEY `idx_attendance_event` (`event_id`),
  ADD KEY `idx_attendance_date` (`date`);

--
-- Indexes for table `promoter_pr_firms`
--
ALTER TABLE `promoter_pr_firms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_promoter_pr_firm` (`event_id`,`promoter_id`,`pr_firm_id`),
  ADD KEY `pr_firm_id` (`pr_firm_id`),
  ADD KEY `idx_promoter_pr_firms_event` (`event_id`),
  ADD KEY `idx_promoter_pr_firms_promoter` (`promoter_id`);

--
-- Indexes for table `pr_firms`
--
ALTER TABLE `pr_firms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pr_firm_cars`
--
ALTER TABLE `pr_firm_cars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pr_firm_car` (`event_id`,`car_id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `idx_pr_firm_cars_event` (`event_id`),
  ADD KEY `idx_pr_firm_cars_pr_firm` (`pr_firm_id`);

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
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `car_assignments`
--
ALTER TABLE `car_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `car_logs`
--
ALTER TABLE `car_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `event_promoters`
--
ALTER TABLE `event_promoters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback_forms`
--
ALTER TABLE `feedback_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `influencers`
--
ALTER TABLE `influencers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `promoter_attendance`
--
ALTER TABLE `promoter_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promoter_pr_firms`
--
ALTER TABLE `promoter_pr_firms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pr_firms`
--
ALTER TABLE `pr_firms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pr_firm_cars`
--
ALTER TABLE `pr_firm_cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `cars_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `car_assignments`
--
ALTER TABLE `car_assignments`
  ADD CONSTRAINT `car_assignments_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `car_assignments_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `car_assignments_ibfk_3` FOREIGN KEY (`influencer_id`) REFERENCES `influencers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `car_assignments_ibfk_4` FOREIGN KEY (`promoter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `car_logs`
--
ALTER TABLE `car_logs`
  ADD CONSTRAINT `car_logs_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `car_logs_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `car_logs_ibfk_3` FOREIGN KEY (`promoter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `car_logs_ibfk_4` FOREIGN KEY (`influencer_id`) REFERENCES `influencers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `car_logs_ibfk_5` FOREIGN KEY (`pr_firm_id`) REFERENCES `pr_firms` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `event_promoters`
--
ALTER TABLE `event_promoters`
  ADD CONSTRAINT `event_promoters_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_promoters_ibfk_2` FOREIGN KEY (`promoter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`car_log_id`) REFERENCES `car_logs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`promoter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `feedback_forms`
--
ALTER TABLE `feedback_forms`
  ADD CONSTRAINT `feedback_forms_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_forms_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `influencers`
--
ALTER TABLE `influencers`
  ADD CONSTRAINT `influencers_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `influencers_ibfk_2` FOREIGN KEY (`pr_firm_id`) REFERENCES `pr_firms` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `promoter_attendance`
--
ALTER TABLE `promoter_attendance`
  ADD CONSTRAINT `promoter_attendance_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promoter_attendance_ibfk_2` FOREIGN KEY (`promoter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `promoter_pr_firms`
--
ALTER TABLE `promoter_pr_firms`
  ADD CONSTRAINT `promoter_pr_firms_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promoter_pr_firms_ibfk_2` FOREIGN KEY (`promoter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promoter_pr_firms_ibfk_3` FOREIGN KEY (`pr_firm_id`) REFERENCES `pr_firms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pr_firm_cars`
--
ALTER TABLE `pr_firm_cars`
  ADD CONSTRAINT `pr_firm_cars_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pr_firm_cars_ibfk_2` FOREIGN KEY (`pr_firm_id`) REFERENCES `pr_firms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pr_firm_cars_ibfk_3` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
