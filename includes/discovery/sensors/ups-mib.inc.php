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

// RFC1628 UPS-MIB
echo(" UPS-MIB ");

echo("Caching OIDs: ");
$ups_array = array();
echo("upsInput ");
$ups_array = snmpwalk_cache_multi_oid($device, "upsInput", $ups_array, "UPS-MIB", mib_dirs());
echo("upsOutput ");
$ups_array = snmpwalk_cache_multi_oid($device, "upsOutput", $ups_array, "UPS-MIB", mib_dirs());
echo("upsBypass ");
$ups_array = snmpwalk_cache_multi_oid($device, "upsBypass", $ups_array, "UPS-MIB", mib_dirs());

$scale = 0.1;
foreach (array_slice(array_keys($ups_array), 1) as $phase)
{
  # Input
  $index = $ups_array[$phase]['upsInputLineIndex'];

  # Workaround if no upsInputLineIndex
  if ($index == '') { $index = $phase; }

  $descr = "Input"; if ($ups_array[0]['upsInputNumLines'] > 1) { $descr .= " Phase $index"; }

  ## Input voltage
  # FIXME maybe use upsConfigLowVoltageTransferPoint and upsConfigHighVoltageTransferPoint as limits? (upsConfig table)
  if (isset($ups_array[$phase]['upsInputVoltage']))
  {
    $oid   = ".1.3.6.1.2.1.33.1.3.3.1.3.$index"; # UPS-MIB:upsInputVoltage.$index
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsInputEntry.".$index, 'ups-mib', $descr, 1, $ups_array[$phase]['upsInputVoltage']);
  }

  ## Input frequency
  if (isset($ups_array[$phase]['upsInputFrequency']))
  {
    $oid   = ".1.3.6.1.2.1.33.1.3.3.1.2.$index"; # UPS-MIB:upsInputFrequency.$index
    discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsInputEntry.".$index, 'ups-mib', $descr, $scale, $ups_array[$phase]['upsInputFrequency']);
  }

  ## Input current
  if (isset($ups_array[$phase]['upsInputCurrent']))
  {
    $oid   = ".1.3.6.1.2.1.33.1.3.3.1.4.$index"; # UPS-MIB:upsInputCurrent.$index
    discover_sensor($valid['sensor'], 'current', $device, $oid, "upsInputEntry.".$index, 'ups-mib', $descr, $scale, $ups_array[$phase]['upsInputCurrent']);
  }

  ## Input power
  if (isset($ups_array[$phase]['upsInputTruePower']))
  {
    $oid   = ".1.3.6.1.2.1.33.1.3.3.1.5.$index"; # UPS-MIB:upsInputTruePower.$index
    discover_sensor($valid['sensor'], 'power', $device, $oid, "upsInputEntry.".$index, 'ups-mib', $descr, $scale, $ups_array[$phase]['upsInputTruePower']);
  }

  # Output
  $index = $ups_array[$phase]['upsOutputLineIndex'];

  # Workaround if no upsOutputLineIndex
  if ($index == '') { $index = $phase; }

  $descr = "Output"; if ($ups_array[0]['upsOutputNumLines'] > 1) { $descr .= " Phase $index"; }

  ## Output voltage
  if (isset($ups_array[$phase]['upsOutputVoltage']))
  {
    $oid   = ".1.3.6.1.2.1.33.1.4.4.1.2.$index"; # UPS-MIB:upsOutputVoltage.$index
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsOutputEntry.".$index, 'ups-mib', $descr, 1, $ups_array[$phase]['upsOutputVoltage']);
  }

  ## Output current
  if (isset($ups_array[$phase]['upsOutputCurrent']))
  {
    $oid   = ".1.3.6.1.2.1.33.1.4.4.1.3.$index"; # UPS-MIB:upsOutputCurrent.$index
    discover_sensor($valid['sensor'], 'current', $device, $oid, "upsOutputEntry.".$index, 'ups-mib', $descr, $scale, $ups_array[$phase]['upsOutputCurrent']);
  }

  ## Output power
  if (isset($ups_array[$phase]['upsOutputPower']))
  {
    $oid   = ".1.3.6.1.2.1.33.1.4.4.1.4.$index"; # UPS-MIB:upsOutputPower.$index
    //discover_sensor($valid['sensor'], 'apower', $device, $oid, "upsOutputEntry.".$index, 'ups-mib', $descr, 1, $ups_array[$phase]['upsOutputPower']);
    discover_sensor($valid['sensor'], 'power', $device, $oid, "upsOutputEntry.".$index, 'ups-mib', $descr, 1, $ups_array[$phase]['upsOutputPower']);
  }

  if (isset($ups_array[$phase]['upsOutputPower']))
  {
    $oid   = ".1.3.6.1.2.1.33.1.4.4.1.5.$index"; # UPS-MIB:upsOutputPercentLoad.$index
    discover_sensor($valid['sensor'], 'capacity', $device, $oid, "upsOutputPercentLoad.$index", 'ups-mib', $descr, 1, $ups_array[$phase]['upsOutputPower']);
  }

  # Bypass

  if ($ups_array[0]['upsBypassNumLines'] > 0)
  {
    $descr = "Bypass"; if ($ups_array[0]['upsBypassNumLines'] > 1) { $descr .= " Phase $index"; }

    ## Bypass voltage
    if (isset($ups_array[$phase]['upsBypassVoltage']))
    {
      $oid   = ".1.3.6.1.2.1.33.1.5.3.1.2.$index"; # UPS-MIB:upsBypassVoltage.$index
      discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsBypassEntry.".$index, 'ups-mib', $descr, 1, $ups_array[$phase]['upsBypassVoltage']);
    }

    ## Bypass current
    if (isset($ups_array[$phase]['upsBypassCurrent']))
    {
      $oid   = ".1.3.6.1.2.1.33.1.5.3.1.3.$index"; # UPS-MIB:upsBypassCurrent.$index
      discover_sensor($valid['sensor'], 'current', $device, $oid, "upsBypassEntry.".$index, 'ups-mib', $descr, $scale, $ups_array[$phase]['upsBypassCurrent']);
    }

    ## Bypass power
    if (isset($ups_array[$phase]['upsBypassPower']))
    {
      $oid   = ".1.3.6.1.2.1.33.1.5.3.1.4.$index"; # UPS-MIB:upsBypassPower.$index
      discover_sensor($valid['sensor'], 'power', $device, $oid, "upsBypassEntry.".$index, 'ups-mib', $descr, 1, $ups_array[$phase]['upsBypassPower']);
    }
  }
}

if (isset($ups_array[0]['upsOutputSource']))
{
  $descr = "Source of Output Power";
  $oid   = ".1.3.6.1.2.1.33.1.4.1.0";
  $value  = $ups_array[0]['upsOutputSource'];

  discover_status($device, $oid, "upsOutputSource.0", 'ups-mib-output-state', $descr, $value, array('entPhysicalClass' => 'other'));
}

$ups_array = snmpwalk_cache_multi_oid($device, "upsBattery", array(), "UPS-MIB", mib_dirs());

if (isset($ups_array[0]['upsBatteryTemperature']) && $ups_array[0]['upsBatteryTemperature'] != 0) // Battery won't be freezing, 0 means no sensor.
{
  $oid = ".1.3.6.1.2.1.33.1.2.7.0"; # UPS-MIB:upsBatteryTemperature.0

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "upsBatteryTemperature", 'ups-mib', "Battery", 1, $ups_array[0]['upsBatteryTemperature']);
}

if (isset($ups_array[0]['upsBatteryCurrent']))
{
  $oid = ".1.3.6.1.2.1.33.1.2.6.0"; # UPS-MIB:upsBatteryCurrent.0

  discover_sensor($valid['sensor'], 'current', $device, $oid, "upsBatteryCurrent", 'ups-mib', "Battery", $scale, $ups_array[0]['upsBatteryCurrent']);
}

if (isset($ups_array[0]['upsBatteryVoltage']))
{
  $oid = ".1.3.6.1.2.1.33.1.2.5.0"; # UPS-MIB:upsBatteryVoltage.0

  discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsBatteryVoltage", 'ups-mib', "Battery", $scale, $ups_array[0]['upsBatteryVoltage']);
}

if (isset($ups_array[0]['upsBatteryStatus']))
{
  $descr = "Battery Status";
  $oid   = ".1.3.6.1.2.1.33.1.2.1.0";
  $value  = $ups_array[0]['upsBatteryStatus'];

  discover_status($device, $oid, "upsBatteryStatus.0", 'ups-mib-battery-state', $descr, $value, array('entPhysicalClass' => 'other'));
}

if (isset($ups_array[0]['upsEstimatedMinutesRemaining']))
{
  $descr  = "Battery Runtime Remaining";
  $oid    = ".1.3.6.1.2.1.33.1.2.3.0";
  $limits = array('limit_low' => snmp_get($device, "upsConfigLowBattTime.0", "-Oqv", "UPS-MIB", mib_dirs(), mib_dirs()));
  $value  = $ups_array[0]['upsEstimatedMinutesRemaining'];

  // FIXME, why mge? seems as copy-paste
  discover_sensor($valid['sensor'], 'runtime', $device, $oid, "upsEstimatedMinutesRemaining.0", 'mge', $descr, 1, $value, $limits);
}

if (isset($ups_array[0]['upsEstimatedChargeRemaining']))
{
  $descr = "Battery Charge Remaining";
  $oid   = ".1.3.6.1.2.1.33.1.2.4.0";
  $value  = $ups_array[0]['upsEstimatedChargeRemaining'];

  discover_sensor($valid['sensor'], 'capacity', $device, $oid, "upsEstimatedChargeRemaining.0", 'ups-mib', $descr, 1, $value);
}

## Output Frequency
$oid   = ".1.3.6.1.2.1.33.1.4.2.0"; # UPS-MIB:upsOutputFrequency.0
$value = snmp_get($device, $oid, "-Oqv");
if (is_numeric($value))
{
  discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsOutputFrequency", 'ups-mib', "Output", $scale, $value);
}

## Bypass Frequency
$oid   = ".1.3.6.1.2.1.33.1.5.1.0"; # UPS-MIB:upsBypassFrequency.0
$value = snmp_get($device, $oid, "-Oqv");
if (is_numeric($value))
{
  discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsBypassFrequency", 'ups-mib', "Bypass", $scale, $value);
}

//UPS-MIB::upsTestId.0 = OID: UPS-MIB::upsTestNoTestsInitiated
//UPS-MIB::upsTestSpinLock.0 = INTEGER: 1
//UPS-MIB::upsTestResultsSummary.0 = INTEGER: noTestsInitiated(6)
//UPS-MIB::upsTestResultsDetail.0 = STRING: No test initiated.
//UPS-MIB::upsTestStartTime.0 = Timeticks: (0) 0:00:00.00
//UPS-MIB::upsTestElapsedTime.0 = INTEGER: 0
$ups_array = snmpwalk_cache_multi_oid($device, "upsTest", array(), "UPS-MIB", mib_dirs());
if (isset($ups_array[0]['upsTestResultsSummary']) && $ups_array[0]['upsTestResultsSummary'] != 'noTestsInitiated')
{
  $descr = "Diagnostics Results";
  $oid   = ".1.3.6.1.2.1.33.1.7.3.0";
  $value  = $ups_array[0]['upsTestResultsSummary'];
  $test_starttime = timeticks_to_sec($ups_array[0]['upsTestStartTime']);
  if ($test_starttime)
  {
    $test_sysUpime = timeticks_to_sec(snmp_get($device, "sysUpTime.0", "-OQUs", "SNMPv2-MIB", mib_dirs()));
    if ($test_sysUpime)
    {
      $test_starttime = time() + $test_starttime - $test_sysUpime; // Unixtime of start test
      $descr .= ' (last ' . format_unixtime($test_starttime) . ')';
    }
  }

  discover_status($device, $oid, "upsTestResultsSummary.0", 'ups-mib-test-state', $descr, $value, array('entPhysicalClass' => 'other'));
}

unset($ups_array);

// EOF
