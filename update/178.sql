DELETE FROM `pseudowires` WHERE `cpwOid` = '2147483647';
ALTER TABLE `pseudowires` CHANGE `cpwOid` `cpwOid` INT(11) UNSIGNED NOT NULL AFTER `device_id`;
ALTER TABLE `pseudowires` CHANGE `cpwVcID` `cpwVcID` INT(11) NOT NULL AFTER `cpwOid`;
ALTER TABLE `pseudowires` ADD `reverse_dns` VARCHAR(255) NOT NULL DEFAULT '' AFTER `peer_ldp_id`;
ALTER TABLE `pseudowires` ADD `peer_descr` VARCHAR(128) NULL;
ALTER TABLE `pseudowires` ADD INDEX (`port_id`);
ALTER TABLE `pseudowires` ADD INDEX (`device_id`);
