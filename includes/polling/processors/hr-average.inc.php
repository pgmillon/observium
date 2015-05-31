<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$hrDevice_oids = array('hrDevice','hrProcessorLoad');
unset($hrDevice_array);
foreach ($hrDevice_oids as $oid) { $hrDevice_array = snmpwalk_cache_oid($device, $oid, $hrDevice_array, "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES", mib_dirs()); }

$hr_cpus = 0; $hr_total = 0;

if (is_array($hrDevice_array))
{
  foreach ($hrDevice_array as $index => $entry)
  {
    if (!isset($entry['hrDeviceType']) && is_numeric($entry['hrProcessorLoad']))
    {
      $entry['hrDeviceType']  = "hrDeviceProcessor";
      $entry['hrDeviceIndex'] = $index;
    }
    elseif ($entry['hrDeviceType'] == "hrDeviceOther" && is_numeric($entry['hrProcessorLoad']) && preg_match('/^cpu[0-9]+:/', $entry['hrDeviceDescr']))
    {
      // Workaround bsnmpd reporting CPUs as hrDeviceOther (fuck you, FreeBSD.)
      $entry['hrDeviceType'] = "hrDeviceProcessor";
    }
    if ($entry['hrDeviceType'] == "hrDeviceProcessor")
    {

      $usage = $entry['hrProcessorLoad'];

      if ($device['os'] == "arista_eos" && $index == "1") { unset($descr); }

      if (isset($descr) && $descr != "An electronic chip that makes the computer work.")
      {
        $hr_cpus++; $hr_total += $usage;
      }
    }
    unset($entry);
  }

  if ($hr_cpus)
  {
    $proc = $hr_total / $hr_cpus;
  }

  unset($hrDevice_oids, $hrDevice_array, $oid);
}

// EOF
