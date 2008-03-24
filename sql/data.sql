-- MySQL dump 10.11
--
-- Host: localhost    Database: db_nephthys
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_10-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `nephthys_buckets`
--

LOCK TABLES `nephthys_buckets` WRITE;
/*!40000 ALTER TABLE `nephthys_buckets` DISABLE KEYS */;
/*!40000 ALTER TABLE `nephthys_buckets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `nephthys_groups`
--

LOCK TABLES `nephthys_groups` WRITE;
/*!40000 ALTER TABLE `nephthys_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `nephthys_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `nephthys_user_to_groups`
--

LOCK TABLES `nephthys_user_to_groups` WRITE;
/*!40000 ALTER TABLE `nephthys_user_to_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `nephthys_user_to_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `nephthys_users`
--

LOCK TABLES `nephthys_users` WRITE;
/*!40000 ALTER TABLE `nephthys_users` DISABLE KEYS */;
INSERT INTO `nephthys_users` VALUES (1,'admin','','d033e22ae348aeb5660fc2140aec35850c4da997','','admin','Y',NULL,7,NULL);
/*!40000 ALTER TABLE `nephthys_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2008-03-24  7:36:16
