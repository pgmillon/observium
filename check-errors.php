#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage alerts
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

chdir(dirname($argv[0]));
$scriptname = basename($argv[0]);

$options = getopt("d");
if (isset($options['d'])) { array_shift($argv); } // for compatability

include("includes/sql-config.inc.php");

// Check all of our interface RRD files for errors

if ($argv[1]) { $where = "AND `port_id` = ?"; $params = array($argv[1]); }

$i = 0;
$errored = 0;

foreach (dbFetchRows("SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id $where", $params) as $interface)
{
  $errors = $interface['ifInErrors_delta'] + $interface['ifOutErrors_delta'];
  if ($errors > '1')
  {
    $errored[] = generate_device_link($interface, $interface['hostname'] . " - " . $interface['ifDescr'] . " - " . $interface['ifAlias'] . " - " . $interface['ifInErrors_delta'] . " - " . $interface['ifOutErrors_delta']);
    $errored++;
  }
  $i++;
}

echo("Checked $i interfaces\n");

if (is_array($errored))
{ // If there are errored ports
  $i = 0;
  $msg = "Interfaces with errors : \n\n";

  foreach ($errored as $int)
  {
    $msg .= "$int\n";  // Add a line to the report email warning about them
    $i++;
  }
  // Send the alert email
  notify($device, "Observium detected errors on $i interface" . ($i != 1 ? 's' : ''), $msg);
}

echo("$errored interfaces with errors over the past 5 minutes.\n");

// EOF
