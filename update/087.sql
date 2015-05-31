ALTER TABLE  `users_ckeys` CHANGE  `user_id`  `username` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
TRUNCATE users_ckeys;
