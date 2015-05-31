<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

global $agent_sensors;

if ($agent_data['array'] != '|')
{
  $items = explode("\n",$agent_data['hdarray']);
  echo "hdarray: " . print_r($items);

  if (count($items))
  {
    foreach ($items as $item)
    {
      list($param, $status) = explode('=', $item, 2);
      $itemcount++;
      if ($status === 'Ok')
      {
        $istatus=1;
      } else {
        $istatus=0;
      }
      echo "Status: $status istatus: $istatus";
      if ($param==='Controller Status')
      {
        discover_sensor($valid['sensor'], 'state', $device, '', $itemcount, 'unix-agent-state', "$param: $status", NULL, $istatus, array('entPhysicalClass' => 'controller'), 'agent');
        $agent_sensors['status']['state'][$itemcount] = array('description' => "$param: $status", 'current' => $istatus, 'index' => $itemcount);
      }
      if (preg_match("/^Drive/","$param"))
      {
        discover_sensor($valid['sensor'], 'state', $device, '', $itemcount, 'unix-agent-state', "$param: $status", NULL, $istatus, array('entPhysicalClass' => 'storage'), 'agent');
        $agent_sensors['status']['state'][$itemcount] = array('description' => "$param: $status", 'current' => $istatus, 'index' => $itemcount);
      }
    }
    echo "\n";
  }
}

// EOF
