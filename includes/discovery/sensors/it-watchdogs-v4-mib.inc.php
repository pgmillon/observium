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

echo(" IT-WATCHDOGS-V4-MIB ");

//IT-WATCHDOGS-V4-MIB::productTitle.0 = STRING: WatchDog 15
//IT-WATCHDOGS-V4-MIB::productVersion.0 = STRING: 1.5.4
//IT-WATCHDOGS-V4-MIB::productFriendlyName.0 = STRING: ENV5003
//IT-WATCHDOGS-V4-MIB::productMacAddress.0 = Hex-STRING: 00 04 A3 F5 81 8D
//IT-WATCHDOGS-V4-MIB::productUrl.0 = IpAddress: 10.100.0.129
//IT-WATCHDOGS-V4-MIB::deviceCount.0 = INTEGER: 1
//IT-WATCHDOGS-V4-MIB::temperatureUnits.0 = INTEGER: 0
//IT-WATCHDOGS-V4-MIB::internalIndex.1 = INTEGER: 1
//IT-WATCHDOGS-V4-MIB::internalSerial.1 = STRING: 680004A3F5818DC3
//IT-WATCHDOGS-V4-MIB::internalName.1 = STRING: ENV5003
//IT-WATCHDOGS-V4-MIB::internalAvail.1 = Gauge32: 1
//IT-WATCHDOGS-V4-MIB::internalTemp.1 = INTEGER: 781 0.1 Degrees
//IT-WATCHDOGS-V4-MIB::internalHumidity.1 = INTEGER: 25 %
//IT-WATCHDOGS-V4-MIB::internalDewPoint.1 = INTEGER: 394 0.1 Degrees

$temperatureUnits = snmp_get($device, 'temperatureUnits.0', "-Oqv", "IT-WATCHDOGS-V4-MIB", mib_dirs('itwatchdogs'));
$oids = snmpwalk_cache_multi_oid($device, "internalTable", array(), "IT-WATCHDOGS-V4-MIB", mib_dirs('itwatchdogs'));

foreach ($oids as $index => $entry)
{
  $descr = "Sensor ".$entry['internalName'];
  // internalTemp
  $oid   = ".1.3.6.1.4.1.17373.4.1.2.1.5.$index";
  $scale = 0.1;

  // 0 => fahrenheit, 1 => celsius
  switch ($temperatureUnits)
  {
    case 0:
      $options['sensor_unit'] = 'F';
      break;
    case 1:
      $options['sensor_unit'] = 'C';
      break;
  }

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "internalTemp.$index", 'wxgoos', $descr, $scale, $entry['internalTemp'], $options);

  // internalHumidity
  $oid   = ".1.3.6.1.4.1.17373.4.1.2.1.6.$index";
  $scale = 1;
  discover_sensor($valid['sensor'], 'humidity', $device, $oid, "internalHumidity.$index", 'wxgoos', $descr, $scale, $entry['internalHumidity']);
}

// EOF
