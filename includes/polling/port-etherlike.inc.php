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

if ($port_stats[$port['ifIndex']] && $port['ifType'] == "ethernetCsmacd"
   && isset($port_stats[$port['ifIndex']]['dot3StatsIndex']))
{ // Check to make sure Port data is cached.

  $this_port = &$port_stats[$port['ifIndex']];

  // CLEANME remove rename after r6000
  $old_rrdfile = get_rrd_path($device, "etherlike-".$port['ifIndex'].".rrd");
  $rrdfile = get_port_rrdfilename($port, "dot3");

  if (!is_file($rrdfile) && is_file(get_rrd_path($device, $old_rrdfile)))
  {
    rename($old_rrdfile, $rrdfile);
  }

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
