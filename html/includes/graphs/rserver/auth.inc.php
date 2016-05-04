<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if (is_numeric($vars['id']))
{
#  $auth= TRUE;
  $rserver = dbFetchRow("SELECT * FROM `loadbalancer_rservers` WHERE `rserver_id` = ?", array($vars['id']));

  if (is_numeric($rserver['device_id']) && ($auth || device_permitted($rserver['device_id'])))
  {
    if ($rserver['state'])
    {
      $rserver = array_merge($rserver, json_decode($rserver['state'], TRUE));
    }
    $device = device_by_id_cache($rserver['device_id']);

    $rrd_filename = get_rrd_path($device, "rserver-".$rserver['rserver_id'].".rrd");

    $title  = generate_device_link($device);
    $title .= " :: Rserver :: " . escape_html($rserver['ServerFarmName'] . ' - ' . $rserver['Name']);
    $auth = TRUE;
  }
}

// EOF
