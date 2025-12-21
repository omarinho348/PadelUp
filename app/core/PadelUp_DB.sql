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

-- Session requests: visitors can request a coaching session with a coach. Persist requester's contact details so coach can follow up.
CREATE TABLE IF NOT EXISTS `session_requests` (
  `request_id` INT AUTO_INCREMENT PRIMARY KEY,
  `coach_id` INT NOT NULL,
  `requester_id` INT NULL,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `message` TEXT NULL,
  `status` ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_session_coach` FOREIGN KEY (`coach_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_session_requester` FOREIGN KEY (`requester_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  KEY `idx_session_coach` (`coach_id`)
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
  `status` ENUM('pending','confirmed','cancelled','paid') DEFAULT 'confirmed',
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

-- --------------------------------------------------
-- TOURNAMENTS
-- --------------------------------------------------
-- Tournaments are created by a `venue_admin` (application-level enforcement).
-- By default the location for a tournament will be one of the courts managed by the venue_admin
-- (this should be validated in application code before inserting or by a trigger if desired).
CREATE TABLE IF NOT EXISTS tournaments (
  tournament_id INT AUTO_INCREMENT PRIMARY KEY,
  venue_id INT NOT NULL ,
  tournament_name VARCHAR(150) NOT NULL,
  created_by INT DEFAULT NULL, -- user_id of the venue_admin who created the tournament
  tournament_date DATE NOT NULL, -- 1-day tournaments only
  start_time TIME NOT NULL,
  max_level INT NOT NULL, -- maximum allowed skill level for participants
  max_size INT NOT NULL DEFAULT 4,
  entrance_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_prize_money DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status ENUM('scheduled','cancelled','completed') NOT NULL DEFAULT 'scheduled',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_tournament_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues`(`venue_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tournament_creator` FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  KEY `idx_tournament_date` (`tournament_date`),
  KEY `idx_venue_id` (`venue_id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tournament registrations (players registering for tournaments)
CREATE TABLE IF NOT EXISTS tournament_registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tournament_id INT NOT NULL,
  user_id INT NOT NULL,
  registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reg_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments`(`tournament_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reg_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_tournament_user` (`tournament_id`, `user_id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tournament teams (doubles registration: 2 players per team)
CREATE TABLE IF NOT EXISTS tournament_teams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tournament_id INT NOT NULL,
  player1_user_id INT NOT NULL,
  player2_user_id INT NOT NULL,
  registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_team_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments`(`tournament_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_team_p1` FOREIGN KEY (`player1_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_team_p2` FOREIGN KEY (`player2_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_team_pair` (`tournament_id`, `player1_user_id`, `player2_user_id`),
  UNIQUE KEY `unique_team_player1` (`tournament_id`, `player1_user_id`),
  UNIQUE KEY `unique_team_player2` (`tournament_id`, `player2_user_id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tournament draw (stores the bracket seeding once generated)
CREATE TABLE IF NOT EXISTS tournament_draw (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tournament_id INT NOT NULL,
  seed_position INT NOT NULL,
  team_id INT NULL,
  is_bye BOOLEAN NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_draw_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments`(`tournament_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_draw_team` FOREIGN KEY (`team_id`) REFERENCES `tournament_teams`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_tournament_seed` (`tournament_id`, `seed_position`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tournament match results (stores winner of each match)
CREATE TABLE IF NOT EXISTS tournament_match_results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tournament_id INT NOT NULL,
  round_number INT NOT NULL,
  match_number INT NOT NULL,
  team1_seed INT NOT NULL,
  team2_seed INT NOT NULL,
  winner_seed INT NOT NULL,
  recorded_by INT NOT NULL,
  recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_match_result_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments`(`tournament_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_match_result_recorder` FOREIGN KEY (`recorded_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_tournament_match` (`tournament_id`, `round_number`, `match_number`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
