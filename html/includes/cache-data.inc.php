<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2015 Adam Armstrong
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

// This code fetches all devices and fills the cache array.
// This means device_by_id_cache actually never has to do any queries by itself, it'll always get the
// cached version when running from the web interface. From the commandline obviously we'll need to fetch
// the data per-device. We pre-fetch the graphs list as well, much faster than a query per device obviously.
if (get_db_version() >= 186)
{
  // FIXME. remove check db_version in r7000
  $graphs_array = dbFetchRows("SELECT * FROM `device_graphs` FORCE INDEX (`graph`) ORDER BY `graph`;");
} else {
  $graphs_array = dbFetchRows("SELECT * FROM `device_graphs` ORDER BY `graph`;");
}
foreach ($graphs_array as $graph)
{
  // Cache this per device_id so we can assign it to the correct (cached) device in the for loop below
  $device_graphs[$graph['device_id']][] = $graph;
}

if ($GLOBALS['config']['geocoding']['enable'] && get_db_version() >= 169)
{
  // FIXME. remove check db_version in r7000
  $devices_array = dbFetchRows("SELECT * FROM `devices` LEFT JOIN `devices_locations` USING (`device_id`) ORDER BY `hostname`;");
} else {
  $devices_array = dbFetchRows("SELECT * FROM `devices` ORDER BY `hostname`;");
}
foreach ($devices_array as $device)
{
  if (device_permitted($device['device_id']))
  {
    // Process device and add all the human-readable stuff.
    humanize_device($device);
    
    // Assign device graphs from array created above
    $device['graphs'] = $device_graphs[$device['device_id']];

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

    if (isset($config['geocoding']['enable']) && $config['geocoding']['enable'])
    {
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

  if ($port['deleted'])
  {
    $ports['deleted']++;
    $cache['ports']['deleted'][] = (int)$port['port_id']; // Collect IDs for deleted
    continue; // Complete don't count port if it deleted
  }

  if ($cache['devices']['id'][$port['device_id']]['ignore'])
  {
    $cache['ports']['device_ignored'][] = (int)$port['port_id']; // Collect IDs for ignored device
  }

  $ports['count']++;
  $cache['ports']['permitted'][] = (int)$port['port_id']; // Collect IDs for permitted

  // Don't count alerts/errored ports if device down
  $device_status = (bool)$cache['devices']['id'][$port['device_id']]['status'];

  if ($port['ifAdminStatus'] == "down")
  {
    $ports['disabled']++;
    //$cache['ports']['disabled'][] = (int)$port['port_id']; // Collect IDs for disabled
  } else {
    if ($port['ifOperStatus'] == "up" || $port['ifOperStatus'] == "monitoring")
    {
      $ports['up']++;
      if ($device_status && ($port['ifOutErrors_delta'] > 0 || $port['ifInErrors_delta'] > 0 ))
      {
        $ports['errored']++;
        $cache['ports']['errored'][] = (int)$port['port_id']; // Collect IDs for errored
      }
    }
    else if ($port['ifOperStatus'] == "down" || $port['ifOperStatus'] == "lowerLayerDown")
    {
      // $ports['down']++;
      if ($device_status && !$port['ignore']) { $ports['alerts']++; $ports['down']++; }
      //if (!$port['ignore']) { $ports['alerts']++; $ports['down']++; }
    } else {
      // All other states: testing, unknown, dormant, notPresent
      $ports['other']++;
      //$cache['ports']['other'][] = (int)$port['port_id']; // Collect IDs for other
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

$sensors_array = dbFetchRows('SELECT `device_id`, `sensors`.`sensor_id`, `sensor_value`, `sensor_class`, `sensor_type`, `sensor_event`,
                             `sensor_ignore`, `sensor_disable`, `sensor_deleted` FROM `sensors`
                             LEFT JOIN `sensors-state` ON `sensors`.`sensor_id` = `sensors-state`.`sensor_id`;'); // FIXME. sensor_deleted not used..
foreach ($sensors_array as $sensor)
{
  if (!isset($cache['devices']['id'][$sensor['device_id']])) { continue; } // Check device permitted

  if (!$config['web_show_disabled'])
  {
    if ($cache['devices']['id'][$sensor['device_id']]['disabled']) { continue; }
  }

  // humanize_sensor($sensor);
  $sensors['count']++;
  $cache['sensor_types'][$sensor['sensor_class']]['count']++;

  if ($sensor['sensor_disable']) { $sensors['disabled']++; }

  if ($sensor['sensor_state'] && $sensor['state_event'] == 'ignore' && !$sensor['sensor_disable'])
  {
    $sensor['sensor_ignore'] = 1;  // Trust me. --mike
  }
  if ($sensor['sensor_ignore'])  { $sensors['ignored']++; }

  switch ($sensor['sensor_event'])
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
  foreach (dbFetchRows('SELECT `device_id`,`bgpPeer_id`,`bgpPeerState`,`bgpPeerAdminStatus`,`bgpPeerRemoteAs` FROM `bgpPeers`;') as $bgp)
  {
    if (!$config['web_show_disabled'])
    {
      if ($cache['devices']['id'][$bgp['device_id']]['disabled']) { continue; }
    }
    if (device_permitted($bgp))
    {
      $routing['bgp']['count']++;
      $cache['bgp']['permitted'][] = (int)$bgp['bgpPeer_id']; // Collect permitted peers
      if ($bgp['bgpPeerAdminStatus'] == 'start' || $bgp['bgpPeerAdminStatus'] == 'running')
      {
        $routing['bgp']['up']++;
        $cache['bgp']['start'][] = (int)$bgp['bgpPeer_id']; // Collect START peers (bgpPeerAdminStatus = (start || running))
        if ($bgp['bgpPeerState'] != 'established')
        {
          $routing['bgp']['alerts']++;
        } else {
          $cache['bgp']['up'][] = (int)$bgp['bgpPeer_id']; // Collect UP peers (bgpPeerAdminStatus = (start || running), bgpPeerState = established)
        }
      } else {
        $routing['bgp']['down']++;
      }
      if ($cache['devices']['id'][$bgp['device_id']]['bgpLocalAs'] == $bgp['bgpPeerRemoteAs'])
      {
        $routing['bgp']['internal']++;
        $cache['bgp']['internal'][] = (int)$bgp['bgpPeer_id']; // Collect iBGP peers
      } else {
        $routing['bgp']['external']++;
        $cache['bgp']['external'][] = (int)$bgp['bgpPeer_id']; // Collect eBGP peers
      }
    }
  }
}

// OSPF
if (isset($config['enable_ospf']) && $config['enable_ospf'])
{
  $routing['ospf']['last_seen'] = $config['time']['now'];
  foreach (dbFetchRows("SELECT `device_id`, `ospfAdminStat` FROM `ospf_instances`") as $ospf)
  {
    if (!$config['web_show_disabled'])
    {
      if ($cache['devices']['id'][$ospf['device_id']]['disabled']) { continue; }
    }
    if (device_permitted($ospf))
    {
      if ($ospf['ospfAdminStat'] == 'enabled')
      {
        $routing['ospf']['up']++;
      }
      else if ($ospf['ospfAdminStat'] == 'disabled')
      {
        $routing['ospf']['down']++;
      } else {
        continue;
      }
      $routing['ospf']['count']++;
    }
  }
}

// Common permission sql query
//r(range_to_list($cache['devices']['permitted']));
//unset($cache['devices']['permitted']);
$cache['where']['devices_permitted'] = generate_query_permitted(array('device'));
$cache['where']['ports_permitted']   = generate_query_permitted(array('port'));

// CEF
$routing['cef']['count'] = count(dbFetchColumn("SELECT `cef_switching_id` FROM `cef_switching` WHERE 1 ".$cache['where']['devices_permitted']." GROUP BY `device_id`, `afi`;"));
// VRF
$routing['vrf']['count'] = count(dbFetchColumn("SELECT DISTINCT `mplsVpnVrfRouteDistinguisher` FROM `vrfs` WHERE 1 ".$cache['where']['devices_permitted']));

// Status
$cache['status']['count'] = dbFetchCell("SELECT COUNT(`status_id`) FROM `status` WHERE 1 ".$cache['where']['devices_permitted']);

// Additional common counts
if ($config['enable_pseudowires'])
{
  $cache['ports']['pseudowires'] = dbFetchColumn('SELECT DISTINCT `port_id` FROM `pseudowires` WHERE 1 '.$cache['where']['ports_permitted']);
  $cache['pseudowires']['count'] = count($cache['ports']['pseudowires']);
}
if ($config['poller_modules']['cisco-cbqos'] || $config['discovery_modules']['cisco-cbqos'])
{
  $cache['ports']['cbqos'] = dbFetchColumn('SELECT DISTINCT `port_id` FROM `ports_cbqos` WHERE 1 '.$cache['where']['ports_permitted']);
  $cache['cbqos']['count'] = count($cache['ports']['cbqos']);
}
if ($config['poller_modules']['unix-agent'])
{
  $cache['packages']['count'] = dbFetchCell("SELECT COUNT(*) FROM `packages` WHERE 1 ".$cache['where']['devices_permitted']);
}
if ($config['poller_modules']['applications'])
{
  $cache['applications']['count'] = dbFetchCell("SELECT COUNT(`app_id`) FROM `applications` WHERE 1 ".$cache['where']['devices_permitted']);
}
if ($config['poller_modules']['wifi'] || $config['discovery_modules']['wifi'])
{
  $cache['wifi_sessions']['count'] = dbFetchCell("SELECT COUNT(`wifi_session_id`) FROM `wifi_sessions` WHERE 1 ".$cache['where']['devices_permitted']);
}
if ($config['poller_modules']['toner'] || $config['discovery_modules']['toner'])
{
  $cache['toner']['count'] = dbFetchCell("SELECT COUNT(*) FROM `toner` WHERE 1 ".$cache['where']['devices_permitted']);
}

$cache_end  = microtime(true);
$cache_time = number_format($cache_end - $cache_start, 3);

// Clean arrays (from DB queries)
unset($devices_array, $ports_array, $sensors_array, $graphs_array);
// Clean variables (generated by foreach)
unset($device, $port, $sensor, $bgp, $ospf);

// EOF
