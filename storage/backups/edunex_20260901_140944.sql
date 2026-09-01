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
-- Table structure for table `academic_events`
--

DROP TABLE IF EXISTS `academic_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `semester_id` int unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_date` date NOT NULL,
  `event_type` enum('holiday','ceremony','deadline','exam','registration') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'deadline',
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `semester_id` (`semester_id`),
  CONSTRAINT `academic_events_ibfk_1` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_events`
--

LOCK TABLES `academic_events` WRITE;
/*!40000 ALTER TABLE `academic_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `academic_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `academic_periods`
--

DROP TABLE IF EXISTS `academic_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_periods` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `academic_year_id` int unsigned NOT NULL,
  `semester_id` int unsigned DEFAULT NULL,
  `school_id` int unsigned DEFAULT NULL,
  `period_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'registration|teaching|examination|result|vacation',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_year` (`academic_year_id`),
  KEY `idx_semester` (`semester_id`),
  KEY `idx_school` (`school_id`),
  KEY `idx_type` (`period_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_periods`
--

LOCK TABLES `academic_periods` WRITE;
/*!40000 ALTER TABLE `academic_periods` DISABLE KEYS */;
/*!40000 ALTER TABLE `academic_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `academic_records`
--

DROP TABLE IF EXISTS `academic_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_records` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int unsigned NOT NULL,
  `course_offering_id` int unsigned NOT NULL,
  `semester_id` int unsigned NOT NULL,
  `credit_hours` int unsigned NOT NULL DEFAULT '3',
  `grade` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grade_points` decimal(3,2) NOT NULL,
  `quality_points` decimal(7,2) GENERATED ALWAYS AS ((`credit_hours` * `grade_points`)) STORED,
  `recorded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_acad_rec` (`student_id`,`course_offering_id`),
  KEY `course_offering_id` (`course_offering_id`),
  KEY `semester_id` (`semester_id`),
  CONSTRAINT `academic_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `academic_records_ibfk_2` FOREIGN KEY (`course_offering_id`) REFERENCES `course_offerings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `academic_records_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_records`
--

LOCK TABLES `academic_records` WRITE;
/*!40000 ALTER TABLE `academic_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `academic_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `academic_years`
--

DROP TABLE IF EXISTS `academic_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_years` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned DEFAULT NULL,
  `name` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT '0',
  `is_demo` tinyint(1) DEFAULT '0',
  `ethiopian_year` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g. 2019 E.C.',
  `ethiopian_start` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ethiopian calendar start date',
  `ethiopian_end` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ethiopian calendar end date',
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft|active|closed|archived',
  `education_level` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'school' COMMENT 'school|university',
  `is_shared` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=applies to all schools of this level',
  `applies_to` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of school IDs if not shared',
  `num_semesters` tinyint unsigned NOT NULL DEFAULT '2',
  `primary_calendar` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ethiopian' COMMENT 'ethiopian|gregorian|both',
  `weekend_days` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fri,sat' COMMENT 'comma-separated day abbreviations',
  `working_days_per_week` tinyint unsigned NOT NULL DEFAULT '5',
  `school_days_target` smallint unsigned DEFAULT NULL COMMENT 'target teaching days per year',
  `registration_start` date DEFAULT NULL,
  `registration_end` date DEFAULT NULL,
  `teaching_start` date DEFAULT NULL,
  `teaching_end` date DEFAULT NULL,
  `exam_start` date DEFAULT NULL,
  `exam_end` date DEFAULT NULL,
  `result_start` date DEFAULT NULL,
  `result_end` date DEFAULT NULL,
  `vacation_start` date DEFAULT NULL,
  `vacation_end` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `academic_years_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_years`
--

LOCK TABLES `academic_years` WRITE;
/*!40000 ALTER TABLE `academic_years` DISABLE KEYS */;
INSERT INTO `academic_years` VALUES (2,2,'2025/2026','2025-09-01','2026-07-31',0,0,NULL,NULL,NULL,NULL,'draft','school',0,NULL,2,'ethiopian','fri,sat',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,2,'Henok Arega','2026-06-06','2026-08-01',1,0,NULL,NULL,NULL,NULL,'active','school',0,NULL,2,'ethiopian','fri,sat',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(7,NULL,'2026/27','2026-09-11','2027-09-10',0,0,'2019 E.C.','','','','active','school',1,NULL,2,'ethiopian','fri,sat',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(10,1,'2026/27','2026-09-11','2027-09-10',0,0,'2019 E.C.','','',NULL,'active','school',0,NULL,2,'ethiopian','fri,sat',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(11,3,'2026/27','2026-09-11','2027-09-10',0,0,'2019 E.C.','','',NULL,'active','school',0,NULL,2,'ethiopian','fri,sat',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=720 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:37:03'),(2,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:37:03'),(3,8,'transfer','Transfer code TRF-FA5C-7B0B issued for Liya Girma','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:40:25'),(4,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:42:28'),(5,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:42:28'),(6,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-21 22:52:56'),(7,1,'login','Signed in','::1','curl/8.20.0','2026-08-21 22:52:56'),(8,2,'login','Signed in as admin','::1','curl/8.20.0','2026-08-21 22:53:24'),(9,2,'login','Signed in','::1','curl/8.20.0','2026-08-21 22:53:24'),(10,2,'login','Signed in as admin','::1','curl/8.20.0','2026-08-21 22:53:37'),(11,2,'login','Signed in','::1','curl/8.20.0','2026-08-21 22:53:37'),(12,8,'login','Signed in as director','::1','curl/8.20.0','2026-08-21 22:53:50'),(13,8,'login','Signed in','::1','curl/8.20.0','2026-08-21 22:53:50'),(14,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:00:08'),(15,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:00:08'),(16,2,'school.suspended','Regional admin admin@edunex.local set school #1 to suspended','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:00:27'),(17,2,'school.active','Regional admin admin@edunex.local set school #1 to active','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:00:29'),(18,2,'announcement.create','Regional admin announced to school #1','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:02:00'),(19,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:02:29'),(20,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:02:29'),(21,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:06'),(22,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:06'),(23,2,'announcement.create','Regional admin announced to school #3','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:19'),(24,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:26'),(25,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:26'),(26,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:04:06'),(27,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:04:06'),(28,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:04:29'),(29,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:04:29'),(30,8,'ai_chat','Tutor: hello','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:08:57'),(31,8,'ai_chat','Tutor: Solve 2x + 6 = 14','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:21:38'),(32,8,'ai_chat','Tutor: hey','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:44:23'),(33,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:10:33'),(34,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:10:33'),(35,8,'ai_chat','Tutor: explain 2+2','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:10:49'),(36,8,'ai_chat','Tutor: what is Erosion?','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:11:04'),(37,8,'ai_chat','Tutor: hey','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:11:16'),(38,8,'ai_chat','Tutor: who are you?','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:11:20'),(39,8,'ai_chat','Tutor: i want maths','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:11:30'),(40,8,'ai_chat','Tutor: Cat','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:12:32'),(41,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:15:41'),(42,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:15:41'),(43,2,'announcement.create','Regional admin announced to school #1','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:15:47'),(44,2,'announcement.create','Regional admin announced to school #3','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:15:53'),(45,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:16:27'),(46,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:16:27'),(47,8,'ai_chat','Tutor: cat','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:45:06'),(48,8,'ai_chat','Tutor: C D _','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:45:38'),(49,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:45:46'),(50,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:45:46'),(51,2,'announcement.create','Regional admin announced to school #1','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:45:54'),(52,2,'announcement.create','Regional admin announced to school #3','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:46:00'),(53,2,'announcement.create','Regional admin announced to school #3','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:46:08'),(54,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:46:24'),(55,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:46:24'),(56,4,'login','Signed in as student','::1','curl/8.20.0','2026-08-22 00:56:26'),(57,4,'login','Signed in','::1','curl/8.20.0','2026-08-22 00:56:26'),(58,4,'ai_chat','Tutor: What is 2+2?','::1','curl/8.20.0','2026-08-22 01:09:10'),(59,2,'login','Signed in as admin','::1','curl/8.20.0','2026-08-22 01:09:47'),(60,4,'login','Signed in as student','::1','curl/8.20.0','2026-08-22 01:10:40'),(61,4,'login','Signed in','::1','curl/8.20.0','2026-08-22 01:10:40'),(62,4,'ai_chat','Tutor: What is 2 plus 2','::1','curl/8.20.0','2026-08-22 01:18:37'),(63,8,'ai_chat','Tutor: who are you?','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 01:28:28'),(64,8,'ai_chat','Tutor: explain 2+2','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 01:28:58'),(65,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 01:29:53'),(66,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 01:29:53'),(67,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:22:54'),(68,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:22:54'),(69,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:23:49'),(70,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:23:49'),(71,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:27:53'),(72,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:27:53'),(73,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:46:32'),(74,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:46:32'),(75,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:47:16'),(76,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:47:16'),(77,8,'user','Director created teacher Henok Arega','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:48:54'),(78,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:46:10'),(79,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:46:10'),(80,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:51:26'),(81,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:51:26'),(82,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 06:52:18'),(83,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 06:52:18'),(84,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 06:53:22'),(85,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 06:53:22'),(86,1,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:55:18'),(87,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:55:18'),(88,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 06:56:22'),(89,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 06:56:22'),(90,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 06:57:14'),(91,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 06:57:14'),(92,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 06:57:27'),(93,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 06:57:27'),(94,1,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 06:57:51'),(95,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 06:57:51'),(96,1,'login','Signed in as sysadmin','::1','curl/8.20.0','2026-08-23 07:01:21'),(97,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 07:01:21'),(98,1,'login','Signed in as sysadmin','::1','curl/8.20.0','2026-08-23 07:06:31'),(99,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 07:06:31'),(100,4,'login','Signed in as student','::1','curl/8.20.0','2026-08-23 07:06:31'),(101,4,'login','Signed in','::1','curl/8.20.0','2026-08-23 07:06:31'),(102,1,'login','Signed in as sysadmin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:08:52'),(103,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:08:52'),(104,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:09:05'),(105,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:09:05'),(106,1,'login','Signed in as sysadmin','::1','curl/8.20.0','2026-08-23 07:10:55'),(107,1,'login','Signed in','::1','curl/8.20.0','2026-08-23 07:10:55'),(108,2,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 07:12:34'),(109,2,'login','Signed in','::1','curl/8.20.0','2026-08-23 07:12:34'),(110,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:15:50'),(111,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:15:50'),(112,2,'login','Signed in as admin','::1','curl/8.20.0','2026-08-23 07:20:40'),(113,2,'login','Signed in','::1','curl/8.20.0','2026-08-23 07:20:40'),(114,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 09:16:47'),(115,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 09:16:47'),(116,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 09:18:01'),(117,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 09:18:01'),(118,8,'login','Signed in as director','::1','curl/8.20.0','2026-08-23 09:23:12'),(119,8,'login','Signed in','::1','curl/8.20.0','2026-08-23 09:23:12'),(120,8,'login','Signed in as director','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 09:24:29'),(121,8,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 09:24:29'),(122,1,'login','Signed in as sysadmin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 09:25:48'),(123,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 09:25:48'),(124,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 10:51:13'),(125,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 10:51:13'),(126,19,'login','Signed in as student','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 11:04:17'),(127,19,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 11:04:17'),(128,19,'login','Signed in as student','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 12:58:54'),(129,19,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 12:58:54'),(130,19,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 13:05:50'),(131,19,'settings','Toggled demo mode: ON (Demo)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 13:05:54'),(132,19,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 13:05:58'),(133,19,'settings','Toggled demo mode: ON (Demo)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 13:06:01'),(134,19,'login','Signed in as student','::1','curl/8.20.0','2026-08-24 13:12:03'),(135,19,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:12:03'),(136,18,'login','Signed in as student','::1','curl/8.20.0','2026-08-24 13:12:03'),(137,18,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:12:03'),(138,14,'login','Signed in as lecturer','::1','curl/8.20.0','2026-08-24 13:14:01'),(139,14,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:14:01'),(140,15,'login','Signed in as bursar','::1','curl/8.20.0','2026-08-24 13:14:01'),(141,15,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:14:01'),(142,17,'login','Signed in as librarian','::1','curl/8.20.0','2026-08-24 13:14:01'),(143,17,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:14:01'),(144,16,'login','Signed in as student_affairs','::1','curl/8.20.0','2026-08-24 13:14:01'),(145,16,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:14:01'),(146,11,'login','Signed in as dean','::1','curl/8.20.0','2026-08-24 13:14:02'),(147,11,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:14:02'),(148,10,'login','Signed in as registrar','::1','curl/8.20.0','2026-08-24 13:14:02'),(149,10,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:14:02'),(150,19,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 13:19:44'),(151,10,'login','Signed in as registrar','::1','curl/8.20.0','2026-08-24 13:19:59'),(152,10,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:19:59'),(153,11,'login','Signed in as dean','::1','curl/8.20.0','2026-08-24 13:19:59'),(154,11,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:19:59'),(155,19,'settings','Toggled demo mode: ON (Demo)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 13:20:42'),(156,10,'login','Signed in as registrar','::1','curl/8.20.0','2026-08-24 13:29:31'),(157,10,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:29:31'),(158,11,'login','Signed in as dean','::1','curl/8.20.0','2026-08-24 13:29:31'),(159,11,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:29:31'),(160,19,'login','Signed in as student','::1','curl/8.20.0','2026-08-24 13:31:37'),(161,19,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:31:37'),(162,14,'login','Signed in as lecturer','::1','curl/8.20.0','2026-08-24 13:31:38'),(163,14,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:31:38'),(164,15,'login','Signed in as bursar','::1','curl/8.20.0','2026-08-24 13:31:38'),(165,15,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:31:38'),(166,17,'login','Signed in as librarian','::1','curl/8.20.0','2026-08-24 13:31:38'),(167,17,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:31:38'),(168,16,'login','Signed in as student_affairs','::1','curl/8.20.0','2026-08-24 13:31:39'),(169,16,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:31:39'),(170,11,'login','Signed in as dean','::1','curl/8.20.0','2026-08-24 13:31:39'),(171,11,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:31:39'),(172,10,'login','Signed in as registrar','::1','curl/8.20.0','2026-08-24 13:31:39'),(173,10,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:31:39'),(174,14,'login','Signed in as lecturer','::1','curl/8.20.0','2026-08-24 13:34:23'),(175,14,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:34:23'),(176,19,'login','Signed in as student','::1','curl/8.20.0','2026-08-24 13:38:29'),(177,19,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:38:29'),(178,14,'login','Signed in as lecturer','::1','curl/8.20.0','2026-08-24 13:38:29'),(179,14,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:38:29'),(180,15,'login','Signed in as bursar','::1','curl/8.20.0','2026-08-24 13:38:29'),(181,15,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:38:29'),(182,17,'login','Signed in as librarian','::1','curl/8.20.0','2026-08-24 13:38:30'),(183,17,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:38:30'),(184,16,'login','Signed in as student_affairs','::1','curl/8.20.0','2026-08-24 13:38:30'),(185,16,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:38:30'),(186,11,'login','Signed in as dean','::1','curl/8.20.0','2026-08-24 13:38:30'),(187,11,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:38:30'),(188,10,'login','Signed in as registrar','::1','curl/8.20.0','2026-08-24 13:38:31'),(189,10,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:38:31'),(190,14,'login','Signed in as lecturer','::1','curl/8.20.0','2026-08-24 13:40:39'),(191,14,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:40:39'),(192,14,'login','Signed in as lecturer','::1','curl/8.20.0','2026-08-24 13:42:42'),(193,14,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:42:42'),(194,14,'login','Signed in as lecturer','::1','curl/8.20.0','2026-08-24 13:47:16'),(195,14,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:47:16'),(196,19,'login','Signed in as student','::1','curl/8.20.0','2026-08-24 13:48:21'),(197,19,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:48:21'),(198,14,'login','Signed in as lecturer','::1','curl/8.20.0','2026-08-24 13:48:21'),(199,14,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:48:21'),(200,15,'login','Signed in as bursar','::1','curl/8.20.0','2026-08-24 13:48:21'),(201,15,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:48:21'),(202,17,'login','Signed in as librarian','::1','curl/8.20.0','2026-08-24 13:48:22'),(203,17,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:48:22'),(204,16,'login','Signed in as student_affairs','::1','curl/8.20.0','2026-08-24 13:48:22'),(205,16,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:48:22'),(206,11,'login','Signed in as dean','::1','curl/8.20.0','2026-08-24 13:48:22'),(207,11,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:48:22'),(208,10,'login','Signed in as registrar','::1','curl/8.20.0','2026-08-24 13:48:23'),(209,10,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:48:23'),(210,14,'login','Signed in as lecturer','::1','curl/8.20.0','2026-08-24 13:50:32'),(211,14,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:50:32'),(212,19,'login','Signed in as student','::1','curl/8.20.0','2026-08-24 13:53:05'),(213,19,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:53:05'),(214,14,'login','Signed in as lecturer','::1','curl/8.20.0','2026-08-24 13:53:06'),(215,14,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:53:06'),(216,15,'login','Signed in as bursar','::1','curl/8.20.0','2026-08-24 13:53:06'),(217,15,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:53:06'),(218,17,'login','Signed in as librarian','::1','curl/8.20.0','2026-08-24 13:53:06'),(219,17,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:53:06'),(220,16,'login','Signed in as student_affairs','::1','curl/8.20.0','2026-08-24 13:53:07'),(221,16,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:53:07'),(222,11,'login','Signed in as dean','::1','curl/8.20.0','2026-08-24 13:53:07'),(223,11,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:53:07'),(224,10,'login','Signed in as registrar','::1','curl/8.20.0','2026-08-24 13:53:07'),(225,10,'login','Signed in','::1','curl/8.20.0','2026-08-24 13:53:07'),(226,19,'login','Signed in as student','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 15:46:34'),(227,19,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 15:46:34'),(228,19,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 15:46:42'),(229,19,'settings','Toggled demo mode: ON (Demo)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 15:47:06'),(230,19,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 15:47:09'),(231,19,'settings','Toggled demo mode: ON (Demo)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 16:02:18'),(232,19,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 16:02:24'),(233,19,'login','Signed in as student','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 21:37:04'),(234,19,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 21:37:04'),(235,19,'settings','Toggled demo mode: ON (Demo)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 21:37:07'),(236,19,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 21:37:15'),(237,19,'settings','Toggled demo mode: ON (Demo)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 21:37:36'),(238,19,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 21:37:56'),(239,19,'settings','Toggled demo mode: ON (Demo)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 21:46:05'),(240,20,'login','Signed in as zonal_admin','::1','curl/8.20.0','2026-08-25 08:08:18'),(241,20,'login','Signed in','::1','curl/8.20.0','2026-08-25 08:08:18'),(242,21,'login','Signed in as woreda_admin','::1','curl/8.20.0','2026-08-25 08:08:33'),(243,21,'login','Signed in','::1','curl/8.20.0','2026-08-25 08:08:33'),(244,2,'login','Signed in as admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 08:18:01'),(245,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 08:18:01'),(246,2,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 08:20:08'),(247,21,'login','Signed in as woreda_admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 08:33:15'),(248,21,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 08:33:15'),(249,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 08:57:06'),(250,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 08:57:06'),(251,20,'login','Signed in as zonal','::1','curl/8.20.0','2026-08-25 08:57:30'),(252,20,'login','Signed in','::1','curl/8.20.0','2026-08-25 08:57:30'),(253,21,'login','Signed in as woreda','::1','curl/8.20.0','2026-08-25 08:57:31'),(254,21,'login','Signed in','::1','curl/8.20.0','2026-08-25 08:57:31'),(255,21,'login','Signed in as woreda','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 10:11:37'),(256,21,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 10:11:37'),(257,22,'login','Signed in as it_admin','::1','curl/8.20.0','2026-08-25 11:58:44'),(258,22,'login','Signed in','::1','curl/8.20.0','2026-08-25 11:58:44'),(259,2,'login','Signed in as regional','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 12:16:50'),(260,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 12:16:50'),(261,2,'login','Signed in as regional','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 15:46:02'),(262,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 15:46:02'),(263,2,'login','Signed in as regional','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 15:48:56'),(264,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 15:48:56'),(265,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 15:51:17'),(266,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 15:51:17'),(267,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 17:42:39'),(268,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 17:42:39'),(269,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 17:43:52'),(270,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 17:43:52'),(271,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 17:46:26'),(272,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 17:46:26'),(273,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 17:47:45'),(274,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 17:47:45'),(275,20,'login','Signed in as zonal','::1','curl/8.20.0','2026-08-25 17:47:46'),(276,20,'login','Signed in','::1','curl/8.20.0','2026-08-25 17:47:46'),(277,21,'login','Signed in as woreda','::1','curl/8.20.0','2026-08-25 17:47:46'),(278,21,'login','Signed in','::1','curl/8.20.0','2026-08-25 17:47:46'),(279,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 17:48:07'),(280,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 17:48:07'),(281,20,'login','Signed in as zonal','::1','curl/8.20.0','2026-08-25 17:48:08'),(282,20,'login','Signed in','::1','curl/8.20.0','2026-08-25 17:48:08'),(283,21,'login','Signed in as woreda','::1','curl/8.20.0','2026-08-25 17:48:08'),(284,21,'login','Signed in','::1','curl/8.20.0','2026-08-25 17:48:08'),(285,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 18:13:51'),(286,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 18:13:51'),(287,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 18:19:09'),(288,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 18:19:09'),(289,1,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 18:19:52'),(290,1,'settings','Toggled demo mode: ON (Demo)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 18:20:21'),(291,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 18:20:54'),(292,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 18:20:54'),(293,1,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 18:23:15'),(294,1,'settings','Toggled demo mode: ON (Demo)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 18:23:21'),(295,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 18:26:27'),(296,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 18:26:27'),(297,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 18:33:29'),(298,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 18:33:29'),(299,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 18:35:37'),(300,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 18:35:37'),(301,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 18:48:04'),(302,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 18:48:04'),(303,1,'settings','Toggled demo mode: ON (Demo)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 18:55:14'),(304,22,'login','Signed in as it_admin','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 18:57:05'),(305,22,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 18:57:05'),(306,22,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 18:57:16'),(307,22,'settings','Toggled demo mode: ON (Demo)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 18:57:19'),(308,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 21:33:04'),(309,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 21:33:04'),(310,1,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 21:33:21'),(311,1,'settings','Toggled demo mode: ON (Demo)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 21:33:26'),(312,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 21:47:46'),(313,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 21:47:46'),(314,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 22:29:48'),(315,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 22:29:48'),(316,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 22:34:36'),(317,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 22:34:36'),(318,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 22:43:02'),(319,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 22:43:02'),(320,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 22:50:40'),(321,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 22:50:40'),(322,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 22:58:39'),(323,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 22:58:39'),(324,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-25 23:30:55'),(325,1,'login','Signed in','::1','curl/8.20.0','2026-08-25 23:30:55'),(326,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 23:32:44'),(327,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 23:32:44'),(328,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 00:18:54'),(329,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 00:18:54'),(330,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 00:19:56'),(331,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 00:19:56'),(332,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 00:20:07'),(333,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 00:20:07'),(334,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 00:20:27'),(335,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 00:20:27'),(336,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 00:23:37'),(337,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 00:23:37'),(338,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 00:23:47'),(339,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 00:23:47'),(340,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 00:33:47'),(341,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 00:33:47'),(342,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 00:44:40'),(343,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 00:44:40'),(344,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 00:44:54'),(345,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 00:44:54'),(346,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 00:45:10'),(347,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 00:45:10'),(348,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 00:52:46'),(349,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 00:52:46'),(350,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 00:53:39'),(351,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 00:53:39'),(352,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 01:02:24'),(353,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 01:02:25'),(354,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 01:02:35'),(355,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 01:02:35'),(356,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 01:02:47'),(357,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 01:02:47'),(358,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 01:02:56'),(359,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 01:02:56'),(360,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 01:03:24'),(361,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 01:03:24'),(362,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 01:03:38'),(363,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 01:03:38'),(364,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 01:03:53'),(365,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 01:03:53'),(366,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 01:13:11'),(367,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 01:13:11'),(368,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 01:13:42'),(369,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 01:13:42'),(370,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 01:35:31'),(371,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 01:35:31'),(372,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 01:43:39'),(373,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 01:43:39'),(374,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-26 22:18:44'),(375,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-26 22:18:44'),(376,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 22:24:47'),(377,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 22:24:47'),(378,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 22:33:33'),(379,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 22:33:33'),(380,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-26 22:37:50'),(381,1,'login','Signed in','::1','curl/8.20.0','2026-08-26 22:37:50'),(382,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-26 23:49:17'),(383,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-26 23:49:17'),(384,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 01:23:31'),(385,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 01:23:31'),(386,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 01:54:31'),(387,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 01:54:31'),(388,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 02:13:25'),(389,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 02:13:25'),(390,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 02:49:14'),(391,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 02:49:14'),(392,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 02:57:17'),(393,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 02:57:17'),(394,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 08:57:01'),(395,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 08:57:01'),(396,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 09:00:41'),(397,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 09:00:41'),(398,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 09:15:29'),(399,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 09:15:29'),(400,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 09:45:24'),(401,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 09:45:24'),(402,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 09:51:36'),(403,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 09:51:36'),(404,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 10:02:25'),(405,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 10:02:25'),(406,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 10:05:56'),(407,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 10:05:56'),(408,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 11:15:23'),(409,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 11:15:23'),(410,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 11:19:03'),(411,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 11:19:03'),(412,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 11:19:27'),(413,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 11:19:27'),(414,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 11:20:07'),(415,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 11:20:08'),(416,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 11:22:32'),(417,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 11:22:32'),(418,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 11:25:58'),(419,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 11:25:58'),(420,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 11:30:17'),(421,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 11:30:17'),(422,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 11:35:49'),(423,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 11:35:49'),(424,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 11:42:18'),(425,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 11:42:18'),(426,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 11:49:11'),(427,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 11:49:11'),(428,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 23:15:43'),(429,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 23:15:43'),(430,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 23:33:30'),(431,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 23:33:30'),(432,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 23:35:34'),(433,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 23:35:34'),(434,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 23:39:47'),(435,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 23:39:47'),(436,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-27 23:42:10'),(437,1,'login','Signed in','::1','curl/8.20.0','2026-08-27 23:42:10'),(438,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 00:04:18'),(439,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 00:04:18'),(440,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 00:12:48'),(441,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 00:12:48'),(442,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 00:14:37'),(443,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 00:14:37'),(444,1,'academic_year','Created shared school calendar: 2026/27','::1','curl/8.20.0','2026-08-28 00:14:38'),(445,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 00:14:56'),(446,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 00:14:56'),(447,1,'academic_year','Created shared school calendar: 2026/27','::1','curl/8.20.0','2026-08-28 00:14:56'),(448,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 00:19:27'),(449,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 00:19:27'),(450,1,'academic_year','Applied shared calendar to 2 schools','::1','curl/8.20.0','2026-08-28 00:19:44'),(451,1,'academic_year','Applied shared calendar to 0 schools','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 00:21:03'),(452,1,'academic_year','Applied shared calendar to 0 schools','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 00:21:05'),(453,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 00:23:38'),(454,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 00:23:38'),(455,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 00:38:56'),(456,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 00:38:56'),(457,25,'login','Signed in as student','::1','curl/8.20.0','2026-08-28 00:39:33'),(458,25,'login','Signed in','::1','curl/8.20.0','2026-08-28 00:39:33'),(459,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 00:43:18'),(460,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 00:43:18'),(461,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 00:59:10'),(462,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 00:59:10'),(463,1,'announcement','Posted: School Holiday in Amhara (pending regional approval for Amhara)','::1','curl/8.20.0','2026-08-28 01:01:17'),(464,2,'login','Signed in as regional','::1','curl/8.20.0','2026-08-28 01:01:34'),(465,2,'login','Signed in','::1','curl/8.20.0','2026-08-28 01:01:34'),(466,6,'login','Signed in as regional','::1','curl/8.20.0','2026-08-28 01:02:41'),(467,6,'login','Signed in','::1','curl/8.20.0','2026-08-28 01:02:41'),(468,6,'announcement.approve','Approved announcement #24 for Amhara','::1','curl/8.20.0','2026-08-28 01:10:23'),(469,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 01:16:47'),(470,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 01:16:47'),(471,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 01:22:52'),(472,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 01:22:52'),(473,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 01:34:48'),(474,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 01:34:48'),(475,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 01:36:05'),(476,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 01:36:05'),(477,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 02:12:01'),(478,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 02:12:01'),(479,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 02:14:54'),(480,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 02:14:54'),(481,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 02:16:48'),(482,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 02:16:48'),(483,1,'report','Generated: Test Enrollment','::1','curl/8.20.0','2026-08-28 02:17:33'),(484,1,'report','Generated: Institution Statistics Report — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 02:17:43'),(485,1,'report','Generated: Digital Platform Usage Report — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 02:17:44'),(486,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 02:19:18'),(487,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 02:19:18'),(488,1,'report','Generated: Enrollment Statistics Report — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 02:19:18'),(489,1,'report','Generated: Academic Performance Report — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 02:19:19'),(490,1,'report','Generated: School Performance Report — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 02:19:19'),(491,1,'report','Generated: Course & Curriculum Analytics Report — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 02:19:19'),(492,1,'report','Generated: Student Progress Report — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 02:19:19'),(493,1,'report','Generated: Regional Education Report — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 02:19:19'),(494,1,'report','Generated: Institution Statistics Report — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 02:19:20'),(495,1,'report','Generated: Digital Platform Usage Report — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 02:19:20'),(496,1,'report','Generated: System Activity Report — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 02:19:20'),(497,1,'report','Generated: Teacher Workforce Statistics Report — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 02:19:20'),(498,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 07:08:29'),(499,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 07:08:29'),(500,1,'report','Generated: hello','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 07:08:58'),(501,1,'report','Generated: hello2','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 07:09:20'),(502,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 07:12:06'),(503,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 07:12:06'),(504,1,'report','Generated: Enrollment PDF Test','::1','curl/8.20.0','2026-08-28 07:12:06'),(505,1,'report','Generated: ere','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 07:19:28'),(506,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 07:32:28'),(507,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 07:32:28'),(508,1,'report','Generated: Enrollment Statistics Report','::1','curl/8.20.0','2026-08-28 07:32:29'),(509,1,'report','Generated: ewdwed','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 07:33:09'),(510,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 07:38:05'),(511,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 07:38:05'),(512,1,'report','Generated: Enrollment Statistics Report','::1','curl/8.20.0','2026-08-28 07:38:05'),(513,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 07:40:08'),(514,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 07:40:08'),(515,1,'report','Generated: Enrollment Statistics Report','::1','curl/8.20.0','2026-08-28 07:40:08'),(516,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 07:42:37'),(517,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 07:42:37'),(518,1,'report','Generated: Enrollment Statistics Report','::1','curl/8.20.0','2026-08-28 07:42:37'),(519,1,'report','Generated: School Performance Report','::1','curl/8.20.0','2026-08-28 07:42:56'),(520,1,'report','Generated: qqqqqq','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 07:43:15'),(521,1,'report','Generated: System Activity Report — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 07:58:06'),(522,1,'report','Generated: fsfs','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 08:16:56'),(523,1,'report','Generated: System Activity Report — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 08:17:06'),(524,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 08:27:14'),(525,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 08:27:14'),(526,1,'report','Generated: Enrollment Test','::1','curl/8.20.0','2026-08-28 08:27:14'),(527,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 08:28:59'),(528,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 08:28:59'),(529,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 08:30:13'),(530,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 08:30:13'),(531,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 08:33:27'),(532,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 08:33:27'),(533,1,'report','Generated: Enrollment Test PDF','::1','curl/8.20.0','2026-08-28 08:33:38'),(534,1,'report','Generated: System Activity Report — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 08:34:48'),(535,1,'report','Generated: Education Performance Report — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 08:35:53'),(536,1,'report','Generated: Student Progress Report — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 08:36:20'),(537,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 08:40:16'),(538,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 08:40:16'),(539,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 10:56:27'),(540,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 10:56:27'),(541,1,'report','Generated: Education Performance Report — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 10:56:44'),(542,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 11:22:58'),(543,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 11:22:58'),(544,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 11:24:59'),(545,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 11:24:59'),(546,1,'report','Generated: Education Performance Report — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 11:26:05'),(547,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 11:44:48'),(548,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 11:44:48'),(549,1,'report','Generated: Enrollment Statistics Report — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 11:52:51'),(550,1,'report','Generated: Student Progress Report — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 11:53:06'),(551,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 12:01:07'),(552,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 12:01:07'),(553,1,'report','Generated: Enrollment Statistics — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 12:01:20'),(554,1,'report','Generated: Teacher Workforce Statistics — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 12:01:20'),(555,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 12:03:23'),(556,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 12:03:23'),(557,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 12:32:39'),(558,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 12:32:39'),(559,1,'report','Generated: Teacher Workforce Statistics — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:33:39'),(560,1,'report','Generated: Course & Curriculum Analytics — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:33:39'),(561,1,'report','Generated: Student Progress — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:33:39'),(562,1,'report','Generated: Regional Education — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:33:39'),(563,1,'report','Generated: Digital Platform Usage — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:33:39'),(564,1,'report','Generated: Compliance — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:33:39'),(565,1,'report','Generated: Enrollment Statistics — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 12:34:41'),(566,1,'report','Generated: Teacher Workforce Statistics — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 12:34:41'),(567,1,'report','Generated: Academic Performance — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 12:34:41'),(568,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 12:35:30'),(569,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 12:35:30'),(570,1,'report','Generated: Enrollment Statistics — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 12:35:31'),(571,1,'report','Generated: Teacher Workforce Statistics — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 12:35:31'),(572,1,'report','Generated: Academic Performance — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 12:35:31'),(573,1,'report','Generated: Institution Statistics — Aug 28, 2026','::1','curl/8.20.0','2026-08-28 12:35:31'),(574,1,'report','Generated: Education Performance — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:46:42'),(575,1,'report','Generated: Enrollment Statistics — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:46:42'),(576,1,'report','Generated: Academic Performance — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:46:42'),(577,1,'report','Generated: Attendance & Participation — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:46:42'),(578,1,'report','Generated: National Exam Performance — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:46:42'),(579,1,'report','Generated: School Performance — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:46:42'),(580,1,'report','Generated: Course & Curriculum Analytics — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:46:42'),(581,1,'report','Generated: Learning Activity — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:46:42'),(582,1,'report','Generated: Attendance & Participation — yy','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:46:59'),(583,1,'report','Generated: Teacher Workforce Statistics — yy','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:46:59'),(584,1,'report','Generated: Institution Statistics Report — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:47:19'),(585,1,'report','Generated: PDF Test','::1','curl/8.20.0','2026-08-28 12:49:34'),(586,1,'report','Generated: Education Performance Report — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:56:00'),(587,1,'report','Generated: Compliance Report — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 12:56:09'),(588,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 13:00:50'),(589,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 13:00:50'),(590,1,'report','Generated: Glass Test','::1','curl/8.20.0','2026-08-28 13:00:51'),(591,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 13:03:44'),(592,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 13:03:44'),(593,1,'report','Generated: Digital Platform Usage Report — Aug 28, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 13:03:51'),(594,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 13:16:07'),(595,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 13:16:07'),(596,1,'badges','Updated badge: First Login','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 13:34:18'),(597,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-28 13:39:29'),(598,1,'login','Signed in','::1','curl/8.20.0','2026-08-28 13:39:29'),(599,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 01:55:14'),(600,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 01:55:14'),(601,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 02:04:28'),(602,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 02:04:28'),(603,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 02:12:44'),(604,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 02:12:44'),(605,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 02:17:05'),(606,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 02:17:05'),(607,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 02:19:17'),(608,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 02:19:17'),(609,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 02:19:38'),(610,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 02:19:38'),(611,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 02:24:19'),(612,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 02:24:19'),(613,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 02:24:43'),(614,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 02:24:43'),(615,1,'module','Disabled module: ai-tutor','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 02:31:04'),(616,1,'module','Installed module: ai-tutor','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 02:31:17'),(617,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 02:46:51'),(618,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 02:46:51'),(619,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 02:59:12'),(620,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 02:59:12'),(621,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 07:04:36'),(622,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 07:04:36'),(623,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 07:13:14'),(624,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 07:13:14'),(625,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 07:14:06'),(626,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 07:14:06'),(627,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 07:16:10'),(628,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 07:16:10'),(629,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 07:18:58'),(630,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 07:18:58'),(631,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 08:02:37'),(632,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 08:02:38'),(633,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 08:23:37'),(634,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 08:23:37'),(635,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 08:28:47'),(636,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 08:28:47'),(637,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 09:03:38'),(638,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 09:03:38'),(639,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 09:06:21'),(640,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 09:06:21'),(641,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 09:31:45'),(642,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 09:31:45'),(643,1,'license','Issued license EDX-PE2Q4-CYQ8R-6GBN5-4LCUB to Asas','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 09:43:21'),(644,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 10:15:05'),(645,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 10:15:05'),(646,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 10:17:14'),(647,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 10:17:14'),(648,4,'login','Signed in as student','::1','curl/8.20.0','2026-08-29 10:18:22'),(649,4,'login','Signed in','::1','curl/8.20.0','2026-08-29 10:18:22'),(650,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 10:35:05'),(651,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 10:35:05'),(652,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 10:36:58'),(653,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 10:36:58'),(654,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 10:37:41'),(655,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 10:37:41'),(656,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 10:38:53'),(657,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 10:38:53'),(658,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 10:54:43'),(659,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 10:54:43'),(660,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 11:00:01'),(661,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 11:00:01'),(662,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 11:01:00'),(663,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 11:01:00'),(664,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 11:02:28'),(665,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 11:02:28'),(666,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 11:08:42'),(667,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 11:08:42'),(668,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 11:10:27'),(669,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 11:10:27'),(670,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 11:22:27'),(671,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 11:22:27'),(672,1,'ledger','Security console re-verify: BROKEN at #1','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 11:23:44'),(673,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 12:16:07'),(674,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 12:16:07'),(675,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 12:51:51'),(676,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 12:51:51'),(677,1,'login','Signed in as ministry','::1','curl/8.20.0','2026-08-29 14:16:33'),(678,1,'login','Signed in','::1','curl/8.20.0','2026-08-29 14:16:33'),(679,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 00:27:55'),(680,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 00:27:55'),(681,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 02:07:07'),(682,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 02:07:07'),(683,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 16:24:15'),(684,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 16:24:15'),(685,1,'report','Generated: Education Performance Report — Aug 30, 2026','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 16:28:20'),(686,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 17:37:26'),(687,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 17:37:26'),(688,1,'calendar_event','Created event dghdghg','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 18:28:08'),(689,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 18:58:02'),(690,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 18:58:02'),(691,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 20:57:03'),(692,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 20:57:03'),(693,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 22:14:15'),(694,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 22:14:15'),(695,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 03:14:52'),(696,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 03:14:52'),(697,1,'files.folder','Created folder hey','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 03:14:59'),(698,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 05:18:44'),(699,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 05:18:44'),(700,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 05:21:23'),(701,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 05:21:23'),(702,1,'it_fix.create','Ticket #1 created for admin/dashboard','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 05:57:25'),(703,1,'it_fix.create','Ticket #2 created for admin/dashboard','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 05:57:32'),(704,1,'it_fix.create','Ticket #3 created for admin/dashboard','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 05:58:19'),(705,1,'it_fix.create','Ticket #4 created for admin/dashboard','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 06:02:21'),(706,1,'it_fix.create','Ticket #5 created for admin/courses','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 06:02:36'),(707,1,'it_fix.freeze','Ticket #5 frozen by user','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 07:09:48'),(708,1,'it_fix.unfreeze','Ticket #5 unfrozen by user','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 07:09:56'),(709,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 07:27:26'),(710,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 07:27:26'),(711,1,'login','Signed in as ministry','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 09:38:34'),(712,1,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 09:38:35'),(713,1,'settings','Toggled demo mode: OFF (Normal)','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 09:39:31'),(714,2,'login','Signed in as regional','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 09:47:28'),(715,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 09:47:28'),(716,2,'login','Signed in as regional','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-09-01 08:07:10'),(717,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-09-01 08:07:10'),(718,2,'login','Signed in as regional','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-09-01 08:31:04'),(719,2,'login','Signed in','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-09-01 08:31:04');
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
  `target_region` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_zone` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval_status` enum('none','pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `approved_by` int unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `course_id` (`course_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `announcements_ibfk_3` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,1,NULL,2,'Welcome to the new academic year!','We are excited to welcome all students back. The digital library now has 200+ resources.',1,'all',NULL,NULL,'none',NULL,NULL,'2026-08-21 22:36:25',0),(2,1,1,3,'Midterm exam schedule published','The Algebra midterm will take place next week. Review chapters 1-2.',1,'course',NULL,NULL,'none',NULL,NULL,'2026-08-21 22:36:25',0),(3,1,NULL,2,'Library maintenance on Sunday','The library will be briefly offline Sunday 02:00-04:00.',0,'all',NULL,NULL,'none',NULL,NULL,'2026-08-21 22:36:25',0),(4,1,NULL,2,'hgh','hhhhhhhhhhhhhh',0,'all',NULL,NULL,'none',NULL,NULL,'2026-08-21 23:02:00',0),(5,3,NULL,2,'cccccccc','ccccccccccc',0,'all',NULL,NULL,'none',NULL,NULL,'2026-08-21 23:03:19',0),(6,1,NULL,2,'gdgd','dgdgdg',0,'all',NULL,NULL,'none',NULL,NULL,'2026-08-22 00:15:47',0),(7,3,NULL,2,'dgdg','dgdgdgd',0,'all',NULL,NULL,'none',NULL,NULL,'2026-08-22 00:15:53',0),(9,1,NULL,2,'FDSFffffffffffffff','fffffffffffffffffffffffffffffffffff',0,'all',NULL,NULL,'none',NULL,NULL,'2026-08-22 00:45:54',0),(10,3,NULL,2,'fffffffffffffffff','ffffffffffffff',0,'all',NULL,NULL,'none',NULL,NULL,'2026-08-22 00:46:00',0),(11,3,NULL,2,'ffffffffff','ffffffffffffffffffff',0,'all',NULL,NULL,'none',NULL,NULL,'2026-08-22 00:46:08',0),(12,1,NULL,3,'Welcome Back to School!','We are excited to start the new academic year. Please check your schedules and course materials.',1,'all',NULL,NULL,'none',NULL,NULL,'2026-08-24 11:55:47',1),(13,1,NULL,3,'Science Fair 2026','Annual science fair will be held on March 15th. All students are encouraged to participate.',0,'students',NULL,NULL,'none',NULL,NULL,'2026-08-24 11:55:47',1),(14,1,NULL,3,'Parent-Teacher Conference','Scheduled for February 20th. Please book your time slot through the portal.',0,'parents',NULL,NULL,'none',NULL,NULL,'2026-08-24 11:55:47',1),(16,1,NULL,3,'Science Fair 2026','Annual science fair will be held on March 15th. All students are encouraged to participate.',0,'students',NULL,NULL,'none',NULL,NULL,'2026-08-24 11:56:24',1),(17,1,NULL,3,'Parent-Teacher Conference','Scheduled for February 20th. Please book your time slot through the portal.',0,'parents',NULL,NULL,'none',NULL,NULL,'2026-08-24 11:56:24',1),(19,3,NULL,10,'Thesis Defense Schedule','All thesis defense schedules have been posted. Please check the portal for your assigned time.',0,'students',NULL,NULL,'none',NULL,NULL,'2026-08-24 11:56:24',1),(20,3,NULL,11,'BDU Semester 3 Registration Open','Course registration for Semester 3 is now open. Students should register before the deadline.',0,'all',NULL,NULL,'none',NULL,NULL,'2026-08-24 12:17:23',1),(21,3,NULL,10,'Mid-Term Examination Schedule Released','The mid-term examination schedule for all departments has been published. Check your department page for details.',0,'all',NULL,NULL,'none',NULL,NULL,'2026-08-24 12:17:23',1),(22,3,NULL,15,'Fee Payment Deadline Extended','The deadline for fee payments has been extended to December 31st. Contact the bursar office for questions.',0,'all',NULL,NULL,'none',NULL,NULL,'2026-08-24 12:17:23',1),(23,3,NULL,14,'Thesis Proposal Defense Workshop','A workshop on thesis proposal writing and defense will be held on October 15th. All final-year students are encouraged to attend.',0,'all',NULL,NULL,'none',NULL,NULL,'2026-08-24 12:17:23',1),(24,1,NULL,1,'School Holiday in Amhara','All schools in Amhara region will be closed next Monday.',0,'all','Amhara',NULL,'approved',6,'2026-08-28 04:10:23','2026-08-28 01:01:17',0);
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
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sub` (`assignment_id`,`student_id`),
  KEY `student_id` (`student_id`),
  KEY `graded_by` (`graded_by`),
  CONSTRAINT `assignment_submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_ibfk_3` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment_submissions`
--

LOCK TABLES `assignment_submissions` WRITE;
/*!40000 ALTER TABLE `assignment_submissions` DISABLE KEYS */;
INSERT INTO `assignment_submissions` VALUES (1,1,4,'I solved problems 1-20. Please see the attached PDF.','','2026-07-28 07:00:00',0,88.00,'Excellent work! Careful with problem 14.',NULL,3,'2026-07-29 09:00:00','graded',0),(14,15,18,NULL,'','2026-08-24 12:15:29',0,95.00,'Excellent implementation with clear documentation.',NULL,NULL,'2026-10-08 14:00:00','graded',1),(15,16,18,NULL,'','2026-08-24 12:15:29',0,88.00,'Good ER diagram, minor normalization issues.',NULL,NULL,'2026-10-10 10:00:00','graded',1),(16,20,18,NULL,'','2026-08-24 12:15:29',0,18.00,'Good understanding of binary concepts.',NULL,NULL,'2026-09-30 10:00:00','graded',1),(17,22,18,NULL,'','2026-08-24 12:15:29',0,45.00,'Solid essay with good research.',NULL,NULL,'2026-10-22 09:00:00','graded',1),(18,17,19,NULL,'','2026-08-24 12:15:29',0,42.00,'Well-structured report with good team coordination.',NULL,NULL,'2026-10-14 11:00:00','graded',1),(19,16,19,NULL,'','2026-08-24 12:15:29',0,79.00,'Solid design but missing some constraints.',NULL,NULL,'2026-10-10 10:30:00','graded',1),(20,19,19,NULL,'','2026-08-24 12:15:29',0,44.00,'Good logic but timing diagram has errors.',NULL,NULL,'2026-10-17 11:00:00','graded',1),(21,22,19,NULL,'','2026-08-24 12:15:29',0,82.00,'Well-written essay with cultural context.',NULL,NULL,'2026-10-22 14:00:00','graded',1);
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
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignments`
--

LOCK TABLES `assignments` WRITE;
/*!40000 ALTER TABLE `assignments` DISABLE KEYS */;
INSERT INTO `assignments` VALUES (1,1,3,'Algebra Worksheet 1','Solve the 20 problems in the attached sheet. Show all your steps.','[{\"criterion\":\"Correctness\",\"max\":60,\"weight\":60},{\"criterion\":\"Steps shown\",\"max\":25,\"weight\":25},{\"criterion\":\"Presentation\",\"max\":15,\"weight\":15}]',100.00,'2026-08-27 01:36:25',1,10.00,'2026-08-21 22:36:25',0),(2,2,3,'Motion Lab Report','Write a lab report about free-fall motion using the provided template.',NULL,50.00,'2026-08-25 01:36:25',1,5.00,'2026-08-21 22:36:25',0),(15,10,14,'Operating Systems Lab 1','Implement a basic shell in C',NULL,100.00,'2026-10-10 23:59:00',1,0.00,'2026-08-24 12:15:17',1),(16,11,14,'Database Design Project','Design an ER diagram and normalize to 3NF',NULL,100.00,'2026-10-08 23:59:00',1,0.00,'2026-08-24 12:15:17',1),(17,12,14,'Agile Sprint Report','Document your team agile sprint process',NULL,50.00,'2026-10-12 23:59:00',1,0.00,'2026-08-24 12:15:17',1),(18,7,14,'Calculus Problem Set 3','Solve integration problems 1-20',NULL,50.00,'2026-10-06 23:59:00',1,0.00,'2026-08-24 12:15:17',1),(19,9,14,'Logic Circuit Implementation','Build a 4-bit adder using logic gates',NULL,100.00,'2026-10-15 23:59:00',1,0.00,'2026-08-24 12:15:17',1),(20,4,14,'CS101 Week 3 Quiz','Short quiz on binary and data representation',NULL,20.00,'2026-09-28 23:59:00',1,0.00,'2026-08-24 12:15:17',1),(21,3,14,'Data Structures Programming Assignment','Implement a balanced BST with rotations',NULL,100.00,'2026-10-18 23:59:00',1,0.00,'2026-08-24 12:15:17',1),(22,8,14,'Essay: Technology in Ethiopian Education','Write a 2000-word essay on EdTech in Ethiopia',NULL,50.00,'2026-10-20 23:59:00',1,0.00,'2026-08-24 12:15:17',1);
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
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_att` (`course_id`,`student_id`,`date`),
  KEY `student_id` (`student_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,1,1,4,'2026-08-13','present',3,'','2026-08-21 22:36:25',0),(2,1,1,4,'2026-08-14','present',3,'','2026-08-21 22:36:25',0),(3,1,1,4,'2026-08-15','late',3,'','2026-08-21 22:36:25',0),(4,1,1,4,'2026-08-16','present',3,'','2026-08-21 22:36:25',0),(5,1,1,4,'2026-08-17','absent',3,'','2026-08-21 22:36:25',0),(6,1,1,4,'2026-08-18','present',3,'','2026-08-21 22:36:25',0),(7,1,1,4,'2026-08-19','present',3,'','2026-08-21 22:36:25',0),(8,1,1,4,'2026-08-20','excused',3,'','2026-08-21 22:36:25',0),(9,1,1,4,'2026-08-21','present',3,'','2026-08-21 22:36:25',0),(10,1,1,4,'2026-08-22','present',3,'','2026-08-21 22:36:25',0),(11,0,2,4,'2026-08-20','present',3,'','2026-08-24 11:59:02',1),(12,0,2,4,'2026-08-21','absent',3,'','2026-08-24 11:59:02',1),(41,3,4,18,'2026-09-15','present',14,'','2026-08-24 12:24:20',1),(42,3,4,18,'2026-09-17','present',14,'','2026-08-24 12:24:20',1),(43,3,4,18,'2026-09-22','late',14,'','2026-08-24 12:24:20',1),(44,3,4,18,'2026-09-24','present',14,'','2026-08-24 12:24:20',1),(45,3,4,18,'2026-09-29','present',14,'','2026-08-24 12:24:20',1),(46,3,4,18,'2026-10-01','absent',14,'','2026-08-24 12:24:20',1),(47,3,4,18,'2026-10-06','present',14,'','2026-08-24 12:24:20',1),(48,3,4,18,'2026-10-08','present',14,'','2026-08-24 12:24:20',1),(49,3,7,18,'2026-09-16','present',14,'','2026-08-24 12:24:20',1),(50,3,7,18,'2026-09-18','present',14,'','2026-08-24 12:24:20',1),(51,3,7,18,'2026-09-23','present',14,'','2026-08-24 12:24:20',1),(52,3,7,18,'2026-09-25','excused',14,'','2026-08-24 12:24:20',1),(53,3,7,18,'2026-09-30','present',14,'','2026-08-24 12:24:20',1),(54,3,7,18,'2026-10-02','present',14,'','2026-08-24 12:24:20',1),(55,3,12,19,'2026-09-19','present',14,'','2026-08-24 12:24:20',1),(56,3,12,19,'2026-09-26','present',14,'','2026-08-24 12:24:20',1),(57,3,12,19,'2026-10-03','late',14,'','2026-08-24 12:24:20',1),(58,3,12,19,'2026-10-10','present',14,'','2026-08-24 12:24:20',1),(59,3,11,19,'2026-09-15','present',14,'','2026-08-24 12:24:20',1),(60,3,11,19,'2026-09-17','absent',14,'','2026-08-24 12:24:20',1),(61,3,11,19,'2026-09-22','present',14,'','2026-08-24 12:24:20',1),(62,3,11,19,'2026-09-24','present',14,'','2026-08-24 12:24:20',1),(63,3,11,19,'2026-09-29','present',14,'','2026-08-24 12:24:20',1);
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
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `badges`
--

LOCK TABLES `badges` WRITE;
/*!40000 ALTER TABLE `badges` DISABLE KEYS */;
INSERT INTO `badges` VALUES (1,'First Steps','medal-silver','Complete your first lesson',50,'learning',0),(2,'Bookworm','thumbs-up','Read 5 lessons',200,'learning',0),(3,'Quiz Whiz','gem','Score 80%+ on any quiz',300,'quiz',0),(4,'Perfect Attendance','heart','7 days of perfect attendance',350,'attendance',0),(5,'On Fire','fire','7-day learning streak',400,'streak',0),(6,'Scholar','graduation','Complete a full course',600,'level',0),(7,'Helping Hand','rocket','Answer 5 forum questions',250,'community',0),(8,'Marathoner','bolt','Reach level 5',500,'level',0),(17,'First Login','medal-gold','Logged in for the first time',10,'learning',1),(18,'Bookworm','medal-bronze','Read 10 library books',50,'learning',1),(19,'Quiz Master','star','Scored 100% on 5 exams',100,'learning',1),(20,'Assignment Hero','crown','Submitted 20 assignments on time',75,'learning',1),(21,'Perfect Attendance','trophy','No absences for a full month',60,'learning',1);
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
  `academic_year_id` int unsigned DEFAULT NULL,
  `semester_id` int unsigned DEFAULT NULL,
  `school_id` int unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_am` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_om` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `event_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `priority` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `ethiopian_date` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gregorian_start` date NOT NULL,
  `gregorian_end` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT '1',
  `scope_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'national',
  `scope_id` int unsigned DEFAULT NULL,
  `issuing_authority` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'school',
  `authority_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `directive_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_closed` tinyint(1) NOT NULL DEFAULT '0',
  `teaching_suspended` tinyint(1) NOT NULL DEFAULT '0',
  `examination_suspended` tinyint(1) NOT NULL DEFAULT '0',
  `attendance_required` tinyint(1) NOT NULL DEFAULT '0',
  `is_academic_day` tinyint(1) NOT NULL DEFAULT '1',
  `makeup_day_required` tinyint(1) NOT NULL DEFAULT '0',
  `affects_academic_days` tinyint(1) NOT NULL DEFAULT '0',
  `affects_semester` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_by` int unsigned NOT NULL,
  `approved_by` int unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `is_demo` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_academic_year` (`academic_year_id`),
  KEY `idx_semester` (`semester_id`),
  KEY `idx_school` (`school_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_scope` (`scope_type`,`scope_id`),
  KEY `idx_status` (`status`),
  KEY `idx_dates` (`gregorian_start`,`gregorian_end`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_events`
--

LOCK TABLES `calendar_events` WRITE;
/*!40000 ALTER TABLE `calendar_events` DISABLE KEYS */;
INSERT INTO `calendar_events` VALUES (1,NULL,NULL,NULL,'dghdghg','fghhf','fghfh','fhgfhgfhgfh','academic','national','normal','ghgj','2026-07-30',NULL,NULL,NULL,1,'national',NULL,'federal','','',0,0,0,0,1,0,1,0,'published','2026-08-30 21:28:11',1,NULL,NULL,0,'2026-08-30 18:28:07','2026-08-30 18:28:11');
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
  `is_demo` tinyint(1) DEFAULT '0',
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
-- Table structure for table `clearance_items`
--

DROP TABLE IF EXISTS `clearance_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clearance_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `request_id` int unsigned NOT NULL,
  `department` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checker_id` int unsigned DEFAULT NULL,
  `status` enum('pending','passed','failed','not_applicable') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `checked_at` timestamp NULL DEFAULT NULL,
  `signature_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `checker_id` (`checker_id`),
  CONSTRAINT `clearance_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `clearance_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clearance_items_ibfk_2` FOREIGN KEY (`checker_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clearance_items`
--

LOCK TABLES `clearance_items` WRITE;
/*!40000 ALTER TABLE `clearance_items` DISABLE KEYS */;
INSERT INTO `clearance_items` VALUES (1,1,'Library',17,'passed','All books returned',NULL,'',1),(2,1,'Finance',15,'passed','All fees paid',NULL,'',1),(3,1,'Academic',NULL,'pending','Awaiting final transcript',NULL,'',1),(4,1,'Dean Office',11,'passed','No outstanding obligations',NULL,'',1);
/*!40000 ALTER TABLE `clearance_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clearance_requests`
--

DROP TABLE IF EXISTS `clearance_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clearance_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int unsigned NOT NULL,
  `type` enum('graduation','transfer','withdrawal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'graduation',
  `status` enum('pending','in_progress','cleared','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `tracking_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_clr_code` (`tracking_code`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `clearance_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clearance_requests`
--

LOCK TABLES `clearance_requests` WRITE;
/*!40000 ALTER TABLE `clearance_requests` DISABLE KEYS */;
INSERT INTO `clearance_requests` VALUES (1,18,'graduation','in_progress','BDU-CLR-0001',NULL,'2026-08-24 11:56:24',NULL,1);
/*!40000 ALTER TABLE `clearance_requests` ENABLE KEYS */;
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
INSERT INTO `conversation_members` VALUES (1,3,NULL),(1,4,NULL),(2,2,'2026-08-22 03:15:59'),(2,3,NULL),(2,4,NULL),(3,4,'2026-08-22 02:50:17'),(3,8,'2026-08-22 04:29:33'),(5,5,NULL),(5,8,'2026-08-22 04:29:42'),(8,2,'2026-08-23 08:23:25'),(8,8,'2026-08-22 04:29:16'),(9,18,NULL),(9,19,'2026-08-24 14:06:31');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversations`
--

LOCK TABLES `conversations` WRITE;
/*!40000 ALTER TABLE `conversations` DISABLE KEYS */;
INSERT INTO `conversations` VALUES (1,1,0,'Liya & David','11114c0cc54c878610cab851b8341a3cd468ae93b470a249601ec677681c1391','2026-08-21 22:36:25'),(2,1,1,'Math 101 Class Group','098f8063a0592f300b7a1b5dc8e75364022dc7c392bb6039fb19bf0137a54650','2026-08-21 22:36:25'),(3,1,0,'','84aba32a1b0cff4ab63487bccf71bbb6a52fc9fcecac7c55f1b725a139f906e3','2026-08-21 23:21:49'),(4,1,0,'','test','2026-08-21 23:36:46'),(5,1,1,'tr','dd400531f2547282b27650b617ec48a569e29cf6372ef7e359099105aaf38990','2026-08-22 00:14:33'),(8,1,0,'','edc5f7bec5834baee0edf3fdff05e8267fee58431c720346ad5a2bc52ed47608','2026-08-22 01:28:09'),(9,2,0,'','47b452d21b153f36de85d7d150940703e378282de51d985f5b076849b0a82bdc','2026-08-24 11:06:31');
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
  `semester_id` int unsigned DEFAULT NULL,
  `progress` decimal(5,2) DEFAULT '0.00',
  `completed` tinyint(1) DEFAULT '0',
  `completed_at` datetime DEFAULT NULL,
  `enrolled_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_enroll` (`course_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `course_enrollments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_enrollments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_enrollments`
--

LOCK TABLES `course_enrollments` WRITE;
/*!40000 ALTER TABLE `course_enrollments` DISABLE KEYS */;
INSERT INTO `course_enrollments` VALUES (1,1,4,NULL,50.00,0,NULL,'2026-08-21 22:36:25',0),(2,2,4,NULL,0.00,0,NULL,'2026-08-21 22:36:25',0),(3,3,4,NULL,25.00,0,NULL,'2026-08-21 22:36:25',0),(40,4,18,NULL,65.00,0,NULL,'2026-08-24 12:23:58',1),(41,7,18,NULL,45.00,0,NULL,'2026-08-24 12:23:58',1),(42,8,18,NULL,30.00,0,NULL,'2026-08-24 12:23:58',1),(43,10,18,NULL,80.00,0,NULL,'2026-08-24 12:23:58',1),(44,12,19,NULL,55.00,0,NULL,'2026-08-24 12:23:58',1),(45,11,19,NULL,40.00,0,NULL,'2026-08-24 12:23:58',1),(46,9,19,NULL,35.00,0,NULL,'2026-08-24 12:23:58',1),(47,7,19,NULL,70.00,0,NULL,'2026-08-24 12:23:58',1);
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
-- Table structure for table `course_offerings`
--

DROP TABLE IF EXISTS `course_offerings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_offerings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL,
  `semester_id` int unsigned NOT NULL,
  `lecturer_id` int unsigned DEFAULT NULL,
  `max_students` int unsigned DEFAULT '40',
  `current_students` int unsigned DEFAULT '0',
  `room` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `schedule_json` json DEFAULT NULL,
  `status` enum('open','full','closed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `semester_id` (`semester_id`),
  KEY `lecturer_id` (`lecturer_id`),
  CONSTRAINT `course_offerings_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_offerings_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_offerings_ibfk_3` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_offerings`
--

LOCK TABLES `course_offerings` WRITE;
/*!40000 ALTER TABLE `course_offerings` DISABLE KEYS */;
INSERT INTO `course_offerings` VALUES (1,1,2,14,40,0,'Room 101',NULL,'open','2026-08-24 11:20:15',0),(2,2,2,14,40,0,'Room 102',NULL,'open','2026-08-24 11:20:15',0);
/*!40000 ALTER TABLE `course_offerings` ENABLE KEYS */;
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
  `credits` int unsigned DEFAULT '3',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `courses_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,1,3,1,'Mathematics 101','MATH101','Foundations of algebra, geometry and calculus for Grade 9 students.','','Grade 9','published',0.00,3,'2026-08-21 22:36:25',1),(2,1,3,2,'Physics Foundations','PHY101','Mechanics, motion and energy explained with real Ethiopian examples.','','Grade 9','published',0.00,3,'2026-08-21 22:36:25',1),(3,3,7,4,'Data Structures','CS201','Arrays, linked lists, stacks, queues, trees and graphs with C.','','1st Year','published',0.00,3,'2026-08-21 22:36:25',1),(4,3,14,NULL,'Introduction to Computer Science','CS101','Fundamentals of computing and programming','','Year 1','published',0.00,3,'2026-08-24 11:34:34',1),(5,3,14,NULL,'Discrete Mathematics','MATH201','Logic, sets, relations, and graph theory','','Year 1','published',0.00,3,'2026-08-24 11:34:34',1),(6,3,14,NULL,'Data Structures','CS201','Arrays, trees, graphs, and algorithms','','Year 2','published',0.00,4,'2026-08-24 11:34:34',1),(7,3,14,NULL,'Calculus II','MATH102','Integration, series, and multivariable calculus','','Year 1','published',0.00,3,'2026-08-24 11:34:34',1),(8,3,14,NULL,'English for Academic Purpose','ENG101','Academic writing and communication skills','','Year 1','published',0.00,2,'2026-08-24 11:34:34',1),(9,3,14,NULL,'Digital Logic Design','EE201','Boolean algebra, gates, and circuits','','Year 2','published',0.00,3,'2026-08-24 11:34:34',1),(10,3,14,NULL,'Operating Systems','CS301','Process management, memory, file systems','','Year 3','published',0.00,4,'2026-08-24 12:14:20',1),(11,3,14,NULL,'Database Systems','CS302','Relational databases, SQL, normalization','','Year 3','published',0.00,3,'2026-08-24 12:14:20',1),(12,3,14,NULL,'Software Engineering','SE301','SDLC, agile, testing, project management','','Year 3','published',0.00,3,'2026-08-24 12:14:20',1),(13,3,14,NULL,'Computer Architecture','EE301','Digital systems, processor design','','Year 3','published',0.00,3,'2026-08-24 12:14:20',1),(14,3,14,NULL,'Numerical Methods','MATH301','Computational methods for engineering','','Year 3','published',0.00,3,'2026-08-24 12:14:20',1);
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
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `idx_dept_faculty` (`faculty_id`),
  CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `departments_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,1,'Science','Dr. Bekele',NULL,'active',0),(2,1,'Languages','Mrs. Tigist',NULL,'active',0),(3,2,'Computer Science','Prof. Alem',NULL,'active',0),(4,2,'Engineering','Dr. Marta',NULL,'active',0),(5,3,'General','Mr. Dawit',NULL,'active',0),(6,2,'Computer Science','Prof. Alem',2,'active',0),(7,2,'Electrical Engineering','Dr. Marta',1,'active',0),(8,1,'Mathematics Department','',NULL,'active',1),(9,1,'Science Department','',NULL,'active',1),(10,1,'Languages Department','',NULL,'active',1),(11,1,'Mathematics Department','',NULL,'active',1),(12,1,'Science Department','',NULL,'active',1),(13,1,'Languages Department','',NULL,'active',1),(14,3,'Computer Science Department','',NULL,'active',1),(15,3,'Mathematics Department','',NULL,'active',1),(16,3,'Electrical Engineering Department','',NULL,'active',1);
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
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `exam_id` (`exam_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `exam_attempts_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_attempts_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_attempts`
--

LOCK TABLES `exam_attempts` WRITE;
/*!40000 ALTER TABLE `exam_attempts` DISABLE KEYS */;
INSERT INTO `exam_attempts` VALUES (1,1,4,'2026-07-20 09:00:00','2026-07-20 09:20:00',NULL,NULL,7.00,12.00,'graded',0),(17,19,18,'2026-09-28 09:00:00','2026-09-28 10:45:00',NULL,NULL,88.00,100.00,'graded',1),(18,20,18,'2026-09-25 10:00:00','2026-09-25 10:40:00',NULL,NULL,75.00,80.00,'graded',1),(19,22,18,'2026-09-20 08:05:00','2026-09-20 08:50:00',NULL,NULL,92.00,100.00,'graded',1),(20,21,19,'2026-10-02 14:05:00','2026-10-02 15:50:00',NULL,NULL,82.00,100.00,'graded',1),(21,20,19,'2026-09-25 10:02:00','2026-09-25 10:38:00',NULL,NULL,68.00,80.00,'graded',1),(22,23,19,'2026-10-05 10:03:00','2026-10-05 11:50:00',NULL,NULL,71.00,100.00,'graded',1);
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
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `exam_questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_questions`
--

LOCK TABLES `exam_questions` WRITE;
/*!40000 ALTER TABLE `exam_questions` DISABLE KEYS */;
INSERT INTO `exam_questions` VALUES (1,1,'mcq','Solve: 2x + 4 = 10. What is x?','[\"2\", \"3\", \"4\", \"5\"]','3',2.00,'','2x = 6, x = 3',1,0),(2,1,'mcq','What is the slope of y = 3x + 2?','[\"1\", \"2\", \"3\", \"6\"]','3',1.00,'','Slope is the coefficient of x',2,0),(3,1,'truefalse','The sum of interior angles of a triangle is 180 degrees.',NULL,'true',1.00,'','Always true for Euclidean triangles',3,0),(4,1,'fill','The formula for the area of a rectangle is A = _ x width.',NULL,'length',1.00,'','A = l x w',4,0),(5,1,'essay','Explain the difference between a linear and a quadratic equation with one example each.',NULL,NULL,5.00,'','Linear: degree 1; quadratic: degree 2',5,0),(6,1,'mcq','If F(0)=0, F(1)=1, F(n)=F(n-1)+F(n-2), what is F(4)?','[\"1\", \"2\", \"3\", \"5\"]','3',2.00,'','Fibonacci: 0,1,1,2,3,5 -> F(4)=3',6,0),(7,2,'mcq','In C, what is the index of the first element of an array?','[\"0\", \"1\", \"-1\", \"None\"]','0',1.00,'','C arrays are zero-indexed',1,0),(8,2,'truefalse','An array stores elements of different types.',NULL,'false',1.00,'','Arrays hold one type',2,0),(9,3,'truefalse','A triangle can have two right angles.',NULL,'false',1.00,'','Sum would exceed 180',1,0),(10,3,'mcq','Recursion is when a function...','[\"Loops forever\", \"Calls itself\", \"Returns a string\", \"Uses no memory\"]','Calls itself',2.00,'','Recursion = self-calling function',2,0),(15,19,'mcq','What is a process?','[\"A program in execution\", \"A file\", \"A thread\", \"A CPU\"]','A program in execution',5.00,'',NULL,0,1),(16,19,'mcq','Which scheduling algorithm uses time quantum?','[\"FCFS\", \"SJF\", \"Round Robin\", \"Priority\"]','Round Robin',5.00,'',NULL,0,1),(17,19,'truefalse','Virtual memory allows programs to use more memory than physically available.','[\"True\", \"False\"]','True',3.00,'',NULL,0,1),(18,19,'mcq','What is a deadlock in OS?','[\"A process that never terminates\", \"A set of processes blocked waiting for events that will never occur\", \"A single process crash\", \"A memory leak\"]','A set of processes blocked waiting for events that will never occur',5.00,'',NULL,0,1),(19,20,'mcq','What does ACID stand for?','[\"Atomicity, Consistency, Isolation, Durability\", \"Association, Connection, Integration, Data\", \"Automated, Centralized, Integrated, Distributed\", \"Access, Control, Identity, Database\"]','Atomicity, Consistency, Isolation, Durability',5.00,'',NULL,0,1),(20,20,'truefalse','Primary key uniquely identifies each record in a table.','[\"True\", \"False\"]','True',3.00,'',NULL,0,1),(21,21,'mcq','What is the main goal of Agile methodology?','[\"Documentation\", \"Working software\", \"Complexity\", \"Structure\"]','Working software',5.00,'',NULL,0,1),(22,21,'truefalse','Scrum uses sprints of 1-4 weeks.','[\"True\", \"False\"]','True',3.00,'',NULL,0,1),(23,22,'mcq','What is the derivative of sin(x)?','[\"cos(x)\", \"-cos(x)\", \"sin(x)\", \"-sin(x)\"]','cos(x)',5.00,'',NULL,0,1),(24,23,'mcq','A full adder has how many inputs?','[\"2\", \"3\", \"4\", \"5\"]','3',5.00,'',NULL,0,1);
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
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exams_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exams`
--

LOCK TABLES `exams` WRITE;
/*!40000 ALTER TABLE `exams` DISABLE KEYS */;
INSERT INTO `exams` VALUES (1,1,3,'Algebra Midterm','Covers chapters 1-2: algebra basics and linear equations.','midterm',30,'2026-08-22 01:36:25','2026-08-29 01:36:25',50.00,1,0,1,'published',NULL,'2026-08-21 22:36:25',0),(2,3,7,'Data Structures Quiz 1','Quick quiz on arrays and pointers.','quiz',15,'2026-08-22 01:36:25','2026-08-25 01:36:25',50.00,1,0,1,'published',NULL,'2026-08-21 22:36:25',0),(3,1,3,'Practice: True/False','Practice session for exam readiness.','practice',10,'2026-08-22 01:36:25','2026-08-24 01:36:25',50.00,1,0,1,'published',NULL,'2026-08-21 22:36:25',0),(19,10,14,'OS Midterm Exam',NULL,'quiz',120,'2026-10-01 09:00:00','2026-10-01 11:00:00',50.00,1,0,1,'published',NULL,'2026-08-24 12:14:39',1),(20,11,14,'Database Systems Quiz 1',NULL,'quiz',45,'2026-09-25 10:00:00','2026-09-25 10:45:00',40.00,1,0,1,'published',NULL,'2026-08-24 12:14:39',1),(21,12,14,'Software Engineering Midterm',NULL,'quiz',120,'2026-10-02 14:00:00','2026-10-02 16:00:00',50.00,1,0,1,'published',NULL,'2026-08-24 12:14:39',1),(22,7,14,'Calculus II Quiz',NULL,'quiz',60,'2026-09-20 08:00:00','2026-09-20 09:00:00',50.00,1,0,1,'published',NULL,'2026-08-24 12:14:39',1),(23,9,14,'Digital Logic Design Midterm',NULL,'quiz',120,'2026-10-05 10:00:00','2026-10-05 12:00:00',50.00,1,0,1,'published',NULL,'2026-08-24 12:14:39',1);
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
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dean_id` int unsigned DEFAULT NULL,
  `vice_dean_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_fac_school` (`school_id`),
  KEY `dean_id` (`dean_id`),
  KEY `vice_dean_id` (`vice_dean_id`),
  CONSTRAINT `faculties_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `faculties_ibfk_2` FOREIGN KEY (`dean_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `faculties_ibfk_3` FOREIGN KEY (`vice_dean_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faculties`
--

LOCK TABLES `faculties` WRITE;
/*!40000 ALTER TABLE `faculties` DISABLE KEYS */;
INSERT INTO `faculties` VALUES (1,2,'Faculty of Engineering','active','ENG',NULL,NULL,'2026-08-24 11:02:38',0),(2,2,'Faculty of Computing','active','FCS',NULL,NULL,'2026-08-24 11:02:38',0),(3,3,'Faculty of Computing and Informatics','active',NULL,NULL,NULL,'2026-08-24 12:13:26',1),(4,3,'Faculty of Engineering','active',NULL,NULL,NULL,'2026-08-24 12:13:26',1),(5,3,'Faculty of Natural Sciences','active',NULL,NULL,NULL,'2026-08-24 12:13:26',1);
/*!40000 ALTER TABLE `faculties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fee_structures`
--

DROP TABLE IF EXISTS `fee_structures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fee_structures` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fee_type` enum('per_credit','fixed','per_course') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `applies_to` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT 'all',
  `semester_id` int unsigned DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `semester_id` (`semester_id`),
  CONSTRAINT `fee_structures_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_structures_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fee_structures`
--

LOCK TABLES `fee_structures` WRITE;
/*!40000 ALTER TABLE `fee_structures` DISABLE KEYS */;
INSERT INTO `fee_structures` VALUES (1,2,'Tuition Fee',5000.00,'fixed','all',NULL,'active',0),(2,2,'Registration Fee',200.00,'fixed','all',NULL,'active',0),(3,2,'Lab Fee',300.00,'fixed','all',NULL,'active',0),(4,3,'Tuition Fee (per credit)',150.00,'per_credit','all',NULL,'active',1),(5,3,'Lab Fee',500.00,'fixed','CS,SE,EE',NULL,'active',1),(6,3,'Library Fee',100.00,'fixed','all',NULL,'active',1);
/*!40000 ALTER TABLE `fee_structures` ENABLE KEYS */;
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
  `school_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_encrypted` tinyint(1) DEFAULT '0',
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `size` int DEFAULT '0',
  `version` int DEFAULT '1',
  `parent_id` int unsigned DEFAULT NULL,
  `is_folder` tinyint(1) DEFAULT '0',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `files_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `files_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
INSERT INTO `files` VALUES (1,1,2,'My Documents','My Documents','',NULL,0,'folder',0,1,NULL,1,NULL,'2026-08-21 22:36:25',0),(2,1,3,'Welcome Presentation','Welcome_Presentation.pdf','/uploads/demo/welcome.pdf',NULL,0,'application/pdf',2048000,1,NULL,0,NULL,'2026-08-24 11:56:57',1),(3,1,3,'Course Syllabus','Course_Syllabus.pdf','/uploads/demo/syllabus.pdf',NULL,0,'application/pdf',512000,1,NULL,0,NULL,'2026-08-24 11:56:57',1),(4,3,14,'CS101 Syllabus','CS101_Syllabus.pdf','/uploads/demo/cs101.pdf',NULL,0,'application/pdf',1024000,1,NULL,0,NULL,'2026-08-24 11:56:57',1),(5,3,14,'Lecture Slides Week 1','Lecture_Week1.pdf','/uploads/demo/week1.pdf',NULL,0,'application/pdf',3072000,1,NULL,0,NULL,'2026-08-24 11:56:57',1),(6,NULL,1,'hey','','files/1/hey',NULL,0,'',0,1,NULL,1,NULL,'2026-08-31 03:14:59',0);
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
  `is_demo` tinyint(1) DEFAULT '0',
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
INSERT INTO `forum_posts` VALUES (1,1,3,'Try the AC method: multiply a and c, find factors that sum to b.',1,'2026-08-21 22:36:25',0),(2,1,4,'That helped a lot, thank you!',0,'2026-08-21 22:36:25',0);
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
  `is_demo` tinyint(1) DEFAULT '0',
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
INSERT INTO `forum_topics` VALUES (1,1,4,'How do I solve quadratic equations?','I keep getting confused with factoring. Any tips?',0,0,'2026-08-21 22:36:25',0),(2,1,3,'Office hours this week','I will be available Wednesday 14:00-16:00.',0,0,'2026-08-21 22:36:25',0);
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
  `is_demo` tinyint(1) DEFAULT '0',
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
INSERT INTO `goals` VALUES (1,4,'Finish Mathematics 101',100,50,'lessons','2026-09-21',0,0);
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
-- Table structure for table `integrity_ledger`
--

DROP TABLE IF EXISTS `integrity_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `integrity_ledger` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prev_hash` char(64) NOT NULL DEFAULT '0',
  `record_hash` char(64) NOT NULL,
  `hmac_hash` char(64) DEFAULT NULL,
  `table_name` varchar(64) NOT NULL,
  `record_id` int unsigned NOT NULL,
  `action` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `data_json` json DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_table_record` (`table_name`,`record_id`),
  KEY `idx_hash` (`record_hash`),
  KEY `idx_prev_hash` (`prev_hash`),
  KEY `idx_recorded` (`recorded_at`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `integrity_ledger`
--

LOCK TABLES `integrity_ledger` WRITE;
/*!40000 ALTER TABLE `integrity_ledger` DISABLE KEYS */;
INSERT INTO `integrity_ledger` VALUES (1,'0','10cb6fd7078d9eb11a9731f126f9f53536c9322b63439da00a5aa9c7c08b8e70','20254437bd775f45426b72fae752374180283d901ce3a9ce1f577cce176d2c89','users',101,'INSERT','{\"role\": \"student\", \"email\": \"student@edunex.local\"}',NULL,'127.0.0.1','2026-08-29 14:09:05'),(2,'10cb6fd7078d9eb11a9731f126f9f53536c9322b63439da00a5aa9c7c08b8e70','6d795838aaaa097e027408d1211d9d03122f707a5b202eea7c09e712f8d477a9','365d70f2998445e82ffc27e006f394b0600b715824331ea261a9ce10124d46aa','users',102,'INSERT','{\"role\": \"teacher\", \"email\": \"teacher@edunex.local\"}',NULL,'127.0.0.1','2026-08-29 14:09:05'),(3,'6d795838aaaa097e027408d1211d9d03122f707a5b202eea7c09e712f8d477a9','97824f5af087d565a90f7e12fbd20b236a08b168c72bc95cd4918699e162587a','aaa7420261086c5e6124abecbefc395994ac07e24ee35c86ec056ef0f8acd23c','courses',1,'INSERT','{\"name\": \"Math 101\", \"teacher_id\": 102}',NULL,'127.0.0.1','2026-08-29 14:09:05'),(4,'97824f5af087d565a90f7e12fbd20b236a08b168c72bc95cd4918699e162587a','ee84a7ec6af58c5c1243760c8357480398642ff0ee271171b9489d8d7854298b','cb893e0c1f81fdee3645549b3bef0abb7d5a98d6bf76b39ccfaa8a25b8256b62','grades',1,'INSERT','{\"score\": 95, \"course_id\": 1, \"student_id\": 101}',NULL,'127.0.0.1','2026-08-29 14:09:05'),(5,'ee84a7ec6af58c5c1243760c8357480398642ff0ee271171b9489d8d7854298b','5bca89f887de991fb9b6da1c1e39fbebf5f4ebbff24e1422b3802d0883ba6e86','908c4165c9482ac8d9635ec7a822e503b320419286d700cf7ec4da9dc19c4d16','grades',1,'UPDATE','{\"note\": \"Bonus points\", \"score\": 98}',NULL,'127.0.0.1','2026-08-29 14:09:05'),(6,'5bca89f887de991fb9b6da1c1e39fbebf5f4ebbff24e1422b3802d0883ba6e86','c4197ef0d65bd999ed0e7f93911a94b55b1b4f5c97c69502493ffb2a56d60b4e','c77e0496bea8c82dbb3bee33f72826afd0a7a04f0ffa7855b2db662fc7e73d8b','attendance',1,'INSERT','{\"date\": \"2026-08-29\", \"status\": \"present\", \"student_id\": 101}',NULL,'127.0.0.1','2026-08-29 14:09:05'),(7,'c4197ef0d65bd999ed0e7f93911a94b55b1b4f5c97c69502493ffb2a56d60b4e','d73e04cb0e82f9cf5c42cb670e84ef731321acf6f7dd95c52e6c1627f99dc76a','588fae97c6c4e651f52e2655f55b057d69de30061013ca67fc09dceeef88c32a','submissions',1,'INSERT','{\"course_id\": 1, \"assignment\": \"Homework 1\", \"student_id\": 101}',NULL,'127.0.0.1','2026-08-29 14:09:05'),(8,'d73e04cb0e82f9cf5c42cb670e84ef731321acf6f7dd95c52e6c1627f99dc76a','ea0963751c3a77dce6af3910b0af8f8524cac7703a31a57ce8da7d9cdbe31fa7','8913663694eed6bc78994f188a151a7d3b3c544c6c72da547b35ab663930070e','users',103,'INSERT','{\"role\": \"parent\", \"email\": \"parent@edunex.local\"}',NULL,'127.0.0.1','2026-08-29 14:09:05'),(9,'ea0963751c3a77dce6af3910b0af8f8524cac7703a31a57ce8da7d9cdbe31fa7','90d83bca9a0867bbf2bcd353f58e764d54d2d978f662a56f5221786d43974010','09a37358521597be24bf0d9d1113508468a99cf350ad114b70a7b52894972644','grades',2,'INSERT','{\"score\": 88, \"course_id\": 1, \"student_id\": 101}',NULL,'127.0.0.1','2026-08-29 14:09:05'),(10,'90d83bca9a0867bbf2bcd353f58e764d54d2d978f662a56f5221786d43974010','b1bf519ef0837f8d19c88f886c0d3fca88e214a5f59906f7981c513683d52ba6','0d1ea8191a46281f9d4f0761fc77c07fe67c715c2a518633ea93548d1e56d65d','certificates',1,'INSERT','{\"type\": \"completion\", \"course_id\": 1, \"student_id\": 101}',NULL,'127.0.0.1','2026-08-29 14:09:05');
/*!40000 ALTER TABLE `integrity_ledger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int unsigned NOT NULL,
  `fee_structure_id` int unsigned DEFAULT NULL,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `fee_structure_id` (`fee_structure_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int unsigned NOT NULL,
  `semester_id` int unsigned NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','partial','paid','overdue') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `semester_id` (`semester_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (4,18,2,4800.00,4800.00,'paid','2025-06-30',NULL,'2026-08-24 12:16:31',1),(6,19,2,4800.00,4800.00,'paid','2025-06-30',NULL,'2026-08-24 12:16:31',1);
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `it_fix_logs`
--

DROP TABLE IF EXISTS `it_fix_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `it_fix_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int unsigned NOT NULL,
  `it_admin_id` int unsigned NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `it_admin_id` (`it_admin_id`),
  CONSTRAINT `it_fix_logs_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `it_fix_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `it_fix_logs_ibfk_2` FOREIGN KEY (`it_admin_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `it_fix_logs`
--

LOCK TABLES `it_fix_logs` WRITE;
/*!40000 ALTER TABLE `it_fix_logs` DISABLE KEYS */;
INSERT INTO `it_fix_logs` VALUES (1,5,1,'frozen_by_user','ddhd',NULL,'2026-08-31 07:09:48'),(2,5,1,'unfrozen_by_user','Ticket unfrozen by user',NULL,'2026-08-31 07:09:56');
/*!40000 ALTER TABLE `it_fix_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `it_fix_scopes`
--

DROP TABLE IF EXISTS `it_fix_scopes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `it_fix_scopes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int unsigned NOT NULL,
  `scope_route` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scope_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  CONSTRAINT `it_fix_scopes_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `it_fix_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `it_fix_scopes`
--

LOCK TABLES `it_fix_scopes` WRITE;
/*!40000 ALTER TABLE `it_fix_scopes` DISABLE KEYS */;
INSERT INTO `it_fix_scopes` VALUES (1,1,'admin/dashboard','Admin Dashboard'),(2,2,'admin/dashboard','Admin Dashboard'),(3,3,'admin/dashboard','Admin Dashboard'),(4,4,'admin/dashboard','Admin Dashboard'),(5,5,'admin/courses','All Courses');
/*!40000 ALTER TABLE `it_fix_scopes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `it_fix_tickets`
--

DROP TABLE IF EXISTS `it_fix_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `it_fix_tickets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned DEFAULT NULL,
  `requested_by` int unsigned NOT NULL,
  `it_admin_id` int unsigned DEFAULT NULL,
  `admin_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_route` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci,
  `user_response` text COLLATE utf8mb4_unicode_ci,
  `api_token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('open','in_progress','resolved','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `priority` enum('normal','high','critical') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `frozen` tinyint(1) DEFAULT '0',
  `frozen_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `claimed_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `escalated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_api_token` (`api_token`),
  KEY `school_id` (`school_id`),
  KEY `requested_by` (`requested_by`),
  KEY `it_admin_id` (`it_admin_id`),
  CONSTRAINT `it_fix_tickets_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `it_fix_tickets_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  CONSTRAINT `it_fix_tickets_ibfk_3` FOREIGN KEY (`it_admin_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `it_fix_tickets`
--

LOCK TABLES `it_fix_tickets` WRITE;
/*!40000 ALTER TABLE `it_fix_tickets` DISABLE KEYS */;
INSERT INTO `it_fix_tickets` VALUES (1,NULL,1,NULL,NULL,'admin/dashboard','Admin Dashboard','page dashboard',NULL,'6b913c1a8cb3dc72417035d7af7cb8d988b37314262e02475dcd78b2e83733c0','open','normal',0,NULL,'2026-08-31 05:57:25',NULL,NULL,NULL,NULL),(2,NULL,1,NULL,NULL,'admin/dashboard','Admin Dashboard','page dashboard',NULL,'5712fc2381fe8e97b62f593f12c090bd4652f2bedbfa2f2e6843f69ac48223b0','open','normal',0,NULL,'2026-08-31 05:57:32',NULL,NULL,NULL,NULL),(3,NULL,1,NULL,NULL,'admin/dashboard','Admin Dashboard','hfghfghfh',NULL,'7e9302b4dfee6ef0187a52377b5ae2a84a5c7a56aa0cc1b7c6200f361eb884d7','open','normal',0,NULL,'2026-08-31 05:58:19',NULL,NULL,NULL,NULL),(4,NULL,1,NULL,NULL,'admin/dashboard','Admin Dashboard','hfghfghfh',NULL,'75bcec492ac61372d399611ae1933d255e8bad351aa678127a99e5c1e94f4e81','open','normal',0,NULL,'2026-08-31 06:02:21',NULL,NULL,NULL,NULL),(5,NULL,1,NULL,NULL,'admin/courses','All Courses','hjhjhjh',NULL,'d8368f7ec5215d942b7f04838fd97d1ce0812a7dd71ee32bfcfa1b0acf56498a','open','normal',0,NULL,'2026-08-31 06:02:36',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `it_fix_tickets` ENABLE KEYS */;
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
-- Table structure for table `ledger_keys`
--

DROP TABLE IF EXISTS `ledger_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ledger_keys` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `key_name` varchar(64) NOT NULL,
  `key_value` char(64) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `rotated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ledger_keys`
--

LOCK TABLES `ledger_keys` WRITE;
/*!40000 ALTER TABLE `ledger_keys` DISABLE KEYS */;
INSERT INTO `ledger_keys` VALUES (1,'primary','a77474aa5524d4d9a70c63388af59484a6657914112e333af6f465f56c4da424',1,'2026-08-29 14:09:05',NULL);
/*!40000 ALTER TABLE `ledger_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ledger_verifications`
--

DROP TABLE IF EXISTS `ledger_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ledger_verifications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `verified_by` int unsigned DEFAULT NULL,
  `total_records` int unsigned DEFAULT '0',
  `broken_links` int unsigned DEFAULT '0',
  `first_hash` char(64) DEFAULT NULL,
  `last_hash` char(64) DEFAULT NULL,
  `verified_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `details` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ledger_verifications`
--

LOCK TABLES `ledger_verifications` WRITE;
/*!40000 ALTER TABLE `ledger_verifications` DISABLE KEYS */;
INSERT INTO `ledger_verifications` VALUES (1,1,10,1,'10cb6fd7078d9eb11a9731f126f9f53536c9322b63439da00a5aa9c7c08b8e70','b1bf519ef0837f8d19c88f886c0d3fca88e214a5f59906f7981c513683d52ba6','2026-08-29 14:23:44',NULL);
/*!40000 ALTER TABLE `ledger_verifications` ENABLE KEYS */;
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
  `is_demo` tinyint(1) DEFAULT '0',
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
INSERT INTO `lessons` VALUES (1,1,1,'Introduction to Algebra','notes','<p>Algebra is the study of mathematical symbols and the rules for manipulating them. It is a unifying thread of almost all of mathematics.</p><p><b>Key concepts:</b> variables, expressions, equations, polynomials.</p><p>A linear equation has the form ax + b = 0. To solve, isolate x: x = -b/a.</p><p><b>Example:</b> 2x + 4 = 10 &rarr; 2x = 6 &rarr; x = 3.</p><p>Recursion in programming is when a function calls itself. In algebra, think of it as defining a value in terms of previous values, like the Fibonacci sequence: F(n) = F(n-1) + F(n-2).</p>','','',25,1,0),(2,1,1,'Linear Equations','video','<p>Watch the video and practice solving linear equations step by step.</p>','','',18,2,0),(3,2,1,'Triangles and Angles','notes','<p>Triangles are three-sided polygons. The sum of interior angles is always 180 degrees.</p><p>Types: equilateral, isosceles, scalene, right-angled.</p>','','',20,1,0),(4,3,2,'Distance and Velocity','video','<p>Velocity is displacement over time. Distance = speed &times; time.</p>','','',15,1,0),(5,4,3,'Arrays in C','notes','<p>An array is a contiguous block of memory holding elements of the same type. Indexing starts at 0 in C.</p>','','',30,1,0),(6,5,3,'Binary Trees','notes','<p>A binary tree is a hierarchical structure where each node has at most two children. Trees are recursive data structures.</p>','','',35,1,0);
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
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `idx_library_search` (`title`,`category`),
  CONSTRAINT `library_items_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `library_items`
--

LOCK TABLES `library_items` WRITE;
/*!40000 ALTER TABLE `library_items` DISABLE KEYS */;
INSERT INTO `library_items` VALUES (1,1,'Algebra Workbook 2026','book','MoE Ethiopia','Mathematics','Official workbook for Grade 9 algebra.','','',120,'published','2026-08-21 22:36:25',0),(2,1,'Physics Notes Chapter 1','notes','Mr. David Alemu','Physics','Handwritten-style notes on motion.','','',45,'published','2026-08-21 22:36:25',0),(3,1,'2019 National Exam Math','past_exam','MoE','Exams','Past national exam paper for practice.','','',230,'published','2026-08-21 22:36:25',0),(4,1,'Introduction to C Programming','book','K&R','Programming','Classic C programming reference.','','',89,'published','2026-08-21 22:36:25',0),(5,1,'Data Structures Slides','slides','Prof. Kebede','Computer Science','Lecture slides on trees and graphs.','','',60,'published','2026-08-21 22:36:25',0),(6,1,'Amharic-English Dictionary','book','Academy','Languages','Bilingual dictionary for students.','','',150,'published','2026-08-21 22:36:25',0),(7,1,'Ethiopian History Timeline','notes','Mrs. Tigist','History','Quick timeline of key events.','','',34,'published','2026-08-21 22:36:25',0),(8,1,'Science Fair Video','video','BDU Media','Science','Documentary on Ethiopian scientists.','','',76,'published','2026-08-21 22:36:25',0),(9,1,'Introduction to Algebra','book','John Smith','Mathematics','A comprehensive guide to algebra for beginners','','',0,'published','2026-08-24 11:55:47',1),(10,1,'Ethiopian History','book','Bekele Gebre','History','Complete history of Ethiopia from ancient times','','',0,'published','2026-08-24 11:55:47',1),(11,1,'Science Experiments Video','video','Dr. Alem','Science','Hands-on experiments for middle school students','','',0,'published','2026-08-24 11:55:47',1),(12,1,'Introduction to Algebra','book','John Smith','Mathematics','A comprehensive guide to algebra for beginners','','',0,'published','2026-08-24 11:56:24',1),(13,1,'Ethiopian History','book','Bekele Gebre','History','Complete history of Ethiopia from ancient times','','',0,'published','2026-08-24 11:56:24',1),(14,1,'Science Experiments Video','video','Dr. Alem','Science','Hands-on experiments for middle school students','','',0,'published','2026-08-24 11:56:24',1),(15,1,'English Grammar Guide','notes','Sarah Johnson','Languages','Essential grammar rules for academic writing','','',0,'published','2026-08-24 11:56:24',1),(17,3,'Data Structures and Algorithms','book','Thomas Cormen','Computer Science','Classic textbook on algorithms','','',0,'published','2026-08-24 11:56:24',1),(18,3,'Linear Algebra','notes','Gilbert Strang','Mathematics','MIT OpenCourseWare lecture notes','','',0,'published','2026-08-24 11:56:24',1),(19,3,'Introduction to Algorithms','book','Thomas Cormen','','Comprehensive guide to algorithms and data structures','','',0,'published','2026-08-24 12:17:24',1),(20,3,'Database System Concepts','book','Abraham Silberschatz','','Fundamentals of database management systems','','',0,'published','2026-08-24 12:17:24',1),(21,3,'Operating System Concepts','book','Abraham Silberschatz','','Modern operating systems principles and design','','',0,'published','2026-08-24 12:17:24',1),(22,3,'Software Engineering: A Practitioner\'s Approach','book','Roger Pressman','','Software development lifecycle and best practices','','',0,'published','2026-08-24 12:17:24',1),(23,3,'Discrete Mathematics and Its Applications','book','Kenneth Rosen','','Mathematical foundations for computer science','','',0,'published','2026-08-24 12:17:24',1),(24,3,'CS101 Lecture Notes','notes','Lecturer','','Complete lecture notes for Introduction to CS','','',0,'published','2026-08-24 12:17:24',1),(25,3,'Database Systems Past Exam 2025','past_exam','BDU','','Final exam with solutions','','',0,'published','2026-08-24 12:17:24',1),(26,3,'Calculus II Study Guide','tutorial','Math Department','','Step-by-step solutions for common calculus problems','','',0,'published','2026-08-24 12:17:24',1),(27,3,'Digital Logic Design Slides','slides','EE Department','','Complete slide deck for digital logic course','','',0,'published','2026-08-24 12:17:24',1),(28,3,'Ethiopian Software Engineering Conference 2025','paper','Proceedings','','Collection of research papers from the conference','','',0,'published','2026-08-24 12:17:24',1);
/*!40000 ALTER TABLE `library_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `license_tier_features`
--

DROP TABLE IF EXISTS `license_tier_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `license_tier_features` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tier` enum('trial','standard','premium','enterprise') NOT NULL,
  `module_key` varchar(60) NOT NULL,
  `max_seats` int DEFAULT '0' COMMENT '0=unlimited',
  `max_schools` int DEFAULT '0' COMMENT '0=unlimited',
  `features` json DEFAULT NULL COMMENT 'Extra feature flags per tier',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tier_module` (`tier`,`module_key`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `license_tier_features`
--

LOCK TABLES `license_tier_features` WRITE;
/*!40000 ALTER TABLE `license_tier_features` DISABLE KEYS */;
INSERT INTO `license_tier_features` VALUES (1,'trial','core',50,1,NULL),(2,'trial','auth',50,1,NULL),(3,'trial','user-management',50,1,NULL),(4,'trial','security',50,1,NULL),(5,'trial','messaging',50,1,NULL),(6,'trial','notifications',50,1,NULL),(7,'trial','backup',50,1,NULL),(8,'trial','api',50,1,NULL),(9,'trial','analytics',50,1,NULL),(10,'trial','audit-trail',50,1,NULL),(11,'trial','attendance',50,1,NULL),(12,'trial','grading',50,1,NULL),(13,'trial','calendar',50,1,NULL),(14,'trial','timetable',50,1,NULL),(15,'trial','reports',50,1,NULL),(16,'standard','core',200,5,NULL),(17,'standard','auth',200,5,NULL),(18,'standard','user-management',200,5,NULL),(19,'standard','security',200,5,NULL),(20,'standard','messaging',200,5,NULL),(21,'standard','notifications',200,5,NULL),(22,'standard','backup',200,5,NULL),(23,'standard','api',200,5,NULL),(24,'standard','analytics',200,5,NULL),(25,'standard','audit-trail',200,5,NULL),(26,'standard','attendance',200,5,NULL),(27,'standard','grading',200,5,NULL),(28,'standard','calendar',200,5,NULL),(29,'standard','timetable',200,5,NULL),(30,'standard','reports',200,5,NULL),(31,'standard','exams',200,5,NULL),(32,'standard','assignments',200,5,NULL),(33,'standard','library',200,5,NULL),(34,'standard','transcripts',200,5,NULL),(35,'standard','finance',200,5,NULL),(36,'standard','id-cards',200,5,NULL),(37,'standard','certificates',200,5,NULL),(38,'standard','course-registration',200,5,NULL),(39,'premium','core',1000,25,NULL),(40,'premium','auth',1000,25,NULL),(41,'premium','user-management',1000,25,NULL),(42,'premium','security',1000,25,NULL),(43,'premium','messaging',1000,25,NULL),(44,'premium','notifications',1000,25,NULL),(45,'premium','backup',1000,25,NULL),(46,'premium','api',1000,25,NULL),(47,'premium','analytics',1000,25,NULL),(48,'premium','audit-trail',1000,25,NULL),(49,'premium','attendance',1000,25,NULL),(50,'premium','grading',1000,25,NULL),(51,'premium','calendar',1000,25,NULL),(52,'premium','timetable',1000,25,NULL),(53,'premium','reports',1000,25,NULL),(54,'premium','exams',1000,25,NULL),(55,'premium','assignments',1000,25,NULL),(56,'premium','library',1000,25,NULL),(57,'premium','transcripts',1000,25,NULL),(58,'premium','finance',1000,25,NULL),(59,'premium','id-cards',1000,25,NULL),(60,'premium','certificates',1000,25,NULL),(61,'premium','course-registration',1000,25,NULL),(62,'premium','ai-tutor',1000,25,NULL),(63,'premium','ai-assistant',1000,25,NULL),(64,'premium','clearance',1000,25,NULL),(65,'premium','thesis',1000,25,NULL),(66,'premium','sms',1000,25,NULL),(67,'premium','email-bulk',1000,25,NULL),(68,'premium','printing',1000,25,NULL),(69,'enterprise','core',0,0,NULL),(70,'enterprise','auth',0,0,NULL),(71,'enterprise','user-management',0,0,NULL),(72,'enterprise','security',0,0,NULL),(73,'enterprise','messaging',0,0,NULL),(74,'enterprise','notifications',0,0,NULL),(75,'enterprise','backup',0,0,NULL),(76,'enterprise','api',0,0,NULL),(77,'enterprise','analytics',0,0,NULL),(78,'enterprise','audit-trail',0,0,NULL),(79,'enterprise','attendance',0,0,NULL),(80,'enterprise','grading',0,0,NULL),(81,'enterprise','calendar',0,0,NULL),(82,'enterprise','timetable',0,0,NULL),(83,'enterprise','reports',0,0,NULL),(84,'enterprise','exams',0,0,NULL),(85,'enterprise','assignments',0,0,NULL),(86,'enterprise','library',0,0,NULL),(87,'enterprise','transcripts',0,0,NULL),(88,'enterprise','finance',0,0,NULL),(89,'enterprise','id-cards',0,0,NULL),(90,'enterprise','certificates',0,0,NULL),(91,'enterprise','course-registration',0,0,NULL),(92,'enterprise','ai-tutor',0,0,NULL),(93,'enterprise','ai-assistant',0,0,NULL),(94,'enterprise','clearance',0,0,NULL),(95,'enterprise','thesis',0,0,NULL),(96,'enterprise','sms',0,0,NULL),(97,'enterprise','email-bulk',0,0,NULL),(98,'enterprise','printing',0,0,NULL),(99,'enterprise','data-import',0,0,NULL),(100,'enterprise','webhooks',0,0,NULL),(101,'enterprise','kindergarten',0,0,NULL),(102,'enterprise','pre-elementary',0,0,NULL),(103,'enterprise','elementary',0,0,NULL),(104,'enterprise','highschool',0,0,NULL);
/*!40000 ALTER TABLE `license_tier_features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `licenses`
--

DROP TABLE IF EXISTS `licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `licenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `school_id` int DEFAULT NULL,
  `license_key` varchar(60) NOT NULL,
  `institution` varchar(200) DEFAULT NULL,
  `type` enum('trial','standard','premium','enterprise') DEFAULT 'standard',
  `seats` int DEFAULT '0',
  `seats_used` int DEFAULT '0',
  `issued_at` date DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `status` enum('active','suspended','expired','revoked') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_key` (`license_key`),
  KEY `idx_school` (`school_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `licenses`
--

LOCK TABLES `licenses` WRITE;
/*!40000 ALTER TABLE `licenses` DISABLE KEYS */;
INSERT INTO `licenses` VALUES (1,1,'EDX-PE2Q4-CYQ8R-6GBN5-4LCUB','Asas','premium',10,0,'2026-08-29',NULL,'active','2026-08-29 12:43:21','2026-08-29 12:43:21');
/*!40000 ALTER TABLE `licenses` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=293 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_history`
--

LOCK TABLES `login_history` WRITE;
/*!40000 ALTER TABLE `login_history` DISABLE KEYS */;
INSERT INTO `login_history` VALUES (1,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:37:03'),(2,2,'failed','::1','','2026-08-21 22:42:20'),(3,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 22:42:28'),(4,1,'success','::1','curl/8.20.0','2026-08-21 22:52:56'),(5,2,'success','::1','curl/8.20.0','2026-08-21 22:53:24'),(6,2,'success','::1','curl/8.20.0','2026-08-21 22:53:37'),(7,8,'success','::1','curl/8.20.0','2026-08-21 22:53:50'),(8,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:02:29'),(9,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:06'),(10,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:03:26'),(11,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:04:06'),(12,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-21 23:04:29'),(13,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:10:33'),(14,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:15:41'),(15,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:16:27'),(16,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:45:46'),(17,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 00:46:24'),(18,4,'success','::1','curl/8.20.0','2026-08-22 00:56:26'),(19,4,'success','::1','curl/8.20.0','2026-08-22 01:10:39'),(20,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-22 01:29:53'),(21,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:22:54'),(22,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:23:49'),(23,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:27:53'),(24,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:46:32'),(25,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 05:47:16'),(26,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:46:10'),(27,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:51:26'),(28,1,'success','::1','curl/8.20.0','2026-08-23 06:52:18'),(29,1,'success','::1','curl/8.20.0','2026-08-23 06:53:22'),(30,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 06:55:18'),(31,1,'success','::1','curl/8.20.0','2026-08-23 06:56:22'),(32,1,'success','::1','curl/8.20.0','2026-08-23 06:57:14'),(33,1,'success','::1','curl/8.20.0','2026-08-23 06:57:27'),(34,1,'success','::1','curl/8.20.0','2026-08-23 06:57:51'),(35,1,'success','::1','curl/8.20.0','2026-08-23 07:01:21'),(36,1,'success','::1','curl/8.20.0','2026-08-23 07:06:31'),(37,4,'success','::1','curl/8.20.0','2026-08-23 07:06:31'),(38,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:08:52'),(39,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:09:05'),(40,1,'success','::1','curl/8.20.0','2026-08-23 07:10:55'),(41,2,'success','::1','curl/8.20.0','2026-08-23 07:12:34'),(42,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 07:15:50'),(43,2,'success','::1','curl/8.20.0','2026-08-23 07:20:40'),(44,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 09:16:47'),(45,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 09:18:01'),(46,8,'success','::1','curl/8.20.0','2026-08-23 09:23:12'),(47,8,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 09:24:29'),(48,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 09:25:48'),(49,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-23 10:51:13'),(50,19,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 11:04:17'),(51,19,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 12:58:54'),(52,19,'failed','::1','','2026-08-24 13:09:27'),(53,19,'failed','::1','','2026-08-24 13:09:37'),(54,19,'success','::1','curl/8.20.0','2026-08-24 13:12:03'),(55,18,'success','::1','curl/8.20.0','2026-08-24 13:12:03'),(56,14,'success','::1','curl/8.20.0','2026-08-24 13:14:00'),(57,15,'success','::1','curl/8.20.0','2026-08-24 13:14:01'),(58,17,'success','::1','curl/8.20.0','2026-08-24 13:14:01'),(59,16,'success','::1','curl/8.20.0','2026-08-24 13:14:01'),(60,11,'success','::1','curl/8.20.0','2026-08-24 13:14:02'),(61,10,'success','::1','curl/8.20.0','2026-08-24 13:14:02'),(62,10,'success','::1','curl/8.20.0','2026-08-24 13:19:59'),(63,11,'success','::1','curl/8.20.0','2026-08-24 13:19:59'),(64,10,'success','::1','curl/8.20.0','2026-08-24 13:29:31'),(65,11,'success','::1','curl/8.20.0','2026-08-24 13:29:31'),(66,19,'success','::1','curl/8.20.0','2026-08-24 13:31:37'),(67,14,'success','::1','curl/8.20.0','2026-08-24 13:31:38'),(68,15,'success','::1','curl/8.20.0','2026-08-24 13:31:38'),(69,17,'success','::1','curl/8.20.0','2026-08-24 13:31:38'),(70,16,'success','::1','curl/8.20.0','2026-08-24 13:31:39'),(71,11,'success','::1','curl/8.20.0','2026-08-24 13:31:39'),(72,10,'success','::1','curl/8.20.0','2026-08-24 13:31:39'),(73,14,'success','::1','curl/8.20.0','2026-08-24 13:34:23'),(74,19,'success','::1','curl/8.20.0','2026-08-24 13:38:29'),(75,14,'success','::1','curl/8.20.0','2026-08-24 13:38:29'),(76,15,'success','::1','curl/8.20.0','2026-08-24 13:38:29'),(77,17,'success','::1','curl/8.20.0','2026-08-24 13:38:30'),(78,16,'success','::1','curl/8.20.0','2026-08-24 13:38:30'),(79,11,'success','::1','curl/8.20.0','2026-08-24 13:38:30'),(80,10,'success','::1','curl/8.20.0','2026-08-24 13:38:31'),(81,14,'success','::1','curl/8.20.0','2026-08-24 13:40:39'),(82,14,'success','::1','curl/8.20.0','2026-08-24 13:42:42'),(83,14,'success','::1','curl/8.20.0','2026-08-24 13:47:15'),(84,19,'success','::1','curl/8.20.0','2026-08-24 13:48:21'),(85,14,'success','::1','curl/8.20.0','2026-08-24 13:48:21'),(86,15,'success','::1','curl/8.20.0','2026-08-24 13:48:21'),(87,17,'success','::1','curl/8.20.0','2026-08-24 13:48:22'),(88,16,'success','::1','curl/8.20.0','2026-08-24 13:48:22'),(89,11,'success','::1','curl/8.20.0','2026-08-24 13:48:22'),(90,10,'success','::1','curl/8.20.0','2026-08-24 13:48:23'),(91,14,'success','::1','curl/8.20.0','2026-08-24 13:50:32'),(92,19,'success','::1','curl/8.20.0','2026-08-24 13:53:05'),(93,14,'success','::1','curl/8.20.0','2026-08-24 13:53:06'),(94,15,'success','::1','curl/8.20.0','2026-08-24 13:53:06'),(95,17,'success','::1','curl/8.20.0','2026-08-24 13:53:06'),(96,16,'success','::1','curl/8.20.0','2026-08-24 13:53:07'),(97,11,'success','::1','curl/8.20.0','2026-08-24 13:53:07'),(98,10,'success','::1','curl/8.20.0','2026-08-24 13:53:07'),(99,19,'failed','::1','','2026-08-24 15:39:18'),(100,19,'failed','::1','','2026-08-24 15:39:23'),(101,19,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 15:46:34'),(102,19,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-24 21:37:04'),(103,20,'success','::1','curl/8.20.0','2026-08-25 08:08:17'),(104,21,'success','::1','curl/8.20.0','2026-08-25 08:08:33'),(105,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 08:18:01'),(106,21,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 08:33:15'),(107,1,'success','::1','curl/8.20.0','2026-08-25 08:57:06'),(108,20,'success','::1','curl/8.20.0','2026-08-25 08:57:30'),(109,21,'success','::1','curl/8.20.0','2026-08-25 08:57:31'),(110,21,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 10:11:37'),(111,22,'success','::1','curl/8.20.0','2026-08-25 11:58:44'),(112,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 12:16:50'),(113,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 15:46:02'),(114,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 15:48:56'),(115,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 15:51:17'),(116,1,'success','::1','curl/8.20.0','2026-08-25 17:42:39'),(117,1,'success','::1','curl/8.20.0','2026-08-25 17:43:52'),(118,1,'success','::1','curl/8.20.0','2026-08-25 17:46:26'),(119,1,'success','::1','curl/8.20.0','2026-08-25 17:47:45'),(120,20,'success','::1','curl/8.20.0','2026-08-25 17:47:46'),(121,21,'success','::1','curl/8.20.0','2026-08-25 17:47:46'),(122,1,'success','::1','curl/8.20.0','2026-08-25 17:48:07'),(123,20,'success','::1','curl/8.20.0','2026-08-25 17:48:08'),(124,21,'success','::1','curl/8.20.0','2026-08-25 17:48:08'),(125,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 18:13:51'),(126,1,'success','::1','curl/8.20.0','2026-08-25 18:19:09'),(127,1,'success','::1','curl/8.20.0','2026-08-25 18:20:54'),(128,1,'success','::1','curl/8.20.0','2026-08-25 18:26:27'),(129,1,'success','::1','curl/8.20.0','2026-08-25 18:33:29'),(130,1,'success','::1','curl/8.20.0','2026-08-25 18:35:37'),(131,1,'success','::1','curl/8.20.0','2026-08-25 18:48:04'),(132,22,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 18:57:05'),(133,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 21:33:04'),(134,1,'success','::1','curl/8.20.0','2026-08-25 21:47:46'),(135,1,'success','::1','curl/8.20.0','2026-08-25 22:29:48'),(136,1,'success','::1','curl/8.20.0','2026-08-25 22:34:36'),(137,1,'success','::1','curl/8.20.0','2026-08-25 22:43:02'),(138,1,'success','::1','curl/8.20.0','2026-08-25 22:50:40'),(139,1,'success','::1','curl/8.20.0','2026-08-25 22:58:39'),(140,1,'success','::1','curl/8.20.0','2026-08-25 23:30:55'),(141,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-25 23:32:44'),(142,1,'success','::1','curl/8.20.0','2026-08-26 00:18:54'),(143,1,'success','::1','curl/8.20.0','2026-08-26 00:19:56'),(144,1,'success','::1','curl/8.20.0','2026-08-26 00:20:07'),(145,1,'success','::1','curl/8.20.0','2026-08-26 00:20:27'),(146,1,'success','::1','curl/8.20.0','2026-08-26 00:23:37'),(147,1,'success','::1','curl/8.20.0','2026-08-26 00:23:47'),(148,1,'success','::1','curl/8.20.0','2026-08-26 00:33:47'),(149,1,'success','::1','curl/8.20.0','2026-08-26 00:44:40'),(150,1,'success','::1','curl/8.20.0','2026-08-26 00:44:54'),(151,1,'success','::1','curl/8.20.0','2026-08-26 00:45:10'),(152,1,'success','::1','curl/8.20.0','2026-08-26 00:52:46'),(153,1,'success','::1','curl/8.20.0','2026-08-26 00:53:39'),(154,1,'success','::1','curl/8.20.0','2026-08-26 01:02:24'),(155,1,'success','::1','curl/8.20.0','2026-08-26 01:02:35'),(156,1,'success','::1','curl/8.20.0','2026-08-26 01:02:47'),(157,1,'success','::1','curl/8.20.0','2026-08-26 01:02:56'),(158,1,'success','::1','curl/8.20.0','2026-08-26 01:03:24'),(159,1,'success','::1','curl/8.20.0','2026-08-26 01:03:38'),(160,1,'success','::1','curl/8.20.0','2026-08-26 01:03:53'),(161,1,'success','::1','curl/8.20.0','2026-08-26 01:13:11'),(162,1,'success','::1','curl/8.20.0','2026-08-26 01:13:42'),(163,1,'success','::1','curl/8.20.0','2026-08-26 01:35:31'),(164,1,'success','::1','curl/8.20.0','2026-08-26 01:43:38'),(165,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-26 22:18:44'),(166,1,'success','::1','curl/8.20.0','2026-08-26 22:24:47'),(167,1,'success','::1','curl/8.20.0','2026-08-26 22:33:33'),(168,1,'success','::1','curl/8.20.0','2026-08-26 22:37:50'),(169,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-26 23:49:16'),(170,1,'success','::1','curl/8.20.0','2026-08-27 01:23:31'),(171,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 01:54:31'),(172,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 02:13:25'),(173,1,'success','::1','curl/8.20.0','2026-08-27 02:49:14'),(174,1,'success','::1','curl/8.20.0','2026-08-27 02:57:17'),(175,1,'success','::1','curl/8.20.0','2026-08-27 08:57:01'),(176,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 09:00:41'),(177,1,'success','::1','curl/8.20.0','2026-08-27 09:15:29'),(178,1,'success','::1','curl/8.20.0','2026-08-27 09:45:24'),(179,1,'success','::1','curl/8.20.0','2026-08-27 09:51:36'),(180,1,'success','::1','curl/8.20.0','2026-08-27 10:02:25'),(181,1,'success','::1','curl/8.20.0','2026-08-27 10:05:56'),(182,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 11:15:23'),(183,1,'success','::1','curl/8.20.0','2026-08-27 11:19:03'),(184,1,'success','::1','curl/8.20.0','2026-08-27 11:19:26'),(185,1,'success','::1','curl/8.20.0','2026-08-27 11:20:07'),(186,1,'success','::1','curl/8.20.0','2026-08-27 11:22:32'),(187,1,'success','::1','curl/8.20.0','2026-08-27 11:25:58'),(188,1,'success','::1','curl/8.20.0','2026-08-27 11:30:17'),(189,1,'success','::1','curl/8.20.0','2026-08-27 11:35:49'),(190,1,'success','::1','curl/8.20.0','2026-08-27 11:42:18'),(191,1,'success','::1','curl/8.20.0','2026-08-27 11:49:11'),(192,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-27 23:15:43'),(193,1,'success','::1','curl/8.20.0','2026-08-27 23:33:30'),(194,1,'success','::1','curl/8.20.0','2026-08-27 23:35:34'),(195,1,'success','::1','curl/8.20.0','2026-08-27 23:39:47'),(196,1,'success','::1','curl/8.20.0','2026-08-27 23:42:10'),(197,1,'success','::1','curl/8.20.0','2026-08-28 00:04:18'),(198,1,'success','::1','curl/8.20.0','2026-08-28 00:12:48'),(199,1,'success','::1','curl/8.20.0','2026-08-28 00:14:37'),(200,1,'success','::1','curl/8.20.0','2026-08-28 00:14:56'),(201,1,'success','::1','curl/8.20.0','2026-08-28 00:19:27'),(202,1,'success','::1','curl/8.20.0','2026-08-28 00:23:38'),(203,1,'success','::1','curl/8.20.0','2026-08-28 00:38:56'),(204,25,'success','::1','curl/8.20.0','2026-08-28 00:39:33'),(205,1,'success','::1','curl/8.20.0','2026-08-28 00:43:18'),(206,1,'success','::1','curl/8.20.0','2026-08-28 00:59:10'),(207,2,'success','::1','curl/8.20.0','2026-08-28 01:01:34'),(208,6,'success','::1','curl/8.20.0','2026-08-28 01:02:41'),(209,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 01:16:47'),(210,1,'success','::1','curl/8.20.0','2026-08-28 01:22:52'),(211,1,'success','::1','curl/8.20.0','2026-08-28 01:34:48'),(212,1,'success','::1','curl/8.20.0','2026-08-28 01:36:05'),(213,1,'success','::1','curl/8.20.0','2026-08-28 02:12:01'),(214,1,'success','::1','curl/8.20.0','2026-08-28 02:14:54'),(215,1,'success','::1','curl/8.20.0','2026-08-28 02:16:48'),(216,1,'success','::1','curl/8.20.0','2026-08-28 02:19:18'),(217,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 07:08:29'),(218,1,'success','::1','curl/8.20.0','2026-08-28 07:12:06'),(219,1,'success','::1','curl/8.20.0','2026-08-28 07:32:28'),(220,1,'success','::1','curl/8.20.0','2026-08-28 07:38:05'),(221,1,'success','::1','curl/8.20.0','2026-08-28 07:40:08'),(222,1,'success','::1','curl/8.20.0','2026-08-28 07:42:37'),(223,1,'success','::1','curl/8.20.0','2026-08-28 08:27:14'),(224,1,'success','::1','curl/8.20.0','2026-08-28 08:28:59'),(225,1,'success','::1','curl/8.20.0','2026-08-28 08:30:13'),(226,1,'success','::1','curl/8.20.0','2026-08-28 08:33:27'),(227,1,'success','::1','curl/8.20.0','2026-08-28 08:40:16'),(228,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 10:56:27'),(229,1,'success','::1','curl/8.20.0','2026-08-28 11:22:58'),(230,1,'success','::1','curl/8.20.0','2026-08-28 11:24:59'),(231,1,'success','::1','curl/8.20.0','2026-08-28 11:44:48'),(232,1,'success','::1','curl/8.20.0','2026-08-28 12:01:07'),(233,1,'success','::1','curl/8.20.0','2026-08-28 12:03:23'),(234,1,'success','::1','curl/8.20.0','2026-08-28 12:32:39'),(235,1,'success','::1','curl/8.20.0','2026-08-28 12:35:30'),(236,1,'success','::1','curl/8.20.0','2026-08-28 13:00:50'),(237,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-28 13:03:43'),(238,1,'success','::1','curl/8.20.0','2026-08-28 13:16:07'),(239,1,'success','::1','curl/8.20.0','2026-08-28 13:39:29'),(240,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 01:55:14'),(241,1,'success','::1','curl/8.20.0','2026-08-29 02:04:28'),(242,1,'success','::1','curl/8.20.0','2026-08-29 02:12:44'),(243,1,'success','::1','curl/8.20.0','2026-08-29 02:17:05'),(244,1,'success','::1','curl/8.20.0','2026-08-29 02:19:16'),(245,1,'success','::1','curl/8.20.0','2026-08-29 02:19:38'),(246,1,'success','::1','curl/8.20.0','2026-08-29 02:24:19'),(247,1,'success','::1','curl/8.20.0','2026-08-29 02:24:43'),(248,1,'success','::1','curl/8.20.0','2026-08-29 02:46:51'),(249,1,'success','::1','curl/8.20.0','2026-08-29 02:59:12'),(250,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 07:04:36'),(251,1,'success','::1','curl/8.20.0','2026-08-29 07:13:13'),(252,1,'success','::1','curl/8.20.0','2026-08-29 07:14:06'),(253,1,'success','::1','curl/8.20.0','2026-08-29 07:16:10'),(254,1,'success','::1','curl/8.20.0','2026-08-29 07:18:58'),(255,1,'success','::1','curl/8.20.0','2026-08-29 08:02:37'),(256,1,'success','::1','curl/8.20.0','2026-08-29 08:23:37'),(257,1,'success','::1','curl/8.20.0','2026-08-29 08:28:47'),(258,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 09:03:38'),(259,1,'success','::1','curl/8.20.0','2026-08-29 09:06:21'),(260,1,'success','::1','curl/8.20.0','2026-08-29 09:31:45'),(261,1,'success','::1','curl/8.20.0','2026-08-29 10:15:05'),(262,1,'success','::1','curl/8.20.0','2026-08-29 10:17:14'),(263,4,'success','::1','curl/8.20.0','2026-08-29 10:18:22'),(264,1,'success','::1','curl/8.20.0','2026-08-29 10:35:05'),(265,1,'success','::1','curl/8.20.0','2026-08-29 10:36:58'),(266,1,'success','::1','curl/8.20.0','2026-08-29 10:37:41'),(267,1,'success','::1','curl/8.20.0','2026-08-29 10:38:53'),(268,1,'success','::1','curl/8.20.0','2026-08-29 10:54:43'),(269,1,'success','::1','curl/8.20.0','2026-08-29 11:00:01'),(270,1,'success','::1','curl/8.20.0','2026-08-29 11:01:00'),(271,1,'success','::1','curl/8.20.0','2026-08-29 11:02:28'),(272,1,'success','::1','curl/8.20.0','2026-08-29 11:08:42'),(273,1,'success','::1','curl/8.20.0','2026-08-29 11:10:27'),(274,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 11:22:27'),(275,1,'success','::1','curl/8.20.0','2026-08-29 12:16:07'),(276,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-29 12:51:51'),(277,1,'success','::1','curl/8.20.0','2026-08-29 14:16:33'),(278,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 00:27:55'),(279,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 02:07:07'),(280,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 16:24:15'),(281,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 17:37:26'),(282,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 18:58:02'),(283,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 20:57:03'),(284,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-30 22:14:15'),(285,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 03:14:52'),(286,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 05:18:44'),(287,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 05:21:23'),(288,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 07:27:26'),(289,1,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 09:38:34'),(290,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-08-31 09:47:28'),(291,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-09-01 08:07:10'),(292,2,'success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2026-09-01 08:31:03');
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
  `is_demo` tinyint(1) DEFAULT '0',
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
INSERT INTO `messages` VALUES (1,1,3,'Great question in the forum today!','','','2026-08-21 22:36:25',0),(2,1,4,'Thank you teacher!','','','2026-08-21 22:36:25',0),(3,2,3,'Reminder: midterm next week.','','','2026-08-21 22:36:25',0),(4,2,2,'Don\'t forget to register for the science fair.','','','2026-08-21 22:36:25',0),(5,3,8,'KG28RCT491Jvuuf54EH6fQ6dAdzICG5NXFaURawqWDUe+sd5vqUPGsJmwibGqkqWPPlMOjLq+LHQdG66lRsYDTuHBn5uGIzo1mc=','d98e6c32cb2bf881ca1641efdc94ea858cdd79fccc8ddda52e38dbc61abeb0b3','','2026-08-22 00:14:08',0),(6,5,8,'gR8XWUksf6OflCBRLcAztTYTyEqGtEn+cjrF1mIJX2KYPvod30qd','3afc2982562d2f3e419579c895f61f9046dc3f8af865242d53a4f591333bfa95','','2026-08-22 00:14:39',0),(10,8,8,'GcyEe33RVPCp7VWQ32AILr7wHRGYixBN6YT7EVfAfdg=','c6a222b6da7ef6fe0f90fab72cd0759629ccdfec0fc4da9adf360813c8307c2c','','2026-08-22 01:28:09',0),(11,8,8,'Og49V9gWjJfLrUM3nxHlRjFUC5l5PvQ0+UVOjm9Q','37c05399db8adce168467e399c3491f24cf6fff34520949d418c75552b34f770','','2026-08-22 01:28:19',0);
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_scopes`
--

DROP TABLE IF EXISTS `module_scopes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_scopes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `module_key` varchar(60) NOT NULL,
  `scope_type` enum('region','zone','woreda','school') NOT NULL,
  `scope_id` int unsigned NOT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mod_scope` (`module_key`,`scope_type`,`scope_id`),
  KEY `idx_module` (`module_key`),
  KEY `idx_scope` (`scope_type`,`scope_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_scopes`
--

LOCK TABLES `module_scopes` WRITE;
/*!40000 ALTER TABLE `module_scopes` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_scopes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `module_key` varchar(60) NOT NULL,
  `description` text,
  `category` enum('core','education','portal','service') DEFAULT 'education',
  `mod_group` varchar(30) DEFAULT 'standard',
  `version` varchar(20) DEFAULT '1.0.0',
  `author` varchar(100) DEFAULT 'Edunex Core',
  `license` varchar(40) DEFAULT 'ARWE-PL',
  `icon` varchar(40) DEFAULT 'box',
  `repository` varchar(255) DEFAULT NULL,
  `security_status` enum('verified','community','unverified') DEFAULT 'verified',
  `dependencies` json DEFAULT NULL,
  `config_json` json DEFAULT NULL,
  `scope_type` enum('all','regional','selective') DEFAULT 'all',
  `sort_order` int DEFAULT '0',
  `education_type` varchar(40) DEFAULT 'all',
  `is_core` tinyint(1) DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `installed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_key` (`module_key`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,'Core Platform','core',NULL,'core','core','2.0.0','Edunex Core','ARWE-PL','shield-check',NULL,'verified','[]','{\"session_timeout\": 3600, \"max_login_attempts\": 5}','all',1,'all',1,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(2,'Authentication','auth',NULL,'core','core','2.0.0','Edunex Core','ARWE-PL','lock',NULL,'verified','[\"core\"]','{\"mfa_enabled\": false, \"password_min_length\": 8}','all',2,'all',1,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(3,'User Management','user-management',NULL,'core','core','2.0.0','Edunex Core','ARWE-PL','users',NULL,'verified','[\"core\", \"auth\"]',NULL,'all',3,'all',1,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(4,'Security','security',NULL,'core','core','2.0.0','Edunex Core','ARWE-PL','shield',NULL,'verified','[\"core\", \"auth\"]',NULL,'all',4,'all',1,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(5,'Messaging','messaging',NULL,'core','core','2.0.0','Edunex Core','ARWE-PL','megaphone',NULL,'verified','[\"core\"]',NULL,'all',5,'all',1,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(6,'Notifications','notifications',NULL,'core','core','2.0.0','Edunex Core','ARWE-PL','bell',NULL,'verified','[\"core\"]',NULL,'all',6,'all',1,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(7,'Backup & Restore','backup',NULL,'core','core','2.0.0','Edunex Core','ARWE-PL','save',NULL,'verified','[\"core\"]',NULL,'all',7,'all',1,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(8,'REST API','api',NULL,'core','core','2.0.0','Edunex Core','ARWE-PL','cloud',NULL,'verified','[\"core\"]',NULL,'all',8,'all',1,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(9,'Analytics','analytics',NULL,'core','core','2.0.0','Edunex Core','ARWE-PL','chart-bar',NULL,'verified','[\"core\"]',NULL,'all',9,'all',1,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(10,'AI Tutor','ai-tutor',NULL,'education','standard','1.4.0','Edunex AI','ARWE-PL','robot',NULL,'verified','[\"core\", \"auth\", \"analytics\"]','{\"features\": {\"qa\": true, \"quiz\": true, \"voice\": false, \"explanation\": true}, \"provider\": \"local\", \"languages\": [\"en\", \"am\"]}','selective',20,'all',0,1,'2026-08-29 05:31:17','2026-08-26 04:50:27','2026-08-29 05:43:12'),(11,'AI Assistant','ai-assistant',NULL,'education','standard','1.2.0','Edunex AI','ARWE-PL','sparkles',NULL,'verified','[\"core\", \"ai-tutor\"]','{\"features\": {\"quizzes\": true, \"materials\": true, \"questions\": true}, \"languages\": [\"en\", \"am\"]}','all',21,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(12,'Examinations','exams',NULL,'education','standard','2.0.0','Edunex Core','ARWE-PL','pen-line',NULL,'verified','[\"core\", \"auth\"]',NULL,'all',22,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(13,'Assignments','assignments',NULL,'education','standard','2.0.0','Edunex Core','ARWE-PL','clipboard-check',NULL,'verified','[\"core\", \"auth\"]',NULL,'all',23,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(14,'Library','library',NULL,'education','standard','2.0.0','Edunex Core','ARWE-PL','books',NULL,'verified','[\"core\"]',NULL,'all',24,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(15,'Attendance','attendance',NULL,'education','standard','2.0.0','Edunex Core','ARWE-PL','check-circle',NULL,'verified','[\"core\", \"auth\"]',NULL,'all',25,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(16,'Grading','grading',NULL,'education','standard','2.0.0','Edunex Core','ARWE-PL','graduation',NULL,'verified','[\"core\", \"auth\"]',NULL,'all',26,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(17,'Transcripts','transcripts',NULL,'education','standard','2.0.0','Edunex Core','ARWE-PL','scroll',NULL,'verified','[\"core\", \"auth\", \"grading\"]',NULL,'all',27,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(18,'Finance','finance',NULL,'portal','standard','2.0.0','Edunex Core','ARWE-PL','banknote',NULL,'verified','[\"core\", \"auth\"]',NULL,'all',28,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(19,'ID Cards','id-cards',NULL,'portal','standard','1.0.0','Edunex Core','ARWE-PL','id-card',NULL,'verified','[\"core\", \"auth\"]',NULL,'all',29,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(20,'Clearance','clearance',NULL,'portal','standard','1.0.0','Edunex Core','ARWE-PL','badge-check',NULL,'verified','[\"core\", \"auth\"]',NULL,'all',30,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(21,'Thesis','thesis',NULL,'education','standard','1.0.0','Edunex Core','ARWE-PL','book-open',NULL,'verified','[\"core\", \"auth\", \"grading\"]',NULL,'all',31,'university',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(22,'Course Registration','course-registration',NULL,'education','standard','1.0.0','Edunex Core','ARWE-PL','folder-plus',NULL,'verified','[\"core\", \"auth\"]',NULL,'all',32,'university',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(23,'Kindergarten','kindergarten',NULL,'education','optional','1.0.0','Edunex Core','ARWE-PL','baby',NULL,'verified','[\"core\", \"calendar\"]',NULL,'selective',50,'kg',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(24,'Pre-Elementary','pre-elementary',NULL,'education','optional','1.0.0','Edunex Core','ARWE-PL','sprout',NULL,'verified','[\"core\", \"calendar\"]',NULL,'selective',51,'pre-elementary',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(25,'Elementary','elementary',NULL,'education','optional','1.0.0','Edunex Core','ARWE-PL','book-open-check',NULL,'verified','[\"core\", \"calendar\"]',NULL,'selective',52,'elementary',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(26,'High School','highschool',NULL,'education','optional','1.0.0','Edunex Core','ARWE-PL','school',NULL,'verified','[\"core\", \"calendar\"]',NULL,'selective',53,'highschool',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(27,'Calendar','calendar',NULL,'education','standard','2.0.0','Edunex Core','ARWE-PL','calendar',NULL,'verified','[\"core\"]',NULL,'all',33,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(28,'Timetable','timetable',NULL,'education','standard','2.0.0','Edunex Core','ARWE-PL','clock',NULL,'verified','[\"core\"]',NULL,'all',34,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(29,'Certificates','certificates',NULL,'portal','standard','1.0.0','Edunex Core','ARWE-PL','award',NULL,'verified','[\"core\", \"auth\"]',NULL,'all',35,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(30,'Reports','reports',NULL,'portal','standard','2.0.0','Edunex Core','ARWE-PL','trend-up',NULL,'verified','[\"core\"]',NULL,'all',36,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(31,'Data Import','data-import',NULL,'service','advanced','1.0.0','Edunex Core','ARWE-PL','upload',NULL,'verified','[\"core\"]',NULL,'all',70,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(32,'Printing','printing',NULL,'service','advanced','1.0.0','Edunex Core','ARWE-PL','printer',NULL,'verified','[\"core\"]',NULL,'all',71,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(33,'SMS','sms',NULL,'service','advanced','1.0.0','Edunex Core','ARWE-PL','message-square',NULL,'verified','[\"core\"]',NULL,'all',72,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(34,'Bulk Email','email-bulk',NULL,'service','advanced','1.0.0','Edunex Core','ARWE-PL','mail',NULL,'verified','[\"core\"]',NULL,'all',73,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(35,'Webhooks','webhooks',NULL,'service','advanced','1.0.0','Edunex Core','ARWE-PL','zap',NULL,'verified','[\"core\", \"api\"]',NULL,'all',74,'all',0,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12'),(36,'Audit Trail','audit-trail',NULL,'core','core','2.0.0','Edunex Core','ARWE-PL','note',NULL,'verified','[\"core\"]',NULL,'all',10,'all',1,1,NULL,'2026-08-26 04:50:27','2026-08-29 05:43:12');
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,4,'assignment','Algebra Worksheet 1 graded','You received 88/100.','assignments/view?sub=1',NULL,'2026-08-21 22:36:25'),(2,4,'exam','Algebra Midterm is coming up','The midterm starts in 7 days.','exams/view?e=1',NULL,'2026-08-21 22:36:25'),(3,4,'announcement','Welcome to Edunex','Explore your courses and AI tutor!','communication/announcement&id=1',NULL,'2026-08-21 22:36:25'),(4,4,'message','New message','hello teachers there will be meeting on Tuesdy','messages&conv=3',NULL,'2026-08-22 00:14:08'),(5,5,'message','New message','hello henok','messages&conv=5',NULL,'2026-08-22 00:14:39'),(14,2,'announcement','FDSFffffffffffffff','fffffffffffffffffffffffffffffffffff','communication/announcement&id=9','2026-08-22 03:46:15','2026-08-22 00:45:54'),(15,8,'announcement','FDSFffffffffffffff','fffffffffffffffffffffffffffffffffff','communication/announcement&id=9','2026-08-23 12:25:39','2026-08-22 00:45:54'),(16,3,'announcement','FDSFffffffffffffff','fffffffffffffffffffffffffffffffffff','communication/announcement&id=9',NULL,'2026-08-22 00:45:54'),(17,4,'announcement','FDSFffffffffffffff','fffffffffffffffffffffffffffffffffff','communication/announcement&id=9',NULL,'2026-08-22 00:45:54'),(18,5,'announcement','FDSFffffffffffffff','fffffffffffffffffffffffffffffffffff','communication/announcement&id=9',NULL,'2026-08-22 00:45:54'),(19,7,'announcement','fffffffffffffffff','ffffffffffffff','communication/announcement&id=10',NULL,'2026-08-22 00:46:00'),(20,7,'announcement','ffffffffff','ffffffffffffffffffff','communication/announcement&id=11',NULL,'2026-08-22 00:46:08'),(21,2,'message','Suggestion about \"Library maintenance on Sunday\"','Test suggestion','messages&conv=7','2026-08-22 04:30:14','2026-08-22 00:59:13'),(22,2,'message','Suggestion about \"Library maintenance on Sunday\"','This is a test suggestion','messages&conv=7','2026-08-22 04:30:14','2026-08-22 01:08:24'),(23,2,'message','Suggestion about \"FDSFffffffffffffff\"','okay','messages&conv=8','2026-08-22 04:30:14','2026-08-22 01:28:09'),(24,2,'message','Suggestion about \"FDSFffffffffffffff\"','hh','messages&conv=8','2026-08-22 04:30:14','2026-08-22 01:28:19'),(25,6,'announcement','School Holiday in Amhara','All schools in Amhara region will be closed next Monday.','communication/announcement&id=24',NULL,'2026-08-28 01:10:23'),(26,13,'announcement','School Holiday in Amhara','All schools in Amhara region will be closed next Monday.','communication/announcement&id=24',NULL,'2026-08-28 01:10:23'),(27,25,'announcement','School Holiday in Amhara','All schools in Amhara region will be closed next Monday.','communication/announcement&id=24',NULL,'2026-08-28 01:10:23'),(28,12,'announcement','School Holiday in Amhara','All schools in Amhara region will be closed next Monday.','communication/announcement&id=24',NULL,'2026-08-28 01:10:23');
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
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int unsigned NOT NULL,
  `student_id` int unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','mobile','online') COLLATE utf8mb4_unicode_ci DEFAULT 'cash',
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `paid_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `recorded_by` int unsigned DEFAULT NULL,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `student_id` (`student_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prerequisites`
--

DROP TABLE IF EXISTS `prerequisites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prerequisites` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL,
  `required_course_id` int unsigned NOT NULL,
  `min_grade` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT 'D',
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `required_course_id` (`required_course_id`),
  CONSTRAINT `prerequisites_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prerequisites_ibfk_2` FOREIGN KEY (`required_course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prerequisites`
--

LOCK TABLES `prerequisites` WRITE;
/*!40000 ALTER TABLE `prerequisites` DISABLE KEYS */;
/*!40000 ALTER TABLE `prerequisites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `faculty_id` int unsigned DEFAULT NULL,
  `department_id` int unsigned DEFAULT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `degree_type` enum('bachelor','master','phd','diploma','certificate') COLLATE utf8mb4_unicode_ci DEFAULT 'bachelor',
  `total_credits` int unsigned DEFAULT '120',
  `duration_years` int unsigned DEFAULT '4',
  `status` enum('active','inactive','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_prog_code` (`school_id`,`code`),
  KEY `faculty_id` (`faculty_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `programs_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `programs_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programs`
--

LOCK TABLES `programs` WRITE;
/*!40000 ALTER TABLE `programs` DISABLE KEYS */;
INSERT INTO `programs` VALUES (1,2,2,3,'BSc Computer Science','BSC-CS','bachelor',132,4,'active','2026-08-24 11:20:15',0),(2,3,NULL,NULL,'Computer Science','CS','bachelor',120,4,'active','2026-08-24 11:38:59',1),(3,3,NULL,NULL,'Software Engineering','SE','bachelor',120,4,'active','2026-08-24 11:38:59',1),(4,3,NULL,NULL,'Information Technology','IT','bachelor',120,4,'active','2026-08-24 11:38:59',1),(5,3,NULL,NULL,'Data Science','DS','bachelor',120,4,'active','2026-08-24 11:38:59',1);
/*!40000 ALTER TABLE `programs` ENABLE KEYS */;
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
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `regions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `status` enum('active','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regions`
--

LOCK TABLES `regions` WRITE;
/*!40000 ALTER TABLE `regions` DISABLE KEYS */;
INSERT INTO `regions` VALUES (1,'Tigray','ET01',13.8670000,38.9040000,'active','2026-08-27 02:43:30'),(2,'Afar','ET02',12.0360000,40.7730000,'active','2026-08-27 02:43:30'),(3,'Amhara','ET03',11.5650000,38.0440000,'active','2026-08-27 02:43:30'),(4,'Oromia','ET04',7.5250000,40.7660000,'active','2026-08-27 02:43:30'),(5,'Somali','ET05',6.9290000,43.3290000,'active','2026-08-27 02:43:30'),(6,'Benishangul-Gumuz','ET06',10.5040000,35.4400000,'active','2026-08-27 02:43:30'),(7,'Central Ethiopia','ET07',7.7980000,37.9730000,'active','2026-08-27 02:43:30'),(8,'South Ethiopia','ET08',5.7780000,36.8900000,'active','2026-08-27 02:43:30'),(9,'South West Ethiopia','ET11',6.9770000,36.1920000,'active','2026-08-27 02:43:30'),(10,'Gambela','ET12',7.6840000,34.3370000,'active','2026-08-27 02:43:30'),(11,'Harari','ET13',9.2900000,42.1730000,'active','2026-08-27 02:43:30'),(12,'Addis Ababa','ET14',8.9800000,38.7860000,'active','2026-08-27 02:43:30'),(13,'Dire Dawa','ET15',9.5820000,41.8810000,'active','2026-08-27 02:43:30'),(14,'Sidama','ET16',6.6640000,38.5460000,'active','2026-08-27 02:43:30'),(15,'Contested','ET99',13.7970000,37.2740000,'active','2026-08-27 02:43:30');
/*!40000 ALTER TABLE `regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registrations`
--

DROP TABLE IF EXISTS `registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int unsigned NOT NULL,
  `course_offering_id` int unsigned NOT NULL,
  `status` enum('registered','dropped','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'registered',
  `grade` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade_points` decimal(3,2) DEFAULT NULL,
  `registered_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `dropped_at` timestamp NULL DEFAULT NULL,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reg` (`student_id`,`course_offering_id`),
  KEY `course_offering_id` (`course_offering_id`),
  CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`course_offering_id`) REFERENCES `course_offerings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registrations`
--

LOCK TABLES `registrations` WRITE;
/*!40000 ALTER TABLE `registrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `registrations` ENABLE KEYS */;
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
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `format` enum('pdf','excel','csv') COLLATE utf8mb4_unicode_ci DEFAULT 'pdf',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `filters` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `data_json` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` VALUES (1,1,1,'enrollment_stats','Test Enrollment','csv','reports/enrollment_stats_20260828_051733.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 02:17:33'),(2,1,1,'institution_stats','Institution Statistics Report — Aug 28, 2026','csv','reports/institution_stats_20260828_051743.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 02:17:43'),(3,1,1,'digital_platform','Digital Platform Usage Report — Aug 28, 2026','csv','reports/digital_platform_20260828_051743.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 02:17:43'),(4,1,1,'enrollment_stats','Enrollment Statistics Report — Aug 28, 2026','csv','reports/enrollment_stats_20260828_051918.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 02:19:18'),(5,1,1,'academic_performance','Academic Performance Report — Aug 28, 2026','csv','reports/academic_performance_20260828_051919.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 02:19:19'),(6,1,1,'school_performance','School Performance Report — Aug 28, 2026','csv','reports/school_performance_20260828_051919.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 02:19:19'),(7,1,1,'course_curriculum','Course & Curriculum Analytics Report — Aug 28, 2026','csv','reports/course_curriculum_20260828_051919.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 02:19:19'),(8,1,1,'student_progress','Student Progress Report — Aug 28, 2026','csv','reports/student_progress_20260828_051919.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 02:19:19'),(9,1,1,'regional_education','Regional Education Report — Aug 28, 2026','csv','reports/regional_education_20260828_051919.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 02:19:19'),(10,1,1,'institution_stats','Institution Statistics Report — Aug 28, 2026','csv','reports/institution_stats_20260828_051920.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 02:19:20'),(11,1,1,'digital_platform','Digital Platform Usage Report — Aug 28, 2026','csv','reports/digital_platform_20260828_051920.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 02:19:20'),(12,1,1,'system_activity','System Activity Report — Aug 28, 2026','csv','reports/system_activity_20260828_051920.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 02:19:20'),(13,1,1,'teacher_workforce','Teacher Workforce Statistics Report — Aug 28, 2026','csv','reports/teacher_workforce_20260828_051920.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 02:19:20'),(14,1,1,'education_performance','hello','csv','reports/education_performance_20260828_100858.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 07:08:58'),(15,1,1,'education_performance','hello2','pdf','reports/education_performance_20260828_100920.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 07:09:20'),(16,1,1,'enrollment_stats','Enrollment PDF Test','pdf','reports/enrollment_stats_20260828_101206.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 07:12:06'),(17,1,1,'education_performance','ere','pdf','reports/education_performance_20260828_101928.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 07:19:28'),(18,1,1,'enrollment_stats','Enrollment Statistics Report','pdf','reports/enrollment_stats_20260828_103228.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 07:32:28'),(19,1,1,'education_performance','ewdwed','pdf','reports/education_performance_20260828_103309.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 07:33:09'),(20,1,1,'enrollment_stats','Enrollment Statistics Report','pdf','reports/enrollment_stats_20260828_103805.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 07:38:05'),(21,1,1,'enrollment_stats','Enrollment Statistics Report','pdf','reports/enrollment_stats_20260828_104008.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 07:40:08'),(22,1,1,'enrollment_stats','Enrollment Statistics Report','pdf','reports/enrollment_stats_20260828_104237.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 07:42:37'),(23,1,1,'school_performance','School Performance Report','pdf','reports/school_performance_20260828_104256.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 07:42:56'),(24,1,1,'education_performance','qqqqqq','pdf','reports/education_performance_20260828_104315.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 07:43:15'),(25,1,1,'system_activity','System Activity Report — Aug 28, 2026','pdf','reports/system_activity_20260828_105806.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 07:58:06'),(26,1,1,'system_activity','fsfs','csv','reports/system_activity_20260828_111656.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 08:16:56'),(27,1,1,'system_activity','System Activity Report — Aug 28, 2026','pdf','reports/system_activity_20260828_111706.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}',NULL,'2026-08-28 08:17:06'),(28,1,1,'enrollment_stats','Enrollment Test','pdf','reports/enrollment_stats_20260828_112714.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Zone\",\"Institution\",\"Total Students\",\"Male\",\"Female\",\"Active\",\"Inactive\",\"New Enrollment\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Zone\":\"\\u2014\",\"Institution\":\"Addis Ababa International School\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"Amhara\",\"Zone\":\"\\u2014\",\"Institution\":\"Bahir Dar University\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"South Ethiopia\",\"Zone\":\"\\u2014\",\"Institution\":\"Hawassa Preparatory School\",\"Total Students\":2,\"Male\":null,\"Female\":null,\"Active\":\"2\",\"Inactive\":\"0\",\"New Enrollment\":\"2\"}]}','2026-08-28 08:27:14'),(29,1,1,'enrollment_stats','Enrollment Test PDF','pdf','reports/enrollment_stats_20260828_113338.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Zone\",\"Institution\",\"Total Students\",\"Male\",\"Female\",\"Active\",\"Inactive\",\"New Enrollment\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Zone\":\"\\u2014\",\"Institution\":\"Addis Ababa International School\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"Amhara\",\"Zone\":\"\\u2014\",\"Institution\":\"Bahir Dar University\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"South Ethiopia\",\"Zone\":\"\\u2014\",\"Institution\":\"Hawassa Preparatory School\",\"Total Students\":2,\"Male\":null,\"Female\":null,\"Active\":\"2\",\"Inactive\":\"0\",\"New Enrollment\":\"2\"}]}','2026-08-28 08:33:38'),(30,1,1,'system_activity','System Activity Report — Aug 28, 2026','pdf','reports/system_activity_20260828_113448.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Action\",\"Detail\",\"User\",\"Date\"],\"rows\":[{\"Action\":\"report\",\"Detail\":\"Generated: Enrollment Test PDF\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 11:33:38\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 11:33:27\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 11:33:27\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 11:30:13\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 11:30:13\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 11:28:59\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 11:28:59\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 11:27:14\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 11:27:14\"},{\"Action\":\"report\",\"Detail\":\"Generated: Enrollment Test\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 11:27:14\"},{\"Action\":\"report\",\"Detail\":\"Generated: System Activity Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 11:17:06\"},{\"Action\":\"report\",\"Detail\":\"Generated: fsfs\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 11:16:56\"},{\"Action\":\"report\",\"Detail\":\"Generated: System Activity Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:58:06\"},{\"Action\":\"report\",\"Detail\":\"Generated: qqqqqq\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:43:15\"},{\"Action\":\"report\",\"Detail\":\"Generated: School Performance Report\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:42:56\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:42:37\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:42:37\"},{\"Action\":\"report\",\"Detail\":\"Generated: Enrollment Statistics Report\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:42:37\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:40:08\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:40:08\"},{\"Action\":\"report\",\"Detail\":\"Generated: Enrollment Statistics Report\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:40:08\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:38:05\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:38:05\"},{\"Action\":\"report\",\"Detail\":\"Generated: Enrollment Statistics Report\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:38:05\"},{\"Action\":\"report\",\"Detail\":\"Generated: ewdwed\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:33:09\"},{\"Action\":\"report\",\"Detail\":\"Generated: Enrollment Statistics Report\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:32:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:32:28\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:32:28\"},{\"Action\":\"report\",\"Detail\":\"Generated: ere\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:19:28\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:12:06\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:12:06\"},{\"Action\":\"report\",\"Detail\":\"Generated: Enrollment PDF Test\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:12:06\"},{\"Action\":\"report\",\"Detail\":\"Generated: hello2\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:09:20\"},{\"Action\":\"report\",\"Detail\":\"Generated: hello\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:08:58\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:08:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 10:08:29\"},{\"Action\":\"report\",\"Detail\":\"Generated: Institution Statistics Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:19:20\"},{\"Action\":\"report\",\"Detail\":\"Generated: Digital Platform Usage Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:19:20\"},{\"Action\":\"report\",\"Detail\":\"Generated: System Activity Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:19:20\"},{\"Action\":\"report\",\"Detail\":\"Generated: Teacher Workforce Statistics Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:19:20\"},{\"Action\":\"report\",\"Detail\":\"Generated: Academic Performance Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:19:19\"},{\"Action\":\"report\",\"Detail\":\"Generated: School Performance Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:19:19\"},{\"Action\":\"report\",\"Detail\":\"Generated: Course & Curriculum Analytics Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:19:19\"},{\"Action\":\"report\",\"Detail\":\"Generated: Student Progress Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:19:19\"},{\"Action\":\"report\",\"Detail\":\"Generated: Regional Education Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:19:19\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:19:18\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:19:18\"},{\"Action\":\"report\",\"Detail\":\"Generated: Enrollment Statistics Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:19:18\"},{\"Action\":\"report\",\"Detail\":\"Generated: Digital Platform Usage Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:17:44\"},{\"Action\":\"report\",\"Detail\":\"Generated: Institution Statistics Report \\u2014 Aug 28, 2026\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:17:43\"},{\"Action\":\"report\",\"Detail\":\"Generated: Test Enrollment\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:17:33\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:16:48\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:16:48\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:14:54\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:14:54\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:12:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 05:12:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 04:36:05\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 04:36:05\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 04:34:48\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 04:34:48\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 04:22:52\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 04:22:52\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 04:16:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 04:16:47\"},{\"Action\":\"announcement.approve\",\"Detail\":\"Approved announcement #24 for Amhara\",\"User\":\"Kebede Hailu\",\"Date\":\"2026-08-28 04:10:23\"},{\"Action\":\"login\",\"Detail\":\"Signed in as regional\",\"User\":\"Kebede Hailu\",\"Date\":\"2026-08-28 04:02:41\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Kebede Hailu\",\"Date\":\"2026-08-28 04:02:41\"},{\"Action\":\"login\",\"Detail\":\"Signed in as regional\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-28 04:01:34\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-28 04:01:34\"},{\"Action\":\"announcement\",\"Detail\":\"Posted: School Holiday in Amhara (pending regional approval for Amhara)\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 04:01:17\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:59:10\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:59:10\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:43:18\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:43:18\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Test UniStudent\",\"Date\":\"2026-08-28 03:39:33\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Test UniStudent\",\"Date\":\"2026-08-28 03:39:33\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:38:56\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:38:56\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:23:38\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:23:38\"},{\"Action\":\"academic_year\",\"Detail\":\"Applied shared calendar to 0 schools\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:21:05\"},{\"Action\":\"academic_year\",\"Detail\":\"Applied shared calendar to 0 schools\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:21:03\"},{\"Action\":\"academic_year\",\"Detail\":\"Applied shared calendar to 2 schools\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:19:44\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:19:27\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:19:27\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:14:56\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:14:56\"},{\"Action\":\"academic_year\",\"Detail\":\"Created shared school calendar: 2026\\/27\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:14:56\"},{\"Action\":\"academic_year\",\"Detail\":\"Created shared school calendar: 2026\\/27\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:14:38\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:14:37\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:14:37\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:12:48\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:12:48\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:04:18\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 03:04:18\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 02:42:10\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 02:42:10\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 02:39:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 02:39:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 02:35:34\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 02:35:34\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 02:33:30\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 02:33:30\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 02:15:43\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-28 02:15:43\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:49:11\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:49:11\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:42:18\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:42:18\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:35:49\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:35:49\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:30:17\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:30:17\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:25:58\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:25:58\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:22:32\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:22:32\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:20:08\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:20:07\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:19:27\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:19:27\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:19:03\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:19:03\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:15:23\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 14:15:23\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 13:05:56\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 13:05:56\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 13:02:25\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 13:02:25\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 12:51:36\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 12:51:36\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 12:45:24\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 12:45:24\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 12:15:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 12:15:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 12:00:41\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 12:00:41\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 11:57:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 11:57:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 05:57:17\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 05:57:17\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 05:49:14\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 05:49:14\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 05:13:25\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 05:13:25\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 04:54:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 04:54:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 04:23:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 04:23:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 02:49:17\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 02:49:17\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 01:37:50\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 01:37:50\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 01:33:33\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 01:33:33\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 01:24:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 01:24:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 01:18:44\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-27 01:18:44\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:43:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:43:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:35:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:35:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:13:42\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:13:42\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:13:11\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:13:11\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:03:53\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:03:53\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:03:38\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:03:38\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:03:24\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:03:24\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:02:56\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:02:56\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:02:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:02:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:02:35\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:02:35\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:02:25\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 04:02:24\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:53:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:53:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:52:46\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:52:46\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:45:10\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:45:10\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:44:54\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:44:54\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:44:40\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:44:40\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:33:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:33:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:23:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:23:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:23:37\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:23:37\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:20:27\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:20:27\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:20:07\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:20:07\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:19:56\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:19:56\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:18:54\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 03:18:54\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 02:32:44\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 02:32:44\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 02:30:55\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 02:30:55\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 01:58:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 01:58:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 01:50:40\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 01:50:40\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 01:43:02\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 01:43:02\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 01:34:36\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 01:34:36\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 01:29:48\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 01:29:48\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 00:47:46\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 00:47:46\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: ON (Demo)\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 00:33:26\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: OFF (Normal)\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 00:33:21\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 00:33:04\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-26 00:33:04\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: ON (Demo)\",\"User\":\"IT Admin\",\"Date\":\"2026-08-25 21:57:19\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: OFF (Normal)\",\"User\":\"IT Admin\",\"Date\":\"2026-08-25 21:57:16\"},{\"Action\":\"login\",\"Detail\":\"Signed in as it_admin\",\"User\":\"IT Admin\",\"Date\":\"2026-08-25 21:57:05\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"IT Admin\",\"Date\":\"2026-08-25 21:57:05\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: ON (Demo)\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:55:14\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:48:04\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:48:04\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:35:37\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:35:37\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:33:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:33:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:26:27\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:26:27\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: ON (Demo)\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:23:21\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: OFF (Normal)\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:23:15\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:20:54\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:20:54\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: ON (Demo)\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:20:21\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: OFF (Normal)\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:19:52\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:19:09\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:19:09\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:13:51\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 21:13:51\"},{\"Action\":\"login\",\"Detail\":\"Signed in as zonal\",\"User\":\"Zone Admin\",\"Date\":\"2026-08-25 20:48:08\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Zone Admin\",\"Date\":\"2026-08-25 20:48:08\"},{\"Action\":\"login\",\"Detail\":\"Signed in as woreda\",\"User\":\"Woreda Admin\",\"Date\":\"2026-08-25 20:48:08\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Woreda Admin\",\"Date\":\"2026-08-25 20:48:08\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 20:48:07\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 20:48:07\"},{\"Action\":\"login\",\"Detail\":\"Signed in as zonal\",\"User\":\"Zone Admin\",\"Date\":\"2026-08-25 20:47:46\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Zone Admin\",\"Date\":\"2026-08-25 20:47:46\"},{\"Action\":\"login\",\"Detail\":\"Signed in as woreda\",\"User\":\"Woreda Admin\",\"Date\":\"2026-08-25 20:47:46\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Woreda Admin\",\"Date\":\"2026-08-25 20:47:46\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 20:47:45\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 20:47:45\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 20:46:26\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 20:46:26\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 20:43:52\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 20:43:52\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 20:42:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 20:42:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 18:51:17\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 18:51:17\"},{\"Action\":\"login\",\"Detail\":\"Signed in as regional\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-25 18:48:56\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-25 18:48:56\"},{\"Action\":\"login\",\"Detail\":\"Signed in as regional\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-25 18:46:02\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-25 18:46:02\"},{\"Action\":\"login\",\"Detail\":\"Signed in as regional\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-25 15:16:50\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-25 15:16:50\"},{\"Action\":\"login\",\"Detail\":\"Signed in as it_admin\",\"User\":\"IT Admin\",\"Date\":\"2026-08-25 14:58:44\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"IT Admin\",\"Date\":\"2026-08-25 14:58:44\"},{\"Action\":\"login\",\"Detail\":\"Signed in as woreda\",\"User\":\"Woreda Admin\",\"Date\":\"2026-08-25 13:11:37\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Woreda Admin\",\"Date\":\"2026-08-25 13:11:37\"},{\"Action\":\"login\",\"Detail\":\"Signed in as woreda\",\"User\":\"Woreda Admin\",\"Date\":\"2026-08-25 11:57:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Woreda Admin\",\"Date\":\"2026-08-25 11:57:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in as zonal\",\"User\":\"Zone Admin\",\"Date\":\"2026-08-25 11:57:30\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Zone Admin\",\"Date\":\"2026-08-25 11:57:30\"},{\"Action\":\"login\",\"Detail\":\"Signed in as ministry\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 11:57:06\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-25 11:57:06\"},{\"Action\":\"login\",\"Detail\":\"Signed in as woreda_admin\",\"User\":\"Woreda Admin\",\"Date\":\"2026-08-25 11:33:15\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Woreda Admin\",\"Date\":\"2026-08-25 11:33:15\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: OFF (Normal)\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-25 11:20:08\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-25 11:18:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-25 11:18:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in as woreda_admin\",\"User\":\"Woreda Admin\",\"Date\":\"2026-08-25 11:08:33\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Woreda Admin\",\"Date\":\"2026-08-25 11:08:33\"},{\"Action\":\"login\",\"Detail\":\"Signed in as zonal_admin\",\"User\":\"Zone Admin\",\"Date\":\"2026-08-25 11:08:18\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Zone Admin\",\"Date\":\"2026-08-25 11:08:18\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: ON (Demo)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-25 00:46:05\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: OFF (Normal)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-25 00:37:56\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: ON (Demo)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-25 00:37:36\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: OFF (Normal)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-25 00:37:15\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: ON (Demo)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-25 00:37:07\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-25 00:37:04\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-25 00:37:04\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: OFF (Normal)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 19:02:24\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: ON (Demo)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 19:02:18\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: OFF (Normal)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 18:47:09\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: ON (Demo)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 18:47:06\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: OFF (Normal)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 18:46:42\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 18:46:34\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 18:46:34\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student_affairs\",\"User\":\"Samuel Worku\",\"Date\":\"2026-08-24 16:53:07\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Samuel Worku\",\"Date\":\"2026-08-24 16:53:07\"},{\"Action\":\"login\",\"Detail\":\"Signed in as dean\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:53:07\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:53:07\"},{\"Action\":\"login\",\"Detail\":\"Signed in as registrar\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:53:07\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:53:07\"},{\"Action\":\"login\",\"Detail\":\"Signed in as lecturer\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:53:06\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:53:06\"},{\"Action\":\"login\",\"Detail\":\"Signed in as bursar\",\"User\":\"Fatima Ali\",\"Date\":\"2026-08-24 16:53:06\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Fatima Ali\",\"Date\":\"2026-08-24 16:53:06\"},{\"Action\":\"login\",\"Detail\":\"Signed in as librarian\",\"User\":\"Hana Tesfa\",\"Date\":\"2026-08-24 16:53:06\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Hana Tesfa\",\"Date\":\"2026-08-24 16:53:06\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:53:05\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:53:05\"},{\"Action\":\"login\",\"Detail\":\"Signed in as lecturer\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:50:32\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:50:32\"},{\"Action\":\"login\",\"Detail\":\"Signed in as registrar\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:48:23\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:48:23\"},{\"Action\":\"login\",\"Detail\":\"Signed in as librarian\",\"User\":\"Hana Tesfa\",\"Date\":\"2026-08-24 16:48:22\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Hana Tesfa\",\"Date\":\"2026-08-24 16:48:22\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student_affairs\",\"User\":\"Samuel Worku\",\"Date\":\"2026-08-24 16:48:22\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Samuel Worku\",\"Date\":\"2026-08-24 16:48:22\"},{\"Action\":\"login\",\"Detail\":\"Signed in as dean\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:48:22\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:48:22\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:48:21\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:48:21\"},{\"Action\":\"login\",\"Detail\":\"Signed in as lecturer\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:48:21\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:48:21\"},{\"Action\":\"login\",\"Detail\":\"Signed in as bursar\",\"User\":\"Fatima Ali\",\"Date\":\"2026-08-24 16:48:21\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Fatima Ali\",\"Date\":\"2026-08-24 16:48:21\"},{\"Action\":\"login\",\"Detail\":\"Signed in as lecturer\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:47:16\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:47:16\"},{\"Action\":\"login\",\"Detail\":\"Signed in as lecturer\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:42:42\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:42:42\"},{\"Action\":\"login\",\"Detail\":\"Signed in as lecturer\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:40:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:40:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in as registrar\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:38:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:38:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in as librarian\",\"User\":\"Hana Tesfa\",\"Date\":\"2026-08-24 16:38:30\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Hana Tesfa\",\"Date\":\"2026-08-24 16:38:30\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student_affairs\",\"User\":\"Samuel Worku\",\"Date\":\"2026-08-24 16:38:30\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Samuel Worku\",\"Date\":\"2026-08-24 16:38:30\"},{\"Action\":\"login\",\"Detail\":\"Signed in as dean\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:38:30\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:38:30\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:38:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:38:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in as lecturer\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:38:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:38:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in as bursar\",\"User\":\"Fatima Ali\",\"Date\":\"2026-08-24 16:38:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Fatima Ali\",\"Date\":\"2026-08-24 16:38:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in as lecturer\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:34:23\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:34:23\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student_affairs\",\"User\":\"Samuel Worku\",\"Date\":\"2026-08-24 16:31:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Samuel Worku\",\"Date\":\"2026-08-24 16:31:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in as dean\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:31:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:31:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in as registrar\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:31:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:31:39\"},{\"Action\":\"login\",\"Detail\":\"Signed in as lecturer\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:31:38\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:31:38\"},{\"Action\":\"login\",\"Detail\":\"Signed in as bursar\",\"User\":\"Fatima Ali\",\"Date\":\"2026-08-24 16:31:38\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Fatima Ali\",\"Date\":\"2026-08-24 16:31:38\"},{\"Action\":\"login\",\"Detail\":\"Signed in as librarian\",\"User\":\"Hana Tesfa\",\"Date\":\"2026-08-24 16:31:38\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Hana Tesfa\",\"Date\":\"2026-08-24 16:31:38\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:31:37\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:31:37\"},{\"Action\":\"login\",\"Detail\":\"Signed in as registrar\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:29:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:29:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in as dean\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:29:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:29:31\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: ON (Demo)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:20:42\"},{\"Action\":\"login\",\"Detail\":\"Signed in as registrar\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:19:59\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:19:59\"},{\"Action\":\"login\",\"Detail\":\"Signed in as dean\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:19:59\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:19:59\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: OFF (Normal)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:19:44\"},{\"Action\":\"login\",\"Detail\":\"Signed in as dean\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:14:02\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dr. Alem Bekele\",\"Date\":\"2026-08-24 16:14:02\"},{\"Action\":\"login\",\"Detail\":\"Signed in as registrar\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:14:02\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Abebe Kebede\",\"Date\":\"2026-08-24 16:14:02\"},{\"Action\":\"login\",\"Detail\":\"Signed in as lecturer\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:14:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Yonas Damtew\",\"Date\":\"2026-08-24 16:14:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in as bursar\",\"User\":\"Fatima Ali\",\"Date\":\"2026-08-24 16:14:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Fatima Ali\",\"Date\":\"2026-08-24 16:14:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in as librarian\",\"User\":\"Hana Tesfa\",\"Date\":\"2026-08-24 16:14:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Hana Tesfa\",\"Date\":\"2026-08-24 16:14:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student_affairs\",\"User\":\"Samuel Worku\",\"Date\":\"2026-08-24 16:14:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Samuel Worku\",\"Date\":\"2026-08-24 16:14:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:12:03\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:12:03\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Liya Girma\",\"Date\":\"2026-08-24 16:12:03\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Liya Girma\",\"Date\":\"2026-08-24 16:12:03\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: ON (Demo)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:06:01\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: OFF (Normal)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:05:58\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: ON (Demo)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:05:54\"},{\"Action\":\"settings\",\"Detail\":\"Toggled demo mode: OFF (Normal)\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 16:05:50\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 15:58:54\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 15:58:54\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 14:04:17\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Daniel Abebe\",\"Date\":\"2026-08-24 14:04:17\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 13:51:13\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 13:51:13\"},{\"Action\":\"login\",\"Detail\":\"Signed in as sysadmin\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 12:25:48\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 12:25:48\"},{\"Action\":\"login\",\"Detail\":\"Signed in as director\",\"User\":\"Dir One\",\"Date\":\"2026-08-23 12:24:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dir One\",\"Date\":\"2026-08-23 12:24:29\"},{\"Action\":\"login\",\"Detail\":\"Signed in as director\",\"User\":\"Dir One\",\"Date\":\"2026-08-23 12:23:12\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dir One\",\"Date\":\"2026-08-23 12:23:12\"},{\"Action\":\"login\",\"Detail\":\"Signed in as director\",\"User\":\"Dir One\",\"Date\":\"2026-08-23 12:18:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dir One\",\"Date\":\"2026-08-23 12:18:01\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 12:16:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 12:16:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 10:20:40\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 10:20:40\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 10:15:50\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 10:15:50\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 10:12:34\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 10:12:34\"},{\"Action\":\"login\",\"Detail\":\"Signed in as sysadmin\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 10:10:55\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 10:10:55\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 10:09:05\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 10:09:05\"},{\"Action\":\"login\",\"Detail\":\"Signed in as sysadmin\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 10:08:52\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 10:08:52\"},{\"Action\":\"login\",\"Detail\":\"Signed in as sysadmin\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 10:06:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 10:06:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Liya Girma\",\"Date\":\"2026-08-23 10:06:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Liya Girma\",\"Date\":\"2026-08-23 10:06:31\"},{\"Action\":\"login\",\"Detail\":\"Signed in as sysadmin\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 10:01:21\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 10:01:21\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:57:51\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:57:51\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:57:27\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:57:27\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:57:14\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:57:14\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:56:22\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:56:22\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:55:18\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:55:18\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:53:22\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:53:22\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:52:18\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Super Admin\",\"Date\":\"2026-08-23 09:52:18\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 09:51:26\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 09:51:26\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 09:46:10\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 09:46:10\"},{\"Action\":\"user\",\"Detail\":\"Director created teacher Henok Arega\",\"User\":\"Dir One\",\"Date\":\"2026-08-23 08:48:54\"},{\"Action\":\"login\",\"Detail\":\"Signed in as director\",\"User\":\"Dir One\",\"Date\":\"2026-08-23 08:47:16\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dir One\",\"Date\":\"2026-08-23 08:47:16\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 08:46:32\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 08:46:32\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 08:27:53\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 08:27:53\"},{\"Action\":\"login\",\"Detail\":\"Signed in as director\",\"User\":\"Dir One\",\"Date\":\"2026-08-23 08:23:49\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dir One\",\"Date\":\"2026-08-23 08:23:49\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 08:22:54\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-23 08:22:54\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-22 04:29:53\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-22 04:29:53\"},{\"Action\":\"ai_chat\",\"Detail\":\"Tutor: explain 2+2\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 04:28:58\"},{\"Action\":\"ai_chat\",\"Detail\":\"Tutor: who are you?\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 04:28:28\"},{\"Action\":\"ai_chat\",\"Detail\":\"Tutor: What is 2 plus 2\",\"User\":\"Liya Girma\",\"Date\":\"2026-08-22 04:18:37\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Liya Girma\",\"Date\":\"2026-08-22 04:10:40\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Liya Girma\",\"Date\":\"2026-08-22 04:10:40\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-22 04:09:47\"},{\"Action\":\"ai_chat\",\"Detail\":\"Tutor: What is 2+2?\",\"User\":\"Liya Girma\",\"Date\":\"2026-08-22 04:09:10\"},{\"Action\":\"login\",\"Detail\":\"Signed in as student\",\"User\":\"Liya Girma\",\"Date\":\"2026-08-22 03:56:26\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Liya Girma\",\"Date\":\"2026-08-22 03:56:26\"},{\"Action\":\"login\",\"Detail\":\"Signed in as director\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 03:46:24\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 03:46:24\"},{\"Action\":\"announcement.create\",\"Detail\":\"Regional admin announced to school #3\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-22 03:46:08\"},{\"Action\":\"announcement.create\",\"Detail\":\"Regional admin announced to school #3\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-22 03:46:00\"},{\"Action\":\"announcement.create\",\"Detail\":\"Regional admin announced to school #1\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-22 03:45:54\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-22 03:45:46\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-22 03:45:46\"},{\"Action\":\"ai_chat\",\"Detail\":\"Tutor: C D _\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 03:45:38\"},{\"Action\":\"ai_chat\",\"Detail\":\"Tutor: cat\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 03:45:06\"},{\"Action\":\"login\",\"Detail\":\"Signed in as director\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 03:16:27\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 03:16:27\"},{\"Action\":\"announcement.create\",\"Detail\":\"Regional admin announced to school #3\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-22 03:15:53\"},{\"Action\":\"announcement.create\",\"Detail\":\"Regional admin announced to school #1\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-22 03:15:47\"},{\"Action\":\"login\",\"Detail\":\"Signed in as admin\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-22 03:15:41\"},{\"Action\":\"login\",\"Detail\":\"Signed in\",\"User\":\"Sara Tesfaye\",\"Date\":\"2026-08-22 03:15:41\"},{\"Action\":\"ai_chat\",\"Detail\":\"Tutor: Cat\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 03:12:32\"},{\"Action\":\"ai_chat\",\"Detail\":\"Tutor: i want maths\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 03:11:30\"},{\"Action\":\"ai_chat\",\"Detail\":\"Tutor: who are you?\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 03:11:20\"},{\"Action\":\"ai_chat\",\"Detail\":\"Tutor: hey\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 03:11:16\"},{\"Action\":\"ai_chat\",\"Detail\":\"Tutor: what is Erosion?\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 03:11:04\"},{\"Action\":\"ai_chat\",\"Detail\":\"Tutor: explain 2+2\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 03:10:49\"},{\"Action\":\"login\",\"Detail\":\"Signed in as director\",\"User\":\"Dir One\",\"Date\":\"2026-08-22 03:10:33\"}]}','2026-08-28 08:34:48'),(31,1,1,'education_performance','Education Performance Report — Aug 28, 2026','csv','reports/education_performance_20260828_113553.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Students\",\"Teachers\",\"Status\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Students\":1,\"Teachers\":2,\"Status\":\"active\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Students\":1,\"Teachers\":0,\"Status\":\"active\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Students\":2,\"Teachers\":2,\"Status\":\"active\"}]}','2026-08-28 08:35:53'),(32,1,1,'student_progress','Student Progress Report — Aug 28, 2026','pdf','reports/student_progress_20260828_113620.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Total Students\",\"Active\",\"Completed\",\"Transferred\",\"Withdrawn\",\"Retention Rate\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Total Students\":1,\"Active\":\"1\",\"Completed\":\"1\",\"Transferred\":0,\"Withdrawn\":0,\"Retention Rate\":\"100.0\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Total Students\":1,\"Active\":\"1\",\"Completed\":\"1\",\"Transferred\":0,\"Withdrawn\":0,\"Retention Rate\":\"100.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Total Students\":2,\"Active\":\"2\",\"Completed\":\"2\",\"Transferred\":0,\"Withdrawn\":0,\"Retention Rate\":\"100.0\"}]}','2026-08-28 08:36:20'),(33,1,1,'education_performance','Education Performance Report — Aug 28, 2026','csv','reports/education_performance_20260828_135643.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Students\",\"Teachers\",\"Status\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Students\":1,\"Teachers\":2,\"Status\":\"active\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Students\":1,\"Teachers\":0,\"Status\":\"active\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Students\":2,\"Teachers\":2,\"Status\":\"active\"}]}','2026-08-28 10:56:43'),(34,1,1,'education_performance','Education Performance Report — Aug 28, 2026','pdf','reports/education_performance_20260828_142605.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Students\",\"Teachers\",\"Status\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Students\":1,\"Teachers\":2,\"Status\":\"active\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Students\":1,\"Teachers\":0,\"Status\":\"active\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Students\":2,\"Teachers\":2,\"Status\":\"active\"}]}','2026-08-28 11:26:05'),(35,1,1,'enrollment_stats','Enrollment Statistics Report — Aug 28, 2026','csv','reports/enrollment_stats_20260828_145251.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Zone\",\"Institution\",\"Total Students\",\"Male\",\"Female\",\"Active\",\"Inactive\",\"New Enrollment\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Zone\":\"\\u2014\",\"Institution\":\"Addis Ababa International School\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"Amhara\",\"Zone\":\"\\u2014\",\"Institution\":\"Bahir Dar University\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"South Ethiopia\",\"Zone\":\"\\u2014\",\"Institution\":\"Hawassa Preparatory School\",\"Total Students\":2,\"Male\":null,\"Female\":null,\"Active\":\"2\",\"Inactive\":\"0\",\"New Enrollment\":\"2\"}]}','2026-08-28 11:52:51'),(36,1,1,'student_progress','Student Progress Report — Aug 28, 2026','csv','reports/student_progress_20260828_145306.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Total Students\",\"Active\",\"Completed\",\"Transferred\",\"Withdrawn\",\"Retention Rate\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Total Students\":1,\"Active\":\"1\",\"Completed\":\"1\",\"Transferred\":0,\"Withdrawn\":0,\"Retention Rate\":\"100.0\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Total Students\":1,\"Active\":\"1\",\"Completed\":\"1\",\"Transferred\":0,\"Withdrawn\":0,\"Retention Rate\":\"100.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Total Students\":2,\"Active\":\"2\",\"Completed\":\"2\",\"Transferred\":0,\"Withdrawn\":0,\"Retention Rate\":\"100.0\"}]}','2026-08-28 11:53:06'),(37,1,1,'enrollment_stats','Enrollment Statistics — Aug 28, 2026','pdf','reports/enrollment_stats_20260828_150120.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"university\",\"year\":\"2026\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Zone\",\"Institution\",\"Total Students\",\"Male\",\"Female\",\"Active\",\"Inactive\",\"New Enrollment\"],\"rows\":[{\"Region\":\"Amhara\",\"Zone\":\"\\u2014\",\"Institution\":\"Bahir Dar University\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"South Ethiopia\",\"Zone\":\"\\u2014\",\"Institution\":\"Hawassa Preparatory School\",\"Total Students\":2,\"Male\":null,\"Female\":null,\"Active\":\"2\",\"Inactive\":\"0\",\"New Enrollment\":\"2\"}]}','2026-08-28 12:01:20'),(38,1,1,'teacher_workforce','Teacher Workforce Statistics — Aug 28, 2026','pdf','reports/teacher_workforce_20260828_150120.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"university\",\"year\":\"2026\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Teachers\",\"Active\",\"On Leave\",\"Avg Experience\",\"Student\\/Teacher Ratio\"],\"rows\":[{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Teachers\":2,\"Active\":\"2\",\"On Leave\":\"0\",\"Avg Experience\":null,\"Student\\/Teacher Ratio\":\"1.0\"}]}','2026-08-28 12:01:20'),(39,1,1,'teacher_workforce','Teacher Workforce Statistics — Aug 28, 2026','csv','reports/teacher_workforce_20260828_153339.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Teachers\",\"Active\",\"On Leave\",\"Avg Experience\",\"Student\\/Teacher Ratio\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Teachers\":2,\"Active\":\"2\",\"On Leave\":\"0\",\"Avg Experience\":null,\"Student\\/Teacher Ratio\":\"2.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Teachers\":2,\"Active\":\"2\",\"On Leave\":\"0\",\"Avg Experience\":null,\"Student\\/Teacher Ratio\":\"1.0\"}]}','2026-08-28 12:33:39'),(40,1,1,'course_curriculum','Course & Curriculum Analytics — Aug 28, 2026','csv','reports/course_curriculum_20260828_153339.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Course\",\"Status\",\"Enrolled\",\"Completed\",\"Completion Rate\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Course\":\"Physics Foundations\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Course\":\"Mathematics 101\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Numerical Methods\",\"Status\":\"published\",\"Enrolled\":0,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Computer Architecture\",\"Status\":\"published\",\"Enrolled\":0,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Software Engineering\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Database Systems\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Operating Systems\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Digital Logic Design\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"English for Academic Purpose\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Calculus II\",\"Status\":\"published\",\"Enrolled\":2,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Data Structures\",\"Status\":\"published\",\"Enrolled\":0,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Discrete Mathematics\",\"Status\":\"published\",\"Enrolled\":0,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Introduction to Computer Science\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Data Structures\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"}]}','2026-08-28 12:33:39'),(41,1,1,'student_progress','Student Progress — Aug 28, 2026','csv','reports/student_progress_20260828_153339.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Total Students\",\"Active\",\"Completed\",\"Transferred\",\"Withdrawn\",\"Retention Rate\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Total Students\":1,\"Active\":\"1\",\"Completed\":\"1\",\"Transferred\":0,\"Withdrawn\":0,\"Retention Rate\":\"100.0\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Total Students\":1,\"Active\":\"1\",\"Completed\":\"1\",\"Transferred\":0,\"Withdrawn\":0,\"Retention Rate\":\"100.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Total Students\":2,\"Active\":\"2\",\"Completed\":\"2\",\"Transferred\":0,\"Withdrawn\":0,\"Retention Rate\":\"100.0\"}]}','2026-08-28 12:33:39'),(42,1,1,'regional_education','Regional Education — Aug 28, 2026','csv','reports/regional_education_20260828_153339.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Schools\",\"Students\",\"Teachers\",\"Courses\",\"Avg Progress\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Schools\":1,\"Students\":\"1\",\"Teachers\":\"2\",\"Courses\":2,\"Avg Progress\":\"25.0\"},{\"Region\":\"Amhara\",\"Schools\":1,\"Students\":\"1\",\"Teachers\":\"0\",\"Courses\":0,\"Avg Progress\":null},{\"Region\":\"South Ethiopia\",\"Schools\":1,\"Students\":\"2\",\"Teachers\":\"2\",\"Courses\":12,\"Avg Progress\":\"49.4\"}]}','2026-08-28 12:33:39'),(43,1,1,'digital_platform','Digital Platform Usage — Aug 28, 2026','csv','reports/digital_platform_20260828_153339.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Metric\",\"Value\"],\"rows\":[[\"Total Users\",24],[\"Active Users (30d)\",20],[\"Total Logins (30d)\",0],[\"Messages Sent\",8],[\"Assignments Created\",10],[\"Exams Taken\",7],[\"Library Downloads\",\"804\"]]}','2026-08-28 12:33:39'),(44,1,1,'compliance','Compliance — Aug 28, 2026','csv','reports/compliance_20260828_153339.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Students\",\"Teachers\",\"Status\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Students\":1,\"Teachers\":2,\"Status\":\"active\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Students\":1,\"Teachers\":0,\"Status\":\"active\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Students\":2,\"Teachers\":2,\"Status\":\"active\"}]}','2026-08-28 12:33:39'),(45,1,1,'enrollment_stats','Enrollment Statistics — Aug 28, 2026','csv','reports/enrollment_stats_20260828_153441.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Zone\",\"Institution\",\"Total Students\",\"Male\",\"Female\",\"Active\",\"Inactive\",\"New Enrollment\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Zone\":\"\\u2014\",\"Institution\":\"Addis Ababa International School\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"Amhara\",\"Zone\":\"\\u2014\",\"Institution\":\"Bahir Dar University\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"South Ethiopia\",\"Zone\":\"\\u2014\",\"Institution\":\"Hawassa Preparatory School\",\"Total Students\":2,\"Male\":null,\"Female\":null,\"Active\":\"2\",\"Inactive\":\"0\",\"New Enrollment\":\"2\"}]}','2026-08-28 12:34:41'),(46,1,1,'teacher_workforce','Teacher Workforce Statistics — Aug 28, 2026','csv','reports/teacher_workforce_20260828_153441.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Teachers\",\"Active\",\"On Leave\",\"Avg Experience\",\"Student\\/Teacher Ratio\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Teachers\":2,\"Active\":\"2\",\"On Leave\":\"0\",\"Avg Experience\":null,\"Student\\/Teacher Ratio\":\"2.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Teachers\":2,\"Active\":\"2\",\"On Leave\":\"0\",\"Avg Experience\":null,\"Student\\/Teacher Ratio\":\"1.0\"}]}','2026-08-28 12:34:41'),(47,1,1,'academic_performance','Academic Performance — Aug 28, 2026','csv','reports/academic_performance_20260828_153441.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Course\",\"Enrolled\",\"Avg Progress\",\"Completion Rate\",\"Completed\",\"In Progress\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Course\":\"Physics Foundations\",\"Enrolled\":1,\"Avg Progress\":\"0.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Course\":\"Mathematics 101\",\"Enrolled\":1,\"Avg Progress\":\"50.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Calculus II\",\"Enrolled\":2,\"Avg Progress\":\"57.5\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"2\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Digital Logic Design\",\"Enrolled\":1,\"Avg Progress\":\"35.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Database Systems\",\"Enrolled\":1,\"Avg Progress\":\"40.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Software Engineering\",\"Enrolled\":1,\"Avg Progress\":\"55.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Operating Systems\",\"Enrolled\":1,\"Avg Progress\":\"80.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"English for Academic Purpose\",\"Enrolled\":1,\"Avg Progress\":\"30.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Introduction to Computer Science\",\"Enrolled\":1,\"Avg Progress\":\"65.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Data Structures\",\"Enrolled\":1,\"Avg Progress\":\"25.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"}]}','2026-08-28 12:34:41'),(48,1,1,'enrollment_stats','Enrollment Statistics — Aug 28, 2026','csv','reports/enrollment_stats_20260828_153531.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Zone\",\"Institution\",\"Total Students\",\"Male\",\"Female\",\"Active\",\"Inactive\",\"New Enrollment\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Zone\":\"\\u2014\",\"Institution\":\"Addis Ababa International School\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"Amhara\",\"Zone\":\"\\u2014\",\"Institution\":\"Bahir Dar University\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"South Ethiopia\",\"Zone\":\"\\u2014\",\"Institution\":\"Hawassa Preparatory School\",\"Total Students\":2,\"Male\":null,\"Female\":null,\"Active\":\"2\",\"Inactive\":\"0\",\"New Enrollment\":\"2\"}]}','2026-08-28 12:35:31'),(49,1,1,'teacher_workforce','Teacher Workforce Statistics — Aug 28, 2026','csv','reports/teacher_workforce_20260828_153531.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Teachers\",\"Active\",\"On Leave\",\"Avg Experience\",\"Student\\/Teacher Ratio\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Teachers\":2,\"Active\":\"2\",\"On Leave\":\"0\",\"Avg Experience\":null,\"Student\\/Teacher Ratio\":\"2.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Teachers\":2,\"Active\":\"2\",\"On Leave\":\"0\",\"Avg Experience\":null,\"Student\\/Teacher Ratio\":\"1.0\"}]}','2026-08-28 12:35:31'),(50,1,1,'academic_performance','Academic Performance — Aug 28, 2026','csv','reports/academic_performance_20260828_153531.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Course\",\"Enrolled\",\"Avg Progress\",\"Completion Rate\",\"Completed\",\"In Progress\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Course\":\"Physics Foundations\",\"Enrolled\":1,\"Avg Progress\":\"0.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Course\":\"Mathematics 101\",\"Enrolled\":1,\"Avg Progress\":\"50.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Calculus II\",\"Enrolled\":2,\"Avg Progress\":\"57.5\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"2\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Digital Logic Design\",\"Enrolled\":1,\"Avg Progress\":\"35.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Database Systems\",\"Enrolled\":1,\"Avg Progress\":\"40.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Software Engineering\",\"Enrolled\":1,\"Avg Progress\":\"55.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Operating Systems\",\"Enrolled\":1,\"Avg Progress\":\"80.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"English for Academic Purpose\",\"Enrolled\":1,\"Avg Progress\":\"30.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Introduction to Computer Science\",\"Enrolled\":1,\"Avg Progress\":\"65.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Data Structures\",\"Enrolled\":1,\"Avg Progress\":\"25.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"}]}','2026-08-28 12:35:31'),(51,1,1,'institution_stats','Institution Statistics — Aug 28, 2026','csv','reports/institution_stats_20260828_153531.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Metric\",\"Value\"],\"rows\":[[\"Total Institutions\",3],[\"Universities\",1],[\"Schools (K-12)\",2],[\"Total Students\",4],[\"Total Teachers\",4],[\"Total Courses\",14],[\"Active Regions\",3]]}','2026-08-28 12:35:31'),(52,1,1,'education_performance','Education Performance — Aug 28, 2026','pdf','reports/education_performance_20260828_154642.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Students\",\"Teachers\",\"Status\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Students\":1,\"Teachers\":2,\"Status\":\"active\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Students\":1,\"Teachers\":0,\"Status\":\"active\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Students\":2,\"Teachers\":2,\"Status\":\"active\"}]}','2026-08-28 12:46:42'),(53,1,1,'enrollment_stats','Enrollment Statistics — Aug 28, 2026','pdf','reports/enrollment_stats_20260828_154642.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Zone\",\"Institution\",\"Total Students\",\"Male\",\"Female\",\"Active\",\"Inactive\",\"New Enrollment\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Zone\":\"\\u2014\",\"Institution\":\"Addis Ababa International School\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"Amhara\",\"Zone\":\"\\u2014\",\"Institution\":\"Bahir Dar University\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"South Ethiopia\",\"Zone\":\"\\u2014\",\"Institution\":\"Hawassa Preparatory School\",\"Total Students\":2,\"Male\":null,\"Female\":null,\"Active\":\"2\",\"Inactive\":\"0\",\"New Enrollment\":\"2\"}]}','2026-08-28 12:46:42'),(54,1,1,'academic_performance','Academic Performance — Aug 28, 2026','pdf','reports/academic_performance_20260828_154642.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Course\",\"Enrolled\",\"Avg Progress\",\"Completion Rate\",\"Completed\",\"In Progress\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Course\":\"Physics Foundations\",\"Enrolled\":1,\"Avg Progress\":\"0.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Course\":\"Mathematics 101\",\"Enrolled\":1,\"Avg Progress\":\"50.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Calculus II\",\"Enrolled\":2,\"Avg Progress\":\"57.5\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"2\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Digital Logic Design\",\"Enrolled\":1,\"Avg Progress\":\"35.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Database Systems\",\"Enrolled\":1,\"Avg Progress\":\"40.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Software Engineering\",\"Enrolled\":1,\"Avg Progress\":\"55.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Operating Systems\",\"Enrolled\":1,\"Avg Progress\":\"80.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"English for Academic Purpose\",\"Enrolled\":1,\"Avg Progress\":\"30.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Introduction to Computer Science\",\"Enrolled\":1,\"Avg Progress\":\"65.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Data Structures\",\"Enrolled\":1,\"Avg Progress\":\"25.0\",\"Completion Rate\":\"0.0\",\"Completed\":\"0\",\"In Progress\":\"1\"}]}','2026-08-28 12:46:42'),(55,1,1,'attendance_participation','Attendance & Participation — Aug 28, 2026','pdf','reports/attendance_participation_20260828_154642.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Students\",\"Teachers\",\"Status\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Students\":1,\"Teachers\":2,\"Status\":\"active\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Students\":1,\"Teachers\":0,\"Status\":\"active\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Students\":2,\"Teachers\":2,\"Status\":\"active\"}]}','2026-08-28 12:46:42'),(56,1,1,'national_exam','National Exam Performance — Aug 28, 2026','pdf','reports/national_exam_20260828_154642.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Students\",\"Teachers\",\"Status\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Students\":1,\"Teachers\":2,\"Status\":\"active\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Students\":1,\"Teachers\":0,\"Status\":\"active\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Students\":2,\"Teachers\":2,\"Status\":\"active\"}]}','2026-08-28 12:46:42'),(57,1,1,'school_performance','School Performance — Aug 28, 2026','pdf','reports/school_performance_20260828_154642.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Zone\",\"Institution\",\"Type\",\"Students\",\"Teachers\",\"Courses\",\"Avg Progress\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Zone\":\"\\u2014\",\"Institution\":\"Addis Ababa International School\",\"Type\":\"school\",\"Students\":1,\"Teachers\":2,\"Courses\":2,\"Avg Progress\":\"25.0\"},{\"Region\":\"Amhara\",\"Zone\":\"\\u2014\",\"Institution\":\"Bahir Dar University\",\"Type\":\"university\",\"Students\":1,\"Teachers\":0,\"Courses\":0,\"Avg Progress\":null},{\"Region\":\"South Ethiopia\",\"Zone\":\"\\u2014\",\"Institution\":\"Hawassa Preparatory School\",\"Type\":\"school\",\"Students\":2,\"Teachers\":2,\"Courses\":12,\"Avg Progress\":\"49.4\"}]}','2026-08-28 12:46:42'),(58,1,1,'course_curriculum','Course & Curriculum Analytics — Aug 28, 2026','pdf','reports/course_curriculum_20260828_154642.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Course\",\"Status\",\"Enrolled\",\"Completed\",\"Completion Rate\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Course\":\"Physics Foundations\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Course\":\"Mathematics 101\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Numerical Methods\",\"Status\":\"published\",\"Enrolled\":0,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Computer Architecture\",\"Status\":\"published\",\"Enrolled\":0,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Software Engineering\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Database Systems\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Operating Systems\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Digital Logic Design\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"English for Academic Purpose\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Calculus II\",\"Status\":\"published\",\"Enrolled\":2,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Data Structures\",\"Status\":\"published\",\"Enrolled\":0,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Discrete Mathematics\",\"Status\":\"published\",\"Enrolled\":0,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Introduction to Computer Science\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Course\":\"Data Structures\",\"Status\":\"published\",\"Enrolled\":1,\"Completed\":\"0\",\"Completion Rate\":\"0.0\"}]}','2026-08-28 12:46:42'),(59,1,1,'learning_activity','Learning Activity — Aug 28, 2026','pdf','reports/learning_activity_20260828_154642.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Students\",\"Teachers\",\"Status\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Students\":1,\"Teachers\":2,\"Status\":\"active\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Students\":1,\"Teachers\":0,\"Status\":\"active\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Students\":2,\"Teachers\":2,\"Status\":\"active\"}]}','2026-08-28 12:46:42'),(60,1,1,'attendance_participation','Attendance & Participation — yy','csv','reports/attendance_participation_20260828_154659.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Students\",\"Teachers\",\"Status\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Students\":1,\"Teachers\":2,\"Status\":\"active\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Students\":1,\"Teachers\":0,\"Status\":\"active\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Students\":2,\"Teachers\":2,\"Status\":\"active\"}]}','2026-08-28 12:46:59'),(61,1,1,'teacher_workforce','Teacher Workforce Statistics — yy','csv','reports/teacher_workforce_20260828_154659.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Teachers\",\"Active\",\"On Leave\",\"Avg Experience\",\"Student\\/Teacher Ratio\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Teachers\":2,\"Active\":\"2\",\"On Leave\":\"0\",\"Avg Experience\":null,\"Student\\/Teacher Ratio\":\"2.0\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Teachers\":2,\"Active\":\"2\",\"On Leave\":\"0\",\"Avg Experience\":null,\"Student\\/Teacher Ratio\":\"1.0\"}]}','2026-08-28 12:46:59'),(62,1,1,'institution_stats','Institution Statistics Report — Aug 28, 2026','csv','reports/institution_stats_20260828_154719.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Metric\",\"Value\"],\"rows\":[[\"Total Institutions\",3],[\"Universities\",1],[\"Schools (K-12)\",2],[\"Total Students\",4],[\"Total Teachers\",4],[\"Total Courses\",14],[\"Active Regions\",3]]}','2026-08-28 12:47:19'),(63,1,1,'enrollment_stats','PDF Test','pdf','reports/enrollment_stats_20260828_154934.pdf','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Zone\",\"Institution\",\"Total Students\",\"Male\",\"Female\",\"Active\",\"Inactive\",\"New Enrollment\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Zone\":\"\\u2014\",\"Institution\":\"Addis Ababa International School\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"Amhara\",\"Zone\":\"\\u2014\",\"Institution\":\"Bahir Dar University\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"South Ethiopia\",\"Zone\":\"\\u2014\",\"Institution\":\"Hawassa Preparatory School\",\"Total Students\":2,\"Male\":null,\"Female\":null,\"Active\":\"2\",\"Inactive\":\"0\",\"New Enrollment\":\"2\"}]}','2026-08-28 12:49:34'),(64,1,1,'education_performance','Education Performance Report — Aug 28, 2026','pdf','reports/education_performance_20260828_155600.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Students\",\"Teachers\",\"Status\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Students\":1,\"Teachers\":2,\"Status\":\"active\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Students\":1,\"Teachers\":0,\"Status\":\"active\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Students\":2,\"Teachers\":2,\"Status\":\"active\"}]}','2026-08-28 12:56:00'),(65,1,1,'compliance','Compliance Report — Aug 28, 2026','csv','reports/compliance_20260828_155609.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Students\",\"Teachers\",\"Status\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Students\":1,\"Teachers\":2,\"Status\":\"active\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Students\":1,\"Teachers\":0,\"Status\":\"active\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Students\":2,\"Teachers\":2,\"Status\":\"active\"}]}','2026-08-28 12:56:09'),(66,1,1,'enrollment_stats','Glass Test','pdf','reports/enrollment_stats_20260828_160051.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Zone\",\"Institution\",\"Total Students\",\"Male\",\"Female\",\"Active\",\"Inactive\",\"New Enrollment\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Zone\":\"\\u2014\",\"Institution\":\"Addis Ababa International School\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"Amhara\",\"Zone\":\"\\u2014\",\"Institution\":\"Bahir Dar University\",\"Total Students\":1,\"Male\":null,\"Female\":null,\"Active\":\"1\",\"Inactive\":\"0\",\"New Enrollment\":\"1\"},{\"Region\":\"South Ethiopia\",\"Zone\":\"\\u2014\",\"Institution\":\"Hawassa Preparatory School\",\"Total Students\":2,\"Male\":null,\"Female\":null,\"Active\":\"2\",\"Inactive\":\"0\",\"New Enrollment\":\"2\"}]}','2026-08-28 13:00:51'),(67,1,1,'digital_platform','Digital Platform Usage Report — Aug 28, 2026','pdf','reports/digital_platform_20260828_160351.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Metric\",\"Value\"],\"rows\":[[\"Total Users\",24],[\"Active Users (30d)\",20],[\"Total Logins (30d)\",0],[\"Messages Sent\",8],[\"Assignments Created\",10],[\"Exams Taken\",7],[\"Library Downloads\",\"804\"]]}','2026-08-28 13:03:51'),(68,1,1,'education_performance','Education Performance Report — Aug 30, 2026','pdf','reports/education_performance_20260830_192820.csv','{\"region\":\"\",\"zone\":\"\",\"school_id\":0,\"education_level\":\"\",\"year\":\"\",\"semester\":\"\",\"date_from\":\"\",\"date_to\":\"\"}','{\"headers\":[\"Region\",\"Institution\",\"Students\",\"Teachers\",\"Status\"],\"rows\":[{\"Region\":\"Addis Ababa\",\"Institution\":\"Addis Ababa International School\",\"Students\":1,\"Teachers\":2,\"Status\":\"active\"},{\"Region\":\"Amhara\",\"Institution\":\"Bahir Dar University\",\"Students\":1,\"Teachers\":0,\"Status\":\"active\"},{\"Region\":\"South Ethiopia\",\"Institution\":\"Hawassa Preparatory School\",\"Students\":2,\"Teachers\":2,\"Status\":\"active\"}]}','2026-08-30 16:28:20');
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role` enum('ministry','regional','zonal','woreda','principal','hod','teacher','student','parent','registrar','dean','vice_dean','lecturer','bursar','student_affairs','librarian','school_board','guest','it_admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
  `permission` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`role`,`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES ('regional','ai.assistant'),('regional','ai.flashcards'),('regional','ai.tutor'),('regional','analytics.view'),('regional','announcements.manage'),('regional','assignments.create'),('regional','assignments.grade'),('regional','assignments.manage'),('regional','attendance.export'),('regional','attendance.manage'),('regional','attendance.record'),('regional','attendance.view'),('regional','backups.manage'),('regional','badges.manage'),('regional','calendar.create'),('regional','calendar.view'),('regional','comments.view'),('regional','courses.create'),('regional','courses.manage'),('regional','courses.view'),('regional','dashboard'),('regional','exams.create'),('regional','exams.grade'),('regional','exams.manage'),('regional','exams.take'),('regional','exams.view'),('regional','files.manage'),('regional','files.upload'),('regional','files.view'),('regional','forum.moderate'),('regional','forum.post'),('regional','gamification.view'),('regional','goals.award'),('regional','grades.export'),('regional','grades.manage'),('regional','grades.view'),('regional','leaderboard.view'),('regional','ledger.verify'),('regional','lessons.manage'),('regional','library.manage'),('regional','library.upload'),('regional','library.view'),('regional','logs.view'),('regional','messages.send'),('regional','messages.view'),('regional','notifications.view'),('regional','profile.view'),('regional','reports.export'),('regional','reports.view'),('regional','search.global'),('regional','settings.manage'),('regional','transfers.approve'),('regional','transfers.manage'),('regional','users.create'),('regional','users.manage'),('regional','users.view'),('principal','accounting.view'),('principal','analytics.view'),('principal','announcements.manage'),('principal','attendance.view'),('principal','courses.view'),('principal','exams.view'),('principal','library.view'),('principal','messages.send'),('principal','messages.view'),('principal','reports.export'),('principal','reports.generate'),('principal','schools.view'),('principal','students.view'),('principal','teachers.manage'),('principal','transfers.manage'),('principal','users.import'),('principal','users.view'),('hod','analytics.view'),('hod','clearance.manage'),('hod','courses.manage'),('hod','courses.view'),('hod','dashboard'),('hod','theses.manage'),('hod','theses.view'),('teacher','ai.assistant'),('teacher','ai.flashcards'),('teacher','ai.tutor'),('teacher','analytics.view'),('teacher','announcements.manage'),('teacher','assignments.create'),('teacher','assignments.grade'),('teacher','assignments.manage'),('teacher','attendance.manage'),('teacher','attendance.record'),('teacher','attendance.view'),('teacher','calendar.create'),('teacher','calendar.view'),('teacher','courses.manage'),('teacher','courses.view'),('teacher','exams.create'),('teacher','exams.grade'),('teacher','exams.manage'),('teacher','files.upload'),('teacher','files.view'),('teacher','forum.manage'),('teacher','forum.post'),('teacher','goals.award'),('teacher','grades.manage'),('teacher','grades.view'),('teacher','lessons.manage'),('teacher','library.manage'),('teacher','library.upload'),('teacher','library.view'),('teacher','messages.send'),('teacher','messages.view'),('teacher','parents.manage'),('teacher','reports.generate'),('teacher','reports.view'),('teacher','students.verify'),('teacher','users.import'),('student','ai.assistant'),('student','ai.flashcards'),('student','ai.tutor'),('student','assignments.submit'),('student','attendance.view'),('student','calendar.view'),('student','certificates.view'),('student','clearance.request'),('student','clearance.view'),('student','courses.enroll'),('student','courses.view'),('student','exams.take'),('student','exams.view'),('student','fees.view'),('student','files.upload'),('student','files.view'),('student','forum.post'),('student','forum.reply'),('student','gamification.view'),('student','goals.view'),('student','grades.view'),('student','leaderboard.view'),('student','library.borrow'),('student','library.view'),('student','messages.send'),('student','registration.drop'),('student','registration.register'),('student','registration.view'),('student','thesis.create'),('student','thesis.submit'),('student','thesis.view'),('student','transcript.request'),('student','transcript.view'),('student','transfers.apply'),('student','transfers.view'),('parent','assignments.view'),('parent','attendance.view'),('parent','calendar.view'),('parent','children.view'),('parent','courses.view'),('parent','grades.view'),('parent','messages.send'),('parent','messages.view'),('parent','reports.view'),('registrar','admissions.manage'),('registrar','announcements.manage'),('registrar','audit.view'),('registrar','clearance.manage'),('registrar','dashboard'),('registrar','enrollments.manage'),('registrar','grades.manage'),('registrar','grades.view'),('registrar','id_cards.manage'),('registrar','programs.manage'),('registrar','semesters.manage'),('registrar','timetable.manage'),('registrar','timetable.view'),('registrar','transcripts.generate'),('registrar','transcripts.manage'),('dean','analytics.view'),('dean','announcements.manage'),('dean','clearance.manage'),('dean','courses.approve'),('dean','dashboard'),('dean','departments.manage'),('dean','departments.view'),('dean','programs.manage'),('dean','programs.view'),('dean','teachers.view'),('dean','theses.manage'),('dean','theses.view'),('vice_dean','analytics.view'),('vice_dean','courses.approve'),('vice_dean','dashboard'),('vice_dean','programs.view'),('vice_dean','theses.view'),('lecturer','analytics.view'),('lecturer','assignments.create'),('lecturer','assignments.grade'),('lecturer','attendance.record'),('lecturer','attendance.view'),('lecturer','courses.manage'),('lecturer','courses.view'),('lecturer','dashboard'),('lecturer','exams.create'),('lecturer','exams.grade'),('lecturer','grades.manage'),('lecturer','grades.view'),('bursar','clearance.manage'),('bursar','dashboard'),('bursar','fees.manage'),('bursar','fees.view'),('bursar','invoices.manage'),('bursar','payments.record'),('bursar','reports.view'),('student_affairs','clearance.manage'),('student_affairs','dashboard'),('student_affairs','id_cards.manage'),('student_affairs','students.view'),('librarian','clearance.manage'),('librarian','dashboard'),('librarian','library.manage'),('librarian','library.upload'),('librarian','library.view'),('guest','courses.view');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `building` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `capacity` int unsigned DEFAULT '40',
  `room_type` enum('lecture_hall','lab','tutorial_room','seminar','office') COLLATE utf8mb4_unicode_ci DEFAULT 'lecture_hall',
  `equipment` json DEFAULT NULL,
  `status` enum('available','maintenance','unavailable') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,2,'Room 101','Engineering Block',40,'lecture_hall',NULL,'available'),(2,2,'Room 102','Engineering Block',40,'lecture_hall',NULL,'available'),(3,2,'Lab A','CS Building',30,'lab',NULL,'available');
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_offering_id` int unsigned NOT NULL,
  `day` enum('monday','tuesday','wednesday','thursday','friday','saturday') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room_id` int unsigned DEFAULT NULL,
  `schedule_type` enum('lecture','lab','tutorial') COLLATE utf8mb4_unicode_ci DEFAULT 'lecture',
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `course_offering_id` (`course_offering_id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`course_offering_id`) REFERENCES `course_offerings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules`
--

LOCK TABLES `schedules` WRITE;
/*!40000 ALTER TABLE `schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scholarship_awards`
--

DROP TABLE IF EXISTS `scholarship_awards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scholarship_awards` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `scholarship_id` int unsigned NOT NULL,
  `student_id` int unsigned NOT NULL,
  `awarded_by` int unsigned DEFAULT NULL,
  `awarded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_scholarship` (`scholarship_id`),
  KEY `idx_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scholarship_awards`
--

LOCK TABLES `scholarship_awards` WRITE;
/*!40000 ALTER TABLE `scholarship_awards` DISABLE KEYS */;
/*!40000 ALTER TABLE `scholarship_awards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scholarships`
--

DROP TABLE IF EXISTS `scholarships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scholarships` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `amount` decimal(10,2) DEFAULT '0.00',
  `status` enum('open','closed','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_school` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scholarships`
--

LOCK TABLES `scholarships` WRITE;
/*!40000 ALTER TABLE `scholarships` DISABLE KEYS */;
INSERT INTO `scholarships` VALUES (1,3,'Excellence Scholarship','Awarded to top 5% of students based on GPA',5000.00,'open','2026-08-24 13:23:30',1),(2,3,'Need-Based Financial Aid','Supporting students from low-income backgrounds',3000.00,'open','2026-08-24 13:23:30',1),(3,3,'STEM Women Scholarship','Encouraging women in STEM fields',4000.00,'open','2026-08-24 13:23:30',1);
/*!40000 ALTER TABLE `scholarships` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=387 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_modules`
--

LOCK TABLES `school_modules` WRITE;
/*!40000 ALTER TABLE `school_modules` DISABLE KEYS */;
INSERT INTO `school_modules` VALUES (1,1,'core',1,NULL),(2,1,'auth',1,NULL),(3,1,'user-management',1,NULL),(4,1,'security',1,NULL),(5,1,'backup',1,NULL),(6,1,'api',1,NULL),(7,1,'analytics',1,NULL),(8,1,'messaging',1,NULL),(9,1,'notifications',1,NULL),(10,1,'teacher-portal',1,NULL),(11,1,'parent-portal',1,NULL),(12,1,'student-portal',1,NULL),(13,1,'academic',1,NULL),(14,1,'attendance',1,NULL),(15,1,'library',1,NULL),(16,1,'ai-tutor',1,NULL),(17,1,'gamification',1,NULL),(18,1,'high-school',1,NULL),(19,1,'examination',1,NULL),(20,1,'certificate',1,NULL),(21,1,'lms',1,NULL),(22,1,'online-courses',1,NULL),(23,2,'core',1,NULL),(24,2,'auth',1,NULL),(25,2,'user-management',1,NULL),(26,2,'security',1,NULL),(27,2,'backup',1,NULL),(28,2,'api',1,NULL),(29,2,'analytics',1,NULL),(30,2,'messaging',1,NULL),(31,2,'notifications',1,NULL),(32,2,'teacher-portal',1,NULL),(33,2,'parent-portal',1,NULL),(34,2,'student-portal',1,NULL),(35,2,'academic',1,NULL),(36,2,'attendance',1,NULL),(37,2,'library',1,NULL),(38,2,'ai-tutor',1,NULL),(39,2,'gamification',1,NULL),(40,2,'high-school',1,NULL),(41,2,'examination',1,NULL),(42,2,'certificate',1,NULL),(43,2,'lms',1,NULL),(44,2,'online-courses',1,NULL),(45,3,'core',1,NULL),(46,3,'auth',1,NULL),(47,3,'user-management',1,NULL),(48,3,'security',1,NULL),(49,3,'backup',1,NULL),(50,3,'api',1,NULL),(51,3,'analytics',1,NULL),(52,3,'messaging',1,NULL),(53,3,'notifications',1,NULL),(54,3,'teacher-portal',1,NULL),(55,3,'parent-portal',1,NULL),(56,3,'student-portal',1,NULL),(57,3,'academic',1,NULL),(58,3,'attendance',1,NULL),(59,3,'library',1,NULL),(60,3,'ai-tutor',1,NULL),(61,3,'gamification',1,NULL),(62,3,'high-school',1,NULL),(63,3,'examination',1,NULL),(64,3,'certificate',1,NULL),(65,3,'lms',1,NULL),(66,3,'online-courses',1,NULL),(128,2,'university',1,NULL),(129,2,'admissions',1,NULL),(130,2,'registrar',1,NULL),(131,2,'dean',1,NULL),(132,2,'cgpa',1,NULL),(133,2,'research',1,NULL),(134,2,'thesis',1,NULL);
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
  `zone_id` int unsigned DEFAULT NULL,
  `woreda_id` int unsigned DEFAULT NULL,
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
  `approval_status` enum('pending','regional_approved','ministry_approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `approved_by` int unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `school_type` enum('public','private','government','ngo','international') COLLATE utf8mb4_unicode_ci DEFAULT 'public',
  `established_year` year DEFAULT NULL,
  `school_description` text COLLATE utf8mb4_unicode_ci,
  `region` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kebele` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gps_lat` decimal(10,7) DEFAULT NULL,
  `gps_lng` decimal(10,7) DEFAULT NULL,
  `alt_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `director_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `director_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `director_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `academic_year` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade_levels` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sections` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_capacity` int unsigned DEFAULT NULL,
  `teaching_language` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Amharic',
  `second_language` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grading_system` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'percentage',
  `attendance_system` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'daily',
  `school_calendar` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'ethiopian',
  `tenant_id` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_id_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_plan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'free',
  `enabled_modules` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schools`
--

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES (1,2,NULL,NULL,'Addis Ababa International School','AAIS','school','secondary','','Addis Ababa','','','','active','2026-08-21 22:36:25','ministry_approved',NULL,NULL,'public',NULL,NULL,'Addis Ababa',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Amharic',NULL,'percentage','daily','ethiopian',NULL,NULL,'free',NULL),(2,6,NULL,NULL,'Bahir Dar University','BDU','university','university','','Bahir Dar','','','','active','2026-08-21 22:36:25','ministry_approved',NULL,NULL,'public',NULL,NULL,'Amhara',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Amharic',NULL,'percentage','daily','ethiopian',NULL,NULL,'free',NULL),(3,2,NULL,1,'Hawassa Preparatory School','HPS','school','university','','Hawassa','','info@hps.edu.et','','active','2026-08-21 22:36:25','ministry_approved',NULL,NULL,'public',NULL,NULL,'South Ethiopia',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Amharic',NULL,'percentage','daily','ethiopian',NULL,NULL,'free',NULL);
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
  `is_demo` tinyint(1) DEFAULT '0',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft|active|closed',
  `description` text COLLATE utf8mb4_unicode_ci,
  `registration_start` date DEFAULT NULL,
  `registration_end` date DEFAULT NULL,
  `teaching_start` date DEFAULT NULL,
  `teaching_end` date DEFAULT NULL,
  `exam_start` date DEFAULT NULL,
  `exam_end` date DEFAULT NULL,
  `result_start` date DEFAULT NULL,
  `result_end` date DEFAULT NULL,
  `vacation_start` date DEFAULT NULL,
  `vacation_end` date DEFAULT NULL,
  `teaching_days` smallint unsigned DEFAULT NULL,
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `year_id` (`year_id`),
  CONSTRAINT `semesters_ibfk_1` FOREIGN KEY (`year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semesters`
--

LOCK TABLES `semesters` WRITE;
/*!40000 ALTER TABLE `semesters` DISABLE KEYS */;
INSERT INTO `semesters` VALUES (2,2,'Semester 1','2025-09-01','2026-01-31',0,'draft',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),(8,5,'sfssf','2026-07-29','2026-07-29',0,'draft',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),(9,7,'Semester I','2026-09-11','2027-01-30',0,'draft',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1),(10,7,'Semester II','2027-02-01','2027-07-30',0,'draft',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2),(14,10,'Semester I','2026-09-11','2027-01-30',0,'draft',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1),(15,10,'Semester II','2027-02-01','2027-07-30',0,'draft',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2),(16,11,'Semester I','2026-09-11','2027-01-30',0,'draft',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1),(17,11,'Semester II','2027-02-01','2027-07-30',0,'draft',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2);
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
INSERT INTO `settings` VALUES ('ai_api_key',''),('ai_api_url',''),('ai_enabled','1'),('ai_model',''),('ai_provider','local'),('announcement',''),('currency','ETB'),('default_language','en'),('default_theme','dark'),('demo_mode','0'),('google_login_enabled','0'),('maintenance_mode','0'),('max_upload_mb','50'),('registration_open','1'),('school_registration_open','1'),('session_timeout_min','120'),('site_name','Edunex'),('site_tagline','AI-Powered Ethiopian Learning Platform'),('support_email','support@edunex.local');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_cards`
--

DROP TABLE IF EXISTS `student_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_cards` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int unsigned NOT NULL,
  `card_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode_data` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `qr_data` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `issued_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` date DEFAULT NULL,
  `status` enum('active','expired','revoked') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_card_num` (`card_number`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `student_cards_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_cards`
--

LOCK TABLES `student_cards` WRITE;
/*!40000 ALTER TABLE `student_cards` DISABLE KEYS */;
INSERT INTO `student_cards` VALUES (1,18,'BDU-2025-CS-0018','','','2026-08-24 11:40:40',NULL,'active','',1),(2,19,'BDU-2025-SE-0019','','','2026-08-24 11:40:40',NULL,'active','',1);
/*!40000 ALTER TABLE `student_cards` ENABLE KEYS */;
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
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `homeroom_teacher_id` (`homeroom_teacher_id`),
  CONSTRAINT `student_groups_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_groups_ibfk_2` FOREIGN KEY (`homeroom_teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_groups`
--

LOCK TABLES `student_groups` WRITE;
/*!40000 ALTER TABLE `student_groups` DISABLE KEYS */;
INSERT INTO `student_groups` VALUES (1,1,'Grade 9-A','9','A',3,0),(2,1,'Grade 10-B','10','B',9,0),(3,2,'CS Year 1','1st','A',NULL,0),(4,3,'Grade 8-C','8','C',NULL,0),(5,1,'Grade 7-A','7','A',NULL,1),(6,1,'Grade 8-B','8','B',NULL,1),(7,1,'Grade 7-A','7','A',NULL,1),(8,1,'Grade 8-B','8','B',NULL,1);
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
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_course` (`course_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `student_notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_notes_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_notes_ibfk_3` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_notes`
--

LOCK TABLES `student_notes` WRITE;
/*!40000 ALTER TABLE `student_notes` DISABLE KEYS */;
INSERT INTO `student_notes` VALUES (1,18,NULL,NULL,'Database Systems Notes','Key concepts: normalization, ACID properties, SQL queries, indexing strategies.',0,'2026-08-24 11:56:57','2026-08-24 11:56:57',1),(2,18,NULL,NULL,'Algorithm Analysis','Big-O notation, divide and conquer, dynamic programming basics.',0,'2026-08-24 11:56:57','2026-08-24 11:56:57',1),(3,19,NULL,NULL,'Web Development Notes','HTML5, CSS3, JavaScript ES6+, React fundamentals.',0,'2026-08-24 11:56:57','2026-08-24 11:56:57',1);
/*!40000 ALTER TABLE `student_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_programs`
--

DROP TABLE IF EXISTS `student_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_programs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int unsigned NOT NULL,
  `program_id` int unsigned NOT NULL,
  `enrolled_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expected_graduation` date DEFAULT NULL,
  `actual_graduation` date DEFAULT NULL,
  `status` enum('active','graduated','transferred','withdrawn','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stu_prog` (`student_id`,`program_id`),
  KEY `program_id` (`program_id`),
  CONSTRAINT `student_programs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_programs_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_programs`
--

LOCK TABLES `student_programs` WRITE;
/*!40000 ALTER TABLE `student_programs` DISABLE KEYS */;
INSERT INTO `student_programs` VALUES (1,18,1,'2026-08-24 11:20:15',NULL,NULL,'active',0),(2,19,1,'2026-08-24 11:20:15',NULL,NULL,'active',0),(3,18,2,'2025-08-31 21:00:00',NULL,NULL,'active',1),(4,19,3,'2025-08-31 21:00:00',NULL,NULL,'active',1);
/*!40000 ALTER TABLE `student_programs` ENABLE KEYS */;
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
-- Table structure for table `theses`
--

DROP TABLE IF EXISTS `theses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `theses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int unsigned NOT NULL,
  `program_id` int unsigned NOT NULL,
  `title` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `abstract` text COLLATE utf8mb4_unicode_ci,
  `advisor_id` int unsigned DEFAULT NULL,
  `status` enum('proposal','in_progress','defense','revision','completed','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'proposal',
  `topic_approved_at` timestamp NULL DEFAULT NULL,
  `defense_date` date DEFAULT NULL,
  `defense_result` enum('pass','fail','revise') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `defense_notes` text COLLATE utf8mb4_unicode_ci,
  `final_submitted_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `program_id` (`program_id`),
  KEY `advisor_id` (`advisor_id`),
  CONSTRAINT `theses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `theses_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `theses_ibfk_3` FOREIGN KEY (`advisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `theses`
--

LOCK TABLES `theses` WRITE;
/*!40000 ALTER TABLE `theses` DISABLE KEYS */;
INSERT INTO `theses` VALUES (1,18,2,'Machine Learning Applications in Healthcare','A comprehensive study of ML techniques applied to medical diagnosis and patient care optimization.',NULL,'proposal',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-24 11:40:29',1),(2,19,3,'Microservices Architecture for Ethiopian Banking Systems','Design and implementation of a scalable microservices platform for digital banking services.',NULL,'in_progress',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-24 11:40:40',1),(3,18,2,'Deep Learning for Amharic Speech Recognition','Development of neural network models for automatic Amharic speech-to-text conversion.',14,'in_progress',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-24 12:16:51',1),(4,19,3,'Scalable Microservices for Ethiopian e-Commerce','Design patterns for building high-traffic e-commerce platforms using microservices architecture.',14,'proposal',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-24 12:16:51',1);
/*!40000 ALTER TABLE `theses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thesis_chapters`
--

DROP TABLE IF EXISTS `thesis_chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesis_chapters` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `thesis_id` int unsigned NOT NULL,
  `chapter_number` int unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` enum('draft','submitted','reviewed','approved') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `feedback_at` timestamp NULL DEFAULT NULL,
  `advisor_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `thesis_id` (`thesis_id`),
  KEY `advisor_id` (`advisor_id`),
  CONSTRAINT `thesis_chapters_ibfk_1` FOREIGN KEY (`thesis_id`) REFERENCES `theses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `thesis_chapters_ibfk_2` FOREIGN KEY (`advisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thesis_chapters`
--

LOCK TABLES `thesis_chapters` WRITE;
/*!40000 ALTER TABLE `thesis_chapters` DISABLE KEYS */;
/*!40000 ALTER TABLE `thesis_chapters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thesis_committee`
--

DROP TABLE IF EXISTS `thesis_committee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesis_committee` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `thesis_id` int unsigned NOT NULL,
  `member_id` int unsigned NOT NULL,
  `role` enum('advisor','co_advisor','examiner') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'examiner',
  `approved_at` timestamp NULL DEFAULT NULL,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `thesis_id` (`thesis_id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `thesis_committee_ibfk_1` FOREIGN KEY (`thesis_id`) REFERENCES `theses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `thesis_committee_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thesis_committee`
--

LOCK TABLES `thesis_committee` WRITE;
/*!40000 ALTER TABLE `thesis_committee` DISABLE KEYS */;
INSERT INTO `thesis_committee` VALUES (1,1,14,'advisor',NULL,1),(2,1,11,'examiner',NULL,1),(3,3,14,'advisor',NULL,1),(4,3,11,'examiner',NULL,1),(5,2,14,'advisor',NULL,1),(6,2,14,'advisor',NULL,1);
/*!40000 ALTER TABLE `thesis_committee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transcript_requests`
--

DROP TABLE IF EXISTS `transcript_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transcript_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int unsigned NOT NULL,
  `type` enum('official','unofficial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unofficial',
  `status` enum('pending','processing','ready','delivered') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` int unsigned DEFAULT NULL,
  `is_demo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `transcript_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transcript_requests_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transcript_requests`
--

LOCK TABLES `transcript_requests` WRITE;
/*!40000 ALTER TABLE `transcript_requests` DISABLE KEYS */;
INSERT INTO `transcript_requests` VALUES (1,18,'unofficial','pending','','','2026-08-24 11:56:24',NULL,NULL,1);
/*!40000 ALTER TABLE `transcript_requests` ENABLE KEYS */;
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
INSERT INTO `transfer_codes` VALUES (1,'TRF-AAIS-0001',1,NULL,'referral',0,'2026-11-20 01:36:25','2026-08-21 22:36:25'),(2,'TRF-BDU-0001',2,NULL,'referral',0,'2026-11-20 01:36:25','2026-08-21 22:36:25'),(3,'TRF-HPS-0001',3,NULL,'referral',0,'2026-11-20 01:36:25','2026-08-21 22:36:25'),(5,'TRF-C537-2103',2,NULL,'referral',0,'2026-11-28 00:00:00','2026-08-30 16:27:20');
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
  `is_demo` tinyint(1) DEFAULT '0',
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
  `is_demo` tinyint(1) DEFAULT '0',
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
INSERT INTO `user_badges` VALUES (4,1,'2026-08-21 22:36:25',0),(4,2,'2026-08-21 22:36:25',0),(18,17,'2026-09-26 07:00:00',1),(18,19,'2026-09-28 11:00:00',1),(18,20,'2026-10-01 06:00:00',1),(19,17,'2026-08-20 05:00:00',1),(19,20,'2026-10-15 07:00:00',1);
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
  `role` enum('ministry','regional','zonal','woreda','principal','hod','teacher','student','parent','registrar','dean','vice_dean','lecturer','bursar','student_affairs','librarian','school_board','guest','it_admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
  `first_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `student_id` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `language` enum('en','am','om','ti','so') COLLATE utf8mb4_unicode_ci DEFAULT 'en',
  `theme` enum('dark','light') COLLATE utf8mb4_unicode_ci DEFAULT 'dark',
  `accent_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'teal',
  `font_size` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT '14',
  `sidebar_style` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'default',
  `content_width` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT '1400',
  `card_radius` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT '18',
  `line_height` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT '1.55',
  `toast_position` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'top-right',
  `table_density` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `show_animations` tinyint(1) DEFAULT '1',
  `compact_mode` tinyint(1) DEFAULT '0',
  `show_avatars` tinyint(1) DEFAULT '1',
  `show_borders` tinyint(1) DEFAULT '1',
  `show_gradients` tinyint(1) DEFAULT '1',
  `blur_effects` tinyint(1) DEFAULT '1',
  `reduce_motion` tinyint(1) DEFAULT '0',
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
  `is_demo` tinyint(1) NOT NULL DEFAULT '0',
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `national_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fayda_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_cert_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kebele` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `woreda_id` int unsigned DEFAULT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  `region` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_region` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_zone` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_woreda` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `qualification` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialization` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certification` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience_years` int DEFAULT NULL,
  `employment_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `position` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `student_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_school` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_grade` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enrollment_date` date DEFAULT NULL,
  `disability_support` tinyint(1) DEFAULT '0',
  `special_needs` text COLLATE utf8mb4_unicode_ci,
  `relationship` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linked_student_ids` text COLLATE utf8mb4_unicode_ci,
  `notification_prefs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `temp_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twofa_required` tinyint(1) DEFAULT '0',
  `permissions` text COLLATE utf8mb4_unicode_ci,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_specialization` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade_levels` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sections` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'ministry','Super','Admin','superadmin@edunex.local','',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','light','teal','14','default','1400','2','1.55','top-right','normal',1,1,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,0,1,0,NULL,'2026-08-31 12:38:34','active',1,NULL,'2026-08-21 22:36:25',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(2,1,'regional','Sara','Tesfaye','admin@edunex.local','+251911000001',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,1,NULL,NULL,NULL,1,NULL,'2026-08-22 01:36:25','active','',0,0,5000,8,12,NULL,'2026-09-01 11:31:03','active',1,NULL,'2026-08-21 22:36:25',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(3,1,'teacher','David','Alemu','teacher@edunex.local','+251911000002',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,1,NULL,NULL,NULL,1,NULL,'2026-08-22 01:36:25','active','',0,0,2500,6,5,NULL,'2026-08-22 01:36:25','active',1,NULL,'2026-08-21 22:36:25',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(4,1,'student','Liya','Girma','student@edunex.local','+251911000003','AAIS-2026-000001','$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,1,5,1,NULL,NULL,NULL,1,3,'2026-08-22 01:36:25','active','',0,0,340,5,5,NULL,'2026-08-29 13:18:22','active',1,NULL,'2026-08-21 22:36:25',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(5,1,'parent','Hana','Girma','parent@edunex.local','+251911000004',NULL,'$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,'2026-08-22 01:36:25','active','',0,0,100,1,0,NULL,'2026-08-22 01:36:25','active',1,NULL,'2026-08-21 22:36:25',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(6,2,'regional','Kebede','Hailu','admin2@edunex.local','+251911000005',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','am','light','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,3,NULL,NULL,NULL,1,NULL,'2026-08-22 01:36:25','active','',0,0,300,2,1,NULL,'2026-08-28 04:02:41','active',1,NULL,'2026-08-21 22:36:25',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(7,3,'teacher','Meron','Tesfa','teacher3@edunex.local','+251911000006',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','light','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,5,NULL,NULL,NULL,1,NULL,'2026-08-22 01:36:25','active','',0,0,800,3,2,NULL,'2026-08-22 01:36:25','active',1,NULL,'2026-08-21 22:36:25',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(8,1,'principal','Dir','One','director@edunex.local','+251911000007',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','avatars/20260823_082726_26f7a07876f4.jpeg','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,1,'',NULL,NULL,1,NULL,'2026-08-22 01:36:25','active','',0,0,1000,3,1,NULL,'2026-08-23 12:24:29','active',1,NULL,'2026-08-21 22:36:25',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(9,1,'teacher','Henok','Arega','test@test.com','',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,1,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,0,1,0,NULL,NULL,'active',0,NULL,'2026-08-23 05:48:54',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(10,3,'registrar','Abebe','Kebede','registrar@bdu.edu.et','+251911001001',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,0,1,0,NULL,'2026-08-24 16:53:07','active',0,NULL,'2026-08-24 11:02:38',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(11,3,'dean','Dr. Alem','Bekele','dean@bdu.edu.et','+251911001002',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,0,1,0,NULL,'2026-08-24 16:53:07','active',0,NULL,'2026-08-24 11:02:38',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(12,2,'vice_dean','Marta','Tadesse','vice_dean@bdu.edu.et','+251911001003',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,0,1,0,NULL,NULL,'active',0,NULL,'2026-08-24 11:02:38',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(13,2,'hod','Tesfaye','Girma','depthead@bdu.edu.et','+251911001004',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,0,1,0,NULL,NULL,'active',0,NULL,'2026-08-24 11:02:38',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(14,3,'lecturer','Yonas','Damtew','lecturer@bdu.edu.et','+251911001005',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,0,1,0,NULL,'2026-08-24 16:53:06','active',0,NULL,'2026-08-24 11:02:38',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(15,3,'bursar','Fatima','Ali','bursar@bdu.edu.et','+251911001006',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,0,1,0,NULL,'2026-08-24 16:53:06','active',0,NULL,'2026-08-24 11:02:38',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(16,3,'student_affairs','Samuel','Worku','studentaffairs@bdu.edu.et','+251911001007',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,0,1,0,NULL,'2026-08-24 16:53:07','active',0,NULL,'2026-08-24 11:02:38',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(17,3,'librarian','Hana','Tesfa','librarian@bdu.edu.et','+251911001008',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,0,1,0,NULL,'2026-08-24 16:53:06','active',0,NULL,'2026-08-24 11:02:38',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(18,3,'student','Liya','Girma','liya@bdu.edu.et','+251911001009',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,1250,12,18,NULL,'2026-08-24 16:12:03','active',0,NULL,'2026-08-24 11:02:38',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(19,3,'student','Daniel','Abebe','daniel@bdu.edu.et','+251911001010',NULL,'$2y$12$2JQEA7/vO.w6x8SmJqL7q.N3qGadSvbmyMhKKCfgnyam5cXGcBtya','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'active','',0,0,980,10,14,NULL,'2026-08-25 00:37:04','active',0,NULL,'2026-08-24 11:02:38',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(20,NULL,'zonal','Zone','Admin','zone@edunex.com','',NULL,'$2y$12$zZJUe8J9JYKSwjH8wsVKwuo.VkKnh.vYuCJcwTA6gPA0jlkWKJDk.','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,'active','',0,0,0,1,0,NULL,'2026-08-25 20:48:08','active',0,NULL,'2026-08-25 08:07:23',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(21,NULL,'woreda','Woreda','Admin','woreda@edunex.com','',NULL,'$2y$12$H4dvLgxCh0Yo2AzCcebNLeuNMvTX/ro/sduWikysqXQDqP3Ko2QIK','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,'active','',0,0,0,1,0,NULL,'2026-08-25 20:48:08','active',0,NULL,'2026-08-25 08:07:23',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(22,3,'it_admin','IT','Admin','itadmin@bdu.edu.et','+251911000020',NULL,'$2y$12$/ata4R4ENoVDo8fhl8MtyuDVNOmMMpyirU5hjgDLd/A0MZh7SOzVK','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,'active','',0,0,0,1,0,NULL,'2026-08-25 21:57:05','active',0,NULL,'2026-08-25 11:53:05',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(23,1,'it_admin','IT','Admin','itadmin@school1.edu.et','+251911000021',NULL,'$2y$12$/ata4R4ENoVDo8fhl8MtyuDVNOmMMpyirU5hjgDLd/A0MZh7SOzVK','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,'active','',0,0,0,1,0,NULL,NULL,'active',0,NULL,'2026-08-25 11:53:05',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(25,2,'student','Test','UniStudent','testuni@edunex.local','',NULL,'$2y$12$84ruPrxsRdgDlom13kpjH.Le7pj228gE5iIgfLvsDfXoXgMhqWdMu','','en','dark','teal','14','default','1400','18','1.55','top-right','normal',1,0,1,1,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,'active','',0,0,0,1,0,NULL,'2026-08-28 03:39:33','active',0,NULL,'2026-08-28 00:39:18',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'regular',NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `woredas`
--

DROP TABLE IF EXISTS `woredas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `woredas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone_id` int unsigned NOT NULL,
  `admin_id` int unsigned DEFAULT NULL,
  `status` enum('active','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `zone_id` (`zone_id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `woredas_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `woredas_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1269 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `woredas`
--

LOCK TABLES `woredas` WRITE;
/*!40000 ALTER TABLE `woredas` DISABLE KEYS */;
INSERT INTO `woredas` VALUES (121,'Tahtay Adiyabo',2,NULL,'active','2026-08-27 09:33:45'),(122,'Zana',2,NULL,'active','2026-08-27 09:33:45'),(123,'Tahtay Koraro',2,NULL,'active','2026-08-27 09:33:45'),(124,'Asgede',2,NULL,'active','2026-08-27 09:33:45'),(125,'Sheraro town',2,NULL,'active','2026-08-27 09:33:45'),(126,'Shire Endaslasie town',2,NULL,'active','2026-08-27 09:33:45'),(127,'Laelay Koraro',2,NULL,'active','2026-08-27 09:33:45'),(128,'Seyemti Adyabo',2,NULL,'active','2026-08-27 09:33:45'),(129,'Adi Daero',2,NULL,'active','2026-08-27 09:33:45'),(130,'Maekel Adiyabo',2,NULL,'active','2026-08-27 09:33:45'),(131,'Tsimbla',2,NULL,'active','2026-08-27 09:33:45'),(132,'Endabaguna town',2,NULL,'active','2026-08-27 09:33:45'),(133,'Chila',3,NULL,'active','2026-08-27 09:33:45'),(134,'Aheferom',3,NULL,'active','2026-08-27 09:33:45'),(135,'Endaba Tsahma',3,NULL,'active','2026-08-27 09:33:45'),(136,'Adwa',3,NULL,'active','2026-08-27 09:33:45'),(137,'Laelay Maychew',3,NULL,'active','2026-08-27 09:33:45'),(138,'Tahtay Maychew',3,NULL,'active','2026-08-27 09:33:45'),(139,'Adet',3,NULL,'active','2026-08-27 09:33:45'),(140,'Kola Tembien',3,NULL,'active','2026-08-27 09:33:45'),(141,'Naeder',3,NULL,'active','2026-08-27 09:33:45'),(142,'Abergele (TG)',3,NULL,'active','2026-08-27 09:33:45'),(143,'Abiy Adi town',3,NULL,'active','2026-08-27 09:33:45'),(144,'Adwa town',3,NULL,'active','2026-08-27 09:33:45'),(145,'Axum town',3,NULL,'active','2026-08-27 09:33:45'),(146,'Rama Adi Arbaete',3,NULL,'active','2026-08-27 09:33:45'),(147,'Ahsea',3,NULL,'active','2026-08-27 09:33:45'),(148,'Egela',3,NULL,'active','2026-08-27 09:33:45'),(149,'Hahayle',3,NULL,'active','2026-08-27 09:33:45'),(150,'Endafelasi',3,NULL,'active','2026-08-27 09:33:45'),(151,'Emba Sieneti',3,NULL,'active','2026-08-27 09:33:45'),(152,'Enticho town',3,NULL,'active','2026-08-27 09:33:45'),(153,'Keyhe tekli',3,NULL,'active','2026-08-27 09:33:45'),(154,'Tanqua Melashe',3,NULL,'active','2026-08-27 09:33:45'),(155,'Gulo Mekeda',4,NULL,'active','2026-08-27 09:33:45'),(156,'Erob',4,NULL,'active','2026-08-27 09:33:45'),(157,'Sebuha Saesie',4,NULL,'active','2026-08-27 09:33:45'),(158,'Ganta Afeshum',4,NULL,'active','2026-08-27 09:33:45'),(159,'Hawzen',4,NULL,'active','2026-08-27 09:33:45'),(160,'Kilte Awulaelo',4,NULL,'active','2026-08-27 09:33:45'),(161,'Atsbi',4,NULL,'active','2026-08-27 09:33:45'),(162,'Adigrat town',4,NULL,'active','2026-08-27 09:33:45'),(163,'Wukro town',4,NULL,'active','2026-08-27 09:33:45'),(164,'Bizet',4,NULL,'active','2026-08-27 09:33:45'),(165,'Edaga Hamus town',4,NULL,'active','2026-08-27 09:33:45'),(166,'Hawzen town',4,NULL,'active','2026-08-27 09:33:45'),(167,'Freweyni town',4,NULL,'active','2026-08-27 09:33:45'),(168,'Tsaeda Emba',4,NULL,'active','2026-08-27 09:33:45'),(169,'Atsbi town',4,NULL,'active','2026-08-27 09:33:45'),(170,'Agulae',4,NULL,'active','2026-08-27 09:33:45'),(171,'Geraleta',4,NULL,'active','2026-08-27 09:33:45'),(172,'Zala Anbesa town',4,NULL,'active','2026-08-27 09:33:45'),(173,'Selewa',5,NULL,'active','2026-08-27 09:33:46'),(174,'Bora (TG)',5,NULL,'active','2026-08-27 09:33:46'),(175,'Neqsege',5,NULL,'active','2026-08-27 09:33:46'),(176,'Emba Alaje',5,NULL,'active','2026-08-27 09:33:46'),(177,'Endamehoni',5,NULL,'active','2026-08-27 09:33:46'),(178,'Raya Azebo',5,NULL,'active','2026-08-27 09:33:46'),(179,'Maichew town',5,NULL,'active','2026-08-27 09:33:46'),(180,'Mekhoni town',5,NULL,'active','2026-08-27 09:33:46'),(181,'Saharti',6,NULL,'active','2026-08-27 09:33:46'),(182,'Enderta',6,NULL,'active','2026-08-27 09:33:46'),(183,'Hintalo',6,NULL,'active','2026-08-27 09:33:46'),(184,'Degua Temben',6,NULL,'active','2026-08-27 09:33:46'),(185,'Hagere Selam town',6,NULL,'active','2026-08-27 09:33:46'),(186,'Samre',6,NULL,'active','2026-08-27 09:33:46'),(187,'Adigudom',6,NULL,'active','2026-08-27 09:33:46'),(188,'Wajirat',6,NULL,'active','2026-08-27 09:33:46'),(189,'Kuiha Sub City',7,NULL,'active','2026-08-27 09:33:46'),(190,'Hadnet Sub City',7,NULL,'active','2026-08-27 09:33:46'),(191,'Ayder Sub City',7,NULL,'active','2026-08-27 09:33:46'),(192,'Hawelti Sub City',7,NULL,'active','2026-08-27 09:33:46'),(193,'Adihaki Sub City',7,NULL,'active','2026-08-27 09:33:46'),(194,'Qedamay Weyane Sub City',7,NULL,'active','2026-08-27 09:33:46'),(195,'Semen Sub City',7,NULL,'active','2026-08-27 09:33:46'),(196,'Dubti',8,NULL,'active','2026-08-27 09:33:46'),(197,'Elidar',8,NULL,'active','2026-08-27 09:33:46'),(198,'Asayita',8,NULL,'active','2026-08-27 09:33:46'),(199,'Afambo',8,NULL,'active','2026-08-27 09:33:46'),(200,'Mile',8,NULL,'active','2026-08-27 09:33:46'),(201,'Chifra',8,NULL,'active','2026-08-27 09:33:46'),(202,'Dubti town',8,NULL,'active','2026-08-27 09:33:46'),(203,'Kori',8,NULL,'active','2026-08-27 09:33:46'),(204,'Adar',8,NULL,'active','2026-08-27 09:33:46'),(205,'Asayita town',8,NULL,'active','2026-08-27 09:33:46'),(206,'Mile town',8,NULL,'active','2026-08-27 09:33:46'),(207,'Chifra town',8,NULL,'active','2026-08-27 09:33:46'),(208,'Samera Logiya town',8,NULL,'active','2026-08-27 09:33:46'),(209,'Erebti',9,NULL,'active','2026-08-27 09:33:46'),(210,'Kunneba',9,NULL,'active','2026-08-27 09:33:46'),(211,'Abaala',9,NULL,'active','2026-08-27 09:33:46'),(212,'Megale',9,NULL,'active','2026-08-27 09:33:46'),(213,'Berahile',9,NULL,'active','2026-08-27 09:33:46'),(214,'Dalol',9,NULL,'active','2026-08-27 09:33:46'),(215,'Afdera',9,NULL,'active','2026-08-27 09:33:46'),(216,'Bidu',9,NULL,'active','2026-08-27 09:33:46'),(217,'Abaala town',9,NULL,'active','2026-08-27 09:33:46'),(218,'Wasama',9,NULL,'active','2026-08-27 09:33:46'),(219,'Amibara',10,NULL,'active','2026-08-27 09:33:46'),(220,'Awash Fantale',10,NULL,'active','2026-08-27 09:33:46'),(221,'Gewane',10,NULL,'active','2026-08-27 09:33:46'),(222,'Dulecha',10,NULL,'active','2026-08-27 09:33:46'),(223,'Gala\'alu',10,NULL,'active','2026-08-27 09:33:46'),(224,'Arguba',10,NULL,'active','2026-08-27 09:33:46'),(225,'Hanruka',10,NULL,'active','2026-08-27 09:33:46'),(226,'Abida',10,NULL,'active','2026-08-27 09:33:46'),(227,'Borimodaytu',10,NULL,'active','2026-08-27 09:33:46'),(228,'Werer',10,NULL,'active','2026-08-27 09:33:46'),(229,'Awash town',10,NULL,'active','2026-08-27 09:33:46'),(230,'Awra (AF)',11,NULL,'active','2026-08-27 09:33:46'),(231,'Euwa',11,NULL,'active','2026-08-27 09:33:46'),(232,'Teru',11,NULL,'active','2026-08-27 09:33:46'),(233,'Yalo',11,NULL,'active','2026-08-27 09:33:46'),(234,'Gulina',11,NULL,'active','2026-08-27 09:33:46'),(235,'Mabay',11,NULL,'active','2026-08-27 09:33:46'),(236,'Telalek',12,NULL,'active','2026-08-27 09:33:46'),(237,'Samurobi',12,NULL,'active','2026-08-27 09:33:46'),(238,'Dawe',12,NULL,'active','2026-08-27 09:33:46'),(239,'Dalefage',12,NULL,'active','2026-08-27 09:33:46'),(240,'Hadelela',12,NULL,'active','2026-08-27 09:33:46'),(241,'Gerani',13,NULL,'active','2026-08-27 09:33:46'),(242,'Kilalu',13,NULL,'active','2026-08-27 09:33:46'),(243,'Yangudi',13,NULL,'active','2026-08-27 09:33:46'),(244,'Sibaybi',13,NULL,'active','2026-08-27 09:33:46'),(245,'Addi Arekay',14,NULL,'active','2026-08-27 09:33:46'),(246,'Beyeda',14,NULL,'active','2026-08-27 09:33:46'),(247,'Janamora',14,NULL,'active','2026-08-27 09:33:46'),(248,'Debark',14,NULL,'active','2026-08-27 09:33:46'),(249,'Dabat',14,NULL,'active','2026-08-27 09:33:46'),(250,'Dabat town',14,NULL,'active','2026-08-27 09:33:46'),(251,'Telemt',14,NULL,'active','2026-08-27 09:33:46'),(252,'Debark town',14,NULL,'active','2026-08-27 09:33:46'),(253,'Ebenat',15,NULL,'active','2026-08-27 09:33:46'),(254,'Libokemekem',15,NULL,'active','2026-08-27 09:33:46'),(255,'Fogera',15,NULL,'active','2026-08-27 09:33:46'),(256,'Farta',15,NULL,'active','2026-08-27 09:33:46'),(257,'Lay Gayint',15,NULL,'active','2026-08-27 09:33:46'),(258,'Tach Gayint',15,NULL,'active','2026-08-27 09:33:46'),(259,'Semada',15,NULL,'active','2026-08-27 09:33:46'),(260,'East Esite',15,NULL,'active','2026-08-27 09:33:46'),(261,'Dera (AM)',15,NULL,'active','2026-08-27 09:33:46'),(262,'Debre Tabor town',15,NULL,'active','2026-08-27 09:33:46'),(263,'Andabet /West Esite',15,NULL,'active','2026-08-27 09:33:46'),(264,'Guna Begemider',15,NULL,'active','2026-08-27 09:33:47'),(265,'Mena Meketewa',15,NULL,'active','2026-08-27 09:33:47'),(266,'Sede Muja',15,NULL,'active','2026-08-27 09:33:47'),(267,'Nefas Mewicha town',15,NULL,'active','2026-08-27 09:33:47'),(268,'Wegeda town',15,NULL,'active','2026-08-27 09:33:47'),(269,'Ebenat town',15,NULL,'active','2026-08-27 09:33:47'),(270,'Hamusit town',15,NULL,'active','2026-08-27 09:33:47'),(271,'Woreta town',15,NULL,'active','2026-08-27 09:33:47'),(272,'Mekan Eyesuse',15,NULL,'active','2026-08-27 09:33:47'),(273,'Adiss Zemen town',15,NULL,'active','2026-08-27 09:33:47'),(274,'Bugna',16,NULL,'active','2026-08-27 09:33:47'),(275,'Raya Kobo',16,NULL,'active','2026-08-27 09:33:47'),(276,'Gidan',16,NULL,'active','2026-08-27 09:33:47'),(277,'Meket',16,NULL,'active','2026-08-27 09:33:47'),(278,'Wadla',16,NULL,'active','2026-08-27 09:33:47'),(279,'Hara town',16,NULL,'active','2026-08-27 09:33:47'),(280,'Guba Lafto',16,NULL,'active','2026-08-27 09:33:47'),(281,'Habru',16,NULL,'active','2026-08-27 09:33:47'),(282,'Woldiya town',16,NULL,'active','2026-08-27 09:33:47'),(283,'Lasta',16,NULL,'active','2026-08-27 09:33:47'),(284,'Dawunt',16,NULL,'active','2026-08-27 09:33:47'),(285,'Gazo',16,NULL,'active','2026-08-27 09:33:47'),(286,'Angot',16,NULL,'active','2026-08-27 09:33:47'),(287,'Filakit town',16,NULL,'active','2026-08-27 09:33:47'),(288,'Gashena town',16,NULL,'active','2026-08-27 09:33:47'),(289,'Mersa town',16,NULL,'active','2026-08-27 09:33:47'),(290,'Lalibela town',16,NULL,'active','2026-08-27 09:33:47'),(291,'Kobo town',16,NULL,'active','2026-08-27 09:33:47'),(292,'Argoba',17,NULL,'active','2026-08-27 09:33:47'),(293,'Tenta',17,NULL,'active','2026-08-27 09:33:47'),(294,'Kutaber',17,NULL,'active','2026-08-27 09:33:47'),(295,'Ambasel',17,NULL,'active','2026-08-27 09:33:47'),(296,'Thehulederie',17,NULL,'active','2026-08-27 09:33:47'),(297,'Delanta',17,NULL,'active','2026-08-27 09:33:47'),(298,'Kalu',17,NULL,'active','2026-08-27 09:33:47'),(299,'Albuko',17,NULL,'active','2026-08-27 09:33:47'),(300,'Dessie Zuria',17,NULL,'active','2026-08-27 09:33:47'),(301,'Legambo',17,NULL,'active','2026-08-27 09:33:47'),(302,'Sayint',17,NULL,'active','2026-08-27 09:33:47'),(303,'Borena /Debresina',17,NULL,'active','2026-08-27 09:33:47'),(304,'Kelela',17,NULL,'active','2026-08-27 09:33:47'),(305,'Jama',17,NULL,'active','2026-08-27 09:33:47'),(306,'Were Ilu',17,NULL,'active','2026-08-27 09:33:47'),(307,'Wegde',17,NULL,'active','2026-08-27 09:33:47'),(308,'Kombolcha town',17,NULL,'active','2026-08-27 09:33:47'),(309,'Dessie town',17,NULL,'active','2026-08-27 09:33:47'),(310,'Mehal Sayint',17,NULL,'active','2026-08-27 09:33:47'),(311,'Legehida',17,NULL,'active','2026-08-27 09:33:47'),(312,'Mekdela',17,NULL,'active','2026-08-27 09:33:47'),(313,'Worebabu',17,NULL,'active','2026-08-27 09:33:47'),(314,'Wereilu town',17,NULL,'active','2026-08-27 09:33:47'),(315,'Wegel tena town',17,NULL,'active','2026-08-27 09:33:47'),(316,'Degolo town',17,NULL,'active','2026-08-27 09:33:47'),(317,'Kelala town',17,NULL,'active','2026-08-27 09:33:47'),(318,'Akeseta town',17,NULL,'active','2026-08-27 09:33:47'),(319,'Gimeba town',17,NULL,'active','2026-08-27 09:33:47'),(320,'Harbu town',17,NULL,'active','2026-08-27 09:33:47'),(321,'Wegedi town',17,NULL,'active','2026-08-27 09:33:47'),(322,'Mekane Selam town',17,NULL,'active','2026-08-27 09:33:47'),(323,'Hike town',17,NULL,'active','2026-08-27 09:33:47'),(324,'Mida Woremo',18,NULL,'active','2026-08-27 09:33:47'),(325,'Merhabete',18,NULL,'active','2026-08-27 09:33:47'),(326,'Ensaro',18,NULL,'active','2026-08-27 09:33:47'),(327,'Moretna Jiru',18,NULL,'active','2026-08-27 09:33:47'),(328,'Menze Gera Midir',18,NULL,'active','2026-08-27 09:33:47'),(329,'Gishe Rabel',18,NULL,'active','2026-08-27 09:33:47'),(330,'Antsokiya',18,NULL,'active','2026-08-27 09:33:47'),(331,'Eferatana Gidem',18,NULL,'active','2026-08-27 09:33:47'),(332,'Menze Mama Midir',18,NULL,'active','2026-08-27 09:33:47'),(333,'Tarema Ber',18,NULL,'active','2026-08-27 09:33:47'),(334,'Mojan Wedera',18,NULL,'active','2026-08-27 09:33:47'),(335,'Kewet',18,NULL,'active','2026-08-27 09:33:47'),(336,'Angolelana Tera',18,NULL,'active','2026-08-27 09:33:47'),(337,'Assagirt',18,NULL,'active','2026-08-27 09:33:47'),(338,'Ankober',18,NULL,'active','2026-08-27 09:33:47'),(339,'Hagere Mariam',18,NULL,'active','2026-08-27 09:33:47'),(340,'Berehet',18,NULL,'active','2026-08-27 09:33:47'),(341,'Minjar Shenkora',18,NULL,'active','2026-08-27 09:33:47'),(342,'Basona Worena',18,NULL,'active','2026-08-27 09:33:47'),(343,'Debre Berhan town',18,NULL,'active','2026-08-27 09:33:47'),(344,'Menze Keya Gabriel',18,NULL,'active','2026-08-27 09:33:47'),(345,'Menze Lalo Midir',18,NULL,'active','2026-08-27 09:33:47'),(346,'Siya Debirna Wayu',18,NULL,'active','2026-08-27 09:33:47'),(347,'Tulefa town',18,NULL,'active','2026-08-27 09:33:47'),(348,'Arerti town',18,NULL,'active','2026-08-27 09:33:47'),(349,'Molale town',18,NULL,'active','2026-08-27 09:33:47'),(350,'Debre Sina town',18,NULL,'active','2026-08-27 09:33:47'),(351,'Enewari town',18,NULL,'active','2026-08-27 09:33:47'),(352,'Shoa Robit',18,NULL,'active','2026-08-27 09:33:47'),(353,'Mehal Meda town',18,NULL,'active','2026-08-27 09:33:47'),(354,'Ataye town',18,NULL,'active','2026-08-27 09:33:47'),(355,'Aleme Ketma town',18,NULL,'active','2026-08-27 09:33:47'),(356,'Bibugn',19,NULL,'active','2026-08-27 09:33:47'),(357,'Hulet Ej Enese',19,NULL,'active','2026-08-27 09:33:47'),(358,'Goncha Siso Enebse',19,NULL,'active','2026-08-27 09:33:47'),(359,'Enebse Sarmder',19,NULL,'active','2026-08-27 09:33:47'),(360,'Enarj Enawga',19,NULL,'active','2026-08-27 09:33:47'),(361,'Enemay',19,NULL,'active','2026-08-27 09:33:48'),(362,'Debay Telatgen',19,NULL,'active','2026-08-27 09:33:48'),(363,'Debre Elias',19,NULL,'active','2026-08-27 09:33:48'),(364,'Michakel',19,NULL,'active','2026-08-27 09:33:48'),(365,'Guzamn',19,NULL,'active','2026-08-27 09:33:48'),(366,'Baso Liben',19,NULL,'active','2026-08-27 09:33:48'),(367,'Awabel',19,NULL,'active','2026-08-27 09:33:48'),(368,'Dejen',19,NULL,'active','2026-08-27 09:33:48'),(369,'Shebel Bernta',19,NULL,'active','2026-08-27 09:33:48'),(370,'Debre Markos town',19,NULL,'active','2026-08-27 09:33:48'),(371,'Senan',19,NULL,'active','2026-08-27 09:33:48'),(372,'Aneded',19,NULL,'active','2026-08-27 09:33:48'),(373,'Amanuel town',19,NULL,'active','2026-08-27 09:33:48'),(374,'Sedae',19,NULL,'active','2026-08-27 09:33:48'),(375,'Gundwoin town',19,NULL,'active','2026-08-27 09:33:48'),(376,'Debrework town',19,NULL,'active','2026-08-27 09:33:48'),(377,'Lumame town',19,NULL,'active','2026-08-27 09:33:48'),(378,'Merto Lemariyam town',19,NULL,'active','2026-08-27 09:33:48'),(379,'Mota town',19,NULL,'active','2026-08-27 09:33:48'),(380,'Dejen town',19,NULL,'active','2026-08-27 09:33:48'),(381,'Bechena town',19,NULL,'active','2026-08-27 09:33:48'),(382,'Sekela',20,NULL,'active','2026-08-27 09:33:48'),(383,'Quarit',20,NULL,'active','2026-08-27 09:33:48'),(384,'Dega Damot',20,NULL,'active','2026-08-27 09:33:48'),(385,'Dembecha',20,NULL,'active','2026-08-27 09:33:48'),(386,'Jabi Tehnan',20,NULL,'active','2026-08-27 09:33:48'),(387,'Bure (AM)',20,NULL,'active','2026-08-27 09:33:48'),(388,'Wemberma',20,NULL,'active','2026-08-27 09:33:48'),(389,'Finote Selam town',20,NULL,'active','2026-08-27 09:33:48'),(390,'Shendi town',20,NULL,'active','2026-08-27 09:33:48'),(391,'Denbecha town',20,NULL,'active','2026-08-27 09:33:48'),(392,'Jiga town',20,NULL,'active','2026-08-27 09:33:48'),(393,'Bure town',20,NULL,'active','2026-08-27 09:33:48'),(394,'Zequala',21,NULL,'active','2026-08-27 09:33:48'),(395,'Sekota',21,NULL,'active','2026-08-27 09:33:48'),(396,'Dehana',21,NULL,'active','2026-08-27 09:33:48'),(397,'Gaz Gibla',21,NULL,'active','2026-08-27 09:33:48'),(398,'Abergele (AM)',21,NULL,'active','2026-08-27 09:33:48'),(399,'Sahila',21,NULL,'active','2026-08-27 09:33:48'),(400,'Sekota town',21,NULL,'active','2026-08-27 09:33:48'),(401,'Tsagbeji',21,NULL,'active','2026-08-27 09:33:48'),(402,'Amde Work town',21,NULL,'active','2026-08-27 09:33:48'),(403,'Dangila',22,NULL,'active','2026-08-27 09:33:48'),(404,'Banja',22,NULL,'active','2026-08-27 09:33:48'),(405,'Ankasha',22,NULL,'active','2026-08-27 09:33:48'),(406,'Guangua',22,NULL,'active','2026-08-27 09:33:48'),(407,'Fagta Lakoma',22,NULL,'active','2026-08-27 09:33:48'),(408,'Jawi',22,NULL,'active','2026-08-27 09:33:48'),(409,'Guagusa Shikudad',22,NULL,'active','2026-08-27 09:33:48'),(410,'Ayehu Guwagusa',22,NULL,'active','2026-08-27 09:33:48'),(411,'Agew Gimija Bet town',22,NULL,'active','2026-08-27 09:33:48'),(412,'Adiss Kidame town',22,NULL,'active','2026-08-27 09:33:48'),(413,'Tilili town',22,NULL,'active','2026-08-27 09:33:48'),(414,'Fendika town',22,NULL,'active','2026-08-27 09:33:48'),(415,'Injibara town',22,NULL,'active','2026-08-27 09:33:48'),(416,'Zigem town',22,NULL,'active','2026-08-27 09:33:48'),(417,'Dangila town',22,NULL,'active','2026-08-27 09:33:48'),(418,'Chagni town',22,NULL,'active','2026-08-27 09:33:48'),(419,'Dewa Cheffa',23,NULL,'active','2026-08-27 09:33:48'),(420,'Bati',23,NULL,'active','2026-08-27 09:33:48'),(421,'Jilye Tumuga',23,NULL,'active','2026-08-27 09:33:48'),(422,'Artuma Fursi',23,NULL,'active','2026-08-27 09:33:48'),(423,'Dewa Harewa',23,NULL,'active','2026-08-27 09:33:48'),(424,'Kemisie town',23,NULL,'active','2026-08-27 09:33:48'),(425,'Chef Robit town',23,NULL,'active','2026-08-27 09:33:48'),(426,'Senbete town',23,NULL,'active','2026-08-27 09:33:48'),(427,'Bati town',23,NULL,'active','2026-08-27 09:33:48'),(428,'Amba Giorgis town',24,NULL,'active','2026-08-27 09:33:48'),(429,'Shawra town',24,NULL,'active','2026-08-27 09:33:48'),(430,'Kolla Debba town',24,NULL,'active','2026-08-27 09:33:48'),(431,'Chilga 1',24,NULL,'active','2026-08-27 09:33:48'),(432,'Tegede',24,NULL,'active','2026-08-27 09:33:48'),(433,'Lay Armacho',24,NULL,'active','2026-08-27 09:33:48'),(434,'Wegera',24,NULL,'active','2026-08-27 09:33:48'),(435,'Gonder Zuria',24,NULL,'active','2026-08-27 09:33:48'),(436,'East Dembia',24,NULL,'active','2026-08-27 09:33:48'),(437,'Chilga 2',24,NULL,'active','2026-08-27 09:33:48'),(438,'Alefa',24,NULL,'active','2026-08-27 09:33:48'),(439,'West Belesa',24,NULL,'active','2026-08-27 09:33:48'),(440,'East Belesa',24,NULL,'active','2026-08-27 09:33:48'),(441,'Gondar town',24,NULL,'active','2026-08-27 09:33:48'),(442,'Masero Denb /Central Armacho',24,NULL,'active','2026-08-27 09:33:48'),(443,'Tach Armacho',24,NULL,'active','2026-08-27 09:33:48'),(444,'Takusa',24,NULL,'active','2026-08-27 09:33:48'),(445,'Kinfaz Begela',24,NULL,'active','2026-08-27 09:33:48'),(446,'West Dembiya',24,NULL,'active','2026-08-27 09:33:48'),(447,'Aykel town',24,NULL,'active','2026-08-27 09:33:48'),(448,'Adagn Ager Chaqo',25,NULL,'active','2026-08-27 09:33:48'),(449,'Mirab Armacho',25,NULL,'active','2026-08-27 09:33:48'),(450,'Metema',25,NULL,'active','2026-08-27 09:33:48'),(451,'Quara',25,NULL,'active','2026-08-27 09:33:48'),(452,'Midre Genet',25,NULL,'active','2026-08-27 09:33:48'),(453,'Metema Yohanes town',25,NULL,'active','2026-08-27 09:33:48'),(454,'Gendawuha town',25,NULL,'active','2026-08-27 09:33:48'),(455,'Semen Achefer',26,NULL,'active','2026-08-27 09:33:48'),(456,'Merawi town',26,NULL,'active','2026-08-27 09:33:48'),(457,'Yilmana Densa',26,NULL,'active','2026-08-27 09:33:48'),(458,'Mecha',26,NULL,'active','2026-08-27 09:33:48'),(459,'Debub Achefer',26,NULL,'active','2026-08-27 09:33:48'),(460,'Debub Mecha',26,NULL,'active','2026-08-27 09:33:48'),(461,'Gonje',26,NULL,'active','2026-08-27 09:33:48'),(462,'Dure Bete',26,NULL,'active','2026-08-27 09:33:48'),(463,'Adete town',26,NULL,'active','2026-08-27 09:33:48'),(464,'Bahir Dar Zuria',26,NULL,'active','2026-08-27 09:33:48'),(465,'Bahir Dar town',27,NULL,'active','2026-08-27 09:33:48'),(466,'Zege town',27,NULL,'active','2026-08-27 09:33:48'),(467,'Deq',27,NULL,'active','2026-08-27 09:33:48'),(468,'Meshenti town',27,NULL,'active','2026-08-27 09:33:49'),(469,'Tis Abay town',27,NULL,'active','2026-08-27 09:33:49'),(470,'Mana Sibu',28,NULL,'active','2026-08-27 09:33:49'),(471,'Nejo',28,NULL,'active','2026-08-27 09:33:49'),(472,'Gimbi',28,NULL,'active','2026-08-27 09:33:49'),(473,'Lalo Asabi',28,NULL,'active','2026-08-27 09:33:49'),(474,'Kiltu Kara',28,NULL,'active','2026-08-27 09:33:49'),(475,'Boji Dirmeji',28,NULL,'active','2026-08-27 09:33:49'),(476,'Ayira',28,NULL,'active','2026-08-27 09:33:49'),(477,'Jarso (West Wellega)',28,NULL,'active','2026-08-27 09:33:49'),(478,'Gudetu Kondole',28,NULL,'active','2026-08-27 09:33:49'),(479,'Boji Chekorsa',28,NULL,'active','2026-08-27 09:33:49'),(480,'Babo',28,NULL,'active','2026-08-27 09:33:49'),(481,'Yubdo',28,NULL,'active','2026-08-27 09:33:49'),(482,'Gaji',28,NULL,'active','2026-08-27 09:33:49'),(483,'Haru',28,NULL,'active','2026-08-27 09:33:49'),(484,'Nole Kaba',28,NULL,'active','2026-08-27 09:33:49'),(485,'Begi',28,NULL,'active','2026-08-27 09:33:49'),(486,'Homa',28,NULL,'active','2026-08-27 09:33:49'),(487,'Sayo Nole',28,NULL,'active','2026-08-27 09:33:49'),(488,'Guliso',28,NULL,'active','2026-08-27 09:33:49'),(489,'Gimbi town',28,NULL,'active','2026-08-27 09:33:49'),(490,'Leta Sibu',28,NULL,'active','2026-08-27 09:33:49'),(491,'Mendi town',28,NULL,'active','2026-08-27 09:33:49'),(492,'Nejo town',28,NULL,'active','2026-08-27 09:33:49'),(493,'Limu (OR)',29,NULL,'active','2026-08-27 09:33:49'),(494,'Ibantu',29,NULL,'active','2026-08-27 09:33:49'),(495,'Gida Ayana',29,NULL,'active','2026-08-27 09:33:49'),(496,'Haro Limu',29,NULL,'active','2026-08-27 09:33:49'),(497,'Boneya Boshe',29,NULL,'active','2026-08-27 09:33:49'),(498,'Wayu Tuka',29,NULL,'active','2026-08-27 09:33:49'),(499,'Bila Seyo',29,NULL,'active','2026-08-27 09:33:49'),(500,'Gobu Seyo',29,NULL,'active','2026-08-27 09:33:49'),(501,'Sibu Sire',29,NULL,'active','2026-08-27 09:33:49'),(502,'Diga',29,NULL,'active','2026-08-27 09:33:49'),(503,'Sasiga',29,NULL,'active','2026-08-27 09:33:49'),(504,'Leka Dulecha',29,NULL,'active','2026-08-27 09:33:49'),(505,'Guto Gida',29,NULL,'active','2026-08-27 09:33:49'),(506,'Jimma Arjo',29,NULL,'active','2026-08-27 09:33:49'),(507,'Nunu Kumba',29,NULL,'active','2026-08-27 09:33:49'),(508,'Wama Hagalo',29,NULL,'active','2026-08-27 09:33:49'),(509,'Kiremu',29,NULL,'active','2026-08-27 09:33:49'),(510,'Nekemte town',29,NULL,'active','2026-08-27 09:33:49'),(511,'Darimu',30,NULL,'active','2026-08-27 09:33:49'),(512,'Alge Sachi',30,NULL,'active','2026-08-27 09:33:49'),(513,'Yayu',30,NULL,'active','2026-08-27 09:33:49'),(514,'Metu Zuria',30,NULL,'active','2026-08-27 09:33:49'),(515,'Ale',30,NULL,'active','2026-08-27 09:33:49'),(516,'Bure (OR)',30,NULL,'active','2026-08-27 09:33:49'),(517,'Sale Nono',30,NULL,'active','2026-08-27 09:33:49'),(518,'Becho (Ilu Aba Bora)',30,NULL,'active','2026-08-27 09:33:49'),(519,'Bilo Nopha',30,NULL,'active','2026-08-27 09:33:49'),(520,'Hurumu',30,NULL,'active','2026-08-27 09:33:49'),(521,'Didu',30,NULL,'active','2026-08-27 09:33:49'),(522,'Halu /Huka',30,NULL,'active','2026-08-27 09:33:49'),(523,'Metu town',30,NULL,'active','2026-08-27 09:33:49'),(524,'Dorani',30,NULL,'active','2026-08-27 09:33:49'),(525,'Limu Seka',31,NULL,'active','2026-08-27 09:33:49'),(526,'Limu Kosa',31,NULL,'active','2026-08-27 09:33:49'),(527,'Sekoru',31,NULL,'active','2026-08-27 09:33:49'),(528,'Tiro Afeta',31,NULL,'active','2026-08-27 09:33:49'),(529,'Kersa (Jimma)',31,NULL,'active','2026-08-27 09:33:49'),(530,'Mena (Jimma)',31,NULL,'active','2026-08-27 09:33:49'),(531,'Goma',31,NULL,'active','2026-08-27 09:33:49'),(532,'Gera',31,NULL,'active','2026-08-27 09:33:49'),(533,'Seka Chekorsa',31,NULL,'active','2026-08-27 09:33:49'),(534,'Dedo',31,NULL,'active','2026-08-27 09:33:49'),(535,'Omo Nada',31,NULL,'active','2026-08-27 09:33:49'),(536,'Sigmo',31,NULL,'active','2026-08-27 09:33:49'),(537,'Setema',31,NULL,'active','2026-08-27 09:33:49'),(538,'Shebe Sambo',31,NULL,'active','2026-08-27 09:33:49'),(539,'Chora (Jimma)',31,NULL,'active','2026-08-27 09:33:49'),(540,'Gumay',31,NULL,'active','2026-08-27 09:33:49'),(541,'Agaro town',31,NULL,'active','2026-08-27 09:33:49'),(542,'Jimma town',31,NULL,'active','2026-08-27 09:33:49'),(543,'Mancho',31,NULL,'active','2026-08-27 09:33:49'),(544,'Omo Beyam',31,NULL,'active','2026-08-27 09:33:49'),(545,'Botor Tolay',31,NULL,'active','2026-08-27 09:33:49'),(546,'Nono Benja',31,NULL,'active','2026-08-27 09:33:49'),(547,'Illu Galan',32,NULL,'active','2026-08-27 09:33:49'),(548,'Ginde Beret',32,NULL,'active','2026-08-27 09:33:49'),(549,'Jeldu',32,NULL,'active','2026-08-27 09:33:49'),(550,'Ambo Zuria',32,NULL,'active','2026-08-27 09:33:49'),(551,'Mida Kegn',32,NULL,'active','2026-08-27 09:33:49'),(552,'Cheliya',32,NULL,'active','2026-08-27 09:33:49'),(553,'Bako Tibe',32,NULL,'active','2026-08-27 09:33:49'),(554,'Dano',32,NULL,'active','2026-08-27 09:33:49'),(555,'Nono',32,NULL,'active','2026-08-27 09:33:49'),(556,'Tikur Enchini',32,NULL,'active','2026-08-27 09:33:49'),(557,'Dendi',32,NULL,'active','2026-08-27 09:33:49'),(558,'Ejere /Addis Alem',32,NULL,'active','2026-08-27 09:33:49'),(559,'Holeta town',32,NULL,'active','2026-08-27 09:33:49'),(560,'Adda Berga',32,NULL,'active','2026-08-27 09:33:49'),(561,'Meta Robi',32,NULL,'active','2026-08-27 09:33:49'),(562,'Ambo town',32,NULL,'active','2026-08-27 09:33:49'),(563,'Abuna Ginde Beret',32,NULL,'active','2026-08-27 09:33:49'),(564,'Toke Kutaye',32,NULL,'active','2026-08-27 09:33:49'),(565,'Jibat',32,NULL,'active','2026-08-27 09:33:49'),(566,'Ifata',32,NULL,'active','2026-08-27 09:33:49'),(567,'Welmera',32,NULL,'active','2026-08-27 09:33:49'),(568,'Ejersa Lafo',32,NULL,'active','2026-08-27 09:33:49'),(569,'Cobi',32,NULL,'active','2026-08-27 09:33:49'),(570,'Meta Walkite',32,NULL,'active','2026-08-27 09:33:49'),(571,'Liban Jawi',32,NULL,'active','2026-08-27 09:33:50'),(572,'Wara Jarso',33,NULL,'active','2026-08-27 09:33:50'),(573,'Dera (OR)',33,NULL,'active','2026-08-27 09:33:50'),(574,'Hidabu Abote',33,NULL,'active','2026-08-27 09:33:50'),(575,'Kuyu',33,NULL,'active','2026-08-27 09:33:50'),(576,'Degem',33,NULL,'active','2026-08-27 09:33:50'),(577,'Gerar Jarso',33,NULL,'active','2026-08-27 09:33:50'),(578,'Debre Libanos',33,NULL,'active','2026-08-27 09:33:50'),(579,'Wuchale',33,NULL,'active','2026-08-27 09:33:50'),(580,'Abichugna Gne\'a',33,NULL,'active','2026-08-27 09:33:50'),(581,'Kimbibit',33,NULL,'active','2026-08-27 09:33:50'),(582,'Sheno town',33,NULL,'active','2026-08-27 09:33:50'),(583,'Fiche town',33,NULL,'active','2026-08-27 09:33:50'),(584,'Yaya Gulele',33,NULL,'active','2026-08-27 09:33:50'),(585,'Jida',33,NULL,'active','2026-08-27 09:33:50'),(586,'Garba Guracha',33,NULL,'active','2026-08-27 09:33:50'),(587,'Aleltu',33,NULL,'active','2026-08-27 09:33:50'),(588,'Bereh',33,NULL,'active','2026-08-27 09:33:50'),(589,'Sululta (OR)',33,NULL,'active','2026-08-27 09:33:50'),(590,'Chancho town',33,NULL,'active','2026-08-27 09:33:50'),(591,'Mulo',33,NULL,'active','2026-08-27 09:33:50'),(592,'Sendafa town',33,NULL,'active','2026-08-27 09:33:50'),(593,'Fentale',34,NULL,'active','2026-08-27 09:33:50'),(594,'Boset',34,NULL,'active','2026-08-27 09:33:50'),(595,'Adama',34,NULL,'active','2026-08-27 09:33:50'),(596,'Lome (OR)',34,NULL,'active','2026-08-27 09:33:50'),(597,'Gimbichu',34,NULL,'active','2026-08-27 09:33:50'),(598,'Ada\'a',34,NULL,'active','2026-08-27 09:33:50'),(599,'Dugda',34,NULL,'active','2026-08-27 09:33:50'),(600,'Adama Tulu Jido Kombolcha',34,NULL,'active','2026-08-27 09:33:50'),(601,'Bishoftu town',34,NULL,'active','2026-08-27 09:33:50'),(602,'Bora (OR)',34,NULL,'active','2026-08-27 09:33:50'),(603,'Liben Chukala',34,NULL,'active','2026-08-27 09:33:50'),(604,'Akaki',34,NULL,'active','2026-08-27 09:33:50'),(605,'Adama town',34,NULL,'active','2026-08-27 09:33:50'),(606,'Mojo town',34,NULL,'active','2026-08-27 09:33:50'),(607,'Batu town',34,NULL,'active','2026-08-27 09:33:50'),(608,'Metehara town',34,NULL,'active','2026-08-27 09:33:50'),(609,'Merti',35,NULL,'active','2026-08-27 09:33:50'),(610,'Aseko',35,NULL,'active','2026-08-27 09:33:50'),(611,'Golocha',35,NULL,'active','2026-08-27 09:33:50'),(612,'Jeju',35,NULL,'active','2026-08-27 09:33:50'),(613,'Dodota',35,NULL,'active','2026-08-27 09:33:50'),(614,'Ziway Dugda',35,NULL,'active','2026-08-27 09:33:50'),(615,'Hitosa',35,NULL,'active','2026-08-27 09:33:50'),(616,'Sude',35,NULL,'active','2026-08-27 09:33:50'),(617,'Chole',35,NULL,'active','2026-08-27 09:33:50'),(618,'Amigna',35,NULL,'active','2026-08-27 09:33:50'),(619,'Seru',35,NULL,'active','2026-08-27 09:33:50'),(620,'Robe',35,NULL,'active','2026-08-27 09:33:50'),(621,'Tena',35,NULL,'active','2026-08-27 09:33:50'),(622,'Shirka',35,NULL,'active','2026-08-27 09:33:50'),(623,'Degeluna Tijo',35,NULL,'active','2026-08-27 09:33:50'),(624,'Tiyo',35,NULL,'active','2026-08-27 09:33:50'),(625,'Munessa',35,NULL,'active','2026-08-27 09:33:50'),(626,'Limu Bilbilo',35,NULL,'active','2026-08-27 09:33:50'),(627,'Guna',35,NULL,'active','2026-08-27 09:33:50'),(628,'Sire',35,NULL,'active','2026-08-27 09:33:50'),(629,'Lude Hitosa',35,NULL,'active','2026-08-27 09:33:50'),(630,'Diksis',35,NULL,'active','2026-08-27 09:33:50'),(631,'Bele Gesgar',35,NULL,'active','2026-08-27 09:33:50'),(632,'Inkolo Wabe',35,NULL,'active','2026-08-27 09:33:50'),(633,'Asela town',35,NULL,'active','2026-08-27 09:33:50'),(634,'Shanan Kolu',35,NULL,'active','2026-08-27 09:33:50'),(635,'Bokoji town',35,NULL,'active','2026-08-27 09:33:50'),(636,'Dera town',35,NULL,'active','2026-08-27 09:33:50'),(637,'Mieso',36,NULL,'active','2026-08-27 09:33:50'),(638,'Doba',36,NULL,'active','2026-08-27 09:33:50'),(639,'Tulo (OR)',36,NULL,'active','2026-08-27 09:33:50'),(640,'Mesela /Shen Duggoo',36,NULL,'active','2026-08-27 09:33:50'),(641,'Chiro town',36,NULL,'active','2026-08-27 09:33:50'),(642,'Anchar',36,NULL,'active','2026-08-27 09:33:50'),(643,'Goba Koricha',36,NULL,'active','2026-08-27 09:33:50'),(644,'Habro',36,NULL,'active','2026-08-27 09:33:50'),(645,'Daro Lebu',36,NULL,'active','2026-08-27 09:33:50'),(646,'Boke',36,NULL,'active','2026-08-27 09:33:50'),(647,'Kuni /Oda Bultum',36,NULL,'active','2026-08-27 09:33:50'),(648,'Gemechis',36,NULL,'active','2026-08-27 09:33:50'),(649,'Chiro Zuria',36,NULL,'active','2026-08-27 09:33:50'),(650,'Bedesa town',36,NULL,'active','2026-08-27 09:33:50'),(651,'Hawi Gudina',36,NULL,'active','2026-08-27 09:33:50'),(652,'Gumbi Bordede',36,NULL,'active','2026-08-27 09:33:50'),(653,'Burqua Dhintu',36,NULL,'active','2026-08-27 09:33:50'),(654,'Babile town',37,NULL,'active','2026-08-27 09:33:50'),(655,'Kombolcha',37,NULL,'active','2026-08-27 09:33:50'),(656,'Jarso (East Hararge)',37,NULL,'active','2026-08-27 09:33:50'),(657,'Gursum (OR)',37,NULL,'active','2026-08-27 09:33:50'),(658,'Babile (OR)',37,NULL,'active','2026-08-27 09:33:50'),(659,'Fedis',37,NULL,'active','2026-08-27 09:33:50'),(660,'Haro Maya',37,NULL,'active','2026-08-27 09:33:50'),(661,'Kurfa Chele',37,NULL,'active','2026-08-27 09:33:50'),(662,'Kersa (East Hararge)',37,NULL,'active','2026-08-27 09:33:50'),(663,'Meta',37,NULL,'active','2026-08-27 09:33:50'),(664,'Goro Gutu',37,NULL,'active','2026-08-27 09:33:50'),(665,'Deder',37,NULL,'active','2026-08-27 09:33:50'),(666,'Melka Balo',37,NULL,'active','2026-08-27 09:33:50'),(667,'Bedeno',37,NULL,'active','2026-08-27 09:33:50'),(668,'Midhaga Tola',37,NULL,'active','2026-08-27 09:33:50'),(669,'Chinaksen',37,NULL,'active','2026-08-27 09:33:50'),(670,'Girawa',37,NULL,'active','2026-08-27 09:33:50'),(671,'Golo Oda',37,NULL,'active','2026-08-27 09:33:50'),(672,'Meyu Muleke',37,NULL,'active','2026-08-27 09:33:50'),(673,'Maya town',37,NULL,'active','2026-08-27 09:33:50'),(674,'Mekanisa Oromo',37,NULL,'active','2026-08-27 09:33:50'),(675,'Goro Muti',37,NULL,'active','2026-08-27 09:33:50'),(676,'Deder town',37,NULL,'active','2026-08-27 09:33:50'),(677,'Kumbi',37,NULL,'active','2026-08-27 09:33:50'),(678,'Agarfa',38,NULL,'active','2026-08-27 09:33:50'),(679,'Gasera',38,NULL,'active','2026-08-27 09:33:50'),(680,'Sinana',38,NULL,'active','2026-08-27 09:33:51'),(681,'Goba (OR)',38,NULL,'active','2026-08-27 09:33:51'),(682,'Harena Buluk',38,NULL,'active','2026-08-27 09:33:51'),(683,'Mena (Bale)',38,NULL,'active','2026-08-27 09:33:51'),(684,'Berbere',38,NULL,'active','2026-08-27 09:33:51'),(685,'Gura Damole',38,NULL,'active','2026-08-27 09:33:51'),(686,'Goro (Bale)',38,NULL,'active','2026-08-27 09:33:51'),(687,'Robe town',38,NULL,'active','2026-08-27 09:33:51'),(688,'Goba town',38,NULL,'active','2026-08-27 09:33:51'),(689,'Dinsho',38,NULL,'active','2026-08-27 09:33:51'),(690,'Yabelo',39,NULL,'active','2026-08-27 09:33:51'),(691,'Moyale (OR)',39,NULL,'active','2026-08-27 09:33:51'),(692,'Dire',39,NULL,'active','2026-08-27 09:33:51'),(693,'Teltale',39,NULL,'active','2026-08-27 09:33:51'),(694,'Miyo',39,NULL,'active','2026-08-27 09:33:51'),(695,'Dilo',39,NULL,'active','2026-08-27 09:33:51'),(696,'Gomole',39,NULL,'active','2026-08-27 09:33:51'),(697,'Guchi',39,NULL,'active','2026-08-27 09:33:51'),(698,'Dubluk',39,NULL,'active','2026-08-27 09:33:51'),(699,'Elwaya',39,NULL,'active','2026-08-27 09:33:51'),(700,'Yabelo town',39,NULL,'active','2026-08-27 09:33:51'),(701,'Ameya',40,NULL,'active','2026-08-27 09:33:51'),(702,'Wenchi',40,NULL,'active','2026-08-27 09:33:51'),(703,'Waliso',40,NULL,'active','2026-08-27 09:33:51'),(704,'Dawo',40,NULL,'active','2026-08-27 09:33:51'),(705,'Ilu',40,NULL,'active','2026-08-27 09:33:51'),(706,'Sebeta Hawas',40,NULL,'active','2026-08-27 09:33:51'),(707,'Kersana Malima',40,NULL,'active','2026-08-27 09:33:51'),(708,'Tole',40,NULL,'active','2026-08-27 09:33:51'),(709,'Becho (SW Shewa)',40,NULL,'active','2026-08-27 09:33:51'),(710,'Seden Sodo',40,NULL,'active','2026-08-27 09:33:51'),(711,'Woliso town',40,NULL,'active','2026-08-27 09:33:51'),(712,'Goro (SW Shewa)',40,NULL,'active','2026-08-27 09:33:51'),(713,'Sodo Daci',40,NULL,'active','2026-08-27 09:33:51'),(714,'Uraga',41,NULL,'active','2026-08-27 09:33:51'),(715,'Bore',41,NULL,'active','2026-08-27 09:33:51'),(716,'Adola',41,NULL,'active','2026-08-27 09:33:51'),(717,'Wadera',41,NULL,'active','2026-08-27 09:33:51'),(718,'Odo Shakiso',41,NULL,'active','2026-08-27 09:33:51'),(719,'Dama',41,NULL,'active','2026-08-27 09:33:51'),(720,'Arda Jila',41,NULL,'active','2026-08-27 09:33:51'),(721,'Girja /Harenfema',41,NULL,'active','2026-08-27 09:33:51'),(722,'Ana Sora',41,NULL,'active','2026-08-27 09:33:51'),(723,'Saba Boru',41,NULL,'active','2026-08-27 09:33:51'),(724,'Aga Wayu',41,NULL,'active','2026-08-27 09:33:51'),(725,'Haro Walabu',41,NULL,'active','2026-08-27 09:33:51'),(726,'Adola town',41,NULL,'active','2026-08-27 09:33:51'),(727,'Shakiso town',41,NULL,'active','2026-08-27 09:33:51'),(728,'Bule Hora',42,NULL,'active','2026-08-27 09:33:51'),(729,'Kercha',42,NULL,'active','2026-08-27 09:33:51'),(730,'Kercha town',42,NULL,'active','2026-08-27 09:33:51'),(731,'Hambela Wamena',42,NULL,'active','2026-08-27 09:33:51'),(732,'Abaya',42,NULL,'active','2026-08-27 09:33:51'),(733,'Dugda Dawa',42,NULL,'active','2026-08-27 09:33:51'),(734,'Gelana (West Guji)',42,NULL,'active','2026-08-27 09:33:51'),(735,'Melka Soda',42,NULL,'active','2026-08-27 09:33:51'),(736,'Bule Hora town',42,NULL,'active','2026-08-27 09:33:51'),(737,'Suro Berguda',42,NULL,'active','2026-08-27 09:33:51'),(738,'Birbirsa Kojowa',42,NULL,'active','2026-08-27 09:33:51'),(739,'Chora (Buno Bedele)',43,NULL,'active','2026-08-27 09:33:51'),(740,'Dega',43,NULL,'active','2026-08-27 09:33:51'),(741,'Dabo Hana',43,NULL,'active','2026-08-27 09:33:51'),(742,'Gechi',43,NULL,'active','2026-08-27 09:33:51'),(743,'Borecha',43,NULL,'active','2026-08-27 09:33:51'),(744,'Dedesa',43,NULL,'active','2026-08-27 09:33:51'),(745,'Meko',43,NULL,'active','2026-08-27 09:33:51'),(746,'Bedele town',43,NULL,'active','2026-08-27 09:33:51'),(747,'Bedele Zuria',43,NULL,'active','2026-08-27 09:33:51'),(748,'Chwaka',43,NULL,'active','2026-08-27 09:33:51'),(749,'Dodola town',44,NULL,'active','2026-08-27 09:33:51'),(750,'Siraro',44,NULL,'active','2026-08-27 09:33:51'),(751,'Shala',44,NULL,'active','2026-08-27 09:33:51'),(752,'Arsi Negele',44,NULL,'active','2026-08-27 09:33:51'),(753,'Kofele',44,NULL,'active','2026-08-27 09:33:51'),(754,'Kore',44,NULL,'active','2026-08-27 09:33:51'),(755,'Gedeb Asasa',44,NULL,'active','2026-08-27 09:33:51'),(756,'Dodola',44,NULL,'active','2026-08-27 09:33:51'),(757,'Kokosa',44,NULL,'active','2026-08-27 09:33:51'),(758,'Nenesebo',44,NULL,'active','2026-08-27 09:33:51'),(759,'Adaba',44,NULL,'active','2026-08-27 09:33:51'),(760,'Shashemene town',44,NULL,'active','2026-08-27 09:33:51'),(761,'Shashemene',44,NULL,'active','2026-08-27 09:33:51'),(762,'Kofele town',44,NULL,'active','2026-08-27 09:33:51'),(763,'Heban Arsi',44,NULL,'active','2026-08-27 09:33:51'),(764,'Wondo',44,NULL,'active','2026-08-27 09:33:51'),(765,'Arsi Negele town',44,NULL,'active','2026-08-27 09:33:51'),(766,'Hawa Galan',45,NULL,'active','2026-08-27 09:33:51'),(767,'Yama Logi Welel',45,NULL,'active','2026-08-27 09:33:51'),(768,'Dale Wabera',45,NULL,'active','2026-08-27 09:33:51'),(769,'Gawo Kebe',45,NULL,'active','2026-08-27 09:33:51'),(770,'Sayo',45,NULL,'active','2026-08-27 09:33:51'),(771,'Denbi Dollo town',45,NULL,'active','2026-08-27 09:33:51'),(772,'Anfilo',45,NULL,'active','2026-08-27 09:33:51'),(773,'Dale Sadi',45,NULL,'active','2026-08-27 09:33:51'),(774,'Gidami',45,NULL,'active','2026-08-27 09:33:51'),(775,'Jimma Horo',45,NULL,'active','2026-08-27 09:33:51'),(776,'Lalo Kile',45,NULL,'active','2026-08-27 09:33:51'),(777,'Sedi Chenka',45,NULL,'active','2026-08-27 09:33:51'),(778,'Horo',46,NULL,'active','2026-08-27 09:33:51'),(779,'Shambu town',46,NULL,'active','2026-08-27 09:33:51'),(780,'Guduru',46,NULL,'active','2026-08-27 09:33:51'),(781,'Ababo',46,NULL,'active','2026-08-27 09:33:51'),(782,'Abay Chomen',46,NULL,'active','2026-08-27 09:33:51'),(783,'Jimma Genete',46,NULL,'active','2026-08-27 09:33:51'),(784,'Jimma Rare',46,NULL,'active','2026-08-27 09:33:51'),(785,'Jarte Jardega',46,NULL,'active','2026-08-27 09:33:51'),(786,'Amuru',46,NULL,'active','2026-08-27 09:33:51'),(787,'Abe Dongoro',46,NULL,'active','2026-08-27 09:33:52'),(788,'Choman Guduru',46,NULL,'active','2026-08-27 09:33:52'),(789,'Horo Buluk',46,NULL,'active','2026-08-27 09:33:52'),(790,'Sulula Finca\'a',46,NULL,'active','2026-08-27 09:33:52'),(791,'Burayu',47,NULL,'active','2026-08-27 09:33:52'),(792,'Eka Tafo',47,NULL,'active','2026-08-27 09:33:52'),(793,'Furi',47,NULL,'active','2026-08-27 09:33:52'),(794,'Galan',47,NULL,'active','2026-08-27 09:33:52'),(795,'Galan Gudo',47,NULL,'active','2026-08-27 09:33:52'),(796,'Gefersa Guje',47,NULL,'active','2026-08-27 09:33:52'),(797,'Koye',47,NULL,'active','2026-08-27 09:33:52'),(798,'Kura Jida',47,NULL,'active','2026-08-27 09:33:52'),(799,'Mana Abichu',47,NULL,'active','2026-08-27 09:33:52'),(800,'Melka Nonno',47,NULL,'active','2026-08-27 09:33:52'),(801,'Sebeta',47,NULL,'active','2026-08-27 09:33:52'),(802,'Sululta',47,NULL,'active','2026-08-27 09:33:52'),(803,'Gololcha Bale',48,NULL,'active','2026-08-27 09:33:52'),(804,'Lege Hida',48,NULL,'active','2026-08-27 09:33:52'),(805,'Ginir',48,NULL,'active','2026-08-27 09:33:52'),(806,'Rayitu',48,NULL,'active','2026-08-27 09:33:52'),(807,'Seweyna',48,NULL,'active','2026-08-27 09:33:52'),(808,'Dawe Ketchen',48,NULL,'active','2026-08-27 09:33:52'),(809,'Ginir town',48,NULL,'active','2026-08-27 09:33:52'),(810,'Negele town',49,NULL,'active','2026-08-27 09:33:52'),(811,'Liben',49,NULL,'active','2026-08-27 09:33:52'),(812,'Meda Welabu',49,NULL,'active','2026-08-27 09:33:52'),(813,'West Welabu',49,NULL,'active','2026-08-27 09:33:52'),(814,'Gora Dola',49,NULL,'active','2026-08-27 09:33:52'),(815,'Gumi Idalo',49,NULL,'active','2026-08-27 09:33:52'),(816,'Arero',49,NULL,'active','2026-08-27 09:33:52'),(817,'Wachile',49,NULL,'active','2026-08-27 09:33:52'),(818,'Dhas',49,NULL,'active','2026-08-27 09:33:52'),(819,'Ayisha',50,NULL,'active','2026-08-27 09:33:52'),(820,'Dembel',50,NULL,'active','2026-08-27 09:33:52'),(821,'Shinile',50,NULL,'active','2026-08-27 09:33:52'),(822,'Erer (SM)',50,NULL,'active','2026-08-27 09:33:52'),(823,'Afdem',50,NULL,'active','2026-08-27 09:33:52'),(824,'Hadhagala',50,NULL,'active','2026-08-27 09:33:52'),(825,'Miesso',50,NULL,'active','2026-08-27 09:33:52'),(826,'Daymeed',50,NULL,'active','2026-08-27 09:33:52'),(827,'Dhunyar',50,NULL,'active','2026-08-27 09:33:52'),(828,'Gota-Biki',50,NULL,'active','2026-08-27 09:33:52'),(829,'Gablalu',50,NULL,'active','2026-08-27 09:33:52'),(830,'Gursum (SM)',51,NULL,'active','2026-08-27 09:33:52'),(831,'Babile (SM)',51,NULL,'active','2026-08-27 09:33:52'),(832,'Shabeeley',51,NULL,'active','2026-08-27 09:33:52'),(833,'Aw-Bare',51,NULL,'active','2026-08-27 09:33:52'),(834,'Kebribeyah',51,NULL,'active','2026-08-27 09:33:52'),(835,'Harshin',51,NULL,'active','2026-08-27 09:33:52'),(836,'Tuliguled',51,NULL,'active','2026-08-27 09:33:52'),(837,'Goljano',51,NULL,'active','2026-08-27 09:33:52'),(838,'Jigjiga town',51,NULL,'active','2026-08-27 09:33:52'),(839,'Wajale town',51,NULL,'active','2026-08-27 09:33:52'),(840,'Kebribayah town',51,NULL,'active','2026-08-27 09:33:52'),(841,'Koran /Mulla',51,NULL,'active','2026-08-27 09:33:52'),(842,'Haroreys',51,NULL,'active','2026-08-27 09:33:52'),(843,'Harawo',51,NULL,'active','2026-08-27 09:33:52'),(844,'Degehamedo',52,NULL,'active','2026-08-27 09:33:52'),(845,'Degehabur',52,NULL,'active','2026-08-27 09:33:52'),(846,'Aware',52,NULL,'active','2026-08-27 09:33:52'),(847,'Gashamo',52,NULL,'active','2026-08-27 09:33:52'),(848,'Gunagado',52,NULL,'active','2026-08-27 09:33:52'),(849,'Bilcil-Bur',52,NULL,'active','2026-08-27 09:33:52'),(850,'Degahabur town',52,NULL,'active','2026-08-27 09:33:52'),(851,'Yocale',52,NULL,'active','2026-08-27 09:33:52'),(852,'Daror',52,NULL,'active','2026-08-27 09:33:52'),(853,'Burqod',52,NULL,'active','2026-08-27 09:33:52'),(854,'Ararso',52,NULL,'active','2026-08-27 09:33:52'),(855,'Dig',52,NULL,'active','2026-08-27 09:33:52'),(856,'Fik',53,NULL,'active','2026-08-27 09:33:52'),(857,'Salahad',53,NULL,'active','2026-08-27 09:33:52'),(858,'Hamero',53,NULL,'active','2026-08-27 09:33:52'),(859,'Lagahida',53,NULL,'active','2026-08-27 09:33:52'),(860,'Meyumuluka',53,NULL,'active','2026-08-27 09:33:52'),(861,'Qubi',53,NULL,'active','2026-08-27 09:33:52'),(862,'Yahob',53,NULL,'active','2026-08-27 09:33:52'),(863,'Wangey',53,NULL,'active','2026-08-27 09:33:52'),(864,'Shaygosh',54,NULL,'active','2026-08-27 09:33:52'),(865,'Kebridehar',54,NULL,'active','2026-08-27 09:33:52'),(866,'Shilabo',54,NULL,'active','2026-08-27 09:33:52'),(867,'Debeweyin',54,NULL,'active','2026-08-27 09:33:52'),(868,'Marsin',54,NULL,'active','2026-08-27 09:33:52'),(869,'Kebridehar town',54,NULL,'active','2026-08-27 09:33:52'),(870,'Goglo',54,NULL,'active','2026-08-27 09:33:52'),(871,'Lasdhankayre',54,NULL,'active','2026-08-27 09:33:52'),(872,'Higloley',54,NULL,'active','2026-08-27 09:33:52'),(873,'El-Ogaden',54,NULL,'active','2026-08-27 09:33:52'),(874,'Bodaley',54,NULL,'active','2026-08-27 09:33:52'),(875,'East Imi',55,NULL,'active','2026-08-27 09:33:52'),(876,'Adadle',55,NULL,'active','2026-08-27 09:33:52'),(877,'Danan',55,NULL,'active','2026-08-27 09:33:52'),(878,'Gode',55,NULL,'active','2026-08-27 09:33:52'),(879,'Kelafo',55,NULL,'active','2026-08-27 09:33:52'),(880,'Mustahil',55,NULL,'active','2026-08-27 09:33:52'),(881,'Ferfer',55,NULL,'active','2026-08-27 09:33:52'),(882,'Berocano',55,NULL,'active','2026-08-27 09:33:52'),(883,'Godey town',55,NULL,'active','2026-08-27 09:33:52'),(884,'Elale',55,NULL,'active','2026-08-27 09:33:52'),(885,'Aba-Korow',55,NULL,'active','2026-08-27 09:33:52'),(886,'Danod',56,NULL,'active','2026-08-27 09:33:52'),(887,'Bokh',56,NULL,'active','2026-08-27 09:33:52'),(888,'Galadi',56,NULL,'active','2026-08-27 09:33:53'),(889,'Warder',56,NULL,'active','2026-08-27 09:33:53'),(890,'Daratole',56,NULL,'active','2026-08-27 09:33:53'),(891,'Lehel-Yucub',56,NULL,'active','2026-08-27 09:33:53'),(892,'Galhamur',56,NULL,'active','2026-08-27 09:33:53'),(893,'Charati',57,NULL,'active','2026-08-27 09:33:53'),(894,'Elkare /Serer',57,NULL,'active','2026-08-27 09:33:53'),(895,'West Imi',57,NULL,'active','2026-08-27 09:33:53'),(896,'Hargele',57,NULL,'active','2026-08-27 09:33:53'),(897,'Barey',57,NULL,'active','2026-08-27 09:33:53'),(898,'Dolobay',57,NULL,'active','2026-08-27 09:33:53'),(899,'Raso',57,NULL,'active','2026-08-27 09:33:53'),(900,'Kohle /Qoxle',57,NULL,'active','2026-08-27 09:33:53'),(901,'God-God',57,NULL,'active','2026-08-27 09:33:53'),(902,'Filtu',58,NULL,'active','2026-08-27 09:33:53'),(903,'Dolo Ado',58,NULL,'active','2026-08-27 09:33:53'),(904,'Goro Baqaqsa',58,NULL,'active','2026-08-27 09:33:53'),(905,'Guradamole',58,NULL,'active','2026-08-27 09:33:53'),(906,'Deka Suftu',58,NULL,'active','2026-08-27 09:33:53'),(907,'Bokolmayo',58,NULL,'active','2026-08-27 09:33:53'),(908,'Ayun',59,NULL,'active','2026-08-27 09:33:53'),(909,'Elwayne',59,NULL,'active','2026-08-27 09:33:53'),(910,'Garbo',59,NULL,'active','2026-08-27 09:33:53'),(911,'Sagag',59,NULL,'active','2026-08-27 09:33:53'),(912,'Dihun',59,NULL,'active','2026-08-27 09:33:53'),(913,'Horshagah',59,NULL,'active','2026-08-27 09:33:53'),(914,'Hararey',59,NULL,'active','2026-08-27 09:33:53'),(915,'Moyale (SM)',60,NULL,'active','2026-08-27 09:33:53'),(916,'Hudet',60,NULL,'active','2026-08-27 09:33:53'),(917,'Mubarek',60,NULL,'active','2026-08-27 09:33:53'),(918,'Qada Duma',60,NULL,'active','2026-08-27 09:33:53'),(919,'Gilgel Beles town',61,NULL,'active','2026-08-27 09:33:53'),(920,'Dangur',61,NULL,'active','2026-08-27 09:33:53'),(921,'Guba',61,NULL,'active','2026-08-27 09:33:53'),(922,'Wembera',61,NULL,'active','2026-08-27 09:33:53'),(923,'Mandura',61,NULL,'active','2026-08-27 09:33:53'),(924,'Dibate',61,NULL,'active','2026-08-27 09:33:53'),(925,'Pawe',61,NULL,'active','2026-08-27 09:33:53'),(926,'Bulen',61,NULL,'active','2026-08-27 09:33:53'),(927,'Menge',62,NULL,'active','2026-08-27 09:33:53'),(928,'Kurmuk',62,NULL,'active','2026-08-27 09:33:53'),(929,'Abrahmo',62,NULL,'active','2026-08-27 09:33:53'),(930,'Sherkole',62,NULL,'active','2026-08-27 09:33:53'),(931,'Bambasi',62,NULL,'active','2026-08-27 09:33:53'),(932,'Bilidigilu',62,NULL,'active','2026-08-27 09:33:53'),(933,'Homosha',62,NULL,'active','2026-08-27 09:33:53'),(934,'Undulu',62,NULL,'active','2026-08-27 09:33:53'),(935,'Assosa town Admin',62,NULL,'active','2026-08-27 09:33:53'),(936,'Bambasi town',62,NULL,'active','2026-08-27 09:33:53'),(937,'Ura',62,NULL,'active','2026-08-27 09:33:53'),(938,'Kamashi town',63,NULL,'active','2026-08-27 09:33:53'),(939,'Zayi',63,NULL,'active','2026-08-27 09:33:53'),(940,'Sedal',63,NULL,'active','2026-08-27 09:33:53'),(941,'Kamashi',63,NULL,'active','2026-08-27 09:33:53'),(942,'Dembe',63,NULL,'active','2026-08-27 09:33:53'),(943,'Mezniga',63,NULL,'active','2026-08-27 09:33:53'),(944,'Mao-komo Special',64,NULL,'active','2026-08-27 09:33:53'),(945,'Boloso Sore',75,NULL,'active','2026-08-27 09:33:53'),(946,'Damot Gale',75,NULL,'active','2026-08-27 09:33:53'),(947,'Damot Woide',75,NULL,'active','2026-08-27 09:33:53'),(948,'Humbo',75,NULL,'active','2026-08-27 09:33:53'),(949,'Sodo Zuria',75,NULL,'active','2026-08-27 09:33:53'),(950,'Kindo Koyesha',75,NULL,'active','2026-08-27 09:33:53'),(951,'Ofa',75,NULL,'active','2026-08-27 09:33:53'),(952,'Boloso Bombe',75,NULL,'active','2026-08-27 09:33:53'),(953,'Damot Sore',75,NULL,'active','2026-08-27 09:33:53'),(954,'Kindo Daddaye',75,NULL,'active','2026-08-27 09:33:53'),(955,'Damot Pullasa',75,NULL,'active','2026-08-27 09:33:53'),(956,'Duguna Fango',75,NULL,'active','2026-08-27 09:33:53'),(957,'Sodo town',75,NULL,'active','2026-08-27 09:33:53'),(958,'Areka town',75,NULL,'active','2026-08-27 09:33:53'),(959,'Boditi town',75,NULL,'active','2026-08-27 09:33:53'),(960,'Tebela town',75,NULL,'active','2026-08-27 09:33:53'),(961,'Gesuba town',75,NULL,'active','2026-08-27 09:33:53'),(962,'Gununo Hamus town',75,NULL,'active','2026-08-27 09:33:53'),(963,'Hobicha Abaya',75,NULL,'active','2026-08-27 09:33:53'),(964,'Kawo Koyesha',75,NULL,'active','2026-08-27 09:33:53'),(965,'Abela Abaya',75,NULL,'active','2026-08-27 09:33:53'),(966,'Bayera Koyesha',75,NULL,'active','2026-08-27 09:33:53'),(967,'Bale Awassa town',75,NULL,'active','2026-08-27 09:33:53'),(968,'Arba Minch town',76,NULL,'active','2026-08-27 09:33:53'),(969,'Arba Minch Zuria',76,NULL,'active','2026-08-27 09:33:53'),(970,'Birbir town',76,NULL,'active','2026-08-27 09:33:53'),(971,'Bonke',76,NULL,'active','2026-08-27 09:33:53'),(972,'Boreda',76,NULL,'active','2026-08-27 09:33:53'),(973,'Chencha town',76,NULL,'active','2026-08-27 09:33:53'),(974,'Chencha Zuriya',76,NULL,'active','2026-08-27 09:33:53'),(975,'Daramalo',76,NULL,'active','2026-08-27 09:33:53'),(976,'Dita',76,NULL,'active','2026-08-27 09:33:53'),(977,'Gacho Baba',76,NULL,'active','2026-08-27 09:33:53'),(978,'Garda Marta',76,NULL,'active','2026-08-27 09:33:53'),(979,'Geressie',76,NULL,'active','2026-08-27 09:33:53'),(980,'Geressie town',76,NULL,'active','2026-08-27 09:33:53'),(981,'Kemba town',76,NULL,'active','2026-08-27 09:33:53'),(982,'Kemba Zuria',76,NULL,'active','2026-08-27 09:33:53'),(983,'Kogota /Ezo',76,NULL,'active','2026-08-27 09:33:53'),(984,'Kucha',76,NULL,'active','2026-08-27 09:33:53'),(985,'Kucha Alpha',76,NULL,'active','2026-08-27 09:33:53'),(986,'Mirab Abaya',76,NULL,'active','2026-08-27 09:33:53'),(987,'Selamber town',76,NULL,'active','2026-08-27 09:33:53'),(988,'Beto town',77,NULL,'active','2026-08-27 09:33:54'),(989,'Buleqi town',77,NULL,'active','2026-08-27 09:33:54'),(990,'Denba Gofa',77,NULL,'active','2026-08-27 09:33:54'),(991,'Gezei Gofa',77,NULL,'active','2026-08-27 09:33:54'),(992,'Laha town',77,NULL,'active','2026-08-27 09:33:54'),(993,'Melekoza',77,NULL,'active','2026-08-27 09:33:54'),(994,'Melo Gada',77,NULL,'active','2026-08-27 09:33:54'),(995,'O\'yida',77,NULL,'active','2026-08-27 09:33:54'),(996,'Sawla town',77,NULL,'active','2026-08-27 09:33:54'),(997,'Uba Debre Tsehay',77,NULL,'active','2026-08-27 09:33:54'),(998,'Zala',77,NULL,'active','2026-08-27 09:33:54'),(999,'Basketo Special',78,NULL,'active','2026-08-27 09:33:54'),(1000,'Laska town',78,NULL,'active','2026-08-27 09:33:54'),(1001,'Baka Dawula',79,NULL,'active','2026-08-27 09:33:54'),(1002,'Gelila town',79,NULL,'active','2026-08-27 09:33:54'),(1003,'Jinka town',79,NULL,'active','2026-08-27 09:33:54'),(1004,'North Ari',79,NULL,'active','2026-08-27 09:33:54'),(1005,'South Ari',79,NULL,'active','2026-08-27 09:33:54'),(1006,'Wub Ari',79,NULL,'active','2026-08-27 09:33:54'),(1007,'Alle Special',80,NULL,'active','2026-08-27 09:33:54'),(1008,'Derashe',81,NULL,'active','2026-08-27 09:33:54'),(1009,'Gidole town',81,NULL,'active','2026-08-27 09:33:54'),(1010,'Amaro',82,NULL,'active','2026-08-27 09:33:54'),(1011,'Kele town',82,NULL,'active','2026-08-27 09:33:54'),(1012,'Kena',83,NULL,'active','2026-08-27 09:33:54'),(1013,'Karat Zuria',83,NULL,'active','2026-08-27 09:33:54'),(1014,'Karat town',83,NULL,'active','2026-08-27 09:33:54'),(1015,'Segen Zuria',83,NULL,'active','2026-08-27 09:33:54'),(1016,'Kolme',83,NULL,'active','2026-08-27 09:33:54'),(1017,'Burji',84,NULL,'active','2026-08-27 09:33:54'),(1018,'Wenago',85,NULL,'active','2026-08-27 09:33:54'),(1019,'Yirgachefe',85,NULL,'active','2026-08-27 09:33:54'),(1020,'Kochere',85,NULL,'active','2026-08-27 09:33:54'),(1021,'Bule',85,NULL,'active','2026-08-27 09:33:54'),(1022,'Dila Zuria',85,NULL,'active','2026-08-27 09:33:54'),(1023,'Gedeb',85,NULL,'active','2026-08-27 09:33:54'),(1024,'Rape',85,NULL,'active','2026-08-27 09:33:54'),(1025,'Churso',85,NULL,'active','2026-08-27 09:33:54'),(1026,'Dila town',85,NULL,'active','2026-08-27 09:33:54'),(1027,'Gedeb town',85,NULL,'active','2026-08-27 09:33:54'),(1028,'Yirgachefe town',85,NULL,'active','2026-08-27 09:33:54'),(1029,'Chelelektu town',85,NULL,'active','2026-08-27 09:33:54'),(1030,'Wonago town',85,NULL,'active','2026-08-27 09:33:54'),(1031,'Bena Tsemay',86,NULL,'active','2026-08-27 09:33:54'),(1032,'Dasenech /Kuraz',86,NULL,'active','2026-08-27 09:33:54'),(1033,'Hamer',86,NULL,'active','2026-08-27 09:33:54'),(1034,'Malie',86,NULL,'active','2026-08-27 09:33:54'),(1035,'Nyngatom',86,NULL,'active','2026-08-27 09:33:54'),(1036,'Salamago',86,NULL,'active','2026-08-27 09:33:54'),(1037,'Turmi town',86,NULL,'active','2026-08-27 09:33:54'),(1038,'Kebena Special',65,NULL,'active','2026-08-27 09:33:54'),(1039,'Abeshege',66,NULL,'active','2026-08-27 09:33:54'),(1040,'Ezha',66,NULL,'active','2026-08-27 09:33:54'),(1041,'Gedebano Gutazer Welene',66,NULL,'active','2026-08-27 09:33:54'),(1042,'Endiguagn',66,NULL,'active','2026-08-27 09:33:54'),(1043,'Gumer',66,NULL,'active','2026-08-27 09:33:54'),(1044,'Cheha',66,NULL,'active','2026-08-27 09:33:54'),(1045,'Enemor Ener',66,NULL,'active','2026-08-27 09:33:54'),(1046,'Muhur Na Aklil',66,NULL,'active','2026-08-27 09:33:54'),(1047,'Geta',66,NULL,'active','2026-08-27 09:33:54'),(1048,'Welkite town',66,NULL,'active','2026-08-27 09:33:54'),(1049,'Emdebir town',66,NULL,'active','2026-08-27 09:33:54'),(1050,'Enor Ener',66,NULL,'active','2026-08-27 09:33:54'),(1051,'Misha',67,NULL,'active','2026-08-27 09:33:54'),(1052,'Gombora',67,NULL,'active','2026-08-27 09:33:54'),(1053,'Lemmo',67,NULL,'active','2026-08-27 09:33:54'),(1054,'Shashogo',67,NULL,'active','2026-08-27 09:33:54'),(1055,'Misrak Badawacho',67,NULL,'active','2026-08-27 09:33:54'),(1056,'Soro',67,NULL,'active','2026-08-27 09:33:54'),(1057,'Duna',67,NULL,'active','2026-08-27 09:33:54'),(1058,'Analemmo',67,NULL,'active','2026-08-27 09:33:54'),(1059,'Mirab Badowach',67,NULL,'active','2026-08-27 09:33:54'),(1060,'Gibe',67,NULL,'active','2026-08-27 09:33:54'),(1061,'Hosaena town',67,NULL,'active','2026-08-27 09:33:54'),(1062,'Shone town',67,NULL,'active','2026-08-27 09:33:54'),(1063,'Gimbichu town',67,NULL,'active','2026-08-27 09:33:54'),(1064,'Jajura town',67,NULL,'active','2026-08-27 09:33:54'),(1065,'Ameka',67,NULL,'active','2026-08-27 09:33:54'),(1066,'Siraro Badawacho',67,NULL,'active','2026-08-27 09:33:54'),(1067,'Mirab Soro',67,NULL,'active','2026-08-27 09:33:54'),(1068,'Angacha',68,NULL,'active','2026-08-27 09:33:54'),(1069,'Kediada Gambela',68,NULL,'active','2026-08-27 09:33:54'),(1070,'Kacha Bira',68,NULL,'active','2026-08-27 09:33:54'),(1071,'Hadero Tunto',68,NULL,'active','2026-08-27 09:33:54'),(1072,'Doyogena',68,NULL,'active','2026-08-27 09:33:54'),(1073,'Damboya',68,NULL,'active','2026-08-27 09:33:54'),(1074,'Durame town',68,NULL,'active','2026-08-27 09:33:54'),(1075,'Adilo',68,NULL,'active','2026-08-27 09:33:54'),(1076,'Shinshincho town',68,NULL,'active','2026-08-27 09:33:54'),(1077,'Hadero town',68,NULL,'active','2026-08-27 09:33:54'),(1078,'Butajira town',69,NULL,'active','2026-08-27 09:33:54'),(1079,'Bui town',69,NULL,'active','2026-08-27 09:33:54'),(1080,'Sodo',69,NULL,'active','2026-08-27 09:33:54'),(1081,'Meskan',69,NULL,'active','2026-08-27 09:33:54'),(1082,'Misrak Meskan',69,NULL,'active','2026-08-27 09:33:54'),(1083,'Debub Sodo',69,NULL,'active','2026-08-27 09:33:54'),(1084,'Kulito town',70,NULL,'active','2026-08-27 09:33:54'),(1085,'Wera',70,NULL,'active','2026-08-27 09:33:54'),(1086,'Atote Ulo',70,NULL,'active','2026-08-27 09:33:54'),(1087,'Wera Djo',70,NULL,'active','2026-08-27 09:33:54'),(1088,'Alicho Woriro',71,NULL,'active','2026-08-27 09:33:54'),(1089,'Siltie',71,NULL,'active','2026-08-27 09:33:54'),(1090,'Lanfero',71,NULL,'active','2026-08-27 09:33:54'),(1091,'Mierab Azenet Berbere',71,NULL,'active','2026-08-27 09:33:54'),(1092,'Dalocha',71,NULL,'active','2026-08-27 09:33:54'),(1093,'Sankura',71,NULL,'active','2026-08-27 09:33:54'),(1094,'Misrak Azenet Berbere',71,NULL,'active','2026-08-27 09:33:54'),(1095,'Wulbareg',71,NULL,'active','2026-08-27 09:33:54'),(1096,'Tora town',71,NULL,'active','2026-08-27 09:33:54'),(1097,'Worabe town',71,NULL,'active','2026-08-27 09:33:55'),(1098,'Kibet town',71,NULL,'active','2026-08-27 09:33:55'),(1099,'Mito',71,NULL,'active','2026-08-27 09:33:55'),(1100,'Misrak Siltie',71,NULL,'active','2026-08-27 09:33:55'),(1101,'Saja town',72,NULL,'active','2026-08-27 09:33:55'),(1102,'Fofa',72,NULL,'active','2026-08-27 09:33:55'),(1103,'Deri Saja zuria',72,NULL,'active','2026-08-27 09:33:55'),(1104,'Toba',72,NULL,'active','2026-08-27 09:33:55'),(1105,'Mareko Special',73,NULL,'active','2026-08-27 09:33:55'),(1106,'Tembaro Special',74,NULL,'active','2026-08-27 09:33:55'),(1107,'Anderacha',87,NULL,'active','2026-08-27 09:33:55'),(1108,'Masha',87,NULL,'active','2026-08-27 09:33:55'),(1109,'Yeki',87,NULL,'active','2026-08-27 09:33:55'),(1110,'Tepi',87,NULL,'active','2026-08-27 09:33:55'),(1111,'Masha town',87,NULL,'active','2026-08-27 09:33:55'),(1112,'Saylem',88,NULL,'active','2026-08-27 09:33:55'),(1113,'Gesha',88,NULL,'active','2026-08-27 09:33:55'),(1114,'Gewata',88,NULL,'active','2026-08-27 09:33:55'),(1115,'Gimbo',88,NULL,'active','2026-08-27 09:33:55'),(1116,'Adiyio',88,NULL,'active','2026-08-27 09:33:55'),(1117,'Tullo',88,NULL,'active','2026-08-27 09:33:55'),(1118,'Cheta',88,NULL,'active','2026-08-27 09:33:55'),(1119,'Decha',88,NULL,'active','2026-08-27 09:33:55'),(1120,'Chena',88,NULL,'active','2026-08-27 09:33:55'),(1121,'Bita',88,NULL,'active','2026-08-27 09:33:55'),(1122,'Bonga town',88,NULL,'active','2026-08-27 09:33:55'),(1123,'Goba (SW)',88,NULL,'active','2026-08-27 09:33:55'),(1124,'Shisho Ande',88,NULL,'active','2026-08-27 09:33:55'),(1125,'Wacha town',88,NULL,'active','2026-08-27 09:33:55'),(1126,'Deka town',88,NULL,'active','2026-08-27 09:33:55'),(1127,'Awurada town',88,NULL,'active','2026-08-27 09:33:55'),(1128,'Shishinda town',88,NULL,'active','2026-08-27 09:33:55'),(1129,'Sheko',89,NULL,'active','2026-08-27 09:33:55'),(1130,'Gurafereda',89,NULL,'active','2026-08-27 09:33:55'),(1131,'Debub Bench',89,NULL,'active','2026-08-27 09:33:55'),(1132,'Shay Bench',89,NULL,'active','2026-08-27 09:33:55'),(1133,'Semen Bench',89,NULL,'active','2026-08-27 09:33:55'),(1134,'Gidi Bench',89,NULL,'active','2026-08-27 09:33:55'),(1135,'Mizan Aman town',89,NULL,'active','2026-08-27 09:33:55'),(1136,'Size town',89,NULL,'active','2026-08-27 09:33:55'),(1137,'Biftu town',89,NULL,'active','2026-08-27 09:33:55'),(1138,'Sheko town',89,NULL,'active','2026-08-27 09:33:55'),(1139,'Tocha',90,NULL,'active','2026-08-27 09:33:55'),(1140,'Mareka',90,NULL,'active','2026-08-27 09:33:55'),(1141,'Loma',90,NULL,'active','2026-08-27 09:33:55'),(1142,'Gena',90,NULL,'active','2026-08-27 09:33:55'),(1143,'Esara',90,NULL,'active','2026-08-27 09:33:55'),(1144,'Kachi',90,NULL,'active','2026-08-27 09:33:55'),(1145,'Tercha Zuriya',90,NULL,'active','2026-08-27 09:33:55'),(1146,'Mari Mansa',90,NULL,'active','2026-08-27 09:33:55'),(1147,'Disa',90,NULL,'active','2026-08-27 09:33:55'),(1148,'Zabagazo',90,NULL,'active','2026-08-27 09:33:55'),(1149,'Tercha town',90,NULL,'active','2026-08-27 09:33:55'),(1150,'Gesa town',90,NULL,'active','2026-08-27 09:33:55'),(1151,'Gachit',91,NULL,'active','2026-08-27 09:33:55'),(1152,'Menit Goldiye',91,NULL,'active','2026-08-27 09:33:55'),(1153,'Gori Gesha',91,NULL,'active','2026-08-27 09:33:55'),(1154,'Menit Shasha',91,NULL,'active','2026-08-27 09:33:55'),(1155,'Bero',91,NULL,'active','2026-08-27 09:33:55'),(1156,'Surma',91,NULL,'active','2026-08-27 09:33:55'),(1157,'Maji',91,NULL,'active','2026-08-27 09:33:55'),(1158,'Bachuma town',91,NULL,'active','2026-08-27 09:33:55'),(1159,'Jemu town',91,NULL,'active','2026-08-27 09:33:55'),(1160,'Maji Tum town',91,NULL,'active','2026-08-27 09:33:55'),(1161,'Konta Koysha',92,NULL,'active','2026-08-27 09:33:55'),(1162,'Chida town',92,NULL,'active','2026-08-27 09:33:55'),(1163,'Amaya town',92,NULL,'active','2026-08-27 09:33:55'),(1164,'Elahanchano',92,NULL,'active','2026-08-27 09:33:55'),(1165,'Ameya Zuria',92,NULL,'active','2026-08-27 09:33:55'),(1166,'Hawassa town',101,NULL,'active','2026-08-27 09:33:55'),(1167,'Wondo-Genet town',102,NULL,'active','2026-08-27 09:33:55'),(1168,'Wondo-Genet',102,NULL,'active','2026-08-27 09:33:55'),(1169,'Malga',102,NULL,'active','2026-08-27 09:33:55'),(1170,'Gorche',102,NULL,'active','2026-08-27 09:33:55'),(1171,'Hawela',102,NULL,'active','2026-08-27 09:33:55'),(1172,'Leku town',102,NULL,'active','2026-08-27 09:33:55'),(1173,'Shebe Dino',102,NULL,'active','2026-08-27 09:33:55'),(1174,'Boricha',102,NULL,'active','2026-08-27 09:33:55'),(1175,'Bilate Zuria',102,NULL,'active','2026-08-27 09:33:55'),(1176,'Hawassa Zuria',102,NULL,'active','2026-08-27 09:33:55'),(1177,'Arbegona',103,NULL,'active','2026-08-27 09:33:55'),(1178,'Shafamo',103,NULL,'active','2026-08-27 09:33:55'),(1179,'Wonosho',103,NULL,'active','2026-08-27 09:33:55'),(1180,'Yirgalem town',103,NULL,'active','2026-08-27 09:33:55'),(1181,'Dale',103,NULL,'active','2026-08-27 09:33:55'),(1182,'Darara',103,NULL,'active','2026-08-27 09:33:55'),(1183,'Loka Abaya',103,NULL,'active','2026-08-27 09:33:55'),(1184,'Bursa',104,NULL,'active','2026-08-27 09:33:55'),(1185,'Teticha',104,NULL,'active','2026-08-27 09:33:55'),(1186,'Aleta Wendo',104,NULL,'active','2026-08-27 09:33:55'),(1187,'Aleta Wondo town',104,NULL,'active','2026-08-27 09:33:55'),(1188,'Aleta Chuko',104,NULL,'active','2026-08-27 09:33:55'),(1189,'Chuko town',104,NULL,'active','2026-08-27 09:33:55'),(1190,'Dara',104,NULL,'active','2026-08-27 09:33:55'),(1191,'Dara Otilicho',104,NULL,'active','2026-08-27 09:33:55'),(1192,'Hulla',104,NULL,'active','2026-08-27 09:33:55'),(1193,'Chirone',104,NULL,'active','2026-08-27 09:33:55'),(1194,'Bura',105,NULL,'active','2026-08-27 09:33:55'),(1195,'Bona Zuria',105,NULL,'active','2026-08-27 09:33:55'),(1196,'Bensa',105,NULL,'active','2026-08-27 09:33:55'),(1197,'Daye town',105,NULL,'active','2026-08-27 09:33:55'),(1198,'Daella',105,NULL,'active','2026-08-27 09:33:55'),(1199,'Chire',105,NULL,'active','2026-08-27 09:33:55'),(1200,'Aroresa',105,NULL,'active','2026-08-27 09:33:55'),(1201,'Hokko',105,NULL,'active','2026-08-27 09:33:55'),(1202,'Chabe Gambeltu',105,NULL,'active','2026-08-27 09:33:55'),(1203,'Akobo',93,NULL,'active','2026-08-27 09:33:55'),(1204,'Lare',93,NULL,'active','2026-08-27 09:33:55'),(1205,'Jikawo',93,NULL,'active','2026-08-27 09:33:55'),(1206,'Wantawo',93,NULL,'active','2026-08-27 09:33:55'),(1207,'Makuey',93,NULL,'active','2026-08-27 09:33:55'),(1208,'Gambela National Park',94,NULL,'active','2026-08-27 09:33:56'),(1209,'Abobo',94,NULL,'active','2026-08-27 09:33:56'),(1210,'Gambela Zuria',94,NULL,'active','2026-08-27 09:33:56'),(1211,'Gog',94,NULL,'active','2026-08-27 09:33:56'),(1212,'Jore',94,NULL,'active','2026-08-27 09:33:56'),(1213,'Dima (GM)',94,NULL,'active','2026-08-27 09:33:56'),(1214,'Gambela town',94,NULL,'active','2026-08-27 09:33:56'),(1215,'Godere',95,NULL,'active','2026-08-27 09:33:56'),(1216,'Mengesh',95,NULL,'active','2026-08-27 09:33:56'),(1217,'Itang',96,NULL,'active','2026-08-27 09:33:56'),(1218,'Sofi',97,NULL,'active','2026-08-27 09:33:56'),(1219,'Shenkor',97,NULL,'active','2026-08-27 09:33:56'),(1220,'Jinela',97,NULL,'active','2026-08-27 09:33:56'),(1221,'Hakim',97,NULL,'active','2026-08-27 09:33:56'),(1222,'Erer (HR)',97,NULL,'active','2026-08-27 09:33:56'),(1223,'Dire Teyara',97,NULL,'active','2026-08-27 09:33:56'),(1224,'Amir Nur',97,NULL,'active','2026-08-27 09:33:56'),(1225,'Aboker',97,NULL,'active','2026-08-27 09:33:56'),(1226,'Abadir',97,NULL,'active','2026-08-27 09:33:56'),(1227,'Akaki Kality Sub City',98,NULL,'active','2026-08-27 09:33:56'),(1228,'Nifas Silk Lafto Sub City',98,NULL,'active','2026-08-27 09:33:56'),(1229,'Kolfe Keraniyo Sub City',98,NULL,'active','2026-08-27 09:33:56'),(1230,'Bole Sub City',98,NULL,'active','2026-08-27 09:33:56'),(1231,'Lideta Sub City',98,NULL,'active','2026-08-27 09:33:56'),(1232,'Kirkos Sub City',98,NULL,'active','2026-08-27 09:33:56'),(1233,'Yeka Sub City',98,NULL,'active','2026-08-27 09:33:56'),(1234,'Addis Ketema Sub City',98,NULL,'active','2026-08-27 09:33:56'),(1235,'Arada Sub City',98,NULL,'active','2026-08-27 09:33:56'),(1236,'Gulele Sub City',98,NULL,'active','2026-08-27 09:33:56'),(1237,'Lemi Kura Sub City',98,NULL,'active','2026-08-27 09:33:56'),(1238,'Sabian',99,NULL,'active','2026-08-27 09:33:56'),(1239,'Malka Jabti /M.Jebdu',99,NULL,'active','2026-08-27 09:33:56'),(1240,'Legehare',99,NULL,'active','2026-08-27 09:33:56'),(1241,'Addis Ketema (DD)',99,NULL,'active','2026-08-27 09:33:56'),(1242,'Gende Kore',99,NULL,'active','2026-08-27 09:33:56'),(1243,'Dechatu',99,NULL,'active','2026-08-27 09:33:56'),(1244,'Hafat Issa',99,NULL,'active','2026-08-27 09:33:56'),(1245,'Kazira',99,NULL,'active','2026-08-27 09:33:56'),(1246,'Police Maret',99,NULL,'active','2026-08-27 09:33:56'),(1247,'Aseliso',100,NULL,'active','2026-08-27 09:33:56'),(1248,'Jeldessa',100,NULL,'active','2026-08-27 09:33:56'),(1249,'Wahil',100,NULL,'active','2026-08-27 09:33:56'),(1250,'Biyoawale',100,NULL,'active','2026-08-27 09:33:56'),(1251,'Setit Humera town',106,NULL,'active','2026-08-27 09:33:56'),(1252,'Korarit',106,NULL,'active','2026-08-27 09:33:56'),(1253,'Kafta Humera',106,NULL,'active','2026-08-27 09:33:56'),(1254,'Welkait',106,NULL,'active','2026-08-27 09:33:56'),(1255,'Tsegede',106,NULL,'active','2026-08-27 09:33:56'),(1256,'May Kadra',106,NULL,'active','2026-08-27 09:33:56'),(1257,'Dansha town',106,NULL,'active','2026-08-27 09:33:56'),(1258,'Awra',106,NULL,'active','2026-08-27 09:33:56'),(1259,'May Gaba',106,NULL,'active','2026-08-27 09:33:56'),(1260,'Tselemti /West Telemt',107,NULL,'active','2026-08-27 09:33:56'),(1261,'Laelay Tselemti',107,NULL,'active','2026-08-27 09:33:56'),(1262,'May Tsebri town',107,NULL,'active','2026-08-27 09:33:56'),(1263,'Raya Alamata',108,NULL,'active','2026-08-27 09:33:56'),(1264,'Ofla',108,NULL,'active','2026-08-27 09:33:56'),(1265,'Korem town',108,NULL,'active','2026-08-27 09:33:56'),(1266,'Alamata town',108,NULL,'active','2026-08-27 09:33:56'),(1267,'Zata',108,NULL,'active','2026-08-27 09:33:56'),(1268,'Chercher',108,NULL,'active','2026-08-27 09:33:56');
/*!40000 ALTER TABLE `woredas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zones`
--

DROP TABLE IF EXISTS `zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `zones` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `region_id` int unsigned DEFAULT NULL,
  `region_admin_id` int unsigned DEFAULT NULL,
  `admin_id` int unsigned DEFAULT NULL,
  `status` enum('active','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `region_admin_id` (`region_admin_id`),
  KEY `admin_id` (`admin_id`),
  KEY `region_id` (`region_id`),
  CONSTRAINT `zones_ibfk_1` FOREIGN KEY (`region_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `zones_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zones`
--

LOCK TABLES `zones` WRITE;
/*!40000 ALTER TABLE `zones` DISABLE KEYS */;
INSERT INTO `zones` VALUES (1,'South Gondar',NULL,NULL,20,'active','2026-08-25 08:07:22'),(2,'North Western',1,NULL,NULL,'active','2026-08-27 02:43:30'),(3,'Central',1,NULL,NULL,'active','2026-08-27 02:43:30'),(4,'Eastern',1,NULL,NULL,'active','2026-08-27 02:43:30'),(5,'Southern',1,NULL,NULL,'active','2026-08-27 02:43:30'),(6,'South Eastern',1,NULL,NULL,'active','2026-08-27 02:43:30'),(7,'Mekelle',1,NULL,NULL,'active','2026-08-27 02:43:30'),(8,'Awsi /Zone 1',2,NULL,NULL,'active','2026-08-27 02:43:30'),(9,'Kilbati /Zone 2',2,NULL,NULL,'active','2026-08-27 02:43:30'),(10,'Gabi /Zone 3',2,NULL,NULL,'active','2026-08-27 02:43:30'),(11,'Fanti /Zone 4',2,NULL,NULL,'active','2026-08-27 02:43:30'),(12,'Hari /Zone 5',2,NULL,NULL,'active','2026-08-27 02:43:30'),(13,'Mahi /Zone 6',2,NULL,NULL,'active','2026-08-27 02:43:30'),(14,'North Gondar',3,NULL,NULL,'active','2026-08-27 02:43:30'),(15,'South Gondar',3,NULL,NULL,'active','2026-08-27 02:43:30'),(16,'North Wello',3,NULL,NULL,'active','2026-08-27 02:43:30'),(17,'South Wello',3,NULL,NULL,'active','2026-08-27 02:43:30'),(18,'North Shewa (AM)',3,NULL,NULL,'active','2026-08-27 02:43:30'),(19,'East Gojam',3,NULL,NULL,'active','2026-08-27 02:43:30'),(20,'West Gojam',3,NULL,NULL,'active','2026-08-27 02:43:30'),(21,'Wag Hamra',3,NULL,NULL,'active','2026-08-27 02:43:30'),(22,'Awi',3,NULL,NULL,'active','2026-08-27 02:43:30'),(23,'Oromo Nationality Administration',3,NULL,NULL,'active','2026-08-27 02:43:30'),(24,'Central Gondar',3,NULL,NULL,'active','2026-08-27 02:43:30'),(25,'West Gondar',3,NULL,NULL,'active','2026-08-27 02:43:31'),(26,'North Gojam',3,NULL,NULL,'active','2026-08-27 02:43:31'),(27,'Bahir Dar town Admin',3,NULL,NULL,'active','2026-08-27 02:43:31'),(28,'West Wellega',4,NULL,NULL,'active','2026-08-27 02:43:31'),(29,'East Wellega',4,NULL,NULL,'active','2026-08-27 02:43:31'),(30,'Ilu Aba Bora',4,NULL,NULL,'active','2026-08-27 02:43:31'),(31,'Jimma',4,NULL,NULL,'active','2026-08-27 02:43:31'),(32,'West Shewa',4,NULL,NULL,'active','2026-08-27 02:43:31'),(33,'North Shewa (OR)',4,NULL,NULL,'active','2026-08-27 02:43:31'),(34,'East Shewa',4,NULL,NULL,'active','2026-08-27 02:43:31'),(35,'Arsi',4,NULL,NULL,'active','2026-08-27 02:43:31'),(36,'West Hararge',4,NULL,NULL,'active','2026-08-27 02:43:31'),(37,'East Hararge',4,NULL,NULL,'active','2026-08-27 02:43:31'),(38,'Bale',4,NULL,NULL,'active','2026-08-27 02:43:31'),(39,'Borena',4,NULL,NULL,'active','2026-08-27 02:43:31'),(40,'South West Shewa',4,NULL,NULL,'active','2026-08-27 02:43:31'),(41,'Guji',4,NULL,NULL,'active','2026-08-27 02:43:31'),(42,'West Guji',4,NULL,NULL,'active','2026-08-27 02:43:31'),(43,'Buno Bedele',4,NULL,NULL,'active','2026-08-27 02:43:31'),(44,'West Arsi',4,NULL,NULL,'active','2026-08-27 02:43:31'),(45,'Kelem Wellega',4,NULL,NULL,'active','2026-08-27 02:43:31'),(46,'Horo Gudru Wellega',4,NULL,NULL,'active','2026-08-27 02:43:31'),(47,'Shager City',4,NULL,NULL,'active','2026-08-27 02:43:31'),(48,'East Bale',4,NULL,NULL,'active','2026-08-27 02:43:31'),(49,'East Borena',4,NULL,NULL,'active','2026-08-27 02:43:31'),(50,'Siti',5,NULL,NULL,'active','2026-08-27 02:43:31'),(51,'Fafan',5,NULL,NULL,'active','2026-08-27 02:43:31'),(52,'Jarar',5,NULL,NULL,'active','2026-08-27 02:43:31'),(53,'Erer',5,NULL,NULL,'active','2026-08-27 02:43:31'),(54,'Korahe',5,NULL,NULL,'active','2026-08-27 02:43:31'),(55,'Shabelle',5,NULL,NULL,'active','2026-08-27 02:43:31'),(56,'Doolo',5,NULL,NULL,'active','2026-08-27 02:43:31'),(57,'Afder',5,NULL,NULL,'active','2026-08-27 02:43:31'),(58,'Liban',5,NULL,NULL,'active','2026-08-27 02:43:31'),(59,'Nogob',5,NULL,NULL,'active','2026-08-27 02:43:31'),(60,'Daawa',5,NULL,NULL,'active','2026-08-27 02:43:31'),(61,'Metekel',6,NULL,NULL,'active','2026-08-27 02:43:31'),(62,'Assosa',6,NULL,NULL,'active','2026-08-27 02:43:31'),(63,'Kamashi',6,NULL,NULL,'active','2026-08-27 02:43:31'),(64,'Mao-komo Special',6,NULL,NULL,'active','2026-08-27 02:43:31'),(65,'Kebena Special',7,NULL,NULL,'active','2026-08-27 02:43:31'),(66,'Guraghe',7,NULL,NULL,'active','2026-08-27 02:43:31'),(67,'Hadiya',7,NULL,NULL,'active','2026-08-27 02:43:31'),(68,'Kembata',7,NULL,NULL,'active','2026-08-27 02:43:31'),(69,'East Guraghe',7,NULL,NULL,'active','2026-08-27 02:43:31'),(70,'Halaba',7,NULL,NULL,'active','2026-08-27 02:43:31'),(71,'Siltie',7,NULL,NULL,'active','2026-08-27 02:43:31'),(72,'Yem',7,NULL,NULL,'active','2026-08-27 02:43:31'),(73,'Mareko Special',7,NULL,NULL,'active','2026-08-27 02:43:31'),(74,'Tembaro Special',7,NULL,NULL,'active','2026-08-27 02:43:31'),(75,'Wolayita',8,NULL,NULL,'active','2026-08-27 02:43:31'),(76,'Gamo',8,NULL,NULL,'active','2026-08-27 02:43:31'),(77,'Gofa',8,NULL,NULL,'active','2026-08-27 02:43:31'),(78,'Basketo',8,NULL,NULL,'active','2026-08-27 02:43:31'),(79,'Ari',8,NULL,NULL,'active','2026-08-27 02:43:31'),(80,'Alle Special',8,NULL,NULL,'active','2026-08-27 02:43:31'),(81,'Derashe',8,NULL,NULL,'active','2026-08-27 02:43:31'),(82,'Kore',8,NULL,NULL,'active','2026-08-27 02:43:31'),(83,'Konso',8,NULL,NULL,'active','2026-08-27 02:43:31'),(84,'Burji',8,NULL,NULL,'active','2026-08-27 02:43:31'),(85,'Gedeo',8,NULL,NULL,'active','2026-08-27 02:43:31'),(86,'South Omo',8,NULL,NULL,'active','2026-08-27 02:43:31'),(87,'Sheka',9,NULL,NULL,'active','2026-08-27 02:43:31'),(88,'Kefa',9,NULL,NULL,'active','2026-08-27 02:43:31'),(89,'Bench Sheko',9,NULL,NULL,'active','2026-08-27 02:43:31'),(90,'Dawuro',9,NULL,NULL,'active','2026-08-27 02:43:31'),(91,'West Omo',9,NULL,NULL,'active','2026-08-27 02:43:31'),(92,'Konta',9,NULL,NULL,'active','2026-08-27 02:43:31'),(93,'Nuwer',10,NULL,NULL,'active','2026-08-27 02:43:31'),(94,'Agnewak',10,NULL,NULL,'active','2026-08-27 02:43:31'),(95,'Majang',10,NULL,NULL,'active','2026-08-27 02:43:31'),(96,'Itang Special',10,NULL,NULL,'active','2026-08-27 02:43:31'),(97,'Harari',11,NULL,NULL,'active','2026-08-27 02:43:31'),(98,'Addis Ababa',12,NULL,NULL,'active','2026-08-27 02:43:31'),(99,'Dire Dawa urban',13,NULL,NULL,'active','2026-08-27 02:43:31'),(100,'Dire Dawa rural',13,NULL,NULL,'active','2026-08-27 02:43:31'),(101,'Hawassa town Admin',14,NULL,NULL,'active','2026-08-27 02:43:31'),(102,'Northern',14,NULL,NULL,'active','2026-08-27 02:43:31'),(103,'Central',14,NULL,NULL,'active','2026-08-27 02:43:31'),(104,'Southern',14,NULL,NULL,'active','2026-08-27 02:43:31'),(105,'Eastern',14,NULL,NULL,'active','2026-08-27 02:43:31'),(106,'Area 1',15,NULL,NULL,'active','2026-08-27 02:43:31'),(107,'Area 2',15,NULL,NULL,'active','2026-08-27 02:43:31'),(108,'Area 3',15,NULL,NULL,'active','2026-08-27 02:43:31');
/*!40000 ALTER TABLE `zones` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-01 14:09:45
