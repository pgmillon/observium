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

echo(" OPENBSD-SENSORS-MIB ");

$obsd_array  = snmpwalk_cache_multi_oid($device, "sensorEntry", array(), "OPENBSD-SENSORS-MIB", mib_dirs('openbsd'));
$sensorTypes = array('temperature' => 'temperature',
                     'fan'         => 'fanspeed',
                     'voltsdc'     => 'voltage',
                     'freq'        => 'frequency',
                     'power'       => 'power',
                     'current'     => 'current');

foreach ($obsd_array as $index => $entry)
{
  if (isset($sensorTypes[$entry['sensorType']]) && is_numeric($entry['sensorValue']))
  {
    $type  = $sensorTypes[$entry['sensorType']];
    $scale = 1;
    if ($type == 'temperature' && $entry['sensorUnits'] == 'degF')
    {
      $scale = 5/9;
    }
    $value = $entry['sensorValue'];
    $descr = $entry['sensorDescr'];
    $index = $entry['sensorIndex'];
    $oid = "1.3.6.1.4.1.30155.2.1.2.1.5.$index";

    discover_sensor($valid['sensor'], $type, $device, $oid, "sensorEntry.$index", "openbsd", $descr, $scale, $value * $scale);
  }
}

// EOF
