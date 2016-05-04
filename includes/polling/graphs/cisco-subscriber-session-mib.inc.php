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

echo(" CISCO-SUBSCRIBER-SESSION-MIB ");
$graph = 'bng_active_sessions'; // Current graph
$graphs[$graph] = FALSE;           // Disable graph by default

if (!isset($graphs_db[$graph]) || $graphs_db[$graph] === TRUE)
{
  //walk BNG-sessions from all RSPs
  $rsp_sessions = snmpwalk_cache_oid($device, "1.3.6.1.4.1.9.9.786.1.2.1.1.5.1", array(), "CISCO-SUBSCRIBER-SESSION-MIB", mib_dirs("cisco"));
  //the active RSP will have most or all of the sessions, return only the value for the active RSP
  $session_count = max($rsp_sessions);
  if (is_numeric($session_count['csubAggStatsUpSessions']))
  {
    $rrd_filename  = "bng-active-sessions.rrd";

    rrdtool_create($device, $rrd_filename, " DS:value:GAUGE:600:0:100000000 ");
    rrdtool_update($device, $rrd_filename, "N:".$session_count['csubAggStatsUpSessions']);

    $graphs[$graph] = TRUE;
  }
}

unset($graph, $session_count);

// EOF
