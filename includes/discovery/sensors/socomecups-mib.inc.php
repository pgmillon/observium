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

$mib = 'SOCOMECUPS-MIB';
echo(" $mib ");

$scale  = 0.1;
$limits = array('limit_low' => 0);

// Input
$phases = snmp_get($device, "upsInputNumLines.0", "-Oqv", $mib);
$oids   = snmpwalk_cache_oid($device, "upsInputTable", array(), $mib);
foreach ($oids as $index => $entry)
{
  $descr = ( $phases > 1 ) ? "Input Phase $index" : "Input";

  // Current
  $oid   = ".1.3.6.1.4.1.4555.1.1.1.1.3.3.1.3.$index";
  if ($entry['upsInputCurrent'] != -1)
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, "upsInputCurrent.$index", 'netvision', $descr, $scale, $entry['upsInputCurrent'], $limits);
  }

  // Voltage
  $oid   = ".1.3.6.1.4.1.4555.1.1.1.1.3.3.1.2.$index";
  if ($entry['upsInputVoltage'] != -1)
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsInputVoltage.$index", 'netvision', $descr, $scale, $entry['upsInputVoltage']);
  }
}
// Input Frequency
$oid   = ".1.3.6.1.4.1.4555.1.1.1.1.3.2.0";
$descr = "Input";
$value = snmp_get($device, "upsInputFrequency.0", "-Oqv", $mib);

discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsInputFrequency.0", 'netvision', $descr, $scale, $value);

// Output
$phases = snmp_get($device, "upsOutputNumLines.0", "-Oqv", $mib);
$oids   = snmpwalk_cache_oid($device, "upsOutputTable", array(), $mib);
foreach ($oids as $index => $entry)
{
  $descr = ( $phases > 1 ) ? "Output Phase $index" : "Output";

  // Current
  $oid   = ".1.3.6.1.4.1.4555.1.1.1.1.4.4.1.3.$index";
  if ($entry['upsOutputCurrent'] != -1)
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, "upsOutputCurrent.$index", 'netvision', $descr, $scale, $entry['upsOutputCurrent'], $limits);
  }

  // Voltage
  $oid   = ".1.3.6.1.4.1.4555.1.1.1.1.4.4.1.2.$index";
  if ($entry['upsOutputVoltage'] != -1)
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsOutputVoltage.$index", 'netvision', $descr, $scale, $entry['upsOutputVoltage']);
  }

  // Load
  $oid   = ".1.3.6.1.4.1.4555.1.1.1.1.4.4.1.4.$index";
  if ($entry['upsOutputPercentLoad'] != -1)
  {
    discover_sensor($valid['sensor'], 'capacity', $device, $oid, "upsOutputPercentLoad.$index", 'netvision', "$descr Load", 1, $entry['upsOutputPercentLoad'], array('limit_high' => 90));
  }
}
// Output Frequency
$oid   = ".1.3.6.1.4.1.4555.1.1.1.1.4.2.0";
$descr = "Output";
$value = snmp_get($device, "upsOutputFrequency.0", "-Oqv", $mib);

discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsOutputFrequency.0", 'netvision', $descr, $scale, $value);

// Bypass
$phases = snmp_get($device, "upsBypassNumLines.0", "-Oqv", $mib);
$oids   = snmpwalk_cache_oid($device, "upsBypassTable", array(), $mib);
foreach ($oids as $index => $entry)
{
  $descr = ( $phases > 1 ) ? "Bypass Phase $index" : "Bypass";

  // Current
  $oid   = ".1.3.6.1.4.1.4555.1.1.1.1.5.3.1.3.$index";
  if ($entry['upsBypassCurrent'] != -1)
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, "upsBypassCurrent.$index", 'netvision', $descr, $scale, $entry['upsBypassCurrent'], $limits);
  }

  // Voltage
  $oid   = ".1.3.6.1.4.1.4555.1.1.1.1.5.3.1.2.$index";
  if ($entry['upsBypassVoltage'] != -1)
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, " .$index", 'netvision', $descr, $scale, $entry['upsBypassVoltage']);
  }
}
// Bypass Frequency
$oid   = ".1.3.6.1.4.1.4555.1.1.1.1.5.1.0";
$descr = "Bypass";
$value = snmp_get($device, "upsBypassFrequency.0", "-Oqv", $mib);
discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsBypassFrequency.0", 'netvision', $descr, $scale, $value);

// Battery
$oids  = snmp_get_multi($device, 'upsEstimatedMinutesRemaining.0 upsEstimatedChargeRemaining.0 upsBatteryVoltage.0 upsBatteryTemperature.0', '-OQUs', $mib);
$entry = $oids[0];

$oid   = ".1.3.6.1.4.1.4555.1.1.1.1.2.3.0";
$descr = "Battery Runtime Remaining";
discover_sensor($valid['sensor'], 'runtime', $device, $oid, "upsEstimatedMinutesRemaining.0", 'netvision', $descr, 1, $entry['upsEstimatedMinutesRemaining']);

$oid   = ".1.3.6.1.4.1.4555.1.1.1.1.2.4.0";
$descr = "Battery Charge Remaining";
discover_sensor($valid['sensor'], 'capacity', $device, $oid, "upsEstimatedChargeRemaining.0", 'netvision', $descr, 1, $entry['upsEstimatedChargeRemaining']);

$oid   = ".1.3.6.1.4.1.4555.1.1.1.1.2.5.0";
$descr = "Battery";

discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsBatteryVoltage.0", 'netvision', $descr, $scale, $entry['upsBatteryVoltage']);

$oid   = ".1.3.6.1.4.1.4555.1.1.1.1.2.6.0";
$descr = "Battery";
discover_sensor($valid['sensor'], 'temperature', $device, $oid, "upsBatteryTemperature.0", 'netvision', $descr, 1, $entry['upsBatteryTemperature']);

// EOF
