<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

echo("Processors : ");

// Include all discovery modules by supported MIB

$include_dir = "includes/discovery/processors";
include("includes/include-dir-mib.inc.php");

// Always poll host-resources-mib
include("processors/host-resources-mib.inc.php");

// Last-resort discovery here
include("processors/ucd-snmp-mib.inc.php");

if ($debug && count($valid['processor'])) { print_vars($valid['processor']); }

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
