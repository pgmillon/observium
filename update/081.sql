ALTER TABLE  `ports` CHANGE  `ifIndex`  `ifIndex` INT( 11 ) NOT NULL;
ALTER TABLE  `devices` ADD INDEX (  `ignore` );

