ALTER TABLE  `alert_table-state` ADD  `ignore_until_ok` BOOLEAN NOT NULL DEFAULT FALSE AFTER  `last_alerted`;
