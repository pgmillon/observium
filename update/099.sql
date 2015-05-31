ALTER TABLE  `devices` CHANGE  `snmpver`  `snmpver` ENUM(  'v1',  'v2c',  'v3' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'v2c';

