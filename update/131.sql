ALTER TABLE `bill_data` DROP INDEX `bill_id`, ADD INDEX `bill_id` (`bill_id`, `timestamp`);
