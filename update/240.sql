ALTER TABLE `devices_attribs` ADD `entity_type` VARCHAR(32) NOT NULL AFTER `attrib_id`;
ALTER TABLE `devices_attribs` CHANGE `device_id` `entity_id` INT(11) NOT NULL;
UPDATE `devices_attribs` SET `entity_type` = 'device' WHERE 1;
RENAME TABLE `devices_attribs` TO `entity_attribs`;
