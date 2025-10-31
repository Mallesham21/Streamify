-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 31, 2025 at 10:36 AM
-- Server version: 11.5.2-MariaDB
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
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 9, 'subscription_purchase', '{\"subscription_id\":\"2\",\"plan_name\":\"3 Month Plan\",\"amount\":\"499.00\",\"payment_id\":\"pay_ROWOKVpUThBuPD\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 07:32:45'),
(2, 9, 'subscription_purchase', '{\"subscription_id\":\"2\",\"plan_name\":\"3 Month Plan\",\"amount\":\"499.00\",\"payment_id\":\"pay_ROWOKVpUThBuPD\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 07:35:33'),
(3, 1, 'category_added', '{\"category_id\":17,\"name\":\"Tollywood\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-10-04 06:35:44'),
(4, 1, 'category_added', '{\"category_id\":18,\"name\":\"Bollywood\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-10-04 06:43:49'),
(5, 1, 'content_added', '{\"content_id\":31,\"title\":\"Coolie\",\"content_type\":\"movie\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-06 04:50:44'),
(6, 1, 'content_added', '{\"content_id\":\"32\",\"title\":\"Avbv\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-08 12:57:29'),
(7, 1, 'content_added', '{\"content_id\":\"33\",\"title\":\"Aaaf\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-08 13:16:40'),
(8, 1, 'content_added', '{\"content_id\":\"34\",\"title\":\"Aibv\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-08 13:18:28'),
(9, 1, 'content_added', '{\"content_id\":\"35\",\"title\":\"Cf\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-08 13:23:38'),
(10, 1, 'content_added', '{\"content_id\":\"36\",\"title\":\"4cc\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-08 13:29:18'),
(11, 1, 'content_added', '{\"content_id\":\"37\",\"title\":\"1fff\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-08 13:32:59'),
(12, 1, 'content_added', '{\"content_id\":\"38\",\"title\":\"1222\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-08 15:37:32'),
(13, 1, 'content_added', '{\"content_id\":\"39\",\"title\":\"1555\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-08 15:40:18'),
(14, 1, 'content_added', '{\"content_id\":\"40\",\"title\":\"Cc\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-08 15:46:32'),
(15, 1, 'content_added', '{\"content_id\":\"41\",\"title\":\"Djdh\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-08 15:55:39'),
(16, 1, 'content_added', '{\"content_id\":\"42\",\"title\":\"17hsbsb\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-08 15:57:44'),
(17, 1, 'content_added', '{\"content_id\":\"43\",\"title\":\"Fd\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-08 15:59:09'),
(18, 1, 'content_added', '{\"content_id\":\"44\",\"title\":\"Duud\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-08 16:01:48'),
(19, 1, 'content_added', '{\"content_id\":\"46\",\"title\":\"Ehgs\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-09 06:25:56'),
(20, 1, 'content_added', '{\"content_id\":\"47\",\"title\":\"1Yeah\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-10 13:18:36'),
(21, 1, 'content_added', '{\"content_id\":\"48\",\"title\":\"2hhv\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-10 13:48:21'),
(22, 1, 'content_added', '{\"content_id\":\"52\",\"title\":\"Ffc\",\"content_type\":\"tv_show\"}', '127.0.0.1', 'Admin Panel', '2025-10-10 14:44:36'),
(23, 1, 'content_added', '{\"content_id\":\"53\",\"title\":\"1dc\",\"content_type\":\"tv_show\"}', '127.0.0.1', 'Admin Panel', '2025-10-10 14:45:29'),
(24, 1, 'content_added', '{\"content_id\":\"54\",\"title\":\"Coolie\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-11 12:58:06'),
(25, 1, 'content_added', '{\"content_id\":\"55\",\"title\":\"1ss\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-11 15:34:56'),
(26, 1, 'content_added', '{\"content_id\":\"56\",\"title\":\"Hv\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-11 15:36:01'),
(27, 1, 'content_added', '{\"content_id\":\"57\",\"title\":\"Cc\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-11 16:33:40'),
(28, 1, 'content_added', '{\"content_id\":\"58\",\"title\":\"1ivv\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-12 02:32:09'),
(29, 1, 'content_added', '{\"content_id\":\"59\",\"title\":\"1hvv\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-12 03:02:26'),
(30, 1, 'content_added', '{\"content_id\":\"60\",\"title\":\"1ivv\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-12 03:23:58'),
(31, 10, 'subscription_purchase', '{\"subscription_id\":\"3\",\"plan_name\":\"12 Month Plan\",\"amount\":\"1499.00\",\"payment_id\":\"pay_RSmnIQkvC3YPLy\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 02:05:10'),
(32, 1, 'content_added', '{\"content_id\":\"61\",\"title\":\"Dx\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-15 04:26:07'),
(33, 1, 'content_added', '{\"content_id\":\"62\",\"title\":\"Coolie\",\"content_type\":\"movie\"}', '127.0.0.1', 'Admin Panel', '2025-10-15 04:27:08'),
(34, 16, 'subscription_purchase', '{\"subscription_id\":\"2\",\"plan_name\":\"3 Month Plan\",\"amount\":\"499.00\",\"payment_id\":\"pay_RUakfRe2OtS7Xf\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 15:36:23'),
(35, 17, 'subscription_purchase', '{\"subscription_id\":\"2\",\"plan_name\":\"3 Month Plan\",\"amount\":\"499.00\",\"payment_id\":\"pay_RUangEEChsl3w4\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 15:39:12'),
(36, 29, 'subscription_purchase', '{\"subscription_id\":\"2\",\"plan_name\":\"3 Month Plan\",\"amount\":\"499.00\",\"payment_id\":\"pay_RUbh8YbkVo0ZT2\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 16:31:56'),
(37, 12, 'subscription_purchase', '{\"subscription_id\":\"2\",\"plan_name\":\"3 Month Plan\",\"amount\":\"499.00\",\"payment_id\":\"pay_RUlrKXLdYPimAt\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 02:28:20'),
(38, 31, 'subscription_purchase', '{\"subscription_id\":\"2\",\"plan_name\":\"3 Month Plan\",\"amount\":\"499.00\",\"payment_id\":\"pay_RZUwsDDVZxoP8b\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-30 01:10:43');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
CREATE TABLE IF NOT EXISTS `banners` (
  `banner_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `target_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`banner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cancellations`
--

DROP TABLE IF EXISTS `cancellations`;
CREATE TABLE IF NOT EXISTS `cancellations` (
  `cancel_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `content_id` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `cancel_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`cancel_id`),
  KEY `user_id` (`user_id`),
  KEY `content_id` (`content_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(1, 'Action'),
(2, 'Drama'),
(3, 'Thriller'),
(4, 'Sci-Fi'),
(5, 'Comedy'),
(6, 'Romance'),
(7, 'Fantasy'),
(8, 'Animation'),
(9, 'Crime'),
(10, 'Adventure'),
(17, 'Tollywood'),
(18, 'Bollywood');

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
CREATE TABLE IF NOT EXISTS `content` (
  `content_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `content_type` enum('movie','tv_show') NOT NULL,
  `release_year` int(11) NOT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `banner_url` varchar(255) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `video_path` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `is_premium` tinyint(1) DEFAULT 0,
  `views` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `duration` int(11) DEFAULT NULL,
  `schedule_date` datetime DEFAULT NULL,
  `is_scheduled` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`content_id`),
  KEY `idx_scheduled_release` (`is_scheduled`,`schedule_date`)
) ENGINE=MyISAM AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`content_id`, `title`, `description`, `content_type`, `release_year`, `thumbnail_url`, `banner_url`, `rating`, `created_at`, `video_path`, `featured`, `is_premium`, `views`, `duration`, `schedule_date`, `is_scheduled`) VALUES
(1, 'Inception', 'A thief enters dreams to steal secrets, but must plant one instead.', 'movie', 2010, 'thumbnails/inception.jpg', 'banners/inception1.jpg', 8.8, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 3431, 148, NULL, 0),
(2, 'The Dark Knight', 'Batman faces chaos brought by the Joker in Gotham.', 'movie', 2008, 'thumbnails/dark_knight.jpg', 'banners/dark_knight1.jpg', 9.0, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 5561, 152, NULL, 0),
(3, 'Interstellar', 'Astronauts travel through a wormhole to save humanity.', 'movie', 2014, 'thumbnails/interstellar.jpg', 'banners/interstellar1.jpg', 8.6, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 7513, 169, NULL, 0),
(4, 'Avengers: Endgame', 'The Avengers assemble for the final battle against Thanos.', 'movie', 2019, 'thumbnails/endgame.jpg', 'banners/Avengers__Endgamejpg', 8.4, '2025-09-02 15:11:19', 'videos/Avengers__Endgame.mp4', 1, 0, 883, 181, NULL, 0),
(5, 'Avatar', 'A marine explores Pandora and joins the Na\'vi people.', 'movie', 2009, 'thumbnails/avatar.jpg', 'uploads/banners/banner_5_1760466265.jpg', 7.9, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 1873, 162, NULL, 0),
(6, 'Titanic', 'A romance blossoms aboard the doomed Titanic.', 'movie', 1997, 'thumbnails/titanic.jpg', 'banners/titanic1.jpg', 7.9, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 6718, 195, NULL, 0),
(7, 'Gladiator', 'A betrayed general seeks vengeance in the Roman arena.', 'movie', 2000, 'thumbnails/gladiator.jpg', 'banners/gladiator1.jpg', 8.5, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 7973, 155, NULL, 0),
(8, 'The Matrix', 'A hacker discovers reality is a simulation.', 'movie', 1999, 'thumbnails/matrix.jpg', 'banners/matrix1.jpg', 8.7, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 9712, 136, NULL, 0),
(9, 'The Godfather', 'The aging patriarch of an organized crime dynasty transfers power.', 'movie', 1972, 'thumbnails/godfather.jpg', 'banners/godfather1.jpg', 9.2, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 4642, 175, NULL, 0),
(10, 'The Shawshank Redemption', 'A man wrongly imprisoned finds hope and freedom.', 'movie', 1994, 'thumbnails/shawshank.jpg', 'banners/shawshank1.jpg', 9.3, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 4075, 142, NULL, 0),
(11, 'Forrest Gump', 'A man with a kind heart experiences decades of U.S. history.', 'movie', 1994, 'thumbnails/forrest.jpg', 'banners/forrest1.jpg', 8.8, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 6448, 142, NULL, 0),
(12, 'Fight Club', 'An insomniac and a soap maker start an underground fight club.', 'movie', 1999, 'thumbnails/fight_club.jpg', 'banners/fight_club1.jpg', 8.8, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 15, 139, NULL, 0),
(13, 'Joker', 'A failed comedian spirals into madness in Gotham.', 'movie', 2019, 'thumbnails/joker.jpg', 'banners/joker1.jpg', 8.4, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 733, 122, NULL, 0),
(14, 'Parasite', 'A poor family infiltrates a wealthy household with dark results.', 'movie', 2019, 'thumbnails/parasite.jpg', 'banners/parasite1.jpg', 8.6, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 3621, 132, NULL, 0),
(15, 'La La Land', 'An aspiring actress and a jazz musician chase dreams in LA.', 'movie', 2016, 'thumbnails/lalaland.jpg', 'banners/lalaland1.jpg', 8.0, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 5905, 128, NULL, 0),
(16, 'The Lion King', 'A lion cub must reclaim his rightful throne.', 'movie', 1994, 'thumbnails/lionking.jpg', 'banners/lionking1.jpg', 8.5, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 8662, 88, NULL, 0),
(17, 'Frozen', 'Two sisters struggle with love, magic, and ice.', 'movie', 2013, 'thumbnails/frozen.jpg', 'banners/frozen1.jpg', 7.4, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 5595, 102, NULL, 0),
(18, 'Spider-Man: No Way Home', 'Peter Parker faces villains from across the multiverse.', 'movie', 2021, 'thumbnails/spiderman_nwh.jpg', 'banners/spiderman_nwh1.jpg', 8.2, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 1990, 148, NULL, 0),
(19, 'Black Panther', 'T’Challa returns to Wakanda to rule as king and Black Panther.', 'movie', 2018, 'thumbnails/blackpanther.jpg', 'banners/blackpanther1.jpg', 7.3, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 3167, 134, NULL, 0),
(20, 'The Avengers', 'Earth’s mightiest heroes unite against Loki.', 'movie', 2012, 'thumbnails/avengers.jpg', 'banners/avengers1.jpg', 8.0, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 9865, 143, NULL, 0),
(21, 'Stranger Things', 'Kids face supernatural horrors in Hawkins.', 'tv_show', 2016, 'thumbnails/stranger_things.jpg', 'banners/stranger_things1.jpg', 8.7, '2025-09-02 15:11:31', 'videos/default.mp4', 0, 1, 9825, NULL, NULL, 0),
(22, 'Breaking Bad', 'A chemistry teacher turns to meth production.', 'tv_show', 2008, 'thumbnails/breakingbad.jpg', 'banners/breakingbad1.jpg', 9.5, '2025-09-02 15:11:31', 'videos/default.mp4', 1, 1, 9531, NULL, NULL, 0),
(23, 'Game of Thrones', 'Noble families fight for power in Westeros.', 'tv_show', 2011, 'thumbnails/got.jpg', 'banners/Game_of_Thronesjpg', 9.3, '2025-09-02 15:11:31', 'videos/default.mp4', 1, 0, 8178, NULL, NULL, 0),
(24, 'The Witcher', 'A monster hunter struggles with destiny and politics.', 'tv_show', 2019, 'thumbnails/witcher.jpg', 'banners/witcher1.jpg', 8.2, '2025-09-02 15:11:31', 'videos/default.mp4', 0, 0, 2293, NULL, NULL, 0),
(25, 'The Crown', 'Chronicles the reign of Queen Elizabeth II.', 'tv_show', 2016, 'thumbnails/crown.jpg', 'banners/crown1.jpg', 8.6, '2025-09-02 15:11:31', 'videos/default.mp4', 0, 0, 6934, NULL, NULL, 0),
(26, 'Money Heist', 'A group of robbers attempt daring heists in Spain.', 'tv_show', 2017, 'thumbnails/moneyheist.jpg', 'banners/moneyheist1.jpg', 8.2, '2025-09-02 15:11:31', 'videos/default.mp4', 0, 0, 7794, NULL, NULL, 0),
(27, 'Dark', 'A German town unravels a time-travel mystery.', 'tv_show', 2017, 'thumbnails/dark.jpg', 'banners/dark1.jpg', 8.8, '2025-09-02 15:11:31', 'videos/default.mp4', 0, 0, 8166, NULL, NULL, 0),
(28, 'The Boys', 'Anti-heroes fight corrupt superheroes.', 'tv_show', 2019, 'thumbnails/boys.jpg', 'banners/boys1.jpg', 8.7, '2025-09-02 15:11:31', 'videos/default.mp4', 0, 0, 7451, NULL, NULL, 0),
(29, 'Squid Game', 'Hundreds compete in deadly games for money.', 'tv_show', 2021, 'thumbnails/squidgame.jpg', 'banners/squidgame1.jpg', 8.0, '2025-09-02 15:11:31', 'videos/default.mp4', 0, 0, 2755, NULL, NULL, 0),
(30, 'Peaky Blinders', 'A gangster family in 1900s Birmingham, England.', 'tv_show', 2013, 'thumbnails/peaky.jpg', 'banners/peaky1.jpg', 8.8, '2025-09-02 15:11:31', 'videos/default.mp4', 0, 0, 1422, NULL, NULL, 0),
(62, 'Coolie', 'Delves into a man\'s relentless quest for vengeance since youth, driven by righting past wrongs, shaping his very existence.', 'movie', 2025, 'thumbnails/Coolie_6.jpg', 'banners/Coolie_10.jpg', 9.0, '2025-10-15 04:27:08', 'videos/default.mp4', 0, 1, 8849, 168, '2025-10-31 15:11:00', 1),
(84, 'Sacred Games', 'A cop uncovers a dark conspiracy that threatens Mumbai.', 'tv_show', 2018, 'thumbnails/Sacred_Games.jpg', 'banners/Sacred_Games.jpg', 8.6, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 9976, NULL, NULL, 0),
(83, 'Mirzapur', 'A lawless town ruled by the mafia king Kaleen Bhaiya.', 'tv_show', 2018, 'thumbnails/mirzapur.jpg', 'banners/mirzapur.jpg', 8.5, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 3337, NULL, NULL, 0),
(82, 'The Family Man', 'A middle-class man secretly works as an intelligence officer.', 'tv_show', 2019, 'thumbnails/familyman.jpg', 'banners/familyman.jpg', 8.8, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 6754, NULL, NULL, 0),
(80, 'Scam 1992', 'The rise and fall of stockbroker Harshad Mehta.', 'tv_show', 2020, 'thumbnails/Scam_1992.jpg', 'banners/Scam_1992.jpg', 9.5, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 3763, NULL, NULL, 0),
(81, 'Made in Heaven', 'Wedding planners navigate love and lies in Delhi.', 'tv_show', 2019, 'thumbnails/madeinheaven.jpg', 'banners/madeinheaven.jpg', 8.3, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 8543, NULL, NULL, 0),
(79, 'Chhichhore', 'A tragic event reunites college friends and memories.', 'movie', 2019, 'thumbnails/Chhichhorejpg', 'banners/Chhichhorejpg', 8.1, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 0, 1433, NULL, NULL, 0),
(78, 'Dangal', 'A father trains his daughters to become wrestling champions.', 'movie', 2016, 'thumbnails/Dangaljpg', 'banners/Dangaljpg', 8.3, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 0, 1537, NULL, NULL, 0),
(77, '3 Idiots', 'Three friends question the education system in India.', 'movie', 2009, 'thumbnails/3_Idiotsjpg', 'banners/3_Idiotsjpg', 8.4, '2025-10-30 09:41:22', 'videos/3_Idiots.mp4', 0, 0, 3388, NULL, NULL, 0),
(76, 'Brahmastra', 'A man discovers his connection to powerful celestial weapons.', 'movie', 2022, 'thumbnails/Brahmastrajpg', 'banners/Brahmastrajpg', 6.8, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 0, 2327, NULL, NULL, 0),
(74, 'Pathaan', 'An Indian spy races to stop a bio-terror attack.', 'movie', 2023, 'thumbnails/pathaan.jpg', 'banners/pathaan.jpg', 7.5, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 1473, NULL, NULL, 0),
(75, 'Gadar 2', 'A father crosses the border to rescue his son from Pakistan.', 'movie', 2023, 'thumbnails/Gadar_2.jpg', 'banners/Gadar_2.jpg', 7.0, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 0, 384, NULL, NULL, 0),
(73, 'Jawan', 'A soldier turned vigilante takes justice into his own hands.', 'movie', 2023, 'thumbnails/jawan.jpg', 'banners/jawan.jpg', 7.8, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 7495, NULL, NULL, 0),
(85, 'Devara', 'A gritty tale of revenge set against the sea.', 'movie', 2025, 'thumbnails/Devarajpg', 'banners/Devarajpg', 8.2, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 6327, NULL, NULL, 0),
(86, 'Pushpa 2: The Rule', 'Pushpa continues his rebellion against the red sandalwood syndicate.', 'movie', 2024, 'thumbnails/pushpa2.jpg', 'banners/pushpa2.jpg', 8.7, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 9152, NULL, NULL, 0),
(87, 'Salaar', 'A power struggle between friends turned enemies.', 'movie', 2023, 'thumbnails/salaar.jpg', 'banners/salaar.jpg', 7.5, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 6773, NULL, NULL, 0),
(88, 'Bahubali: The Beginning', 'A prince raised in exile discovers his royal legacy.', 'movie', 2015, 'thumbnails/Bahubali__The_Beginningjpg', 'banners/Bahubali__The_Beginningjpg', 8.1, '2025-10-30 09:41:22', 'videos/Bahubali__The_Beginning.mp4', 0, 0, 6415, NULL, NULL, 0),
(89, 'Bahubali: The Conclusion', 'The epic conclusion to the Mahishmati saga.', 'movie', 2017, 'thumbnails/Bahubali__The_Conclusionjpg', 'banners/Bahubali__The_Conclusionjpg', 8.2, '2025-10-30 09:41:22', 'videos/Bahubali__The_Conclusion.mp4', 0, 0, 1755, NULL, NULL, 0),
(90, 'Ala Vaikunthapurramuloo', 'A young man discovers his real parentage in a wealthy family.', 'movie', 2020, 'thumbnails/Ala_Vaikunthapurramuloojpg', 'banners/Ala_Vaikunthapurramuloojpg', 7.9, '2025-10-30 09:41:22', 'videos/Ala_Vaikunthapurramuloo.mp4', 0, 0, 9534, NULL, NULL, 0),
(91, 'Sita Ramam', 'An orphaned soldier receives mysterious love letters.', 'movie', 2022, 'thumbnails/sitaramam.jpg', 'banners/sitaramam.jpg', 8.6, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 0, 2394, NULL, NULL, 0),
(92, 'Jersey', 'A struggling cricketer makes an emotional comeback.', 'movie', 2019, 'thumbnails/jersey.jpg', 'banners/jersey.jpg', 8.5, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 0, 3374, NULL, NULL, 0),
(93, 'Eega', 'A man reincarnates as a fly to take revenge.', 'movie', 2012, 'thumbnails/eega.jpg', 'banners/eega.jpg', 7.7, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 0, 9685, NULL, NULL, 0),
(94, 'Vikram', 'A black-ops squad hunts a masked serial killer.', 'movie', 2022, 'thumbnails/vikram.jpg', 'banners/vikram.jpg', 8.3, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 8308, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `content_categories`
--

DROP TABLE IF EXISTS `content_categories`;
CREATE TABLE IF NOT EXISTS `content_categories` (
  `content_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`content_id`,`category_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Dumping data for table `content_categories`
--

INSERT INTO `content_categories` (`content_id`, `category_id`) VALUES
(1, 1),
(1, 3),
(1, 4),
(2, 1),
(2, 3),
(2, 9),
(3, 1),
(3, 2),
(3, 4),
(4, 1),
(4, 3),
(4, 10),
(5, 2),
(5, 4),
(5, 10),
(6, 2),
(6, 6),
(6, 10),
(7, 1),
(7, 2),
(7, 10),
(8, 1),
(8, 3),
(8, 4),
(9, 2),
(9, 9),
(10, 2),
(10, 9),
(11, 2),
(11, 6),
(12, 3),
(12, 9),
(13, 3),
(13, 9),
(14, 2),
(14, 3),
(15, 2),
(15, 6),
(16, 2),
(16, 7),
(16, 10),
(17, 6),
(17, 7),
(17, 8),
(18, 1),
(18, 3),
(18, 4),
(19, 1),
(19, 2),
(19, 10),
(20, 1),
(20, 3),
(20, 10),
(21, 2),
(21, 3),
(21, 4),
(22, 2),
(22, 3),
(22, 9),
(23, 2),
(23, 7),
(23, 10),
(24, 2),
(24, 4),
(24, 7),
(25, 2),
(25, 6),
(26, 2),
(26, 3),
(26, 9),
(27, 2),
(27, 3),
(27, 4),
(28, 1),
(28, 3),
(28, 9),
(29, 2),
(29, 3),
(30, 2),
(30, 3),
(30, 9),
(31, 1),
(31, 2),
(62, 1),
(62, 3),
(62, 9),
(73, 1),
(73, 3),
(73, 18),
(74, 1),
(74, 3),
(74, 18),
(75, 1),
(75, 10),
(75, 18),
(76, 3),
(76, 6),
(76, 10),
(76, 18),
(77, 2),
(77, 6),
(77, 18),
(78, 2),
(78, 18),
(79, 2),
(79, 18),
(80, 2),
(80, 9),
(80, 18),
(81, 2),
(81, 6),
(81, 18),
(82, 3),
(82, 18),
(83, 9),
(83, 18),
(84, 3),
(84, 9),
(84, 18),
(85, 1),
(85, 3),
(85, 17),
(86, 1),
(86, 10),
(86, 17),
(87, 1),
(87, 17),
(88, 1),
(88, 10),
(88, 17),
(89, 1),
(89, 10),
(89, 17),
(90, 2),
(90, 6),
(90, 17),
(91, 2),
(91, 6),
(91, 17),
(92, 2),
(92, 17),
(93, 17),
(94, 1),
(94, 3),
(94, 17);

-- --------------------------------------------------------

--
-- Table structure for table `episodes`
--

DROP TABLE IF EXISTS `episodes`;
CREATE TABLE IF NOT EXISTS `episodes` (
  `episode_id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) DEFAULT NULL,
  `episode_number` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  `is_scheduled` tinyint(1) DEFAULT 0,
  `scheduled_release_date` datetime DEFAULT NULL,
  PRIMARY KEY (`episode_id`),
  KEY `content_id` (`content_id`),
  KEY `idx_scheduled_release` (`is_scheduled`,`scheduled_release_date`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Dumping data for table `episodes`
--

INSERT INTO `episodes` (`episode_id`, `content_id`, `episode_number`, `title`, `description`, `duration_minutes`, `release_date`, `rating`, `video_path`, `is_scheduled`, `scheduled_release_date`) VALUES
(1, 21, 1, 'Chapter One: The Vanishing of Will Byers', 'Will disappears in Hawkins, strange events follow.', 50, '2016-07-15', 8.6, 'videos/default.mp4', 0, NULL),
(11, 22, 1, 'Pilot', 'Walter White turns to cooking meth after cancer diagnosis.', 58, '2008-01-20', NULL, 'videos/default.mp4', 0, NULL),
(3, 23, 1, 'Winter Is Coming', 'The Stark family introduces the world of Westeros.', 62, '2011-04-17', 9.1, 'videos/default.mp4', 0, NULL),
(4, 24, 1, 'The End’s Beginning', 'Geralt battles a monster and destiny begins to unfold.', 61, '2019-12-20', 8.5, 'videos/default.mp4', 0, NULL),
(5, 25, 1, 'Wolferton Splash', 'Elizabeth marries Philip and King George’s health declines.', 57, '2016-11-04', 8.3, 'videos/default.mp4', 0, NULL),
(6, 26, 1, 'Episode 1', 'The Professor recruits a team for the Royal Mint heist.', 47, '2017-05-02', 8.2, 'videos/default.mp4', 0, NULL),
(7, 27, 1, 'Secrets', 'A boy disappears, sparking mystery across generations.', 51, '2017-12-01', 8.6, 'videos/default.mp4', 0, NULL),
(8, 28, 1, 'The Name of the Game', 'Hughie joins Butcher to fight corrupt superheroes.', 60, '2019-07-26', 8.5, 'videos/default.mp4', 0, NULL),
(9, 29, 1, 'Red Light, Green Light', '456 players join deadly survival games.', 62, '2021-09-17', 8.2, 'videos/default.mp4', 0, NULL),
(10, 30, 1, 'Episode 1', 'Tommy Shelby leads the Peaky Blinders gang in Birmingham.', 57, '2013-09-12', 8.3, 'videos/default.mp4', 0, NULL),
(12, 22, 2, 'Cat\'s in the bag', 'Cat cat', 48, '2008-01-27', NULL, 'videos/default.mp4', 0, NULL),
(13, 22, 3, 'Ep3', 'Episode 3', 59, '2025-10-07', NULL, 'videos/default.mp4', 0, NULL),
(16, 21, 2, 'Chapter Two: The Weirdo on Maple Street', 'Eleven escapes from the lab and meets Mike, Dustin, and Lucas. The boys try to contact Will using a makeshift radio.', 55, '2016-07-15', NULL, 'videos/default.mp4', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`feedback_id`),
  KEY `user_id` (`user_id`),
  KEY `content_id` (`content_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `user_id`, `content_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 1, 1, 5, 'Absolutely mind-blowing time travel concept! The acting was superb and the plot kept me engaged throughout.', '2025-07-16 09:00:22'),
(2, 2, 1, 4, 'Great sci-fi movie with clever twists, though some parts felt a bit rushed. Still highly recommend!', '2025-07-16 13:15:10'),
(3, 3, 2, 5, 'A beautiful AI love story that makes you question what it means to be human. Stunning visuals!', '2025-07-17 03:45:33'),
(4, 1, 3, 3, 'Good action sequences but the Mars plot felt unrealistic. Enjoyable but not groundbreaking.', '2025-07-17 05:50:45'),
(5, 2, 4, 5, 'Best time loop show I\'ve seen! Each episode adds new layers to the mystery. Can\'t wait for season 2!', '2025-07-18 10:40:28'),
(6, 3, 5, 4, 'Hilarious and relatable for any programmer. The ghost in the IDE episode had me in stitches!', '2025-07-18 14:00:15'),
(7, 1, 2, 2, 'Interesting premise but the romance felt forced. The AI characters were well done though.', '2025-07-19 05:15:50'),
(8, 2, 3, 4, 'Solid action flick with amazing driving sequences. Could use more character development.', '2025-07-19 08:50:37'),
(9, 3, 1, 5, 'The time travel mechanics actually make sense! Best sci-fi movie this year.', '2025-07-20 02:45:42'),
(10, 1, 4, 4, 'The time loop concept has been done before, but this brings fresh humor to it.', '2025-07-20 12:00:18'),
(11, 2, 5, 3, 'Funny but some jokes about programming were too inside baseball for general audiences.', '2025-07-21 07:10:55'),
(12, 3, 3, 5, 'The Mars sequence was worth the price of admission alone! Stunning visuals.', '2025-07-21 14:45:30'),
(13, 6, 1, 4, 'Best ever movie', '2025-08-09 06:12:45'),
(14, 9, 4, 5, 'Best movie', '2025-08-12 15:40:26'),
(15, 9, 21, 5, 'Best show', '2025-09-03 13:46:52'),
(16, 9, 6, 4, 'Nice', '2025-09-03 14:31:58'),
(17, 12, 23, 5, 'I love this show', '2025-10-18 05:36:02');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('welcome','subscription_success','subscription_ending','subscription_expired','content_available','reminder_set','new_episode','recommendation','system','watchlist_reminder','general') NOT NULL DEFAULT 'general',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_user_read` (`user_id`,`is_read`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_notifications_user_type` (`user_id`,`type`),
  KEY `idx_notifications_created_read` (`created_at`,`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `type`, `title`, `message`, `related_id`, `is_read`, `created_at`) VALUES
(1, 12, 'reminder_set', 'Reminder Set', 'You will be notified when \"Coolie\" releases on Oct 16, 2025', 62, 1, '2025-10-15 08:09:11'),
(2, 12, 'general', 'Reminder Removed', 'Reminder for \"Coolie\" has been removed', 62, 1, '2025-10-16 07:43:44'),
(3, 12, 'reminder_set', 'Reminder Set', 'You will be notified when \"Coolie\" releases on Oct 15, 2025', 62, 1, '2025-10-16 07:43:57'),
(9, 12, 'content_available', 'New Content Available', 'The content \"Coolie\" you were waiting for is now available to watch.', 62, 0, '2025-10-16 16:36:53'),
(10, 10, 'content_available', 'New Content Available', 'The content \"Coolie\" you were waiting for is now available to watch.', 62, 0, '2025-10-16 16:36:53'),
(11, 17, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Pruthviraj! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-16 17:00:28'),
(12, 18, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Pruthvirajxx! Start exploring thousands of movies and TV shows.', NULL, 1, '2025-10-16 17:00:59'),
(13, 19, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Mallesham211! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-16 17:11:37'),
(14, 20, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Mallesham2116! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-16 17:11:55'),
(15, 21, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Dygv4! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-16 17:14:37'),
(16, 22, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Sarthak306! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-16 17:18:52'),
(17, 23, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Dygvv! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-16 17:27:26'),
(18, 24, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Dygvcx! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-17 01:56:50'),
(19, 25, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Guv55! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-17 02:31:51'),
(20, 26, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Disbbd! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-17 02:37:28'),
(21, 27, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Pravin! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-17 02:46:42'),
(22, 27, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Pravin! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-17 14:27:34'),
(23, 27, 'recommendation', 'Recommended for You', 'You might like \"Game of Thrones\" trending now.', 23, 0, '2025-10-17 14:30:59'),
(24, 27, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Pravin! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-17 14:41:08'),
(25, 27, 'recommendation', 'Recommended for You', 'You might like \"Game of Thrones\" trending now.', 23, 0, '2025-10-17 14:41:18'),
(26, 27, 'recommendation', 'Recommended for You', 'You might like \"Game of Thrones\" trending now.', 23, 0, '2025-10-17 14:41:24'),
(27, 27, 'recommendation', 'Recommended for You', 'You might like \"Game of Thrones\" trending now.', 23, 0, '2025-10-17 14:41:27'),
(28, 27, 'recommendation', 'Recommended for You', 'You might like \"Game of Thrones\" trending now.', 23, 0, '2025-10-17 14:41:40'),
(32, 28, 'reminder_set', 'Reminder Set', 'You will be notified when \"Coolie\" releases on Oct 15, 2025', 62, 0, '2025-10-17 14:45:40'),
(33, 28, 'content_available', 'New Content Available', 'The content \"Coolie\" you were waiting for is now available to watch.', 62, 0, '2025-10-17 14:54:46'),
(34, 17, 'subscription_success', 'Subscription Activated', 'Your 3 Month Plan has been successfully activated! Your subscription is valid until Jan 15, 2026.', NULL, 0, '2025-10-17 15:39:12'),
(35, 29, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Ravi123! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-17 16:01:01'),
(36, 29, 'recommendation', 'Recommended for You', 'You might like \"Game of Thrones\" trending right now.', 23, 0, '2025-10-17 16:01:01'),
(37, 29, 'recommendation', 'Recommended for You', 'You might like \"Squid Game\" popular among new users.', 29, 0, '2025-10-17 16:01:01'),
(38, 29, 'subscription_success', 'Subscription Activated', 'Your 3 Month Plan has been successfully activated! Your subscription is valid until Jan 15, 2026.', NULL, 0, '2025-10-17 16:31:56'),
(39, 12, 'subscription_success', 'Subscription Activated', 'Your 3 Month Plan has been successfully activated! Your subscription is valid until Jan 16, 2026.', NULL, 0, '2025-10-18 02:28:20'),
(40, 30, 'welcome', 'Welcome to Streamify!', 'Welcome aboard, Ashish! Start exploring thousands of movies and TV shows.', NULL, 0, '2025-10-26 04:40:22'),
(41, 30, 'recommendation', 'Recommended for You', 'You might like \"Game of Thrones\" trending right now.', 23, 0, '2025-10-26 04:40:22'),
(42, 30, 'recommendation', 'Recommended for You', 'You might like \"Squid Game\" popular among new users.', 29, 0, '2025-10-26 04:40:22'),
(47, 31, 'reminder_set', 'Reminder Set', 'You will be notified when \"Coolie\" releases on Oct 15, 2025', 62, 0, '2025-10-30 01:16:30'),
(48, 31, 'content_available', 'New Content Available', 'The content \"Coolie\" you were waiting for is now available to watch.', 62, 0, '2025-10-30 01:16:39');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `user_id` (`user_id`),
  KEY `subscription_id` (`subscription_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `user_id`, `subscription_id`, `amount`, `currency`, `payment_method`, `status`, `transaction_id`, `payment_date`, `created_at`) VALUES
(1, 9, 6, 499.00, 'INR', 'razorpay', 'completed', 'pay_ROWOKVpUThBuPD', '2025-10-02 07:32:45', '2025-10-02 07:32:45'),
(2, 9, 7, 499.00, 'INR', 'razorpay', 'completed', 'pay_ROWOKVpUThBuPD', '2025-10-02 07:35:33', '2025-10-02 07:35:33'),
(3, 10, 8, 1499.00, 'INR', 'razorpay', 'completed', 'pay_RSmnIQkvC3YPLy', '2025-10-13 02:05:10', '2025-10-13 02:05:10'),
(4, 16, 9, 499.00, 'INR', 'razorpay', 'completed', 'pay_RUakfRe2OtS7Xf', '2025-10-17 15:36:23', '2025-10-17 15:36:23'),
(5, 17, 10, 499.00, 'INR', 'razorpay', 'completed', 'pay_RUangEEChsl3w4', '2025-10-17 15:39:12', '2025-10-17 15:39:12'),
(6, 29, 11, 499.00, 'INR', 'razorpay', 'completed', 'pay_RUbh8YbkVo0ZT2', '2025-10-17 16:31:56', '2025-10-17 16:31:56'),
(7, 12, 12, 499.00, 'INR', 'razorpay', 'completed', 'pay_RUlrKXLdYPimAt', '2025-10-18 02:28:20', '2025-10-18 02:28:20'),
(8, 31, 13, 499.00, 'INR', 'razorpay', 'completed', 'pay_RZUwsDDVZxoP8b', '2025-10-30 01:10:43', '2025-10-30 01:10:43');

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

DROP TABLE IF EXISTS `reminders`;
CREATE TABLE IF NOT EXISTS `reminders` (
  `reminder_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `status` enum('active','cancelled','notified') NOT NULL DEFAULT 'active',
  `reminder_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`reminder_id`),
  UNIQUE KEY `unique_user_content` (`user_id`,`content_id`),
  KEY `content_id` (`content_id`),
  KEY `idx_status` (`status`),
  KEY `idx_reminder_date` (`reminder_date`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `reminders`
--

INSERT INTO `reminders` (`reminder_id`, `user_id`, `content_id`, `status`, `reminder_date`, `created_at`, `updated_at`) VALUES
(2, 12, 62, 'notified', '2025-10-15 15:11:00', '2025-10-16 07:43:57', '2025-10-16 16:36:53'),
(4, 10, 62, 'notified', '2025-10-15 15:11:00', '2025-10-16 08:18:59', '2025-10-16 16:36:53'),
(5, 28, 62, 'notified', '2025-10-15 15:11:00', '2025-10-17 14:45:40', '2025-10-17 14:54:46'),
(6, 31, 62, 'notified', '2025-10-15 15:11:00', '2025-10-30 01:16:30', '2025-10-30 01:16:39');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `sub_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`sub_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`sub_id`, `name`, `price`, `duration_days`, `description`, `features`, `created_at`) VALUES
(1, '1 Month Plan', 199.00, 30, 'Perfect for short-term streaming', '[\"HD Streaming\",\"1 Device\",\"Cancel Anytime\"]', '2025-10-02 03:18:19'),
(2, '3 Month Plan', 499.00, 90, 'Better value for binge watchers', '[\"HD Streaming\",\"2 Devices\",\"Offline Download\"]', '2025-10-02 03:18:19'),
(3, '12 Month Plan', 1499.00, 365, 'Best value for Streamify lovers', '[\"4K Streaming\",\"4 Devices\",\"Exclusive Content\"]', '2025-10-02 03:18:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile_no` varchar(15) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `subscription_type` enum('free','premium') DEFAULT 'free',
  `is_premium` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `mobile_no`, `password_hash`, `profile_pic`, `role`, `subscription_type`, `is_premium`, `created_at`, `last_login`) VALUES
(6, 'Mallesham22', 'mallesham10@gmail.com', NULL, '$2y$10$uxS3Knrkf7HF8fO1J.HVfe4EPUXY7bgRxIElY2XjF/HWJ0.FJX9Wa', 'uploads/profile_pics/user_6_1754836322.jpg', 'user', 'free', 0, '2025-08-07 03:49:32', NULL),
(7, 'Kira', 'kira@gmail.com', NULL, '$2y$10$Eap9rsy9RITdNlTX34mE9eMHDrP2FwkeLVNgqygmm8nYdLbBDjSKy', 'uploads/profile_pics/68954fde75b5c_1754615774.jpg', 'user', 'free', 0, '2025-08-07 21:46:14', NULL),
(8, 'John', 'john@gmail.com', NULL, '$2y$10$Sk5lXDoz1aPUlTa2RXsWmeFjXftwRG3S0vDdi6EMWt.6qVUUIbCOu', 'uploads/profile_pics/6898a7607cfaa_1754834784.png', 'user', 'free', 0, '2025-08-10 10:36:24', NULL),
(9, 'Sarthak30', 'sarthak@gmail.com', NULL, '$2y$10$M.smIcFmsYdy8iTyKkSKD.rbubmzUMXIDAQYXNX6grXLZiQjka1FG', 'uploads/profile_pics/user_9_1755014412.jpg', 'user', 'premium', 1, '2025-08-10 12:47:36', NULL),
(10, 'Prem123', 'prem123@gmail.com', '', '$2y$10$ddMZGyo85bYV05sQMZdwMObLSiZkcHCyDPryAtfOoZek5IVJZRyuq', 'uploads/profile_pics/user_10_1760955491.jpg', 'user', 'premium', 1, '2025-10-04 01:30:38', NULL),
(11, 'admin', 'admin@gmail.com', NULL, '$2y$10$IJBTRBsdzi.ZAhbiYHrhnOmwGrdNl7KKzTiuXvHFamlu3AhFwCgnG', 'uploads/profile_pics/68ed3b2357069_1760377635.jpg', 'admin', 'free', 0, '2025-10-13 14:17:15', NULL),
(12, 'Mallesham21', 'malleshamkota21@gmail.com', '9049303496', '$2y$10$n324HHcEb9Q0C762CJZzb.YNslhLLcsAaFMBakUijTeJkxoI9k926', 'uploads/profile_pics/user_12_1760463474.jpg', 'user', 'premium', 1, '2025-10-14 14:07:28', NULL),
(13, 'Raju1234', 'raju@gmail.com', NULL, '$2y$10$akBeE/3TX0Y.4SGs8vXxrOdndq2M9WANgW/ZTTlMkn3KVST1UE5mG', 'uploads/profile_pics/68f0ab22131d4_1760602914.jpg', 'user', 'free', 0, '2025-10-16 04:51:54', NULL),
(14, 'User123', 'user@gmail.com', NULL, '$2y$10$OkMbVuUalhCoFLdDQe6NMemALgUzOdxj/pUZ2IevQBYyYavRy158K', 'uploads/profile_pics/68f120a2cff79_1760632994.jpg', 'user', 'free', 0, '2025-10-16 13:13:14', NULL),
(30, 'Ashish', 'ashish@gmail.com', '9999999999', '$2y$10$7Fg1m8DKJrvJ/YywmBX3I.oNgcqpcp4UQJQhY5fXLkmIfPHKZb.Ie', 'uploads/profile_pics/68fda6360e73e_1761453622.jpg', 'user', 'free', 0, '2025-10-26 00:10:22', NULL),
(16, 'Dygv', 'sarthvvak@gmail.com', NULL, '$2y$10$VqxT/aUCPQC1cYhHOzvsP.C9ONUdfE2vw1.qIA6KPKLpIu/KPokLW', 'uploads/profile_pics/68f12465eeb86_1760633957.jpg', 'user', 'premium', 1, '2025-10-16 13:29:18', NULL),
(17, 'Pruthviraj', 'ggff@gmyg.uvb', NULL, '$2y$10$u2emIkeeFjl.POIFU9IY8e2RYWTfDcVsMOpfnPiNq3JnT6iX6XeLa', 'uploads/profile_pics/68f124ac2d8fe_1760634028.jpg', 'user', 'premium', 1, '2025-10-16 13:30:28', NULL),
(18, 'Pruthvirajxx', 'mallesharreem2110@gmail.com', NULL, '$2y$10$yH.3By/X22j4.vwZG1jDyu3/1KnA5aMZDAVxxaQ4sh4x3ovbvTFQW', 'uploads/profile_pics/68f124cb666bd_1760634059.jpg', 'user', 'free', 0, '2025-10-16 13:30:59', NULL),
(19, 'Mallesham211', 'mallesham21103@gmail.com', NULL, '$2y$10$7Ctp0Et/bRqaWou5UGm0RONywERmmB/P6zVPSYCOM9tN4ULBQr7dy', 'uploads/profile_pics/68f1274905b92_1760634697.jpg', 'user', 'free', 0, '2025-10-16 13:41:37', NULL),
(20, 'Mallesham2116', 'mallesh55am21103@gmail.com', NULL, '$2y$10$/YUId18pG1VFxDl8YtdPh.mKTvGvhSatyrO6553ov7.EcPobKiEAG', 'uploads/profile_pics/68f1275ba784d_1760634715.jpg', 'user', 'free', 0, '2025-10-16 13:41:55', NULL),
(21, 'Dygv4', 'sarth55ak@gmail.com', NULL, '$2y$10$YL/oQqxrFh.8RKlV3tKU7Ok7a9dtIM2WiLNm5wHHYPRs/JibaHG.e', 'uploads/profile_pics/68f127fd3e3a3_1760634877.jpg', 'user', 'free', 0, '2025-10-16 13:44:37', NULL),
(22, 'Sarthak306', 'sartha6k@gmail.com', NULL, '$2y$10$v.1vDMd6LkoNoKHzUy1oMO4DChbsgbHgenFKA9sbipCtLyjPzVEiG', 'uploads/profile_pics/68f128fc26332_1760635132.jpg', 'user', 'free', 0, '2025-10-16 13:48:52', NULL),
(23, 'Dygvv', 'malleshavmkota21@gmail.com', NULL, '$2y$10$CC5nYA5JxYn810seV8yRauYns4Jf0IebFvyPf9XrfLS7Hpm/41GnW', 'uploads/profile_pics/68f12afdd86dd_1760635645.jpg', 'user', 'free', 0, '2025-10-16 13:57:26', NULL),
(24, 'Dygvcx', 'sdxarthak@gmail.com', NULL, '$2y$10$47mrL7TPMK3rHz.ECoMWROW9pLY5J75n3y6mOQ9OdghnYe8kvlrrO', 'uploads/profile_pics/68f1a2626195e_1760666210.jpg', 'user', 'free', 0, '2025-10-16 22:26:50', NULL),
(25, 'Guv55', 'hhb@gmail.com', NULL, '$2y$10$33.B8syeP4Low2plv3enXeYZSJTbRxg9GR0.BNtoBZ5iFbkT0F4KW', 'uploads/profile_pics/68f1aa9745843_1760668311.jpg', 'user', 'free', 0, '2025-10-16 23:01:51', NULL),
(26, 'Disbbd', 'susg@gmail.com', NULL, '$2y$10$bpoqtRZkgeSry8c40qd9guobiOTDBPxh5W8I92TN2h1W/b1s6KW9G', 'uploads/profile_pics/68f1abe83319d_1760668648.jpg', 'user', 'free', 0, '2025-10-16 23:07:28', NULL),
(27, 'Pravin', 'pravin@gmail.com', NULL, '$2y$10$8xnWSxkiirEOo73AeVXqDO6zn38ud8KZuesBl8YptsnFVfVvse/1G', 'uploads/profile_pics/68f1ae12696d8_1760669202.jpg', 'user', 'free', 0, '2025-10-16 23:16:42', NULL),
(28, 'Gagan', 'gagan@gmail.com', NULL, '$2y$10$90zMvLVwmBXlfCbH4.cj9uTnySsgMm4.4bhzuCS0cULXKB4pySeGW', 'uploads/profile_pics/68f255e6e325e_1760712166.jpg', 'user', 'free', 0, '2025-10-17 11:12:47', NULL),
(29, 'Ravi1234', 'ravi@gmail.com', '9999999996', '$2y$10$v0.a5P2Fx9BYI0VhjrHSK.R3SEIKMFHJ5.c0gmEokrfYmLbB/2jrO', 'uploads/profile_pics/68f2683d4102c_1760716861.jpg', 'user', 'premium', 1, '2025-10-17 12:31:01', NULL),
(31, 'Pravin123', 'pravin12@gmail.com', '4567891235', '$2y$10$YuQqEC4uR30zcG2kkoBy..bexmwtr46NdprOj6wD/6VWzaRa7Q6/m', 'uploads/profile_pics/6902ba696e7e5_1761786473.jpg', 'user', 'premium', 1, '2025-10-29 19:37:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

DROP TABLE IF EXISTS `user_subscriptions`;
CREATE TABLE IF NOT EXISTS `user_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `sub_id` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `sub_id` (`sub_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Dumping data for table `user_subscriptions`
--

INSERT INTO `user_subscriptions` (`id`, `user_id`, `sub_id`, `start_date`, `end_date`, `status`) VALUES
(7, 9, 2, '2025-10-02 09:35:33', '2025-12-31 09:35:33', 'active'),
(6, 9, 2, '2025-10-02 09:32:45', '2025-12-31 09:32:45', 'expired'),
(8, 10, 3, '2025-10-13 04:05:10', '2026-10-13 04:05:10', 'active'),
(9, 16, 2, '2025-10-17 17:36:23', '2026-01-15 17:36:23', 'active'),
(10, 17, 2, '2025-10-17 17:39:12', '2026-01-15 17:39:12', 'active'),
(11, 29, 2, '2025-10-17 18:31:56', '2026-01-15 18:31:56', 'active'),
(12, 12, 2, '2025-10-18 04:28:20', '2026-01-16 04:28:20', 'active'),
(13, 31, 2, '2025-10-30 01:10:43', '2026-01-28 01:10:43', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `watchlist`
--

DROP TABLE IF EXISTS `watchlist`;
CREATE TABLE IF NOT EXISTS `watchlist` (
  `watchlist_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`watchlist_id`),
  UNIQUE KEY `unique_watchlist` (`user_id`,`content_id`),
  KEY `content_id` (`content_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Dumping data for table `watchlist`
--

INSERT INTO `watchlist` (`watchlist_id`, `user_id`, `content_id`, `added_at`) VALUES
(1, 10, 21, '2025-10-13 16:53:48'),
(11, 12, 21, '2025-10-18 02:43:56'),
(9, 12, 22, '2025-10-15 09:30:09'),
(8, 12, 23, '2025-10-14 17:39:09'),
(14, 31, 22, '2025-10-30 01:09:57');

-- --------------------------------------------------------

--
-- Table structure for table `watch_history`
--

DROP TABLE IF EXISTS `watch_history`;
CREATE TABLE IF NOT EXISTS `watch_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `content_id` int(11) DEFAULT NULL,
  `progress_percent` decimal(5,2) DEFAULT 0.00,
  `last_watched` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rating` int(11) DEFAULT NULL,
  PRIMARY KEY (`history_id`),
  KEY `user_id` (`user_id`),
  KEY `content_id` (`content_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Dumping data for table `watch_history`
--

INSERT INTO `watch_history` (`history_id`, `user_id`, `content_id`, `progress_percent`, `last_watched`, `rating`) VALUES
(1, 1, 1, 35.00, '2025-07-13 15:08:07', NULL),
(2, 1, 3, 72.00, '2025-07-15 14:08:07', 5),
(3, 2, 2, 12.50, '2025-07-15 12:08:07', NULL),
(4, 2, 4, 55.30, '2025-07-15 10:08:07', 4),
(5, 3, 5, 88.90, '2025-07-15 14:38:07', NULL),
(6, 6, 4, 2.73, '2025-08-09 04:47:30', NULL),
(7, 9, 6, 0.00, '2025-10-02 07:36:40', NULL),
(8, 12, 21, 75.50, '2025-10-18 15:11:56', NULL),
(9, 31, 21, 0.80, '2025-10-30 03:49:21', NULL),
(10, 31, 90, 28.17, '2025-10-31 09:38:49', NULL),
(11, 31, 77, 96.27, '2025-10-31 09:28:50', NULL),
(12, 31, 22, 14.45, '2025-10-31 09:40:46', NULL),
(13, 31, 23, 7.43, '2025-10-31 09:32:47', NULL),
(14, 31, 29, 7.47, '2025-10-31 09:33:03', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
