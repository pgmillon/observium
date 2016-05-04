<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

if ($port_stats[$port['ifIndex']] && $port['ifType'] == "ethernetCsmacd"
   && isset($port_stats[$port['ifIndex']]['dot3StatsIndex']))
{ // Check to make sure Port data is cached.

  $this_port = &$port_stats[$port['ifIndex']];

  foreach ($etherlike_oids as $oid)
  {
    $oid = truncate(str_replace("dot3Stats", "", $oid), 19, '');
    $rrd_create .= " DS:$oid:COUNTER:600:U:100000000000";
  }

  rrdtool_create($device, $rrdfile, $rrd_create);

  if ($config['statsd']['enable'] == TRUE)
  {
    foreach ($etherlike_oids as $oid)
    {
      // Update StatsD/Carbon
      StatsD::gauge(str_replace(".", "_", $device['hostname']).'.'.'port'.'.'.$port['ifIndex'].'.'.$oid, $this_port[$oid]);
    }
  }

  $rrdupdate = "N";
  foreach ($etherlike_oids as $oid)
  {
    $data = $this_port[$oid] + 0;
    $rrdupdate .= ":$data";
  }
  rrdtool_update($device, $rrdfile, $rrdupdate);

  echo("EtherLike ");
}

// EOF
