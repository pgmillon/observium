ALTER TABLE `applications` ADD  `app_instance` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER  `app_type`;
ALTER TABLE `applications` ADD UNIQUE  `unique` (  `device_id` ,  `app_type` ,  `app_instance` );
