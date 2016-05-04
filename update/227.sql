ALTER TABLE  `entity_permissions` ADD  `perm_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
ALTER TABLE  `entity_permissions` ADD INDEX `user_id` (`user_id`);
