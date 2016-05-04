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

$mib = 'Baytech-MIB-403-1';
echo(" $mib ");
$modulecurrentmax = 160;

$oids  = snmpwalk_cache_multi_oid($device, "sBTAModulesRPCEntry", array(), $mib);
$count = count($oids);
$scale = 0.1;

if (OBS_DEBUG > 1 && $count) { var_dump($oids); }
foreach ($oids as $index => $entry)
{
  $descr = ($count > 1 ? "Module $index" : "Module");

  // Current
  if (is_numeric($entry['sBTAModulesRPCCurrent']) && $entry['sBTAModulesRPCCurrent'] > -1)
  {
    $oid     = '.1.3.6.1.4.1.4779.1.3.5.2.1.4.' . $index;
    $value   = $entry['sBTAModulesRPCCurrent'];
//    $options = array('limit_high' => (isset($entry['sBTAModulesRPCCurrentAlarmThreshold']) && $entry['sBTAModulesRPCCurrentAlarmThreshold'] > -3000 ? $entry['sBTAModulesRPCCurrentAlarmThreshold'] * 0.1 : NULL),
    $options = array('limit_high' => $modulecurrentmax * $scale ,
                     'limit_low'  => (isset($entry['sBTAModulesRPCLowCurrentThreshold']) && $entry['sBTAModulesRPCLowCurrentThreshold'] > -3000 ? $entry['sBTAModulesRPCLowCurrentThreshold'] * 0.1 : NULL));

    discover_sensor($valid['sensor'], 'current', $device, $oid, "sBTAModulesRPCCurrent.$index", 'baytech', $descr . ' Current', $scale, $value, $options);
  }

  // Voltage
  if (is_numeric($entry['sBTAModulesRPCVoltage']) && $entry['sBTAModulesRPCVoltage'] != -2000000)
  {
    $oid     = '.1.3.6.1.4.1.4779.1.3.5.2.1.6.' . $index;
    $value   = $entry['sBTAModulesRPCVoltage'];
    $options = array('limit_high' => (isset($entry['sBTAModulesRPCOverVoltageThreshold']) && $entry['sBTAModulesRPCOverVoltageThreshold'] > -3000 ? $entry['sBTAModulesRPCOverVoltageThreshold'] * 0.1 : NULL),
                     'limit_low'  => (isset($entry['sBTAModulesRPCUnderVoltageThreshold']) && $entry['sBTAModulesRPCUnderVoltageThreshold'] > -3000 ? $entry['sBTAModulesRPCUnderVoltageThreshold'] * 0.1 : NULL));

    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "sBTAModulesRPCVoltage.$index", 'baytech', $descr . ' Voltage', $scale, $value, $options);
  }

  // Power
  if (is_numeric($entry['sBTAModulesRPCPower']) && $entry['sBTAModulesRPCPower'] != -2000000)
  {
    $oid     = '.1.3.6.1.4.1.4779.1.3.5.2.1.7.' . $index;
    $value   = $entry['sBTAModulesRPCPower'];
    $options = array('limit_high' => (isset($entry['NOTSUPPORTED']) && $entry['NOTSUPPORTED'] > -3000 ? $entry['NOTSUPPORTED'] : 3328),
                     'limit_low'  => (isset($entry['NOTSUPPORTED']) && $entry['NOTSUPPORTED'] > -3000 ? $entry['NOTSUPPORTED'] : 0));

    discover_sensor($valid['sensor'], 'power', $device, $oid, "sBTAModulesRPCPower.$index", 'baytech', $descr . ' Power', 1, $value, $options);
  }

  // Temperature
  if (is_numeric($entry['sBTAModulesRPCTemperature']) && $entry['sBTAModulesRPCTemperature'] > -1000000)
  {
    $oid     = '.1.3.6.1.4.1.4779.1.3.5.2.1.8.' . $index;
    $value   = $entry['sBTAModulesRPCTemperature'];
    $options = array('limit_high' => (isset($entry['sBTAModulesRPCOverTempThreshold']) && $entry['sBTAModulesRPCOverTempThreshold'] > -3000 ? $entry['sBTAModulesRPCOverTempThreshold'] * 0.1 : NULL),
                     'limit_low'  => (isset($entry['NOTSUPPORTED']) && $entry['NOTSUPPORTED'] > -3000 ? $entry['NOTSUPPORTED'] * 0.1 : NULL));

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "sBTAModulesRPCTemperature.$index", 'baytech', $descr . ' Temperature', $scale, $value, $options);
  }
}

$oids  = snmpwalk_cache_multi_oid($device, "sBTAModulesRPCBreakersEntry", array(), $mib);
$count = count($oids);
$scale = 0.1;

if (OBS_DEBUG > 1 && $count) { var_dump($oids); }
foreach ($oids as $index => $entry)
{
  $module = $entry['sBTAModulesRPCBreakersModulesIndex'];
  $breaker = $entry['sBTAModulesRPCBreakersBreakersIndex'];
  $descr = "Breaker $module.$breaker";

  // Current
  if (is_numeric($entry['sBTAModulesRPCBreakersCurrent']) && $entry['sBTAModulesRPCBreakersCurrent'] > -1)
  {
    $oid     = ".1.3.6.1.4.1.4779.1.3.5.9.1.4.$module.$breaker";
    $value   = $entry['sBTAModulesRPCBreakersCurrent'];
    $options = array('limit_high' => (isset($entry['sBTAModulesRPCBreakersCurrentAlarmThreshold']) && $entry['sBTAModulesRPCBreakersCurrentAlarmThreshold'] > -3000 ? $entry['sBTAModulesRPCBreakersCurrentAlarmThreshold'] * 0.1 : NULL),
                     'limit_low'  => 0);

    discover_sensor($valid['sensor'], 'current', $device, $oid, "sBTAModulesRPCBreakersCurrent.$module.$breaker", 'baytech', $descr . ' Current', $scale, $value, $options);
  }
}

// EOF
