-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 31, 2025 at 12:53 PM
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
(5, 'Avatar', 'A marine explores Pandora and joins the Na\'vi people.', 'movie', 2009, 'thumbnails/avatar.jpg', 'banners/Avatar.jpg', 7.9, '2025-09-02 15:11:19', 'videos/default.mp4', 1, 0, 1874, 162, NULL, 0),
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
(20, 'The Avengers', 'Earth’s mightiest heroes unite against Loki.', 'movie', 2012, 'thumbnails/avengers.jpg', 'banners/avengers1.jpg', 8.0, '2025-09-02 15:11:19', 'videos/default.mp4', 0, 0, 9868, 143, NULL, 0),
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
(84, 'Sacred Games', 'A cop uncovers a dark conspiracy that threatens Mumbai.', 'tv_show', 2018, 'thumbnails/Sacred_Games.jpg', 'banners/Sacred_Games.jpg', 8.6, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 9977, NULL, NULL, 0),
(83, 'Mirzapur', 'A lawless town ruled by the mafia king Kaleen Bhaiya.', 'tv_show', 2018, 'thumbnails/mirzapur.jpg', 'banners/mirzapur.jpg', 8.5, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 3337, NULL, NULL, 0),
(82, 'The Family Man', 'A middle-class man secretly works as an intelligence officer.', 'tv_show', 2019, 'thumbnails/The_Family_Man.jpg', 'banners/The_Family_Man.jpg', 8.8, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 6754, NULL, NULL, 0),
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
(91, 'Sita Ramam', 'An orphaned soldier receives mysterious love letters.', 'movie', 2022, 'thumbnails/Sita_Ramam.jpg', 'banners/Sita_Ramam.jpg', 8.6, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 0, 2394, NULL, NULL, 0),
(92, 'Jersey', 'A struggling cricketer makes an emotional comeback.', 'movie', 2019, 'thumbnails/jersey.jpg', 'banners/jersey.jpg', 8.5, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 0, 3374, NULL, NULL, 0),
(93, 'Eega', 'A man reincarnates as a fly to take revenge.', 'movie', 2012, 'thumbnails/eega.jpg', 'banners/eega.jpg', 7.7, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 0, 9685, NULL, NULL, 0),
(94, 'Vikram', 'A black-ops squad hunts a masked serial killer.', 'movie', 2022, 'thumbnails/vikram.jpg', 'banners/vikram.jpg', 8.3, '2025-10-30 09:41:22', 'videos/default.mp4', 0, 1, 8308, NULL, NULL, 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
