#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage cli
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

chdir(dirname($argv[0]));
$scriptname = basename($argv[0]);

include_once("includes/defaults.inc.php");
include_once("config.php");

$options = getopt("d");
if (isset($options['d'])) { array_shift($argv); } // for compatability

include_once("includes/definitions.inc.php");
include("includes/functions.inc.php");

print_message("%g".OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION."\n%WRename Device%n\n", 'color');
if (OBS_DEBUG) { print_versions(); }

// Remove a host and all related data from the system

if ($argv[1] && $argv[2])
{
  $host = strtolower($argv[1]);
  $id = get_device_id_by_hostname($host);
  if ($id)
  {
    $tohost = strtolower($argv[2]);
    $toid = get_device_id_by_hostname($tohost);
    if ($toid)
    {
      print_error("NOT renamed. New hostname $tohost already exists.");
    } else {
      if (renamehost($id, $tohost, 'console'))
      {
        print_message("Host $host renamed to $tohost.");
      }
    }
  } else {
    print_error("Host $host doesn't exist.");
  }
} else {
    print_message("%n
USAGE:
$scriptname <old hostname> <new hostname>

%rInvalid arguments!%n", 'color', FALSE);
}

// EOF
