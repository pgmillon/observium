ALTER TABLE `dbSchema` CHANGE `version` `attrib_value` VARCHAR(255) CHARACTER SET `utf8` COLLATE `utf8_unicode_ci`;
ALTER TABLE `dbSchema` ADD `attrib_type` VARCHAR(255) CHARACTER SET `utf8` COLLATE `utf8_unicode_ci` NOT NULL DEFAULT 'dbSchema' FIRST;
ALTER TABLE `dbSchema` DROP PRIMARY KEY;
ALTER TABLE `dbSchema` ADD PRIMARY KEY (`attrib_type`);
ALTER TABLE `dbSchema` CHANGE `attrib_type` `attrib_type` VARCHAR(255) CHARACTER SET `utf8` COLLATE `utf8_unicode_ci` NOT NULL;
ALTER TABLE `dbSchema` RENAME TO `observium_attribs`;