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

echo(" READYDATAOS-MIB ");

/**
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskNumber.1 = INTEGER: 1
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskNumber.2 = INTEGER: 2
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskNumber.3 = INTEGER: 3
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskID.1 = STRING: "sdc"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskID.2 = STRING: "sdb"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskID.3 = STRING: "sda"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskSlotName.1 = STRING: "1x1"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskSlotName.2 = STRING: "2x1"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskSlotName.3 = STRING: "3x1"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskSerial.1 = STRING: "WD-WMAZA2971349"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskSerial.2 = STRING: "WD-WMAZA2953392"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskSerial.3 = STRING: "WD-WCAZA2984027"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskModel.1 = STRING: "WDC WD20EARS-00MVWB0"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskModel.2 = STRING: "WDC WD20EARS-00MVWB0"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskModel.3 = STRING: "WDC WD20EURS-63S48Y0"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.ataError.1 = INTEGER: 0
 enterprises.netgear.ngNasManager.diskTable.diskEntry.ataError.2 = INTEGER: 0
 enterprises.netgear.ngNasManager.diskTable.diskEntry.ataError.3 = INTEGER: 0
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskCapacity.1 = STRING: "2000398934016"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskCapacity.2 = STRING: "2000398934016"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskCapacity.3 = STRING: "2000398934016"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskInterface.1 = STRING: "SATA"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskInterface.2 = STRING: "SATA"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskInterface.3 = STRING: "SATA"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskState.1 = STRING: "ONLINE"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskState.2 = STRING: "ONLINE"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskState.3 = STRING: "ONLINE"
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskTemperature.1 = INTEGER: 43
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskTemperature.2 = INTEGER: 46
 enterprises.netgear.ngNasManager.diskTable.diskEntry.diskTemperature.3 = INTEGER: 44
**/

$cache['readydataos-mib']['diskTable'] = snmpwalk_cache_multi_oid($device, "diskTable", array(), "READYDATAOS-MIB", mib_dirs('netgear'));

foreach ($cache['readydataos-mib']['diskTable'] as $index => $entry)
{
  $descr = $entry['diskID'] . " (".$entry['diskSlotName']."): " . trim($entry['diskModel']);
  $oid   = ".1.3.6.1.4.1.4526.22.3.1.10.".$index;
  $value = $entry['diskTemperature'];

  if ($value != '')
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'diskTemperature.'.$index, 'readydataos-mib_diskTemperature', $descr, 1, $value, array('entPhysicalClass' => 'storage'));
  }

  $oid   = ".1.3.6.1.4.1.4526.22.3.1.9.".$index;
  $value = $entry['diskState'];

  if ($value != '')
  {
    //discover_sensor($valid['sensor'], 'state', $device, $oid, 'diskState.'.$index, 'readydataos-mib_diskState', $descr, NULL, $value, array('entPhysicalClass' => 'storage'));
  }
}

/*

 READYDATAOS-MIB::fanNumber.1 = INTEGER: 1
 READYDATAOS-MIB::fanRPM.1 = INTEGER: 819
 READYDATAOS-MIB::fanStatus.1 = STRING: "ok"
 READYDATAOS-MIB::fanType.1 = STRING: "Fan"
 READYDATAOS-MIB::temperatureNumber.1 = INTEGER: 1
 READYDATAOS-MIB::temperatureValue.1 = INTEGER: 61
 READYDATAOS-MIB::temperatureTyoe.1 = STRING: "cpu"
 READYDATAOS-MIB::temperatureMin.1 = INTEGER: 1
 READYDATAOS-MIB::temperatureMax.1 = INTEGER: 85

*/

$cache['readydataos-mib']['fanTable'] = snmpwalk_cache_multi_oid($device, "fanTable", array(), "READYDATAOS-MIB", mib_dirs('netgear'));

foreach ($cache['readydataos-mib']['fanTable'] as $index => $entry)
{
  $descr = "Fan ". $entry['fanNumber'] . " (".$entry['fanType'].")";
  $oid   = ".1.3.6.1.4.1.4526.22.4.1.2.".$index;
  $value = $entry['fanRPM'];

  if ($value != '')
  {
    discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, 'fanRPM.'.$index, 'readydataos-mib_fanRPM', $descr, 1, $value, array('entPhysicalClass' => 'device'));
  }

  $oid   = ".1.3.6.1.4.1.4526.22.4.1.3.".$index;
  $value = $entry['fanStatus'];

  if ($value != '')
  {
    //discover_sensor($valid['sensor'], 'state', $device, $oid, 'fanStatus.'.$index, 'readydataos-mib_fanStatus', $descr, NULL, $value, array('entPhysicalClass' => 'storage'));
  }

}

$cache['readydataos-mib']['temperatureTable'] = snmpwalk_cache_multi_oid($device, "temperatureTable", array(), "READYDATAOS-MIB", mib_dirs('netgear'));

foreach ($cache['readydataos-mib']['temperatureTable'] as $index => $entry)
{
  $descr = $entry['temperatureTyoe'] . " ".$entry['temperatureNumber'];
  $oid   = ".1.3.6.1.4.1.4526.22.5.1.2.".$index;
  $value = $entry['temperatureValue'];

  if ($value != '')
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'temperatureValue.'.$index, 'readydataos-mib_temperatureValue', $descr, 1, $value, array('entPhysicalClass' => 'device'));
  }

  $oid   = ".1.3.6.1.4.1.4526.22.5.1.3.".$index;
  $value = $entry['fanStatus'];

  if ($value != '')
  {
    //discover_sensor($valid['sensor'], 'state', $device, $oid, 'fanStatus.'.$index, 'readydataos-mib_fanStatus', $descr, NULL, $value, array('entPhysicalClass' => 'storage'));
  }

}

// EOF
