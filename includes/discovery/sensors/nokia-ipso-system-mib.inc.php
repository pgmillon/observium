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

$mib = 'NOKIA-IPSO-SYSTEM-MIB';
echo(" $mib ");

// NOKIA-IPSO-SYSTEM-MIB::ipsoChassisTemperature.0 = INTEGER: normal(1)
$value = snmp_get($device, 'ipsoChassisTemperature.0', '-Oqv', $mib, mib_dirs('checkpoint'));
if ($value !== '')
{
  $oid   = '.1.3.6.1.4.1.94.1.21.1.1.5.0';
  $descr = 'Chassis Temperature';

  discover_sensor($valid['sensor'], 'state', $device, $oid, "ipsoChassisTemperature.0", "ipso-temperature-state", $descr, NULL, $value, array('entPhysicalClass' => 'temperature'));
}

// NOKIA-IPSO-SYSTEM-MIB::ipsoFanOperStatus.1 = INTEGER: running(1)
$data = snmpwalk_cache_multi_oid($device, 'ipsoFanTable', array(), $mib, mib_dirs('checkpoint'));
$data_multi = count($data) > 1; // Set TRUE if more than one index
foreach ($data as $index => $entry)
{
  $oid   = '.1.3.6.1.4.1.94.1.21.1.2.1.1.2.' . $index;
  $descr = 'Chassis Fan';
  if ($data_multi) { $descr .= " $index"; }
  $value = $entry['ipsoFanOperStatus'];

  discover_sensor($valid['sensor'], 'state', $device, $oid, "ipsoFanOperStatus.$index", "ipso-sensor-state", $descr, NULL, $value, array('entPhysicalClass' => 'fan'));
}

// NOKIA-IPSO-SYSTEM-MIB::ipsoPowerSupplyOverTemperature.1 = INTEGER: normal(1)
// NOKIA-IPSO-SYSTEM-MIB::ipsoPowerSupplyOperStatus.1 = INTEGER: running(1)
$data = snmpwalk_cache_multi_oid($device, 'ipsoPowerSupplyTable', array(), $mib, mib_dirs('checkpoint'));
$data_multi = count($data) > 1; // Set TRUE if more than one index
foreach ($data as $index => $entry)
{
  $oid   = '.1.3.6.1.4.1.94.1.21.1.3.1.1.2.' . $index;
  $descr = 'Power Supply Temperature';
  if ($data_multi) { $descr .= " $index"; }
  $value = $entry['ipsoPowerSupplyOverTemperature'];

  discover_sensor($valid['sensor'], 'state', $device, $oid, "ipsoPowerSupplyOverTemperature.$index", "ipso-temperature-state", $descr, NULL, $value, array('entPhysicalClass' => 'temperature'));

  $oid   = '.1.3.6.1.4.1.94.1.21.1.3.1.1.3.' . $index;
  $descr = 'Power Supply';
  if ($data_multi) { $descr .= " $index"; }
  $value = $entry['ipsoPowerSupplyOperStatus'];

  discover_sensor($valid['sensor'], 'state', $device, $oid, "ipsoPowerSupplyOperStatus.$index", "ipso-sensor-state", $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

// EOF
