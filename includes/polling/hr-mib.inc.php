<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

//  Generic System Statistics

if (is_device_mib($device, 'HOST-RESOURCES-MIB'))
{
  $oid_list = "hrSystemProcesses.0 hrSystemNumUsers.0";
  $hrSystem  = snmp_get_multi ($device, $oid_list, "-OUQs", "HOST-RESOURCES-MIB");

  print_cli_data_field("Collecting", 2);

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
} # end is_device_mib()

// EOF
