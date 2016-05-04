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

if (is_numeric($vars['plugin']))
{
  $mplug = dbFetchRow("SELECT * FROM `munin_plugins` AS M, `devices` AS D WHERE `mplug_id` = ? AND D.device_id = M.device_id ", array($vars['plugin']));
} else {
  $mplug = dbFetchRow("SELECT * FROM `munin_plugins` AS M, `devices` AS D WHERE M.`device_id` = ? AND `mplug_type` = ?  AND D.device_id = M.device_id", array($device['device_id'], $vars['plugin']));
}

if (is_numeric($mplug['device_id']) && ($auth || device_permitted($mplug['device_id'])))
{
  $device = &$mplug;
  $title  = generate_device_link($device);
  $plugfile = get_rrd_path($device, "munin/" . $mplug['mplug_type']);
  $title .= " :: Plugin :: " . $mplug['mplug_type']  . " - " . $mplug['mplug_title'];

  $auth = TRUE;
}

?>
