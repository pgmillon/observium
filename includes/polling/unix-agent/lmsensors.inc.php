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

if ($agent_data['lmsensors'] != '|')
{
  $array = preg_split("/\n/", $agent_data['lmsensors'], -1, PREG_SPLIT_NO_EMPTY);

  foreach ($array as $line)
  {
    if (preg_match("/(.*):(.*)\((.*)=(.*),(.*)=(.*)\)(.*)/", $line, $data)) {}
    elseif (preg_match("/(.*):(.*)\((.*)=(.*)\)(.*)/", $line, $data)) {}
    elseif (preg_match("/(.*):(.*)/", $line, $data)) {}
    foreach ($data as $key=>$value)
    {
      $data[$key] = trim($value);
    }

    if (count($data) > 2)
    {
      preg_match('/[a-zA-Z]+$/', $data[2], $unit);

      $array['scale'] = 1;
      switch (trim($unit[0]))
      {
        case "F":
          $array['class'] = "temperature";
          $array['scale'] = 5/9;
          break;
        case "C":
          $array['class'] = "temperature";
          break;
        case "RPM":
          $array['class'] = "fanspeed";
          break;
        case "V":
          $array['class'] = "voltage";
          break;
      }

      array_shift($data); // Remove useless line
      $array['descr'] = array_shift($data); // Set Description.
      $array['current'] = preg_replace('/[^0-9\.\-]/', '', array_shift($data));

      while ($value = array_shift($data))
      {
        switch($value)
        {
          case "low":
          case "high":
          case "crit":
          case "warn":
          case "hyst":
           $array[$value] = preg_replace('/[^0-9\.\-]/', '', array_shift($data));
           break;
        }
      }
      if ($array['class'] == "temperature" && $array['scale'] < 1)
      {
        //$array['current'] = f2c($array['current']);
        $array['high']    = f2c($array['high']);
        $array['low']     = f2c($array['low']);
      }
    }

    if (isset($array) && isset($array['class']))
    {
      $sensors_array[$array['descr']] = $array;
    }
    unset($array);
  }

  foreach ($sensors_array as $key => $array)
  {
    $limits = array('limit_high' => $array['high'], 'limit_low' => $array['low']);
    discover_sensor($valid['sensor'], $array['class'], $device, '', $key, 'lmsensors', $array['descr'], $array['scale'], $array['current'], $limits, 'agent');
    if ($array['class'] == "temperature" && $array['scale'] < 1)
    {
      $array['current'] = f2c($array['current']);
    }
    $agent_sensors[$array['class']]['lmsensors'][$key] = array('description' => $array['descr'], 'current' => $array['current'], 'index' => $key);
  }

  #print_r($sensors_array);
}

// EOF
