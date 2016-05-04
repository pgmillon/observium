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

echo(" HH3C-ENTITY-EXT-MIB ");

$oids = array('h3cEntityExtStateTable', 'entPhysicalName');
$entity_array = array();
foreach ($oids as $oid)
{
  $entity_array = snmpwalk_cache_multi_oid($device, $oid, $entity_array, 'ENTITY-MIB:HH3C-ENTITY-EXT-MIB', mib_dirs('hh3c'));
}

foreach ($entity_array as $index => $entry)
{
  // HH3C-ENTITY-EXT-MIB::hh3cEntityExtTemperature.8 = INTEGER: 50
  // HH3C-ENTITY-EXT-MIB::hh3cEntityExtTemperatureThreshold.8 = INTEGER: 85
  // HH3C-ENTITY-EXT-MIB::hh3cEntityExtCriticalTemperatureThreshold.8 = INTEGER: 95
  // HH3C-ENTITY-EXT-MIB::hh3cEntityExtLowerTemperatureThreshold.8 = INTEGER: -30
  // HH3C-ENTITY-EXT-MIB::hh3cEntityExtShutdownTemperatureThreshold.8 = INTEGER: 65535
  if ($entry['hh3cEntityExtTemperature'] != 0 &&
      $entry['hh3cEntityExtTemperatureThreshold']         != 65535 &&
      $entry['hh3cEntityExtLowerTemperatureThreshold']    != 65535)
  {
    $limits['limit_low']       = $entry['hh3cEntityExtLowerTemperatureThreshold'];
    $limits['limit_high_warn'] = $entry['hh3cEntityExtTemperatureThreshold'];
    if (($entry['hh3cEntityExtCriticalTemperatureThreshold'] != 65535 && $entry['hh3cEntityExtCriticalTemperatureThreshold'] >= $entry['hh3cEntityExtTemperatureThreshold']))
    {
      $limits['limit_high']    = $entry['hh3cEntityExtCriticalTemperatureThreshold'];
    } else {
      $limits['limit_high']    = $entry['hh3cEntityExtTemperatureThreshold'] + 10;
    }

    $value = $entry['hh3cEntityExtTemperature'];
    $oid   = ".1.3.6.1.4.1.25506.2.6.1.1.1.1.12.$index";
    $descr = $entry['entPhysicalName'];

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "hh3cEntityExtTemperature.$index", 'hh3c-entity-ext-mib', $descr, 1, $value, $limits);
  }

  // HH3C-ENTITY-EXT-MIB::hh3cEntityExtVoltage.1 = INTEGER: 0
  // HH3C-ENTITY-EXT-MIB::hh3cEntityExtVoltageLowThreshold.1 = INTEGER: 0
  // HH3C-ENTITY-EXT-MIB::hh3cEntityExtVoltageHighThreshold.1 = INTEGER: 0

  if ($entry['hh3cEntityExtVoltage'] != 0)
  {
    $limits['limit_low']  = $entry['hh3cEntityExtVoltageLowThreshold'];
    $limits['limit_high'] = $entry['hh3cEntityExtVoltageHighThreshold'];

    $value = $entry['hh3cEntityExtVoltage'];
    $oid   = ".1.3.6.1.4.1.25506.2.6.1.1.1.1.14.$index";
    $descr = $entry['entPhysicalName'];

    // FIXME scale is unknown, and not documented in the MIB; probably not 1?? My V1910 doesn't have voltage sensors.
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "hh3cEntityExtVoltage.$index", 'hh3c-entity-ext-mib', $descr, 1, $value, $limits);
  }

  //  [hh3cEntityExtErrorStatus] => normal
}

unset($oids, $index, $value, $descr);

// EOF
