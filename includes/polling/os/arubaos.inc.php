<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// ArubaOS (MODEL: Aruba3600), Version 6.1.2.2 (29541)
// ArubaOS Version 6.1.2.3-2.1.0.0 // - AP135

$badchars = array("(", ")", ",");
list(,,$hardware,,$version,) = str_replace($badchars, "", explode (" ", $poll_device['sysDescr']));

// Build SNMP Cache Array

// Stuff about the controller
$switch_info_oids = array('wlsxSwitchRole','wlsxSwitchMasterIp');
echo("Caching Oids: ");
foreach ($switch_info_oids as $oid) { echo("$oid "); $aruba_info = snmpwalk_cache_oid($device, $oid, $aruba_info, "WLSX-SWITCH-MIB", mib_dirs(array("aruba"))); }

echo(PHP_EOL);

if ($aruba_info[0]['wlsxSwitchRole'] == 'master')
{
  $features = "Master Controller";
} else {
  $features = "Local Controller for ".$aruba_info[0]['wlsxSwitchMasterIp'];
}

// EOF
