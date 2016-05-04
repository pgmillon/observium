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

// EtherLike-MIB functions

// Process in main ports loop
function process_port_etherlike(&$this_port, $device)
{
  $etherlike_oids = array(
    'dot3StatsAlignmentErrors', 'dot3StatsFCSErrors', 'dot3StatsSingleCollisionFrames', 'dot3StatsMultipleCollisionFrames',
    'dot3StatsSQETestErrors', 'dot3StatsDeferredTransmissions', 'dot3StatsLateCollisions', 'dot3StatsExcessiveCollisions',
    'dot3StatsInternalMacTransmitErrors', 'dot3StatsCarrierSenseErrors', 'dot3StatsFrameTooLongs', 'dot3StatsInternalMacReceiveErrors',
    'dot3StatsSymbolErrors'
  );

  // Overwrite ifDuplex with dot3StatsDuplexStatus if it exists
  if (isset($this_port['dot3StatsDuplexStatus']))
  {
    // echo("dot3Duplex, ");
    $this_port['ifDuplex'] = $this_port['dot3StatsDuplexStatus'];
  }

  if ($this_port['ifType'] == "ethernetCsmacd" && isset($this_port['dot3StatsIndex']))
  { // Check to make sure Port data is cached.

    $rrd_create = "";
    foreach ($etherlike_oids as $oid)
    {
      $oid = truncate(str_replace("dot3Stats", "", $oid), 19, '');
      $rrd_create .= " DS:$oid:COUNTER:600:U:100000000000";
    }

    $rrdfile_dot3 = get_port_rrdfilename($this_port, "dot3");

    rrdtool_create($device, $rrdfile_dot3, $rrd_create);

    if ($GLOBALS['config']['statsd']['enable'] == TRUE)
    {
      foreach ($etherlike_oids as $oid)
      {
        // Update StatsD/Carbon
        StatsD::gauge(str_replace(".", "_", $device['hostname']).'.'.'port'.'.'.$this_port['ifIndex'].'.'.$oid, $this_port[$oid]);
      }
    }

    $rrdupdate = "N";
    foreach ($etherlike_oids as $oid)
    {
      $data = $this_port[$oid] + 0;
      $rrdupdate .= ":$data";
    }
    rrdtool_update($device, $rrdfile_dot3, $rrdupdate);
  }
}

// EOF
