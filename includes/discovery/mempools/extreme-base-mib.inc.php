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

$mib = 'EXTREME-BASE-MIB';
echo(" $mib ");

# lookup for memory data
$mempool_array = snmpwalk_cache_oid($device, 'extremeMemoryMonitorSystemTable', NULL, $mib, mib_dirs('extreme'));

if (is_array($mempool_array))
{
  foreach ($mempool_array as $index => $entry)
  {
    if (is_numeric($entry['extremeMemoryMonitorSystemFree']) && is_numeric($index))
    {
      $descr  = "Memory $index";
      $free   = $entry['extremeMemoryMonitorSystemFree']  * 1024;
      $total  = $entry['extremeMemoryMonitorSystemTotal'] * 1024;
      $used   = $total - $free;
      discover_mempool($valid['mempool'], $device, $index, $mib, $descr, 1024, $total, $used);
    }
  }
}
unset ($mempool_array, $index, $descr, $total, $used, $free);

// EOF
