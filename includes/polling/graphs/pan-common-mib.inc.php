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

$graph = 'panos_sessions'; // Current graph
$graphs[$graph] = FALSE; // Disable graph by default

if (!isset($graphs_db[$graph]) || $graphs_db[$graph] === TRUE)
{
  $session_count = snmp_get($device, 'panSessionActive.0', '-OQUvs', 'PAN-COMMON-MIB');

  if (is_numeric($session_count))
  {
    $rrd_filename  = 'panos-sessions.rrd';

    rrdtool_create($device, $rrd_filename, ' DS:sessions:GAUGE:600:0:100000000 ');
    rrdtool_update($device, $rrd_filename, 'N:'.$session_count);

    $graphs[$graph] = TRUE;
  }
}

unset($graph, $session_count);

// EOF
