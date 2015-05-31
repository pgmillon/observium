ALTER TABLE  `netscaler_services_vservers` CHANGE  `vsvr_name`  `vsvr_name` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , CHANGE  `svc_name`  `svc_name` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE  `netscaler_services_vservers` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `netscaler_services` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `netscaler_vservers` CHANGE  `vsvr_ipv6`  `vsvr_ipv6` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,CHANGE  `vsvr_entitytype`  `vsvr_entitytype` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE  `netscaler_vservers` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
