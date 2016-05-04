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

echo(" STE-MIB ");

/**
  STE-MIB::sensIndex.1 = INTEGER: 26518
  STE-MIB::sensIndex.2 = INTEGER: 29068
  STE-MIB::sensName.1 = STRING: "Sensor 26518"
  STE-MIB::sensName.2 = STRING: "Sensor 29068"
  STE-MIB::sensState.1 = INTEGER: normal(1)
  STE-MIB::sensState.2 = INTEGER: alarmlo(4)
  STE-MIB::sensString.1 = STRING: "19.6"
  STE-MIB::sensString.2 = STRING: "26.1"
  STE-MIB::sensValue.1 = INTEGER: 196
  STE-MIB::sensValue.2 = INTEGER: 261
  STE-MIB::sensSN.1 = STRING: "289667F30100000F"
  STE-MIB::sensSN.2 = STRING: "268C71130100004D"
  STE-MIB::sensUnit.1 = INTEGER: celsius(1)
  STE-MIB::sensUnit.2 = INTEGER: percent(4)
  STE-MIB::sensID.1 = INTEGER: 26518
  STE-MIB::sensID.2 = INTEGER: 29068
 */

$oids = snmpwalk_cache_multi_oid($device, "sensTable", array(), "STE-MIB");

foreach ($oids as $index => $entry)
{
  $oid   = ".1.3.6.1.4.1.21796.4.1.3.1.5.$index";
  $descr = $entry['sensName'];
  $value = $entry['sensValue'];
  $scale = 0.1;

  // sensUnit: none (0), celsius (1), fahrenheit (2), kelvin (3), percent(4)
  switch ($entry['sensUnit'])
  {
    case 'celsius':
      $type = 'temperature';
      break;
    case 'percent':
      $type = 'humidity';
      break;
    default:
      continue 2; // continue foreach loop
  }

  if (is_numeric($value) && $entry['sensState'] != 'invalid')
  {
    discover_sensor($valid['sensor'], $type, $device, $oid, "steSensor.$index", 'ste', $descr, $scale, $value);
  }
}

// EOF
