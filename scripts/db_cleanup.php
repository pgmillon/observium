#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage housekeeping
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

chdir(dirname($argv[0]).'/..'); // .. because we're in scripts/

include_once("includes/defaults.inc.php");
include_once("config.php");

$options = getopt("Vydh");

include_once("includes/definitions.inc.php");
include("includes/functions.inc.php");

$scriptname = basename($argv[0]);

$cli = is_cli();

if (isset($options['V']))
{
  print_message(OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION);
  if (is_array($options['V'])) { print_versions(); }
  exit;
}
print_message("%g".OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION."\n%WDB Cleanup%n\n", 'color');
if (OBS_DEBUG) { print_versions(); }

$prompt = !isset($options['y']);
$answer = TRUE;

if (isset($options['h']))
{
  print_message("%n
USAGE:
$scriptname [-Vydh]

OPTIONS:
 -V                                          Show version and exit.
 -y                                          Automatically answer 'yes' to prompts

DEBUGGING OPTIONS:
 -d                                          Enable debugging output.
 -dd                                         More verbose debugging output.

EXAMPLES:
  $scriptname -y                             Clean up database without prompts

%rInvalid arguments!%n", 'color', FALSE);
  exit;
} else {
  // Store highest device ID so we don't delete data from devices that were added during our run.
  // Counting on mysql auto_increment to never give out a lower ID, obviously.
  $max_id = -1;

  // Fetch all existing device IDs
  foreach (dbFetch("SELECT `device_id` FROM `devices`") as $device)
  {
    $devices[] = $device['device_id'];
    if ($device['device_id'] > $max_id) { $max_id = $device['device_id']; }
  }

  // Cleanup tables with links to devices that on longer exist
  foreach ($config['device_tables'] as $table)
  {
    $where = '`device_id` NOT IN (' . implode($devices, ',') . ') AND `device_id` < ?';

    if ($table == 'entity_permissions')
    {
      $where = "`entity_type` = 'device' AND `entity_id` NOT IN (" . implode($devices, ',') . ") AND `entity_id` = ?";
    }

    $rows  = dbFetchRows("SELECT 1 FROM `$table` WHERE $where", array($max_id));
    $count = count($rows);
    if ($count)
    {
      if ($prompt)
      {
        $answer = print_prompt("$count rows in table $table for non-existing devices will be deleted");
      }
      if ($answer)
      {
        $table_status = dbDelete($table, $where, array($max_id));
        print_debug("Database cleanup for table $table: deleted $count entries");
        logfile("cleanup.log", "Database cleanup for table $table: deleted $count entries");
      }
    }  
    else if ($prompt)
    {
      print_message("No orphaned rows found in table $table.");
    }
  }

  // Cleanup duplicate entries in the device_graphs table
  foreach (dbFetchRows("SELECT * FROM `device_graphs`") as $entry)
  {
    $graphs[$entry['device_id']][$entry['graph']][] = $entry['device_graph_id'];
  }

  foreach ($graphs as $device_id => $device_graph)
  {
    foreach ($device_graph as $graph => $data)
    {
      if (count($data) > 1)
      {
        // More than one entry for a single graph type for this device, let's clean up.
        // Leave the first entry intact, chop it off the array
        $device_graph_ids = array_slice($data,1);
        if ($prompt)
        {
          $answer = print_prompt(count($device_graph_ids) . " duplicate graph rows of type $graph for device $device_id will be deleted");
        }
        if ($answer)
        {
          $table_status = dbDelete('device_graphs', "`device_graph_id` IN (?)", array($device_graph_ids));
          print_debug("Deleted " . count($device_graph_ids) . " duplicate graph rows of type $graph for device $device_id");
          logfile("cleanup.log", "Deleted " . count($device_graph_ids) . " duplicate graph rows of type $graph for device $device_id");
        }
      }
    }
  }
}

// EOF
