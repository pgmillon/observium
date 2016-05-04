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

echo(" MG-SNMP-UPS-MIB ");

$cache['mge'] = array();
$cache['mge'] = snmpwalk_cache_multi_oid($device, "upsmgInputPhaseTable", $cache['mge'], "MG-SNMP-UPS-MIB");

// Input
$numPhase = snmp_get($device, "upsmgInputPhaseNum.0", "-Oqv", "MG-SNMP-UPS-MIB");

// Great job MGE - my devices don't have mginputPhaseIndex, and mginputMinimumVoltage and mginputMaximumVoltage. are using different indexes.
if (count(array_keys($cache['mge'])) > $numPhase) { unset($cache['mge'][0]); } // Remove [0] key with above 2 fields, leaving 1.0 etc for actual phases.
$scale = 0.1;
foreach ($cache['mge'] as $index => $entry)
{
  list($i,) = explode('.',$index,2);

  if ($i > $numPhase) { break; } // MGE returns 3 phase values even if their mgInputPhaseNum is 1. Doh.

  $descr = "Input"; if ($numPhase > 1) { $descr .= " Phase $index"; }

  if (is_numeric($entry['mginputVoltage']))
  {
    $oid   = ".1.3.6.1.4.1.705.1.6.2.1.2.$index"; // MG-SNMP-UPS-MIB:mginputVoltage.$index
    $value = $entry['mginputVoltage'];
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, 100+$index, 'mge-ups', $descr, $scale, $value);
  }

  if (is_numeric($entry['mginputCurrent']))
  {
    $oid   = ".1.3.6.1.4.1.705.1.6.2.1.6.$index"; // MG-SNMP-UPS-MIB:mginputCurrent.$index
    $value = $entry['mginputCurrent'];
    discover_sensor($valid['sensor'], 'current', $device, $oid, 100+$index, 'mge-ups', $descr, $scale, $value);
  }

  if (is_numeric($entry['mginputFrequency']))
  {
    $oid   = ".1.3.6.1.4.1.705.1.6.2.1.3.$index"; // MG-SNMP-UPS-MIB:mginputFrequency.$index
    $value = $entry['mginputFrequency'];
    discover_sensor($valid['sensor'], 'frequency', $device, $oid, 100+$index, 'mge-ups', $descr, $scale, $value);
  }
}

// Output
$cache['mge'] = array();
$cache['mge'] = snmpwalk_cache_multi_oid($device, "upsmgOutput", $cache['mge'], "MG-SNMP-UPS-MIB");

$entry = $cache['mge'][0]; // Use 0 only, upsmgOutput includes the PhaseTable used below.

// MG-SNMP-UPS-MIB::upsmgOutputOnBattery.0 = INTEGER: no(2)
if (isset($entry['upsmgOutputOnBattery']))
{
  $descr = "On Battery";
  $oid   = ".1.3.6.1.4.1.705.1.7.3.0";
  $value = $entry['upsmgOutputOnBattery'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, "upsmgOutputOnBattery.$index", 'mge-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'battery'));
}

// MG-SNMP-UPS-MIB::upsmgOutputOnByPass.0 = INTEGER: no(2)
if (isset($entry['upsmgOutputOnByPass']))
{
  $descr = "On Bypass";
  $oid   = ".1.3.6.1.4.1.705.1.7.4.0";
  $value = $entry['upsmgOutputOnByPass'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, "upsmgOutputOnByPass.$index", 'mge-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'battery'));
}

// FIXME TODO: State sensors:
// MG-SNMP-UPS-MIB::upsmgOutputUnavailableByPass.0 = INTEGER: no(2)
if (isset($entry['upsmgOutputUnavailableByPass']))
{
  $descr = "On Bypass";
  $oid   = ".1.3.6.1.4.1.705.1.7.5.0";
  $value = $entry['upsmgOutputUnavailableByPass'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, "upsmgOutputUnavailableByPass.$index", 'mge-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

// MG-SNMP-UPS-MIB::upsmgOutputNoByPass.0 = INTEGER: no(2)
if (isset($entry['upsmgOutputNoByPass']))
{
  $descr = "No Bypass";
  $oid   = ".1.3.6.1.4.1.705.1.7.6.0";
  $value = $entry['upsmgOutputNoByPass'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, "upsmgOutputNoByPass.$index", 'mge-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

// MG-SNMP-UPS-MIB::upsmgOutputUtilityOff.0 = INTEGER: no(2)
if (isset($entry['upsmgOutputUtilityOff']))
{
  $descr = "Utility Off";
  $oid   = ".1.3.6.1.4.1.705.1.7.7.0";
  $value = $entry['upsmgOutputUtilityOff'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, "upsmgOutputUtilityOff.$index", 'mge-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

// MG-SNMP-UPS-MIB::upsmgOutputOnBoost.0 = INTEGER: no(2)
if (isset($entry['upsmgOutputOnBoost']))
{
  $descr = "On Boost";
  $oid   = ".1.3.6.1.4.1.705.1.7.8.0";
  $value = $entry['upsmgOutputOnBoost'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, "upsmgOutputOnBoost.$index", 'mge-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

// MG-SNMP-UPS-MIB::upsmgOutputInverterOff.0 = INTEGER: no(2)
if (isset($entry['upsmgOutputInverterOff']))
{
  $descr = "Inverter Off";
  $oid   = ".1.3.6.1.4.1.705.1.7.9.0";
  $value = $entry['upsmgOutputInverterOff'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, "upsmgOutputInverterOff.$index", 'mge-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

// MG-SNMP-UPS-MIB::upsmgOutputOverLoad.0 = INTEGER: no(2)
if (isset($entry['upsmgOutputOverLoad']))
{
  $descr = "Over Load";
  $oid   = ".1.3.6.1.4.1.705.1.7.10.0";
  $value = $entry['upsmgOutputOverLoad'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, "upsmgOutputOverLoad.$index", 'mge-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

// MG-SNMP-UPS-MIB::upsmgOutputOverTemp.0 = INTEGER: no(2)
if (isset($entry['upsmgOutputOverTemp']))
{
  $descr = "Over Temperature";
  $oid   = ".1.3.6.1.4.1.705.1.7.11.0";
  $value = $entry['upsmgOutputOverTemp'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, "upsmgOutputOverTemp.$index", 'mge-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'temperature'));
}

// MG-SNMP-UPS-MIB::upsmgOutputOnBuck.0 = INTEGER: 2

$scale = 0.1;
$cache['mge'] = array();
$cache['mge'] = snmpwalk_cache_multi_oid($device, "upsmgOutputPhaseTable", $cache['mge'], "MG-SNMP-UPS-MIB");

$upsmgOutputPhaseNum = snmp_get($device, "upsmgOutputPhaseNum.0", "-Oqv", "MG-SNMP-UPS-MIB");

foreach ($cache['mge'] as $index => $entry)
{
  $descr = "Output"; if ($upsmgOutputPhaseNum > 1) { $descr .= " Phase $index"; }

  if ($index > $upsmgOutputPhaseNum) { break; } // MGE returns 3 phase values even if their mgOutputPhaseNum is 1. Doh.

  $oid   = ".1.3.6.1.4.1.705.1.7.2.1.4.$index"; // MG-SNMP-UPS-MIB:mgoutputLoadPerPhase.$index
  $value = $entry['mgoutputLoadPerPhase'];
  discover_sensor($valid['sensor'], 'capacity', $device, $oid, "mgoutputLoadPerPhase.$index", 'mge-ups', $descr, 1, $value, array('limit_high' => 85, 'limit_high_warn' => 70));

  $oid   = ".1.3.6.1.4.1.705.1.7.2.1.2.$index"; // MG-SNMP-UPS-MIB:mgoutputVoltage.$index
  $value = $entry['mgoutputVoltage'];
  discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'mge-ups', $descr, $scale, $value);

  $oid   = ".1.3.6.1.4.1.705.1.7.2.1.5.$index"; // MG-SNMP-UPS-MIB:mgoutputCurrent.$index
  $value = $entry['mgoutputCurrent'];
  discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'mge-ups', $descr, $scale, $value);

  $oid   = ".1.3.6.1.4.1.705.1.7.2.1.3.$index"; // MG-SNMP-UPS-MIB:mgoutputFrequency.$index
  $value = $entry['mgoutputFrequency'];
  discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, 'mge-ups', $descr, $scale, $value);
}

echo(" ");

// Battery data
$cache['mge'] = array();
foreach (array("upsmgBattery") as $table)
{
  echo("$table ");
  $cache['mge'] = snmpwalk_cache_multi_oid($device, $table, $cache['mge'], "MG-SNMP-UPS-MIB", NULL, OBS_SNMP_ALL_NUMERIC);
}

foreach ($cache['mge'] as $index => $entry)
{
  $descr = "Battery";

  // MG-SNMP-UPS-MIB::upsmgBatteryVoltage.0 = 810
  if (isset($entry['upsmgBatteryVoltage']))
  {
    $oid       = ".1.3.6.1.4.1.705.1.5.5.$index";
    $value     = $entry['upsmgBatteryVoltage'];

    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsmgBatteryVoltage.$index", 'mge', $descr, $scale, $value);
  }

  // MG-SNMP-UPS-MIB::upsmgBatteryCurrent.0 = 0
  if (isset($entry['upsmgBatteryCurrent']))
  {
    $oid       = ".1.3.6.1.4.1.705.1.5.6.$index";
    $value     = $entry['upsmgBatteryCurrent'];

    discover_sensor($valid['sensor'], 'current', $device, $oid, "upsmgBatteryCurrent.$index", 'mge', $descr, $scale, $value);
  }

  // MG-SNMP-UPS-MIB::upsmgBatteryTemperature.0 = INTEGER: 15
  if (isset($entry['upsmgBatteryTemperature']))
  {
    $oid       = ".1.3.6.1.4.1.705.1.5.7.$index";
    $value     = $entry['upsmgBatteryTemperature'];

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "upsmgBatteryTemperature.$index", 'mge', $descr, 1, $value);
  }

  // MG-SNMP-UPS-MIB::upsmgBatteryLevel.0 = INTEGER: 100
  if (isset($entry['upsmgBatteryLevel']))
  {
    $oid       = ".1.3.6.1.4.1.705.1.5.2.$index";
    $limits    = array('limit_low' => snmp_get($device, "upsmgConfigLowBatteryLevel.0", "-Oqc", "MG-SNMP-UPS-MIB"));
    $value     = $entry['upsmgBatteryLevel'];

    discover_sensor($valid['sensor'], 'capacity', $device, $oid, "upsmgBatteryLevel.$index", 'mge', $descr, 1, $value, $limits);
  }

  // MG-SNMP-UPS-MIB::upsmgBatteryRemainingTime.0 = INTEGER: 12180
  if (isset($entry['upsmgBatteryRemainingTime']))
  {
    $descr     = "Battery Runtime Remaining";
    $oid       = ".1.3.6.1.4.1.705.1.5.1.$index";
    $limits    = array('limit_low' => snmp_get($device, "upsmgConfigLowBatteryTime.0", "-Oqc", "MG-SNMP-UPS-MIB"));
    $value     = $entry['upsmgBatteryRemainingTime'];
    $scale     = 1 / 60;

    // FIXME: Use this as limit?
    // MG-SNMP-UPS-MIB::upsmgConfigLowBatteryTime.0 = 180

    discover_sensor($valid['sensor'], 'runtime', $device, $oid, "upsmgBatteryRemainingTime.$index", 'mge', $descr, $scale, $value);
  }

  // MG-SNMP-UPS-MIB::upsmgBatteryFaultBattery.0 = no
  if (isset($entry['upsmgBatteryFaultBattery']))
  {
    $descr = "Battery Fault";
    $oid   = ".1.3.6.1.4.1.705.1.5.9.$index";
    $value = $entry['upsmgBatteryFaultBattery'];
    discover_sensor($valid['sensor'], 'state', $device, $oid, "upsmgBatteryFaultBattery.$index", 'mge-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'battery'));
  }

  // MG-SNMP-UPS-MIB::upsmgBatteryChargerFault.0 = no
  if (isset($entry['upsmgBatteryChargerFault']))
  {
    $descr = "Charger Fault";
    $oid   = ".1.3.6.1.4.1.705.1.5.15.$index";
    $value = $entry['upsmgBatteryChargerFault'];
    discover_sensor($valid['sensor'], 'state', $device, $oid, "upsmgBatteryChargerFault.$index", 'mge-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'battery'));
  }

  // MG-SNMP-UPS-MIB::upsmgBatteryLowBattery.0 = no
  // According to MGE, LowCondition is the correct indicator, so we ignore LowBattery.
  // MG-SNMP-UPS-MIB::upsmgBatteryLowCondition.0 = no
  if (isset($entry['upsmgBatteryLowCondition']))
  {
    $descr = "Battery Low";
    $oid   = ".1.3.6.1.4.1.705.1.5.16.$index";
    $value = $entry['upsmgBatteryLowCondition'];
    discover_sensor($valid['sensor'], 'state', $device, $oid, "upsmgBatteryLowCondition.$index", 'mge-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'battery'));
  }

  // MG-SNMP-UPS-MIB::upsmgBatteryReplacement.0 = no
  if (isset($entry['upsmgBatteryReplacement']))
  {
    $descr = "Battery Replacement Needed";
    $oid   = ".1.3.6.1.4.1.705.1.5.11.$index";
    $value = $entry['upsmgBatteryReplacement'];
    discover_sensor($valid['sensor'], 'state', $device, $oid, "upsmgBatteryReplacement.$index", 'mge-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'battery'));
  }

}

echo(" ");

// Environmental monitoring

$cache['mge'] = array();
foreach (array("upsmgEnviron") as $table)
{
  echo("$table ");
  $cache['mge'] = snmpwalk_cache_multi_oid($device, $table, $cache['mge'], "MG-SNMP-UPS-MIB", NULL, OBS_SNMP_ALL_NUMERIC);
}

// MG-SNMP-UPS-MIB::upsmgEnvironAmbientTemp.0 = INTEGER: 0
// MG-SNMP-UPS-MIB::upsmgEnvironAmbientHumidity.0 = INTEGER: 0
$scale = 0.1;
foreach ($cache['mge'] as $index => $entry)
{
    $descr           = "Ambient";

    $oid             = ".1.3.6.1.4.1.705.1.8.1.$index";
    $value           = $entry['upsmgEnvironAmbientTemp'];

    if ($value != 0)
    { // Temp = 0 -> Sensor not available
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "upsmgEnvironAmbientTemp.$index", 'mge', $descr, $scale, $value);
    }

    $oid             = ".1.3.6.1.4.1.705.1.8.2.$index";
    $value           = $entry['upsmgEnvironAmbientHumidity'];

    if ($value != 0)
    { // Humidity = 0 -> Sensor not available
      // Should be /10 on all devices but apparently not on all, let's try to work around:
      if ($value > 100) { $scale = 0.1; } else { $scale = 1; }
      discover_sensor($valid['sensor'], 'humidity', $device, $oid, "upsmgEnvironAmbientHumidity.$index", 'mge', $descr, $scale, $value);
    }
}

$cache['mge'] = array();
foreach (array("upsmgConfigEnvironmentTable","upsmgEnvironmentSensorTable") as $table)
{
  echo("$table ");
  $cache['mge'] = snmpwalk_cache_multi_oid($device, $table, $cache['mge'], "MG-SNMP-UPS-MIB", NULL, OBS_SNMP_ALL_NUMERIC);
}

// upsmgConfigSensorIndex.1 = 1
// upsmgConfigSensorName.1 = "Environment sensor"
// upsmgConfigTemperatureLow.1 = 5
// upsmgConfigTemperatureHigh.1 = 40
// upsmgConfigTemperatureHysteresis.1 = 2
// upsmgConfigHumidityLow.1 = 5
// upsmgConfigHumidityHigh.1 = 90
// upsmgConfigHumidityHysteresis.1 = 5
// upsmgConfigInput1Name.1 = "Input //1"
// upsmgConfigInput1ClosedLabel.1 = "closed"
// upsmgConfigInput1OpenLabel.1 = "open"
// upsmgConfigInput2Name.1 = "Input //2"
// upsmgConfigInput2ClosedLabel.1 = "closed"
// upsmgConfigInput2OpenLabel.1 = "open"
//
// upsmgEnvironmentIndex.1 = 1
// upsmgEnvironmentComFailure.1 = no
// upsmgEnvironmentTemperature.1 = 287
// upsmgEnvironmentTemperatureLow.1 = no
// upsmgEnvironmentTemperatureHigh.1 = no
// upsmgEnvironmentHumidity.1 = 17
// upsmgEnvironmentHumidityLow.1 = no
// upsmgEnvironmentHumidityHigh.1 = no
// upsmgEnvironmentInput1State.1 = open
// upsmgEnvironmentInput2State.1 = open

foreach ($cache['mge'] as $index => $entry)
{
  if ($entry['upsmgEnvironmentComFailure'] == 'no') // yes means no environment module present
  {
    $descr           = $entry['upsmgConfigSensorName'];

    $oid             = ".1.3.6.1.4.1.705.1.8.7.1.6.$index";
    $value           = $entry['upsmgEnvironmentHumidity'];
    // FIXME warninglevels might need some other calculation instead of hysteresis
    $hysteresis      = $entry['upsmgConfigHumidityHysteresis'];
    $limits          = array('limit_high'      => $entry['upsmgConfigHumidityHigh'],
                             'limit_low'       => $entry['upsmgConfigHumidityLow'],
                             'limit_high_warn' => $entry['upsmgConfigHumidityHigh'] - $hysteresis,
                             'limit_low_warn'  => $entry['upsmgConfigHumidityLow']  + $hysteresis);

    if ($value != 0)
    { // Humidity = 0 -> Sensor not available
      // Should be /10 on all devices but apparently not on all, let's try to work around:
      if ($value > 100) { $scale = 0.1; } else { $scale = 1; }
      discover_sensor($valid['sensor'], 'humidity', $device, $oid, $index, 'mge', $descr, $scale, $value, $limits);
    }

    $scale           = 0.1;
    $oid             = '.1.3.6.1.4.1.705.1.8.7.1.3.' . $index;
    $value           = $entry['upsmgEnvironmentTemperature'];
    // FIXME warninglevels might need some other calculation instead of hysteresis
    $hysteresis      = $entry['upsmgConfigTemperatureHysteresis'];
    $limits          = array('limit_high'      => $entry['upsmgConfigTemperatureHigh'],
                             'limit_low'       => $entry['upsmgConfigTemperatureLow'],
                             'limit_high_warn' => $entry['upsmgConfigTemperatureHigh'] - $hysteresis,
                             'limit_low_warn'  => $entry['upsmgConfigTemperatureLow']  + $hysteresis);

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'mge', $descr, $scale, $value, $limits);
  }
}

// EOF
