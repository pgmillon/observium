RENAME TABLE `access_points` TO `accesspoints` ;
RENAME TABLE `access_points-state` TO `accesspoints-state` ;
ALTER TABLE  `mac_accounting` ADD  `device_id` INT NOT NULL AFTER  `port_id`;
DROP TABLE `mac_accounting-state`;
CREATE TABLE `mac_accounting-state` (  `ma_id` int(11) NOT NULL,  `bytes_input` bigint(20) DEFAULT NULL,  `bytes_input_delta` bigint(20) DEFAULT NULL,  `bytes_input_rate` int(11) DEFAULT NULL,  `bytes_output` bigint(20) DEFAULT NULL,  `bytes_output_delta` bigint(20) DEFAULT NULL,  `bytes_output_rate` int(11) DEFAULT NULL,  `pkts_input` bigint(20) DEFAULT NULL,  `pkts_input_delta` bigint(20) DEFAULT NULL,  `pkts_input_rate` int(11) DEFAULT NULL,  `pkts_output` bigint(20) DEFAULT NULL,  `pkts_output_delta` bigint(20) DEFAULT NULL,  `pkts_output_rate` int(11) DEFAULT NULL,  `poll_time` int(11) DEFAULT NULL,  `poll_period` int(11) DEFAULT NULL,  PRIMARY KEY (`ma_id`)) ENGINE=MEMORY DEFAULT CHARSET=latin1;
ALTER TABLE `mac_accounting`  DROP `in_oid`,  DROP `out_oid`;
