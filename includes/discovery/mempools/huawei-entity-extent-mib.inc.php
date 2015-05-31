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

// Huawei VRP  mempools
$mib = 'HUAWEI-ENTITY-EXTENT-MIB';
echo(" $mib ");

$mempool_array = snmpwalk_cache_multi_oid($device, "hwEntityMemUsage", NULL, $mib, mib_dirs('huawei'));

if (is_array($mempool_array))
{
  $mempool_array = snmpwalk_cache_multi_oid($device, "hwEntityMemSize",  $mempool_array, $mib, mib_dirs('huawei'));
  $mempool_array = snmpwalk_cache_multi_oid($device, "hwEntityBomEnDesc",$mempool_array, $mib, mib_dirs('huawei'));
  foreach ($mempool_array as $index => $entry)
  {
    if (is_numeric($entry['hwEntityMemUsage']) && $entry['hwEntityMemSize'] > 0 )
    {
      $descr   = $entry['hwEntityBomEnDesc'];
      $percent = $entry['hwEntityMemUsage'];
      if (!strstr($descr, "No") && !strstr($percent, "No") && $descr != "" )
      {
        $total = $entry['hwEntityMemSize'];
        $used  = $total * $percent / 100;
        discover_mempool($valid['mempool'], $device, $index, $mib, $descr, 1, $total, $used);
      }
    }
  }
}
unset ($mempool_array, $index, $descr, $total, $used, $percent);

// EOF
