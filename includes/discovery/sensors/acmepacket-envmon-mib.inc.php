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

echo(" ACMEPACKET-ENVMON-MIB ");

// Temperatures:
echo(" Temp ");

$oids = array();
$oids = snmpwalk_cache_multi_oid($device, "apEnvMonTemperatureStatusValue", $oids, "ACMEPACKET-ENVMON-MIB", mib_dirs('acme'));

foreach ($oids as $index => $entry)
{
  $descr = trim(snmp_get($device, "apEnvMonTemperatureStatusDescr.$index", "-Oqv", "ACMEPACKET-ENVMON-MIB", mib_dirs('acme')),'"');

  // remove some information from the temerature sensor description (including misspelling)
  $descr = preg_replace('/ \(degrees Cel[cs]ius\)/', '', $descr);
  $descr = preg_replace('/ Temperature/', '', $descr);
  $oid   = ".1.3.6.1.4.1.9148.3.3.1.3.1.1.4.$index";
  $value = $entry['apEnvMonTemperatureStatusValue'];

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'acme-env', $descr, 1, $value);
  }
}

// Voltage
echo(" Voltage ");

$oids = array();
$oids = snmpwalk_cache_multi_oid($device, "apEnvMonVoltageStatusValue", $oids, "ACMEPACKET-ENVMON-MIB", mib_dirs('acme'));

$scale = si_to_scale('milli');
foreach ($oids as $index => $entry)
{
  $descr = trim(snmp_get($device, "apEnvMonVoltageStatusDescr.$index", "-Oqv", "ACMEPACKET-ENVMON-MIB", mib_dirs('acme')),'"');

  // remove some information from the voltage description
  $descr = preg_replace('/ \(millivolts\)/', '', $descr);
  $oid   = ".1.3.6.1.4.1.9148.3.3.1.2.1.1.4.$index";
  $value = $entry['apEnvMonVoltageStatusValue'] * $scale;

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'acme-env', $descr, $scale, $value);
  }
}

// FAN:
echo(" Fan ");

$oids = array();
$oids = snmpwalk_cache_multi_oid($device, "apEnvMonFanState", $oids, "ACMEPACKET-ENVMON-MIB", mib_dirs('acme'));

foreach ($oids as $index => $entry)
{
  $descr = trim(snmp_get($device, "apEnvMonFanStatusDescr.$index", "-Oqv", "ACMEPACKET-ENVMON-MIB", mib_dirs('acme')),'"');

  // remove some information from the voltage description
  $descr = preg_replace('/ [Ss]peed/', '', $descr);
  $oid   = ".1.3.6.1.4.1.9148.3.3.1.4.1.1.5.$index";
  $value = state_string_to_numeric('acme-env-state', $entry['apEnvMonFanState']);

  discover_sensor($valid['sensor'], 'state', $device, $oid, "apEnvMonFanState.$index", 'acme-env-state', $descr, NULL, $value, array('entPhysicalClass' => 'fan'));
}

// Power
echo(" Power ");

$oids = array();
$oids = snmpwalk_cache_multi_oid($device, "apEnvMonPowerSupplyState", $oids, "ACMEPACKET-ENVMON-MIB", mib_dirs('acme'));

foreach ($oids as $index => $entry)
{
  $descr = trim(snmp_get($device, "apEnvMonPowerSupplyStatusDescr.$index", "-Oqv", "ACMEPACKET-ENVMON-MIB", mib_dirs('acme')),'"');
  $oid   = ".1.3.6.1.4.1.9148.3.3.1.5.1.1.4.$index";
  $value = state_string_to_numeric('acme-env-state', $entry['apEnvMonPowerSupplyState']);

  discover_sensor($valid['sensor'], 'state', $device, $oid, "apEnvMonPowerSupplyState.$index", 'acme-env-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));
}

// EOF
