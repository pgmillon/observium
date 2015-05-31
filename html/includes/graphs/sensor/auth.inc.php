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
  $sensor = dbFetchRow("SELECT * FROM `sensors` WHERE `sensor_id` = ?", array($vars['id']));

  if (is_numeric($sensor['device_id']) && ($auth || device_permitted($sensor['device_id'])))
  {
    if ($subtype == 'graph')
    {
      // Fix generic subtype
      $subtype = $sensor['sensor_class'];
      $graph_include = $config['html_dir'] . "/includes/graphs/$type/$subtype.inc.php";
    }

    $device = device_by_id_cache($sensor['device_id']);

    $rrd_filename = get_rrd_path($device, get_sensor_rrd($device, $sensor));

    $title  = generate_device_link($device);
    $title .= " :: Sensor :: " . htmlentities($sensor['sensor_descr']);
    $auth = TRUE;
  }
}

// EOF
