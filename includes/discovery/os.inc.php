<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

if ($detect_os)
{
  $os = get_device_os($device);

  if ($os != $device['os'])
  {
    $type = (isset($config['os'][$os]['type']) ? $config['os'][$os]['type'] : 'unknown'); // Also change $type
    print_warning("Device OS changed: ".$device['os']." -> $os!");
    log_event('OS changed: '.$device['os'].' -> '.$os, $device, 'device', $device['device_id'], 'warning');
    dbUpdate(array('os' => $os), 'devices', '`device_id` = ?', array($device['device_id']));
    $device['os'] = $os; $device['type'] = $type;
  }
}

// EOF
