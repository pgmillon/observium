RENAME TABLE  `port_in_measurements` TO  `bill_port_in_data` ;
RENAME TABLE  `port_out_measurements` TO  `bill_port_out_data` ;
ALTER TABLE  `bill_ports` ADD INDEX (  `bill_id` );
ALTER TABLE  `bill_ports` ADD  `last_polled` INT NOT NULL ,ADD  `last_period` INT NOT NULL ,ADD  `counter_in` INT NOT NULL ,ADD  `delta_in` INT NOT NULL ,ADD  `counter_out` INT NOT NULL ,ADD  `delta_out` INT NOT NULL;
ALTER TABLE  `bill_ports` ADD UNIQUE (`bill_id` ,`port_id`);
ALTER TABLE  `bill_ports` CHANGE  `counter_in`  `counter_in` BIGINT( 20 ) NULL DEFAULT NULL ,CHANGE  `delta_in`  `delta_in` BIGINT( 20 ) NULL DEFAULT NULL ,CHANGE  `counter_out`  `counter_out` BIGINT( 20 ) NULL DEFAULT NULL ,CHANGE  `delta_out`  `delta_out` BIGINT( 20 ) NULL DEFAULT NULL;
ALTER TABLE  `bill_ports` CHANGE  `last_period`  `bill_port_period` INT( 11 ) NOT NULL ,CHANGE  `counter_in`  `bill_port_counter_in` BIGINT( 20 ) NULL DEFAULT NULL ,CHANGE  `delta_in`  `bill_port_delta_in` BIGINT( 20 ) NULL DEFAULT NULL ,CHANGE  `counter_out`  `bill_port_counter_out` BIGINT( 20 ) NULL DEFAULT NULL ,CHANGE  `delta_out`  `bill_port_delta_out` BIGINT( 20 ) NULL DEFAULT NULL;
ALTER TABLE  `bills` ADD  `bill_polled` INT NOT NULL AFTER  `bill_quota`;
ALTER TABLE  `bill_ports` CHANGE  `last_polled`  `bill_port_polled` INT( 11 ) NOT NULL;

