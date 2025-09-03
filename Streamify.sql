-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 18, 2025 at 07:03 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `streamify`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(1, 'Sci-Fi'),
(2, 'Drama'),
(3, 'Comedy'),
(4, 'Action');

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
CREATE TABLE IF NOT EXISTS `content` (
  `content_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `release_year` int DEFAULT NULL,
  `content_type` enum('movie','tv_show') NOT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `video_path` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT '0',
  `views` int UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`content_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`content_id`, `title`, `description`, `release_year`, `content_type`, `thumbnail_url`, `rating`, `created_at`, `video_path`, `featured`, `views`) VALUES
(1, 'The Quantum Paradox', 'A cocky physicist accidentally texts himself from the future—hijinks (and time-police) ensue.', 2024, 'movie', 'thumbnails/quantum.jpg', 8.3, '2025-07-13 08:14:00', 'videos/quantum_paradox.mp4', 1, 222),
(2, 'Love in Binary', 'Two A.I. chatbots try to ghost each other. Humanity ships them anyway.', 2023, 'movie', 'thumbnails/love_binary.jpg', 7.5, '2025-07-13 08:14:00', 'videos/love_in_binary.mp4', 1, 4443),
(3, 'Blast Velocity', 'A retired stunt driver is blackmailed into one last ride … to Mars.', 2022, 'movie', 'thumbnails/blast_velocity.jpg', 7.9, '2025-07-13 08:14:00', 'videos/blast_velocity.mp4', 1, 12),
(4, 'Time Loopers', 'Every Monday resets time—Tuesdays are overrated anyway.', 2025, 'tv_show', 'thumbnails/time_loopers.jpg', 8.7, '2025-07-13 08:14:00', NULL, 0, 4737),
(5, 'Code & Chaos', 'Four broke programmers rent a haunted co-working space. StackOverflow can’t save them.', 2024, 'tv_show', 'thumbnails/code_chaos.jpg', 8.1, '2025-07-13 08:14:00', NULL, 0, 633);

-- --------------------------------------------------------

--
-- Table structure for table `content_categories`
--

DROP TABLE IF EXISTS `content_categories`;
CREATE TABLE IF NOT EXISTS `content_categories` (
  `content_id` int NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`content_id`,`category_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `content_categories`
--

INSERT INTO `content_categories` (`content_id`, `category_id`) VALUES
(1, 1),
(2, 2),
(3, 1),
(3, 4),
(4, 1),
(5, 3);

-- --------------------------------------------------------

--
-- Table structure for table `episodes`
--

DROP TABLE IF EXISTS `episodes`;
CREATE TABLE IF NOT EXISTS `episodes` (
  `episode_id` int NOT NULL AUTO_INCREMENT,
  `content_id` int DEFAULT NULL,
  `episode_number` int DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text,
  `duration_minutes` int DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`episode_id`),
  KEY `content_id` (`content_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `episodes`
--

INSERT INTO `episodes` (`episode_id`, `content_id`, `episode_number`, `title`, `description`, `duration_minutes`, `release_date`, `rating`, `video_path`) VALUES
(1, 4, 1, 'Déjà View', 'Our hero oversleeps. Again. Or does he?', 45, '2025-01-10', 8.6, 'videos/Dreamland Sweets.mp4'),
(2, 4, 2, 'Again And Again', 'They try to break the loop with karaoke. Spoiler: it fails.', 44, '2025-01-17', 8.5, 'videos/time_loopers/s01e02/master.m3u8'),
(3, 5, 1, 'Null Pointer', 'A ghost crashes their IDE and their rent.', 30, '2024-06-01', 8.0, 'videos/code_chaos/s01e01/master.m3u8'),
(4, 5, 2, 'Merge Conflict', 'Possessed Git repo insists on tabs. HORROR. ?', 32, '2024-06-08', 8.2, 'videos/code_chaos/s01e02/master.m3u8');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
CREATE TABLE IF NOT EXISTS `ratings` (
  `rating_id` int NOT NULL AUTO_INCREMENT,
  `content_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rating_id`),
  UNIQUE KEY `unique_rating` (`content_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `content_id` int DEFAULT NULL,
  `comment` text,
  `rating` int DEFAULT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  KEY `user_id` (`user_id`),
  KEY `content_id` (`content_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `subscription_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `subscription_type` enum('premium','free') DEFAULT 'free',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `payment_status` enum('paid','unpaid') DEFAULT 'unpaid',
  PRIMARY KEY (`subscription_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `subscription_type` enum('free','premium') DEFAULT 'free',
  `is_premium` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `profile_pic`, `role`, `subscription_type`, `is_premium`, `created_at`, `last_login`) VALUES
(1, 'neo', 'neo@matrix.io', '$2y$10$92IXUNpkjO0rOQ5byMi.Y.', NULL, 'user', 'free', 0, '2025-07-15 15:08:07', '2025-07-15 15:08:07'),
(2, 'trinity', 'trin@matrix.io', '$2y$10$92IXUNpkjO0rOQ5byMi.Y.', NULL, 'user', 'premium', 1, '2025-07-15 15:08:07', '2025-07-15 15:08:07'),
(3, 'morpheus', 'morph@matrix.io', '$2y$10$92IXUNpkjO0rOQ5byMi.Y.', NULL, 'admin', 'premium', 1, '2025-07-15 15:08:07', '2025-07-15 15:08:07');

-- --------------------------------------------------------

--
-- Table structure for table `watchlist`
--

DROP TABLE IF EXISTS `watchlist`;
CREATE TABLE IF NOT EXISTS `watchlist` (
  `user_id` int NOT NULL,
  `content_id` int NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`content_id`),
  KEY `content_id` (`content_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `watch_history`
--

DROP TABLE IF EXISTS `watch_history`;
CREATE TABLE IF NOT EXISTS `watch_history` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `content_id` int DEFAULT NULL,
  `progress_percent` decimal(5,2) DEFAULT '0.00',
  `last_watched` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `rating` int DEFAULT NULL,
  PRIMARY KEY (`history_id`),
  KEY `user_id` (`user_id`),
  KEY `content_id` (`content_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `watch_history`
--

INSERT INTO `watch_history` (`history_id`, `user_id`, `content_id`, `progress_percent`, `last_watched`, `rating`) VALUES
(1, 1, 1, 35.00, '2025-07-13 15:08:07', NULL),
(2, 1, 3, 72.00, '2025-07-15 14:08:07', 5),
(3, 2, 2, 12.50, '2025-07-15 12:08:07', NULL),
(4, 2, 4, 55.30, '2025-07-15 10:08:07', 4),
(5, 3, 5, 88.90, '2025-07-15 14:38:07', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
