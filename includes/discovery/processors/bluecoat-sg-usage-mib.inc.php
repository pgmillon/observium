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

// ProxyAV devices hide their CPUs/Memory/Interfaces in here
echo("BLUECOAT-SG-USAGE-MIB ");

$av_array = snmpwalk_cache_oid($device, "deviceUsage", array(), "BLUECOAT-SG-USAGE-MIB", mib_dirs('bluecoat'));
if (OBS_DEBUG > 1) { print_vars($av_array); }

if (is_array($av_array))
{
  foreach ($av_array as $index => $entry)
  {
    if (strpos($entry['deviceUsageName'], "CPU") !== FALSE)
    {
      $descr = $entry['deviceUsageName'];
      $oid = ".1.3.6.1.4.1.3417.2.4.1.1.1.4.".$index;
      $usage = $entry['deviceUsagePercent'];

      discover_processor($valid['processor'], $device, $oid, $index, "cpu", $descr, "1", $usage, NULL, NULL);
    }
  }
}

unset ($av_array);

// EOF
