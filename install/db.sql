CREATE TABLE IF NOT EXISTS `aircraft` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `ifaircraftid` text NOT NULL,
  `liveryname` text DEFAULT NULL,
  `ifliveryid` text DEFAULT NULL,
  `notes` VARCHAR(12),
  `rankreq` int(11) NOT NULL DEFAULT '1',
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` text NOT NULL,
  `content` text NOT NULL,
  `author` text NOT NULL,
  `dateposted` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `news` (`subject`, `content`, `author`) VALUES
('Welcome to Flare!', 'Thank You for installing Flare, the Crew Center made for Infinite Flight and integrated with VANet. Before you get your pilots set up with Flare, here are some things to keep in mind.\r\n\r\nFirst of all, if it\'s you\'re not a fan of the orange (kudos if you get the reference), you can change the color theme in site settings.\r\nNext, it\'s worth keeping in mind you can add transfer hours and transfer flights to your pilots. This means you can bring over hours and the number of PIREPs a pilot has filed from another Crew Center. This can be done in the admin panel.\r\nFinally, if you\'re confused with Flare at all check out the tutorials available at https://vanet.app/tutorials.\r\n\r\nEnjoy!', 'Flare Installer');

CREATE TABLE IF NOT EXISTS `pilots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `callsign` varchar(120) NOT NULL,
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
  `status` int(3) NOT NULL DEFAULT '0',
  `joined` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `callsign` (`callsign`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `userid` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pireps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flightnum` varchar(10) NOT NULL,
  `departure` varchar(4) NOT NULL,
  `arrival` varchar(4) NOT NULL,
  `flighttime` int(11) NOT NULL,
  `pilotid` int(11) NOT NULL,
  `date` date NOT NULL,
  `aircraftid` int(11) NOT NULL,
  `multi` text NOT NULL,
  `status` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `ranks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `timereq` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `ranks` (`name`, `timereq`) VALUES
('Cadet', 0);

INSERT INTO `ranks` (`name`, `timereq`) VALUES
('Second Officer', 25);

CREATE TABLE IF NOT EXISTS `routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fltnum` varchar(10) NOT NULL,
  `dep` varchar(4) NOT NULL,
  `arr` varchar(4) NOT NULL,
  `duration` int(11) NOT NULL,
  `aircraftid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
COMMIT;

CREATE TABLE IF NOT EXISTS `multipliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) NOT NULL,
  `multiplier` double NOT NULL,
  `name` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `options` ( 
  `name` varchar(120) NOT NULL, 
  `value` text NOT NULL, 
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `options` (`name`, `value`) VALUES ('FORCE_SERVER', '0');
INSERT INTO `options` (`name`, `value`) VALUES ('CHECK_PRERELEASE', '0');
INSERT INTO `options` (`name`, `value`) VALUES ('TEXT_COLOUR', '#fff');
INSERT INTO `options` (`name`, `value`) VALUES ('VA_CALLSIGN_FORMAT', '/.*/i');