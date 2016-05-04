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

echo(" LIEBERT-GP-ENVIRONMENTAL-MIB ");

#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureMeasurementDegC.1 = INTEGER: 22 degrees Celsius
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureHighThresholdDegC.1 = INTEGER: 26 degrees Celsius
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureLowThresholdDegC.1 = INTEGER: 16 degrees Celsius
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureMeasurementTenthsDegC.3 = 244
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureHighThresholdTenthsDegC.3 = 350
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureLowThresholdTenthsDegC.3 = 100
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureDescrDegC.1 = OID: LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvControlTemperature
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureDescrDegC.2 = OID: LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvSupplyAirTemperature
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureDescrDegC.3 = OID: LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvReturnAirTemperature
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureDescrDegC.7 = OID: LIEBERT-GP-ENVIRONMENTAL-MIB::lgpDigitalScrollCompressor1Temperature
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureDescrDegC.8 = OID: LIEBERT-GP-ENVIRONMENTAL-MIB::lgpDigitalScrollCompressor2Temperature
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvTemperatureDescrDegC.10 = OID: LIEBERT-GP-ENVIRONMENTAL-MIB::lgpCoolantTemperature

// temperature/humidity sensor
$lgpEnvTemperature = snmpwalk_cache_oid($device, 'lgpEnvTemperature', array(), 'LIEBERT-GP-ENVIRONMENTAL-MIB', mib_dirs('liebert'));

foreach ($lgpEnvTemperature as $index => $entry)
{
  $descr = str_replace(array('lgp', 'lgpEnv'), '', $entry['lgpEnvTemperatureDescrDegC']);
  $descr = trim(preg_replace('/([A-Z][a-z]+\d*)/', '$1 ', $descr)); // turn "DigitalScrollCompressor1Temperature" into "Digital Scroll Compressor1 Temperature"

  if (isset($entry['lgpEnvTemperatureMeasurementTenthsDegC']) &&
      $entry['lgpEnvTemperatureMeasurementTenthsDegC'] >= 0 && $entry['lgpEnvTemperatureMeasurementTenthsDegC'] < 2147483647)
  {
    // Tenths tables have more accurate values
    $scale      = 0.1;
    $oid        = '.1.3.6.1.4.1.476.1.42.3.4.1.3.3.1.50.'.$index;
    $value      = $entry['lgpEnvTemperatureMeasurementTenthsDegC'];
    $limits     = array('limit_high' => (isset($entry['lgpEnvTemperatureHighThresholdTenthsDegC']) ? $entry['lgpEnvTemperatureHighThresholdTenthsDegC'] * $scale : NULL),
                        'limit_low'  => (isset($entry['lgpEnvTemperatureLowThresholdTenthsDegC'])  ? $entry['lgpEnvTemperatureLowThresholdTenthsDegC']  * $scale : NULL));

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "lgpEnvTemperatureMeasurementTenthsDegC.$index", 'liebert', $descr, $scale, $value, $limits);
  }
  else if (isset($entry['lgpEnvTemperatureMeasurementDegC']) &&
           $entry['lgpEnvTemperatureMeasurementDegC'] >= 0 && $entry['lgpEnvTemperatureMeasurementDegC'] < 2147483647)
  {
    $oid        = '.1.3.6.1.4.1.476.1.42.3.4.1.3.3.1.3.'.$index;
    $value      = $entry['lgpEnvTemperatureMeasurementDegC'];
    $limits     = array('limit_high' => (isset($entry['lgpEnvTemperatureHighThresholdDegC']) ? $entry['lgpEnvTemperatureHighThresholdDegC'] : NULL),
                        'limit_low'  => (isset($entry['lgpEnvTemperatureLowThresholdDegC'])  ? $entry['lgpEnvTemperatureLowThresholdDegC']  : NULL));

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "lgpEnvTemperatureMeasurementDegC.$index", 'liebert', $descr, 1, $value, $limits);
  }
}

#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvHumidityMeasurementRel.1 = INTEGER: 18 percent Relative Humidity
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvHumidityHighThresholdRel.1 = INTEGER: 0 percent Relative Humidity
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvHumidityLowThresholdRel.1 = INTEGER: 0 percent Relative Humidity
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvHumidityMeasurementRelTenths.1 = 554
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvHumidityHighThresholdRelTenths.1 = 800
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvHumidityLowThresholdRelTenths.1 = 150
#LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvHumidityDescrRel.1 = lgpEnvControlHumidity

$lgpEnvHumidity = snmpwalk_cache_oid($device, 'lgpEnvHumidity', array(), 'LIEBERT-GP-ENVIRONMENTAL-MIB', mib_dirs('liebert'));

foreach ($lgpEnvHumidity as $index => $entry)
{
  $descr = str_replace(array('lgp', 'lgpEnv'), '', $entry['lgpEnvHumidityDescrRel']);
  $descr = trim(preg_replace('/([A-Z][a-z]+\d*)/', '$1 ', $descr)); // turn "EnvControlHumidity" into "Env Control Humidity"

  if (isset($entry['lgpEnvHumidityMeasurementRelTenths']) &&
      $entry['lgpEnvHumidityMeasurementRelTenths'] >= 0 && $entry['lgpEnvHumidityMeasurementRelTenths'] < 2147483647)
  {
    // Tenths tables have more accurate values
    $scale      = 0.1;
    $oid        = '.1.3.6.1.4.1.476.1.42.3.4.2.2.3.1.50.'.$index;
    $value      = $entry['lgpEnvHumidityMeasurementRelTenths'];
    $limits     = array('limit_high' => (isset($entry['lgpEnvHumidityHighThresholdRelTenths']) ? $entry['lgpEnvHumidityHighThresholdRelTenths'] * $scale : NULL),
                        'limit_low'  => (isset($entry['lgpEnvHumidityLowThresholdRelTenths'])  ? $entry['lgpEnvHumidityLowThresholdRelTenths']  * $scale : NULL));

    discover_sensor($valid['sensor'], 'humidity', $device, $oid, "lgpEnvHumidityMeasurementRel.$index", 'liebert', $descr, $scale, $value, $limits);
  }
  else if (isset($entry['lgpEnvHumidityMeasurementRel']) && $entry['lgpEnvHumidityMeasurementRel'] >= 0)
  {
    $oid        = '.1.3.6.1.4.1.476.1.42.3.4.2.2.3.1.3.'.$index;
    $value      = $entry['lgpEnvHumidityMeasurementRel'];
    $limits     = array('limit_high' => (isset($entry['lgpEnvHumidityHighThresholdRel']) ? $entry['lgpEnvHumidityHighThresholdRel'] : NULL),
                        'limit_low'  => (isset($entry['lgpEnvHumidityLowThresholdRel'])  ? $entry['lgpEnvHumidityLowThresholdRel']  : NULL));

    discover_sensor($valid['sensor'], 'humidity', $device, $oid, "lgpEnvHumidityMeasurementRel.$index", 'liebert', $descr, 1, $value, $limits);
  }
}

//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateSystem.0 = INTEGER: on(1)
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateCooling.0 = INTEGER: on(1)
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateHumidifying.0 = INTEGER: off(2)
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateDehumidifying.0 = INTEGER: on(1)
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateFan.0 = INTEGER: on(1)
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateCoolingCapacity.0 = Gauge32: 81 percent
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateHeatingCapacity.0 = Gauge32: 0 percent
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateOperatingReason.0 = INTEGER: none(1)
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateOperatingMode.0 = INTEGER: auto(1)
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateFanCapacity.0 = Gauge32: 60 percent
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateFreeCoolingCapacity.0 = Gauge32: 0 percent
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateDehumidifyingCapacity.0 = Gauge32: 81 percent
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateHumidifyingCapacity.0 = Gauge32: 0 percent
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateFreeCooling.0 = INTEGER: off(2)
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateElectricHeater.0 = INTEGER: off(2)
//LIEBERT-GP-ENVIRONMENTAL-MIB::lgpEnvStateHotWater.0 = INTEGER: off(2)
$lgpEnvState = snmpwalk_cache_oid($device, 'lgpEnvState', array(), 'LIEBERT-GP-ENVIRONMENTAL-MIB', mib_dirs('liebert'));

$states = array(
  'lgpEnvStateSystem'         => array('type' => 'state', 'oid' => '.1.3.6.1.4.1.476.1.42.3.4.3.1.0'),
  'lgpEnvStateCooling'        => array('type' => 'state', 'oid' => '.1.3.6.1.4.1.476.1.42.3.4.3.2.0'),
  'lgpEnvStateHumidifying'    => array('type' => 'state', 'oid' => '.1.3.6.1.4.1.476.1.42.3.4.3.4.0'),
  'lgpEnvStateDehumidifying'  => array('type' => 'state', 'oid' => '.1.3.6.1.4.1.476.1.42.3.4.3.5.0'),
  'lgpEnvStateFan'            => array('type' => 'state', 'oid' => '.1.3.6.1.4.1.476.1.42.3.4.3.7.0'),
  'lgpEnvStateFreeCooling'    => array('type' => 'state', 'oid' => '.1.3.6.1.4.1.476.1.42.3.4.3.20.0'),
  'lgpEnvStateElectricHeater' => array('type' => 'state', 'oid' => '.1.3.6.1.4.1.476.1.42.3.4.3.21.0'),
  'lgpEnvStateHotWater'       => array('type' => 'state', 'oid' => '.1.3.6.1.4.1.476.1.42.3.4.3.22.0'),
  'lgpEnvStateCoolingCapacity'       => array('type' => 'capacity', 'oid' => '.1.3.6.1.4.1.476.1.42.3.4.3.9.0'),
  'lgpEnvStateHeatingCapacity'       => array('type' => 'capacity', 'oid' => '.1.3.6.1.4.1.476.1.42.3.4.3.10.0'),
  'lgpEnvStateFanCapacity'           => array('type' => 'capacity', 'oid' => '.1.3.6.1.4.1.476.1.42.3.4.3.16.0'),
  'lgpEnvStateFreeCoolingCapacity'   => array('type' => 'capacity', 'oid' => '.1.3.6.1.4.1.476.1.42.3.4.3.17.0'),
  'lgpEnvStateDehumidifyingCapacity' => array('type' => 'capacity', 'oid' => '.1.3.6.1.4.1.476.1.42.3.4.3.18.0'),
);

foreach ($lgpEnvState[0] as $name => $value)
{
  $descr = str_replace('lgpEnvState', '', $name);
  $descr = trim(preg_replace('/([A-Z][a-z]+\d*)/', '$1 ', $descr)); // turn "EnvStateSystem" into "Env State System"
  if (isset($states[$name]))
  {
    $oid = $states[$name]['oid'];
    switch($states[$name]['type'])
    {
      case 'capacity':
        // Capacity
        discover_sensor($valid['sensor'], 'capacity', $device, $oid, "$name.0", 'liebert', $descr, 1, $value);
        break;
      default:
        // Statuses
        discover_status($device, $oid, "$name.0", 'liebert-state', $descr, $value);
    }
  }
}

// EOF
