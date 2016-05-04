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

echo(" DeltaUPS-MIB ");

$dupsSensors = array(
  array('OID' => "1.3.6.1.4.1.2254.2.4.7.7.0",  'descr' => "Battery",     'scale' => 1,  'class' => 'current'),     # dupsBatteryCurrent.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.5.5.0",  'descr' => "Output",      'scale' => 0.1, 'class' => 'current'),     # dupsOutputCurrent1.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.4.4.0",  'descr' => "Input",       'scale' => 0.1, 'class' => 'current'),     # dupsInputCurrent1.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.6.4.0",  'descr' => "Bypass",      'scale' => 0.1, 'class' => 'current'),     # dupsBypassCurrent1.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.7.8.0",  'descr' => "Battery Capacity", 'scale' => 1, 'class' => 'capacity'),     # dupsBatteryCapacity.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.7.5.0",  'descr' => "Battery Runtime Remaining", 'scale' => 1, 'class' => 'runtime'),     # dupsBatteryEstimatedTime.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.5.7.0",  'descr' => "Output Load",      'scale' => 1, 'class' => 'capacity'),     # dupsOutputLoad1.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.5.2.0",  'descr' => "Output",      'scale' => 0.1, 'class' => 'frequency'),   # dupsOutputFrequency.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.4.2.0",  'descr' => "Input",       'scale' => 0.1, 'class' => 'frequency'),   # dupsInputFrequency.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.10.2.0", 'descr' => "Environment", 'scale' => 1,  'class' => 'humidity'),    # dupsEnvHumidity.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.10.1.0", 'descr' => "Environment", 'scale' => 1,  'class' => 'temperature'), # dupsEnvTemperature.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.7.9.0",  'descr' => "Battery",     'scale' => 1,  'class' => 'temperature'), # dupsTemperature.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.7.6.0",  'descr' => "Battery",     'scale' => 0.1, 'class' => 'voltage'),     # dupsBatteryVoltage.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.5.4.0",  'descr' => "Output",      'scale' => 0.1, 'class' => 'voltage'),     # dupsOutputVoltage1.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.4.3.0",  'descr' => "Input",       'scale' => 0.1, 'class' => 'voltage'),     # dupsInputVoltage1.0
  array('OID' => "1.3.6.1.4.1.2254.2.4.6.3.0",  'descr' => "Bypass",      'scale' => 0.1, 'class' => 'voltage'),     # dupsBypassVoltage1.0
);

//FIXME - This only discovers a single phase - probably needs more values above? ie dupsBypassVoltage1.0 is polled, dupsBypassVoltage2.0 and 3.0 aren't, etc.

foreach ($dupsSensors as $eachArray => $eachValue)
{
  // DeltaUPS does not have tables, so no need to walk, only need snmpget
  $value = snmp_get($device, $eachValue['OID'], "-Ovq");
  // Get index values from current OID
  $preIndex = strstr($eachValue['OID'], '2254.2.4');
  // Format and strip index to only include everything after 2254.2.4
  $index = substr($preIndex, 9);

  // Prevent NULL returned values from being added as sensors
  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], $eachValue['class'], $device, $eachValue['OID'], $index, "DeltaUPS", $eachValue['descr'], $eachValue['scale'], $value);
  }
}

// EOF
