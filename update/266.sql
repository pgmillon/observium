# Recreate error index with longer oid size
ALTER TABLE `snmp_errors` DROP INDEX `error_index`;
ALTER TABLE `snmp_errors` ADD UNIQUE `error_index` (`device_id`, `error_code`, `snmp_cmd`, `mib`(50), `oid`(100));
