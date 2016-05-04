<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/**
 * Display status alerts.
 *
 * Display pages with alerts about device troubles.
 * Examples:
 * print_status(array('devices' => TRUE)) - display for devices down
 *
 * Another statuses:
 * devices, uptime, ports, errors, services, bgp
 *
 * @param array $status
 * @return none
 *
 */
function print_status($status)
{
  global $config;

  $max_interval = filter_var($status['max']['interval'], FILTER_VALIDATE_INT, array('options' => array('default' => 24,
                                                                                                       'min_range' => 1)));
  $max_count    = filter_var($status['max']['count'],    FILTER_VALIDATE_INT, array('options' => array('default' => 200,
                                                                                                       'min_range' => 1)));

   $string  = '<table class="table  table-striped table-hover table-condensed">' . PHP_EOL;
 /* $string .= '  <thead>' . PHP_EOL;
  $string .= '  <tr>' . PHP_EOL;
  $string .= '    <th>Device</th>' . PHP_EOL;
  $string .= '    <th>Type</th>' . PHP_EOL;
  $string .= '    <th>Status</th>' . PHP_EOL;
  $string .= '    <th>Entity</th>' . PHP_EOL;
  // $string .= '    <th>Location</th>' . PHP_EOL;
  $string .= '    <th>Information</th>' . PHP_EOL;
  $string .= '  </tr>' . PHP_EOL;
  $string .= '  </thead>' . PHP_EOL;
  $string .= '  <tbody>' . PHP_EOL;
 */

  $query_device_permitted = generate_query_permitted(array('device'), array('device_table' => 'D', 'hide_ignored' => TRUE));
  $query_port_permitted   = generate_query_permitted(array('port'),   array('port_table' => 'I',   'hide_ignored' => TRUE));

  // Show Device Status
  if ($status['devices'])
  {
    $query = 'SELECT * FROM `devices` AS D';
    $query .= ' WHERE D.`status` = 0' . $query_device_permitted;
    $query .= ' ORDER BY D.`hostname` ASC';
    $entries = dbFetchRows($query);
    foreach ($entries as $device)
    {
      $string .= '  <tr>' . PHP_EOL;
      $string .= '    <td class="entity">' . generate_device_link($device, short_hostname($device['hostname'])) . '</td>' . PHP_EOL;
      // $string .= '    <td><span class="badge badge-inverse">Device</span></td>' . PHP_EOL;
      $string .= '    <td><span class="label label-important">Device Down</span></td>' . PHP_EOL;
      $string .= '    <td class="entity"><i class="oicon-servers"></i> ' . generate_device_link($device, short_hostname($device['hostname'])) . '</td>' . PHP_EOL;
      // $string .= '    <td style="white-space: nowrap">' . escape_html(truncate($device['location'], 30)) . '</td>' . PHP_EOL;
      $string .= '    <td style="white-space: nowrap">' . deviceUptime($device, 'short') . '</td>' . PHP_EOL;
      $string .= '  </tr>' . PHP_EOL;
    }
  }

  // Uptime
  if ($status['uptime'])
  {
    if (filter_var($config['uptime_warning'], FILTER_VALIDATE_FLOAT) !== FALSE && $config['uptime_warning'] > 0)
    {
      $query = 'SELECT * FROM `devices` AS D';
      $query .= ' WHERE D.`status` = 1 AND D.`uptime` > 0 AND D.`uptime` < ' . $config['uptime_warning'];
      $query .= $query_device_permitted;
      $query .= 'ORDER BY D.`hostname` ASC';
      $entries = dbFetchRows($query);

      // Since reboot event more complicated than just device uptime less than some time,
      // additionally check reboot events
      if (count($entries))
      {
        $rebooted_devices = dbFetchColumn('SELECT DISTINCT(`device_id`) FROM `eventlog` WHERE `message` LIKE ? AND `entity_type` = ? AND (`timestamp` BETWEEN NOW() - INTERVAL ? SECOND AND NOW());', array('%rebooted%', 'device', $config['uptime_warning']));
      }

      foreach ($entries as $device)
      {
        // Skip, because device not really rebooted, but just uptime counter wrapped
        if (!in_array($device['device_id'], $rebooted_devices)) { continue; }

        $string .= '  <tr>' . PHP_EOL;
        $string .= '    <td class="entity">' . generate_device_link($device, short_hostname($device['hostname'])) . '</td>' . PHP_EOL;
        // $string .= '    <td><span class="badge badge-inverse">Device</span></td>' . PHP_EOL;
        $string .= '    <td><span class="label label-success">Device Rebooted</span></td>' . PHP_EOL;
        $string .= '    <td class="entity"><i class="oicon-servers"></i> ' . generate_device_link($device, short_hostname($device['hostname'])) . '</td>' . PHP_EOL;
        // $string .= '    <td style="white-space: nowrap">' . escape_html(truncate($device['location'], 30)) . '</td>' . PHP_EOL;
        $string .= '    <td style="white-space: nowrap">Uptime ' . formatUptime($device['uptime'], 'short') . '</td>' . PHP_EOL;
        $string .= '  </tr>' . PHP_EOL;
      }
    }
  }

  // Ports Down
  if ($status['ports'] || $status['neighbours'] || $status['links'])
  {
    $status['neighbours'] = $status['neighbours'] && !$status['ports']; // Disable 'neighbours' if 'ports' already enabled

    $query = 'SELECT * FROM `ports` AS I ';
    if ($status['neighbours'])
    {
      $query .= 'INNER JOIN `neighbours` AS L ON I.`port_id` = L.`port_id` ';
    }
    $query .= 'LEFT JOIN `devices` AS D ON I.`device_id` = D.`device_id` ';
    $query .= "WHERE D.`status` = 1 AND D.ignore = 0 AND I.ignore = 0 AND I.deleted = 0 AND I.`ifAdminStatus` = 'up' AND (I.`ifOperStatus` = 'lowerLayerDown' OR I.`ifOperStatus` = 'down') ";
    if ($status['neighbours'])
    {
      $query .= ' AND L.`active` = 1 ';
    }
    $query .= $query_port_permitted;
    $query .= ' AND I.`ifLastChange` >= DATE_SUB(NOW(), INTERVAL '.$max_interval.' HOUR) ';
    if ($status['neighbours']) { $query .= 'GROUP BY L.`port_id` '; }
    $query .= 'ORDER BY I.`ifLastChange` DESC, D.`hostname` ASC, I.`ifDescr` * 1 ASC ';
    $entries = dbFetchRows($query);
    $i = 1;
    foreach ($entries as $port)
    {
      if ($i > $max_count)
      {
        $string .= '  <tr><td></td><td><span class="badge badge-info">Port</span></td>';
        $string .= '<td><span class="label label-important">Port Down</span></td>';
        $string .= '<td colspan=3>Too many ports down. See <strong><a href="'.generate_url(array('page'=>'ports'), array('state'=>'down')).'">All DOWN ports</a></strong>.</td></tr>' . PHP_EOL;
        break;
      }
      humanize_port($port);
      $string .= '  <tr>' . PHP_EOL;
      $string .= '    <td class="entity">' . generate_device_link($port, short_hostname($port['hostname'])) . '</td>' . PHP_EOL;
      // $string .= '    <td><span class="badge badge-info">Port</span></td>' . PHP_EOL;
      $string .= '    <td><span class="label label-important">Port Down</span></td>' . PHP_EOL;
      $string .= '    <td class="entity"><i class="oicon-network-ethernet"></i> ' . generate_port_link($port, short_ifname($port['port_label'])) . '</td>' . PHP_EOL;
      // $string .= '    <td style="white-space: nowrap">' . escape_html(truncate($port['location'], 30)) . '</td>' . PHP_EOL;
      $string .= '    <td style="white-space: nowrap">Down for ' . formatUptime($config['time']['now'] - strtotime($port['ifLastChange']), 'short'); // This is like deviceUptime()
      if ($status['links']) { $string .= ' ('.nicecase($port['protocol']).': ' .$port['remote_hostname'].' / ' .$port['remote_port'] .')'; }
      $string .= '</td>' . PHP_EOL;
      $string .= '  </tr>' . PHP_EOL;
      $i++;
    }
  }

  // Ports Errors (only deltas)
  if ($status['errors'])
  {
    $query = 'SELECT * FROM `ports` AS I ';
    $query .= 'LEFT JOIN `ports-state` AS E ON I.`port_id` = E.`port_id` ';
    $query .= 'LEFT JOIN `devices` AS D ON I.`device_id` = D.`device_id` ';
    $query .= "WHERE D.`status` = 1 AND I.`ifOperStatus` = 'up' AND (E.`ifInErrors_delta` > 0 OR E.`ifOutErrors_delta` > 0)";
    $query .= $query_port_permitted;
    $query .= 'ORDER BY D.`hostname` ASC, I.`ifDescr` * 1 ASC';
    $entries = dbFetchRows($query);
    foreach ($entries as $port)
    {
      humanize_port($port);
      $string .= '  <tr>' . PHP_EOL;
      $string .= '    <td class="entity">' . generate_device_link($port, short_hostname($port['hostname'])) . '</td>' . PHP_EOL;
      // $string .= '    <td><span class="badge badge-info">Port</span></td>' . PHP_EOL;
      $string .= '    <td><span class="label label-important">Port Errors</span></td>' . PHP_EOL;
      $string .= '    <td class="entity"><i class="oicon-network-ethernet"></i> '.generate_port_link($port, short_ifname($port['port_label']), 'port_errors') . '</td>' . PHP_EOL;
      // $string .= '    <td style="white-space: nowrap">' . escape_html(truncate($port['location'], 30)) . '</td>' . PHP_EOL;
      $string .= '    <td>Errors ';
      if ($port['ifInErrors_delta']) { $string .= 'In: ' . $port['ifInErrors_delta']; }
      if ($port['ifInErrors_delta'] && $port['ifOutErrors_delta']) { $string .= ', '; }
      if ($port['ifOutErrors_delta']) { $string .= 'Out: ' . $port['ifOutErrors_delta']; }
      $string .= '</td>' . PHP_EOL;
      $string .= '  </tr>' . PHP_EOL;
    }
  }

  // Services
  if ($status['services'])
  {
    $query = 'SELECT * FROM `services` AS S ';
    $query .= 'LEFT JOIN `devices` AS D ON S.`device_id` = D.`device_id` ';
    $query .= "WHERE S.`service_status` = 'down' AND S.`service_ignore` = 0";
    $query .= $query_device_permitted;
    $query .= 'ORDER BY D.`hostname` ASC';
    $entries = dbFetchRows($query);
    foreach ($entries as $service)
    {
      $string .= '  <tr>' . PHP_EOL;
      $string .= '    <td class="entity">' . generate_device_link($service, short_hostname($service['hostname'])) . '</td>' . PHP_EOL;
      // $string .= '    <td><span class="badge">Service</span></td>' . PHP_EOL;
      $string .= '    <td><span class="label label-important">Service Down</span></td>' . PHP_EOL;
      $string .= '    <td>' . $service['service_type'] . '</td>' . PHP_EOL;
      // $string .= '    <td style="white-space: nowrap">' . escape_html(truncate($service['location'], 30)) . '</td>' . PHP_EOL;
      $string .= '    <td style="white-space: nowrap">Down for ' . formatUptime($config['time']['now'] - strtotime($service['service_changed']), 'short') . '</td>' . PHP_EOL; // This is like deviceUptime()
      $string .= '  </tr>' . PHP_EOL;
    }
  }

  // BGP
  if ($status['bgp'])
  {
    if (isset($config['enable_bgp']) && $config['enable_bgp'])
    {
      // Description for BGP states
      $bgpstates = 'IDLE - Router is searching routing table to see whether a route exists to reach the neighbor. &#xA;';
      $bgpstates .= 'CONNECT - Router found a route to the neighbor and has completed the three-way TCP handshake. &#xA;';
      $bgpstates .= 'OPEN SENT - Open message sent, with parameters for the BGP session. &#xA;';
      $bgpstates .= 'OPEN CONFIRM - Router received agreement on the parameters for establishing session. &#xA;';
      $bgpstates .= 'ACTIVE - Router did not receive agreement on parameters of establishment. &#xA;';
      //$bgpstates .= 'ESTABLISHED - Peering is established; routing begins.';

      $query = 'SELECT * FROM `devices` AS D ';
      $query .= 'LEFT JOIN `bgpPeers` AS B ON B.`device_id` = D.`device_id` ';
      $query .= "WHERE D.`status` = 1 AND (`bgpPeerAdminStatus` = 'start' OR `bgpPeerAdminStatus` = 'running') AND `bgpPeerState` != 'established' ";
      $query .= $query_device_permitted;
      $query .= 'ORDER BY D.`hostname` ASC';
      $entries = dbFetchRows($query);
      foreach ($entries as $peer)
      {
        humanize_bgp($peer);
        $peer_ip = generate_entity_link("bgp_peer", $peer, $peer['human_remoteip']);

        $string .= '  <tr>' . PHP_EOL;
        $string .= '    <td class="entity">' . generate_device_link($peer, short_hostname($peer['hostname']), array('tab' => 'routing', 'proto' => 'bgp')) . '</td>' . PHP_EOL;
        // $string .= '    <td><span class="badge badge-warning">BGP</span></td>' . PHP_EOL;
        $string .= '    <td><span class="label label-warning" title="' . $bgpstates . '">BGP ' . nicecase($peer['bgpPeerState']) . '</span></td>' . PHP_EOL;
        $string .= '    <td class="entity" style="white-space: nowrap"><i class="oicon-chain"></i> ' . $peer_ip . '</td>' . PHP_EOL;
        // $string .= '    <td style="white-space: nowrap">' . escape_html(truncate($peer['location'], 30)) . '</td>' . PHP_EOL;
        $string .= '    <td><strong>AS' . $peer['bgpPeerRemoteAs'] . ' :</strong> ' . $peer['astext'] . '</td>' . PHP_EOL;
        $string .= '  </tr>' . PHP_EOL;
      }
    }
  }

  // $string .= '  </tbody>' . PHP_EOL;
  $string .= '</table>';

  // Final print all statuses
  echo($string);
}

/**
 * Display status alerts.
 *
 * Display pages with alerts about device troubles.
 * Examples:
 * print_status(array('devices' => TRUE)) - display for devices down
 *
 * Another statuses:
 * devices, uptime, ports, errors, services, bgp
 *
 * @param array $status
 * @return none
 *
 */
function print_status_boxes($status)
{
  $status_array = get_status_array($status);

  $status_array = array_sort($status_array, 'sev', 'SORT_DESC');

  foreach ($status_array as $entry)
  {

    if ($entry['sev'] > 51) { $class="alert-danger"; } elseif ($entry['sev'] > 20) { $class="alert-warn"; } else { $class="alert-info"; }
    if ($entry['wide']) { $class .= ' statusbox-wide'; }

    echo('<div class="alert statusbox '.$class.'">');
    echo('<span class="header">'.$entry['device_link'].'</span>');
    echo('<p>');
    echo($entry['class'] .' '.$entry['event'].'<br />');
    echo('<span class="entity">'.$entry['entity_link'].'</span><br />');
    echo('<small>'.$entry['time'].'</small><br />');
    echo('</p>');
    echo('</div>');
  }
}

// DOCME needs phpdoc block
function get_status_array($status)
{
  // Mike: I know that there are duplicated variables, but later will remove global
  global $config, $cache;

  $max_interval = filter_var($status['max']['interval'], FILTER_VALIDATE_INT, array('options' => array('default' => 24,
                                                                                                       'min_range' => 1)));
  $max_count    = filter_var($status['max']['count'],    FILTER_VALIDATE_INT, array('options' => array('default' => 200,
                                                                                                       'min_range' => 1)));
  $query_device_permitted = generate_query_permitted(array('device'), array('device_table' => 'D', 'hide_ignored' => TRUE));
  $query_port_permitted   = generate_query_permitted(array('port'),   array('port_table' => 'I',   'hide_ignored' => TRUE));

  // Show Device Status
  if ($status['devices'])
  {
    $query = 'SELECT * FROM `devices` AS D ';
    $query .= 'WHERE D.`status` = 0' . $query_device_permitted;
    $query .= 'ORDER BY D.`hostname` ASC';
    $entries = dbFetchRows($query);
    foreach ($entries as $device)
    {
      $boxes[] = array('sev' => 100, 'class' => 'Device', 'event' => 'Down', 'device_link' => generate_device_link($device, short_hostname($device['hostname'])),
                       'time' => deviceUptime($device, 'short-3'));
    }
  }

  // Uptime
  if ($status['uptime'])
  {
    if (filter_var($config['uptime_warning'], FILTER_VALIDATE_FLOAT) !== FALSE && $config['uptime_warning'] > 0)
    {
      $query = 'SELECT * FROM `devices` AS D ';
      $query .= 'WHERE D.`status` = 1 AND D.`uptime` > 0 AND D.`uptime` < ' . $config['uptime_warning'];
      $query .= $query_device_permitted;
      $query .= 'ORDER BY D.`hostname` ASC';
      $entries = dbFetchRows($query);

      // Since reboot event more complicated than just device uptime less than some time,
      // additionally check reboot events
      if (count($entries))
      {
        $rebooted_devices = dbFetchColumn('SELECT DISTINCT(`device_id`) FROM `eventlog` WHERE `message` LIKE ? AND `entity_type` = ? AND (`timestamp` BETWEEN NOW() - INTERVAL ? SECOND AND NOW());', array('%rebooted%', 'device', $config['uptime_warning']));
      }

      foreach ($entries as $device)
      {
        // Skip, because device not really rebooted, but just uptime counter wrapped
        if (!in_array($device['device_id'], $rebooted_devices)) { continue; }

        $boxes[] = array('sev' => 10, 'class' => 'Device', 'event' => 'Rebooted', 'device_link' => generate_device_link($device, short_hostname($device['hostname'])),
                         'time' => deviceUptime($device, 'short-3'), 'location' => $device['location']);
      }
    }
  }

  // Ports Down
  if ($status['ports'] || $status['neighbours'])
  {
    $status['neighbours'] = $status['neighbours'] && !$status['ports']; // Disable 'neighbours' if 'ports' already enabled

    $query = 'SELECT * FROM `ports` AS I ';
    if ($status['neighbours'])
    {
      $query .= 'INNER JOIN `neighbours` as L ON I.`port_id` = L.`port_id` ';
    }
    $query .= 'LEFT JOIN `devices` AS D ON I.`device_id` = D.`device_id` ';
    $query .= "WHERE D.`status` = 1 AND D.ignore = 0 AND I.ignore = 0 AND I.deleted = 0 AND I.`ifAdminStatus` = 'up' AND (I.`ifOperStatus` = 'lowerLayerDown' OR I.`ifOperStatus` = 'down') ";
    if ($status['neighbours'])
    {
      $query .= ' AND L.`active` = 1 ';
    }
    $query .= $query_port_permitted;
    $query .= ' AND I.`ifLastChange` >= DATE_SUB(NOW(), INTERVAL '.$max_interval.' HOUR) ';
    if ($status['neighbours'])
    {
      $query .= 'GROUP BY L.`port_id` ';
    }
    $query .= 'ORDER BY I.`ifLastChange` DESC, D.`hostname` ASC, I.`ifDescr` * 1 ASC ';
    $entries = dbFetchRows($query);
    $i = 1;
    foreach ($entries as $port)
    {
      if ($i > $max_count)
      {
        // Limit to 200 ports on overview page
        break;
      }
      humanize_port($port);
      $boxes[] = array('sev' => 50, 'class' => 'Port', 'event' => 'Down', 'device_link' => generate_device_link($port, short_hostname($port['hostname'])),
                       'entity_link' => generate_port_link($port, short_ifname($port['port_label'], 13)),
                       'time' => formatUptime($config['time']['now'] - strtotime($port['ifLastChange'])), 'location' => $device['location']);
    }
  }

  // Ports Errors (only deltas)
  if ($status['errors'])
  {
    foreach ($cache['ports']['errored'] as $port_id)
    {
      if (in_array($port_id, $cache['ports']['ignored'])) { continue; } // Skip ignored ports

      $port   = get_port_by_id($port_id);
      $device = device_by_id_cache($port['device_id']);
      humanize_port($port);

      if ($port['ifInErrors_delta']) { $port['string'] .= 'Rx: ' . format_number($port['ifInErrors_delta']); }
      if ($port['ifInErrors_delta'] && $port['ifOutErrors_delta']) { $port['string'] .= ', '; }
      if ($port['ifOutErrors_delta']) { $port['string'] .= 'Tx: ' . format_number($port['ifOutErrors_delta']); }

      $boxes[] = array('sev' => 75, 'class' => 'Port', 'event' => 'Errors', 'device_link' => generate_device_link($device, short_hostname($device['hostname'])),
                       'entity_link' => generate_port_link($port, short_ifname($port['port_label'], 13)),
                       'time' => $port['string'], 'location' => $device['location']);
    }
  }

  // Services
  if ($status['services'])
  {
    $query = 'SELECT * FROM `services` AS S ';
    $query .= 'LEFT JOIN `devices` AS D ON S.`device_id` = D.`device_id` ';
    $query .= "WHERE S.`service_status` = 'down' AND S.`service_ignore` = 0";
    $query .= $query_device_permitted;
    $query .= 'ORDER BY D.`hostname` ASC';
    $entries = dbFetchRows($query);
    foreach ($entries as $service)
    {
      $boxes[] = array('sev' => 50, 'class' => 'Service', 'event' => 'Down', 'device_link' => generate_device_link($service, short_hostname($service['hostname'])),
                       'entity_link' => $service['service_type'],
                       'time' => formatUptime($config['time']['now'] - strtotime($service['service_changed']), 'short'), 'location' => $device['location']);
    }
  }

  // BGP
  if ($status['bgp'])
  {
    if (isset($config['enable_bgp']) && $config['enable_bgp'])
    {
      $query = 'SELECT * FROM `bgpPeers` AS B ';
      $query .= 'LEFT JOIN `devices` AS D ON B.`device_id` = D.`device_id` ';
      $query .= 'LEFT JOIN `bgpPeers-state` AS BS ON B.`bgpPeer_id` = BS.`bgpPeer_id` ';
      $query .= "WHERE D.`status` = 1 AND (`bgpPeerAdminStatus` = 'start' OR `bgpPeerAdminStatus` = 'running') AND `bgpPeerState` != 'established' ";
      $query .= $query_device_permitted;
      $query .= 'ORDER BY D.`hostname` ASC';
      $entries = dbFetchRows($query);
      foreach ($entries as $peer)
      {
        humanize_bgp($peer);
        $peer_ip = generate_entity_link("bgp_peer", $peer, $peer['human_remoteip']);

        $peer['wide'] = (strstr($peer['bgpPeerRemoteAddr'], ':')) ? TRUE : FALSE;
        $boxes[] = array('sev' => 75, 'class' => 'BGP Peer', 'event' => 'Down', 'device_link' => generate_device_link($peer, short_hostname($peer['hostname'])),
                         'entity_link' => $peer_ip, 'wide' => $peer['wide'],
                         'time' => formatUptime($peer['bgpPeerFsmEstablishedTime'], 'short-3'), 'location' => $device['location']);
      }
    }
  }

  // Return boxes array
  return $boxes;
}

// EOF
