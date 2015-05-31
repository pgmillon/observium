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

echo(" MIKROTIK-MIB ");

// While the code below may look wrong, it actually is right!
//
// When using the MIB, the sensor values are automatically converted to more precision:
//   MIKROTIK-MIB::mtxrHlTemperature.0 = INTEGER: 48.0
//
// We use the MIB when running discovery below, so $value is filled in with the correct value immediately.
//
// However, when not using the MIB (like the poller does), we get the raw value from the device:
//   .1.3.6.1.4.1.14988.1.1.3.10.0 = INTEGER: 480
//
// This means that while we don't multiply $value by 0.1, we still need to set $scale to this to fix polling.
// This also influences limit calculation!

// MIKROTIK-MIB::mtxrHlTemperature.0 = INTEGER: 22.0
$oids = snmpwalk_cache_oid($device, "mtxrHlTemperature", array(), "MIKROTIK-MIB", mib_dirs('mikrotik'));

foreach ($oids as $index => $entry)
{
  $descr   = "System ".$index;
  $oid     = "1.3.6.1.4.1.14988.1.1.3.10.".$index;
  $value = $entry['mtxrHlTemperature'];
  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'routeros', $descr, 0.1, $value);
  }
}

// MIKROTIK-MIB::mtxrHlVoltage.0 = INTEGER: 13.4
$oids = snmpwalk_cache_oid($device, "mtxrHlVoltage", array(), "MIKROTIK-MIB", mib_dirs('mikrotik'));

foreach ($oids as $index => $entry)
{
  $descr   = "System ".$index;
  $oid     = "1.3.6.1.4.1.14988.1.1.3.8.".$index;
  $value = $entry['mtxrHlVoltage'];
  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'routeros', $descr, 0.1, $value);
  }
}

// EOF
