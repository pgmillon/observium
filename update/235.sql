ALTER TABLE `processors` CHANGE `processor_index` `processor_index` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `mempools` CHANGE `mempool_index` `mempool_index` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `storage` CHANGE `storage_index` `storage_index` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
