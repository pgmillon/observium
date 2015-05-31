#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage syslog
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

include_once("includes/defaults.inc.php");
include_once("config.php");
include_once("includes/definitions.inc.php");
include($config['install_dir'] . "/includes/functions.inc.php");

// Disable sql profiling, this is a background process without any way to display it
$config['profile_sql'] = FALSE;

$i = 1;

if (isset($config['syslog']['fifo']) && $config['syslog']['fifo'] !== FALSE)
{
  // FIFO configured, try to grab logs from it
  #echo 'Opening FIFO: '.$config['syslog']['fifo'].PHP_EOL; //No any echo to STDOUT/STDERR!
  $s = fopen($config['syslog']['fifo'], 'r');
} else {
  // No FIFO configured, take logs from stdin
  #echo 'Opening STDIN'.PHP_EOL;                            //No any echo to STDOUT/STDERR!
  $s = fopen('php://stdin', 'r');
}

while ($line = fgets($s))
{
  if (isset($config['syslog']['debug']) && $config['syslog']['debug'])
  {
    // Store RAW syslog line into debug.log
    logfile('debug.log', $line);
  }
  // host || facility || priority || level || tag || timestamp || msg || program
  list($entry['host'], $entry['facility'], $entry['priority'], $entry['level'], $entry['tag'], $entry['timestamp'], $entry['msg'], $entry['program']) = explode("||", trim($line));
  process_syslog($entry, 1);
  unset($entry, $line);
  $i++;
}

// EOF
