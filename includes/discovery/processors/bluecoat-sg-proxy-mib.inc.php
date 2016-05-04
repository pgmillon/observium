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

echo("BLUECOAT-SG-PROXY-MIB ");

$processors_array = snmpwalk_cache_oid($device, "sgProxyCpuCoreBusyPerCent", array(), "BLUECOAT-SG-PROXY-MIB", mib_dirs('bluecoat'));
if (OBS_DEBUG > 1) { print_vars($processors_array); }

if (is_array($processors_array))
{
  foreach ($processors_array as $index => $entry)
  {
    $descr = ($index == 1) ? "CPU" : "CPU" . strval($index - 1);
    $oid = ".1.3.6.1.4.1.3417.2.11.2.4.1.8.".$index;
    $usage = $entry['sgProxyCpuCoreBusyPerCent'];

    discover_processor($valid['processor'], $device, $oid, $index, "cpu", $descr, "1", $usage, NULL, NULL);
  }
}

unset ($processors_array);

// EOF
