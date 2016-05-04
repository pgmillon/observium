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

echo(" READYNAS-MIB ");

/**
enterprises.netgear.nasManager.diskTable.diskEntry.diskNumber.1 = INTEGER: 1
enterprises.netgear.nasManager.diskTable.diskEntry.diskNumber.2 = INTEGER: 2
enterprises.netgear.nasManager.diskTable.diskEntry.diskNumber.3 = INTEGER: 3
enterprises.netgear.nasManager.diskTable.diskEntry.diskNumber.4 = INTEGER: 4
enterprises.netgear.nasManager.diskTable.diskEntry.diskChannel.1 = INTEGER: 1
enterprises.netgear.nasManager.diskTable.diskEntry.diskChannel.2 = INTEGER: 2
enterprises.netgear.nasManager.diskTable.diskEntry.diskChannel.3 = INTEGER: 3
enterprises.netgear.nasManager.diskTable.diskEntry.diskChannel.4 = INTEGER: 4
enterprises.netgear.nasManager.diskTable.diskEntry.diskModel.1 = STRING: "Seagate ST31000524AS 931 GB"
enterprises.netgear.nasManager.diskTable.diskEntry.diskModel.2 = STRING: "Seagate ST31000524AS 931 GB"
enterprises.netgear.nasManager.diskTable.diskEntry.diskModel.3 = STRING: "Seagate ST31000524AS 931 GB"
enterprises.netgear.nasManager.diskTable.diskEntry.diskModel.4 = STRING: "Seagate ST31000524AS 931 GB"
enterprises.netgear.nasManager.diskTable.diskEntry.diskState.1 = STRING: "ok"
enterprises.netgear.nasManager.diskTable.diskEntry.diskState.2 = STRING: "ok"
enterprises.netgear.nasManager.diskTable.diskEntry.diskState.3 = STRING: "ok"
enterprises.netgear.nasManager.diskTable.diskEntry.diskState.4 = STRING: "ok"
enterprises.netgear.nasManager.diskTable.diskEntry.diskTemperature.1 = INTEGER: 105
enterprises.netgear.nasManager.diskTable.diskEntry.diskTemperature.2 = INTEGER: 114
enterprises.netgear.nasManager.diskTable.diskEntry.diskTemperature.3 = INTEGER: 113
enterprises.netgear.nasManager.diskTable.diskEntry.diskTemperature.4 = INTEGER: 105
**/

$cache['readynas-mib']['diskTable'] = snmpwalk_cache_multi_oid($device, "diskTable", array(), "READYNAS-MIB", mib_dirs('netgear'));

foreach ($cache['readynas-mib']['diskTable'] as $index => $entry)
{
  $descr = $entry['diskNumber'] . " (".$entry['diskChannel']."): " . trim($entry['diskModel']);
  $oid   = ".1.3.6.1.4.1.4526.18.3.1.5.".$index;
  $value = $entry['diskTemperature'];

  if ($value != '')
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'diskTemperature.'.$index, 'readynas-mib_diskTemperature', $descr, 1, $value, array('entPhysicalClass' => 'storage'));
  }

  $oid   = ".1.3.6.1.4.1.4526.18.3.1.4.".$index;
  $value = $entry['diskState'];

  if ($value != '')
  {
    //discover_sensor($valid['sensor'], 'state', $device, $oid, 'diskState.'.$index, 'readynas-mib_diskState', $descr, NULL, $value, array('entPhysicalClass' => 'storage'));
  }
}

/*
 enterprises.netgear.nasManager.fanTable.fanEntry.fanNumber.1 = INTEGER: 1
 enterprises.netgear.nasManager.fanTable.fanEntry.fanRPM.1 = INTEGER: 2027
 enterprises.netgear.nasManager.fanTable.fanEntry.fanType.1 = STRING: "none"
*/

$cache['readynas-mib']['fanTable'] = snmpwalk_cache_multi_oid($device, "fanTable", array(), "READYNAS-MIB", mib_dirs('netgear'));

foreach ($cache['readynas-mib']['fanTable'] as $index => $entry)
{
  $descr = "Fan ". $entry['fanNumber'] . " (".$entry['fanType'].")";
  $oid   = ".1.3.6.1.4.1.4526.18.4.1.2.".$index;
  $value = $entry['fanRPM'];

  if ($value != '')
  {
    discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, 'fanRPM.'.$index, 'readynas-mib_fanRPM', $descr, 1, $value, array('entPhysicalClass' => 'device'));
  }

  $oid   = ".1.3.6.1.4.1.4526.22.4.1.3.".$index;
  $value = $entry['fanStatus'];

  if ($value != '')
  {
    //discover_sensor($valid['sensor'], 'state', $device, $oid, 'fanStatus.'.$index, 'readynas-mib_fanStatus', $descr, NULL, $value, array('entPhysicalClass' => 'storage'));
  }

}

/*
 enterprises.netgear.nasManager.temperatureTable.temperatureEntry.temperatureNumber.1 = INTEGER: 1
 enterprises.netgear.nasManager.temperatureTable.temperatureEntry.temperatureValue.1 = INTEGER: 98
 enterprises.netgear.nasManager.temperatureTable.temperatureEntry.temperatureStatus.1 = STRING: "ok"
*/

$cache['readynas-mib']['temperatureTable'] = snmpwalk_cache_multi_oid($device, "temperatureTable", array(), "READYNAS-MIB", mib_dirs('netgear'));

foreach ($cache['readynas-mib']['temperatureTable'] as $index => $entry)
{
  $descr = "Temperature ".$entry['temperatureNumber'];
  $oid   = ".1.3.6.1.4.1.4526.18.5.1.2.".$index;
  $value = $entry['temperatureValue'];

  if ($value != '')
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'temperatureValue.'.$index, 'readynas-mib_temperatureValue', $descr, 1, $value, array('entPhysicalClass' => 'device'));
  }

  $oid   = ".1.3.6.1.4.1.4526.22.5.1.3.".$index;
  $value = $entry['fanStatus'];

  if ($value != '')
  {
    //discover_sensor($valid['sensor'], 'state', $device, $oid, 'fanStatus.'.$index, 'readynas-mib_fanStatus', $descr, NULL, $value, array('entPhysicalClass' => 'storage'));
  }

}

// EOF
