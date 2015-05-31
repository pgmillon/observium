ALTER TABLE `storage` ADD `storage_hc` BOOLEAN NOT NULL DEFAULT FALSE AFTER `storage_descr`;
ALTER TABLE `storage` DROP `storage_perc_warn`;
