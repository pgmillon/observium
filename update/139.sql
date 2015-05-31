ALTER TABLE  `alert_contacts` CHANGE  `contact_type`  `contact_method` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ;
ALTER TABLE  `alert_contacts` CHANGE  `contact_dest`  `contact_endpoint` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ;
ALTER TABLE  `alert_contacts` ADD  `contact_disabled` BOOLEAN NOT NULL DEFAULT FALSE ;
ALTER TABLE  `alert_contacts` ADD  `contact_disabled_until` INT NULL DEFAULT NULL ;
CREATE TABLE IF NOT EXISTS `alert_contacts_assoc` (  `aca_id` int(11) NOT NULL AUTO_INCREMENT,  `alert_checker_id` int(11) NOT NULL,  `alert_contact_id` int(11) NOT NULL,  PRIMARY KEY (`aca_id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
