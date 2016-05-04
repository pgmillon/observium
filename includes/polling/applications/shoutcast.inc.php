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

if (!empty($agent_data['app']['shoutcast']))
{
  $app_id = discover_app($device, 'shoutcast');

  // Polls shoutcast statistics from agent script
  $shoutcast = $agent_data['app']['shoutcast'];

  $servers = explode("\n", $shoutcast);

  foreach ($servers as $item=>$server)
  {
    $server = trim($server);

    if (!empty($server))
    {
      $data              = explode(";", $server);
      list($host, $port) = explode(":", $data['0'], 2);
      $bitrate           = $data['1'];
      $traf_in           = $data['2'];
      $traf_out          = $data['3'];
      $current           = $data['4'];
      $status            = $data['5'];
      $peak              = $data['6'];
      $max               = $data['7'];
      $unique            = $data['8'];
      $rrdfile           = "app-shoutcast-$app_id-".$host."_".$port.".rrd";

      rrdtool_create($device, $rrdfile, " \
                  DS:bitrate:GAUGE:600:0:125000000000 \
                  DS:traf_in:GAUGE:600:0:125000000000 \
                  DS:traf_out:GAUGE:600:0:125000000000 \
                  DS:current:GAUGE:600:0:125000000000 \
                  DS:status:GAUGE:600:0:125000000000 \
                  DS:peak:GAUGE:600:0:125000000000 \
                  DS:max:GAUGE:600:0:125000000000 \
                  DS:unique:GAUGE:600:0:125000000000 ");

      rrdtool_update($device, $rrdfile, "N:$bitrate:$traf_in:$traf_out:$current:$status:$peak:$max:$unique");
    }
  }
}

// EOF
