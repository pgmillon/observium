ALTER TABLE `sensors-state` ADD `sensor_last_change` INT NOT NULL AFTER `sensor_polled`;
UPDATE `sensors-state` SET `sensor_event` = 'ok' WHERE `sensor_event` = 'up';
ALTER TABLE `sensors-state` CHANGE `sensor_event` `sensor_event` ENUM('ok','warning','alert','ignore') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `status-state` CHANGE `status_event` `status_event` ENUM('ok','warning','alert','ignore') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
