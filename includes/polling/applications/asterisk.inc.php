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

if (!empty($agent_data['app']['asterisk']))
{
  $rrd_filename = "app-asterisk-".$app['app_id'].".rrd";

  $data = array(
    'activechan' => 0,
    'activecall' => 0,
    'iaxchannels' => 0,
    'sipchannels' => 0,
    'sippeers' => 0,
    'sippeersonline' => 0,
    'iaxpeers' => 0,
    'iaxpeersonline' => 0,
  );

//  list ($activechan,$activecall,$iaxchannels,$sipchannels,$sippeers,$sippeersonline,$iaxpeers,$iaxpeersonline) = explode("\n", $agent_data['app']['asterisk']);

  $lines = explode("\n", $agent_data['app']['asterisk']);
  foreach ($lines as $line)
  {
    list($key, $val) = explode(":", $line);
    $key = trim($key);
    if (isset($data[$key])) { $data[$key] = intval(trim($val)); }
  }

  rrdtool_create($device, $rrd_filename, "\
        DS:activechan:GAUGE:600:0:125000000000 \
        DS:activecall:GAUGE:600:0:125000000000 \
        DS:iaxchannels:GAUGE:600:0:125000000000 \
        DS:sipchannels:GAUGE:600:0:125000000000 \
        DS:sippeers:GAUGE:600:0:125000000000 \
        DS:sippeersonline:GAUGE:600:0:125000000000 \
        DS:iaxpeers:GAUGE:600:0:125000000000 \
        DS:iaxpeersonline:GAUGE:600:0:125000000000");

  $rrd_update = 'N';
  foreach ($data as $param => $value)
  {
    $rrd_update .= ':'.$value;
  }

  rrdtool_update($device, $rrd_filename, $rrd_update);
}

// EOF
