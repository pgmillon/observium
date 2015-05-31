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

$mib = 'BLUECOAT-SG-PROXY-MIB';
echo(" $mib ");

$mempool_array = snmpwalk_cache_oid($device, "sgProxyMem", NULL, $mib, mib_dirs('bluecoat'));

if (is_array($mempool_array))
{
  foreach ($mempool_array as $index => $entry)
  {
    if (is_numeric($index) && is_numeric($entry['sgProxyMemAvailable']))
    {
      $total = $entry['sgProxyMemAvailable'];
      $used  = $entry['sgProxyMemSysUsage'];
      discover_mempool($valid['mempool'], $device, $index, $mib, "Memory ".$index, 1, $total, $used);
    }
  }
}
unset ($mempool_array, $index, $total, $used);

// EOF

