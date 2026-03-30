-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: klimatici_db
-- ------------------------------------------------------
-- Server version	8.0.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_type` enum('client','employee','admin') NOT NULL DEFAULT 'client',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@klimatici.bg','$2y$10$abcdefghijklmnopqrstuvwxyz','Иван Иванов','0888123456','admin',1,'2026-01-26 20:23:04','2026-01-26 20:23:04'),(2,'test@klimatici.bg','$2y$10$k.runhAhBF3P.7o5rYTQyObNWpjt/qJnjbx5CWbx/NqMms.DW..c2','Тест Потребител','0888123456','client',1,'2026-01-26 21:17:54','2026-01-26 21:17:54'),(3,'nikiK@gmail.com','$2y$10$Ma82aO4idnL7KvP.iPD90eFP8P61osi364ZwCyAdLo1OrRjn.rSWe','Николай Кънчев','0887953324','client',1,'2026-01-26 21:27:01','2026-01-26 21:27:01'),(4,'alexpop@gmail.com','$2y$10$xNZCu2C8Zz1IgQO/aTdmjOOUn9NViDh4svhwfCM8edbhyuGk2jrha','Александър Попов','087975321','employee',1,'2026-01-26 21:32:52','2026-01-26 21:32:52'),(5,'ivan123@gmail.com','$2y$10$rTaks5hq1WJGR3USV1sr9ea2OOppwBMEQ41N0ve2NZatVRdG1B68S','Иван Иванов','0887975124','client',1,'2026-01-28 10:40:19','2026-01-28 10:40:19'),(6,'slavi@gmail.com','$2y$10$0ejT7lxx2Mu2tBXmxfg8UuDaFvvNEIwRqZFF.nsytEQMpF49xN1WC','Svetoslav Panow','0887976123','admin',1,'2026-02-07 12:09:12','2026-02-07 12:12:32'),(8,'niki@gmail.com','$2y$10$iZpIu3CA2bC7txcJFl2gFuyU1DdfdErXxoxz89Obw/xP7WYdUow/.','Ники Кънчев','0888','employee',1,'2026-03-02 10:39:20','2026-03-02 10:39:20'),(9,'dimanMihnev@festo.com','$2y$10$V8uxjSnZDNt/.BPcVyNoR.bpWylm9Ei52wDPgnn8iRu4GMNdN.I6i','Диман Михнев','0887654212','client',1,'2026-03-18 10:06:30','2026-03-18 10:06:30');
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

-- Dump completed on 2026-03-28  9:21:56
