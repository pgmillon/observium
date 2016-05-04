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

// Note. $cache_discovery['ucd-snmp-mib'] - is cached 'UCD-SNMP-MIB::dskEntry' (see ucd-snmp-mib.inc.php in current directory)

$mib = 'HOST-RESOURCES-MIB';

if (!isset($cache_discovery['host-resources-mib']))
{
  $cache_discovery['host-resources-mib'] = snmpwalk_cache_oid($device, "hrStorageEntry", NULL, "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES", mib_dirs());
  if (OBS_DEBUG && count($cache_discovery['host-resources-mib'])) { print_vars($cache_discovery['host-resources-mib']); }
}

if (count($cache_discovery['host-resources-mib']))
{
  echo(" $mib ");

  foreach ($cache_discovery['host-resources-mib'] as $index => $storage)
  {
    $hc = 0;
    $mib = 'HOST-RESOURCES-MIB';
    $fstype = $storage['hrStorageType'];
    $descr = $storage['hrStorageDescr'];
    $units = $storage['hrStorageAllocationUnits'];
    $deny = FALSE;

    switch($fstype)
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
        $deny = TRUE;
        break;
    }

    if (isset($config['ignore_mount_removable']) && $config['ignore_mount_removable'] && $fstype == "hrStorageRemovableDisk") { $deny = TRUE; print_debug("skip(removable)"); }
    if (isset($config['ignore_mount_network'])   && $config['ignore_mount_network']   && $fstype == "hrStorageNetworkDisk")   { $deny = TRUE; print_debug("skip(network)"); }
    if (isset($config['ignore_mount_optical'])   && $config['ignore_mount_optical']   && $fstype == "hrStorageCompactDisc")   { $deny = TRUE; print_debug("skip(cd)"); }

    if (!$deny)
    {
      //32bit counters
      $size = snmp_dewrap32bit($storage['hrStorageSize']) * $units;
      $used = snmp_dewrap32bit($storage['hrStorageUsed']) * $units;

      $path = rewrite_storage($descr);

      // Find index from 'UCD-SNMP-MIB::dskTable'
      foreach ($cache_discovery['ucd-snmp-mib'] as $dsk)
      {
        if ($dsk['dskPath'] === $path)
        {
          // Using 64bit counters if available
          if (isset($dsk['dskTotalLow']))
          {
            $dsk['units'] = 1024;
            $dsk['size'] = $dsk['dskTotalHigh'] * 4294967296 + $dsk['dskTotalLow'];
            $dsk['size'] *= $dsk['units'];
            if (($dsk['size'] - $size) > $units)
            {
              // Use 64bit counters only if dskTotal bigger then hrStorageSize
              // This is try.. if, if, if and more if
              $hc = 1;
              $mib    = 'UCD-SNMP-MIB';
              $index  = $dsk['dskIndex'];
              $fstype = $dsk['dskDevice'];
              $descr  = $dsk['dskPath'];
              $units  = $dsk['units'];
              $size   = $dsk['size'];
              $used   = $dsk['dskUsedHigh']  * 4294967296 + $dsk['dskUsedLow'];
              $used  *= $units;
            }
          }
          break;
        }
      }
    }

    if (!$deny && is_numeric($index))
    {
      discover_storage($valid['storage'], $device, $index, $fstype, $mib, $descr, $units, $size, $used, $hc);
    }

    unset($deny, $fstype, $descr, $size, $used, $units, $path, $dsk, $hc);
  }
}
else if (count($cache_discovery['ucd-snmp-mib']))
{
  // Discover 'UCD-SNMP-MIB' if 'HOST-RESOURCES-MIB' empty.
  $mib = 'UCD-SNMP-MIB';
  echo(" $mib ");

  foreach ($cache_discovery['ucd-snmp-mib'] as $index => $dsk)
  {
    $hc = 0;
    $fstype = $dsk['dskDevice'];
    $descr  = $dsk['dskPath'];
    $units  = 1024;
    $deny   = FALSE;

    // Using 64bit counters if available
    if (isset($dsk['dskTotalLow']))
    {
      $hc = 1;
      $size  = $dsk['dskTotalHigh'] * 4294967296 + $dsk['dskTotalLow'];
      $size *= $units;
      $used  = $dsk['dskUsedHigh']  * 4294967296 + $dsk['dskUsedLow'];
      $used *= $units;
    } else {
      $size  = $dsk['dskTotal'] * $units;
      $used  = $dsk['dskUsed'] * $units;
    }

    if (!$deny && is_numeric($index))
    {
      discover_storage($valid['storage'], $device, $index, $fstype, $mib, $descr, $units, $size, $used, $hc);
    }
    unset($deny, $fstype, $descr, $size, $used, $units, $hc);
  }
}

// EOF
