SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `flare` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `flare`;

DROP TABLE IF EXISTS `aircraft`;
CREATE TABLE IF NOT EXISTS `aircraft` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `ifliveryid` varchar(36) NOT NULL,
  `rankreq` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `news`;
CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` text NOT NULL,
  `content` text NOT NULL,
  `author` text NOT NULL,
  `dateposted` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `pilots`;
CREATE TABLE IF NOT EXISTS `pilots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `callsign` varchar(8) NOT NULL,
  `name` text NOT NULL,
  `ifc` text NOT NULL,
  `ifuserid` varchar(36) DEFAULT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `transhours` int(11) NOT NULL DEFAULT '0',
  `transflights` int(11) NOT NULL DEFAULT '0',
  `violand` double DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  `notes` varchar(1200) NOT NULL DEFAULT '',
  `darkmode` tinyint(1) NOT NULL DEFAULT '0',
  `permissions` text NOT NULL,
  `status` int(3) NOT NULL DEFAULT '0',
  `joined` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `callsign` (`callsign`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `pireps`;
CREATE TABLE IF NOT EXISTS `pireps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flightnum` int(11) NOT NULL,
  `departure` varchar(4) NOT NULL,
  `arrival` varchar(4) NOT NULL,
  `flighttime` int(11) NOT NULL,
  `pilotid` int(11) NOT NULL,
  `date` date NOT NULL,
  `aircraftid` int(11) NOT NULL,
  `multi` text NOT NULL,
  `status` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ranks`;
CREATE TABLE IF NOT EXISTS `ranks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `timereq` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

INSERT INTO `ranks` (`id`, `name`, `timereq`) VALUES
(4, 'Cadet', 0);

DROP TABLE IF EXISTS `routes`;
CREATE TABLE IF NOT EXISTS `routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typecode` varchar(4) NOT NULL,
  `fltnum` int(11) NOT NULL,
  `dep` varchar(4) NOT NULL,
  `arr` varchar(4) NOT NULL,
  `duration` int(11) NOT NULL,
  `aircraftid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
COMMIT;
