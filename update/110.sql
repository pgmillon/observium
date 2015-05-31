ALTER TABLE `syslog` ADD INDEX (`priority`);
ALTER TABLE `eventlog` ADD INDEX (`type`);
ALTER TABLE `netscaler_vservers` ADD INDEX (`device_id`, `vsvr_name`);
ALTER TABLE `netscaler_services` ADD INDEX (`device_id`, `svc_name`);
