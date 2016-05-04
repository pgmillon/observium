ALTER TABLE `slas` ADD `sla_graph` ENUM('echo','jitter') NULL AFTER `sla_status`;
ALTER TABLE `slas-state` CHANGE `rtt_sense` `rtt_sense` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `slas-state` ADD `rtt_event` ENUM('ok','warning','alert','ignore') NOT NULL AFTER `rtt_sense`;
ALTER TABLE `slas-state` CHANGE `rtt_value` `rtt_value` DECIMAL(11,2) NOT NULL, CHANGE `rtt_minimum` `rtt_minimum` DECIMAL(11,2) NULL DEFAULT NULL, CHANGE `rtt_maximum` `rtt_maximum` DECIMAL(11,2) NULL DEFAULT NULL;
ALTER TABLE `slas-state` ADD `rtt_last_change` INT(11) NOT NULL AFTER `rtt_unixtime`;
ALTER TABLE `slas-state` ADD `rtt_stddev` DECIMAL(11,3) NULL DEFAULT NULL AFTER `rtt_maximum`;
