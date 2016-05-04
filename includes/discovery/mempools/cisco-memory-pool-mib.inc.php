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
if (!isset($valid['mempool']['cisco-enhanced-mempool-mib']))
{
  $mib = 'CISCO-MEMORY-POOL-MIB';
  echo("$mib ");

  $mempool_array = snmpwalk_cache_oid($device, 'ciscoMemoryPool', NULL, $mib, mib_dirs('cisco'));
  if (is_array($mempool_array))
  {
    foreach ($mempool_array as $index => $entry)
    {
      if (is_numeric($entry['ciscoMemoryPoolUsed']) && is_numeric($index))
      {
        $descr = $entry['ciscoMemoryPoolName'];
        $used  = $entry['ciscoMemoryPoolUsed'];
        $free  = $entry['ciscoMemoryPoolFree'];
        $total = $used + $free;
        discover_mempool($valid['mempool'], $device, $index, $mib, $descr, 1, $total, $used);
      }
    }
  }
  unset ($mempool_array, $index, $descr, $total, $used, $free);
}

// EOF
