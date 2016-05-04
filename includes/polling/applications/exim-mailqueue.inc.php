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

// Correct output of the agent script should look like this:
//<<<exim-mailqueue>>>
//frozen:173
//bounces:1052
//total:2496
//active:2323

if (!empty($agent_data['app']['exim-mailqueue']))
{
  $app_id = discover_app($device, 'exim-mailqueue');

  $cnt = $agent_data['app']['exim-mailqueue'];

  foreach (explode("\n",$cnt) as $line)
  {
    list($item,$value) = explode(":",$line,2);
    $cnt_data[trim($item)] = trim($value);
  }

  $rrd_filename = "app-exim-mailqueue-$app_id.rrd";

  // mailqueue count
  rrdtool_create($device, $rrd_filename, " DS:frozen:GAUGE:600:0:1000000\
              DS:bounces:GAUGE:600:0:1000000\
              DS:total:GAUGE:600:0:1000000\
              DS:active:GAUGE:600:0:1000000");

  rrdtool_update($device, $rrd_filename, "N:".$cnt_data['frozen'].":".$cnt_data['bounces'].":".$cnt_data['total'].":".$cnt_data['active']);

  unset($rrd_data,$cnt,$cnt_data);
}

// EOF
