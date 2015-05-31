TRUNCATE TABLE `mac_accounting`;
ALTER TABLE  `mac_accounting` ADD UNIQUE  `port_vlan_mac` (  `port_id` ,  `vlan_id` ,  `mac` );

