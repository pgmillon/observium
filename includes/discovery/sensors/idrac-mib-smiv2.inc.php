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

/**
IDRAC-MIB-SMIv2::coolingDevicechassisIndex.1.1 = INTEGER: 1
IDRAC-MIB-SMIv2::coolingDeviceIndex.1.1 = INTEGER: 1
IDRAC-MIB-SMIv2::coolingDeviceStateCapabilities.1.1 = INTEGER: 0
IDRAC-MIB-SMIv2::coolingDeviceStateSettings.1.1 = INTEGER: enabled(2)
IDRAC-MIB-SMIv2::coolingDeviceStatus.1.1 = INTEGER: ok(3)
IDRAC-MIB-SMIv2::coolingDeviceReading.1.1 = INTEGER: 2880
IDRAC-MIB-SMIv2::coolingDeviceType.1.1 = INTEGER: coolingDeviceTypeIsAFan(3)
IDRAC-MIB-SMIv2::coolingDeviceLocationName.1.1 = STRING: "System Board Fan1 RPM"
IDRAC-MIB-SMIv2::coolingDeviceLowerNonCriticalThreshold.1.1 = INTEGER: 840
IDRAC-MIB-SMIv2::coolingDeviceLowerCriticalThreshold.1.1 = INTEGER: 600
IDRAC-MIB-SMIv2::coolingDevicecoolingUnitIndexReference.1.1 = INTEGER: 1
IDRAC-MIB-SMIv2::coolingDeviceSubType.1.1 = INTEGER: coolingDeviceSubTypeIsAPowerSupplyFanThatReadsinRPM(5)
IDRAC-MIB-SMIv2::coolingDeviceProbeCapabilities.1.1 = INTEGER: 0
IDRAC-MIB-SMIv2::coolingDeviceFQDD.1.1 = STRING: "Fan.Embedded.1"
**/

echo(" IDRAC-MIB-SMIv2 ");

$oids = array ('coolingDeviceStatus', 'coolingDeviceReading', 'coolingDeviceLocationName', 'coolingDeviceLowerNonCriticalThreshold', 'coolingDeviceLowerCriticalThreshold');

$data = array();
foreach ($oids as $oid)
{
  $data = snmpwalk_cache_twopart_oid($device, $oid, $data, 'IDRAC-MIB-SMIv2', mib_dirs('dell'));
}

foreach ($data as $index_a => $entries)
{
  foreach ($entries as $index_b => $entry)
  {

    $index = $index_a.".".$index_b;
    $descr = $entry['coolingDeviceLocationName'];

    // Add the numerical sensor
    if (isset($entry['coolingDeviceReading']))
    {
      $oid = ".1.3.6.1.4.1.674.10892.5.4.700.12.1.6.".$index;
      $options = array();

      if (isset($entry['coolingDeviceLowerNonCriticalThreshold'])) { $options['warn_low']   = $entry['coolingDeviceLowerNonCriticalThreshold']; }
      if (isset($entry['coolingDeviceLowerCriticalThreshold']))    { $options['limit_low']  = $entry['coolingDeviceLowerCriticalThreshold']; }
      $options['warn_high']   = NULL;
      $options['limig_high']  = NULL;

      discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'coolingDeviceReading', $descr, 1, $entry['coolingDeviceReading'], $options);
    }

    // Add the status indicator

    if (isset($entry['coolingDeviceStatus']))
    {
      $options = array();
      $oid   = ".1.3.6.1.4.1.674.10892.5.4.700.12.1.5.".$index;
    }

  }
}

/**

IDRAC-MIB-SMIv2::temperatureProbechassisIndex.1.1 = INTEGER: 1
IDRAC-MIB-SMIv2::temperatureProbeIndex.1.1 = INTEGER: 1
IDRAC-MIB-SMIv2::temperatureProbeStateCapabilities.1.1 = INTEGER: 0
IDRAC-MIB-SMIv2::temperatureProbeStateSettings.1.1 = INTEGER: enabled(2)
IDRAC-MIB-SMIv2::temperatureProbeStatus.1.1 = INTEGER: ok(3)
IDRAC-MIB-SMIv2::temperatureProbeReading.1.1 = INTEGER: 250
IDRAC-MIB-SMIv2::temperatureProbeType.1.1 = INTEGER: temperatureProbeTypeIsAmbientESM(3)
IDRAC-MIB-SMIv2::temperatureProbeLocationName.1.1 = STRING: "System Board Inlet Temp"
IDRAC-MIB-SMIv2::temperatureProbeUpperCriticalThreshold.1.1 = INTEGER: 470
IDRAC-MIB-SMIv2::temperatureProbeUpperNonCriticalThreshold.1.1 = INTEGER: 420
IDRAC-MIB-SMIv2::temperatureProbeLowerNonCriticalThreshold.1.1 = INTEGER: 30
IDRAC-MIB-SMIv2::temperatureProbeLowerCriticalThreshold.1.1 = INTEGER: -70
IDRAC-MIB-SMIv2::temperatureProbeProbeCapabilities.1.1 = INTEGER: 15

**/

$oids = array ('temperatureProbeStatus', 'temperatureProbeReading', 'temperatureProbeLocationName', 'temperatureProbeUpperCriticalThreshold', 'temperatureProbeUpperNonCriticalThreshold', 'temperatureProbeLowerNonCriticalThreshold', 'temperatureProbeLowerCriticalThreshold');

$data = array();
foreach ($oids as $oid)
{
  $data = snmpwalk_cache_twopart_oid($device, $oid, $data, 'IDRAC-MIB-SMIv2', mib_dirs('dell'));
}

foreach ($data as $index_a => $entries)
{
  foreach ($entries as $index_b => $entry)
  {

    $index = $index_a.".".$index_b;
    $descr = $entry['temperatureProbeLocationName'];

    if (isset($entry['temperatureProbeReading']))
    {
      $oid = ".1.3.6.1.4.1.674.10892.5.4.700.20.1.6.".$index;
      $options = array();

      if (isset($entry['temperatureProbeLowerNonCriticalThreshold'])) { $options['warn_low']   = $entry['temperatureProbeLowerNonCriticalThreshold']; }
      if (isset($entry['temperatureProbeLowerCriticalThreshold']))    { $options['limit_low']  = $entry['temperatureProbeLowerCriticalThreshold']; }
      if (isset($entry['temperatureProbeUpperNonCriticalThreshold'])) { $options['warn_high']  = $entry['temperatureProbeUpperNonCriticalThreshold']; }
      if (isset($entry['temperatureProbeUpperCriticalThreshold']))    { $options['limit_high'] = $entry['temperatureProbeUpperCriticalThreshold']; }

      discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'temperatureProbeReading', $descr, 0.1, $entry['temperatureProbeReading'], $options);
    }

  }
}

/**

IDRAC-MIB::voltageProbechassisIndex.1.3 = INTEGER: 1
IDRAC-MIB::voltageProbeIndex.1.3 = INTEGER: 3
IDRAC-MIB::voltageProbeStateCapabilities.1.3 = INTEGER: 0
IDRAC-MIB::voltageProbeStateSettings.1.3 = INTEGER: enabled(2)
IDRAC-MIB::voltageProbeStatus.1.3 = INTEGER: ok(3)
IDRAC-MIB::voltageProbeType.1.3 = INTEGER: voltageProbeTypeIsDiscrete(16)
IDRAC-MIB::voltageProbeLocationName.1.3 = STRING: "System Board 3.3V PG"
IDRAC-MIB::voltageProbeProbeCapabilities.1.3 = INTEGER: 0
IDRAC-MIB::voltageProbeDiscreteReading.1.3 = INTEGER: voltageIsGood(1)

**/

$oids = array ('voltageProbeStatus', 'voltageProbeReading', 'temperatureProbeLocationName', 'temperatureProbeUpperCriticalThreshold', 'temperatureProbeUpperNonCriticalThreshold', 'temperatureProbeLowerNonCriticalThreshold', 'temperatureProbeLowerCriticalThreshold');

$data = array();
foreach ($oids as $oid)
{
  $data = snmpwalk_cache_twopart_oid($device, $oid, $data, 'IDRAC-MIB-SMIv2', mib_dirs('dell'));
}

foreach ($data as $index_a => $entries)
{
  foreach ($entries as $index_b => $entry)
  {

    $index = $index_a.".".$index_b;
    $descr = $entry['voltageProbeLocationName'];

  }
}

// EOF
