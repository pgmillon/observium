#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage cli
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

chdir(dirname($argv[0]));
$scriptname = basename($argv[0]);

$options = getopt("dp");
if (isset($options['d'])) { array_shift($argv); } // for compatability

include("includes/sql-config.inc.php");

print_message("%g".OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION."\n%WRename Device%n\n", 'color');
if (OBS_DEBUG) { print_versions(); }

$options = array();
if (isset($options['p']))
{
  $options['ping_skip'] = 1;
  array_shift($argv);
}

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
      if (renamehost($id, $tohost, 'console', $options))
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

OPTIONS:
 -p                                          Skip icmp echo checks, device renamed only by SNMP checks

DEBUGGING OPTIONS:
 -d                                          Enable debugging output.
 -dd                                         More verbose debugging output.

%rInvalid arguments!%n", 'color', FALSE);
}

// EOF
