ALTER TABLE  `netscaler_vservers` ADD  `vsvr_ignore` INT NOT NULL DEFAULT  '0',ADD  `vsvr_ignore_until` INT NOT NULL DEFAULT  '0';
ALTER TABLE  `netscaler_services` ADD  `svc_ignore` INT NOT NULL DEFAULT  '0',ADD   `svc_ignore_until` INT NOT NULL DEFAULT  '0';
