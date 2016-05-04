ALTER TABLE `neighbours` ADD INDEX `count` (`port_id`, `active`);
ALTER TABLE `ports_cbqos` ADD INDEX `port_id` (`port_id`);
ALTER TABLE `p2p_radios` ADD INDEX `count` (`deleted`, `device_id`);
ALTER TABLE `slas` ADD INDEX `count` (`deleted`, `device_id`);
