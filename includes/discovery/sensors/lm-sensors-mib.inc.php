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

echo(" LM-SENSORS-MIB ");

$lm_array['temp'] = snmpwalk_cache_multi_oid($device, "lmTempSensorsEntry", array(), "LM-SENSORS-MIB", mib_dirs());
$lm_array['fan']  = snmpwalk_cache_multi_oid($device, "lmFanSensorsEntry",  array(), "LM-SENSORS-MIB", mib_dirs());
$lm_array['volt'] = snmpwalk_cache_multi_oid($device, "lmVoltSensorsEntry", array(), "LM-SENSORS-MIB", mib_dirs());
//$lm_array['misc'] = snmpwalk_cache_multi_oid($device, "lmMiscSensorsEntry", array(), "LM-SENSORS-MIB", mib_dirs());

$scale = 0.001;
foreach ($lm_array['temp'] as $index => $entry)
{
  $oid   = ".1.3.6.1.4.1.2021.13.16.2.1.3.$index";
  $descr = str_ireplace(array('temperature-', 'temp-'), '', $entry['lmTempSensorsDevice']);
  $value = $entry['lmTempSensorsValue'] * $scale;
  if ($entry['lmTempSensorsValue'] > 0 && $value <= 1000)
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'lmsensors', $descr, $scale, $value);
  }
}

$scale = 1;
foreach ($lm_array['fan'] as $index => $entry)
{
  $oid   = ".1.3.6.1.4.1.2021.13.16.3.1.3.$index";
  $descr = str_ireplace('fan-', '', $entry['lmFanSensorsDevice']);
  $value = $entry['lmFanSensorsValue'] * $scale;
  if ($entry['lmFanSensorsValue'] > 0)
  {
    discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'lmsensors', $descr, $scale, $value);
  }
}

$scale = 0.001;
foreach ($lm_array['volt'] as $index => $entry)
{
  $oid   = ".1.3.6.1.4.1.2021.13.16.4.1.3.$index";
  $descr = str_ireplace(array('voltage, ', 'volt-'), '', $entry['lmVoltSensorsDevice']);
  $value = $entry['lmVoltSensorsValue'] * $scale;
  if (is_numeric($entry['lmVoltSensorsValue']))
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'lmsensors', $descr, $scale, $value);
  }
}

// EOF
