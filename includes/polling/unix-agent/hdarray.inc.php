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

if ($agent_data['array'] != '|')
{
  $items = explode("\n", $agent_data['hdarray']);
  echo "hdarray: " . print_r($items);

  if (count($items))
  {
    foreach ($items as $item)
    {
      list($param, $status) = explode('=', $item, 2);
      $itemcount++; // Note this is not best index
      switch ($status)
      {
        case 'Ok':
          $istatus = 1;
          break;
        case 'Non-Critical':
          // Warn
          $istatus = 2;
          break;
        default:
          // Fail
          $istatus = 0;
      }
      echo "Status: $status istatus: $istatus";
      if ($param == 'Controller Status')
      {
        discover_sensor($valid['sensor'], 'state', $device, '', $itemcount, 'unix-agent-state', $param, NULL, $istatus, array('entPhysicalClass' => 'controller'), 'agent');
        $agent_sensors['state']['unix-agent-state'][$itemcount] = array('description' => $param, 'current' => $istatus, 'index' => $itemcount);
      }
      else if (preg_match('/^Drive \d/', $param))
      {
        discover_sensor($valid['sensor'], 'state', $device, '', $itemcount, 'unix-agent-state', $param, NULL, $istatus, array('entPhysicalClass' => 'storage'), 'agent');
        $agent_sensors['state']['unix-agent-state'][$itemcount] = array('description' => $param, 'current' => $istatus, 'index' => $itemcount);
      }
    }
    echo PHP_EOL;
  }
}

// EOF
