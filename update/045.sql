ALTER TABLE  `netscaler_services` ADD  `svc_fullname` VARCHAR( 128 ) NULL AFTER  `svc_name`;
ALTER TABLE  `netscaler_vservers` ADD  `vsvr_fullname` VARCHAR( 128 ) NULL AFTER  `vsvr_name`;
ALTER TABLE  `netscaler_services` ADD  `svc_label` VARCHAR( 128 ) NULL AFTER  `svc_fullname`;
ALTER TABLE  `netscaler_vservers` ADD  `vsvr_label` VARCHAR( 128 ) NULL AFTER  `vsvr_fullname`;
