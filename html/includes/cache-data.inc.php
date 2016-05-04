<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
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
  if ($graph['enabled'])
  {
    $device_graphs[$graph['device_id']][$graph['graph']] = $graph;
  }
}
$cache['graphs'] = array(); // All permitted graphs

// Cache scheduled maintenance currently active
$cache['maint'] = cache_alert_maintenance();

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
    $cache['graphs']  = array_unique(array_merge($cache['graphs'], array_keys($device['graphs']))); // Add to global array cache

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
      $country_code = $device['location_country'];
      $cache['locations']['entries'][$country_code]['count']++;
      $cache['locations']['entries'][$country_code]['level'] = 'location_country';

      if (isset($config['location_countries_with_counties']) && in_array($country_code, $config['location_countries_with_counties']) )
      {
        $cache['locations']['entries'][$country_code]['entries'][$device['location_county']]['count']++;
        $cache['locations']['entries'][$country_code]['entries'][$device['location_county']]['level'] = 'location_county';

        $cache['locations']['entries'][$country_code]['entries'][$device['location_county']]['entries'][$device['location_city']]['count']++;
        $cache['locations']['entries'][$country_code]['entries'][$device['location_county']]['entries'][$device['location_city']]['level'] = 'location_city';

        $cache['locations']['entries'][$country_code]['entries'][$device['location_county']]['entries'][$device['location_city']]['entries'][$device['location']]['count']++;
        $cache['locations']['entries'][$country_code]['entries'][$device['location_county']]['entries'][$device['location_city']]['entries'][$device['location']]['level'] = 'location';
      } //county only
      else
      { //check for county and states
        if (isset($config['location_countries_with_counties_and_states']) && in_array($country_code, $config['location_countries_with_counties_and_states']) )
        {
          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['count']++;
          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['level'] = 'location_state';

          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['entries'][$device['location_county']]['count']++;
          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['entries'][$device['location_county']]['level'] = 'location_county';

          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['entries'][$device['location_county']]['entries'][$device['location_city']]['count']++;
          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['entries'][$device['location_county']]['entries'][$device['location_city']]['level'] = 'location_city';

          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['entries'][$device['location_county']]['entries'][$device['location_city']]['entries'][$device['location']]['count']++;
          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['entries'][$device['location_county']]['entries'][$device['location_city']]['entries'][$device['location']]['level'] = 'location';
        } //state and county
        else
        {
          //state only
          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['count']++;
          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['level'] = 'location_state';
          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['entries'][$device['location_city']]['count']++;
          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['entries'][$device['location_city']]['level'] = 'location_city';
          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['entries'][$device['location_city']]['entries'][$device['location']]['count']++;
          $cache['locations']['entries'][$country_code]['entries'][$device['location_state']]['entries'][$device['location_city']]['entries'][$device['location']]['level'] = 'location';
        } //state only
      } //county only / else
    }
  }
}
sort($cache['graphs']);

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
                        //'permitted' => array(),
                        'ignored'   => array(),
                        'errored'   => array(),
                        //'disabled'  => array(),
                        'poll_disabled' => array(),
                        'device_ignored' => array(),
                        'device_disabled' => array(),
                        'deleted'   => array());

/*

// Possible out of memory error: dbFetchRows() -> mysqli_fetch_assoc()
// with 220000 entries and increment:
//  1000: peak memory usage ~  87MB, execute time ~40s
//  5000: peak memory usage ~  89MB, execute time ~21s
// 10000: peak memory usage ~  96MB, execute time ~19s
// 20000: peak memory usage ~ 112MB, execute time ~18s
// 50000: peak memory usage ~ 188MB, execute time ~17s
$ports_count = dbFetchCell("SELECT COUNT(*) FROM `ports`;");
$increment = 15000;
$query = "SELECT `device_id`, `port_id`, `ifAdminStatus`, `ifOperStatus`, `deleted`, `ignore`, `ifOutErrors_delta`, `ifInErrors_delta` FROM `ports`
          LEFT JOIN `ports-state` USING(`port_id`)";
$i = 0;
while ($i <= $ports_count)
{
  $ports_array = dbFetchRows($query . " LIMIT $i, $increment;");
  port_permitted_array($ports_array);
  //print_vars("TOTAL $ports_count, LIMIT $i, $increment;");

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
  $i += $increment;
}
*/

$where_permitted = generate_query_permitted(array('device', 'port'));

$where_hide      = " AND `deleted` = 0";

// Deleted
$cache['ports']['deleted'] = dbFetchColumn("SELECT `port_id` FROM `ports` WHERE 1 " . $where_permitted . " AND `deleted` = 1");
$ports['deleted']          = count($cache['ports']['deleted']);

// Devices disabled
if (isset($cache['devices']['disabled']) && count($cache['devices']['disabled']) > 0)
{
  $cache['ports']['device_disabled'] = dbFetchColumn("SELECT `port_id` FROM `ports` WHERE 1 " . $where_permitted . generate_query_values($cache['devices']['disabled'], 'device_id'));
  if (!$config['web_show_disabled'])
  {
    $where_hide  .= generate_query_values($cache['devices']['disabled'], 'device_id', '!=');
  }
}

// Devices ignored
$where_devices_ignored = '';
if (isset($cache['devices']['ignored']) && count($cache['devices']['ignored']) > 0)
{
  $cache['ports']['device_ignored'] = dbFetchColumn("SELECT `port_id` FROM `ports` WHERE 1 " . $where_permitted . $where_hide . generate_query_values($cache['devices']['ignored'], 'device_id'));
  $where_devices_ignored  = " OR `device_id` IN (".implode(',', $cache['devices']['ignored']).")";
}

// Ports poll disabled
$cache['ports']['poll_disabled'] = dbFetchColumn("SELECT `port_id` FROM `ports` WHERE 1 " . $where_permitted . $where_hide . " AND `disabled` = '1'");

// Ports ignored
$cache['ports']['ignored'] = dbFetchColumn("SELECT `port_id` FROM `ports` WHERE 1 " . $where_permitted . $where_hide . " AND (`ignore` = '1'" . $where_devices_ignored . ")");
$ports['ignored']          = count($cache['ports']['ignored']);

$where_hide      .= " AND `ignore` = 0";

// Ports errored
$cache['ports']['errored'] = dbFetchColumn("SELECT `port_id` FROM `ports` LEFT JOIN `ports-state` USING(`port_id`) WHERE 1 " . $where_permitted . $where_hide . " AND `ifAdminStatus` = 'up' AND (`ifOperStatus` = 'up' OR `ifOperStatus` = 'testing') AND (`ifOutErrors_delta` > 0 OR `ifInErrors_delta` > 0)");
$ports['errored']          = count($cache['ports']['errored']);

// Ports counts
$ports['count']            = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE 1 " . $where_permitted . $where_hide);
$ports['shutdown']         = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE 1 " . $where_permitted . $where_hide . " AND `ifAdminStatus` = 'down'");
$ports['down']             = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE 1 " . $where_permitted . $where_hide . " AND `ifAdminStatus` = 'up' AND `ifOperStatus` = 'down'");
$ports['up']               = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE 1 " . $where_permitted . $where_hide . " AND `ifAdminStatus` = 'up' AND (`ifOperStatus` = 'up' OR `ifOperStatus` = 'testing')");

//r($where_hide);
//r($cache['devices']);
//r($cache['ports']);
//r($ports);
//r($ports_db);
//r($permissions);

// Sensors
$sensors = array('count'    => 0,
                 'ok'       => 0,
                 'alert'    => 0,
                 'warning'  => 0,
                 'ignored'  => 0,
                 'disabled' => 0);
$cache['sensor_types'] = array();

$sensors_array = dbFetchRows('SELECT `device_id`, `sensor_id`, `sensor_class`, `sensor_type`, `sensor_ignore`, `sensor_disable`,
                                     `sensor_value`, `sensor_event` FROM `sensors`
                             LEFT JOIN `sensors-state` USING(`sensor_id`);'); // FIXME. sensor_deleted not used..

foreach ($sensors_array as $sensor)
{

  if (!is_entity_permitted($sensor['sensor_id'], 'sensor', $sensor['device_id'])) { continue; } // Check device permitted

  if (!$config['web_show_disabled'])
  {
    if ($cache['devices']['id'][$sensor['device_id']]['disabled']) { continue; }
  }
  if ($sensor['sensor_deleted']) { continue; }

  // humanize_sensor($sensor);
  $sensors['count']++;
  $cache['sensor_types'][$sensor['sensor_class']]['count']++;

  if ($sensor['sensor_disable'])
  {
    $sensors['disabled']++;
    continue;
  }

  if ($sensor['sensor_event'] == 'ignore' || $sensor['sensor_ignore'])
  {
    $sensors['ignored']++;
    continue;
  }

  switch ($sensor['sensor_event'])
  {
    case 'warning':
      $sensors['warning']++; // 'warning' also 'ok'
    case 'ok':
      $sensors['ok']++;
      break;
    case 'alert':
      $sensors['alert']++;
      $cache['sensor_types'][$sensor['sensor_class']]['alert']++;
      break;
    default:
      $sensors['alert']++; // unknown event (empty) also alert
  }
}

// Statuses
$statuses = array('count'    => 0,
                  'ok'       => 0,
                  'alert'    => 0,
                  'warning'  => 0,
                  'ignored'  => 0,
                  'disabled' => 0);
$cache['status_classes'] = array();

$status_array = dbFetchRows('SELECT `device_id`, `status_id` `entPhysicalClass`, `status_ignore`, `status_disable`,
                                    `status_event` FROM `status`
                             LEFT JOIN `status-state` USING(`status_id`);'); // FIXME. status_deleted not used..
foreach ($status_array as $status)
{
  if (!isset($cache['devices']['id'][$status['device_id']])) { continue; } // Check device permitted

  if (!$config['web_show_disabled'])
  {
    if ($cache['devices']['id'][$status['device_id']]['disabled']) { continue; }
  }
  if ($status['status_deleted']) { continue; }

  $statuses['count']++;
  $cache['status_classes'][$status['entPhysicalClass']]['count']++;

  if ($status['status_disable'])
  {
    $statuses['disabled']++;
    continue;
  }

  if ($status['status_event'] == 'ignore' || $status['status_ignore'])
  {
    $statuses['ignored']++;
    continue;
  }

  switch ($status['status_event'])
  {
    case 'warning':
      $statuses['warning']++; // 'warning' also 'ok'
    case 'ok':
      $statuses['ok']++;
      break;
    case 'alert':
      $statuses['alert']++;
      $cache['status_classes'][$status['entPhysicalClass']]['alert']++;
      break;
    default:
      $statuses['alert']++; // unknown event (empty) also alert
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
$cache['status']['count'] = $statuses['count']; //dbFetchCell("SELECT COUNT(`status_id`) FROM `status` WHERE 1 ".$cache['where']['devices_permitted']);

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
  $cache['applications']['count'] = dbFetchCell("SELECT COUNT(*) FROM `applications` WHERE 1 ".$cache['where']['devices_permitted']);
}
if ($config['poller_modules']['wifi'] || $config['discovery_modules']['wifi'])
{
  $cache['wifi_sessions']['count'] = dbFetchCell("SELECT COUNT(*) FROM `wifi_sessions` WHERE 1 ".$cache['where']['devices_permitted']);
}
if ($config['poller_modules']['toner'] || $config['discovery_modules']['toner'])
{
  $cache['toner']['count'] = dbFetchCell("SELECT COUNT(*) FROM `toner` WHERE 1 ".$cache['where']['devices_permitted']);
}

$cache['neighbours']['count'] = dbFetchCell("SELECT COUNT(*) FROM `neighbours` WHERE `active` = 1 "  . $cache['where']['ports_permitted']);
$cache['sla']['count']        = dbFetchCell("SELECT COUNT(*) FROM `slas` WHERE `deleted` = 0 "       . $cache['where']['devices_permitted']);
$cache['p2pradios']['count']  = dbFetchCell("SELECT COUNT(*) FROM `p2p_radios` WHERE `deleted` = 0 " . $cache['where']['devices_permitted']);

$cache_end  = microtime(true);
$cache_time = number_format($cache_end - $cache_start, 3);

// Clean arrays (from DB queries)
unset($devices_array, $ports_array, $sensors_array, $status_array, $graphs_array, $device_graphs);
// Clean variables (generated by foreach)
unset($device, $port, $sensor, $status, $bgp, $ospf);

// EOF
