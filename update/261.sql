#IGNORE_ERROR
ALTER TABLE `pseudowires` DROP `pwMplsPeerLdpID`;
ALTER TABLE `pseudowires` CHANGE `pwIndex` `pwIndex` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `pwLocalIfMtu` `pwLocalIfMtu` INT(11) UNSIGNED NULL DEFAULT NULL, CHANGE `pwRemoteIfMtu` `pwRemoteIfMtu` INT(11) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `pseudowires` ADD `pwOutboundLabel` INT(11) UNSIGNED NOT NULL AFTER `pwID`, ADD `pwInboundLabel` INT(11) UNSIGNED NOT NULL AFTER `pwOutboundLabel`;
ALTER TABLE `pseudowires` ADD `pwRowStatus` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `pwRemoteIfString`;
ALTER TABLE `pseudowires` ADD INDEX `row_status` (`device_id`, `pwRowStatus`);
CREATE TABLE `pseudowires-state` ( `pseudowire_id` INT(11) UNSIGNED NOT NULL , `pwOperStatus` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `pwLocalStatus` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `pwRemoteStatus` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `pwUptime` INT(11) UNSIGNED NOT NULL , `event` ENUM('ok','warning','alert','ignore') NOT NULL , `last_change` INT(11) UNSIGNED NOT NULL , PRIMARY KEY (`pseudowire_id`)) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
