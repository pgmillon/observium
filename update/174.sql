#IGNORE_ERROR
ALTER TABLE `syslog` ADD INDEX `program_device` (`program`, `device_id`);
ALTER TABLE `eventlog` ADD INDEX `type_device` (`type`, `device_id`);
ALTER TABLE `alert_log` ADD INDEX `alert_device` (`alert_test_id`, `device_id`);
