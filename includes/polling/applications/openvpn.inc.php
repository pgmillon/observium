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

if (is_array($agent_data['app']['openvpn']))
{
  foreach ($agent_data['app']['openvpn'] as $key => $entry)
  {
    if (substr($key,0,9) == 'loadstats')
    {
      list(,$instance) = explode('-',$key,2);

      $loadstats[$instance] = array();

      # SUCCESS: nclients=1,bytesin=484758,bytesout=180629
      foreach (explode(',', str_replace('SUCCESS: ','', $entry)) as $keyvalue)
      {
        list($key, $value) = explode('=', $keyvalue, 2);
        $loadstats[$instance][$key] = $value;
      }
    }
  }
}

foreach ($loadstats as $instance => $data)
{
  discover_app($device, 'openvpn', $instance);

  $rrd_filename = "app-openvpn-" . $instance . ".rrd";
  $rrd_values = array();

  foreach (array('nclients', 'bytesin', 'bytesout') as $key)
  {
    $rrd_values[] = (is_numeric($data[$key]) ? $data[$key] : "U");
  }

  rrdtool_create($device, $rrd_filename, " \
        DS:nclients:GAUGE:600:0:U \
        DS:bytesin:DERIVE:600:0:125000000000 \
        DS:bytesout:DERIVE:600:0:125000000000 ");

  rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));
}

unset($loadstats, $rrd_values, $rrd_filename);

// EOF
