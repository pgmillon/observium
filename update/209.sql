ALTER TABLE  `sensors` ADD  `sensor_mib` VARCHAR( 64 ) NULL DEFAULT NULL AFTER  `sensor_oid` ;
ALTER TABLE  `sensors` ADD  `sensor_object` VARCHAR( 64 ) NULL DEFAULT NULL AFTER  `sensor_mib` ;
ALTER TABLE  `sensors` DROP `sensor_divisor` ;
ALTER TABLE  `status`  ADD  `status_mib` VARCHAR( 64 ) NULL DEFAULT NULL AFTER  `status_oid` ;
ALTER TABLE  `status`  ADD  `status_object` VARCHAR( 64 ) NULL DEFAULT NULL AFTER  `status_mib` ;
ALTER TABLE  `status` CHANGE  `status_type`  `status_type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ;
ALTER TABLE  `sensors` CHANGE  `sensor_type`  `sensor_type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ;
