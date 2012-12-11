-- Copyright (C) 2005 The Linux Box Corp.  All rights reserved.
--   206 S. Fifth Avenue Suite 150
--   Ann Arbor, MI 48104
--   http://www.linuxbox.com
-- Written by Ryan Hughes (ryan@linuxbox.com)
--  
-- This program is free software; you can redistribute it and/or
-- modify it under the terms of the GNU General Public License
-- as published by the Free Software Foundation; either version
-- 2 of the License, or (at your option) any later version.

-- MySQL dump 10.9
--
-- Host: localhost    Database: brook_sing_tsung_test
-- ------------------------------------------------------
-- Server version	4.1.21-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `profile`
--

DROP TABLE IF EXISTS `profile`;
CREATE TABLE `profile` (
  `id` int(9) NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `client` varchar(255) default NULL,
  `use_controller_vm` int(1) default NULL,
  `server_host` varchar(255) default NULL,
  `server_port` int(6) default NULL,
  `load_arrival_phase_minutes` int(11) default NULL,
  `load_interarrival_duration_sec` double default NULL,
  `maxusers` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;

--
-- Table structure for table `profile_sessions`
--

DROP TABLE IF EXISTS `profile_sessions`;
CREATE TABLE `profile_sessions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `profile_id` int(11) default NULL,
  `session_id` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=95 DEFAULT CHARSET=utf8;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
CREATE TABLE `session` (
  `id` int(9) NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `data` blob,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `name_2` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=80 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

