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
  $sensor = dbFetchRow("SELECT * FROM `sensors` WHERE `sensor_id` = ?", array($vars['id']));

  if (is_numeric($sensor['device_id']) && ($auth || is_entity_permitted($sensor['sensor_id'], 'sensor') || device_permitted($sensor['device_id'])))
  {

    $device = device_by_id_cache($sensor['device_id']);

    $rrd_filename = get_rrd_path($device, get_sensor_rrd($device, $sensor));

    $title  = generate_device_link($device);
    $title .= " :: Sensors :: ";
    $auth = TRUE;
  }
}

// EOF
