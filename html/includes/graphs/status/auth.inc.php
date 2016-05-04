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
  $status = dbFetchRow("SELECT * FROM `status` WHERE `status_id` = ?", array($vars['id']));

  if (is_numeric($status['device_id']) && ($auth || device_permitted($status['device_id'])))
  {

    $device = device_by_id_cache($status['device_id']);

    $rrd_filename = get_rrd_path($device, get_status_rrd($device, $status));

    $title  = generate_device_link($device);
    $title .= " :: Status :: " . escape_html($status['status_descr']);
    $auth = TRUE;
  }
}

// EOF
