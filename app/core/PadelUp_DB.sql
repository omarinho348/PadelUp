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

-- NEW SCHEMA: DROP OLD TABLES (manual execution may be required if they exist)
-- NOTE: Remove/drop old tables manually before running if needed:
-- DROP TABLE IF EXISTS users, player_profiles, coach_profiles, venues;

-- Users table (base identities & roles)
CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin','venue_admin','coach','player') NOT NULL DEFAULT 'player',
  `phone` VARCHAR(30) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Player profiles (extended player-only attributes)
CREATE TABLE `player_profiles` (
  `player_id` INT PRIMARY KEY,
  `skill_level` DECIMAL(4,2) NOT NULL DEFAULT 0.00,
  `gender` ENUM('male','female','other') NULL,
  `birth_date` DATE NULL,
  `padel_iq_rating` DECIMAL(4,2) NOT NULL DEFAULT 0.00,
  `preferred_side` ENUM('right','left') NULL,
  CONSTRAINT `fk_player_user` FOREIGN KEY (`player_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Coach profiles (extended coach-only attributes)
CREATE TABLE `coach_profiles` (
  `coach_id` INT PRIMARY KEY,
  `bio` TEXT NULL,
  `hourly_rate` DECIMAL(10,2) NULL,
  `experience_years` INT NULL,
  `location` VARCHAR(120) NULL,
  CONSTRAINT `fk_coach_user` FOREIGN KEY (`coach_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Venues (owned/managed by venue_admin) 
CREATE TABLE `venues` (
  `venue_id` INT AUTO_INCREMENT PRIMARY KEY,
  `venue_admin_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `address` VARCHAR(200) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `opening_time` TIME NULL,
  `closing_time` TIME NULL,
  `hourly_rate` INT NOT NULL DEFAULT 0,
  `logo_path` VARCHAR(255) NULL,
  CONSTRAINT `fk_venue_admin` FOREIGN KEY (`venue_admin_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  KEY `idx_city` (`city`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `courts` (
  `court_id` INT AUTO_INCREMENT PRIMARY KEY,
  `venue_id` INT NOT NULL,
  `court_name` VARCHAR(100) NOT NULL,
  `court_type` ENUM('indoor','outdoor','covered') DEFAULT 'outdoor',
  `is_active` BOOLEAN NOT NULL DEFAULT 1,
  CONSTRAINT `fk_courts_venue` FOREIGN KEY (`venue_id`)
      REFERENCES `venues`(`venue_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `bookings` (
  `booking_id` INT AUTO_INCREMENT PRIMARY KEY,
  `court_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `booking_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending','confirmed','cancelled') DEFAULT 'confirmed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_booking_court` FOREIGN KEY (`court_id`) REFERENCES `courts`(`court_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Migration (run separately if table already exists):
-- ALTER TABLE venues ADD COLUMN `hourly_rate` INT NOT NULL DEFAULT 0 AFTER `closing_time`;

-- Marketplace Products
CREATE TABLE `products` (
  `product_id` INT AUTO_INCREMENT PRIMARY KEY,
  `seller_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `category` ENUM('rackets', 'shoes', 'apparel', 'accessories') NOT NULL,
  `product_condition` ENUM('new', 'used_like_new', 'used_good', 'used_fair') NOT NULL,
  `image_url` VARCHAR(255) NULL,
  `status` ENUM('available', 'sold') NOT NULL DEFAULT 'available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_product_seller` FOREIGN KEY (`seller_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------
-- MATCHMAKING TABLES
-- --------------------------------------------------

-- 1. Matches Table
CREATE TABLE IF NOT EXISTS matches (
    match_id INT AUTO_INCREMENT PRIMARY KEY,
    creator_id INT NOT NULL,                         
    venue_id INT NOT NULL,                           
    match_date DATE NOT NULL,
    match_time TIME NOT NULL,
    min_skill_level INT NOT NULL,
    max_skill_level INT NOT NULL,
    max_players INT NOT NULL DEFAULT 4,
    current_players INT NOT NULL DEFAULT 1,
    status ENUM('open','full','completed') NOT NULL DEFAULT 'open',
    description TEXT,
    FOREIGN KEY (creator_id) REFERENCES users(user_id),
    FOREIGN KEY (venue_id) REFERENCES venues(venue_id)
);

-- 2. Match Players Table
CREATE TABLE IF NOT EXISTS match_players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    player_id INT NOT NULL,                         -- references player_profile.player_id
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(match_id),
    FOREIGN KEY (player_id) REFERENCES users(user_id),
    UNIQUE KEY unique_player_match (match_id, player_id)
);

CREATE TABLE `match_results` (
  `result_id` INT AUTO_INCREMENT PRIMARY KEY,
  `match_id` INT NOT NULL UNIQUE,
  `team1_player1_id` INT NOT NULL,
  `team1_player2_id` INT NOT NULL,
  `team2_player1_id` INT NOT NULL,
  `team2_player2_id` INT NOT NULL,
  `team1_set1_score` TINYINT UNSIGNED,
  `team2_set1_score` TINYINT UNSIGNED,
  `team1_set2_score` TINYINT UNSIGNED,
  `team2_set2_score` TINYINT UNSIGNED,
  `team1_set3_score` TINYINT UNSIGNED,
  `team2_set3_score` TINYINT UNSIGNED,
  `winner_team` ENUM('1', '2') NOT NULL,
  `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_result_match` FOREIGN KEY (`match_id`) REFERENCES `matches`(`match_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_t1_p1` FOREIGN KEY (`team1_player1_id`) REFERENCES `users`(`user_id`),
  CONSTRAINT `fk_t1_p2` FOREIGN KEY (`team1_player2_id`) REFERENCES `users`(`user_id`),
  CONSTRAINT `fk_t2_p1` FOREIGN KEY (`team2_player1_id`) REFERENCES `users`(`user_id`),
  CONSTRAINT `fk_t2_p2` FOREIGN KEY (`team2_player2_id`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
