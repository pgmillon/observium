#ERROR_IGNORE
ALTER TABLE `sensors` ADD `sensor_unit` VARCHAR(16) NULL DEFAULT NULL AFTER `sensor_descr`;
#this duplicate entry since in some old installs already exist column `sensor_unit`
ALTER TABLE `sensors` CHANGE `sensor_unit` `sensor_unit` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
