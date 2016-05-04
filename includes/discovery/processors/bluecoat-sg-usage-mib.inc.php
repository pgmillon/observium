<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

// ProxyAV devices hide their CPUs/Memory/Interfaces in here
echo(" BLUECOAT-SG-USAGE-MIB ");

$av_array = snmpwalk_cache_oid($device, "deviceUsage", array(), "BLUECOAT-SG-USAGE-MIB", mib_dirs('bluecoat'));
if (OBS_DEBUG > 1) { print_vars($av_array); }

if (is_array($av_array))
{
  foreach ($av_array as $index => $entry)
  {
    if (strpos($entry['deviceUsageName'], "CPU") !== false) {
        $descr = $entry['deviceUsageName'];
        $oid = ".1.3.6.1.4.1.3417.2.4.1.1.1.4.".$index;
        $usage = $entry['deviceUsagePercent'];

        discover_processor($valid['processor'], $device, $oid, $index, "cpu", $descr, "1", $usage, NULL, NULL);
    }
  }
}

unset ($av_array);

// EOF
