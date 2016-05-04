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
  $toner = dbFetchRow("SELECT * FROM `toner` WHERE `toner_id` = ?", array($vars['id']));

  if (is_numeric($toner['device_id']) && ($auth || device_permitted($toner['device_id'])))
  {
    $device = device_by_id_cache($toner['device_id']);
    $rrd_filename  = get_rrd_path($device, "toner-" . $toner['toner_index'] . ".rrd");

    $title  = generate_device_link($device);
    $title .= " :: Toner :: " . escape_html($toner['toner_descr']);
    $auth = TRUE;
  }
}

// EOF
