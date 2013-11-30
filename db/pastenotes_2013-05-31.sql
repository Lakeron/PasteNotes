# ************************************************************
# Sequel Pro SQL dump
# Version 4004
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.27)
# Database: pastenotes
# Generation Time: 2013-05-30 22:32:33 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table notes
# ------------------------------------------------------------

CREATE TABLE `notes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` text,
  `request` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;

INSERT INTO `notes` (`id`, `code`, `request`)
VALUES
	(13,'0c9252e08a0e05ef4cdf93acf9e48e78',X'613A313A7B733A343A22636F6465223B733A33323A223063393235326530386130653035656634636466393361636639653438653738223B7D'),
	(14,'5f529fdd0b49e9a16927205163fb5d40',X'613A313A7B733A343A22636F6465223B733A33323A223566353239666464306234396539613136393237323035313633666235643430223B7D');

/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pools
# ------------------------------------------------------------

CREATE TABLE `pools` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `note_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isDeleted` tinyint(4) DEFAULT '0',
  `isDone` tinyint(4) DEFAULT '0',
  `isActive` tinyint(4) DEFAULT '0',
  `position` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  KEY `note_id` (`note_id`),
  CONSTRAINT `pools_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `pools` WRITE;
/*!40000 ALTER TABLE `pools` DISABLE KEYS */;

INSERT INTO `pools` (`id`, `note_id`, `name`, `isDeleted`, `isDone`, `isActive`)
VALUES
	(10,13,'Done',0,1,0),
	(11,13,'Active pool',0,0,1),
	(12,13,'Deleted',1,0,0),
	(13,14,'Done',0,1,0),
	(14,14,'Active pool',0,0,1),
	(15,14,'Deleted',1,0,0),
	(21,13,'Today todo\'s',0,0,0),
	(32,13,'New pool',0,0,0);

/*!40000 ALTER TABLE `pools` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tasks
# ------------------------------------------------------------

CREATE TABLE `tasks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `note_id` int(11) unsigned DEFAULT NULL,
  `text` text,
  `pool_id` int(11) unsigned DEFAULT '2',
  `position` int(11) DEFAULT '0',
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `note_id` (`note_id`),
  KEY `status_id` (`pool_id`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`pool_id`) REFERENCES `pools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;

INSERT INTO `tasks` (`id`, `note_id`, `text`, `pool_id`, `position`, `createTime`)
VALUES
	(120,13,'test',21,1,'2013-05-31 00:30:28'),
	(121,13,'test2',11,1,'2013-05-31 00:29:56'),
	(122,13,'test3',21,5,'2013-05-31 00:30:06'),
	(123,13,'test4',21,3,'2013-05-31 00:30:06'),
	(124,13,'test5',21,6,'2013-05-31 00:30:06'),
	(125,13,'super test',21,4,'2013-05-31 00:30:06'),
	(126,13,'super test2',32,1,'2013-05-31 00:30:24');

/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
