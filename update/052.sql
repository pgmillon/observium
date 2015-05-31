CREATE TABLE IF NOT EXISTS `ip_mac` ( `mac_id` int(11) NOT NULL AUTO_INCREMENT, `port_id` int(11) NOT NULL, `mac_address` char(12) NOT NULL, `ip_address` varchar(39) NOT NULL, `ip_version` tinyint(4) NOT NULL, PRIMARY KEY (`mac_id`), KEY `port_id` (`port_id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `ipv4_mac`;
