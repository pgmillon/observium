ALTER TABLE `bgpPeers` CHANGE `bgpPeerIdentifier` `bgpPeerIdentifier` VARCHAR(39) NOT NULL, CHANGE `bgpLocalAddr` `bgpPeerLocalAddr` VARCHAR(39) NOT NULL, CHANGE `bgpPeerRemoteAddr` `bgpPeerRemoteAddr` VARCHAR(39) NOT NULL;
UPDATE `bgpPeers` SET `bgpPeerRemoteAddr` = `bgpPeerIdentifier`;
TRUNCATE TABLE `bgpPeers_cbgp`;
ALTER TABLE `bgpPeers_cbgp` CHANGE `bgpPeerIdentifier` `bgpPeerRemoteAddr` VARCHAR(39) NOT NULL;
ALTER TABLE `bgpPeers_cbgp` ADD `bgpPeerIndex` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER `bgpPeerRemoteAddr`;
ALTER TABLE `bgpPeers-state` ADD `bgpPeer_polled` INT(11) NULL DEFAULT NULL;
ALTER TABLE `bgpPeers_cbgp-state` CHANGE `AcceptedPrefixes` `AcceptedPrefixes` INT(11) NULL DEFAULT NULL, CHANGE `DeniedPrefixes` `DeniedPrefixes` INT(11) NULL DEFAULT NULL, CHANGE `PrefixAdminLimit` `PrefixAdminLimit` INT(11) NULL DEFAULT NULL, CHANGE `PrefixThreshold` `PrefixThreshold` INT(11) NULL DEFAULT NULL, CHANGE `PrefixClearThreshold` `PrefixClearThreshold` INT(11) NULL DEFAULT NULL, CHANGE `AdvertisedPrefixes` `AdvertisedPrefixes` INT(11) NULL DEFAULT NULL, CHANGE `SuppressedPrefixes` `SuppressedPrefixes` INT(11) NULL DEFAULT NULL, CHANGE `WithdrawnPrefixes` `WithdrawnPrefixes` INT(11) NULL DEFAULT NULL;
