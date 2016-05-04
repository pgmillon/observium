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

if (!empty($agent_data['app']['drbd'][$app['app_instance']]))
{
  $app_id = discover_app($device, 'drbd', $app['app_instance']);

  $rrd_filename = "app-drbd-".$app['app_instance'].".rrd";

  foreach (explode("|", $agent_data['app']['drbd'][$app['app_instance']]) as $part)
  {
    list($stat, $val) = explode("=", $part, 2);
    if (!empty($stat))
    {
      $drbd[$stat] = $val;
    }
  }

  rrdtool_create($device, $rrd_filename, " \
        DS:ns:DERIVE:600:0:125000000000 \
        DS:nr:DERIVE:600:0:125000000000 \
        DS:dw:DERIVE:600:0:125000000000 \
        DS:dr:DERIVE:600:0:125000000000 \
        DS:al:DERIVE:600:0:125000000000 \
        DS:bm:DERIVE:600:0:125000000000 \
        DS:lo:GAUGE:600:0:125000000000 \
        DS:pe:GAUGE:600:0:125000000000 \
        DS:ua:GAUGE:600:0:125000000000 \
        DS:ap:GAUGE:600:0:125000000000 \
        DS:oos:GAUGE:600:0:125000000000 ");

  $ds_list = array('ns','nr','dw','dr','al','bm','lo','pe','ua','ap','oos');
  foreach ($ds_list as $ds)
  {
    if (!is_numeric($drbd[$ds]))
    {
      $drbd[$ds] = "U";
    }
  }

  rrdtool_update($device, $rrd_filename, "N:".$drbd['ns'].":".$drbd['nr'].":".$drbd['dw'].":".$drbd['dr'].":".$drbd['al'].":".$drbd['bm'].":".$drbd['lo'].":".$drbd['pe'].":".$drbd['ua'].":".$drbd['ap'].":".$drbd['oos']);
  unset($drbd);
}

// EOF
