<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

print_cli_data_field("Discovering MIBs", 3);

// Include all discovery modules by supported MIB

$include_dir = "includes/discovery/processors";
include("includes/include-dir-mib.inc.php");

// Detect processors by simple MIB-based discovery :
// FIXME - this should also be extended to understand multiple entries in a table, and take descr from an OID but this is all I need right now :)
foreach (get_device_mibs($device) as $mib)
{
  if (is_array($config['mibs'][$mib]['processor']))
  {
    echo("$mib ");
    foreach ($config['mibs'][$mib]['processor'] as $entry_name => $entry)
    {
      //echo($entry_name.' ');
      $entry['found'] = FALSE;
      if ($entry['type'] == 'table')
      {
        $processors_array = snmpwalk_cache_oid($device, $entry['table'], array(), $mib, mib_dirs($config['mibs'][$mib]['mib_dir']));
        if ($entry['table_descr'])
        {
          // If descr in separate table with same indexes
          $processors_array = snmpwalk_cache_oid($device, $entry['table_descr'], $processors_array, $mib, mib_dirs($config['mibs'][$mib]['mib_dir']));
        }
        $i = 1; // Used in descr as $i++
        foreach ($processors_array as $index => $processor)
        {
          $dot_index = '.' . $index;
          $oid   = $entry['oid_num'] . $dot_index;
          if ($entry['oid_descr'] && $processor[$entry['oid_descr']])
          {
            $descr = $processor[$entry['oid_descr']];
          }
          if (!$descr)
          {
            if (isset($entry['descr']))
            {
              if (strpos($entry['descr'], '{i}') === FALSE)
              {
                $descr = $entry['descr'] . ' ' . $index;
              } else {
                $descr = str_replace('{i}', $i, $entry['descr']);
              }
            } else {
              $descr = 'Processor ' . $index;
            }
          }
          $usage = snmp_fix_numeric($processor[$entry['oid']]);
          if (is_numeric($usage))
          {
            discover_processor($valid['processor'], $device, $oid, $entry['table'] . $dot_index, $entry_name, $descr, 1, $usage);
            $entry['found'] = TRUE;
          }
          $i++;
        }
      } else {
        if ($entry['oid_descr'])
        {
          $descr = snmp_get($device, $entry['oid_descr'], '-OQUvs', $mib, mib_dirs($config['mibs'][$mib]['mib_dir']));
        }
        if (!$descr)
        {
          if (isset($entry['descr']))
          {
            $descr = $entry['descr'];
          } else {
            $descr = 'Processor';
          }
        }
        $usage = snmp_get($device, $entry['oid'], '-OQUvs', $mib, mib_dirs($config['mibs'][$mib]['mib_dir']));
        $usage = snmp_fix_numeric($usage);
        if (is_numeric($usage))
        {
          discover_processor($valid['processor'], $device, $entry['oid_num'], 0, $entry_name, $descr, 1, $usage);
          $entry['found'] = TRUE;
        }
      }
      unset($processors_array, $processor, $dot_index, $descr, $i); // clean
      if (isset($entry['stop_if_found']) && $entry['stop_if_found'] && $entry['found']) { break; } // Stop loop if processor founded
    }
  }
}

// Remove processors which weren't redetected here
foreach (dbFetchRows('SELECT * FROM `processors` WHERE `device_id` = ?', array($device['device_id'])) as $test_processor)
{
  $processor_index = $test_processor['processor_index'];
  $processor_type  = $test_processor['processor_type'];
  $processor_descr = $test_processor['processor_descr'];
  print_debug($processor_index . " -> " . $processor_type);

  if (!$valid['processor'][$processor_type][$processor_index])
  {
    $GLOBALS['module_stats'][$module]['deleted']++; //echo('-');
    dbDelete('processors', '`processor_id` = ?', array($test_processor['processor_id']));
    log_event("Processor removed: type ".$processor_type." index ".$processor_index." descr ". $processor_descr, $device, 'processor', $test_processor['processor_id']);
  }
  unset($processor_oid); unset($processor_type);
}

$GLOBALS['module_stats'][$module]['status'] = count($valid['processor']);
if (OBS_DEBUG && $GLOBALS['module_stats'][$module]['status'])
{
  print_vars($valid['processor']);
}

// EOF
