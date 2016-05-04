<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

global $agent_sensors;

if ($agent_data['areca']['hw'] != '')
{
  foreach (explode("\n", $agent_data['areca']['hw']) as $line)
  {
    list($key,$content) = explode(':', $line, 2);
    $areca[trim($key)] = trim($content);
  }

  foreach ($areca as $key => $value)
  {
    if ($key == "Battery Status")
    {
      $value = str_replace('%','',$value);
      discover_sensor($valid['sensor'], 'capacity', $device, '', 0, 'areca', "Areca Battery Status", 1, $value, array(), 'agent');
      $agent_sensors['capacity']['areca'][0] = array('description' => "Areca Battery Status", 'current' => $value, 'index' => 0);
    }
    elseif ($key == "Fan#1 Speed (RPM)")
    {
      // Currently doesn't handle more than one fan (but I know of no Areca controllers with >1 fan)
      // Could be done with a regex like below.
      discover_sensor($valid['sensor'], 'fanspeed', $device, '', 1, 'areca', "Areca Fan #1", 1, $value, array(), 'agent');
      $agent_sensors['fanspeed']['areca'][1] = array('description' => "Areca Fan #1", 'current' => $value, 'index' => 1);
    }
    elseif (preg_match("/^HDD\ .*\ Temp\./", $key))
    {
      // Temperature value. Currently not handled as this can be retrieved over SNMP, unlike fan and battery status (for SATA controllers).
    }
  }

  unset($areca);
}

// EOF
