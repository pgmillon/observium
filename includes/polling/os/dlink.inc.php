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

if (preg_match('/^(?:dlink |d-link )?(?<hardware>[a-z]+\-\d+[\w\/-]+) +(?<features>.+)/i', $poll_device['sysDescr'], $matches))
{
  //D-Link DES-1228/ME Metro Ethernet Switch
  //D-Link DES-3026 Fast Ethernet Switch
  //D-Link DES-3028P Fast Ethernet Switch
  //D-Link DES-3200-28 Fast Ethernet Switch
  //DES-3200-10/C1 Fast Ethernet Switch
  //DES-3200-28P Fast Ethernet Switch
  //DES-3226S Fast-Ethernet Switch
  //DES-3526 Fast-Ethernet Switch
  //DGS-1224T                                4.21.02
  //DES-1210-28/ME          6.07.B004
  //DGS-3120-24SC Gigabit Ethernet Switch
  //DGS-3450 Gigabit Ethernet Switch
  //DGS-3627G Gigabit Ethernet Switch
  $hardware = $matches['hardware'];
  if (preg_match('/^(\d+\.)+[\w\-]+$/', $matches['features']))
  {
    //4.21.02
    //6.07.B004
    $version = $matches['features'];
  } else {
    //Fast-Ethernet Switch
    //Fast Ethernet Switch
    //Gigabit Ethernet Switch
    $features = str_replace('-', ' ', $matches['features']);
  }
} else {
  // SINGLE-IP-MIB::swSingleIPPlatform.0 = STRING: "DES-3028P L2 Switch"
  list($hardware) = explode(' ', snmp_get($device, "swSingleIPPlatform.0", "-Ovq", "SINGLE-IP-MIB", mib_dirs('d-link')));
}

if (!$version)
{
  // DLINK-EQUIPMENT-MIB::swUnitMgmtVersion.1 = STRING: "6.00.B21"
  //$version = snmp_get($device, "swUnitMgmtVersion.1", "-Ovq", "DLINK-EQUIPMENT-MIB");
  // RMON2-MIB::probeSoftwareRev.0 = STRING: "Build 6.00.B21"
  $version = snmp_get($device, "probeSoftwareRev.0", "-Ovq", "RMON2-MIB");
  $version = str_replace("Build ", "", $version);
}
// HW revision is not required, but anyone can come in handy in the future.
// I for example have more than five revisions for one platform (DES-3550)
// RMON2-MIB::probeHardwareRev.0 = STRING: "0A3G"
//$revision = trim(snmp_get($device, "probeHardwareRev.0", "-Ovq", "RMON2-MIB"), '"');
//$hardware = ($revision === '') ? $hardware : $hardware . " " . $revision ;

// AGENT-GENERAL-MIB::agentSerialNumber.0 = STRING: "PL5T2A1000668"
$serial = snmp_get($device, "agentSerialNumber.0", "-Ovq", "AGENT-GENERAL-MIB", mib_dirs('d-link'));

// EOF
