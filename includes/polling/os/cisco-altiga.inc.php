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

$data = snmp_get_multi($device, 'alHardwareChassis.0 alHardwareSerialNumber.0', '-OQUs', 'ALTIGA-HARDWARE-STATS-MIB', mib_dirs('cisco'));
if (isset($data[0]))
{
  $hardware = strtoupper($data[0]['alHardwareChassis']);
  $serial   = $data[0]['alHardwareSerialNumber'];
} else {
  $serial   = snmp_get($device, 'entPhysicalSerialNum.1', '-OQv', 'ENTITY-MIB');
}

if (preg_match('/VPN 3000 Concentrator (Series )?Version (?<version>[\w\.]+)/', $poll_device['sysDescr'], $matches))
{
  //Cisco Systems, Inc./VPN 3000 Concentrator Series Version 2.5.Rel Jun 21 2000 18:57:52
  //Cisco Systems, Inc./VPN 3000 Concentrator Version 4.0.1.Rel built by vmurphy on May 06 2003 13:13:03
  //Cisco Systems, Inc./VPN 3000 Concentrator Version 4.1.7.F built by vmurphy on May 17 2005 02:38:46
  //Cisco Systems, Inc./VPN 3000 Concentrator Version 4.7.Rel built by vmurphy on Mar 10 2005 14:58:16
  $version  = $matches['version'];
} else {
  $version  = snmp_get($device, 'alVersionString.0', '-OQv', 'ALTIGA-VERSION-STATS-MIB', mib_dirs('cisco'));
}
$version = str_replace('.Rel', '', $version);

// EOF
