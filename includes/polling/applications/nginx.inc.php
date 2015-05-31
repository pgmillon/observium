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

if (!empty($agent_data['app']['nginx']))
{
  $nginx = $agent_data['app']['nginx'];

  $rrd_filename = "app-nginx-".$app['app_id'].".rrd";

  echo(" nginx statistics".PHP_EOL);

  list($active, $reading, $writing, $waiting, $req) = explode("\n", $nginx);

  rrdtool_create($device, $rrd_filename, " \
          DS:Requests:DERIVE:600:0:125000000000 \
          DS:Active:GAUGE:600:0:125000000000 \
          DS:Reading:GAUGE:600:0:125000000000 \
          DS:Writing:GAUGE:600:0:125000000000 \
          DS:Waiting:GAUGE:600:0:125000000000 ");

  rrdtool_update($device, $rrd_filename, "N:$req:$active:$reading:$writing:$waiting");

  unset($nginx,$rrd_filename,$active,$reading,$writing,$req);
}

// EOF
