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

echo("TIMETRA-CHASSIS-MIB ");

if (!isset($cache_discovery['timetra-chassis-mib']))
{
  $cache_discovery['timetra-chassis-mib'] = snmpwalk_cache_twopart_oid($device, 'tmnxHwTable', NULL, 'TIMETRA-CHASSIS-MIB');
}

foreach ($cache_discovery['timetra-chassis-mib'] as $chassis => $entries)
{
  foreach ($entries as $index => $entry)
  {
    $inventory[$index] = array(
      'entPhysicalDescr'        => $entry['tmnxHwName'],
      'entPhysicalClass'        => $entry['tmnxHwClass'],
      'entPhysicalName'         => $entry['tmnxHwName'],
      'entPhysicalAlias'        => $entry['tmnxHwAlias'],
      'entPhysicalAssetID'      => $entry['tmnxHwAssetID'],
      'entPhysicalIsFRU'        => $entry['tmnxHwIsFRU'],
      'entPhysicalSerialNum'    => $entry['tmnxHwSerialNumber'],
      'entPhysicalContainedIn'  => $entry['tmnxHwContainedIn'],
      'entPhysicalParentRelPos' => $entry['tmnxHwParentRelPos'],
      'entPhysicalMfgName'      => $entry['tmnxHwMfgString'] // 'Alcatel-Lucent'
    );
    if ($entry['tmnxHwContainedIn'] === '0' && $entry['tmnxHwParentRelPos'] == '-1')
    {
      $inventory[$index]['entPhysicalName'] .= ' '.$chassis;
    }
    discover_inventory($valid['inventory'], $device, $index, $inventory[$index], 'timetra-chassis-mib');
  }
}

// EOF
