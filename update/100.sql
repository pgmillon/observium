ALTER TABLE  `devices` CHANGE  `transport`  `transport` ENUM(  'udp',  'tcp',  'udp6',  'tcp6' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'udp';

