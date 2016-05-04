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

echo(" Sentry3-MIB ");

$scale = 0.01;
$scale_voltage = 0.1;

$sentry3_InfeedEntry = snmpwalk_cache_twopart_oid($device, 'InfeedEntry', array(), 'Sentry3-MIB');
$sentry3_OutletEntry = snmpwalk_cache_threepart_oid($device, 'OutletEntry', array(), 'Sentry3-MIB');

foreach ($sentry3_InfeedEntry as $tower => $feeds)
{
  foreach ($feeds as $feed => $entry)
  {
    $descr = str_replace('_', ', ', $entry['infeedName']);
    $index = "$tower.$feed";

    // infeedLoadValue
    $oid   = '.1.3.6.1.4.1.1718.3.2.2.1.7.' . $index;
    if (isset($entry['infeedLoadValue']) && $entry['infeedLoadValue'] >= 0)
    {
      $limits = array('limit_high'      => $entry['infeedCapacity'],
                      'limit_high_warn' => $entry['infeedLoadHighThresh']);
      $value  = $entry['infeedLoadValue'];

      discover_sensor($valid['sensor'], 'current', $device, $oid, "infeedLoad.$index", 'sentry3', $descr, $scale, $value, $limits);
    } else {
      // FIXME. States for $entry['infeedLoadStatus']
    }

    // infeedVoltage
    $oid   = '.1.3.6.1.4.1.1718.3.2.2.1.11.' . $index;
    if (isset($entry['infeedVoltage']) && $entry['infeedVoltage'] >= 0)
    {
      $value = $entry['infeedVoltage'];

      discover_sensor($valid['sensor'], 'voltage', $device, $oid, "infeedVoltage.$index", 'sentry3', $descr, $scale_voltage, $value);
    }

    // infeedPower
    $oid   = '.1.3.6.1.4.1.1718.3.2.2.1.12.' . $index;
    if (isset($entry['infeedPower']) && $entry['infeedPower'] >= 0)
    {
      $value = $entry['infeedPower'];

      discover_sensor($valid['sensor'], 'power', $device, $oid, "infeedPower.$index", 'sentry3', $descr, 1, $value);
    }

    // outletLoadValue
    foreach ($sentry3_OutletEntry[$tower][$feed] as $outlet => $ou_entry)
    {
      $descr = str_replace('_', ', ', $ou_entry['outletName']);
      $index = "$tower.$feed.$outlet";

      $oid   = '.1.3.6.1.4.1.1718.3.2.3.1.7.' . $index;
      if (isset($ou_entry['outletLoadValue']) && $ou_entry['outletLoadValue'] >= 0)
      {
        // Should be "outletCapacity" but is always -1. According to MIB: "A negative value indicates that the capacity was not available."
        $limits = array('limit_high' => $ou_entry['outletLoadHighThresh'],
                        'limit_low'  => $ou_entry['outletLoadLowThresh']);
        $value  = $ou_entry['outletLoadValue'];

        discover_sensor($valid['sensor'], 'current', $device, $oid, "outletLoad.$index", 'sentry3', $descr, $scale, $value, $limits);
      } else {
        // FIXME. States for $ou_entry['outletLoadStatus'], $ou_entry['outletStatus']
      }
    }
  }
}

// temperature/humidity sensor
$sentry3_TempHumidSensorEntry = snmpwalk_cache_oid($device, 'TempHumidSensorEntry', array(), 'Sentry3-MIB');
if (OBS_DEBUG > 1 && count($sentry3_TempHumidSensorEntry)) { var_dump($sentry3_TempHumidSensorEntry); }

foreach ($sentry3_TempHumidSensorEntry as $index => $entry)
{
  $descr = $entry['tempHumidSensorName'];

  // tempHumidSensorTempValue
  $oid        = '.1.3.6.1.4.1.1718.3.2.5.1.6.'.$index;

  if (isset($entry['tempHumidSensorTempValue']) && $entry['tempHumidSensorTempValue'] >= 0)
  {
    if (isset($entry['tempHumidSensorTempScale']))
    {
      // Note, after MIB revision "200606120930Z" scale changed to "tenth degrees"
      $scale_temp = 0.1;
    } else {
      $scale_temp = 1;
    }

    $value      = $entry['tempHumidSensorTempValue'];
    $options    = array('limit_high' => (isset($entry['tempHumidSensorTempHighThresh']) ? $entry['tempHumidSensorTempHighThresh'] * $scale_temp : NULL),
                        'limit_low'  => (isset($entry['tempHumidSensorTempLowThresh'])  ? $entry['tempHumidSensorTempLowThresh']  * $scale_temp : NULL));

    if ($entry['tempHumidSensorTempScale'] == 'fahrenheit')
    {
      $options['sensor_unit'] = 'F';
      $options['limit_high'] = f2c($options['limit_high']);
      $options['limit_low']  = f2c($options['limit_low']);
    }

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "tempHumidSensor.$index", 'sentry3', $descr, $scale_temp, $value, $options);
  }

  // tempHumidSensorHumidValue
  $oid        = '.1.3.6.1.4.1.1718.3.2.5.1.10.'.$index;
  if (isset($entry['tempHumidSensorHumidValue']) && $entry['tempHumidSensorHumidValue'] >= 0)
  {
    $options    = array('limit_high' => (isset($entry['tempHumidSensorHumidHighThresh']) ? $entry['tempHumidSensorHumidHighThresh'] : NULL),
                        'limit_low'  => (isset($entry['tempHumidSensorHumidLowThresh'])  ? $entry['tempHumidSensorHumidLowThresh']  : NULL));
    $value      = $entry['tempHumidSensorHumidValue'];

    discover_sensor($valid['sensor'], 'humidity', $device, $oid, "tempHumidSensor.$index", 'sentry3', $descr, 1, $value, $options);
  }
}

// EOF
