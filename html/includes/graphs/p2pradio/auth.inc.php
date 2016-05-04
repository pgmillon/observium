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
  $radio = dbFetchRow("SELECT * FROM `p2p_radios` WHERE `radio_id` = ?", array($vars['id']));

  if (is_numeric($radio['device_id']) && ($auth || device_permitted($radio['device_id'])))
  {

    $device = device_by_id_cache($radio['device_id']);

    $rrd_filename = get_rrd_path($device, "p2p_radio-" . $radio['radio_mib'] . "-" . $radio['radio_index'] . ".rrd");

    $title  = generate_device_link($device);
    $title .= " :: P2P Radio :: " . escape_html($radio['radio_name']);
    $auth = TRUE;
  }
}

// EOF
