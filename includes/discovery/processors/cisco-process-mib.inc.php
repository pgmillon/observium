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

echo("CISCO-PROCESS-MIB ");

$processors_array = snmpwalk_cache_oid($device, "cpmCPU", NULL, "CISCO-PROCESS-MIB");
if (OBS_DEBUG > 1) { print_vars($processors_array); }

foreach ($processors_array as $index => $entry)
{
  if (is_numeric($entry['cpmCPUTotal5minRev']) || is_numeric($entry['cpmCPUTotal5min']))
  {
    $entPhysicalIndex = $entry['cpmCPUTotalPhysicalIndex'];

    if (isset($entry['cpmCPUTotal5minRev']))
    {
      $usage_oid = ".1.3.6.1.4.1.9.9.109.1.1.1.1.8." . $index;
      $usage = $entry['cpmCPUTotal5minRev'];
    } elseif (isset($entry['cpmCPUTotal5min'])) {
      $usage_oid = ".1.3.6.1.4.1.9.9.109.1.1.1.1.5." . $index;
      $usage = $entry['cpmCPUTotal5min'];
    }

    if ($entPhysicalIndex)
    {
      $descr_oid = "entPhysicalName." . $entPhysicalIndex;
      $descr = snmp_get($device, $descr_oid, "-Oqv", "ENTITY-MIB");
    }
    if (!$descr) { $descr = "Processor $index"; }

    if (!strstr($descr, "No") && !strstr($usage, "No") && $descr != "")
    {
      discover_processor($valid['processor'], $device, $usage_oid, $index, "cpm", $descr, "1", $entry['juniSystemModuleCpuUtilPct'], $entPhysicalIndex, NULL);
    }
  }
}

if (!is_array($valid['processor']['cpm']))
{
  $avgBusy5 = snmp_get($device, ".1.3.6.1.4.1.9.2.1.58.0", "-Oqv");
  if (is_numeric($avgBusy5))
  {
    discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.9.2.1.58.0", "0", "ios", "Processor", "1", $avgBusy5, NULL, NULL);
  }
}

unset($processors_array);

// EOF
