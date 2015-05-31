ALTER TABLE  `alert_table-state` DROP  `ignore_until_ok` ;
ALTER TABLE  `alert_table` ADD  `ignore_until_ok` BOOLEAN NULL DEFAULT NULL ;
ALTER TABLE  `alert_table` CHANGE  `ignore_until`  `ignore_until` DATETIME NULL DEFAULT NULL ;
ALTER TABLE  `devices` CHANGE  `ignore_until`  `ignore_until` DATETIME NULL DEFAULT NULL ;
ALTER TABLE  `alert_tests` CHANGE  `ignore_until`  `ignore_until` DATETIME NULL DEFAULT NULL ;
