ALTER TABLE `snmp_errors` ADD UNIQUE `error_index` (`device_id`, `error_code`, `snmp_cmd`, `mib`(50), `oid`(50));
