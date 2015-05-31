ALTER TABLE  `devices` CHANGE  `serial`  `serial` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE  `devices` ADD  `ssh_port` INT NOT NULL DEFAULT  '22' AFTER  `retries`;
