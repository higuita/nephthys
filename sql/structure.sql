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
-- Table structure for table `nephthys_buckets`
--

DROP TABLE IF EXISTS `nephthys_buckets`;
CREATE TABLE `nephthys_buckets` (
  `bucket_idx` int(11) NOT NULL auto_increment,
  `bucket_name` varchar(255) default NULL,
  `bucket_sender` varchar(255) default NULL,
  `bucket_receiver` varchar(255) default NULL,
  `bucket_hash` varchar(64) default NULL,
  `bucket_created` int(11) default NULL,
  `bucket_expire` int(11) default NULL,
  `bucket_note` text,
  `bucket_owner` int(11) default NULL,
  `bucket_active` varchar(1) default NULL,
  PRIMARY KEY  (`bucket_idx`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

--
-- Table structure for table `nephthys_groups`
--

DROP TABLE IF EXISTS `nephthys_groups`;
CREATE TABLE `nephthys_groups` (
  `group_idx` int(11) NOT NULL auto_increment,
  `group_name` varchar(255) default NULL,
  `group_active` varchar(1) default NULL,
  PRIMARY KEY  (`group_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `nephthys_user_to_groups`
--

DROP TABLE IF EXISTS `nephthys_user_to_groups`;
CREATE TABLE `nephthys_user_to_groups` (
  `ug_user_idx` int(11) default NULL,
  `ug_group_idx` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `nephthys_users`
--

DROP TABLE IF EXISTS `nephthys_users`;
CREATE TABLE `nephthys_users` (
  `user_idx` int(11) NOT NULL auto_increment,
  `user_name` varchar(255) default NULL,
  `user_full_name` varchar(255) default NULL,
  `user_pass` varchar(255) default NULL,
  `user_email` varchar(255) default NULL,
  `user_priv` varchar(16) default NULL,
  `user_active` varchar(1) default NULL,
  `user_last_login` int(11) default NULL,
  `user_default_expire` int(11) default NULL,
  `user_auto_created` varchar(1) default NULL,
  PRIMARY KEY  (`user_idx`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2008-03-24  7:36:10
