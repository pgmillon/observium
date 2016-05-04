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

foreach (dbFetchRows("SELECT *, A.id AS id FROM `alerts` AS A, `devices` AS D WHERE A.device_id = D.device_id AND alerted = '0'") as $alert)
{
  $id = $alert['id'];
  $host = $alert['hostname'];
  $date = $alert['time_logged'];
  $msg = $alert['message'];
  $alert_text .= "$date $host $msg";

  dbUpdate(array('alerted' => '1'), 'alerts', '`id` = ?', array($id));
}

if ($alert_text)
{
  echo("$alert_text");
  #  `echo '$alert_text' | gnokii --sendsms <NUMBER>`;
}

// EOF
