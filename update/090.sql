ALTER TABLE  `devices` ADD  `ignore_until` INT NULL DEFAULT NULL AFTER  `ignore`;
ALTER TABLE  `alert_tests` ADD  `ignore_until` INT NULL DEFAULT NULL AFTER  `enable`;
ALTER TABLE  `alert_table` ADD  `ignore_until` INT NULL DEFAULT NULL AFTER  `delay`;
