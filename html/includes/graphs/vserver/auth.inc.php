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
  $vserver = dbFetchRow("SELECT * FROM `loadbalancer_vservers` AS WHERE `classmap_id` = ?", array($vars['id']));

  if (is_numeric($vserver['device_id']) && ($auth || device_permitted($vserver['device_id'])))
  {
    $device = device_by_id_cache($vserver['device_id']);

    $rrd_filename = get_rrd_path($device, "vserver-".$vserver['classmap_index'].".rrd");

    $title  = generate_device_link($device);
    $title .= " :: Serverfarm :: " . escape_html($vserver['classmap']);
    $auth = TRUE;
  }
}

// EOF
