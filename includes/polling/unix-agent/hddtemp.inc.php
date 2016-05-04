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

if ($agent_data['hddtemp'] != '|')
{
  $disks = explode('||',trim($agent_data['hddtemp'],'|'));

  if (count($disks))
  {
    echo "hddtemp: ";
    foreach ($disks as $disk)
    {
      list($blockdevice, $descr, $value, $unit) = explode('|', $disk, 4);
      # FIXME: should not use diskcount as index; drive serial preferred but hddtemp does not supply it.
      # Device name itself is just as useless as the actual position however.
      # In case of change in index, please provide an rrd-rename upgrade-script.
      ++$diskcount;
      discover_sensor($valid['sensor'], 'temperature', $device, '', $diskcount, 'hddtemp', "$blockdevice: $descr", 1, $value, array(), 'agent');
      $agent_sensors['temperature']['hddtemp'][$diskcount] = array('description' => "$blockdevice: $descr", 'current' => $value, 'index' => $diskcount);
    }
    echo "\n";
  }
}

// EOF
