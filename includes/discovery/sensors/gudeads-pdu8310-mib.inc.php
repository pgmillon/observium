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

echo(" GUDEADS-PDU8310-MIB ");

$channels = snmp_get($device, "pdu8310ActivePowerChan.0","-Oqv", "GUDEADS-PDU8310-MIB", mib_dirs('gude'));

for ($index = 1;$index <= $channels;$index++)
{
  // GUDEADS-PDU8310-MIB::pdu8310Voltage.1 = Gauge32: 230 V

  $oid   = ".1.3.6.1.4.1.28507.27.1.5.1.2.1.6.$index";
  $descr = "Output";
  $value = snmp_get($device, "pdu8310Voltage.$index","-Oqv", "GUDEADS-PDU8310-MIB", mib_dirs('gude'));

  if ($value != '' && $value != -9999)
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "pdu8310Voltage.$index", 'pdu8310', $descr, 1, $value);
  }

  // GUDEADS-PDU8310-MIB::pdu8310PowerActive.1 = Gauge32: 39 W

  $oid   = ".1.3.6.1.4.1.28507.27.1.5.1.2.1.4.$index";
  $descr = "Output";
  $value = snmp_get($device, "pdu8310PowerActive.$index","-Oqv", "GUDEADS-PDU8310-MIB", mib_dirs('gude'));

  if ($value != '' && $value != -9999)
  {
    discover_sensor($valid['sensor'], 'power', $device, $oid, "pdu8310PowerActive.$index", 'pdu8310', $descr, 1, $value);
  }

  // GUDEADS-PDU8310-MIB::pdu8310PowerApparent.1 = Gauge32: 77 VA

  $oid   = ".1.3.6.1.4.1.28507.27.1.5.1.2.1.10.$index";
  $descr = "Output";
  $value = snmp_get($device, "pdu8310PowerApparent.$index","-Oqv", "GUDEADS-PDU8310-MIB", mib_dirs('gude'));

  if ($value != '' && $value != -9999)
  {
    discover_sensor($valid['sensor'], 'apower', $device, $oid, "pdu8310PowerApparent.$index", 'pdu8310', $descr, 1, $value);
  }

  // GUDEADS-PDU8310-MIB::pdu8310Frequency.1 = Gauge32: 4997 0.01 hz

  $oid   = ".1.3.6.1.4.1.28507.27.1.5.1.2.1.7.$index";
  $descr = "Output";
  $value = snmp_get($device, "pdu8310Frequency.$index","-Oqv", "GUDEADS-PDU8310-MIB", mib_dirs('gude'));

  $scale = 0.01;
  if ($value != '' && $value != -9999)
  {
    discover_sensor($valid['sensor'], 'frequency', $device, $oid, "pdu8310Frequency.$index", 'pdu8310', $descr, $scale, $value);
  }

  // GUDEADS-PDU8310-MIB::pdu8310Current.1 = Gauge32: 336 mA

  $oid   = ".1.3.6.1.4.1.28507.27.1.5.1.2.1.5.$index";
  $descr = "Output";
  $value = snmp_get($device, "pdu8310Current.$index","-Oqv", "GUDEADS-PDU8310-MIB", mib_dirs('gude'));

  $scale = 0.001;
  if ($value != '' && $value != -9999)
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, "pdu8310Current.$index", 'pdu8310', $descr, $scale, $value);
  }
}

// EOF
