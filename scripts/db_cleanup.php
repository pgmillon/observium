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
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

chdir(dirname($argv[0]).'/..'); // .. because we're in scripts/

$options = getopt("Vydh");

if (isset($options['d']))
{
  echo("DEBUG!\n");
  $debug = TRUE;
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
#  ini_set('error_reporting', E_ALL ^ E_NOTICE);
} else {
  $debug = FALSE;
#  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
#  ini_set('error_reporting', 0);
}

include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include("includes/functions.inc.php");

$scriptname = basename($argv[0]);

$cli = is_cli();

if (isset($options['V']))
{
  print_message(OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION);
  exit;
}
print_message("%g".OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION."\n%WDB Cleanup%n\n", 'color');

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
}

// EOF
