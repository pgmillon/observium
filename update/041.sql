ALTER TABLE  `netscaler_vservers` ADD  `vsvr_ipv6` VARCHAR( 64 ) NULL DEFAULT NULL AFTER  `vsvr_ip`;
ALTER TABLE  `netscaler_vservers` ADD  `vsvr_entitytype` VARCHAR( 32 ) NULL DEFAULT NULL AFTER  `vsvr_type`;
