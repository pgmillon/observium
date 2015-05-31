<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

# FIXME Could perhaps do with a rewrite? with MIB?

echo(" Papouch-SMI ");

$scale = 0.1;

// TME

$descr = snmp_get($device, "1.3.6.1.4.1.18248.1.1.3.0", "-Oqv");
$temperature = snmp_get($device, "1.3.6.1.4.1.18248.1.1.1.0", "-Oqv");

if ($descr != "" && is_numeric($temperature) && $temperature > 0)
{
  $temperature_oid = ".1.3.6.1.4.1.18248.1.1.1.0";
  $descr = trim(str_replace("\"", "", $descr));
  discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, 1, 'papouch-tme', $descr, $scale, $temperature * $scale);
}

// TH2E

$temperature = snmp_get($device, "1.3.6.1.4.1.18248.20.1.2.1.1.2.1", "-Oqv");

if (is_numeric($temperature) && $temperature > 0)
{
  if (snmp_get($device, "1.3.6.1.4.1.18248.20.1.3.1.1.1.1", "-Oqv"))
  {
    $limits = array('limit_low' => snmp_get($device, "1.3.6.1.4.1.18248.20.1.3.1.1.2.1", "-Oqv") * $scale);
    // The MIB is invalid and I can't find the max value in snmpwalk :[ *sigh*
    // Hysteresis parameter value is in SNMPv2-SMI::enterprises.18248.20.1.3.1.1.3.1 = INTEGER: 100
  } else {
    $limits = array();
  }

  $temperature_oid = ".1.3.6.1.4.1.18248.20.1.2.1.1.2.1";
  discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, 1, 'papouch-th2e', "Temperature" , $scale, $temperature * $scale, $limits);
}

$temperature = snmp_get($device, "1.3.6.1.4.1.18248.20.1.2.1.1.2.3", "-Oqv");

if (is_numeric($temperature) && $temperature > 0)
{
  if (snmp_get($device, "1.3.6.1.4.1.18248.20.1.3.1.1.1.3", "-Oqv"))
  {
    $limits = array('limit_low' => snmp_get($device, "1.3.6.1.4.1.18248.20.1.3.1.1.2.3", "-Oqv") * $scale);
    // The MIB is invalid and I can't find the max value in snmpwalk :[ *sigh*
    // Hysteresis parameter value is in SNMPv2-SMI::enterprises.18248.20.1.3.1.1.3.3 = INTEGER: 100
  } else {
    $limits = array();
  }

  $temperature_oid = ".1.3.6.1.4.1.18248.20.1.2.1.1.2.3";
  discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, "3", 'papouch-th2e', "Dew Point" , $scale, $temperature * $scale, $limits);
}

$humidity = snmp_get($device, "1.3.6.1.4.1.18248.20.1.2.1.1.2.1", "-Oqv");

if (is_numeric($humidity) && $humidity > 0)
{
  if (snmp_get($device, "1.3.6.1.4.1.18248.20.1.2.1.1.1.2", "-Oqv"))
  {
    $limits = array('limit_low' => snmp_get($device, "1.3.6.1.4.1.18248.20.1.3.1.1.2.2", "-Oqv") * $scale);
    // The MIB is invalid and I can't find the max value in snmpwalk :[ *sigh*
    // Hysteresis parameter value is in SNMPv2-SMI::enterprises.18248.20.1.3.1.1.3.2 = INTEGER: 100
  } else {
    $limits = array();
  }

  $humidity_oid = ".1.3.6.1.4.1.18248.20.1.2.1.1.2.2";
  discover_sensor($valid['sensor'], 'humidity', $device, $humidity_oid, 1, 'papouch-th2e', "Humidity" , $scale, $humidity * $scale, $limits);
}

// EOF
