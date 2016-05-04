ALTER TABLE `links` CHANGE `id` `neighbour_id` INT(11) NOT NULL AUTO_INCREMENT, CHANGE `local_port_id` `port_id` INT(11) NULL DEFAULT NULL, CHANGE `active` `active` BOOLEAN NOT NULL DEFAULT TRUE;
RENAME TABLE `links` TO `neighbours`;
