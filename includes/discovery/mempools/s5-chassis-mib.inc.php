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

//S5-CHASSIS-MIB::s5ChasUtilMemoryTotalMB.3.10.0 = Gauge32: 128 MegaBytes
//S5-CHASSIS-MIB::s5ChasUtilMemoryAvailableMB.3.10.0 = Gauge32: 65 MegaBytes

$mib = 'S5-CHASSIS-MIB';
echo(" $mib ");

$mempool_array = snmpwalk_cache_oid($device, "s5ChasUtilEntry", NULL, $mib, mib_dirs('nortel'));
if (is_array($mempool_array))
{
  foreach ($mempool_array as $index => $entry)
  {
    if (is_numeric($entry['s5ChasUtilMemoryAvailableMB']) && is_numeric($entry['s5ChasUtilMemoryTotalMB']))
    {
      $precision = 1024 * 1024;
      $total     = $entry['s5ChasUtilMemoryTotalMB'];
      $total    *= $precision;
      $free      = $entry['s5ChasUtilMemoryAvailableMB'];
      $free     *= $precision;
      $used      = $total - $free;
      $descr = "Memory Unit " . ($index + 1);
      discover_mempool($valid['mempool'], $device, $index, $mib, $descr, $precision, $total, $used);
    }
  }
}
unset ($mempool_array, $index, $descr, $precision, $total, $used, $free);

// EOF
