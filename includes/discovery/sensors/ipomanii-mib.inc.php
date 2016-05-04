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

// IPOMANII-MIB

// FIXME: Currently no EMD "stack" support

echo(" IPOMANII-MIB ");

echo("outletConfigDesc ");
$cache['ipoman']['out'] = snmpwalk_cache_multi_oid($device, "outletConfigDesc", $cache['ipoman']['out'], "IPOMANII-MIB");
echo("outletConfigLocation ");
$cache['ipoman']['out'] = snmpwalk_cache_multi_oid($device, "outletConfigLocation", $cache['ipoman']['out'], "IPOMANII-MIB");
echo("inletConfigDesc ");
$cache['ipoman']['in'] = snmpwalk_cache_multi_oid($device, "inletConfigDesc", $cache['ipoman'], "IPOMANII-MIB");

$oids_in = array();
$oids_out = array();

echo("inletConfigCurrentHigh ");
$oids_in = snmpwalk_cache_multi_oid($device, "inletConfigCurrentHigh", $oids_in, "IPOMANII-MIB");
echo("inletStatusCurrent ");
$oids_in = snmpwalk_cache_multi_oid($device, "inletStatusCurrent", $oids_in, "IPOMANII-MIB");
echo("outletConfigCurrentHigh ");
$oids_out = snmpwalk_cache_multi_oid($device, "outletConfigCurrentHigh", $oids_out, "IPOMANII-MIB");
echo("outletStatusCurrent ");
$oids_out = snmpwalk_cache_multi_oid($device, "outletStatusCurrent", $oids_out, "IPOMANII-MIB");

$scale = 0.001;
foreach ($oids_in as $index => $entry)
{
  $descr  = (trim($cache['ipoman']['in'][$index]['inletConfigDesc'],'"') != '' ? trim($cache['ipoman']['in'][$index]['inletConfigDesc'],'"') : "Inlet $index");
  $oid    = ".1.3.6.1.4.1.2468.1.4.2.1.3.1.3.1.3.$index";
  $value  = $entry['inletStatusCurrent'];
  $limits = array('limit_high' => $entry['inletConfigCurrentHigh'] / 10);

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, '1.3.1.3.'.$index, 'ipoman', $descr, $scale, $value, $limits);
  }
  // FIXME: iPoMan 1201 also says it has 2 inlets, at least until firmware 1.06 - wtf?
}

foreach ($oids_out as $index => $entry)
{
  $descr  = (trim($cache['ipoman']['out'][$index]['outletConfigDesc'],'"') != '' ? trim($cache['ipoman']['out'][$index]['outletConfigDesc'],'"') : "Output $index");
  $oid    = ".1.3.6.1.4.1.2468.1.4.2.1.3.2.3.1.3.$index";
  $value  = $entry['outletStatusCurrent'];
  $limits = array('limit_high' => $entry['outletConfigCurrentHigh'] / 10);

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, '2.3.1.3.'.$index, 'ipoman', $descr, $scale, $value, $limits);
  }
}

$oids = array();

echo("inletConfigFrequencyHigh ");
$oids = snmpwalk_cache_multi_oid($device, "inletConfigFrequencyHigh", $oids, "IPOMANII-MIB");
echo("inletConfigFrequencyLow ");
$oids = snmpwalk_cache_multi_oid($device, "inletConfigFrequencyLow", $oids, "IPOMANII-MIB");
echo("inletStatusFrequency ");
$oids = snmpwalk_cache_multi_oid($device, "inletStatusFrequency", $oids, "IPOMANII-MIB");

$scale = 0.1;
foreach ($oids as $index => $entry)
{
  $descr  = (trim($cache['ipoman']['in'][$index]['inletConfigDesc'],'"') != '' ? trim($cache['ipoman']['in'][$index]['inletConfigDesc'],'"') : "Inlet $index");
  $oid    = ".1.3.6.1.4.1.2468.1.4.2.1.3.1.3.1.4.$index";
  $value  = $entry['inletStatusFrequency'];
  $limits = array(
    'limit_high' => ($entry['inletConfigFrequencyHigh'] != 0 ? $entry['inletConfigFrequencyHigh'] : NULL),
    'limit_low'  => ($entry['inletConfigFrequencyLow'] != 0 ? $entry['inletConfigFrequencyLow'] : NULL)
  );

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, 'ipoman', $descr, $scale, $value, $limits);
  }
  // FIXME: iPoMan 1201 also says it has 2 inlets, at least until firmware 1.06 - wtf?
}

// FIXME: What to do with IPOMANII-MIB::ipmEnvEmdConfigHumiOffset.0 ?

$emd_installed = snmp_get($device, "IPOMANII-MIB::ipmEnvEmdStatusEmdType.0"," -Oqv");
$scale = 0.1;
if ($emd_installed == 'eMD-HT')
{
  $descr  = snmp_get($device, "IPOMANII-MIB::ipmEnvEmdConfigHumiName.0", "-Oqv");
  $oid    = ".1.3.6.1.4.1.2468.1.4.2.1.5.1.1.3.0";
  $value  = snmp_get($device, "IPOMANII-MIB::ipmEnvEmdStatusHumidity.0", "-Oqv");
  $limits = array('limit_high' => snmp_get($device, "IPOMANII-MIB::ipmEnvEmdConfigHumiHighSetPoint.0", "-Oqv"),
                  'limit_low'  => snmp_get($device, "IPOMANII-MIB::ipmEnvEmdConfigHumiLowSetPoint.0", "-Oqv"));

  if ($descr != "" && is_numeric($value) && $value > "0")
  {
    $descr = trim(str_replace("\"", "", $descr));

    discover_sensor($valid['sensor'], 'humidity', $device, $oid, "1", 'ipoman', $descr, $scale, $value, $limits);
  }
}

if ($emd_installed != 'disabled')
{
  $descr  = snmp_get($device, "IPOMANII-MIB::ipmEnvEmdConfigTempName.0", "-Oqv");
  $oid    = ".1.3.6.1.4.1.2468.1.4.2.1.5.1.1.2.0";
  $value  = snmp_get($device, "IPOMANII-MIB::ipmEnvEmdStatusTemperature.0", "-Oqv");
  $limits = array('limit_high' => snmp_get($device, "IPOMANII-MIB::ipmEnvEmdConfigTempHighSetPoint.0", "-Oqv"),
                  'limit_low'  => snmp_get($device, "IPOMANII-MIB::ipmEnvEmdConfigTempLowSetPoint.0", "-Oqv"));

  if ($descr != "" && is_numeric($value) && $value > "0")
  {
    $descr = trim(str_replace("\"", "", $descr));

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "1", 'ipoman', $descr, $scale, $value, $limits);
  }
}

// Inlet Disabled due to the fact thats it's Kwh instead of just Watt

#  $oids_in = array();
$oids_out = array();

#  echo("inletStatusWH ");
#  $oids_in = snmpwalk_cache_multi_oid($device, "inletStatusWH", $oids_in, "IPOMANII-MIB");
echo("outletStatusWH ");
$oids_out = snmpwalk_cache_multi_oid($device, "outletStatusWH", $oids_out, "IPOMANII-MIB");

#  foreach ($oids_in as $index => $entry)
#  {
#    $descr = (trim($cache['ipoman']['in'][$index]['inletConfigDesc'],'"') != '' ? trim($cache['ipoman']['in'][$index]['inletConfigDesc'],'"') : "Inlet $index");
#    $oid   = ".1.3.6.1.4.1.2468.1.4.2.1.3.1.3.1.5.$index";
#    $value = $entry['inletStatusWH'];
#
#    discover_sensor($valid['sensor'], 'power', $device, $oid, '1.3.1.3.'.$index, 'ipoman', $descr, $scale, $value);
#    // FIXME: iPoMan 1201 also says it has 2 inlets, at least until firmware 1.06 - wtf?
#  }

$scale = 0.1;
foreach ($oids_out as $index => $entry)
{
  $descr = (trim($cache['ipoman']['out'][$index]['outletConfigDesc'],'"') != '' ? trim($cache['ipoman']['out'][$index]['outletConfigDesc'],'"') : "Output $index");
  $oid   = ".1.3.6.1.4.1.2468.1.4.2.1.3.2.3.1.5.$index";
  $value = $entry['outletStatusWH'];

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'power', $device, $oid, '2.3.1.3.'.$index, 'ipoman', $descr, $scale, $value);
  }
}

$oids = array();

echo("inletConfigVoltageHigh ");
$oids = snmpwalk_cache_multi_oid($device, "inletConfigVoltageHigh", $oids, "IPOMANII-MIB");
echo("inletConfigVoltageLow ");
$oids = snmpwalk_cache_multi_oid($device, "inletConfigVoltageLow", $oids, "IPOMANII-MIB");
echo("inletStatusVoltage ");
$oids = snmpwalk_cache_multi_oid($device, "inletStatusVoltage", $oids, "IPOMANII-MIB");

$scale = 0.1;
foreach ($oids as $index => $entry)
{
  $descr  = (trim($cache['ipoman']['in'][$index]['inletConfigDesc'],'"') != '' ? trim($cache['ipoman']['in'][$index]['inletConfigDesc'],'"') : "Inlet $index");
  $oid    = ".1.3.6.1.4.1.2468.1.4.2.1.3.1.3.1.2.$index";
  $value  = $entry['inletStatusVoltage'];
  $limits = array('limit_high' => $entry['inletConfigVoltageHigh'], 'limit_low' => $entry['inletConfigVoltageLow']);

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'ipoman', $descr, $scale, $value, $limits);
  }
  // FIXME: iPoMan 1201 also says it has 2 inlets, at least until firmware 1.06 - wtf?
}

// EOF
