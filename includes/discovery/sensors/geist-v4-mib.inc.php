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

echo(" GEIST-V4-MIB ");

$scale = 0.1;

// GEIST-V4-MIB::productTitle.0 = STRING: GBB15
// GEIST-V4-MIB::productVersion.0 = STRING: 1.5.4
// GEIST-V4-MIB::productFriendlyName.0 = STRING: GBB15
// GEIST-V4-MIB::productMacAddress.0 = Hex-STRING: 00 04 A3 7F 9F 83
// GEIST-V4-MIB::productUrl.0 = IpAddress: 76.79.48.112
// GEIST-V4-MIB::deviceCount.0 = INTEGER: 1
// GEIST-V4-MIB::temperatureUnits.0 = INTEGER: 1
// GEIST-V4-MIB::internalIndex.1 = INTEGER: 1
// GEIST-V4-MIB::internalSerial.1 = STRING: 670004A37F9F83C3
// GEIST-V4-MIB::internalName.1 = STRING: GBB15
// GEIST-V4-MIB::internalAvail.1 = Gauge32: 1
// GEIST-V4-MIB::internalTemp.1 = INTEGER: 262 0.1 Degrees
// GEIST-V4-MIB::internalHumidity.1 = INTEGER: 14 %
// GEIST-V4-MIB::internalDewPoint.1 = INTEGER: -33 0.1 Degrees
// GEIST-V4-MIB::internalIO1.1 = INTEGER: 100
// GEIST-V4-MIB::internalIO2.1 = INTEGER: 100
// GEIST-V4-MIB::internalIO3.1 = INTEGER: 100
// GEIST-V4-MIB::internalIO4.1 = INTEGER: 100
// GEIST-V4-MIB::internalRelayState.1 = Gauge32: 0

$cache['geist']['internalTable'] = snmpwalk_cache_multi_oid($device, "internalTable", array(), "GEIST-V4-MIB", mib_dirs('geist'));

foreach ($cache['geist']['internalTable'] as $index => $entry)
{
  if ($entry['internalAvail'])
  {
    $descr = $entry['internalName'] . " Temperature";

    $oid   = ".1.3.6.1.4.1.21239.5.1.2.1.5.$index";
    $value = $entry['internalTemp'];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'internalTemp.'.$index, 'geist-v4-mib', $descr, $scale, $value);
    }

    $descr = $entry['internalName'] . " Dew Point";
    $oid   = ".1.3.6.1.4.1.21239.5.1.2.1.7.$index";
    $value = $entry['internalDewPoint'];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'internalDewPoint.'.$index, 'geist-v4-mib', $descr, $scale, $value);
    }

    $descr = $entry['internalName'] . " Humidity";
    $oid   = ".1.3.6.1.4.1.21239.5.1.2.1.6.$index";
    $value = $entry['internalHumidity'];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'humidity', $device, $oid, 'internalHumidity.'.$index, 'geist-v4-mib', $descr, 1, $value);
    }

    $descr = $entry['climateName'] . " Analog I/O Sensor 1";
    $oid   = ".1.3.6.1.4.1.21239.5.1.2.1.8.$index";
    $value = $entry['internalIO1'];

    if ($value != '')
    {
      discover_sensor($valid['sensor'], 'state', $device, $oid, 'internalIO1.'.$index, 'geist-v4-mib-io-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
    }

    $descr = $entry['climateName'] . " Analog I/O Sensor 2";
    $oid   = ".1.3.6.1.4.1.21239.5.1.2.1.9.$index";
    $value = $entry['internalIO2'];

    if ($value != '')
    {
      discover_sensor($valid['sensor'], 'state', $device, $oid, 'internalIO2.'.$index, 'geist-v4-mib-io-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
    }

    $descr = $entry['climateName'] . " Analog I/O Sensor 3";
    $oid   = ".1.3.6.1.4.1.21239.5.1.2.1.10.$index";
    $value = $entry['internalIO3'];

    if ($value != '')
    {
      discover_sensor($valid['sensor'], 'state', $device, $oid, 'internalIO3.'.$index, 'geist-v4-mib-io-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
    }

    $descr = $entry['climateName'] . " Analog I/O Sensor 4";
    $oid   = ".1.3.6.1.4.1.21239.5.1.2.1.11.$index";
    $value = $entry['internalIO4'];

    if ($value != '')
    {
      discover_sensor($valid['sensor'], 'state', $device, $oid, 'internalIO4.'.$index, 'geist-v4-mib-io-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
    }
  }
}

// Not supported yet (no test device available):
// - tempSensorTable
// - airFlowSensorTable
// - dewPointSensorTable
// - ccatSensorTable
// - t3hdSensorTable
// - thdSensorTable
// - rpmSensorTable

// EOF
