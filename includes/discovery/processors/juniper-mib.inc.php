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

echo("JUNIPER-MIB ");
$processors_array = snmpwalk_cache_multi_oid($device, "jnxOperatingCPU",      array(), "JUNIPER-MIB");

if (is_array($processors_array))
{
  $processors_array = snmpwalk_cache_multi_oid($device, "jnxOperatingDRAMSize", $processors_array, "JUNIPER-MIB");
  //$processors_array = snmpwalk_cache_multi_oid($device, "jnxOperatingMemory",   $processors_array, "JUNIPER-MIB");
  $processors_array = snmpwalk_cache_multi_oid($device, "jnxOperatingDescr",    $processors_array, "JUNIPER-MIB");
  if (OBS_DEBUG) { print_vars($processors_array); }

  foreach ($processors_array as $index => $entry)
  {
    if (strpos($entry['jnxOperatingDescr'], "Routing Engine") !== FALSE ||
        strpos($entry['jnxOperatingDescr'], "FPC") !== FALSE ||
        $entry['jnxOperatingDRAMSize'] > 0)
    {
      if (stripos($entry['jnxOperatingDescr'], "sensor") !== FALSE ||
          stripos($entry['jnxOperatingDescr'], "fan") !== FALSE ||
          stripos($entry['jnxOperatingDescr'], "pcmcia") !== FALSE) { continue; }

      $oid = ".1.3.6.1.4.1.2636.3.1.13.1.8." . $index;
      $descr = $entry['jnxOperatingDescr'];
      $usage = $entry['jnxOperatingCPU'];
      if (!strstr($descr, "No") && !strstr($usage, "No") && $descr != "")
      {
        discover_processor($valid['processor'], $device, $oid, $index, "junos", $descr, 1, $usage);
      }
    } // End if checks
  } // End Foreach
} // End if array

unset ($processors_array);

// EOF
