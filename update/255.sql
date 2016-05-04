ALTER TABLE `slas-state` ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE `slas` ADD `sla_limit_high` INT(11) NOT NULL DEFAULT '5000' AFTER `sla_status`, ADD `sla_limit_high_warn` INT(11) NOT NULL DEFAULT '625' AFTER `sla_limit_high`;
