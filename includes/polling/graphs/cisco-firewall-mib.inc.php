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

$graph = 'firewall_sessions_ipv4'; // Current graph
$graphs[$graph] = FALSE;           // Disable graph by default

if (!isset($graphs_db[$graph]) || $graphs_db[$graph] === TRUE)
{
  $session_count = snmp_get($device, ".1.3.6.1.4.1.9.9.147.1.2.2.2.1.5.40.6", "-OQUvs", "CISCO-FIREWALL-MIB", mib_dirs("cisco"));

  if (is_numeric($session_count))
  {
    $rrd_filename  = "firewall-sessions-ipv4.rrd";

    rrdtool_create($device, $rrd_filename, " DS:value:GAUGE:600:0:100000000 ");
    rrdtool_update($device, $rrd_filename, "N:".$session_count);

    $graphs[$graph] = TRUE;
  }
}

unset($graph, $session_count);

// EOF
