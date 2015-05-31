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

echo(" HUAWEI-ENTITY-EXTENT-MIB ");

$processors_array = snmpwalk_cache_multi_oid($device, "hwEntityCpuUsage", $processors_array, "HUAWEI-ENTITY-EXTENT-MIB", mib_dirs('huawei'));
$processors_array = snmpwalk_cache_multi_oid($device, "hwEntityMemSize",  $processors_array, "HUAWEI-ENTITY-EXTENT-MIB", mib_dirs('huawei'));
$processors_array = snmpwalk_cache_multi_oid($device, "hwEntityBomEnDesc",$processors_array, "HUAWEI-ENTITY-EXTENT-MIB", mib_dirs('huawei'));
if ($debug) { print_vars($processors_array); }

if (is_array($processors_array))
{
  foreach ($processors_array as $index => $entry)
  {
    if ($entry['hwEntityMemSize'] != 0)
    {
      if ($debug) { echo($index . " " . $entry['hwEntityBomEnDesc'] . " -> " . $entry['hwEntityCpuUsage'] . " -> " . $entry['hwEntityMemSize']. "\n"); }
      $usage_oid = ".1.3.6.1.4.1.2011.5.25.31.1.1.1.1.5." . $index;
      $descr = $entry['hwEntityBomEnDesc'];
      $usage = $entry['hwEntityCpuUsage'];
      if (!strstr($descr, "No") && !strstr($usage, "No") && $descr != "" )
      {
        discover_processor($valid['processor'], $device, $usage_oid, $index, "vrp", $descr, "1", $usage, NULL, NULL);
      }
    } // End if checks
  } // End Foreach
} // End if array

unset ($processors_array);

// EOF
