#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage snmptraps
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

$entry = explode(",", $argv[1]);

logfile('SNMPTRAP: '.$argv[1]);

#print_vars($entry);

$device = @dbFetchRow("SELECT * FROM devices WHERE `hostname` = ?", array($entry['0']));

if (!$device['device_id'])
{
  $device = @dbFetchRow("SELECT * FROM ipv4_addresses AS A, ports AS I WHERE A.ipv4_address = ? AND I.port_id = A.port_id", array($entry['0']));
}

if (!$device['device_id']) { exit; }

$file = $config['install_dir'] . "/includes/snmptrap/".$entry['1'].".inc.php";
if (is_file($file)) { include("$file"); } else { echo("unknown trap ($file)"); }

// EOF
