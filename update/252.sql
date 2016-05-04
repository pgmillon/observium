#unused tables
DROP TABLE IF EXISTS `graph_types`;
DROP TABLE IF EXISTS `graph_types_dead`;
#fix encoding

#compatability with strict mode
ALTER TABLE `devices` CHANGE `location` `location` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `authlog` CHANGE `user_agent` `user_agent` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE `observium_attribs` CHANGE `attrib_type` `attrib_type` VARCHAR(255) CHARACTER SET `utf8` COLLATE `utf8_unicode_ci` NOT NULL;
ALTER TABLE `ports` CHANGE `ifLastChange` `ifLastChange` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
#long operation!
ALTER TABLE `eventlog` CHANGE `timestamp` `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;