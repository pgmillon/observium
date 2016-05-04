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

/* Detection for JDSU OEM Erbium Dotted Fibre Amplifiers */

echo(" NSCRTV-ROOT ");
/// FIXME rewrite

// Voltage Sensors
$oids = snmp_walk($device, "oaDCPowerName", "-OsqnU", "NSCRTV-ROOT");

$scale = 0.1;

foreach (explode("\n", $oids) as $data)
{
  $data = trim($data);
  if ($data)
  {
    list($oid,$descr) = explode(" ", $data,2);
    $split_oid = explode('.',$oid);
    $index = $split_oid[count($split_oid)-1];
    $oid  = ".1.3.6.1.4.1.17409.1.11.7.1.2." . $index;
    $value = snmp_get($device, $oid, "-Oqv", "NSCRTV-ROOT");
    $limits = array();
    if ($descr == '+5v')
    {
      $limits = array('limit_high' => 5.3, 'limit_low' => 4.8, 'limit_high_warn' => 5.2, 'limit_low_warn' => 4.9);
    } elseif ($descr == '-5v') {
      $limits = array('limit_high' => -4.8, 'limit_low' => -5.3, 'limit_high_warn' => -4.9, 'limit_low_warn' => -5.2);
    }

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'jdsu-edfa-power', $descr, $scale, $value, $limits);
    }
  }
}

// Pump Sensors
$oids = snmp_walk($device, "oaPumpBIAS", "-OsqnU", "NSCRTV-ROOT");

$scale = 0.001;

foreach (explode("\n", $oids) as $data)
{
  $data = trim($data);
  if ($data)
  {
    list($oid,$descr) = explode(" ", $data,2);
    $split_oid = explode('.',$oid);
    $index = $split_oid[count($split_oid)-1];
    $oid  = ".1.3.6.1.4.1.17409.1.11.4.1.2." . $index;
    $value = snmp_get($device, $oid, "-Oqv", "NSCRTV-ROOT");

    if (is_numeric($value) && $value != 0)
    {
      $descr = "Pump Bias $index";
      discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'jdsu-edfa-pump-bias', $descr, $scale, $value);
    }
  }
}

$oids = snmp_walk($device, "oaPumpTEC", "-OsqnU", "NSCRTV-ROOT");

$scale = 0.01;

foreach (explode("\n", $oids) as $data)
{
  $data = trim($data);
  if ($data)
  {
    list($oid,$descr) = explode(" ", $data,2);
    $split_oid = explode('.',$oid);
    $index = $split_oid[count($split_oid)-1];
    $oid  = ".1.3.6.1.4.1.17409.1.11.4.1.3." . $index;
    $value = snmp_get($device, $oid, "-Oqv", "NSCRTV-ROOT");

    if (is_numeric($value) && $value != 0)
    {
      $descr = "Pump TEC $index";
      discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'jdsu-edfa-pump-tec', $descr, $scale, $value);
    }
  }
}

$oids = snmp_walk($device, "oaPumpTemp", "-OsqnU", "NSCRTV-ROOT");

$scale = 0.1;

foreach (explode("\n", $oids) as $data)
{
  $data = trim($data);
  if ($data)
  {
    list($oid,$descr) = explode(" ", $data,2);
    $split_oid = explode('.',$oid);
    $index = $split_oid[count($split_oid)-1];
    $oid  = ".1.3.6.1.4.1.17409.1.11.4.1.4." . $index;
    $value = snmp_get($device, $oid, "-Oqv", "NSCRTV-ROOT");

    if (is_numeric($value) && $value != 0)
    {
      $descr = "Pump Temperature $index";
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'jdsu-edfa-pump-temp', $descr, $scale, $value);
    }
  }
}

// dBm sensors
$oid   = ".1.3.6.1.4.1.17409.1.11.2.0";
$value = snmp_get($device, $oid, "-Oqv", "NSCRTV-ROOT");

if (is_numeric($value))
{
  $limits = array('limit_high' => 16, 'limit_low' => 9, 'limit_high_warn' => 15, 'limit_low_warn' => 10);
  discover_sensor($valid['sensor'], 'dbm', $device, $oid, 0, 'jdsu-edfa-tx', 'Optical Output Power', $scale, $value, $limits);
}

$oid   = ".1.3.6.1.4.1.17409.1.11.3.0";
$value = snmp_get($device, $oid, "-Oqv", "NSCRTV-ROOT");

if (is_numeric($value))
{
  $limits = array('limit_high' => -9, 'limit_low' => -18, 'limit_high_warn' => -10, 'limit_low_warn' => -14);
  discover_sensor($valid['sensor'], 'dbm', $device, $oid, 0, 'jdsu-edfa-rx', 'Optical Input Power', $scale, $value, $limits);
}

// Temperature sensors
$oid   = ".1.3.6.1.4.1.17409.1.3.1.13.0";
$value = snmp_get($device, $oid, "-Oqv", "NSCRTV-ROOT");

if (is_numeric($value))
{
  $limits = array('limit_high' => 50, 'limit_low' => 5, 'limit_high_warn' => 40, 'limit_low_warn' => 10);
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, 0, 'jdsu-edfa-temp', 'Environment Temperature', 1, $value, $limits);
}

// EOF
