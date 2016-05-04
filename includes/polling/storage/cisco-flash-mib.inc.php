<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// CISCO-FLASH-MIB

/* Use per index individual snmpget's, because snmpwalk by this MIB produce high cpu usage on cisco devices! */

//if (!is_array($cache_storage['cisco-flash-mib']))
//{
//  $cache_storage['cisco-flash-mib'] = snmpwalk_cache_oid($device, "ciscoFlashPartitionTable", NULL, "CISCO-FLASH-MIB", mib_dirs("cisco"));
//  /** produce timeouts
//  $cache_storage['cisco-flash-mib'] = snmpwalk_cache_oid($device, "ciscoFlashPartitionSizeExtended", NULL, "CISCO-FLASH-MIB", mib_dirs("cisco"));
//  if ($GLOBALS['snmp_status'])
//  {
//    $cache_storage['cisco-flash-mib'] = snmpwalk_cache_oid($device, "ciscoFlashPartitionFreeSpaceExtended", $cache_storage['cisco-flash-mib'], "CISCO-FLASH-MIB", mib_dirs("cisco"));
//  }
//  $cache_storage['cisco-flash-mib'] = snmpwalk_cache_oid($device, "ciscoFlashPartitionSize", $cache_storage['cisco-flash-mib'], "CISCO-FLASH-MIB", mib_dirs("cisco"));
//  $cache_storage['cisco-flash-mib'] = snmpwalk_cache_oid($device, "ciscoFlashPartitionFreeSpace", $cache_storage['cisco-flash-mib'], "CISCO-FLASH-MIB", mib_dirs("cisco"));
//  */
//  if (OBS_DEBUG > 1 && count($cache_storage['cisco-flash-mib'])) { print_vars($cache_storage['cisco-flash-mib']); }
//}

//$entry = $cache_storage['cisco-flash-mib'][$storage['storage_index']];

$storage['units'] = 1;
if ($storage['storage_hc'])
{
  $oids = array('ciscoFlashPartitionSizeExtended.' . $storage['storage_index'], 'ciscoFlashPartitionFreeSpaceExtended.' . $storage['storage_index']);
  $entry = snmp_get_multi($device, $oids, "-OQUs", "CISCO-FLASH-MIB", mib_dirs("cisco"));
  $entry = array_shift($entry);
  $storage['size']  = $entry['ciscoFlashPartitionSizeExtended'];
  $storage['free']  = $entry['ciscoFlashPartitionFreeSpaceExtended'];
} else {
  $oids = array('ciscoFlashPartitionSize.' . $storage['storage_index'], 'ciscoFlashPartitionFreeSpace.' . $storage['storage_index']);
  $entry = snmp_get_multi($device, $oids, "-OQUs", "CISCO-FLASH-MIB", mib_dirs("cisco"));
  $entry = array_shift($entry);
  $storage['size']  = $entry['ciscoFlashPartitionSize'];
  $storage['free']  = $entry['ciscoFlashPartitionFreeSpace'];
}
if (OBS_DEBUG > 1 && count($entry)) { print_vars($entry); }

$storage['used']  = $storage['size'] - $storage['free'];

// EOF
