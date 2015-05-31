<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// DLINK-EQUIPMENT-MIB::swUnitMgmtVersion.1 = STRING: "6.00.B21"
//$version = trim(snmp_get($device, "swUnitMgmtVersion.1", "-Ovq", "DLINK-EQUIPMENT-MIB"), '"');
// RMON2-MIB::probeSoftwareRev.0 = STRING: "Build 6.00.B21"
$version = trim(snmp_get($device, "probeSoftwareRev.0", "-Ovq", "RMON2-MIB"), '"');
$version = str_replace("Build ", "", $version);

// sysDescr.0 = STRING: "D-Link DES-3028P Fast Ethernet Switch"
// sysDescr.0 = STRING: "DES-3526 Fast-Ethernet Switch"
// sysDescr.0 = STRING: "DGS-3450 Gigabit Ethernet Switch"
if (preg_match('/^(?:dlink\ |d-link\ |)([\w-]+)\ (.+)/i', $poll_device['sysDescr'], $matches))
{
  $hardware = $matches[1];
  $features = str_replace('-', ' ', $matches[2]);
} else {
  // SINGLE-IP-MIB::swSingleIPPlatform.0 = STRING: "DES-3028P L2 Switch"
  list($hardware) = explode(' ', trim(snmp_get($device, "swSingleIPPlatform.0", "-Ovq", "SINGLE-IP-MIB", mib_dirs('d-link'))), '"');
}

// HW revision is not required, but anyone can come in handy in the future.
// I for example have more than five revisions for one platform (DES-3550)
// RMON2-MIB::probeHardwareRev.0 = STRING: "0A3G"
//$revision = trim(snmp_get($device, "probeHardwareRev.0", "-Ovq", "RMON2-MIB"), '"');
//$hardware = ($revision === '') ? $hardware : $hardware . " " . $revision ;

// DLINK-AGENT-MIB::agentSerialNumber.0 = STRING: "PL5T2A1000668"
$serial = trim(snmp_get($device, "agentSerialNumber.0", "-Ovq", "DLINK-AGENT-MIB", mib_dirs('d-link')), '"');

// EOF
