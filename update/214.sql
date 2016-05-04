ALTER TABLE `devices` CHANGE `hostname` `hostname` VARCHAR(253) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `sysName` `sysName` VARCHAR(253) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `links` CHANGE `remote_hostname` `remote_hostname` VARCHAR(253) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
