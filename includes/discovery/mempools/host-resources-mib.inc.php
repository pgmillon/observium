<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$mib = 'HOST-RESOURCES-MIB';
echo(" $mib ");

$mempool_array = snmpwalk_cache_oid($device, "hrStorageEntry", NULL, "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES", mib_dirs());

if (is_array($mempool_array))
{
  foreach ($mempool_array as $index => $entry)
  {
    $descr  = $entry['hrStorageDescr'];
    $units  = $entry['hrStorageAllocationUnits'];
    $total  = $entry['hrStorageSize'] * $units;
    $used   = $entry['hrStorageUsed'] * $units;
    $deny   = TRUE;

    switch($entry['hrStorageType'])
    {
      case 'hrStorageVirtualMemory':
      case 'hrStorageRam':
      case 'hrStorageOther':
      case 'hrStorageTypes.20':
      case 'nwhrStorageDOSMemory':
      case 'nwhrStorageMemoryAlloc':
      case 'nwhrStorageMemoryPermanent':
      case 'nwhrStorageCacheBuffers':
      case 'nwhrStorageCacheMovable':
      case 'nwhrStorageCacheNonMovable':
      case 'nwhrStorageCodeAndDataMemory':
      case 'nwhrStorageIOEngineMemory':
      case 'nwhrStorageMSEngineMemory':
      case 'nwhrStorageUnclaimedMemory':
        $deny = FALSE;
        break;
    }

    if ($device['os'] == "routeros" && $descr == "main memory") { $deny = FALSE; }

    if (strstr($descr, "MALLOC") || strstr($descr, "UMA")) { $deny = TRUE;  }   // Ignore FreeBSD INSANITY
    if (strstr($descr, "procfs") || strstr($descr, "/proc")) { $deny = TRUE;  } // Ignore ProcFS

    if (!$deny && is_numeric($entry['hrStorageSize']) && $total)
    {
      discover_mempool($valid['mempool'], $device, $index, $mib, $descr, $units, $total, $used);
    }
  }
}
unset ($mempool_array, $index, $descr, $total, $used, $units, $deny);

// EOF
