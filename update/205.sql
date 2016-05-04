ALTER TABLE `slas` DROP INDEX `unique_key`, ADD UNIQUE `unique_key` (`device_id`, `sla_mib`(50), `sla_index`(50), `sla_owner`(50));
