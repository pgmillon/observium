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

// Huawei VRP  mempools
$mib = 'HUAWEI-ENTITY-EXTENT-MIB';
echo("$mib ");

$mempool_array = snmpwalk_cache_multi_oid($device, "hwEntityMemUsage", NULL, $mib, mib_dirs('huawei'));

if (is_array($mempool_array))
{
  $mempool_array = snmpwalk_cache_multi_oid($device, "hwEntityMemSize",  $mempool_array, $mib, mib_dirs('huawei'));
  $mempool_array = snmpwalk_cache_multi_oid($device, "ENTITY-MIB::entPhysicalName",$mempool_array, $mib, mib_dirs('huawei'));
  foreach ($mempool_array as $index => $entry)
  {
    if (is_numeric($entry['hwEntityMemUsage']) && $entry['hwEntityMemSize'] > 0 )
    {
      $descr   = rewrite_entity_name($entry['entPhysicalName']);
      $percent = $entry['entPhysicalName'];
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
