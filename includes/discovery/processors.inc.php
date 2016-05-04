<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

echo("Processors : ");

// Include all discovery modules by supported MIB

$include_dir = "includes/discovery/processors";
include("includes/include-dir-mib.inc.php");

// Detect processors by simple MIB-based discovery :
// FIXME - this should also be extended to understand multiple entries in a table, and take descr from an OID but this is all I need right now :)
foreach($config['os'][$device['os']]['mibs'] AS $mib)
{
  if(is_array($config['mibs'][$mib]['processor']))
  {
    echo(' '.$mib.': ');
    foreach($config['mibs'][$mib]['processor'] AS $entry_name => $entry)
    {
      echo($entry_name.' ');
      $usage = snmp_get($device, $entry['oid'], '-OQUvs', $mib, mib_dirs($config['mibs'][$mib]['mib_dir']));
      if (is_numeric($usage))
      {
        discover_processor($valid['processor'], $device, $entry['oid_num'], 0, $entry_name, $entry['descr'], 1, $usage);
      }
    }
  }
}


if (OBS_DEBUG && count($valid['processor'])) { print_vars($valid['processor']); }

// Remove processors which weren't redetected here
foreach (dbFetchRows('SELECT * FROM `processors` WHERE `device_id` = ?', array($device['device_id'])) as $test_processor)
{
  $processor_index = $test_processor['processor_index'];
  $processor_type  = $test_processor['processor_type'];
  $processor_descr = $test_processor['processor_descr'];
  print_debug($processor_index . " -> " . $processor_type);

  if (!$valid['processor'][$processor_type][$processor_index])
  {
    echo('-');
    dbDelete('processors', '`processor_id` = ?', array($test_processor['processor_id']));
    log_event("Processor removed: type ".$processor_type." index ".$processor_index." descr ". $processor_descr, $device, 'processor', $test_processor['processor_id']);
  }
  unset($processor_oid); unset($processor_type);
}

echo(PHP_EOL);

// EOF
