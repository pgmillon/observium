ALTER TABLE  `sensors` ADD  `sensor_disable` BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE  `ports` ADD  `port_label` VARCHAR( 64 ) NOT NULL DEFAULT  '' AFTER  `device_id`;
ALTER TABLE  `netscaler_services` CHANGE  `svc_ignore`  `svc_ignore` BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE  `netscaler_vservers` CHANGE  `vsvr_ignore`  `vsvr_ignore` BOOLEAN NOT NULL DEFAULT FALSE;
