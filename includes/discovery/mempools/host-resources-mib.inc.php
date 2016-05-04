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

$mib = 'HOST-RESOURCES-MIB';
echo("$mib ");

if (!isset($cache_discovery['host-resources-mib']))
{
  $cache_discovery['host-resources-mib'] = snmpwalk_cache_oid($device, "hrStorageEntry", NULL, "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES", mib_dirs());
}

//$debug_stats = array('total' => 0, 'used' => 0);
if (count($cache_discovery['host-resources-mib']))
{
  foreach ($cache_discovery['host-resources-mib'] as $index => $entry)
  {
    $descr  = $entry['hrStorageDescr'];
    $units  = $entry['hrStorageAllocationUnits'];
    $total  = $entry['hrStorageSize']; // * $units;
    $used   = $entry['hrStorageUsed']; // * $units;
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
    else if ($device['os'] == "mcd")
    {
      // Yes, hardcoded logic for mcd, because they do not use standard
      // See: http://jira.observium.org/browse/OBSERVIUM-1269
      if ($index === 1)
      {
        // hrStorageType.1 = hrStorageRam
        // hrStorageDescr.1 = System Free Memory
        // hrStorageAllocationUnits.1 = 1
        // hrStorageSize.1 = 160481280
        // hrStorageUsed.1 = 160481280
        $descr = "Memory";
        $free  = $total;
        $total = 536870912; // 512Mb, Really total memory calculates as summary of all memory pools from this mib
        $used  = $total - $free;
        discover_mempool($valid['mempool'], $device, $index, "host-resources-mcd", $descr, $units, $total, $used);
      }
      $deny = TRUE;
    }

    if (strstr($descr, "MALLOC") || strstr($descr, "UMA")) { $deny = TRUE;  }   // Ignore FreeBSD INSANITY
    if (strstr($descr, "procfs") || strstr($descr, "/proc")) { $deny = TRUE;  } // Ignore ProcFS

    if (!$deny && is_numeric($entry['hrStorageSize']) && $total)
    {
      discover_mempool($valid['mempool'], $device, $index, $mib, $descr, $units, $total, $used);
      //$debug_stats['total'] += $total;
      //$debug_stats['used']  += $used;
    }
  }
}
unset ($index, $descr, $total, $used, $units, $deny);

// EOF
