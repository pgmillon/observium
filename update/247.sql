ALTER TABLE `vlans_fdb` DROP INDEX `dev_vlan_mac`, ADD UNIQUE `dev_vlan_mac_port` (`device_id`, `vlan_id`, `mac_address`, `port_id`);
ALTER TABLE `lb_virtuals` ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
