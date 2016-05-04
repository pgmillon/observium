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

//S5-CHASSIS-MIB::s5ChasUtilMemoryTotalMB.3.10.0 = Gauge32: 128 MegaBytes
//S5-CHASSIS-MIB::s5ChasUtilMemoryAvailableMB.3.10.0 = Gauge32: 65 MegaBytes

$mib = 'S5-CHASSIS-MIB';
echo("$mib ");

$mempool_array = snmpwalk_cache_oid($device, "s5ChasUtilEntry", NULL, $mib, mib_dirs('nortel'));
//$mempool_array = snmpwalk_cache_oid($device, "s5ChasComTable", $mempool_array, "$mib:S5-REG-MIB", mib_dirs('nortel'));
//print_vars($mempool_array);
if (is_array($mempool_array))
{
  $i = 1;
  foreach ($mempool_array as $index => $entry)
  {
    if (is_numeric($entry['s5ChasUtilMemoryAvailableMB']) && is_numeric($entry['s5ChasUtilMemoryTotalMB']))
    {
      $precision = 1024 * 1024;
      $total     = $entry['s5ChasUtilMemoryTotalMB'];
      //$total    *= $precision;
      $free      = $entry['s5ChasUtilMemoryAvailableMB'];
      //$free     *= $precision;
      $used      = $total - $free;
      $descr = "Memory Unit " . $i;
      discover_mempool($valid['mempool'], $device, $index, $mib, $descr, $precision, $total, $used);
      $i++;
    }
  }
}
unset ($mempool_array, $index, $descr, $precision, $total, $used, $free);

// EOF
