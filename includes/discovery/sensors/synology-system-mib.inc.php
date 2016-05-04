<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// SYNOLOGY-SYSTEM-MIB::systemStatus.0 = INTEGER: 1
// SYNOLOGY-SYSTEM-MIB::temperature.0 = INTEGER: 31
// SYNOLOGY-SYSTEM-MIB::powerStatus.0 = INTEGER: 1
// SYNOLOGY-SYSTEM-MIB::systemFanStatus.0 = INTEGER: 1
// SYNOLOGY-SYSTEM-MIB::cpuFanStatus.0 = INTEGER: 1

echo(" SYNOLOGY-SYSTEM-MIB ");

$value = snmp_get($device, "temperature.0", "-Oqv", "SYNOLOGY-SYSTEM-MIB", mib_dirs('synology'));

if (is_numeric($value) && $value > 0)
{
  $descr = "System Temperature";
  $oid   = ".1.3.6.1.4.1.6574.1.2.0";

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "temperature.0", 'synology-system-mib', $descr, 1, $value);
}

$value = snmp_get($device, "systemStatus.0", "-Oqv", "SYNOLOGY-SYSTEM-MIB", mib_dirs('synology'));

if (is_numeric($value) && $value > 0)
{
  $descr = "System Status";
  $oid   = ".1.3.6.1.4.1.6574.1.1.0";

  discover_sensor($valid['sensor'], 'state', $device, $oid, "systemStatus.0", 'synology-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'chassis'));
}

$value = snmp_get($device, "powerStatus.0", "-Oqv", "SYNOLOGY-SYSTEM-MIB", mib_dirs('synology'));

if (is_numeric($value) && $value > 0)
{
  $descr = "Power Status";
  $oid   = ".1.3.6.1.4.1.6574.1.3.0";

  discover_sensor($valid['sensor'], 'state', $device, $oid, "powerStatus.0", 'synology-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));
}

$value = snmp_get($device, "systemFanStatus.0", "-Oqv", "SYNOLOGY-SYSTEM-MIB", mib_dirs('synology'));

if (is_numeric($value) && $value > 0)
{
  $descr = "System Fan Status";
  $oid   = ".1.3.6.1.4.1.6574.1.4.1.0";

  discover_sensor($valid['sensor'], 'state', $device, $oid, "systemFanStatus.0", 'synology-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'fan'));
}

$value = snmp_get($device, "cpuFanStatus.0", "-Oqv", "SYNOLOGY-SYSTEM-MIB", mib_dirs('synology'));

if (is_numeric($value) && $value > 0)
{
  $descr = "CPU Fan Status";
  $oid   = ".1.3.6.1.4.1.6574.1.4.2.0";

  discover_sensor($valid['sensor'], 'state', $device, $oid, "cpuFanStatus.0", 'synology-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'fan'));
}

/*
Currently not monitored: upgradeAvailable OBJECT-TYPE
    "This oid is for checking whether there is a latest DSM can be upgraded.
         Available(1): There is version ready for download.
         Unavailable(2): The DSM is latest version.
         Connecting(3): Checking for the latest DSM.
         Disconnected(4): Failed to connect to server.
         Others(5): If DSM is upgrading or downloading, the status will show others."
*/

// EOF
