ALTER TABLE  `devices` ADD  `is_polling` BOOLEAN NOT NULL DEFAULT  '0' AFTER  `last_discovered` , ADD  `is_discovering` BOOLEAN NOT NULL DEFAULT  '0' AFTER  `is_polling`;
