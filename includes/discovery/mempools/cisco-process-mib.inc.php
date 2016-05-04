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

// Ignore this discovery module if we have already discovered things in CISCO-ENHANCED-MEMPOOL-MIB. Dirty duplication.
if (!isset($valid['mempool']['cisco-enhanced-mempool-mib']) && !isset($valid['mempool']['cisco-memory-pool-mib']))
{

  $mib = 'CISCO-PROCESS-MIB';
  echo("$mib ");

  $mempool_array = snmpwalk_cache_oid($device, 'cpmCPUMemoryUsed', NULL, $mib, mib_dirs('cisco'));
  $mempool_array = snmpwalk_cache_oid($device, 'cpmCPUMemoryFree', $mempool_array, $mib, mib_dirs('cisco'));
  $mempool_array = snmpwalk_cache_oid($device, 'cpmCPUTotalPhysicalIndex', $mempool_array, $mib, mib_dirs('cisco'));

  if (OBS_DEBUG > 1) { print_vars($mempool_array); }

  foreach ($mempool_array as $index => $entry)
  {
    if (is_numeric($entry['cpmCPUMemoryUsed']) && is_numeric($entry['cpmCPUMemoryFree']))
    {
      if ($entry['cpmCPUTotalPhysicalIndex'])
      {
        $descr = snmp_get($device, "entPhysicalName." . $entry['cpmCPUTotalPhysicalIndex'], "-Oqv", "ENTITY-MIB", mib_dirs());
      } else {
        $descr = "Memory Pool ".$index;
      }

      $precision = 1024;
      //$used      = $entry['cpmCPUMemoryUsed'] * $precision;
      //$free      = $entry['cpmCPUMemoryFree'] * $precision;
      $total     = $used + $free;

      discover_mempool($valid['mempool'], $device, $index, $mib, $descr, $precision, $total, $used);
    }
  }
}

// EOF
