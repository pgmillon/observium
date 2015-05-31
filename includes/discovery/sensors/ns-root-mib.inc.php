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

echo(" NS-ROOT-MIB ");

if (!is_array($ns_sensor_array) && strpos($device['hardware'], 'NetScaler Virtual Appliance') === FALSE)
{
  $ns_sensor_array = array();
  echo(" sysHealthCounterValue ");
  $ns_sensor_array = snmpwalk_cache_multi_oid($device, "sysHealthCounterValue", $ns_sensor_array, "NS-ROOT-MIB", mib_dirs('citrix'));
}

foreach ($ns_sensor_array as $descr => $data)
{
  $value = $data['sysHealthCounterValue'];

  $oid = ".1.3.6.1.4.1.5951.4.1.1.41.7.1.2." . string_to_oid($descr);

  if     (strpos($descr, "Temp") !== FALSE) { $scale = 1;     $type = "temperature"; }
  elseif (strpos($descr, "Fan")  !== FALSE) { $scale = 1;     $type = "fanspeed"; }
  elseif (strpos($descr, "Volt") !== FALSE) { $scale = 0.001; $type = "voltage"; }
  elseif (strpos($descr, "Vtt")  !== FALSE) { $scale = 0.001; $type = "voltage"; }
  elseif (preg_match('/PowerSupply\dFailureStatus/', $descr)) { $physical = 'power'; $type = "state"; }
  else { continue; } // Skip all other

  if ($type == 'state')
  {
    discover_sensor($valid['sensor'], $type, $device, $oid, $descr, 'netscaler-state',  $descr, NULL, $value, array('entPhysicalClass' => $physical));
  }
  else if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], $type, $device, $oid, $descr, 'netscaler-health', $descr, $scale, $value * $scale);
  }
}

unset($ns_sensor_array);

$sysHighAvailabilityMode = snmp_get($device, 'sysHighAvailabilityMode.0', '-Ovq', 'NS-ROOT-MIB', mib_dirs('citrix'));

if ($sysHighAvailabilityMode)
{
  $descr = 'HA Mode';
  $oid   = '.1.3.6.1.4.1.5951.4.1.1.6.0';
  $value = state_string_to_numeric('netscaler-ha-state', $sysHighAvailabilityMode);
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'sysHighAvailabilityMode.0', 'netscaler-ha-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

unset($sysHighAvailabilityMode);

// EOF
