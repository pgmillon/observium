ALTER TABLE `devices_locations` DROP INDEX `device_id`;
ALTER TABLE `devices_locations` ADD UNIQUE KEY `device_id` (`device_id`);