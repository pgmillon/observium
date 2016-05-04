#IGNORE_ERROR
ALTER TABLE  `alert_table-state` ENGINE = INNODB;
#ignore error with duplicate table 'last_ok'
ALTER TABLE  `alert_table-state` ADD  `last_ok` INT NULL AFTER  `last_recovered` ;
ALTER TABLE  `alert_table-state` CHANGE  `last_checked`  `last_checked` INT( 10 ) NULL DEFAULT NULL , CHANGE  `last_changed`  `last_changed` INT( 10 ) NULL DEFAULT NULL , CHANGE  `last_recovered`  `last_recovered` INT( 10 ) NULL DEFAULT NULL , CHANGE  `last_ok`  `last_ok` INT( 10 ) NULL DEFAULT NULL , CHANGE  `last_failed`  `last_failed` INT( 10 ) NULL DEFAULT NULL ;
