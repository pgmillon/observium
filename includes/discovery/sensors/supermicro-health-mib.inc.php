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

// Supermicro Sensors
$cache['supermicro'] = snmpwalk_cache_multi_oid($device, "smHealthMonitorTable", array(), "SUPERMICRO-HEALTH-MIB");

echo(" SUPERMICRO-HEALTH-MIB ");

foreach ($cache['supermicro'] as $index => $sensor_data)
{
  $descr   = $sensor_data['smHealthMonitorName'];
  $oid     = "1.3.6.1.4.1.10876.2.1.1.1.1.4.$index";
  $value   = $sensor_data['smHealthMonitorReading'];
  $monitor = 1;
  $type    = $sensor_data['smHealthMonitorType'];

  $scale   = 1;
  $mibtype = 'supermicro';

  switch ($type)
  {
    case 0: # Fanspeed
      $descr      = str_replace(' Fan Speed','',$descr);
      $descr      = str_replace(' Speed','',$descr);
      $sensortype = 'fanspeed';
      break;
    case 1: # Voltage
      $scale      = 0.001;
      $sensortype = 'voltage';
      $descr      = trim(str_ireplace("Voltage", "", $descr));
      break;
    case 2: # Temperature
      $descr      = trim(str_ireplace("temperature", "", $descr));
      $sensortype = 'temperature';
      break;
    case 3: # State
      $sensortype = 'state';
      $mibtype       = 'supermicro-state';
      break;
    default:
      $monitor    = 0; # We don't know of any other sensor type.
      break;
  }

  $limits = array('limit_high' => (is_numeric($sensor_data['smHealthMonitorHighLimit']) ? $sensor_data['smHealthMonitorHighLimit'] * $scale : NULL),
                  'limit_low'  => (is_numeric($sensor_data['smHealthMonitorLowLimit'])  ? $sensor_data['smHealthMonitorLowLimit']  * $scale : NULL));

  if ($monitor & $descr != '')
  {
    discover_sensor($valid['sensor'], $sensortype, $device, $oid, $index, $mibtype, $descr, $scale, $value, $limits);
  }
}

unset($cache['supermicro']);

// EOF
