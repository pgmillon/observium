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

// Force10 M-Series

$mib = 'F10-M-SERIES-CHASSIS-MIB';
echo("$mib ");

$mempool_array = snmpwalk_cache_oid($device, "chStackUnitMemUsageUtil", NULL, $mib, mib_dirs('force10'));
if (is_array($mempool_array))
{
  $mempool_array = snmpwalk_cache_oid($device, "chStackUnitSysType", $mempool_array, $mib, mib_dirs('force10'));
  $total_array   = snmpwalk_cache_oid($device, "chSysProcessorMemSize", NULL, $mib, mib_dirs('force10'));
  if (OBS_DEBUG > 1 && count($total_array)) { print_vars($total_array); }
  foreach ($mempool_array as $index => $entry)
  {
    if (is_numeric($entry['chStackUnitMemUsageUtil']))
    {
      if (is_numeric($total_array[$index]['chSysProcessorMemSize']))
      {
        $precision = 1024 * 1024;
        $total     = $total_array[$index]['chSysProcessorMemSize']; // FTOS display memory in MB
        //$total    *= $precision;
      } else {
        $precision = 1;
        $total     = 1090519040; // Hardcoded total. See FIXME above.
      }
      $percent = $entry['chStackUnitMemUsageUtil'];
      $used    = $total * $percent / 100;
      $descr = "Unit " . strval($index - 1) . " " . $entry['chStackUnitSysType'];
      discover_mempool($valid['mempool'], $device, $index, $mib, $descr, $precision, $total, $used);
    }
  }
}
unset ($mempool_array, $total_array, $index, $descr, $precision, $total, $used, $percent);

// EOF
