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

  $vsvr = dbFetchRow("SELECT * FROM `netscaler_vservers` AS I, `devices` AS D WHERE I.vsvr_id = ? AND I.device_id = D.device_id", array($vars['id']));

  if (is_numeric($vsvr['device_id']) && ($auth || device_permitted($vsvr['device_id'])))
  {
    $device = device_by_id_cache($vsvr['device_id']);

    $rrd_filename = get_rrd_path($device, "netscaler-vsvr-".$vsvr['vsvr_name'].".rrd");

    $title  = generate_device_link($device);
    $title .= " :: Netscaler VServer :: " . escape_html($vsvr['vsvr_name']);
    $auth = TRUE;
  }
}

// EOF
