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

echo("HOST-RESOURCES-MIB ");

$hrDevice_oids = array('hrDevice', 'hrProcessorLoad');
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
      $hrDeviceIndex = $entry['hrDeviceIndex'];

      $usage_oid = ".1.3.6.1.2.1.25.3.3.1.2.$index";
      $usage = $entry['hrProcessorLoad'];

      // What is this for? I have forgotten. What has : in its hrDeviceDescr?
      // Set description to that found in hrDeviceDescr, first part only if containing a :
      $descr_array = explode(":",$entry['hrDeviceDescr']);
      if ($descr_array['1']) { $descr = $descr_array['1']; } else { $descr = $descr_array['0']; }

      // Workaround to set fake description for Mikrotik and other who don't populate hrDeviceDescr
      if (empty($entry['hrDeviceDescr'])) { $descr = "Processor"; }

      $descr = rewrite_entity_name($descr);

      if ($device['os'] == "arista_eos" && $index == "1") { unset($descr); }

      if (isset($descr) && $descr != "An electronic chip that makes the computer work.")
      {
        discover_processor($valid['processor'], $device, $usage_oid, $index, "hr", $descr, "1", $usage, NULL, $hrDeviceIndex);
        $hr_cpus++; $hr_total += $usage;
      }
      unset($old_rrd,$new_rrd,$descr,$entry,$usage_oid,$index,$usage,$hrDeviceIndex,$descr_array);
    }
    unset($entry);
  }

  if ($hr_cpus)
  {
    $hr_total = $hr_total / $hr_cpus;
    discover_processor($valid['processor'], $device, 1, 1, "hr-average", "Average", 1, $usage, NULL, NULL);
    $ucd_count = @dbFetchCell("SELECT COUNT(*) FROM `processors` WHERE `device_id` = ? AND `processor_type` = 'ucd-old'", array($device['device_id']));
    if ($ucd_count)
    {
      $GLOBALS['module_stats']['processors']['deleted']++; //echo('-');
      dbDelete('processors', "`device_id` = ? AND `processor_type` = 'ucd-old'", array($device['device_id'])); // Heh, this is because UCD-SNMP-MIB run earlier
    }
  }

  unset($hrDevice_oids, $hrDevice_array, $oid);
}

// EOF
