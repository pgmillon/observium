<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// .1.3.6.1.4.1.17163.1.1.2.7.0 STEELHEAD-MIB::systemHealth.0 = INTEGER: critical(50000)
// .1.3.6.1.4.1.17163.1.1.2.8.0 STEELHEAD-MIB::optServiceStatus.0 = INTEGER: stopped(8)
// .1.3.6.1.4.1.17163.1.1.2.9.0 STEELHEAD-MIB::systemTemperature.0 = Gauge32: 0

echo(" STEELHEAD-MIB ");

$oid   = "1.3.6.1.4.1.17163.1.1.2.7.0";
$value = state_string_to_numeric('steelhead-system-state', snmp_get($device, "systemHealth.0", "-Oqv", "STEELHEAD-MIB", mib_dirs('riverbed')));

if (is_numeric($value) && $value > 0)
{
  $descr = "System Health";

  discover_sensor($valid['sensor'], 'state', $device, $oid, "systemHealth.0", 'steelhead-system-state', $descr, NULL, $value, array('entPhysicalClass' => 'chassis'));
}

$oid = "1.3.6.1.4.1.17163.1.1.2.8.0";
$value = state_string_to_numeric('steelhead-service-state', snmp_get($device, "optServiceStatus.0", "-Oqv", "STEELHEAD-MIB", mib_dirs('riverbed')));

if (is_numeric($value) && $value > 0)
{
  $descr = "Service Status";

  discover_sensor($valid['sensor'], 'state', $device, $oid, "optServiceStatus.0", 'steelhead-service-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

$oid = "1.3.6.1.4.1.17163.1.1.2.9.0";
$value = snmp_get($device, "systemTemperature.0", "-Oqv", "STEELHEAD-MIB", mib_dirs('riverbed'));

if (is_numeric($value) && $value > 0)
{
  $descr = "System Temperature";

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "systemTemperature.0", 'steelhead-mib', $descr, 1, $value);
}

// EOF

