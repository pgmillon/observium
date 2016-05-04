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

echo("FOUNDRY-SN-AGENT-MIB ");

$processors_array = snmpwalk_cache_triple_oid($device, "snAgentCpuUtilEntry", $processors_array, "FOUNDRY-SN-AGENT-MIB", mib_dirs('foundry'));
if (OBS_DEBUG > 1) { print_vars($processors_array); }
foreach ($processors_array as $index => $entry)
{
  if ((isset($entry['snAgentCpuUtilValue']) || isset($entry['snAgentCpuUtil100thPercent'])) && $entry['snAgentCpuUtilInterval'] == "300")
  {
    #$entPhysicalIndex = $entry['cpmCPUTotalPhysicalIndex'];

    if ($entry['snAgentCpuUtil100thPercent'])
    {
      $usage_oid = ".1.3.6.1.4.1.1991.1.1.2.11.1.1.6." . $index;
      $usage = $entry['snAgentCpuUtil100thPercent'];
      $precision = 100;
    } elseif ($entry['snAgentCpuUtilValue']) {
      $usage_oid = ".1.3.6.1.4.1.1991.1.1.2.11.1.1.4." . $index;
      $usage = $entry['snAgentCpuUtilValue'];
      $precision = 100;
    }

    list($slot, $instance, $interval) = explode(".", $index);

    $descr_oid = "snAgentConfigModuleDescription." . $entry['snAgentCpuUtilSlotNum'];
    $descr = snmp_get($device, $descr_oid, "-Oqv", "FOUNDRY-SN-AGENT-MIB", mib_dirs('foundry'));
    $descr = str_replace("\"", "", $descr);
    list($descr) = explode(" ", $descr);

    $descr = "Slot " . $entry['snAgentCpuUtilSlotNum'] . " " . $descr;
    $descr = $descr . " [".$instance."]";

    if (!strstr($descr, "No") && !strstr($usage, "No") && $descr != "")
    {
      discover_processor($valid['processor'], $device, $usage_oid, $index, "ironware", $descr, $precision, $usage, $entPhysicalIndex, NULL);
    }
  }
}

unset ($processors_array);

// EOF
