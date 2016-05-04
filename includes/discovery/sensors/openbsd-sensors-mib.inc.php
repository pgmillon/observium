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

echo(" OPENBSD-SENSORS-MIB ");

$obsd_array  = snmpwalk_cache_multi_oid($device, "sensorEntry", array(), "OPENBSD-SENSORS-MIB");
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
    $options = array('sensor_unit' => 'C');
    if ($type == 'temperature' && $entry['sensorUnits'] == 'degF')
    {
      $options['sensor_unit'] = 'F';
    }
    $value = $entry['sensorValue'];
    $descr = $entry['sensorDescr'];
    $index = $entry['sensorIndex'];
    $oid = "1.3.6.1.4.1.30155.2.1.2.1.5.$index";

    discover_sensor($valid['sensor'], $type, $device, $oid, "sensorEntry.$index", "openbsd", $descr, $scale, $value, $options);
  }
}

// EOF
