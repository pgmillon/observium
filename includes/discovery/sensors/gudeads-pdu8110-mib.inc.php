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

echo(" GUDEADS-PDU8110-MIB ");

// GUDEADS-PDU8110-MIB::pdu8110TempSensor.1 = INTEGER: -9999 0.1 degree Celsius
// GUDEADS-PDU8110-MIB::pdu8110TempSensor.2 = INTEGER: -9999 0.1 degree Celsius
// GUDEADS-PDU8110-MIB::pdu8110HygroSensor.1 = INTEGER: -9999 0.1 percent humidity
// GUDEADS-PDU8110-MIB::pdu8110HygroSensor.2 = INTEGER: -9999 0.1 percent humidity

$cache['pdu8110x'] = snmpwalk_cache_multi_oid($device, "pdu8110SensorTable", array(), "GUDEADS-PDU8110-MIB", mib_dirs('gude'));

$scale = 0.1;
foreach ($cache['pdu8110x'] as $index => $entry)
{
  $oid   = ".1.3.6.1.4.1.28507.23.1.6.1.1.2.$index";
  $descr = "Temp Sensor $index";
  $value = $entry['pdu8110TempSensor'];

  if ($value != '' && $value != -9999)
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'pdu8110TempSensor.'.$index, 'pdu8110', $descr, $scale, $value);
  }

  $oid   = ".1.3.6.1.4.1.28507.23.1.6.1.1.3.$index";
  $descr = "Hygro Sensor $index";
  $value = $entry['pdu8110HygroSensor'];

  if ($value != '' && $value != -9999)
  {
    discover_sensor($valid['sensor'], 'humidity', $device, $oid, 'pdu8110HygroSensor.'.$index, 'pdu8110', $descr, $scale, $value);
  }
}

$channels = snmp_get($device, "pdu8110ActivePowerChan.0","-Oqv", "GUDEADS-PDU8110-MIB", mib_dirs('gude'));

$scale = 0.001;
for ($index = 1;$index <= $channels;$index++)
{
  // GUDEADS-PDU8110-MIB::pdu8110Current.1 = Gauge32: 933 mA

  $oid   = ".1.3.6.1.4.1.28507.23.1.5.1.2.1.5.$index";
  $descr = "Output";
  $value = snmp_get($device, "pdu8110Current.$index","-Oqv", "GUDEADS-PDU8110-MIB", mib_dirs('gude'));

  if ($value != '' && $value != -9999)
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, "pdu8110Current.$index", 'pdu8110', $descr, $scale, $value);
  }
}

// EOF
