<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// HOST-RESOURCES-MIB
//  Generic System Statistics

$oid_list = "hrSystemProcesses.0 hrSystemNumUsers.0";
$hrSystem  = snmp_get_multi ($device, $oid_list, "-OUQs", "HOST-RESOURCES-MIB");

echo("HR Stats:");

if (is_numeric($hrSystem[0]['hrSystemProcesses']))
{
  $rrd_file = "hr_processes.rrd";
  rrdtool_create($device, $rrd_file,"DS:procs:GAUGE:600:0:U ");
  rrdtool_update($device, $rrd_file,  "N:".$hrSystem[0]['hrSystemProcesses']);
  $graphs['hr_processes'] = TRUE;
  echo(" Processes");
}

if (is_numeric($hrSystem[0]['hrSystemNumUsers']))
{
  $rrd_file = "hr_users.rrd";
  rrdtool_create($device, $rrd_file,"DS:users:GAUGE:600:0:U ");
  rrdtool_update($device, $rrd_file,  "N:".$hrSystem[0]['hrSystemNumUsers']);
  $graphs['hr_users'] = TRUE;
  echo(" Users");
}

echo(PHP_EOL);

// EOF
