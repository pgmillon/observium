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

$options = getopt("d");
if (isset($options['d'])) { array_shift($argv); } // for compatability

include("includes/sql-config.inc.php");

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
