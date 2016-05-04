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

$mib = 'SW-MIB';
echo(" $mib ");

$sensor_array = snmpwalk_cache_multi_oid($device, 'swSensorTable', array(), $mib, mib_dirs('brocade'));

$sensor_type_map = array(
  'temperature'  => 'temperature',
  'fan'          => 'fanspeed',
  'power-supply' => 'state');

foreach ($sensor_array as $index => $entry)
{
  if ($sensor_type_map[$entry['swSensorType']] && is_numeric($entry['swSensorValue']))
  {
    $descr   = rewrite_entity_name($entry['swSensorInfo']);
    $oid     = '.1.3.6.1.4.1.1588.2.1.1.1.1.22.1.4.'.$index;
    $type    = $sensor_type_map[$entry['swSensorType']];
    $value   = $entry['swSensorValue'];

    discover_sensor($valid['sensor'], $type, $device, $oid, $index, 'sw-mib', $descr, 1, $value);
  }
}

unset($sensor_type_map, $sensor_array, $index, $type, $value, $descr);

// EOF
