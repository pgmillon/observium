ALTER TABLE `ports` ADD INDEX `port_cache` (`port_id`, `device_id`, `ignore`, `deleted`, `ifOperStatus`, `ifAdminStatus`);
ALTER TABLE `ports` ADD INDEX `device_cache` (`device_id`, `disabled`, `deleted`);
ALTER TABLE `alert_table` ADD INDEX `alert_cache` (`alert_table_id`, `alert_test_id`, `device_id`, `entity_type`, `entity_id`, `alert_status`);
ALTER TABLE `sensors` ADD INDEX `sensor_cache` (`sensor_id`, `device_id`, `sensor_class`, `sensor_type`, `sensor_ignore`, `sensor_disable`);
ALTER TABLE `status` ADD INDEX `status_cache` (`status_id`, `device_id`, `entPhysicalClass`, `status_ignore`, `status_disable`);
ALTER TABLE `bgpPeers` CHANGE `bgpPeerState` `bgpPeerState` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `bgpPeerAdminStatus` `bgpPeerAdminStatus` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `bgpPeers` ADD INDEX `bgp_cache` (`bgpPeer_id`, `device_id`, `bgpPeerState`, `bgpPeerAdminStatus`, `bgpPeerRemoteAs`);
ALTER TABLE `ospf_instances` ADD INDEX `ospf_cache` (`device_id`, `ospf_instance_id`, `ospfAdminStat`);
ALTER TABLE `vrfs` ADD INDEX `vrf_cache` (`vrf_id`, `device_id`, `mplsVpnVrfRouteDistinguisher`);
