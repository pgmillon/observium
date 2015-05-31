CREATE TABLE IF NOT EXISTS `devices_perftimes` (  `device_id` int(11) NOT NULL,  `operation` varchar(32) COLLATE utf8_unicode_ci NOT NULL,  `start` int(11) NOT NULL,  `duration` double(8,2) NOT NULL,  KEY `device_id` (`device_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

