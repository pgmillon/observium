<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if (is_numeric($vars['id']))
{
#  $auth= TRUE;
  $vserver = dbFetchRow("SELECT * FROM `loadbalancer_vservers` AS I, `devices` AS D WHERE I.classmap_id = ? AND I.device_id = D.device_id", array($vars['id']));

  if (is_numeric($vserver['device_id']) && ($auth || device_permitted($vserver['device_id'])))
  {
    $device = device_by_id_cache($vserver['device_id']);

    $rrd_filename = get_rrd_path($device, "vserver-".$vserver['classmap_id'].".rrd");

    $title  = generate_device_link($device);
    $title .= " :: Serverfarm :: " . htmlentities($vserver['classmap_id']);
    $auth = TRUE;
  }
}

// EOF
