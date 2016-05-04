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

print_message("%g".OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION."\n%WRemove Device%n\n", 'color');

// Remove a host and all related data from the system
if ($argv[1])
{
  $host = strtolower($argv[1]);
  $id = get_device_id_by_hostname($host);
  $delete_rrd = (isset($argv[2]) && strtolower($argv[2]) == 'rrd') ? TRUE : FALSE;

  // Test if a valid id was fetched from get_device_id_by_hostname()
  if (isset($id) && is_numeric($id))
  {
    print_warning(delete_device($id, $delete_rrd));
    print_success("Device $host removed.");
  } else {
    print_error("Device $host doesn't exist!");
  }

} else {
    print_message("%n
USAGE:
$scriptname <hostname> [rrd]

EXAMPLE:
%WKeep RRDs%n:   $scriptname <hostname>
%WRemove RRDs%n: $scriptname <hostname> rrd

%rInvalid arguments!%n", 'color', FALSE);
}

// EOF
