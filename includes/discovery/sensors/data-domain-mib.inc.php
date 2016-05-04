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

$mib = 'DATA-DOMAIN-MIB';
echo(" $mib ");

$cache['datadomain']['power'] = snmpwalk_cache_oid($device, 'powerModuleTable', array(), $mib);
$cache['datadomain']['temp']  = snmpwalk_cache_oid($device, 'temperatureSensorTable', array(), $mib);
$cache['datadomain']['fan']   = snmpwalk_cache_oid($device, 'fanPropertiesTable', array(), $mib);
$cache['datadomain']['disk']  = snmpwalk_cache_oid($device, 'diskPropertiesTable', array(), $mib);

foreach ($cache['datadomain']['power'] as $index => $entry)
{
  if ($encl == '') { $encl = 'Enclosure'; }
  $descr = $encl.':'.$entry['powerEnclosureID'].' - '.$entry['powerModuleDescription'];
  $oid   = '.1.3.6.1.4.1.19746.1.1.1.1.1.1.4.'.$index;
  $value = $entry['powerModuleStatus'];

  if ($value !== '')
  {
    discover_sensor($valid['sensor'], 'state', $device, $oid, 'powerModuleStatus.'.$index, 'data-domain-mib-pwr-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));
  }
}

foreach ($cache['datadomain']['temp'] as $index => $entry)
{
  if ($encl == '') { $encl = 'Enclosure'; }
  $descr = $encl.':'.$entry['tempEnclosureID'].' - '.$entry['tempSensorDescription'];
  $oid   = '.1.3.6.1.4.1.19746.1.1.2.1.1.1.5.'.$index;
  $value = $entry['tempSensorCurrentValue'];

  if ($value > 0)
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'data-domain-mib', $descr, 1, $value);
  }
}

foreach ($cache['datadomain']['fan'] as $index => $entry)
{
  if ($encl == '') { $encl = 'Enclosure'; }
  $descr = $encl.':'.$entry['fanEnclosureID'].' - '.$entry['fanDescription'];
  $oid   = '.1.3.6.1.4.1.19746.1.1.3.1.1.1.6.'.$index;
  $value = $entry['fanStatus'];

  if ($value !== '')
  {
    discover_sensor($valid['sensor'], 'state', $device, $oid, 'fanStatus.'.$index, 'data-domain-mib-fan-state', $descr, NULL, $value, array('entPhysicalClass' => 'fan'));
  }
}

foreach ($cache['datadomain']['disk'] as $index => $entry)
{
  if ($encl == '') { $encl = 'Enclosure'; }
  $descr = $encl.':'.$entry['diskPropEnclosureID'].' - Disk '.$entry['diskPropIndex'].': '.$entry['diskModel'];
  $oid   = '.1.3.6.1.4.1.19746.1.6.1.1.1.8.'.$index;
  $value = $entry['diskPropState'];

  if ($value !== '')
  {
    discover_sensor($valid['sensor'], 'state', $device, $oid, 'diskPropState.'.$index, 'data-domain-mib-disk-state', $descr, NULL, $value, array('entPhysicalClass' => 'storage'));
  }
}

// EOF
