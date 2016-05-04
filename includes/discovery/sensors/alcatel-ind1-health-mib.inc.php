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

// NOTE. Because Alcatel changed their MIBs content (same oid names have different indexes), here used only numeric OIDs.

echo(" ALCATEL-IND1-HEALTH-MIB ");

// Old AOS

$descr   = "Chassis Temperature";
$oid     = ".1.3.6.1.4.1.6486.800.1.2.1.16.1.1.1.18.0"; // healthDeviceTemperatureChas1MinAvg
$value   = snmp_get($device, $oid, '-Oqv', 'ALCATEL-IND1-HEALTH-MIB', mib_dirs('aos'));

if (is_numeric($value) && $value > 0)
{
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, 1, 'alcatel-device', $descr, 1, $value);
}

$descr   = "CPU Temperature";
$oid     = ".1.3.6.1.4.1.6486.800.1.2.1.16.1.1.1.22.0"; // healthDeviceTemperatureCmmCpu1MinAvg
$value   = snmp_get($device, $oid, '-Oqv', 'ALCATEL-IND1-HEALTH-MIB', mib_dirs('aos'));

if (is_numeric($value) && $value > 0)
{
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, 0, 'alcatel-device', $descr, 1, $value);
}

// EOF
