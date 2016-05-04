ALTER TABLE `alert_table` ADD INDEX (`device_id`);
ALTER TABLE `devices_attribs` ADD INDEX (`device_id`);
ALTER TABLE `devices_attribs` ADD INDEX `device_type` (`device_id`,`attrib_type`(50));
ALTER TABLE `devices_locations` ADD INDEX (`device_id`);
