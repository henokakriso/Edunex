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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_years`
--

LOCK TABLES `academic_years` WRITE;
/*!40000 ALTER TABLE `academic_years` DISABLE KEYS */;
INSERT INTO `academic_years` VALUES (1,1,'2025/2026','2025-09-01','2026-07-31',0),(2,2,'2025/2026','2025-09-01','2026-07-31',1),(3,3,'2025/2026','2025-09-01','2026-07-31',0),(4,1,'2028/29',NULL,NULL,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,2,'api_login','Desktop app login: admin@edunex.local','127.0.0.1','curl/8.20.0','2026-08-01 02:00:08'),(2,6,'api_login','Desktop app login: admin2@edunex.local','127.0.0.1','curl/8.20.0','2026-08-01 02:00:08'),(3,2,'login','Signed in as admin','127.0.0.1','curl/8.20.0','2026-08-01 02:00:09'),(4,2,'login','Signed in','127.0.0.1','curl/8.20.0','2026-08-01 02:00:09'),(5,2,'user','Created user Test User (teacher)','127.0.0.1','curl/8.20.0','2026-08-01 02:00:10'),(6,8,'login','Signed in as director','127.0.0.1','curl/8.20.0','2026-08-01 02:00:11'),(7,8,'login','Signed in','127.0.0.1','curl/8.20.0','2026-08-01 02:00:11'),(8,3,'login','Signed in as teacher','127.0.0.1','curl/8.20.0','2026-08-01 02:00:11'),(9,3,'login','Signed in','127.0.0.1','curl/8.20.0','2026-08-01 02:00:11'),(10,4,'login','Signed in as student','127.0.0.1','curl/8.20.0','2026-08-01 02:00:12'),(11,4,'login','Signed in','127.0.0.1','curl/8.20.0','2026-08-01 02:00:12'),(12,5,'login','Signed in as parent','127.0.0.1','curl/8.20.0','2026-08-01 02:00:13'),(13,5,'login','Signed in','127.0.0.1','curl/8.20.0','2026-08-01 02:00:13'),(14,2,'api_login','Desktop app login: admin@edunex.local','127.0.0.1','curl/8.20.0','2026-08-01 02:00:15'),(15,4,'api_login','Desktop app login: student@edunex.local','127.0.0.1','curl/8.20.0','2026-08-01 02:00:16'),(16,2,'login','Signed in as admin','127.0.0.1','curl/8.20.0','2026-08-01 02:00:24'),(17,2,'login','Signed in','127.0.0.1','curl/8.20.0','2026-08-01 02:00:24'),(18,8,'login','Signed in as director','127.0.0.1','curl/8.20.0','2026-08-01 02:00:26'),(19,8,'login','Signed in','127.0.0.1','curl/8.20.0','2026-08-01 02:00:26'),(20,3,'login','Signed in as teacher','127.0.0.1','curl/8.20.0','2026-08-01 02:00:28'),(21,3,'login','Signed in','127.0.0.1','curl/8.20.0','2026-08-01 02:00:28'),(22,4,'login','Signed in as student','127.0.0.1','curl/8.20.0','2026-08-01 02:00:30'),(23,4,'login','Signed in','127.0.0.1','curl/8.20.0','2026-08-01 02:00:30'),(24,5,'login','Signed in as parent','127.0.0.1','curl/8.20.0','2026-08-01 02:00:31'),(25,5,'login','Signed in','127.0.0.1','curl/8.20.0','2026-08-01 02:00:31'),(26,4,'login','Signed in as student','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:05:15'),(27,4,'login','Signed in','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:05:15'),(28,4,'ai_chat','Tutor: hey','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:05:29'),(29,4,'ai_chat','Tutor: i want maths','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:05:41'),(30,4,'ai_chat','Tutor: explain 2+2','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:05:56'),(31,4,'xp','+10 XP — Completed lesson: Introduction to Algebra','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:06:11'),(32,4,'xp','+10 XP — Submitted assignment: Motion Lab Report','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:08:21'),(33,4,'ai_chat','Tutor: hey','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:08:50'),(34,8,'login','Signed in as director','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:10:16'),(35,8,'login','Signed in','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:10:16'),(36,8,'transfer','Transfer code TRF-7301-B7D3 issued for Liya Girma','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:10:41'),(37,3,'login','Signed in as teacher','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:12:25'),(38,3,'login','Signed in','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:12:25'),(39,3,'user','Verified student #10 (Henok Arega)','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:12:29'),(40,10,'login','Signed in as student','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:12:45'),(41,10,'login','Signed in','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:12:45'),(42,10,'ai_chat','Tutor: i want maths','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:12:57'),(43,10,'xp','+20 XP — Enrolled in Mathematics 101','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:13:49'),(44,10,'xp','+10 XP — Completed lesson: Triangles and Angles','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:13:57'),(45,5,'login','Signed in as parent','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:17:14'),(46,5,'login','Signed in','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:17:14'),(47,5,'login','Signed in as parent','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 11:22:17'),(48,5,'login','Signed in','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 11:22:17'),(49,5,'xp','+15 XP — Goal completed','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 11:25:02'),(50,10,'login','Signed in as student','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 11:38:03'),(51,10,'login','Signed in','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 11:38:03'),(52,10,'xp','+10 XP — Completed lesson: Introduction to Algebra','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 11:39:17'),(53,10,'xp','+10 XP — Completed lesson: Triangles and Angles','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 11:39:20'),(54,5,'login','Signed in as parent','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 11:41:31'),(55,5,'login','Signed in','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 11:41:31'),(56,10,'login','Signed in as student','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 22:00:18'),(57,10,'login','Signed in','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 22:00:18'),(58,1,'login','Signed in as admin','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 22:04:04'),(59,1,'login','Signed in','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 22:04:04'),(60,1,'user','Deleted user Test User','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 22:14:30'),(61,1,'user','Created user nat nat (director)','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 22:16:22'),(62,1,'school','Created school holeta','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 22:18:42'),(63,1,'announcement','Posted: Hello New Year','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 22:38:13'),(64,1,'report','Generated report: hell grades','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 22:47:58'),(65,1,'report','Generated report: hell grades','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 22:48:23');
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
  `box` int DEFAULT '0',
  `reviewed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deck_id` (`deck_id`),
  CONSTRAINT `ai_cards_ibfk_1` FOREIGN KEY (`deck_id`) REFERENCES `ai_decks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_cards`
--

LOCK TABLES `ai_cards` WRITE;
/*!40000 ALTER TABLE `ai_cards` DISABLE KEYS */;
INSERT INTO `ai_cards` VALUES (1,1,'hey','you',0,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_chats`
--

LOCK TABLES `ai_chats` WRITE;
/*!40000 ALTER TABLE `ai_chats` DISABLE KEYS */;
INSERT INTO `ai_chats` VALUES (1,4,NULL,'New chat',NULL,'2026-08-01 02:00:13'),(2,8,NULL,'New chat',NULL,'2026-08-01 02:00:27'),(3,3,NULL,'New chat',NULL,'2026-08-01 02:00:28'),(4,4,NULL,'New chat',NULL,'2026-08-01 02:00:30'),(5,4,NULL,'New chat',NULL,'2026-08-01 02:05:26'),(6,4,NULL,'explain 2+2',NULL,'2026-08-01 02:05:29'),(7,4,NULL,'New chat',NULL,'2026-08-01 02:08:44'),(8,4,NULL,'hey',NULL,'2026-08-01 02:08:50'),(9,10,NULL,'New chat',NULL,'2026-08-01 02:12:52'),(10,10,NULL,'i want maths',NULL,'2026-08-01 02:12:57'),(11,10,NULL,'New chat',NULL,'2026-08-01 11:41:14');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_decks`
--

LOCK TABLES `ai_decks` WRITE;
/*!40000 ALTER TABLE `ai_decks` DISABLE KEYS */;
INSERT INTO `ai_decks` VALUES (1,4,'h','2026-08-01 02:09:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_messages`
--

LOCK TABLES `ai_messages` WRITE;
/*!40000 ALTER TABLE `ai_messages` DISABLE KEYS */;
INSERT INTO `ai_messages` VALUES (1,6,'user','hey','2026-08-01 02:05:29'),(2,6,'ai','👋 Good morning, Liya Girma! Welcome back.\n\nYour learning streak is **3 day(s)** — keep it alive! 🔥\n\nI can help you:\n• Explain any topic (try \'Explain recursion\')\n• Summarize lessons (\'Summarize Chapter 1\')\n• Create quizzes (\'Quiz me on algebra\')\n• Make flashcards (\'Create flashcards\')\n• Translate to Amharic (\'Translate good morning to Amharic\')\n• Draw diagrams (\'Diagram binary tree\')\n• Check your exam readiness (\'Am I ready for my exam?\')\n\nWhat shall we learn today?','2026-08-01 02:05:29'),(3,6,'user','i want maths','2026-08-01 02:05:41'),(4,6,'ai','I\'d love to explain **i want maths**, but I need a bit more context. Here\'s what I can do:\n\n🤖 **I\'m your AI Tutor** — I remember our conversation and adapt to your pace.\n\n**Try asking me:**\n• *Explain recursion*\n• *Summarize Chapter 3*\n• *Create a quiz about algebra*\n• *Generate flashcards*\n• *Explain with a diagram*\n• *Translate thank you to Amharic* (አመሰግናለሁ!)\n• *Explain like I\'m five*\n• *Correct my grammar: i goed to school*\n• *Create a study plan*\n• *Am I ready for my exam?*\n• *Suggest my weak topics*\n\nI also learn from your courses — ask me to summarize any of your lessons!','2026-08-01 02:05:41'),(5,6,'user','explain 2+2','2026-08-01 02:05:56'),(6,6,'ai','I\'d love to explain **2+2**, but I need a bit more context. Here\'s what I can do:\n\n🤖 **I\'m your AI Tutor** — I remember our conversation and adapt to your pace.\n\n**Try asking me:**\n• *Explain recursion*\n• *Summarize Chapter 3*\n• *Create a quiz about algebra*\n• *Generate flashcards*\n• *Explain with a diagram*\n• *Translate thank you to Amharic* (አመሰግናለሁ!)\n• *Explain like I\'m five*\n• *Correct my grammar: i goed to school*\n• *Create a study plan*\n• *Am I ready for my exam?*\n• *Suggest my weak topics*\n\nI also learn from your courses — ask me to summarize any of your lessons!','2026-08-01 02:05:56'),(7,8,'user','hey','2026-08-01 02:08:50'),(8,8,'ai','👋 Good morning, Liya Girma! Welcome back.\n\nYour learning streak is **1 day(s)** — keep it alive! 🔥\n\nI can help you:\n• Explain any topic (try \'Explain recursion\')\n• Summarize lessons (\'Summarize Chapter 1\')\n• Create quizzes (\'Quiz me on algebra\')\n• Make flashcards (\'Create flashcards\')\n• Translate to Amharic (\'Translate good morning to Amharic\')\n• Draw diagrams (\'Diagram binary tree\')\n• Check your exam readiness (\'Am I ready for my exam?\')\n\nWhat shall we learn today?','2026-08-01 02:08:50'),(9,10,'user','i want maths','2026-08-01 02:12:57'),(10,10,'ai','I\'d love to explain **i want maths**, but I need a bit more context. Here\'s what I can do:\n\n🤖 **I\'m your AI Tutor** — I remember our conversation and adapt to your pace.\n\n**Try asking me:**\n• *Explain recursion*\n• *Summarize Chapter 3*\n• *Create a quiz about algebra*\n• *Generate flashcards*\n• *Explain with a diagram*\n• *Translate thank you to Amharic* (አመሰግናለሁ!)\n• *Explain like I\'m five*\n• *Correct my grammar: i goed to school*\n• *Create a study plan*\n• *Am I ready for my exam?*\n• *Suggest my weak topics*\n\nI also learn from your courses — ask me to summarize any of your lessons!','2026-08-01 02:12:57');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (2,1,1,3,'Midterm exam schedule published','The Algebra midterm will take place next week. Review chapters 1-2.',1,'course','2026-08-01 01:59:37'),(4,1,NULL,1,'Hello New Year','fjfjgfjgfgfjgfjgfjfghfjh',1,'all','2026-08-01 22:38:13');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment_submissions`
--

LOCK TABLES `assignment_submissions` WRITE;
/*!40000 ALTER TABLE `assignment_submissions` DISABLE KEYS */;
INSERT INTO `assignment_submissions` VALUES (1,1,4,'I solved problems 1-20. Please see the attached PDF.','','2026-07-28 07:00:00',0,88.00,'Excellent work! Careful with problem 14.',NULL,3,'2026-07-29 09:00:00','graded'),(2,2,4,'gjghj','','2026-08-01 02:08:21',0,NULL,NULL,NULL,NULL,NULL,'submitted');
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
INSERT INTO `assignments` VALUES (1,1,3,'Algebra Worksheet 1','Solve the 20 problems in the attached sheet. Show all your steps.','[{\"criterion\":\"Correctness\",\"max\":60,\"weight\":60},{\"criterion\":\"Steps shown\",\"max\":25,\"weight\":25},{\"criterion\":\"Presentation\",\"max\":15,\"weight\":15}]',100.00,'2026-08-06 04:59:37',1,10.00,'2026-08-01 01:59:37'),(2,2,3,'Motion Lab Report','Write a lab report about free-fall motion using the provided template.',NULL,50.00,'2026-08-04 04:59:37',1,5.00,'2026-08-01 01:59:37');
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
INSERT INTO `attendance` VALUES (1,1,1,4,'2026-07-23','present',3,'','2026-08-01 01:59:37'),(2,1,1,4,'2026-07-24','present',3,'','2026-08-01 01:59:37'),(3,1,1,4,'2026-07-25','late',3,'','2026-08-01 01:59:37'),(4,1,1,4,'2026-07-26','present',3,'','2026-08-01 01:59:37'),(5,1,1,4,'2026-07-27','absent',3,'','2026-08-01 01:59:37'),(6,1,1,4,'2026-07-28','present',3,'','2026-08-01 01:59:37'),(7,1,1,4,'2026-07-29','present',3,'','2026-08-01 01:59:37'),(8,1,1,4,'2026-07-30','excused',3,'','2026-08-01 01:59:37'),(9,1,1,4,'2026-07-31','present',3,'','2026-08-01 01:59:37'),(10,1,1,4,'2026-08-01','present',3,'','2026-08-01 01:59:37');
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
  `icon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 0xF09F8E96,
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
INSERT INTO `badges` VALUES (1,'First Steps','🌱','Complete your first lesson',50,'learning'),(2,'Bookworm','📚','Read 5 lessons',200,'learning'),(3,'Quiz Whiz','🧠','Score 80%+ on any quiz',300,'quiz'),(4,'Perfect Attendance','🎯','7 days of perfect attendance',350,'attendance'),(5,'On Fire','🔥','7-day learning streak',400,'streak'),(6,'Scholar','🎓','Complete a full course',600,'level'),(7,'Helping Hand','🤝','Answer 5 forum questions',250,'community'),(8,'Marathoner','🏃','Reach level 5',500,'level'),(9,'First Steps','🌱','Complete your first lesson',50,'learning'),(10,'Bookworm','📚','Read 5 lessons',200,'learning'),(11,'Quiz Whiz','🧠','Score 80%+ on any quiz',300,'quiz'),(12,'Perfect Attendance','🎯','7 days of perfect attendance',350,'attendance'),(13,'On Fire','🔥','7-day learning streak',400,'streak'),(14,'Scholar','🎓','Complete a full course',600,'level'),(15,'Helping Hand','🤝','Answer 5 forum questions',250,'community'),(16,'Marathoner','🏃','Reach level 5',500,'level');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookmarks`
--

LOCK TABLES `bookmarks` WRITE;
/*!40000 ALTER TABLE `bookmarks` DISABLE KEYS */;
INSERT INTO `bookmarks` VALUES (1,10,1,2,'','2026-08-01 11:38:50');
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
INSERT INTO `calendar_events` VALUES (1,1,4,'Algebra Midterm','exam','2026-08-08 04:59:37','2026-08-08 04:59:37',0,'','','2026-08-01 01:59:37'),(2,1,4,'Physics Lab Report due','assignment','2026-08-04 04:59:37',NULL,0,'','','2026-08-01 01:59:37'),(3,1,4,'Algebra Worksheet due','assignment','2026-08-06 04:59:37',NULL,0,'','','2026-08-01 01:59:37'),(4,1,NULL,'School Sports Day','event','2026-08-13 04:59:37','2026-08-13 04:59:37',1,'','','2026-08-01 01:59:37'),(5,1,4,'Study group: Data Structures','meeting','2026-08-03 04:59:37',NULL,0,'','','2026-08-01 01:59:37');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certificates`
--

LOCK TABLES `certificates` WRITE;
/*!40000 ALTER TABLE `certificates` DISABLE KEYS */;
INSERT INTO `certificates` VALUES (1,10,1,'CERT-8C34CCEF','875e4d165ac7ec260ed52d7a6c340d32f58d4b3d39688ce922badad610a812d7','2026-08-01 14:39:20','Passed');
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
INSERT INTO `challenges` VALUES (1,1,'Study 5 lessons this week','Complete 5 lessons in any course.',100,'2026-07-29','2026-08-05'),(2,1,'Perfect quiz score','Get 100% on any practice quiz.',150,'2026-08-01','2026-08-08');
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
INSERT INTO `conversation_members` VALUES (1,3,NULL),(1,4,NULL),(2,2,NULL),(2,3,NULL),(2,4,NULL),(3,4,NULL);
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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversations`
--

LOCK TABLES `conversations` WRITE;
/*!40000 ALTER TABLE `conversations` DISABLE KEYS */;
INSERT INTO `conversations` VALUES (1,1,0,'Liya & David','2026-08-01 01:59:37'),(2,1,1,'Math 101 Class Group','2026-08-01 01:59:37'),(3,1,0,'','2026-08-01 02:09:32');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_enrollments`
--

LOCK TABLES `course_enrollments` WRITE;
/*!40000 ALTER TABLE `course_enrollments` DISABLE KEYS */;
INSERT INTO `course_enrollments` VALUES (1,1,4,33.30,0,NULL,'2026-08-01 01:59:37'),(2,2,4,0.00,0,NULL,'2026-08-01 01:59:37'),(3,3,4,25.00,0,NULL,'2026-08-01 01:59:37'),(4,1,10,100.00,1,'2026-08-01 14:39:20','2026-08-01 02:13:49');
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
INSERT INTO `courses` VALUES (1,1,3,1,'Mathematics 101','MATH101','Foundations of algebra, geometry and calculus for Grade 9 students.','','Grade 9','published',0.00,'2026-08-01 01:59:37'),(2,1,3,2,'Physics Foundations','PHY101','Mechanics, motion and energy explained with real Ethiopian examples.','','Grade 9','published',0.00,'2026-08-01 01:59:37'),(3,3,7,4,'Data Structures','CS201','Arrays, linked lists, stacks, queues, trees and graphs with C.','','1st Year','published',0.00,'2026-08-01 01:59:37');
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
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,1,'Science','Dr. Bekele'),(2,1,'Languages','Mrs. Tigist'),(3,2,'Computer Science','Prof. Alem'),(4,2,'Engineering','Dr. Marta'),(5,3,'General','Mr. Dawit'),(6,1,'AI','henok');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_attempts`
--

LOCK TABLES `exam_attempts` WRITE;
/*!40000 ALTER TABLE `exam_attempts` DISABLE KEYS */;
INSERT INTO `exam_attempts` VALUES (1,1,4,'2026-07-20 09:00:00','2026-07-20 09:20:00',NULL,NULL,7.00,12.00,'graded'),(2,2,4,'2026-08-01 05:06:32',NULL,'{\"q_7\":\"0\",\"q_8\":\"True\"}',NULL,NULL,0.00,'in_progress'),(3,1,10,'2026-08-01 14:39:39',NULL,'{\"q_1\":\"3\",\"q_2\":\"2\",\"q_3\":\"True\",\"q_4\":\"\",\"q_5\":\"\"}',NULL,NULL,0.00,'in_progress');
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
INSERT INTO `exams` VALUES (1,1,3,'Algebra Midterm','Covers chapters 1-2: algebra basics and linear equations.','midterm',30,'2026-08-01 04:59:37','2026-08-08 04:59:37',50.00,1,0,1,'published','2026-08-01 01:59:37'),(2,3,7,'Data Structures Quiz 1','Quick quiz on arrays and pointers.','quiz',15,'2026-08-01 04:59:37','2026-08-04 04:59:37',50.00,1,0,1,'published','2026-08-01 01:59:37'),(3,1,3,'Practice: True/False','Practice session for exam readiness.','practice',10,'2026-08-01 04:59:37','2026-08-03 04:59:37',50.00,1,0,1,'published','2026-08-01 01:59:37');
/*!40000 ALTER TABLE `exams` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
INSERT INTO `files` VALUES (1,1,2,'My Documents','My Documents','','folder',0,1,NULL,1,NULL,'2026-08-01 01:59:37'),(2,1,5,'','','','',0,1,NULL,1,NULL,'2026-08-01 11:33:13'),(3,1,5,'hello','','','',0,1,NULL,1,NULL,'2026-08-01 11:33:21');
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
INSERT INTO `forum_posts` VALUES (1,1,3,'Try the AC method: multiply a and c, find factors that sum to b.',1,'2026-08-01 01:59:37'),(2,1,4,'That helped a lot, thank you!',0,'2026-08-01 01:59:37');
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
INSERT INTO `forum_topics` VALUES (1,1,4,'How do I solve quadratic equations?','I keep getting confused with factoring. Any tips?',0,0,'2026-08-01 01:59:37'),(2,1,3,'Office hours this week','I will be available Wednesday 14:00-16:00.',0,0,'2026-08-01 01:59:37');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `goals`
--

LOCK TABLES `goals` WRITE;
/*!40000 ALTER TABLE `goals` DISABLE KEYS */;
INSERT INTO `goals` VALUES (1,4,'Finish Mathematics 101',100,50,'lessons','2026-08-31',0),(2,5,'hello',5,5,'lessons',NULL,1);
/*!40000 ALTER TABLE `goals` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ledger`
--

LOCK TABLES `ledger` WRITE;
/*!40000 ALTER TABLE `ledger` DISABLE KEYS */;
INSERT INTO `ledger` VALUES (1,1,0,'ledger.genesis','school',1,'{\"genesis\":1,\"school\":\"Addis Ababa International School\",\"created\":\"2026-08-01T14:39:20+03:00\"}','0000000000000000000000000000000000000000000000000000000000000000','316a4f815d7a8cc4b1a3d240424329c4f8c2879da8b43dd6411b21a11efb2839','2026-08-01 11:39:20'),(2,1,10,'certificate.issued','certificate',1,'{\"cert_code\":\"CERT-8C34CCEF\",\"course_id\":1,\"course\":\"Mathematics 101\"}','316a4f815d7a8cc4b1a3d240424329c4f8c2879da8b43dd6411b21a11efb2839','7b06bde8e4602b01668bcf9992d5f5a0b13f6fd0d415c22463c690ec49c97d80','2026-08-01 11:39:20');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_progress`
--

LOCK TABLES `lesson_progress` WRITE;
/*!40000 ALTER TABLE `lesson_progress` DISABLE KEYS */;
INSERT INTO `lesson_progress` VALUES (1,4,1,1,1,0,'2026-08-01 05:06:11'),(2,10,3,1,1,0,'2026-08-01 14:39:20'),(3,10,1,1,1,0,'2026-08-01 14:39:17');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `library_items`
--

LOCK TABLES `library_items` WRITE;
/*!40000 ALTER TABLE `library_items` DISABLE KEYS */;
INSERT INTO `library_items` VALUES (1,1,'Algebra Workbook 2026','book','MoE Ethiopia','Mathematics','Official workbook for Grade 9 algebra.','','',120,'published','2026-08-01 01:59:37'),(2,1,'Physics Notes Chapter 1','notes','Mr. David Alemu','Physics','Handwritten-style notes on motion.','','',45,'published','2026-08-01 01:59:37'),(3,1,'2019 National Exam Math','past_exam','MoE','Exams','Past national exam paper for practice.','','',230,'published','2026-08-01 01:59:37'),(4,1,'Introduction to C Programming','book','K&R','Programming','Classic C programming reference.','','',89,'published','2026-08-01 01:59:37'),(5,1,'Data Structures Slides','slides','Prof. Kebede','Computer Science','Lecture slides on trees and graphs.','','',60,'published','2026-08-01 01:59:37'),(6,1,'Amharic-English Dictionary','book','Academy','Languages','Bilingual dictionary for students.','','',150,'published','2026-08-01 01:59:37'),(7,1,'Ethiopian History Timeline','notes','Mrs. Tigist','History','Quick timeline of key events.','','',34,'published','2026-08-01 01:59:37'),(8,1,'Science Fair Video','video','BDU Media','Science','Documentary on Ethiopian scientists.','','',76,'published','2026-08-01 01:59:37'),(9,1,'C programming','book','henok','STEM','ghgjh','','uploads/library/20260802_013953_eb52d0ce75f5.pdf',0,'published','2026-08-01 22:39:53');
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
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_history`
--

LOCK TABLES `login_history` WRITE;
/*!40000 ALTER TABLE `login_history` DISABLE KEYS */;
INSERT INTO `login_history` VALUES (1,2,'success','127.0.0.1','desktop-api','2026-08-01 02:00:08'),(2,5,'failed','127.0.0.1','desktop-api','2026-08-01 02:00:08'),(3,6,'success','127.0.0.1','desktop-api','2026-08-01 02:00:08'),(4,2,'success','127.0.0.1','curl/8.20.0','2026-08-01 02:00:09'),(5,8,'success','127.0.0.1','curl/8.20.0','2026-08-01 02:00:11'),(6,3,'success','127.0.0.1','curl/8.20.0','2026-08-01 02:00:11'),(7,4,'success','127.0.0.1','curl/8.20.0','2026-08-01 02:00:12'),(8,5,'success','127.0.0.1','curl/8.20.0','2026-08-01 02:00:13'),(9,2,'success','127.0.0.1','desktop-api','2026-08-01 02:00:15'),(10,5,'failed','127.0.0.1','desktop-api','2026-08-01 02:00:15'),(11,4,'success','127.0.0.1','desktop-api','2026-08-01 02:00:16'),(12,2,'success','127.0.0.1','curl/8.20.0','2026-08-01 02:00:24'),(13,8,'success','127.0.0.1','curl/8.20.0','2026-08-01 02:00:26'),(14,3,'success','127.0.0.1','curl/8.20.0','2026-08-01 02:00:28'),(15,4,'success','127.0.0.1','curl/8.20.0','2026-08-01 02:00:30'),(16,5,'success','127.0.0.1','curl/8.20.0','2026-08-01 02:00:31'),(17,2,'failed','127.0.0.1','','2026-08-01 02:04:25'),(18,4,'success','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:05:15'),(19,8,'failed','127.0.0.1','','2026-08-01 02:09:52'),(20,8,'success','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:10:16'),(21,3,'success','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:12:25'),(22,10,'success','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:12:45'),(23,5,'success','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 02:17:14'),(24,5,'success','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 11:22:17'),(25,10,'success','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 11:38:03'),(26,5,'success','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 11:41:31'),(27,10,'success','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 22:00:18'),(28,2,'failed','127.0.0.1','','2026-08-01 22:01:24'),(29,2,'failed','127.0.0.1','','2026-08-01 22:01:48'),(30,2,'failed','127.0.0.1','','2026-08-01 22:03:26'),(31,2,'failed','127.0.0.1','','2026-08-01 22:03:32'),(32,2,'failed','127.0.0.1','','2026-08-01 22:03:36'),(33,1,'success','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-01 22:04:04');
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
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `conversation_id` (`conversation_id`),
  KEY `sender_id` (`sender_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,1,3,'Great question in the forum today!','','2026-08-01 01:59:37'),(2,1,4,'Thank you teacher!','','2026-08-01 01:59:37'),(3,2,3,'Reminder: midterm next week.','','2026-08-01 01:59:37'),(4,2,2,'Don\'t forget to register for the science fair.','','2026-08-01 01:59:37');
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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,4,'assignment','Algebra Worksheet 1 graded','You received 88/100.','assignments/view?sub=1',NULL,'2026-08-01 01:59:37'),(2,4,'exam','Algebra Midterm is coming up','The midterm starts in 7 days.','exams/view?e=1',NULL,'2026-08-01 01:59:37'),(3,4,'announcement','Welcome to Edunex','Explore your courses and AI tutor!','dashboard',NULL,'2026-08-01 01:59:37'),(4,4,'achievement','Achievement unlocked: Quiz Whiz','Score 80%+ on any quiz','gamification/badges',NULL,'2026-08-01 02:06:11'),(5,4,'achievement','Achievement unlocked: Perfect Attendance','7 days of perfect attendance','gamification/badges',NULL,'2026-08-01 02:06:11'),(6,4,'achievement','Achievement unlocked: On Fire','7-day learning streak','gamification/badges',NULL,'2026-08-01 02:06:11'),(7,4,'achievement','Achievement unlocked: Scholar','Complete a full course','gamification/badges',NULL,'2026-08-01 02:06:11'),(8,4,'achievement','Achievement unlocked: Helping Hand','Answer 5 forum questions','gamification/badges',NULL,'2026-08-01 02:06:11'),(9,4,'achievement','Achievement unlocked: Marathoner','Reach level 5','gamification/badges',NULL,'2026-08-01 02:06:11'),(10,4,'achievement','Achievement unlocked: First Steps','Complete your first lesson','gamification/badges',NULL,'2026-08-01 02:06:11'),(11,4,'achievement','Achievement unlocked: Bookworm','Read 5 lessons','gamification/badges',NULL,'2026-08-01 02:06:11'),(12,4,'achievement','Achievement unlocked: Quiz Whiz','Score 80%+ on any quiz','gamification/badges',NULL,'2026-08-01 02:06:11'),(13,4,'achievement','Achievement unlocked: Perfect Attendance','7 days of perfect attendance','gamification/badges',NULL,'2026-08-01 02:06:11'),(14,4,'achievement','Achievement unlocked: On Fire','7-day learning streak','gamification/badges',NULL,'2026-08-01 02:06:11'),(15,4,'achievement','Achievement unlocked: Scholar','Complete a full course','gamification/badges',NULL,'2026-08-01 02:06:11'),(16,4,'achievement','Achievement unlocked: Helping Hand','Answer 5 forum questions','gamification/badges',NULL,'2026-08-01 02:06:11'),(17,4,'achievement','Achievement unlocked: Marathoner','Reach level 5','gamification/badges',NULL,'2026-08-01 02:06:11'),(18,3,'assignment','New submission: Motion Lab Report','Liya submitted an assignment.','teacher/assignment&id=2',NULL,'2026-08-01 02:08:21'),(19,10,'achievement','Account verified','Your homeroom teacher approved your account. Welcome to Edunex!','student/dashboard','2026-08-01 14:38:11','2026-08-01 02:12:29'),(20,5,'achievement','Achievement unlocked: First Steps','Complete your first lesson','gamification/badges','2026-08-01 14:30:46','2026-08-01 11:25:02'),(21,5,'achievement','Achievement unlocked: First Steps','Complete your first lesson','gamification/badges','2026-08-01 14:30:46','2026-08-01 11:25:02'),(22,5,'achievement','Level up! You reached level 2','Goal completed','gamification','2026-08-01 14:30:46','2026-08-01 11:25:02'),(23,10,'achievement','Achievement unlocked: First Steps','Complete your first lesson','gamification/badges',NULL,'2026-08-01 11:39:20'),(24,10,'achievement','Achievement unlocked: First Steps','Complete your first lesson','gamification/badges',NULL,'2026-08-01 11:39:20'),(25,10,'achievement','Course completed! 🎓','You earned a certificate for Mathematics 101.','certificates',NULL,'2026-08-01 11:39:20'),(26,4,'announcement','Transfer approved!','Welcome to your new school.','dashboard',NULL,'2026-08-01 22:36:47'),(27,1,'announcement','Hello New Year','fjfjgfjgfgfjgfjgfjfghfjh','communication/announcements',NULL,'2026-08-01 22:38:13'),(28,2,'announcement','Hello New Year','fjfjgfjgfgfjgfjgfjfghfjh','communication/announcements',NULL,'2026-08-01 22:38:13'),(29,8,'announcement','Hello New Year','fjfjgfjgfgfjgfjgfjfghfjh','communication/announcements',NULL,'2026-08-01 22:38:13'),(30,11,'announcement','Hello New Year','fjfjgfjgfgfjgfjgfjfghfjh','communication/announcements',NULL,'2026-08-01 22:38:13'),(31,3,'announcement','Hello New Year','fjfjgfjgfgfjgfjgfjfghfjh','communication/announcements',NULL,'2026-08-01 22:38:13'),(32,10,'announcement','Hello New Year','fjfjgfjgfgfjgfjgfjfghfjh','communication/announcements',NULL,'2026-08-01 22:38:13'),(33,5,'announcement','Hello New Year','fjfjgfjgfgfjgfjgfjfghfjh','communication/announcements',NULL,'2026-08-01 22:38:13'),(34,6,'announcement','Hello New Year','fjfjgfjgfgfjgfjgfjfghfjh','communication/announcements',NULL,'2026-08-01 22:38:13'),(35,4,'announcement','Hello New Year','fjfjgfjgfgfjgfjgfjfghfjh','communication/announcements',NULL,'2026-08-01 22:38:13'),(36,7,'announcement','Hello New Year','fjfjgfjgfgfjgfjgfjfghfjh','communication/announcements',NULL,'2026-08-01 22:38:13');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` VALUES (1,1,1,'student','hell grades','csv','reports/student_20260802_014758.csv','{\"school\":0}','2026-08-01 22:47:58'),(2,1,1,'teacher','hell grades','csv','reports/teacher_20260802_014823.csv','{\"school\":0}','2026-08-01 22:48:23');
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
INSERT INTO `role_permissions` VALUES ('director','analytics.view'),('director','attendance.view'),('director','courses.view'),('director','exams.view'),('director','library.view'),('director','reports.generate'),('director','schools.view'),('director','students.view'),('director','teachers.manage'),('director','transfers.manage'),('director','users.import'),('teacher','announcements.manage'),('teacher','assignments.manage'),('teacher','attendance.manage'),('teacher','courses.manage'),('teacher','exams.manage'),('teacher','forum.manage'),('teacher','grades.manage'),('teacher','library.manage'),('teacher','parents.manage'),('teacher','reports.generate'),('teacher','students.verify'),('teacher','users.import'),('student','assignments.submit'),('student','certificates.view'),('student','courses.view'),('student','exams.take'),('student','forum.post'),('student','library.view'),('student','messages.send'),('parent','children.view'),('parent','reports.view'),('guest','courses.view');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schools` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('school','university','college','training','other') COLLATE utf8mb4_unicode_ci DEFAULT 'school',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` enum('active','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schools`
--

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES (1,'Addis Ababa International School','AAIS','school','','','000-000-0000','','','active','2026-08-01 01:59:37'),(2,'Bahir Dar University','BDU','university','','Bahir Dar','','info@bdu.edu.et','','active','2026-08-01 01:59:37'),(3,'Hawassa Preparatory School','HPS','school','','Hawassa','','info@hps.edu.et','','active','2026-08-01 01:59:37'),(4,'holeta','HSS','school','Addis Abeba','Addis Ababa','000-000-0000','test@test.com','','active','2026-08-01 22:18:42');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semesters`
--

LOCK TABLES `semesters` WRITE;
/*!40000 ALTER TABLE `semesters` DISABLE KEYS */;
INSERT INTO `semesters` VALUES (1,1,'Semester 1','2025-09-01','2026-01-31'),(2,2,'Semester 1','2025-09-01','2026-01-31'),(3,3,'Semester 1','2025-09-01','2026-01-31'),(4,1,'',NULL,NULL);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_groups`
--

LOCK TABLES `student_groups` WRITE;
/*!40000 ALTER TABLE `student_groups` DISABLE KEYS */;
INSERT INTO `student_groups` VALUES (1,1,'Grade 9-A','9','A',3),(2,1,'Grade 10-B','10','B',3),(3,2,'CS Year 1','1st','A',NULL),(4,3,'Grade 8-C','8','C',NULL),(5,1,'8','8','r',NULL);
/*!40000 ALTER TABLE `student_groups` ENABLE KEYS */;
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
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subjects_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,1,1,'Mathematics','MATH101'),(2,1,1,'Physics','PHY101'),(3,1,2,'English','ENG101'),(4,2,3,'Data Structures','CS201'),(5,2,4,'Circuit Theory','EE201'),(6,3,5,'General Science','GSC101'),(7,2,NULL,'Concrete','HSS');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transfer_codes`
--

LOCK TABLES `transfer_codes` WRITE;
/*!40000 ALTER TABLE `transfer_codes` DISABLE KEYS */;
INSERT INTO `transfer_codes` VALUES (1,'TRF-AAIS-0001',1,NULL,'referral',0,'2026-10-30 04:59:37','2026-08-01 01:59:37'),(2,'TRF-BDU-0001',2,NULL,'referral',0,'2026-10-30 04:59:37','2026-08-01 01:59:37'),(3,'TRF-HPS-0001',3,NULL,'referral',0,'2026-10-30 04:59:37','2026-08-01 01:59:37'),(4,'TRF-7301-B7D3',1,4,'transfer',0,'2026-10-30 05:10:41','2026-08-01 02:10:41'),(5,'TRF-4543-7FAB',1,NULL,'transfer',0,'2026-10-31 00:00:00','2026-08-01 22:36:34');
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
  `status` enum('pending','approved','rejected','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transfer_requests`
--

LOCK TABLES `transfer_requests` WRITE;
/*!40000 ALTER TABLE `transfer_requests` DISABLE KEYS */;
INSERT INTO `transfer_requests` VALUES (1,4,NULL,1,2,'','completed','',1,'2026-08-02 01:36:47',NULL,NULL,'2026-08-01 02:07:30');
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
INSERT INTO `user_badges` VALUES (4,1,'2026-08-01 01:59:37'),(4,2,'2026-08-01 01:59:37'),(4,3,'2026-08-01 02:06:11'),(4,4,'2026-08-01 02:06:11'),(4,5,'2026-08-01 02:06:11'),(4,6,'2026-08-01 02:06:11'),(4,7,'2026-08-01 02:06:11'),(4,8,'2026-08-01 02:06:11'),(4,9,'2026-08-01 02:06:11'),(4,10,'2026-08-01 02:06:11'),(4,11,'2026-08-01 02:06:11'),(4,12,'2026-08-01 02:06:11'),(4,13,'2026-08-01 02:06:11'),(4,14,'2026-08-01 02:06:11'),(4,15,'2026-08-01 02:06:11'),(4,16,'2026-08-01 02:06:11'),(5,1,'2026-08-01 11:25:02'),(5,9,'2026-08-01 11:25:02'),(10,1,'2026-08-01 11:39:20'),(10,9,'2026-08-01 11:39:20');
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
INSERT INTO `user_challenges` VALUES (2,1,0,0),(2,2,0,0),(3,1,0,0),(3,2,0,0),(4,1,1,0),(4,2,2,0),(5,1,0,0),(5,2,0,0),(8,1,0,0),(8,2,0,0),(10,1,1,0),(10,2,0,0);
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
  `role` enum('admin','director','teacher','student','parent','guest') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
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
  `twofa_secret` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `twofa_enabled` tinyint(1) DEFAULT '0',
  `xp` int DEFAULT '0',
  `level` int DEFAULT '1',
  `streak` int DEFAULT '0',
  `streak_last` date DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `status` enum('active','pending','suspended','banned') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'admin','Super','Admin','superadmin@edunex.local','',NULL,'$2y$12$FfFAMGO6yjT6junPJhM.2.0HJ0cb1ilASKXtoSpAGhkYSbkWwuUzS','','en','dark',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,1,0,NULL,'2026-08-02 01:04:04','active',NULL,'2026-08-01 01:59:37'),(2,1,'admin','Sara','Tesfaye','admin@edunex.local','+251911000001',NULL,'$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','en','dark',NULL,NULL,1,NULL,NULL,NULL,1,NULL,'2026-08-01 04:59:37','active','',0,5000,8,12,NULL,'2026-08-01 05:00:24','active',NULL,'2026-08-01 01:59:37'),(3,1,'teacher','David','Alemu','teacher@edunex.local','+251911000002',NULL,'$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','en','dark',NULL,NULL,1,NULL,NULL,NULL,1,NULL,'2026-08-01 04:59:37','active','',0,2500,6,5,NULL,'2026-08-01 05:12:25','active',NULL,'2026-08-01 01:59:37'),(4,2,'student','Liya','Girma','student@edunex.local','+251911000003','AAIS-2026-000001','$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','om','dark',1,5,1,'',NULL,NULL,1,3,'2026-08-01 04:59:37','active','',0,1220,4,1,'2026-08-01','2026-08-01 05:05:15','active',NULL,'2026-08-01 01:59:37'),(5,1,'parent','Hana','Girma','parent@edunex.local','+251911000004',NULL,'$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','en','dark',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,'2026-08-01 04:59:37','active','',0,115,2,1,'2026-08-01','2026-08-01 14:41:31','active',NULL,'2026-08-01 01:59:37'),(6,2,'admin','Kebede','Hailu','admin2@edunex.local','+251911000005',NULL,'$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','am','light',NULL,NULL,3,NULL,NULL,NULL,1,NULL,'2026-08-01 04:59:37','active','',0,300,2,1,NULL,'2026-08-01 05:00:08','active',NULL,'2026-08-01 01:59:37'),(7,3,'teacher','Meron','Tesfa','teacher3@edunex.local','+251911000006',NULL,'$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','en','light',NULL,NULL,5,NULL,NULL,NULL,1,NULL,'2026-08-01 04:59:37','active','',0,800,3,2,NULL,'2026-08-01 04:59:37','active',NULL,'2026-08-01 01:59:37'),(8,1,'director','Dir','One','director@edunex.local','+251911000007',NULL,'$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','en','dark',NULL,NULL,1,NULL,NULL,NULL,1,NULL,'2026-08-01 04:59:37','active','',0,1000,3,1,NULL,'2026-08-01 05:10:16','active',NULL,'2026-08-01 01:59:37'),(10,1,'student','Henok','Arega','henockarega111@gmail.com','0987546132','AAI-2026-000002','$2y$12$y5bf0r1kHGFZhT95y1pHXuqrda4H6KjTpjP/DBguMUEFqCFqFTxuS','','en','dark',2,NULL,NULL,NULL,NULL,NULL,1,3,'2026-08-01 05:12:29','active','',0,50,1,1,'2026-08-01','2026-08-02 01:00:18','active',NULL,'2026-08-01 02:02:44'),(11,1,'director','nat','nat','nat@nat.com','+251912563434',NULL,'$2y$12$b8tap2AlbsEDK8xJ9J.7DuG5cNLLhN5FXTZXl3GndvUVXpKL0TErq','','en','dark',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,'active','',0,0,1,0,NULL,NULL,'active',NULL,'2026-08-01 22:16:22');
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

-- Dump completed on 2026-08-02  1:59:26
