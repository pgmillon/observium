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

echo(" POSEIDON-MIB ");

/**
POSEIDON-MIB::sensName.1 = STRING: "Humidity indoor"
POSEIDON-MIB::sensName.2 = STRING: "Prague Outdoor"
POSEIDON-MIB::sensName.3 = STRING: "Prague Window"
POSEIDON-MIB::sensName.4 = STRING: "Prague Outdoor"
POSEIDON-MIB::sensName.5 = STRING: "Temp rack2"
POSEIDON-MIB::sensName.6 = STRING: "Batt voltage"
POSEIDON-MIB::sensName.7 = STRING: "HWg Indoor RH"
POSEIDON-MIB::sensName.8 = STRING: "HWg Indoor DP"
POSEIDON-MIB::sensName.9 = STRING: "HWg Indoor P"
POSEIDON-MIB::sensName.10 = STRING: "Sens-UI Voltage"
POSEIDON-MIB::sensName.11 = STRING: "Sens-UI Current"
POSEIDON-MIB::sensState.1 = INTEGER: normal(1)
POSEIDON-MIB::sensState.2 = INTEGER: normal(1)
POSEIDON-MIB::sensState.3 = INTEGER: alarmstate(2)
POSEIDON-MIB::sensState.4 = INTEGER: normal(1)
POSEIDON-MIB::sensState.5 = INTEGER: alarmstate(2)
POSEIDON-MIB::sensState.6 = INTEGER: alarmstate(2)
POSEIDON-MIB::sensState.7 = INTEGER: normal(1)
POSEIDON-MIB::sensState.8 = INTEGER: normal(1)
POSEIDON-MIB::sensState.9 = INTEGER: normal(1)
POSEIDON-MIB::sensState.10 = INTEGER: normal(1)
POSEIDON-MIB::sensState.11 = INTEGER: normal(1)
POSEIDON-MIB::sensString.1 = STRING: "38.0 %RH"
POSEIDON-MIB::sensString.2 = STRING: "38.6 %RH"
POSEIDON-MIB::sensString.3 = STRING: "31.2 C"
POSEIDON-MIB::sensString.4 = STRING: "18.3 C"
POSEIDON-MIB::sensString.5 = STRING: "26.9 C"
POSEIDON-MIB::sensString.6 = STRING: "27.5 C"
POSEIDON-MIB::sensString.7 = STRING: "30.6 %RH"
POSEIDON-MIB::sensString.8 = STRING: "8.7 DP C"
POSEIDON-MIB::sensString.9 = STRING: "99.2 kPa"
POSEIDON-MIB::sensString.10 = STRING: "5.2 V"
POSEIDON-MIB::sensString.11 = STRING: "4.1 mA"
POSEIDON-MIB::sensValue.1 = INTEGER: 380
POSEIDON-MIB::sensValue.2 = INTEGER: 386
POSEIDON-MIB::sensValue.3 = INTEGER: 312
POSEIDON-MIB::sensValue.4 = INTEGER: 183
POSEIDON-MIB::sensValue.5 = INTEGER: 269
POSEIDON-MIB::sensValue.6 = INTEGER: 275
POSEIDON-MIB::sensValue.7 = INTEGER: 306
POSEIDON-MIB::sensValue.8 = INTEGER: 87
POSEIDON-MIB::sensValue.9 = INTEGER: 992
POSEIDON-MIB::sensValue.10 = INTEGER: 52
POSEIDON-MIB::sensValue.11 = INTEGER: 41
POSEIDON-MIB::sensUnit.1 = INTEGER: percent(3)
POSEIDON-MIB::sensUnit.2 = INTEGER: percent(3)
POSEIDON-MIB::sensUnit.3 = INTEGER: celsius(0)
POSEIDON-MIB::sensUnit.4 = INTEGER: celsius(0)
POSEIDON-MIB::sensUnit.5 = INTEGER: celsius(0)
POSEIDON-MIB::sensUnit.6 = INTEGER: celsius(0)
POSEIDON-MIB::sensUnit.7 = INTEGER: percent(3)
POSEIDON-MIB::sensUnit.8 = INTEGER: dewPoint(9)
POSEIDON-MIB::sensUnit.9 = INTEGER: pressure(11)
POSEIDON-MIB::sensUnit.10 = INTEGER: volt(4)
POSEIDON-MIB::sensUnit.11 = INTEGER: miliAmper(5)
POSEIDON-MIB::sensUnitString.1 = STRING: "%RH"
POSEIDON-MIB::sensUnitString.2 = STRING: "%RH"
POSEIDON-MIB::sensUnitString.3 = STRING: "C"
POSEIDON-MIB::sensUnitString.4 = STRING: "C"
POSEIDON-MIB::sensUnitString.5 = STRING: "C"
POSEIDON-MIB::sensUnitString.6 = STRING: "C"
POSEIDON-MIB::sensUnitString.7 = STRING: "%RH"
POSEIDON-MIB::sensUnitString.8 = STRING: "DP C"
POSEIDON-MIB::sensUnitString.9 = STRING: "kPa"
POSEIDON-MIB::sensUnitString.10 = STRING: "V"
POSEIDON-MIB::sensUnitString.11 = STRING: "mA"
 */

/** Limits
POSEIDON-MIB::sensLimitMin.1 = INTEGER: 100
POSEIDON-MIB::sensLimitMin.2 = INTEGER: 200
POSEIDON-MIB::sensLimitMin.3 = INTEGER: -50
POSEIDON-MIB::sensLimitMin.4 = INTEGER: -150
POSEIDON-MIB::sensLimitMin.5 = INTEGER: 0
POSEIDON-MIB::sensLimitMin.6 = INTEGER: 0
POSEIDON-MIB::sensLimitMin.7 = INTEGER: 150
POSEIDON-MIB::sensLimitMin.8 = INTEGER: 0
POSEIDON-MIB::sensLimitMin.9 = INTEGER: 0
POSEIDON-MIB::sensLimitMin.10 = INTEGER: 40
POSEIDON-MIB::sensLimitMin.11 = INTEGER: 0
POSEIDON-MIB::sensLimitMax.1 = INTEGER: 600
POSEIDON-MIB::sensLimitMax.2 = INTEGER: 950
POSEIDON-MIB::sensLimitMax.3 = INTEGER: 250
POSEIDON-MIB::sensLimitMax.4 = INTEGER: 400
POSEIDON-MIB::sensLimitMax.5 = INTEGER: 250
POSEIDON-MIB::sensLimitMax.6 = INTEGER: 250
POSEIDON-MIB::sensLimitMax.7 = INTEGER: 800
POSEIDON-MIB::sensLimitMax.8 = INTEGER: 100
POSEIDON-MIB::sensLimitMax.9 = INTEGER: 1010
POSEIDON-MIB::sensLimitMax.10 = INTEGER: 60
POSEIDON-MIB::sensLimitMax.11 = INTEGER: 200
POSEIDON-MIB::sensHysteresis.1 = INTEGER: 0
POSEIDON-MIB::sensHysteresis.2 = INTEGER: 0
POSEIDON-MIB::sensHysteresis.3 = INTEGER: 0
POSEIDON-MIB::sensHysteresis.4 = INTEGER: 0
POSEIDON-MIB::sensHysteresis.5 = INTEGER: 0
POSEIDON-MIB::sensHysteresis.6 = INTEGER: 0
POSEIDON-MIB::sensHysteresis.7 = INTEGER: 0
POSEIDON-MIB::sensHysteresis.8 = INTEGER: 0
POSEIDON-MIB::sensHysteresis.9 = INTEGER: 0
POSEIDON-MIB::sensHysteresis.10 = INTEGER: 0
POSEIDON-MIB::sensHysteresis.11 = INTEGER: 0
 */

$oids = snmpwalk_cache_multi_oid($device, "sensTable",  array(), "POSEIDON-MIB");
$oids = snmpwalk_cache_multi_oid($device, "sensLimitMin", $oids, "POSEIDON-MIB");
$oids = snmpwalk_cache_multi_oid($device, "sensLimitMax", $oids, "POSEIDON-MIB");

foreach ($oids as $index => $entry)
{
  $oid   = ".1.3.6.1.4.1.21796.3.3.3.1.6.$index";
  $descr = $entry['sensName'];
  $value = $entry['sensValue'];
  $scale = 0.1;

  // sensUnit: celsius (0), fahrenheit (1), kelvin (2), percent(3), volt (4), miliAmper (5), noUnit (6),
  //           pulse (7), switch (8), dewPoint (9), absoluteHumidity (10), pressure (11), universal (12)
  switch ($entry['sensUnit'])
  {
    case 'celsius':
      $type = 'temperature';
      break;
    case 'percent':
      $type = 'humidity';
      break;
    case 'volt':
      $type = 'voltage';
      break;
    case 'miliAmper':
      $type = 'current';
      $scale = 0.0001;
      break;
    //case 'pressure':
    //  $type = 'pressure';
    //  $scale = 100;
    //  break;
    default:
      continue 2; // continue foreach loop
  }

  if (is_numeric($value) && $entry['sensState'] != 'invalid')
  {
    if (is_numeric($entry['sensLimitMax']) && is_numeric($entry['sensLimitMin']))
    {
      $limits = array('limit_high' => $entry['sensLimitMax'] * $scale,
                      'limit_low'  => $entry['sensLimitMin'] * $scale);
    } else {
      $limits = array();
    }
    discover_sensor($valid['sensor'], $type, $device, $oid, "poseidonSensor.$index", 'poseidon', $descr, $scale, $value, $limits);
  }
}

// EOF
