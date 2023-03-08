CREATE TABLE `aircraft` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `ifaircraftid` text NOT NULL,
  `liveryname` text DEFAULT NULL,
  `ifliveryid` text DEFAULT NULL,
  `notes` varchar(12) DEFAULT NULL,
  `rankreq` int(11) DEFAULT NULL,
  `awardreq` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `awards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `imageurl` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `awards_granted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `awardid` int(11) NOT NULL,
  `pilotid` int(11) NOT NULL,
  `dateawarded` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `cache` (
  `name` varchar(120) NOT NULL,
  `value` text NOT NULL,
  `expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `hub_changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pilotId` int(11) NOT NULL,
  `before` varchar(4) NOT NULL,
  `after` varchar(4) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `leave_absence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pilotid` int(11) NOT NULL,
  `fromdate` date NOT NULL,
  `todate` date NOT NULL,
  `reason` text NOT NULL,
  `status` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `multipliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) NOT NULL,
  `multiplier` double NOT NULL,
  `name` varchar(120) NOT NULL,
  `minrankid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` text NOT NULL,
  `content` text NOT NULL,
  `author` text NOT NULL,
  `dateposted` datetime NOT NULL DEFAULT current_timestamp(),
  `status` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pilotid` int(11) NOT NULL,
  `icon` varchar(20) NOT NULL,
  `subject` varchar(20) NOT NULL,
  `content` varchar(60) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `options` (
  `name` varchar(120) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `userid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pilots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `callsign` varchar(120) NOT NULL,
  `name` text NOT NULL,
  `ifc` text NOT NULL,
  `ifuserid` varchar(36) DEFAULT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `transhours` int(11) NOT NULL DEFAULT 0,
  `transflights` int(11) NOT NULL DEFAULT 0,
  `violand` double DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  `notes` varchar(1200) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT 0,
  `joined` datetime NOT NULL DEFAULT current_timestamp(),
  `vanet_id` text DEFAULT NULL,
  `vanet_accesstoken` text DEFAULT NULL,
  `vanet_refreshtoken` text DEFAULT NULL,
  `vanet_expiry` datetime DEFAULT NULL,
  `vanet_memberid` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `pilot_hubs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pilotId` int(11) NOT NULL,
  `hub` varchar(4) NOT NULL,
  `isCaptain` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `pireps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flightnum` text DEFAULT NULL,
  `departure` varchar(4) NOT NULL,
  `arrival` varchar(4) NOT NULL,
  `flighttime` int(11) NOT NULL,
  `pilotid` int(11) NOT NULL,
  `date` date NOT NULL,
  `aircraftid` int(11) NOT NULL,
  `fuelused` int(11) NOT NULL,
  `multi` text NOT NULL,
  `status` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `pireps_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pirepid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `content` text NOT NULL,
  `dateposted` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ranks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `timereq` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fltnum` text DEFAULT NULL,
  `dep` varchar(4) NOT NULL,
  `arr` varchar(4) NOT NULL,
  `duration` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `route_aircraft` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `routeid` int(11) NOT NULL,
  `aircraftid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `news` (`subject`, `content`, `author`) VALUES
('Welcome to Flare!', 'Thank You for installing Flare, the Crew Center made for Infinite Flight and integrated with VANet. Before you get your pilots set up with Flare, here are some things to keep in mind.\r\n\r\nFirst of all, if it\'s you\'re not a fan of the orange (kudos if you get the reference), you can change the color theme in site settings.\r\nNext, it\'s worth keeping in mind you can add transfer hours and transfer flights to your pilots. This means you can bring over hours and the number of PIREPs a pilot has filed from another Crew Center. This can be done in the admin panel.\r\nFinally, if you\'re confused with Flare at all check out the tutorials available at https://vanet.app/tutorials.\r\n\r\nEnjoy!', 'Flare Installer');

INSERT INTO `ranks` (`name`, `timereq`) VALUES
('Cadet', 0);

INSERT INTO `ranks` (`name`, `timereq`) VALUES
('Second Officer', 25);

INSERT INTO `options` (`name`, `value`) VALUES ('FORCE_SERVER', '0');
INSERT INTO `options` (`name`, `value`) VALUES ('CHECK_PRERELEASE', '0');
INSERT INTO `options` (`name`, `value`) VALUES ('TEXT_COLOUR', '#fff');
INSERT INTO `options` (`name`, `value`) VALUES ('VA_CALLSIGN_FORMAT', '/.*/i');