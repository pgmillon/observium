TRUNCATE `loadbalancer_vservers`;
ALTER TABLE `loadbalancer_vservers` DROP `classmap_id`;
ALTER TABLE `loadbalancer_vservers` ADD `classmap_id` INT(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`classmap_id`);
ALTER TABLE `loadbalancer_vservers` ADD `classmap_index` VARCHAR(32) NOT NULL AFTER `classmap`;
ALTER TABLE `loadbalancer_vservers` CHANGE `device_id` `device_id` INT(11) NOT NULL AFTER `classmap_id`;
ALTER TABLE `loadbalancer_rservers` CHANGE `farm_id` `rserver_index` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `loadbalancer_rservers` ADD `state` TEXT CHARACTER SET utf8 COLLATE utf8_bin NULL AFTER `StateDescr`;
