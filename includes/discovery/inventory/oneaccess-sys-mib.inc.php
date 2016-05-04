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

echo("ONEACCESS-SYS-MIB ");

if (!isset($cache_discovery['oneaccess-sys-mib']))
{
  $cache_discovery['oneaccess-sys-mib'] = snmpwalk_cache_oid($device, 'oacExpIMSysHwComponentsTable', array(), 'ONEACCESS-SYS-MIB', mib_dirs('oneaccess'));
}

foreach ($cache_discovery['oneaccess-sys-mib'] as $index => $entry)
{
  //print_r($entry);
  $index = (int)$entry['oacExpIMSysHwcIndex'] + 1;
  $inventory[$index] = array(
    'entPhysicalDescr'        => $entry['oacExpIMSysHwcDescription'],
    'entPhysicalName'         => $entry['oacExpIMSysHwcProductName'],
    'entPhysicalClass'        => $entry['oacExpIMSysHwcClass'],
    'entPhysicalModelName'    => $entry['oacExpIMSysHwcType'],
    //'entPhysicalAssetID'      => $entry['oacExpIMSysHwcManufacturer'],
    'entPhysicalSerialNum'    => $entry['oacExpIMSysHwcSerialNumber'],
    'entPhysicalIsFRU'        => 'false',
    'entPhysicalMfgName'      => 'OneAccess',
    'entPhysicalContainedIn'  => ($entry['oacExpIMSysHwcIndex'] == 0 ? 0 : 1),
    'entPhysicalParentRelPos' => ($entry['oacExpIMSysHwcIndex'] == 0 ? -1 : (int)$entry['oacExpIMSysHwcIndex']),
  );

  discover_inventory($valid['inventory'], $device, $index, $inventory[$index], 'oneaccess-sys-mib');
}

// EOF
