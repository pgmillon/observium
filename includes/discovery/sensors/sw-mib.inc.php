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

    // Because I am nice, rename old RRDs - CLEANME remove in future version
    $old_rrd  = $config['rrd_dir'] . '/'.$device['hostname'].'/sensor-'.$type.'--'.$index.'.rrd';
    $new_rrd  = $config['rrd_dir'] . '/'.$device['hostname'].'/sensor-'.$type.'-sw-mib-'.$index.'.rrd';
    if (is_file($old_rrd)) { rename($old_rrd, $new_rrd); print_warning('Moved RRD'); }

    discover_sensor($valid['sensor'], $type, $device, $oid, $index, 'sw-mib', $descr, 1, $value);
  }
}

unset($sensor_type_map, $sensor_array, $index, $type, $value, $descr);

// EOF
