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

echo(" SYNOLOGY-DISK-MIB ");

// SYNOLOGY-DISK-MIB::diskID.0 = STRING: "Disk 1"
// SYNOLOGY-DISK-MIB::diskID.1 = STRING: "Disk 2"
// SYNOLOGY-DISK-MIB::diskModel.0 = STRING: "WD30EFRX-68EUZN0        "
// SYNOLOGY-DISK-MIB::diskModel.1 = STRING: "WD30EFRX-68EUZN0        "
// SYNOLOGY-DISK-MIB::diskStatus.0 = INTEGER: 1
// SYNOLOGY-DISK-MIB::diskStatus.1 = INTEGER: 1
// SYNOLOGY-DISK-MIB::diskTemperature.0 = INTEGER: 30
// SYNOLOGY-DISK-MIB::diskTemperature.1 = INTEGER: 30

$cache['synology']['diskTable'] = snmpwalk_cache_multi_oid($device, "diskTable", array(), "SYNOLOGY-DISK-MIB", mib_dirs('synology'));

foreach ($cache['synology']['diskTable'] as $index => $entry)
{
  $descr = $entry['diskID'] . ": " . trim($entry['diskModel']);

  $oid   = ".1.3.6.1.4.1.6574.2.1.1.6.$index";
  $value = $entry['diskTemperature'];

  if ($value != '')
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'diskTemperature.'.$index, 'synology-disk-mib', $descr, 1, $value);
  }

  $oid   = ".1.3.6.1.4.1.6574.2.1.1.5.$index";
  $value = $entry['diskStatus'];

  if ($value != '')
  {
    discover_sensor($valid['sensor'], 'state', $device, $oid, 'diskStatus.'.$index, 'synology-disk-state', $descr, NULL, $value, array('entPhysicalClass' => 'storage'));
  }
}

// EOF
