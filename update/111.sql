ALTER TABLE  `alert_table-state` ADD  `last_recovered` INT NOT NULL AFTER  `last_changed`;
ALTER TABLE  `alert_table-state` ADD  `has_alerted` BOOLEAN NOT NULL AFTER  `last_recovered`;
