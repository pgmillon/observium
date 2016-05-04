#ALTER TABLE `sensors` ADD `entPhysicalIndex_measured` VARCHAR(16) COLLATE utf8_unicode_ci DEFAULT NULL AFTER `sensor_custom_limit`;
#ALTER TABLE `sensors` ADD `measured_class` VARCHAR( 32 ) NULL DEFAULT NULL AFTER  `entPhysicalIndex_measured`;
#ALTER TABLE `sensors` ADD `measured_entity` VARCHAR( 32 ) NULL DEFAULT NULL AFTER  `measured_class`;