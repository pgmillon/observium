ALTER TABLE `sensors` CHANGE `poller_type` `poller_type` ENUM('snmp', 'agent', 'ipmi') NOT NULL DEFAULT 'snmp';
ALTER TABLE `sensors` ADD `sensor_custom_limit` TINYINT(1) NOT NULL DEFAULT '0' AFTER `sensor_limit_low_warn`;
