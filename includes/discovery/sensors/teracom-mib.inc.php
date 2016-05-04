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

echo(" TERACOM-MIB ");

$scale = 1;

// Tested with TCW240B. And yes the Teracom MIBs are stupid

// 1-Wire sensors
for ($i = 1;$i <= 8;$i++)
{
  $data = snmp_get_multi($device, 's'.$i.'description.0 s'.$i.'ID.0 s'.$i.'1x10Int.0 s'.$i.'2x10Int.0 s'.$i.'1MINx10Int.0 s'.$i.'1MAXx10Int.0', '-OQUs', 'TERACOM-MIB', mib_dirs('teracom'));
  if ($data[0]['s'.$i.'ID'] == 'ff:ff:ff:ff:ff:ff') { continue; }
  $descr = $data[0]['s'.$i.'description'];
  $oid   = ".1.3.6.1.4.1.38783.1.3.1.1.$i.0";
  $value = $data[0]['s'.$i.'1x10Int'];
  // TODO figure out how to identify sensor types
  $type = 'temperature';
  $scale = 0.1;
  $limits = array('limit_low' => $data[0]['s'.$i.'1MINx10Int'] * $scale,
                  'limit_high' => $data[0]['s'.$i.'1MAXx10Int'] * $scale);
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, 's'.$i.'1x10Int.0', 'teracom', $descr, $scale, $value, $limits);
}

// Analog inputs
for ($i = 1;$i <= 4;$i++)
{
  $data  = snmp_get_multi($device, 'voltage'.$i.'description.0 voltage'.$i.'x10Int.0 voltage'.$i.'min.0 voltage'.$i.'max.0', '-OQUs', 'TERACOM-MIB', mib_dirs('teracom'));
  $descr = $data[0]['voltage'.$i.'description'];
  switch(substr($descr,0,2))
  {
    case 'I ':
      $type = 'current';
      $descr = substr($descr,2);
      break;
    case 'F ':
      $type = 'frequency';
      $descr = substr($descr,2);
      break;
    case 'H ':
      $type = 'humidity';
      $descr = substr($descr,2);
      break;
    default:
      $type = 'voltage';
  }
  $oid   = ".1.3.6.1.4.1.38783.1.3.2.$i.0";
  $value = $data[0]['voltage'.$i.'x10Int'];
  $scale = 0.1;
  $limits = array('limit_low' => $data[0]['voltage'.$i.'min'] * $scale,
                  'limit_high' => $data[0]['voltage'.$i.'max'] * $scale);
  discover_sensor($valid['sensor'], $type, $device, $oid, 'voltage'.$i.'x10Int.0', 'teracom', $descr, $scale, $value, $limits);
}

// Digital inputs
for ($i = 1;$i <= 4;$i++)
{
  $data = snmp_get_multi($device, 'digitalInput'.$i.'description.0 digitalInput'.$i.'State.0', '-OQUs', 'TERACOM-MIB', mib_dirs('teracom'));
  $descr = $data[0]['digitalInput'.$i.'description'];
  $oid   = ".1.3.6.1.4.1.38783.1.3.3.$i.0";
  $value = $data[0]['digitalInput'.$i.'State'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'digitalInput'.$i.'State.0', 'teracom-digitalin-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

// Relay outputs
for ($i = 1;$i <= 4;$i++)
{
  $data = snmp_get_multi($device, 'relay'.$i.'description.0 relay'.$i.'State.0', '-OQUs', 'TERACOM-MIB', mib_dirs('teracom'));
  $descr = $data[0]['relay'.$i.'description'];
  $oid   = ".1.3.6.1.4.1.38783.1.3.4.$i.1.0";
  $value = $data[0]['relay'.$i.'State'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'relay'.$i.'State.0', 'teracom-relay-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

// Status
$value = snmp_get($device, "hardwareErr.0", "-Oqv", "TERACOM-MIB", mib_dirs('teracom'));
if ($value !== '')
{
  $descr = "Status";
  $oid   = ".1.3.6.1.4.1.38783.1.3.8.0";
  discover_sensor($valid['sensor'], 'state', $device, $oid, "hardwareErr.0", 'teracom-alarm-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

unset($data, $oid, $descr, $limits, $value);

// EOF
