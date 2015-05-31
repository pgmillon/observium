ALTER TABLE `pseudowires` ADD `peer_addr` VARCHAR(128) NOT NULL AFTER `peer_device_id`;
ALTER TABLE `pseudowires` CHANGE `peer_device_id` `peer_device_id` INT(11) NULL, CHANGE `peer_ldp_id` `peer_ldp_id` VARCHAR(32) NULL;
