# Index works with INNODB after modifying app_instance to VARCHAR(255) - Ciro Iriarte - 2013.02.17
#ALTER TABLE  `applications` ENGINE = MyISAM;
#ALTER TABLE `applications` ADD  `app_instance` VARCHAR( 256 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER  `app_type`;
#ALTER TABLE `applications` ADD UNIQUE  `unique` (  `device_id` ,  `app_type` ,  `app_instance` );
