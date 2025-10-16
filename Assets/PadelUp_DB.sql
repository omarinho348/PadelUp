-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2025 at 01:02 AM
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
-- Database: `padelup`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Gender` enum('male','female','other') NOT NULL,
  `DateOfBirth` date NOT NULL,
  `Height` int(11) NOT NULL,
  `DominantHand` enum('right','left') NOT NULL,
  `PreferredPosition` enum('rightside','leftside','both') NOT NULL,
  `SkillLevel` enum('beginner','intermediate','advanced') NOT NULL,
  `Location` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `RegisterDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `FullName`, `Email`, `Gender`, `DateOfBirth`, `Height`, `DominantHand`, `PreferredPosition`, `SkillLevel`, `Location`, `Password`, `RegisterDate`) VALUES
(2, 'Yaya', 'ye@gmail.com', 'male', '2025-10-02', 190, 'right', 'rightside', 'intermediate', 'Cairo, Egypt', '$2y$10$AiFYrsEhBieioAIqV6K2LOrxzglfbHKYvl94NjrAagVIjzbTQJiEi', '2025-10-16 21:42:52'),
(4, 'z', 'z@gmail.com', 'male', '2025-10-02', 190, 'right', 'rightside', 'intermediate', 'Cairo, Egypt', '$2y$10$1u1f3P.FSxD95c0bbeKlLO42AAmi41hNRhp.xOwcS8e3YL6hgIC7u', '2025-10-16 21:45:28'),
(5, 'OA', 'Omar@gmail.com', 'male', '2025-09-29', 100, 'right', 'leftside', 'beginner', 'Cairo, Egypt', '$2y$10$MhgCQjDPuOR0tXfcsqNVAuwdWvy5IpBmyE98fxOQLCZ8sa/VDqx/m', '2025-10-16 22:29:17'),
(7, 'omar', 'OA@gmail.com', 'male', '2025-10-20', 180, 'right', 'both', 'intermediate', 'Cairo, Egypt', '$2y$10$8fGKXstPZecw5oSByfdQMen4x2t2tHlaj8rXtI.gSD3F0tw1MJSYG', '2025-10-16 22:47:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
