<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// Time our cache filling.
$cache_start = microtime(true);

// Devices
$devices = array('count'    => 0,
                 'up'       => 0,
                 'down'     => 0,
                 'ignored'  => 0,
                 'disabled' => 0);
$cache['devices'] = array('id'        => array(),
                          'hostname'  => array(),
                          'permitted' => array(),
                          'ignored'   => array(),
                          'disabled'  => array());
foreach (dbFetchRows("SELECT * FROM `devices` ORDER BY `hostname`") as $device)
{
  if (device_permitted($device['device_id']))
  {
    // Process device and add all the human-readable stuff.
    humanize_device($device);

    $cache['devices']['permitted'][] = (int)$device['device_id']; // Collect IDs for permitted
    $cache['devices']['hostname'][$device['hostname']] = $device['device_id'];
    $cache['devices']['id'][$device['device_id']] = $device;

    if ($device['disabled'])
    {
      $devices['disabled']++;
      $cache['devices']['disabled'][] = (int)$device['device_id']; // Collect IDs for disabled
      if (!$config['web_show_disabled']) { continue; }
    }

    if ($device['ignore'])
    {
      $devices['ignored']++;
      $cache['devices']['ignored'][] = (int)$device['device_id']; // Collect IDs for ignored
    }

    $devices['count']++;

    if (!$device['ignore'])
    {
      if ($device['status']) { $devices['up']++; }
      else { $devices['down']++; }
    }

    $cache['devices']['timers']['polling'] += $device['last_polled_timetaken'];
    $cache['devices']['timers']['discovery'] += $device['last_discovered_timetaken'];

    $cache['device_types'][$device['type']]++;
    $cache['device_locations'][$device['location']]++;

    $cache['locations']['entries'][$device['location_country']]['count']++;
    $cache['locations']['entries'][$device['location_country']]['level'] = 'location_country';

    $cache['locations']['entries'][$device['location_country']]['entries'][$device['location_state']]['count']++;
    $cache['locations']['entries'][$device['location_country']]['entries'][$device['location_state']]['level'] = 'location_state';

    $cache['locations']['entries'][$device['location_country']]['entries'][$device['location_state']]['entries'][$device['location_county']]['count']++;
    $cache['locations']['entries'][$device['location_country']]['entries'][$device['location_state']]['entries'][$device['location_county']]['level'] = 'location_county';

    $cache['locations']['entries'][$device['location_country']]['entries'][$device['location_state']]['entries'][$device['location_county']]['entries'][$device['location_city']]['count']++;
    $cache['locations']['entries'][$device['location_country']]['entries'][$device['location_state']]['entries'][$device['location_county']]['entries'][$device['location_city']]['level'] = 'location_city';

    $cache['locations']['entries'][$device['location_country']]['entries'][$device['location_state']]['entries'][$device['location_county']]['entries'][$device['location_city']]['entries'][$device['location']]['count']++;
    $cache['locations']['entries'][$device['location_country']]['entries'][$device['location_state']]['entries'][$device['location_county']]['entries'][$device['location_city']]['entries'][$device['location']]['level'] = 'location';

  }
}

// Ports
$ports = array('count'    => 0,
               'up'       => 0,
               'down'     => 0,
               'ignored'  => 0,
               'disabled' => 0,
               'errored'  => 0,
               'alerts'   => 0,
               'deleted'  => 0);

$cache['ports'] = array(//'id'        => array(),
                        'permitted' => array(),
                        'ignored'   => array(),
                        'errored'   => array(),
                        //'disabled'  => array(),
                        'poll_disabled' => array(),
                        'device_ignored' => array(),
                        'device_disabled' => array(),
                        'deleted'   => array());

$ports_array = dbFetchRows("SELECT `device_id`, `ports`.`port_id`, `ifAdminStatus`, `ifOperStatus`, `deleted`, `ignore`, `ifOutErrors_delta`, `ifInErrors_delta` FROM `ports`
                            LEFT JOIN `ports-state` ON `ports`.`port_id` = `ports-state`.`port_id`");
port_permitted_array($ports_array);

foreach ($ports_array as $port)
{
  if (!$config['web_show_disabled'])
  {
    if ($cache['devices']['id'][$port['device_id']]['disabled'])
    {
      $cache['ports']['device_disabled'][] = (int)$port['port_id']; // Collect IDs for disabled device
      continue;
    }
  }

  if ($cache['devices']['id'][$port['device_id']]['ignore'])
  {
    $cache['ports']['device_ignored'][] = (int)$port['port_id']; // Collect IDs for ignored device
  }

  $ports['count']++;
  $cache['ports']['permitted'][] = (int)$port['port_id']; // Collect IDs for permitted

  if ($port['deleted'])
  {
    $ports['deleted']++;
    $cache['ports']['deleted'][] = (int)$port['port_id']; // Collect IDs for deleted
    continue; // Don't count port statuses if port deleted
  }

  if (!$cache['devices']['id'][$port['device_id']]['status'])
  {
    // Don't count up/down/alerts/errored ports if device down
  }
  elseif ($port['ifAdminStatus'] == "down")
  {
    $ports['disabled']++;
    //$cache['ports']['disabled'][] = (int)$port['port_id']; // Collect IDs for disabled
  } else {
    if ($port['ifOperStatus'] == "up" || $port['ifOperStatus'] == "monitoring")
    {
      $ports['up']++;
      if ($port['ifOutErrors_delta'] > 0 || $port['ifInErrors_delta'] > 0 )
      {
        $ports['errored']++;
        $cache['ports']['errored'][] = (int)$port['port_id']; // Collect IDs for errored
      }
    }
    if ($port['ifOperStatus'] == "down" || $port['ifOperStatus'] == "lowerLayerDown")
    {
      #$ports['down']++;
      #if (!$port['ignore']) { $ports['alerts']++; }
      if (!$port['ignore']) { $ports['alerts']++; $ports['down']++; }

    }
  }

  if ($port['disabled'])
  {
    $cache['ports']['poll_disabled'][] = (int)$port['port_id']; // Collect IDs for poll disabled
  }
  if ($port['ignore'])
  {
    $ports['ignored']++;
    $cache['ports']['ignored'][] = (int)$port['port_id']; // Collect IDs for ignored
  }
}

// Sensors
$sensors = array('count'    => 0,
                 'up'       => 0,
                 'alert'    => 0,
                 'warning'  => 0,
                 'ignored'  => 0,
                 'disabled' => 0);
$cache['sensor_types'] = array();

foreach (dbFetchRows('SELECT * FROM `sensors` LEFT JOIN `sensors-state` ON `sensors`.`sensor_id` = `sensors-state`.`sensor_id`') as $sensor)
{
  if (!isset($cache['devices']['id'][$sensor['device_id']])) { continue; } // Check device permitted

  if (!$config['web_show_disabled'])
  {
    if ($cache['devices']['id'][$sensor['device_id']]['disabled']) { continue; }
  }

  humanize_sensor($sensor);
  $sensors['count']++;
  $cache['sensor_types'][$sensor['sensor_class']]['count']++;

  if ($sensor['sensor_disable']) { $sensors['disabled']++; }

  if ($sensor['sensor_state'] && $sensor['state_event'] == 'ignore' && !$sensor['sensor_disable'])
  {
    $sensor['sensor_ignore'] = 1;  // Trust me. --mike
  }
  if ($sensor['sensor_ignore'])  { $sensors['ignored']++; }

  switch ($sensor['state_event'])
  {
    case 'warning':
      $sensors['warning']++; // 'warning' also 'up'
    case 'up':
      $sensors['up']++;
      break;
    case 'alert':
      if (!$sensor['sensor_ignore'])
      {
        $sensors['alert']++;
        $cache['sensor_types'][$sensor['sensor_class']]['alert']++;
      }
      break;
  }
}

// Routing
// BGP
if (isset($config['enable_bgp']) && $config['enable_bgp'])
{
  $routing['bgp']['last_seen'] = $config['time']['now'];
  foreach (dbFetchRows('SELECT `device_id`,`bgpPeerState`,`bgpPeerAdminStatus`,`bgpPeerRemoteAs` FROM bgpPeers') as $bgp)
  {
    if (!$config['web_show_disabled'])
    {
      if ($cache['devices']['id'][$bgp['device_id']]['disabled']) { continue; }
    }
    if (device_permitted($bgp))
    {
      $routing['bgp']['count']++;
      if ($bgp['bgpPeerAdminStatus'] == 'start' || $bgp['bgpPeerAdminStatus'] == 'running')
      {
        $routing['bgp']['up']++;
        if ($bgp['bgpPeerState'] != 'established')
        {
          $routing['bgp']['alerts']++;
        }
      } else {
        $routing['bgp']['down']++;
      }
      if ($cache['devices']['id'][$bgp['device_id']]['bgpLocalAs'] == $bgp['bgpPeerRemoteAs'])
      {
        $routing['bgp']['internal']++;
      } else {
        $routing['bgp']['external']++;
      }
    }
  }
}

// OSPF
if (isset($config['enable_ospf']) && $config['enable_ospf'])
{
  $routing['ospf']['last_seen'] = $config['time']['now'];
  foreach (dbFetchRows("SELECT `device_id`,`ospfAdminStat` FROM `ospf_instances`") as $ospf)
  {
    if (!$config['web_show_disabled'])
    {
      if ($cache['devices']['id'][$ospf['device_id']]['disabled']) { continue; }
    }
    if (device_permitted($ospf))
    {
      $routing['ospf']['count']++;
      if ($ospf['ospfAdminStat'] == 'enabled')
      {
        $routing['ospf']['up']++;
      } else {
        $routing['ospf']['down']++;
      }
    }
  }
}

// CEF
$routing['cef']['count'] = dbFetchCell("SELECT COUNT(cef_switching_id) from `cef_switching`");
// VRF
$routing['vrf']['count'] = dbFetchCell("SELECT COUNT(vrf_id) from `vrfs`");

$cache_end  = microtime(true);
$cache_time = number_format($cache_end - $cache_start, 3);

// Clean arrays (from DB queries)
unset($ports_array);
// Clean variables (generated by foreach)
unset($device, $port, $sensor, $bgp, $ospf);

// EOF
