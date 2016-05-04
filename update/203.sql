ALTER TABLE `slas` ADD `sla_mib` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'cisco-rttmon-mib' AFTER `device_id`;
