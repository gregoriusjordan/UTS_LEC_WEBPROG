-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2024 at 09:15 PM
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
-- Database: `utslec`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `max_participants` int(11) NOT NULL,
  `registered_participants` int(11) NOT NULL,
  `status` enum('open','closed','canceled') DEFAULT 'open',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `banner` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `title`, `description`, `event_date`, `event_time`, `location`, `max_participants`, `registered_participants`, `status`, `image`, `created_at`, `banner`) VALUES
(1, 'sdsad', 'sadasd', '2024-11-01', '18:16:00', 'asdsadas', 200, 1, 'open', '67010417f0a84.jpg', '2024-10-05 09:17:11', '67010417f0cf3_banner.jpg'),
(2, 'kucing', 'keren', '2024-10-24', '21:52:00', 'kuningan', 100, 1, 'open', '671a432d056f5.png', '2024-10-24 12:53:01', '671a432d08acb_banner.png'),
(3, 'kucing', 'keren', '2024-10-24', '21:52:00', 'kuningan', 100, 0, 'open', '671a47c9aa88f.png', '2024-10-24 13:12:41', '671a47c9ac0ac_banner.png'),
(4, 'eventser', 'dwa', '2024-10-24', '20:20:00', 'kuningan', 12, 0, 'open', '671a483826970.png', '2024-10-24 13:14:32', '671a483828749_banner.png'),
(5, 'kok', 'gt', '2024-10-24', '21:14:00', '1', 1, 0, 'open', '', '2024-10-24 13:15:48', '671a488452fca_banner.PNG'),
(6, 'daw', 'wda', '2024-10-24', '20:17:00', 'dawd', 1, 0, 'open', '', '2024-10-24 13:16:06', '671a48967bee6_banner.PNG'),
(7, 'wad', 'awd', '2024-11-07', '20:21:00', 'dwad', 11, 0, 'open', '', '2024-10-24 13:16:23', '671a48a7aa213_banner.png'),
(8, 'kurcaci makan ayam kuki', 'yah begitulah ceritanya', '2024-11-02', '06:24:00', 'kuningan', 100, 1, 'open', '', '2024-10-24 18:25:15', '671a910bab786_banner.png');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`registration_id`, `id`, `event_id`, `registered_at`) VALUES
(2, 2, 1, '2024-10-05 09:35:21'),
(3, 3, 1, '2024-10-24 09:33:28'),
(4, 3, 2, '2024-10-24 18:01:56'),
(5, 3, 3, '2024-10-24 18:01:58'),
(6, 3, 4, '2024-10-24 18:02:02'),
(7, 3, 5, '2024-10-24 18:02:09'),
(8, 7, 2, '2024-10-24 18:15:25'),
(9, 7, 1, '2024-10-24 18:16:05'),
(10, 3, 8, '2024-10-24 18:26:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) NOT NULL,
  `reset_token` varchar(255) NOT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `profile_picture`, `reset_token`, `reset_expiry`) VALUES
(1, 'asd', 'admin@example.com', '$2y$10$GimY/1KFfTk1.7OAi25R4uG3b8zQl0ZDLfodih5SrI6VZb3z07XtK', 'admin', '2024-10-05 09:16:36', '', '', NULL),
(2, 'asds', 'admin1@example.com', '$2y$10$qWu0DhdihZlF9nkO4vK1TevlUXqgfShaYTuYYHce0qa8kDueQeZSO', 'user', '2024-10-05 09:22:01', '', '', NULL),
(3, 'greg', 'gregoriusjordan@gmail.com', '$2y$10$9rgPlLP9LyVOq1/ZLPEuP.1j2zg8/H0YUe1wX7SHK1/TwKKEy.aCy', 'user', '2024-10-24 09:19:00', '', 'b8c634c7c2b2612b141e101d924d2daea3c950df4f61dcc0169354db80191f38e2fe624e157cf79d9ede9797f713abb4c0ff', '2024-10-24 20:24:26'),
(4, 'greg', 'greg@gmail.com', '$2y$10$LeUAFFYurYn9feqfyT0R.u8c/MLkQQIjRrs8RjZuEb6IKOncO5rVC', 'admin', '2024-10-24 12:50:34', '', '', NULL),
(6, 'gregs', 'greg1@gmail.com', '$2y$10$RxRMAW3KiMI3xz0vU3wW6eeuj/xcxl23zp2Jg0JSMwqBFsgPSQ5nS', 'admin', '2024-10-24 12:51:20', '', '', NULL),
(7, 'Gregor', 'roastedpotato1375@gmail.com', '$2y$10$fzuMxxrl1UIrtDCATekJren7G9ljE1DyZHmYJfvXhoJI9AgcPnhey', 'user', '2024-10-24 17:33:30', '', 'c656ee566a64b06920faaa0dc50dcddbe099f0ce52cb56592c9a14a39710cb5cad2a2e74a0717e80f58c9e846570e8ef5251', '2024-10-24 20:53:04'),
(8, 'gregs', 'admin@gmail.com', '$2y$10$V.ysgDD1tTdmzVWwhfewO.lLp1AkSoHVuGKAZuLrIwsQCuzEYyFZG', 'admin', '2024-10-24 18:21:52', '', '', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD KEY `id` (`id`),
  ADD KEY `event_id` (`event_id`);

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
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
