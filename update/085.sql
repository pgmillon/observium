ALTER TABLE  `users_ckeys` CHANGE  `user_ip`  `user_uniq` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE  `users_ckeys` CHANGE  `user_uniq`  `user_uniq` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE  `users_ckeys` CHANGE  `user_encpass`  `user_encpass` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
