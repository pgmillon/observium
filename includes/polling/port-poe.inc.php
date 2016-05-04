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

// This code is currently not used.

if (isset($port_stats[$port['ifIndex']]) && $port['ifType'] == "ethernetCsmacd")
{ // Check to make sure Port data is cached.

    $this_port = &$port_stats[$port['ifIndex']];

    $rrdfile = get_port_rrdfilename($port, "poe");

    // FIXME CISCOSPECIFIC
    $rrd_create .= " DS:PortPwrAllocated:GAUGE:600:0:U";
    $rrd_create .= " DS:PortPwrAvailable:GAUGE:600:0:U";
    $rrd_create .= " DS:PortConsumption:DERIVE:600:0:U";
    $rrd_create .= " DS:PortMaxPwrDrawn:GAUGE:600:0:U ";

    rrdtool_create($device, $rrdfile, $rrd_create);

    if ($config['statsd']['enable'] == TRUE)
    {
      foreach (array('cpeExtPsePortPwrAllocated', 'cpeExtPsePortPwrAvailable', 'cpeExtPsePortPwrConsumption', 'cpeExtPsePortMaxPwrDrawn') as $oid)
      {
        // Update StatsD/Carbon
        StatsD::gauge(str_replace(".", "_", $device['hostname']).'.'.'port'.'.'.$port['ifIndex'].'.'.$oid, $this_port[$oid]);
      }
    }

    $upd = "$polled:".$port['cpeExtPsePortPwrAllocated'].":".$port['cpeExtPsePortPwrAvailable'].":".$port['cpeExtPsePortPwrConsumption'].":".$port['cpeExtPsePortMaxPwrDrawn'];
    $ret = rrdtool_update($device, "$rrdfile", $upd);

    echo("PoE ");
  }

// EOF
