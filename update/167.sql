ALTER TABLE `bgpPeers` CHANGE `bgpPeerRemoteAs` `bgpPeerRemoteAs` INT(11) UNSIGNED NOT NULL;
ALTER TABLE `devices` CHANGE `bgpLocalAs` `bgpLocalAs` INT(11) UNSIGNED NULL DEFAULT NULL;
