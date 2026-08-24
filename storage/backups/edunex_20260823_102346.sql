mysqldump: [Warning] Using a password on the command line interface can be insecure.
-- MySQL dump 10.13  Distrib 8.4.7, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: edunex
-- ------------------------------------------------------
-- Server version	8.4.7

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
mysqldump: Error: 'Access denied; you need (at least one of) the PROCESS privilege(s) for this operation' when trying to dump tablespaces

--
-- Table structure for table `academic_years`
--

DROP TABLE IF EXISTS `academic_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_years` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `name` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `academic_years_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_years`
--

LOCK TABLES `academic_years` WRITE;
/*!40000 ALTER TABLE `academic_years` DISABLE KEYS */;
INSERT INTO `academic_years` VALUES (1,1,'2025/2026','2025-09-01','2026-07-31',1),(2,2,'2025/2026','2025-09-01','2026-07-31',1),(3,3,'2025/2026','2025-09-01','2026-07-31',1);
/*!40000 ALTER TABLE `academic_years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_user` (`user_id`),
  KEY `idx_activity_time` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:37:03'),(2,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:37:03'),(3,8,'transfer','Transfer code TRF-FA5C-7B0B issued for Liya Girma','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:40:25'),(4,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:42:28'),(5,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:42:28'),(6,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-21 22:52:56'),(7,1,'login','Signed in','::1','curl/8.20.0','2026-08-21 22:52:56'),(8,2,'login','Signed in as admin','::1','curl/8.20.0','2026-08-21 22:53:24'),(9,2,'login','Signed in','::1','curl/8.20.0','2026-08-21 22:53:24'),(10,2,'login','Signed in as admin','::1','curl/8.20.0','2026-08-21 22:53:37'),(11,2,'login','Signed in','::1','curl/8.20.0','2026-08-21 22:53:37'),(12,8,'login','Signed in as director','::1','curl/8.20.0','2026-08-21 22:53:50'),(13,8,'login','Signed in','::1','curl/8.20.0','2026-08-21 22:53:50'),(14,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:00:08'),(15,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:00:08'),(16,2,'school.suspended','Regional admin admin@edunex.local set school #1 to suspended','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:00:27'),(17,2,'school.active','Regional admin admin@edunex.local set school #1 to active','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:00:29'),(18,2,'announcement.create','Regional admin announced to school #1','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:02:00'),(19,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:02:29'),(20,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:02:29'),(21,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:06'),(22,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:06'),(23,2,'announcement.create','Regional admin announced to school #3','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:19'),(24,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:26'),(25,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:26'),(26,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:04:06'),(27,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:04:06'),(28,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:04:29'),(29,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:04:29'),(30,8,'ai_chat','Tutor: hello','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:08:57'),(31,8,'ai_chat','Tutor: Solve 2x + 6 = 14','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:21:38'),(32,8,'ai_chat','Tutor: hey','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:44:23'),(33,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:10:33'),(34,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:10:33'),(35,8,'ai_chat','Tutor: explain 2+2','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:10:49'),(36,8,'ai_chat','Tutor: what is Erosion?','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:11:04'),(37,8,'ai_chat','Tutor: hey','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:11:16'),(38,8,'ai_chat','Tutor: who are you?','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:11:20'),(39,8,'ai_chat','Tutor: i want maths','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:11:30'),(40,8,'ai_chat','Tutor: Cat','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:12:32'),(41,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:15:41'),(42,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:15:41'),(43,2,'announcement.create','Regional admin announced to school #1','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:15:47'),(44,2,'announcement.create','Regional admin announced to school #3','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:15:53'),(45,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:16:27'),(46,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:16:27'),(47,8,'ai_chat','Tutor: cat','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:45:06'),(48,8,'ai_chat','Tutor: C D _','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:45:38'),(49,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:45:46'),(50,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:45:46'),(51,2,'announcement.create','Regional admin announced to school #1','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:45:54'),(52,2,'announcement.create','Regional admin announced to school #3','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:46:00'),(53,2,'announcement.create','Regional admin announced to school #3','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:46:08'),(54,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:46:24'),(55,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:46:24'),(56,4,'login','Signed in as student','::1','curl/8.20.0','2026-08-22 00:56:26'),(57,4,'login','Signed in','::1','curl/8.20.0','2026-08-22 00:56:26'),(58,4,'ai_chat','Tutor: What is 2+2?','::1','curl/8.20.0','2026-08-22 01:09:10'),(59,2,'login','Signed in as admin','::1','curl/8.20.0','2026-08-22 01:09:47'),(60,4,'login','Signed in as student','::1','curl/8.20.0','2026-08-22 01:10:40'),(61,4,'login','Signed in','::1','curl/8.20.0','2026-08-22 01:10:40'),(62,4,'ai_chat','Tutor: What is 2 plus 2','::1','curl/8.20.0','2026-08-22 01:18:37'),(63,8,'ai_chat','Tutor: who are you?','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 01:28:28'),(64,8,'ai_chat','Tutor: explain 2+2','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 01:28:58'),(65,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 01:29:53'),(66,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 01:29:53'),(67,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:22:54'),(68,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:22:54'),(69,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:23:49'),(70,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:23:49'),(71,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:27:53'),(72,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:27:53'),(73,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:46:32'),(74,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:46:32'),(75,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:47:16'),(76,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:47:16'),(77,8,'user','Director created teacher Henok Arega','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:48:54'),(78,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:46:10'),(79,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:46:10'),(80,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:51:26'),(81,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:51:26'),(82,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 06:52:18'),(83,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 06:52:18'),(84,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 06:53:22'),(85,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 06:53:22'),(86,1,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:55:18'),(87,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:55:18'),(88,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 06:56:22'),(89,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 06:56:22'),(90,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 06:57:14'),(91,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 06:57:14'),(92,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 06:57:27'),(93,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 06:57:27'),(94,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 06:57:51'),(95,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 06:57:51'),(96,1,'login','Signed in as sysadmin','::1','curl/8.20.0','2026-08-23 07:01:21'),(97,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 07:01:21'),(98,1,'login','Signed in as sysadmin','::1','curl/8.20.0','2026-08-23 07:06:31'),(99,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 07:06:31'),(100,4,'login','Signed in as student','::1','curl/8.20.0','2026-08-23 07:06:31'),(101,4,'login','Signed in','::1','curl/8.20.0','2026-08-23 07:06:31'),(102,1,'login','Signed in as sysadmin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:08:52'),(103,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:08:52'),(104,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:09:05'),(105,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:09:05'),(106,1,'login','Signed in as sysadmin','::1','curl/8.20.0','2026-08-23 07:10:55'),(107,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 07:10:55'),(108,2,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 07:12:34'),(109,2,'login','Signed in','::1','curl/8.20.0','2026-08-23 07:12:34'),(110,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:15:50'),(111,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:15:50'),(112,2,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 07:20:40'),(113,2,'login','Signed in','::1','curl/8.20.0','2026-08-23 07:20:40');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_cards`
--

DROP TABLE IF EXISTS `ai_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_cards` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `deck_id` int unsigned NOT NULL,
  `front` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `back` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `box` int DEFAULT '0',
  `reviewed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deck_id` (`deck_id`),
  CONSTRAINT `ai_cards_ibfk_1` FOREIGN KEY (`deck_id`) REFERENCES `ai_decks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_cards`
--

LOCK TABLES `ai_cards` WRITE;
/*!40000 ALTER TABLE `ai_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_chats`
--

DROP TABLE IF EXISTS `ai_chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_chats` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `course_id` int unsigned DEFAULT NULL,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT 'New chat',
  `context` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `ai_chats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ai_chats_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_chats`
--

LOCK TABLES `ai_chats` WRITE;
/*!40000 ALTER TABLE `ai_chats` DISABLE KEYS */;
INSERT INTO `ai_chats` VALUES (3,4,NULL,'What is 2 plus 2',NULL,'2026-08-22 01:08:39');
/*!40000 ALTER TABLE `ai_chats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_decks`
--

DROP TABLE IF EXISTS `ai_decks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_decks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `ai_decks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_decks`
--

LOCK TABLES `ai_decks` WRITE;
/*!40000 ALTER TABLE `ai_decks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_decks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_messages`
--

DROP TABLE IF EXISTS `ai_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_messages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `chat_id` int unsigned NOT NULL,
  `role` enum('user','ai') COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chat_id` (`chat_id`),
  CONSTRAINT `ai_messages_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `ai_chats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_messages`
--

LOCK TABLES `ai_messages` WRITE;
/*!40000 ALTER TABLE `ai_messages` DISABLE KEYS */;
INSERT INTO `ai_messages` VALUES (23,3,'user','What is 2+2?','2026-08-22 01:09:10'),(24,3,'ai','2 +','2026-08-22 01:09:10'),(25,3,'user','What is 2 plus 2','2026-08-22 01:18:37'),(26,3,'ai','2 +2 =4.','2026-08-22 01:18:37');
/*!40000 ALTER TABLE `ai_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_question_bank`
--

DROP TABLE IF EXISTS `ai_question_bank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_question_bank` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned DEFAULT NULL,
  `topic` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keywords` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `type` enum('mcq','truefalse','fill','short') COLLATE utf8mb4_unicode_ci DEFAULT 'mcq',
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` json DEFAULT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `explanation` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_question_bank`
--

LOCK TABLES `ai_question_bank` WRITE;
/*!40000 ALTER TABLE `ai_question_bank` DISABLE KEYS */;
INSERT INTO `ai_question_bank` VALUES (1,1,'Algebra','linear equation solve','mcq','Solve 3x - 9 = 0','[\"-3\", \"3\", \"9\", \"27\"]','3','3x = 9 so x = 3'),(2,1,'Algebra','quadratic','mcq','The roots of x^2 - 5x + 6 = 0 are:','[\"2,3\", \"1,6\", \"-2,-3\", \"5,1\"]','2,3','Factors (x-2)(x-3)'),(3,1,'Physics','velocity speed','mcq','A car travels 120 km in 2 hours. Its average speed is:','[\"40 km/h\", \"60 km/h\", \"120 km/h\", \"240 km/h\"]','60 km/h','Speed = distance / time'),(4,1,'Data Structures','array index','truefalse','In C, array indexing starts at 1.',NULL,'false','C arrays start at index 0'),(5,1,'Data Structures','recursion','mcq','What is the base case in recursion?','[\"The recursive call\", \"The stopping condition\", \"A syntax error\", \"None\"]','The stopping condition','Base case stops recursion'),(6,1,'General','triangle angles','mcq','Sum of interior angles of a quadrilateral:','[\"180\", \"270\", \"360\", \"540\"]','360','Two triangles = 360'),(7,1,'English','grammar past tense','fill','She ___ (go) to school yesterday.',NULL,'went','Past tense of go is went'),(8,1,'Algebra','exponent','mcq','2^5 equals:','[\"16\", \"25\", \"32\", \"64\"]','32','2x2x2x2x2 = 32');
/*!40000 ALTER TABLE `ai_question_bank` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `course_id` int unsigned DEFAULT NULL,
  `author_id` int unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `pinned` tinyint(1) DEFAULT '0',
  `audience` enum('all','students','teachers','parents','course') COLLATE utf8mb4_unicode_ci DEFAULT 'all',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `course_id` (`course_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `announcements_ibfk_3` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,1,NULL,2,'Welcome to the new academic year!','We are excited to welcome all students back. The digital library now has 200+ resources.',1,'all','2026-08-21 22:36:25'),(2,1,1,3,'Midterm exam schedule published','The Algebra midterm will take place next week. Review chapters 1-2.',1,'course','2026-08-21 22:36:25'),(3,1,NULL,2,'Library maintenance on Sunday','The library will be briefly offline Sunday 02:00-04:00.',0,'all','2026-08-21 22:36:25'),(4,1,NULL,2,'hgh','hhhhhhhhhhhhhh',0,'all','2026-08-21 23:02:00'),(5,3,NULL,2,'cccccccc','ccccccccccc',0,'all','2026-08-21 23:03:19'),(6,1,NULL,2,'gdgd','dgdgdg',0,'all','2026-08-22 00:15:47'),(7,3,NULL,2,'dgdg','dgdgdgd',0,'all','2026-08-22 00:15:53'),(9,1,NULL,2,'FDSFffffffffffffff','fffffffffffffffffffffffffffffffffff',0,'all','2026-08-22 00:45:54'),(10,3,NULL,2,'fffffffffffffffff','ffffffffffffff',0,'all','2026-08-22 00:46:00'),(11,3,NULL,2,'ffffffffff','ffffffffffffffffffff',0,'all','2026-08-22 00:46:08');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignment_reviews`
--

DROP TABLE IF EXISTS `assignment_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assignment_reviews` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `submission_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `role` enum('teacher','student') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `submission_id` (`submission_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `assignment_reviews_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `assignment_submissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment_reviews`
--

LOCK TABLES `assignment_reviews` WRITE;
/*!40000 ALTER TABLE `assignment_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `assignment_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignment_submissions`
--

DROP TABLE IF EXISTS `assignment_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assignment_submissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `assignment_id` int unsigned NOT NULL,
  `student_id` int unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_late` tinyint(1) DEFAULT '0',
  `score` decimal(6,2) DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `ai_feedback` text COLLATE utf8mb4_unicode_ci,
  `graded_by` int unsigned DEFAULT NULL,
  `graded_at` datetime DEFAULT NULL,
  `status` enum('submitted','graded','returned') COLLATE utf8mb4_unicode_ci DEFAULT 'submitted',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sub` (`assignment_id`,`student_id`),
  KEY `student_id` (`student_id`),
  KEY `graded_by` (`graded_by`),
  CONSTRAINT `assignment_submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_ibfk_3` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment_submissions`
--

LOCK TABLES `assignment_submissions` WRITE;
/*!40000 ALTER TABLE `assignment_submissions` DISABLE KEYS */;
INSERT INTO `assignment_submissions` VALUES (1,1,4,'I solved problems 1-20. Please see the attached PDF.','','2026-07-28 07:00:00',0,88.00,'Excellent work! Careful with problem 14.',NULL,3,'2026-07-29 09:00:00','graded');
/*!40000 ALTER TABLE `assignment_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assignments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL,
  `teacher_id` int unsigned NOT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `rubric` text COLLATE utf8mb4_unicode_ci,
  `max_score` decimal(6,2) DEFAULT '100.00',
  `due_date` datetime DEFAULT NULL,
  `allow_late` tinyint(1) DEFAULT '1',
  `late_penalty` decimal(5,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignments`
--

LOCK TABLES `assignments` WRITE;
/*!40000 ALTER TABLE `assignments` DISABLE KEYS */;
INSERT INTO `assignments` VALUES (1,1,3,'Algebra Worksheet 1','Solve the 20 problems in the attached sheet. Show all your steps.','[{\"criterion\":\"Correctness\",\"max\":60,\"weight\":60},{\"criterion\":\"Steps shown\",\"max\":25,\"weight\":25},{\"criterion\":\"Presentation\",\"max\":15,\"weight\":15}]',100.00,'2026-08-27 01:36:25',1,10.00,'2026-08-21 22:36:25'),(2,2,3,'Motion Lab Report','Write a lab report about free-fall motion using the provided template.',NULL,50.00,'2026-08-25 01:36:25',1,5.00,'2026-08-21 22:36:25');
/*!40000 ALTER TABLE `assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `course_id` int unsigned NOT NULL,
  `student_id` int unsigned NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late','excused') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `recorded_by` int unsigned NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_att` (`course_id`,`student_id`,`date`),
  KEY `student_id` (`student_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,1,1,4,'2026-08-13','present',3,'','2026-08-21 22:36:25'),(2,1,1,4,'2026-08-14','present',3,'','2026-08-21 22:36:25'),(3,1,1,4,'2026-08-15','late',3,'','2026-08-21 22:36:25'),(4,1,1,4,'2026-08-16','present',3,'','2026-08-21 22:36:25'),(5,1,1,4,'2026-08-17','absent',3,'','2026-08-21 22:36:25'),(6,1,1,4,'2026-08-18','present',3,'','2026-08-21 22:36:25'),(7,1,1,4,'2026-08-19','present',3,'','2026-08-21 22:36:25'),(8,1,1,4,'2026-08-20','excused',3,'','2026-08-21 22:36:25'),(9,1,1,4,'2026-08-21','present',3,'','2026-08-21 22:36:25'),(10,1,1,4,'2026-08-22','present',3,'','2026-08-21 22:36:25');
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_codes`
--

DROP TABLE IF EXISTS `attendance_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_codes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `course_id` int unsigned NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int unsigned NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `course_id` (`course_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `attendance_codes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_codes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_codes`
--

LOCK TABLES `attendance_codes` WRITE;
/*!40000 ALTER TABLE `attendance_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `badges`
--

DROP TABLE IF EXISTS `badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `badges` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'medal',
  `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `xp_required` int DEFAULT '0',
  `category` enum('learning','streak','quiz','attendance','community','level') COLLATE utf8mb4_unicode_ci DEFAULT 'learning',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `badges`
--

LOCK TABLES `badges` WRITE;
/*!40000 ALTER TABLE `badges` DISABLE KEYS */;
INSERT INTO `badges` VALUES (1,'First Steps','leaf','Complete your first lesson',50,'learning'),(2,'Bookworm','books','Read 5 lessons',200,'learning'),(3,'Quiz Whiz','brain','Score 80%+ on any quiz',300,'quiz'),(4,'Perfect Attendance','target','7 days of perfect attendance',350,'attendance'),(5,'On Fire','flame','7-day learning streak',400,'streak'),(6,'Scholar','graduation','Complete a full course',600,'level'),(7,'Helping Hand','handshake','Answer 5 forum questions',250,'community'),(8,'Marathoner','run','Reach level 5',500,'level'),(9,'First Steps','leaf','Complete your first lesson',50,'learning'),(10,'Bookworm','books','Read 5 lessons',200,'learning'),(11,'Quiz Whiz','brain','Score 80%+ on any quiz',300,'quiz'),(12,'Perfect Attendance','target','7 days of perfect attendance',350,'attendance'),(13,'On Fire','flame','7-day learning streak',400,'streak'),(14,'Scholar','graduation','Complete a full course',600,'level'),(15,'Helping Hand','handshake','Answer 5 forum questions',250,'community'),(16,'Marathoner','run','Reach level 5',500,'level');
/*!40000 ALTER TABLE `badges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookmarks`
--

DROP TABLE IF EXISTS `bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookmarks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `course_id` int unsigned NOT NULL,
  `lesson_id` int unsigned NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `course_id` (`course_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookmarks_ibfk_3` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookmarks`
--

LOCK TABLES `bookmarks` WRITE;
/*!40000 ALTER TABLE `bookmarks` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookmarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_events`
--

DROP TABLE IF EXISTS `calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar_events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('class','exam','assignment','event','meeting','deadline','birthday','reminder') COLLATE utf8mb4_unicode_ci DEFAULT 'event',
  `start_at` datetime NOT NULL,
  `end_at` datetime DEFAULT NULL,
  `all_day` tinyint(1) DEFAULT '0',
  `location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `calendar_events_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `calendar_events_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_events`
--

LOCK TABLES `calendar_events` WRITE;
/*!40000 ALTER TABLE `calendar_events` DISABLE KEYS */;
INSERT INTO `calendar_events` VALUES (1,1,4,'Algebra Midterm','exam','2026-08-29 01:36:25','2026-08-29 01:36:25',0,'','','2026-08-21 22:36:25'),(2,1,4,'Physics Lab Report due','assignment','2026-08-25 01:36:25',NULL,0,'','','2026-08-21 22:36:25'),(3,1,4,'Algebra Worksheet due','assignment','2026-08-27 01:36:25',NULL,0,'','','2026-08-21 22:36:25'),(4,1,NULL,'School Sports Day','event','2026-09-03 01:36:25','2026-09-03 01:36:25',1,'','','2026-08-21 22:36:25'),(5,1,4,'Study group: Data Structures','meeting','2026-08-24 01:36:25',NULL,0,'','','2026-08-21 22:36:25');
/*!40000 ALTER TABLE `calendar_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certificates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int unsigned NOT NULL,
  `course_id` int unsigned NOT NULL,
  `cert_code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issued_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `grade` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cert_code` (`cert_code`),
  UNIQUE KEY `qr_hash` (`qr_hash`),
  KEY `student_id` (`student_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certificates`
--

LOCK TABLES `certificates` WRITE;
/*!40000 ALTER TABLE `certificates` DISABLE KEYS */;
/*!40000 ALTER TABLE `certificates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `challenges`
--

DROP TABLE IF EXISTS `challenges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `challenges` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reward_xp` int DEFAULT '50',
  `starts_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `challenges_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `challenges`
--

LOCK TABLES `challenges` WRITE;
/*!40000 ALTER TABLE `challenges` DISABLE KEYS */;
INSERT INTO `challenges` VALUES (1,1,'Study 5 lessons this week','Complete 5 lessons in any course.',100,'2026-08-19','2026-08-26'),(2,1,'Perfect quiz score','Get 100% on any practice quiz.',150,'2026-08-22','2026-08-29');
/*!40000 ALTER TABLE `challenges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversation_members`
--

DROP TABLE IF EXISTS `conversation_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversation_members` (
  `conversation_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `last_read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`conversation_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `conversation_members_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversation_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversation_members`
--

LOCK TABLES `conversation_members` WRITE;
/*!40000 ALTER TABLE `conversation_members` DISABLE KEYS */;
INSERT INTO `conversation_members` VALUES (1,3,NULL),(1,4,NULL),(2,2,'2026-08-22 03:15:59'),(2,3,NULL),(2,4,NULL),(3,4,'2026-08-22 02:50:17'),(3,8,'2026-08-22 04:29:33'),(5,5,NULL),(5,8,'2026-08-22 04:29:42'),(8,2,'2026-08-23 08:23:25'),(8,8,'2026-08-22 04:29:16');
/*!40000 ALTER TABLE `conversation_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `is_group` tinyint(1) DEFAULT '0',
  `title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `conv_key` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversations`
--

LOCK TABLES `conversations` WRITE;
/*!40000 ALTER TABLE `conversations` DISABLE KEYS */;
INSERT INTO `conversations` VALUES (1,1,0,'Liya & David','11114c0cc54c878610cab851b8341a3cd468ae93b470a249601ec677681c1391','2026-08-21 22:36:25'),(2,1,1,'Math 101 Class Group','098f8063a0592f300b7a1b5dc8e75364022dc7c392bb6039fb19bf0137a54650','2026-08-21 22:36:25'),(3,1,0,'','84aba32a1b0cff4ab63487bccf71bbb6a52fc9fcecac7c55f1b725a139f906e3','2026-08-21 23:21:49'),(4,1,0,'','test','2026-08-21 23:36:46'),(5,1,1,'tr','dd400531f2547282b27650b617ec48a569e29cf6372ef7e359099105aaf38990','2026-08-22 00:14:33'),(8,1,0,'','edc5f7bec5834baee0edf3fdff05e8267fee58431c720346ad5a2bc52ed47608','2026-08-22 01:28:09');
/*!40000 ALTER TABLE `conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_enrollments`
--

DROP TABLE IF EXISTS `course_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_enrollments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `progress` decimal(5,2) DEFAULT '0.00',
  `completed` tinyint(1) DEFAULT '0',
  `completed_at` datetime DEFAULT NULL,
  `enrolled_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_enroll` (`course_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `course_enrollments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_enrollments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_enrollments`
--

LOCK TABLES `course_enrollments` WRITE;
/*!40000 ALTER TABLE `course_enrollments` DISABLE KEYS */;
INSERT INTO `course_enrollments` VALUES (1,1,4,50.00,0,NULL,'2026-08-21 22:36:25'),(2,2,4,0.00,0,NULL,'2026-08-21 22:36:25'),(3,3,4,25.00,0,NULL,'2026-08-21 22:36:25');
/*!40000 ALTER TABLE `course_enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_modules`
--

DROP TABLE IF EXISTS `course_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_modules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `course_modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_modules`
--

LOCK TABLES `course_modules` WRITE;
/*!40000 ALTER TABLE `course_modules` DISABLE KEYS */;
INSERT INTO `course_modules` VALUES (1,1,'Chapter 1: Algebra',1),(2,1,'Chapter 2: Geometry',2),(3,2,'Chapter 1: Motion',1),(4,3,'Module 1: Basics',1),(5,3,'Module 2: Trees',2);
/*!40000 ALTER TABLE `course_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `teacher_id` int unsigned NOT NULL,
  `subject_id` int unsigned DEFAULT NULL,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `level` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `price` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `courses_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,1,3,1,'Mathematics 101','MATH101','Foundations of algebra, geometry and calculus for Grade 9 students.','','Grade 9','published',0.00,'2026-08-21 22:36:25'),(2,1,3,2,'Physics Foundations','PHY101','Mechanics, motion and energy explained with real Ethiopian examples.','','Grade 9','published',0.00,'2026-08-21 22:36:25'),(3,3,7,4,'Data Structures','CS201','Arrays, linked lists, stacks, queues, trees and graphs with C.','','1st Year','published',0.00,'2026-08-21 22:36:25');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `head` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `faculty_id` int unsigned DEFAULT NULL,
  `status` enum('active','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `idx_dept_faculty` (`faculty_id`),
  CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `departments_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,1,'Science','Dr. Bekele',NULL,'active'),(2,1,'Languages','Mrs. Tigist',NULL,'active'),(3,2,'Computer Science','Prof. Alem',NULL,'active'),(4,2,'Engineering','Dr. Marta',NULL,'active'),(5,3,'General','Mr. Dawit',NULL,'active');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_answers`
--

DROP TABLE IF EXISTS `exam_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_answers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `attempt_id` int unsigned NOT NULL,
  `question_id` int unsigned NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci,
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_earned` decimal(5,2) DEFAULT '0.00',
  `feedback` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_answer` (`attempt_id`,`question_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `exam_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `exam_attempts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `exam_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_answers`
--

LOCK TABLES `exam_answers` WRITE;
/*!40000 ALTER TABLE `exam_answers` DISABLE KEYS */;
INSERT INTO `exam_answers` VALUES (1,1,1,'3',1,2.00,NULL),(2,1,2,'3',1,1.00,NULL),(3,1,3,'true',1,1.00,NULL),(4,1,4,'length',1,1.00,NULL),(5,1,5,'Linear has degree 1, quadratic has degree 2.',NULL,2.00,NULL);
/*!40000 ALTER TABLE `exam_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_attempts`
--

DROP TABLE IF EXISTS `exam_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_attempts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int unsigned NOT NULL,
  `student_id` int unsigned NOT NULL,
  `started_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `submitted_at` datetime DEFAULT NULL,
  `auto_save` text COLLATE utf8mb4_unicode_ci,
  `flagged` text COLLATE utf8mb4_unicode_ci,
  `score` decimal(6,2) DEFAULT NULL,
  `total_points` decimal(6,2) DEFAULT '0.00',
  `status` enum('in_progress','submitted','graded') COLLATE utf8mb4_unicode_ci DEFAULT 'in_progress',
  PRIMARY KEY (`id`),
  KEY `exam_id` (`exam_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `exam_attempts_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_attempts_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_attempts`
--

LOCK TABLES `exam_attempts` WRITE;
/*!40000 ALTER TABLE `exam_attempts` DISABLE KEYS */;
INSERT INTO `exam_attempts` VALUES (1,1,4,'2026-07-20 09:00:00','2026-07-20 09:20:00',NULL,NULL,7.00,12.00,'graded');
/*!40000 ALTER TABLE `exam_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_questions`
--

DROP TABLE IF EXISTS `exam_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_questions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int unsigned NOT NULL,
  `type` enum('mcq','truefalse','essay','fill','coding','matching','order','image','audio','video') COLLATE utf8mb4_unicode_ci NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` json DEFAULT NULL,
  `correct_answer` text COLLATE utf8mb4_unicode_ci,
  `points` decimal(5,2) DEFAULT '1.00',
  `media_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `explanation` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `exam_questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_questions`
--

LOCK TABLES `exam_questions` WRITE;
/*!40000 ALTER TABLE `exam_questions` DISABLE KEYS */;
INSERT INTO `exam_questions` VALUES (1,1,'mcq','Solve: 2x + 4 = 10. What is x?','[\"2\", \"3\", \"4\", \"5\"]','3',2.00,'','2x = 6, x = 3',1),(2,1,'mcq','What is the slope of y = 3x + 2?','[\"1\", \"2\", \"3\", \"6\"]','3',1.00,'','Slope is the coefficient of x',2),(3,1,'truefalse','The sum of interior angles of a triangle is 180 degrees.',NULL,'true',1.00,'','Always true for Euclidean triangles',3),(4,1,'fill','The formula for the area of a rectangle is A = _ x width.',NULL,'length',1.00,'','A = l x w',4),(5,1,'essay','Explain the difference between a linear and a quadratic equation with one example each.',NULL,NULL,5.00,'','Linear: degree 1; quadratic: degree 2',5),(6,1,'mcq','If F(0)=0, F(1)=1, F(n)=F(n-1)+F(n-2), what is F(4)?','[\"1\", \"2\", \"3\", \"5\"]','3',2.00,'','Fibonacci: 0,1,1,2,3,5 -> F(4)=3',6),(7,2,'mcq','In C, what is the index of the first element of an array?','[\"0\", \"1\", \"-1\", \"None\"]','0',1.00,'','C arrays are zero-indexed',1),(8,2,'truefalse','An array stores elements of different types.',NULL,'false',1.00,'','Arrays hold one type',2),(9,3,'truefalse','A triangle can have two right angles.',NULL,'false',1.00,'','Sum would exceed 180',1),(10,3,'mcq','Recursion is when a function...','[\"Loops forever\", \"Calls itself\", \"Returns a string\", \"Uses no memory\"]','Calls itself',2.00,'','Recursion = self-calling function',2);
/*!40000 ALTER TABLE `exam_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exams`
--

DROP TABLE IF EXISTS `exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exams` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL,
  `teacher_id` int unsigned NOT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('quiz','midterm','final','practice') COLLATE utf8mb4_unicode_ci DEFAULT 'quiz',
  `duration_min` int DEFAULT '30',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `passing_score` decimal(5,2) DEFAULT '50.00',
  `auto_grade` tinyint(1) DEFAULT '1',
  `shuffle_questions` tinyint(1) DEFAULT '0',
  `show_result` tinyint(1) DEFAULT '1',
  `status` enum('draft','published','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `results_sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exams_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exams`
--

LOCK TABLES `exams` WRITE;
/*!40000 ALTER TABLE `exams` DISABLE KEYS */;
INSERT INTO `exams` VALUES (1,1,3,'Algebra Midterm','Covers chapters 1-2: algebra basics and linear equations.','midterm',30,'2026-08-22 01:36:25','2026-08-29 01:36:25',50.00,1,0,1,'published',NULL,'2026-08-21 22:36:25'),(2,3,7,'Data Structures Quiz 1','Quick quiz on arrays and pointers.','quiz',15,'2026-08-22 01:36:25','2026-08-25 01:36:25',50.00,1,0,1,'published',NULL,'2026-08-21 22:36:25'),(3,1,3,'Practice: True/False','Practice session for exam readiness.','practice',10,'2026-08-22 01:36:25','2026-08-24 01:36:25',50.00,1,0,1,'published',NULL,'2026-08-21 22:36:25');
/*!40000 ALTER TABLE `exams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faculties`
--

DROP TABLE IF EXISTS `faculties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faculties` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dean_id` int unsigned DEFAULT NULL,
  `vice_dean_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fac_school` (`school_id`),
  KEY `dean_id` (`dean_id`),
  KEY `vice_dean_id` (`vice_dean_id`),
  CONSTRAINT `faculties_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `faculties_ibfk_2` FOREIGN KEY (`dean_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `faculties_ibfk_3` FOREIGN KEY (`vice_dean_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faculties`
--

LOCK TABLES `faculties` WRITE;
/*!40000 ALTER TABLE `faculties` DISABLE KEYS */;
/*!40000 ALTER TABLE `faculties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_versions`
--

DROP TABLE IF EXISTS `file_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `file_versions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int unsigned NOT NULL,
  `version` int NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int DEFAULT '0',
  `created_by` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `file_versions_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  CONSTRAINT `file_versions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_versions`
--

LOCK TABLES `file_versions` WRITE;
/*!40000 ALTER TABLE `file_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `files` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `size` int DEFAULT '0',
  `version` int DEFAULT '1',
  `parent_id` int unsigned DEFAULT NULL,
  `is_folder` tinyint(1) DEFAULT '0',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `files_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `files_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
INSERT INTO `files` VALUES (1,1,2,'My Documents','My Documents','','folder',0,1,NULL,1,NULL,'2026-08-21 22:36:25');
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_posts`
--

DROP TABLE IF EXISTS `forum_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forum_posts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` int unsigned NOT NULL,
  `author_id` int unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_answer` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `forum_topics` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forum_posts_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_posts`
--

LOCK TABLES `forum_posts` WRITE;
/*!40000 ALTER TABLE `forum_posts` DISABLE KEYS */;
INSERT INTO `forum_posts` VALUES (1,1,3,'Try the AC method: multiply a and c, find factors that sum to b.',1,'2026-08-21 22:36:25'),(2,1,4,'That helped a lot, thank you!',0,'2026-08-21 22:36:25');
/*!40000 ALTER TABLE `forum_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_topics`
--

DROP TABLE IF EXISTS `forum_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forum_topics` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL,
  `author_id` int unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `pinned` tinyint(1) DEFAULT '0',
  `views` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `forum_topics_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forum_topics_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_topics`
--

LOCK TABLES `forum_topics` WRITE;
/*!40000 ALTER TABLE `forum_topics` DISABLE KEYS */;
INSERT INTO `forum_topics` VALUES (1,1,4,'How do I solve quadratic equations?','I keep getting confused with factoring. Any tips?',0,0,'2026-08-21 22:36:25'),(2,1,3,'Office hours this week','I will be available Wednesday 14:00-16:00.',0,0,'2026-08-21 22:36:25');
/*!40000 ALTER TABLE `forum_topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `goals`
--

DROP TABLE IF EXISTS `goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `goals` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target` int DEFAULT '100',
  `current` int DEFAULT '0',
  `unit` enum('xp','lessons','days','quizzes') COLLATE utf8mb4_unicode_ci DEFAULT 'lessons',
  `due_date` date DEFAULT NULL,
  `completed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `goals`
--

LOCK TABLES `goals` WRITE;
/*!40000 ALTER TABLE `goals` DISABLE KEYS */;
INSERT INTO `goals` VALUES (1,4,'Finish Mathematics 101',100,50,'lessons','2026-09-21',0);
/*!40000 ALTER TABLE `goals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grade_audit`
--

DROP TABLE IF EXISTS `grade_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grade_audit` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int unsigned NOT NULL,
  `course_id` int unsigned NOT NULL,
  `school_id` int unsigned NOT NULL,
  `assessment_type` enum('exam','assignment','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'exam',
  `assessment_id` int unsigned NOT NULL,
  `old_score` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_score` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` enum('create','update','delete','override') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'update',
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `actor_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_grade_audit_student` (`student_id`),
  KEY `idx_grade_audit_course` (`course_id`),
  KEY `idx_grade_audit_assessment` (`assessment_type`,`assessment_id`),
  KEY `school_id` (`school_id`),
  KEY `actor_id` (`actor_id`),
  CONSTRAINT `grade_audit_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grade_audit_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grade_audit_ibfk_3` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grade_audit_ibfk_4` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grade_audit`
--

LOCK TABLES `grade_audit` WRITE;
/*!40000 ALTER TABLE `grade_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `grade_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ledger`
--

DROP TABLE IF EXISTS `ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ledger` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `actor_id` int unsigned NOT NULL,
  `event_type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` int unsigned NOT NULL,
  `payload` text COLLATE utf8mb4_unicode_ci,
  `prev_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ledger_school` (`school_id`,`id`),
  KEY `idx_ledger_entity` (`entity_type`,`entity_id`),
  CONSTRAINT `ledger_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ledger`
--

LOCK TABLES `ledger` WRITE;
/*!40000 ALTER TABLE `ledger` DISABLE KEYS */;
/*!40000 ALTER TABLE `ledger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lesson_progress`
--

DROP TABLE IF EXISTS `lesson_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_progress` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `lesson_id` int unsigned NOT NULL,
  `course_id` int unsigned NOT NULL,
  `completed` tinyint(1) DEFAULT '0',
  `position_sec` int DEFAULT '0',
  `last_accessed` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lesson_prog` (`user_id`,`lesson_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `lesson_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_progress_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_progress`
--

LOCK TABLES `lesson_progress` WRITE;
/*!40000 ALTER TABLE `lesson_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `lesson_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lessons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int unsigned NOT NULL,
  `course_id` int unsigned NOT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('video','pdf','notes','slides','audio','link') COLLATE utf8mb4_unicode_ci DEFAULT 'notes',
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `video_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `duration_min` int DEFAULT '0',
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `course_modules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lessons_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons`
--

LOCK TABLES `lessons` WRITE;
/*!40000 ALTER TABLE `lessons` DISABLE KEYS */;
INSERT INTO `lessons` VALUES (1,1,1,'Introduction to Algebra','notes','<p>Algebra is the study of mathematical symbols and the rules for manipulating them. It is a unifying thread of almost all of mathematics.</p><p><b>Key concepts:</b> variables, expressions, equations, polynomials.</p><p>A linear equation has the form ax + b = 0. To solve, isolate x: x = -b/a.</p><p><b>Example:</b> 2x + 4 = 10 &rarr; 2x = 6 &rarr; x = 3.</p><p>Recursion in programming is when a function calls itself. In algebra, think of it as defining a value in terms of previous values, like the Fibonacci sequence: F(n) = F(n-1) + F(n-2).</p>','','',25,1),(2,1,1,'Linear Equations','video','<p>Watch the video and practice solving linear equations step by step.</p>','','',18,2),(3,2,1,'Triangles and Angles','notes','<p>Triangles are three-sided polygons. The sum of interior angles is always 180 degrees.</p><p>Types: equilateral, isosceles, scalene, right-angled.</p>','','',20,1),(4,3,2,'Distance and Velocity','video','<p>Velocity is displacement over time. Distance = speed &times; time.</p>','','',15,1),(5,4,3,'Arrays in C','notes','<p>An array is a contiguous block of memory holding elements of the same type. Indexing starts at 0 in C.</p>','','',30,1),(6,5,3,'Binary Trees','notes','<p>A binary tree is a hierarchical structure where each node has at most two children. Trees are recursive data structures.</p>','','',35,1);
/*!40000 ALTER TABLE `lessons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `library_favorites`
--

DROP TABLE IF EXISTS `library_favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `library_favorites` (
  `user_id` int unsigned NOT NULL,
  `item_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`item_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `library_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `library_favorites_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `library_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `library_favorites`
--

LOCK TABLES `library_favorites` WRITE;
/*!40000 ALTER TABLE `library_favorites` DISABLE KEYS */;
INSERT INTO `library_favorites` VALUES (8,7,'2026-08-21 23:04:52');
/*!40000 ALTER TABLE `library_favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `library_items`
--

DROP TABLE IF EXISTS `library_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `library_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('book','notes','paper','slides','video','past_exam','tutorial') COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `category` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci,
  `cover` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `downloads` int DEFAULT '0',
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `idx_library_search` (`title`,`category`),
  CONSTRAINT `library_items_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `library_items`
--

LOCK TABLES `library_items` WRITE;
/*!40000 ALTER TABLE `library_items` DISABLE KEYS */;
INSERT INTO `library_items` VALUES (1,1,'Algebra Workbook 2026','book','MoE Ethiopia','Mathematics','Official workbook for Grade 9 algebra.','','',120,'published','2026-08-21 22:36:25'),(2,1,'Physics Notes Chapter 1','notes','Mr. David Alemu','Physics','Handwritten-style notes on motion.','','',45,'published','2026-08-21 22:36:25'),(3,1,'2019 National Exam Math','past_exam','MoE','Exams','Past national exam paper for practice.','','',230,'published','2026-08-21 22:36:25'),(4,1,'Introduction to C Programming','book','K&R','Programming','Classic C programming reference.','','',89,'published','2026-08-21 22:36:25'),(5,1,'Data Structures Slides','slides','Prof. Kebede','Computer Science','Lecture slides on trees and graphs.','','',60,'published','2026-08-21 22:36:25'),(6,1,'Amharic-English Dictionary','book','Academy','Languages','Bilingual dictionary for students.','','',150,'published','2026-08-21 22:36:25'),(7,1,'Ethiopian History Timeline','notes','Mrs. Tigist','History','Quick timeline of key events.','','',34,'published','2026-08-21 22:36:25'),(8,1,'Science Fair Video','video','BDU Media','Science','Documentary on Ethiopian scientists.','','',76,'published','2026-08-21 22:36:25');
/*!40000 ALTER TABLE `library_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_history`
--

DROP TABLE IF EXISTS `login_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `status` enum('success','failed','locked') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_history`
--

LOCK TABLES `login_history` WRITE;
/*!40000 ALTER TABLE `login_history` DISABLE KEYS */;
INSERT INTO `login_history` VALUES (1,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:37:03'),(2,2,'failed','::1','','2026-08-21 22:42:20'),(3,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:42:28'),(4,1,'success','::1','curl/8.20.0','2026-08-21 22:52:56'),(5,2,'success','::1','curl/8.20.0','2026-08-21 22:53:24'),(6,2,'success','::1','curl/8.20.0','2026-08-21 22:53:37'),(7,8,'success','::1','curl/8.20.0','2026-08-21 22:53:50'),(8,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:02:29'),(9,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:06'),(10,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:26'),(11,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:04:06'),(12,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:04:29'),(13,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:10:33'),(14,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:15:41'),(15,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:16:27'),(16,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:45:46'),(17,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:46:24'),(18,4,'success','::1','curl/8.20.0','2026-08-22 00:56:26'),(19,4,'success','::1','curl/8.20.0','2026-08-22 01:10:39'),(20,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 01:29:53'),(21,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:22:54'),(22,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:23:49'),(23,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:27:53'),(24,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:46:32'),(25,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:47:16'),(26,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:46:10'),(27,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:51:26'),(28,1,'success','::1','curl/8.20.0','2026-08-23 06:52:18'),(29,1,'success','::1','curl/8.20.0','2026-08-23 06:53:22'),(30,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:55:18'),(31,1,'success','::1','curl/8.20.0','2026-08-23 06:56:22'),(32,1,'success','::1','curl/8.20.0','2026-08-23 06:57:14'),(33,1,'success','::1','curl/8.20.0','2026-08-23 06:57:27'),(34,1,'success','::1','curl/8.20.0','2026-08-23 06:57:51'),(35,1,'success','::1','curl/8.20.0','2026-08-23 07:01:21'),(36,1,'success','::1','curl/8.20.0','2026-08-23 07:06:31'),(37,4,'success','::1','curl/8.20.0','2026-08-23 07:06:31'),(38,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:08:52'),(39,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:09:05'),(40,1,'success','::1','curl/8.20.0','2026-08-23 07:10:55'),(41,2,'success','::1','curl/8.20.0','2026-08-23 07:12:34'),(42,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:15:50'),(43,2,'success','::1','curl/8.20.0','2026-08-23 07:20:40');
/*!40000 ALTER TABLE `login_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` int unsigned NOT NULL,
  `sender_id` int unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `hmac` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `conversation_id` (`conversation_id`),
  KEY `sender_id` (`sender_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,1,3,'Great question in the forum today!','','','2026-08-21 22:36:25'),(2,1,4,'Thank you teacher!','','','2026-08-21 22:36:25'),(3,2,3,'Reminder: midterm next week.','','','2026-08-21 22:36:25'),(4,2,2,'Don\'t forget to register for the science fair.','','','2026-08-21 22:36:25'),(5,3,8,'KG28RCT491Jvuuf54EH6fQ6dAdzICG5NXFaURawqWDUe+sd5vqUPGsJmwibGqkqWPPlMOjLq+LHQdG66lRsYDTuHBn5uGIzo1mc=','d98e6c32cb2bf881ca1641efdc94ea858cdd79fccc8ddda52e38dbc61abeb0b3','','2026-08-22 00:14:08'),(6,5,8,'gR8XWUksf6OflCBRLcAztTYTyEqGtEn+cjrF1mIJX2KYPvod30qd','3afc2982562d2f3e419579c895f61f9046dc3f8af865242d53a4f591333bfa95','','2026-08-22 00:14:39'),(10,8,8,'GcyEe33RVPCp7VWQ32AILr7wHRGYixBN6YT7EVfAfdg=','c6a222b6da7ef6fe0f90fab72cd0759629ccdfec0fc4da9adf360813c8307c2c','','2026-08-22 01:28:09'),(11,8,8,'Og49V9gWjJfLrUM3nxHlRjFUC5l5PvQ0+UVOjm9Q','37c05399db8adce168467e399c3491f24cf6fff34520949d418c75552b34f770','','2026-08-22 01:28:19');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `type` enum('assignment','exam','feedback','announcement','achievement','message','system','reminder') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`,`read_at`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,4,'assignment','Algebra Worksheet 1 graded','You received 88/100.','assignments/view?sub=1',NULL,'2026-08-21 22:36:25'),(2,4,'exam','Algebra Midterm is coming up','The midterm starts in 7 days.','exams/view?e=1',NULL,'2026-08-21 22:36:25'),(3,4,'announcement','Welcome to Edunex','Explore your courses and AI tutor!','communication/announcement&id=1',NULL,'2026-08-21 22:36:25'),(4,4,'message','New message','hello teachers there will be meeting on Tuesdy','messages&conv=3',NULL,'2026-08-22 00:14:08'),(5,5,'message','New message','hello henok','messages&conv=5',NULL,'2026-08-22 00:14:39'),(14,2,'announcement','FDSFffffffffffffff','fffffffffffffffffffffffffffffffffff','communication/announcement&id=9','2026-08-22 03:46:15','2026-08-22 00:45:54'),(15,8,'announcement','FDSFffffffffffffff','fffffffffffffffffffffffffffffffffff','communication/announcement&id=9','2026-08-22 04:28:19','2026-08-22 00:45:54'),(16,3,'announcement','FDSFffffffffffffff','fffffffffffffffffffffffffffffffffff','communication/announcement&id=9',NULL,'2026-08-22 00:45:54'),(17,4,'announcement','FDSFffffffffffffff','fffffffffffffffffffffffffffffffffff','communication/announcement&id=9',NULL,'2026-08-22 00:45:54'),(18,5,'announcement','FDSFffffffffffffff','fffffffffffffffffffffffffffffffffff','communication/announcement&id=9',NULL,'2026-08-22 00:45:54'),(19,7,'announcement','fffffffffffffffff','ffffffffffffff','communication/announcement&id=10',NULL,'2026-08-22 00:46:00'),(20,7,'announcement','ffffffffff','ffffffffffffffffffff','communication/announcement&id=11',NULL,'2026-08-22 00:46:08'),(21,2,'message','Suggestion about \"Library maintenance on Sunday\"','Test suggestion','messages&conv=7','2026-08-22 04:30:14','2026-08-22 00:59:13'),(22,2,'message','Suggestion about \"Library maintenance on Sunday\"','This is a test suggestion','messages&conv=7','2026-08-22 04:30:14','2026-08-22 01:08:24'),(23,2,'message','Suggestion about \"FDSFffffffffffffff\"','okay','messages&conv=8','2026-08-22 04:30:14','2026-08-22 01:28:09'),(24,2,'message','Suggestion about \"FDSFffffffffffffff\"','hh','messages&conv=8','2026-08-22 04:30:14','2026-08-22 01:28:19');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otp_codes`
--

DROP TABLE IF EXISTS `otp_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `otp_codes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` enum('verify','2fa','reset','transfer') COLLATE utf8mb4_unicode_ci DEFAULT 'verify',
  `used` tinyint(1) DEFAULT '0',
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `otp_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `otp_codes`
--

LOCK TABLES `otp_codes` WRITE;
/*!40000 ALTER TABLE `otp_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `otp_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reactions`
--

DROP TABLE IF EXISTS `reactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `target_type` enum('post','forum','message','announcement') COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_id` int unsigned NOT NULL,
  `reaction` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'like',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_react` (`user_id`,`target_type`,`target_id`),
  CONSTRAINT `reactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reactions`
--

LOCK TABLES `reactions` WRITE;
/*!40000 ALTER TABLE `reactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `reactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reports` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `type` enum('attendance','grade','course','teacher','student','department','financial','system') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `format` enum('pdf','excel','csv') COLLATE utf8mb4_unicode_ci DEFAULT 'pdf',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `filters` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role` enum('admin','director','teacher','student','parent','guest') COLLATE utf8mb4_unicode_ci NOT NULL,
  `permission` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`role`,`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES ('admin','ai.assistant'),('admin','ai.flashcards'),('admin','ai.tutor'),('admin','analytics.view'),('admin','announcements.manage'),('admin','assignments.create'),('admin','assignments.grade'),('admin','assignments.manage'),('admin','attendance.export'),('admin','attendance.manage'),('admin','attendance.record'),('admin','attendance.view'),('admin','backups.manage'),('admin','badges.manage'),('admin','calendar.create'),('admin','calendar.view'),('admin','comments.view'),('admin','courses.create'),('admin','courses.manage'),('admin','courses.view'),('admin','dashboard'),('admin','exams.create'),('admin','exams.grade'),('admin','exams.manage'),('admin','exams.take'),('admin','exams.view'),('admin','files.manage'),('admin','files.upload'),('admin','files.view'),('admin','forum.moderate'),('admin','forum.post'),('admin','gamification.view'),('admin','goals.award'),('admin','grades.export'),('admin','grades.manage'),('admin','grades.view'),('admin','leaderboard.view'),('admin','ledger.verify'),('admin','lessons.manage'),('admin','library.manage'),('admin','library.upload'),('admin','library.view'),('admin','logs.view'),('admin','messages.send'),('admin','messages.view'),('admin','notifications.view'),('admin','profile.view'),('admin','reports.export'),('admin','reports.view'),('admin','search.global'),('admin','settings.manage'),('admin','transfers.approve'),('admin','transfers.manage'),('admin','users.create'),('admin','users.manage'),('admin','users.view'),('director','accounting.view'),('director','analytics.view'),('director','announcements.manage'),('director','attendance.view'),('director','courses.view'),('director','exams.view'),('director','library.view'),('director','messages.send'),('director','messages.view'),('director','reports.export'),('director','reports.generate'),('director','schools.view'),('director','students.view'),('director','teachers.manage'),('director','transfers.manage'),('director','users.import'),('director','users.view'),('teacher','ai.assistant'),('teacher','ai.flashcards'),('teacher','ai.tutor'),('teacher','analytics.view'),('teacher','announcements.manage'),('teacher','assignments.create'),('teacher','assignments.grade'),('teacher','assignments.manage'),('teacher','attendance.manage'),('teacher','attendance.record'),('teacher','attendance.view'),('teacher','calendar.create'),('teacher','calendar.view'),('teacher','courses.manage'),('teacher','courses.view'),('teacher','exams.create'),('teacher','exams.grade'),('teacher','exams.manage'),('teacher','files.upload'),('teacher','files.view'),('teacher','forum.manage'),('teacher','forum.post'),('teacher','goals.award'),('teacher','grades.manage'),('teacher','grades.view'),('teacher','lessons.manage'),('teacher','library.manage'),('teacher','library.upload'),('teacher','library.view'),('teacher','messages.send'),('teacher','messages.view'),('teacher','parents.manage'),('teacher','reports.generate'),('teacher','reports.view'),('teacher','students.verify'),('teacher','users.import'),('student','ai.assistant'),('student','ai.flashcards'),('student','ai.tutor'),('student','assignments.submit'),('student','attendance.view'),('student','calendar.view'),('student','certificates.view'),('student','courses.enroll'),('student','courses.view'),('student','exams.take'),('student','exams.view'),('student','files.upload'),('student','files.view'),('student','forum.post'),('student','forum.reply'),('student','gamification.view'),('student','goals.view'),('student','grades.view'),('student','leaderboard.view'),('student','library.borrow'),('student','library.view'),('student','messages.send'),('student','transfers.apply'),('student','transfers.view'),('parent','assignments.view'),('parent','attendance.view'),('parent','calendar.view'),('parent','children.view'),('parent','courses.view'),('parent','grades.view'),('parent','messages.send'),('parent','messages.view'),('parent','reports.view'),('guest','courses.view');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_modules`
--

DROP TABLE IF EXISTS `school_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_modules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `module_key` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  `installed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_school_mod` (`school_id`,`module_key`),
  CONSTRAINT `school_modules_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_modules`
--

LOCK TABLES `school_modules` WRITE;
/*!40000 ALTER TABLE `school_modules` DISABLE KEYS */;
INSERT INTO `school_modules` VALUES (1,1,'core',1,NULL),(2,1,'auth',1,NULL),(3,1,'user-management',1,NULL),(4,1,'security',1,NULL),(5,1,'backup',1,NULL),(6,1,'api',1,NULL),(7,1,'analytics',1,NULL),(8,1,'messaging',1,NULL),(9,1,'notifications',1,NULL),(10,1,'teacher-portal',1,NULL),(11,1,'parent-portal',1,NULL),(12,1,'student-portal',1,NULL),(13,1,'academic',1,NULL),(14,1,'attendance',1,NULL),(15,1,'library',1,NULL),(16,1,'ai-tutor',1,NULL),(17,1,'gamification',1,NULL),(18,1,'high-school',1,NULL),(19,1,'examination',1,NULL),(20,1,'certificate',1,NULL),(21,1,'lms',1,NULL),(22,1,'online-courses',1,NULL),(23,2,'core',1,NULL),(24,2,'auth',1,NULL),(25,2,'user-management',1,NULL),(26,2,'security',1,NULL),(27,2,'backup',1,NULL),(28,2,'api',1,NULL),(29,2,'analytics',1,NULL),(30,2,'messaging',1,NULL),(31,2,'notifications',1,NULL),(32,2,'teacher-portal',1,NULL),(33,2,'parent-portal',1,NULL),(34,2,'student-portal',1,NULL),(35,2,'academic',1,NULL),(36,2,'attendance',1,NULL),(37,2,'library',1,NULL),(38,2,'ai-tutor',1,NULL),(39,2,'gamification',1,NULL),(40,2,'high-school',1,NULL),(41,2,'examination',1,NULL),(42,2,'certificate',1,NULL),(43,2,'lms',1,NULL),(44,2,'online-courses',1,NULL),(45,3,'core',1,NULL),(46,3,'auth',1,NULL),(47,3,'user-management',1,NULL),(48,3,'security',1,NULL),(49,3,'backup',1,NULL),(50,3,'api',1,NULL),(51,3,'analytics',1,NULL),(52,3,'messaging',1,NULL),(53,3,'notifications',1,NULL),(54,3,'teacher-portal',1,NULL),(55,3,'parent-portal',1,NULL),(56,3,'student-portal',1,NULL),(57,3,'academic',1,NULL),(58,3,'attendance',1,NULL),(59,3,'library',1,NULL),(60,3,'ai-tutor',1,NULL),(61,3,'gamification',1,NULL),(62,3,'high-school',1,NULL),(63,3,'examination',1,NULL),(64,3,'certificate',1,NULL),(65,3,'lms',1,NULL),(66,3,'online-courses',1,NULL);
/*!40000 ALTER TABLE `school_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schools` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int unsigned DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('school','university','college','training','other') COLLATE utf8mb4_unicode_ci DEFAULT 'school',
  `education_level` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT 'secondary',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` enum('active','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schools`
--

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES (1,2,'Addis Ababa International School','AAIS','school','secondary','','Addis Ababa','','info@aais.edu.et','','active','2026-08-21 22:36:25'),(2,6,'Bahir Dar University','BDU','university','secondary','','Bahir Dar','','info@bdu.edu.et','','active','2026-08-21 22:36:25'),(3,2,'Hawassa Preparatory School','HPS','school','secondary','','Hawassa','','info@hps.edu.et','','active','2026-08-21 22:36:25');
/*!40000 ALTER TABLE `schools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `semesters`
--

DROP TABLE IF EXISTS `semesters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `semesters` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `year_id` int unsigned NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `year_id` (`year_id`),
  CONSTRAINT `semesters_ibfk_1` FOREIGN KEY (`year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semesters`
--

LOCK TABLES `semesters` WRITE;
/*!40000 ALTER TABLE `semesters` DISABLE KEYS */;
INSERT INTO `semesters` VALUES (1,1,'Semester 1','2025-09-01','2026-01-31'),(2,2,'Semester 1','2025-09-01','2026-01-31'),(3,3,'Semester 1','2025-09-01','2026-01-31');
/*!40000 ALTER TABLE `semesters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `selector` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES (2,2,'b6518f44ef54356b63bfe9b9','119c5a0d90a7ee2d8437ab55ff1ac6aaad74077448efc4a97d8b67250c0109fc','::1','','2026-09-21 01:42:28'),(3,1,'2e865178bab1d73e95cedb6a','e0fdf2fcc0149dca755bbf39ec5ade20f284b34d53520d2927d70ad048512f58','::1','','2026-09-21 01:52:56'),(4,2,'918380184527d925bb53a07d','3d81f57bea93258dc851656a0511bdd2677be03730213ea83d9ffa565e64948e','::1','','2026-09-21 01:53:24'),(5,2,'179d25d9e399fdb5f8e14085','ae12b9c9d426745f1acbec85ee62b008b91c34999145611005b5c88f2aa46180','::1','','2026-09-21 01:53:37'),(6,8,'9b4e5877b4289e89cf7bedfd','4ab23dabd117dd7fbb08e139df04c3fe826d0fca8018528c4c6f2ce0422af776','::1','','2026-09-21 01:53:50'),(7,2,'8d273b21b9815f45c2657d54','7e57652eea0cc1a919612d54f383c7d958d6419a03e71bc7d23ec9040557df89','::1','','2026-09-21 02:00:08'),(10,2,'4eacc3586f0ff2c9c24ce243','13bc58898ab993ebadd21757ccffd30ff0f3739d98da92698c5bbf3d30af7716','::1','','2026-09-21 04:09:47');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('ai_api_key',''),('ai_api_url',''),('ai_enabled','1'),('ai_model',''),('ai_provider','local'),('announcement',''),('currency','ETB'),('default_language','en'),('default_theme','dark'),('google_login_enabled','0'),('maintenance_mode','0'),('max_upload_mb','50'),('registration_open','1'),('school_registration_open','1'),('session_timeout_min','120'),('site_name','Edunex'),('site_tagline','AI-Powered Ethiopian Learning Platform'),('support_email','support@edunex.local');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_groups`
--

DROP TABLE IF EXISTS `student_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grade` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `section` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `homeroom_teacher_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `homeroom_teacher_id` (`homeroom_teacher_id`),
  CONSTRAINT `student_groups_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_groups_ibfk_2` FOREIGN KEY (`homeroom_teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_groups`
--

LOCK TABLES `student_groups` WRITE;
/*!40000 ALTER TABLE `student_groups` DISABLE KEYS */;
INSERT INTO `student_groups` VALUES (1,1,'Grade 9-A','9','A',3),(2,1,'Grade 10-B','10','B',9),(3,2,'CS Year 1','1st','A',NULL),(4,3,'Grade 8-C','8','C',NULL);
/*!40000 ALTER TABLE `student_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_notes`
--

DROP TABLE IF EXISTS `student_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_notes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `lesson_id` int unsigned DEFAULT NULL,
  `course_id` int unsigned DEFAULT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `pinned` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_course` (`course_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `student_notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_notes_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_notes_ibfk_3` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_notes`
--

LOCK TABLES `student_notes` WRITE;
/*!40000 ALTER TABLE `student_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subjects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `department_id` int unsigned DEFAULT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` enum('active','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subjects_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,1,1,'Mathematics','MATH101','active'),(2,1,1,'Physics','PHY101','active'),(3,1,2,'English','ENG101','active'),(4,2,3,'Data Structures','CS201','active'),(5,2,4,'Circuit Theory','EE201','active'),(6,3,5,'General Science','GSC101','active');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_subjects`
--

DROP TABLE IF EXISTS `teacher_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_subjects` (
  `teacher_id` int unsigned NOT NULL,
  `subject_id` int unsigned NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`teacher_id`,`subject_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `teacher_subjects_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_subjects`
--

LOCK TABLES `teacher_subjects` WRITE;
/*!40000 ALTER TABLE `teacher_subjects` DISABLE KEYS */;
/*!40000 ALTER TABLE `teacher_subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transfer_codes`
--

DROP TABLE IF EXISTS `transfer_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transfer_codes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` int unsigned NOT NULL,
  `student_id` int unsigned DEFAULT NULL,
  `purpose` enum('referral','transfer') COLLATE utf8mb4_unicode_ci DEFAULT 'referral',
  `used` tinyint(1) DEFAULT '0',
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `transfer_codes_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transfer_codes`
--

LOCK TABLES `transfer_codes` WRITE;
/*!40000 ALTER TABLE `transfer_codes` DISABLE KEYS */;
INSERT INTO `transfer_codes` VALUES (1,'TRF-AAIS-0001',1,NULL,'referral',0,'2026-11-20 01:36:25','2026-08-21 22:36:25'),(2,'TRF-BDU-0001',2,NULL,'referral',0,'2026-11-20 01:36:25','2026-08-21 22:36:25'),(3,'TRF-HPS-0001',3,NULL,'referral',0,'2026-11-20 01:36:25','2026-08-21 22:36:25'),(4,'TRF-FA5C-7B0B',1,4,'transfer',0,'2026-11-20 01:40:25','2026-08-21 22:40:25');
/*!40000 ALTER TABLE `transfer_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transfer_requests`
--

DROP TABLE IF EXISTS `transfer_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transfer_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int unsigned NOT NULL,
  `source_student_id` int unsigned DEFAULT NULL,
  `from_school_id` int unsigned NOT NULL,
  `to_school_id` int unsigned NOT NULL,
  `referral_code` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` enum('pending','approved','rejected','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `approved_by` int unsigned DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `record_snapshot` json DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `from_school_id` (`from_school_id`),
  KEY `to_school_id` (`to_school_id`),
  KEY `idx_transfer_student` (`student_id`),
  CONSTRAINT `transfer_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transfer_requests_ibfk_2` FOREIGN KEY (`from_school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transfer_requests_ibfk_3` FOREIGN KEY (`to_school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transfer_requests`
--

LOCK TABLES `transfer_requests` WRITE;
/*!40000 ALTER TABLE `transfer_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `transfer_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_badges`
--

DROP TABLE IF EXISTS `user_badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_badges` (
  `user_id` int unsigned NOT NULL,
  `badge_id` int unsigned NOT NULL,
  `earned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`badge_id`),
  KEY `badge_id` (`badge_id`),
  CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_badges`
--

LOCK TABLES `user_badges` WRITE;
/*!40000 ALTER TABLE `user_badges` DISABLE KEYS */;
INSERT INTO `user_badges` VALUES (4,1,'2026-08-21 22:36:25'),(4,2,'2026-08-21 22:36:25');
/*!40000 ALTER TABLE `user_badges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_challenges`
--

DROP TABLE IF EXISTS `user_challenges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_challenges` (
  `user_id` int unsigned NOT NULL,
  `challenge_id` int unsigned NOT NULL,
  `progress` int DEFAULT '0',
  `completed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`user_id`,`challenge_id`),
  KEY `challenge_id` (`challenge_id`),
  CONSTRAINT `user_challenges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_challenges_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_challenges`
--

LOCK TABLES `user_challenges` WRITE;
/*!40000 ALTER TABLE `user_challenges` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_challenges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned DEFAULT NULL,
  `role` enum('admin','sysadmin','director','teacher','student','parent','guest') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
  `first_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `student_id` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `language` enum('en','am','om','ti','so') COLLATE utf8mb4_unicode_ci DEFAULT 'en',
  `theme` enum('dark','light') COLLATE utf8mb4_unicode_ci DEFAULT 'dark',
  `group_id` int unsigned DEFAULT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `department_id` int unsigned DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `birth_date` date DEFAULT NULL,
  `gender` enum('m','f','o') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified` tinyint(1) DEFAULT '0',
  `verified_by` int unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `enrollment_status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `twofa_secret` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `twofa_enabled` tinyint(1) DEFAULT '0',
  `hena_counter` int unsigned DEFAULT '0',
  `xp` int DEFAULT '0',
  `level` int DEFAULT '1',
  `streak` int DEFAULT '0',
  `streak_last` date DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `status` enum('active','pending','suspended','banned') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `session_version` int DEFAULT '0',
  `privacy` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `student_id` (`student_id`),
  KEY `group_id` (`group_id`),
  KEY `parent_id` (`parent_id`),
  KEY `idx_users_school_role` (`school_id`,`role`),
  KEY `idx_users_student_id` (`student_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `student_groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'sysadmin','Super','Admin','superadmin@edunex.local','',NULL,'$2y$12$9qbvc17hFQyjtZbj9MMWkebNK0EO11bajz0KidA.m86kfUKMXeXfi','','en','dark',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,0,1,0,NULL,'2026-08-23 10:10:55','active',1,NULL,'2026-08-21 22:36:25'),(2,1,'admin','Sara','Tesfaye','admin@edunex.local','+251911000001',NULL,'$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','en','dark',NULL,NULL,1,NULL,NULL,NULL,1,NULL,'2026-08-22 01:36:25','active','',0,0,5000,8,12,NULL,'2026-08-23 10:20:40','active',1,NULL,'2026-08-21 22:36:25'),(3,1,'teacher','David','Alemu','teacher@edunex.local','+251911000002',NULL,'$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','en','dark',NULL,NULL,1,NULL,NULL,NULL,1,NULL,'2026-08-22 01:36:25','active','',0,0,2500,6,5,NULL,'2026-08-22 01:36:25','active',1,NULL,'2026-08-21 22:36:25'),(4,1,'student','Liya','Girma','student@edunex.local','+251911000003','AAIS-2026-000001','$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','en','dark',1,5,1,NULL,NULL,NULL,1,3,'2026-08-22 01:36:25','active','',0,0,1200,4,3,NULL,'2026-08-23 10:06:31','active',1,NULL,'2026-08-21 22:36:25'),(5,1,'parent','Hana','Girma','parent@edunex.local','+251911000004',NULL,'$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','en','dark',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,'2026-08-22 01:36:25','active','',0,0,100,1,0,NULL,'2026-08-22 01:36:25','active',1,NULL,'2026-08-21 22:36:25'),(6,2,'admin','Kebede','Hailu','admin2@edunex.local','+251911000005',NULL,'$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','am','light',NULL,NULL,3,NULL,NULL,NULL,1,NULL,'2026-08-22 01:36:25','active','',0,0,300,2,1,NULL,'2026-08-22 01:36:25','active',1,NULL,'2026-08-21 22:36:25'),(7,3,'teacher','Meron','Tesfa','teacher3@edunex.local','+251911000006',NULL,'$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','en','light',NULL,NULL,5,NULL,NULL,NULL,1,NULL,'2026-08-22 01:36:25','active','',0,0,800,3,2,NULL,'2026-08-22 01:36:25','active',1,NULL,'2026-08-21 22:36:25'),(8,1,'director','Dir','One','director@edunex.local','+251911000007',NULL,'$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','avatars/20260823_082726_26f7a07876f4.jpeg','en','dark',NULL,NULL,1,'',NULL,NULL,1,NULL,'2026-08-22 01:36:25','active','',0,0,1000,3,1,NULL,'2026-08-23 08:47:16','active',1,NULL,'2026-08-21 22:36:25'),(9,1,'teacher','Henok','Arega','test@test.com','',NULL,'$2y$12$Ktg03dDgMHyLJmtxpCGuqu6E8ydSBJCaszIONuXN/iVMr37Bq8I9y','','en','dark',NULL,NULL,1,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,0,1,0,NULL,NULL,'active',0,NULL,'2026-08-23 05:48:54');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-23 10:23:47
