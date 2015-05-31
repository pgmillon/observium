ALTER TABLE  `alert_tests` ADD  `show_frontpage` INT( 1 ) NOT NULL DEFAULT  '1' AFTER  `enable` ;
ALTER TABLE  `alert_tests` CHANGE  `severity`  `severity` ENUM(  'crit',  'err' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'crit';
UPDATE `alert_tests` SET `severity` = 'crit' WHERE 1;
ALTER TABLE  `alert_tests` CHANGE  `conditions`  `conditions` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
