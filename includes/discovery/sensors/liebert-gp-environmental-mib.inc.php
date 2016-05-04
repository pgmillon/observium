<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

echo(" LIEBERT-GP-ENVIRONMENTAL-MIB ");

#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureMeasurementDegC.1 = INTEGER: 22 degrees Celsius
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureHighThresholdDegC.1 = INTEGER: 26 degrees Celsius
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureLowThresholdDegC.1 = INTEGER: 16 degrees Celsius

#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvHumidityMeasurementRel.1 = INTEGER: 18 percent Relative Humidity
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvHumidityHighThresholdRel.1 = INTEGER: 0 percent Relative Humidity
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvHumidityLowThresholdRel.1 = INTEGER: 0 percent Relative Humidity

// temperature/humidity sensor
$lgpEnvTemperature = snmpwalk_cache_oid($device, 'lgpEnvTemperature', array(), 'LIEBERT-GP-ENVIRONMENTAL-MIB', mib_dirs('liebert'));

foreach ($lgpEnvTemperature as $index => $entry)
{
  if (isset($entry['lgpEnvTemperatureMeasurementDegC']) && $entry['lgpEnvTemperatureMeasurementDegC'] >= 0)
  {
    $descr = $entry['lgpEnvTemperatureDescrDegC'];
    $index = $entry['lgpEnvTemperatureIdDegC'];

    //lgpEnvTemperatureMeasurementDegC
    $oid        = '.1.3.6.1.4.1.476.1.42.3.4.1.3.3.1.3.'.$index;
    $value      = $entry['lgpEnvTemperatureMeasurementDegC'];
    $limits     = array('limit_high' => (isset($entry['lgpEnvTemperatureHighThresholdDegC']) ? $entry['lgpEnvTemperatureHighThresholdDegC'] : NULL),
                        'limit_low'  => (isset($entry['lgpEnvTemperatureLowThresholdDegC'])  ? $entry['lgpEnvTemperatureLowThresholdDegC']  : NULL));

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "lgpEnvTemperatureMeasurementDegC.$index", 'liebert', $descr, 1, $value, $limits);
  }
}

$lgpEnvHumidity = snmpwalk_cache_oid($device, 'lgpEnvHumidity', array(), 'LIEBERT-GP-REGISTRATION-MIB:LIEBERT-GP-ENVIRONMENTAL-MIB', mib_dirs('liebert'));

foreach ($lgpEnvHumidity as $index => $entry)
{
  if (isset($entry['lgpEnvHumidityMeasurementRel']) && $entry['lgpEnvHumidityMeasurementRel'] >= 0)
  {
    $descr = $entry['lgpEnvHumidityDescrRel'];
    $index = $entry['lgpEnvHumidityIdRel'];

    //lgpEnvHumidityMeasurementRel
    $oid        = '.1.3.6.1.4.1.476.1.42.3.4.2.2.3.1.3.'.$index;
    $value      = $entry['lgpEnvHumidityMeasurementRel'];
    $limits     = array('limit_high' => (isset($entry['lgpEnvHumidityHighThresholdRel']) ? $entry['lgpEnvHumidityHighThresholdRel'] : NULL),
                        'limit_low'  => (isset($entry['lgpEnvHumidityLowThresholdRel'])  ? $entry['lgpEnvHumidityLowThresholdRel']  : NULL));

    discover_sensor($valid['sensor'], 'humidity', $device, $oid, "lgpEnvHumidityMeasurementRel.$index", 'liebert', $descr, 1, $value, $limits);
  }
}

// EOF
