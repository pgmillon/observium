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
 * Display bgp peers.
 *
 * Display pages with BGP Peers.
 * Examples:
 * print_bgp() - display all bgp peers from all devices
 * print_bgp(array('pagesize' => 99)) - display 99 bgp peers from all device
 * print_bgp(array('pagesize' => 10, 'pageno' => 3, 'pagination' => TRUE)) - display 10 bgp peers from page 3 with pagination header
 * print_bgp(array('pagesize' => 10, 'device' = 4)) - display 10 bgp peers for device_id 4
 *
 * @param array $vars
 * @return none
 *
 */
function print_bgp_table($vars)
{
  // Get bgp peers array
  $entries = get_bgp_array($vars);
  //r($entries);

  if (!$entries['count'])
  {
    // There have been no entries returned. Print the warning.
    print_warning('<h4>No BGP peers found!</h4>');
  } else {
    // Entries have been returned. Print the table.
    $list = array('device' => FALSE);
    if ($vars['page'] != 'device') { $list['device'] = TRUE; }

    switch ($vars['graph'])
    {
      case 'prefixes_ipv4unicast':
      case 'prefixes_ipv4multicast':
      case 'prefixes_ipv4vpn':
      case 'prefixes_ipv6unicast':
      case 'prefixes_ipv6multicast':
      case 'macaccounting_bits':
      case 'macaccounting_pkts':
      case 'updates':
        $table_class = 'table-striped-two';
        $list['graph'] = TRUE;
        break;
      default:
        $table_class = 'table-striped';
        $list['graph'] = FALSE;
    }

    $string = generate_box_open();

    $string .= '<table class="table  '.$table_class.' table-hover table-condensed ">' . PHP_EOL;

    $cols = array(
                    array(NULL,            'class="state-marker"'),
                    array(NULL,            'style="width: 1px;"'),
      'device'   => array('Local address', 'style="width: 150px;"'),
                    array(NULL,            'style="width: 20px;"'),
      'peer_ip'  => array('Peer address',  'style="width: 150px;"'),
      'type'     => array('Type',          'style="width: 50px;"'),
                    array('Family',        'style="width: 50px;"'),
      'peer_as'  => 'Remote AS',
      'state'    => 'State',
                    'Uptime / Updates',
    );
    //if (!$list['device']) { unset($cols['device']); }
    $string .= get_table_header($cols, $vars);

    $string .= '  <tbody>' . PHP_EOL;

    foreach ($entries['entries'] as $peer)
    {
      $local_dev = device_by_id_cache($peer['device_id']);
      $local_as  = ($list['device'] ? ' (AS'.$peer['bgpLocalAs'].')' : '');
      $local_name = generate_device_link($local_dev, short_hostname($local_dev['hostname']), array('tab' => 'routing', 'proto' => 'bgp'));
      $local_ip  = generate_device_link($local_dev, $peer['human_localip'].$local_as, array('tab' => 'routing', 'proto' => 'bgp'));
      $peer_as   = 'AS'.$peer['bgpPeerRemoteAs'];
      if ($peer['peer_device_id'])
      {
        $peer_dev = device_by_id_cache($peer['peer_device_id']);
        $peer_name = generate_device_link($peer_dev, short_hostname($peer_dev['hostname']), array('tab' => 'routing', 'proto' => 'bgp'));
      } else {
        $peer_name = $peer['reverse_dns'];
      }
      $peer_ip   = generate_entity_link("bgp_peer", $peer, $peer['human_remoteip']);
      $peer_afis = &$entries['afisafi'][$peer['device_id']][$peer['bgpPeerRemoteAddr']];

      $string .= '  <tr class="'.$peer['html_row_class'].'">' . PHP_EOL;
      $string .= '     <td class="state-marker"></td>' . PHP_EOL;
      $string .= '     <td></td>' . PHP_EOL;
      $string .= '     <td style="white-space: nowrap" class="entity">' . $local_ip . '<br />' . $local_name . '</td>' . PHP_EOL;
      $string .= '     <td><span class="text-success"><i class="glyphicon glyphicon-arrow-right"></i></span></td>' . PHP_EOL;
      $string .= '     <td style="white-space: nowrap" class="entity">' . $peer_ip  . '<br />' . $peer_name . '</td>' . PHP_EOL;
      $string .= '     <td><span class="label label-'.$peer['peer_type_class'].'">' . $peer['peer_type'] . '</span></td>' . PHP_EOL;
      $string .= '     <td><small>' . implode('<br />', $peer_afis) . '</small></td>' . PHP_EOL;
      $string .= '     <td><strong>' . $peer_as . '</strong><br />' . $peer['astext'] . '</td>' . PHP_EOL;
      $string .= '     <td><strong><span class=" label label-'.$peer['admin_class'].'">' . $peer['bgpPeerAdminStatus'] . '</span><br /><span class="label label-'.$peer['state_class'].'">' . $peer['bgpPeerState'] . '</span></strong></td>' . PHP_EOL;
      $string .= '     <td style="white-space: nowrap">' .formatUptime($peer['bgpPeerFsmEstablishedTime']). '<br />
                Updates: <i class="icon-circle-arrow-down" style="color: #008C00;"></i> ' . format_si($peer['bgpPeerInUpdates']) . ' <i class="icon-circle-arrow-up" style="color: #394182;"></i> ' . format_si($peer['bgpPeerOutUpdates']) . '</td>' . PHP_EOL;
      $string .= '  </tr>' . PHP_EOL;

      // Graphs
      $peer_graph = FALSE;
      switch ($vars['graph'])
      {
        case 'prefixes_ipv4unicast':
        case 'prefixes_ipv4multicast':
        case 'prefixes_ipv4vpn':
        case 'prefixes_ipv6unicast':
        case 'prefixes_ipv6multicast':
          $afisafi = preg_replace('/prefixes_(ipv[46])(\w+)/', '$1.$2', $vars['graph']); // prefixes_ipv6unicast ->> ipv6.unicast
          if (in_array($afisafi, $peer_afis) && $peer['bgpPeer_id'])
          {
            $graph_array['type'] = 'bgp_'.$vars['graph'];
            $graph_array['id']   = $peer['bgpPeer_id'];
            $peer_graph          = TRUE;
          }
          break;
        case 'updates':
          if ($peer['bgpPeer_id'])
          {
            $graph_array['type'] = 'bgp_updates';
            $graph_array['id']   = $peer['bgpPeer_id'];
            $peer_graph          = TRUE;
          }
          break;
        case 'macaccounting_bits':
        case 'macaccounting_pkts':
          //FIXME. I really still not know it works or not? -- mike
          // This part copy-pasted from old code as is
          $acc = dbFetchRow("SELECT * FROM `mac_accounting` AS M
                            LEFT JOIN `ip_mac` AS I ON M.mac = I.mac_address
                            LEFT JOIN `ports` AS P ON P.port_id = M.port_id
                            LEFT JOIN `devices` AS D ON D.device_id = P.device_id
                            WHERE I.ip_address = ?", array($peer['bgpPeerRemoteAddr']));
          $database = get_rrd_path($device, "cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd");
          if (is_array($acc) && is_file($database))
          {
            $peer_graph          = TRUE;
            $graph_array['id']   = $acc['ma_id'];
            $graph_array['type'] = $vars['graph'];
          }
          break;
      }

      if ($peer_graph)
      {
        $graph_array['to']     = $config['time']['now'];
        $string .= '  <tr class="'.$peer['html_row_class'].'">' . PHP_EOL;
        $string .= '    <td class="state-marker"></td><td colspan="10" style="white-space: nowrap">' . PHP_EOL;

        $string .= generate_graph_row($graph_array);

        $string .= '    </td>'.PHP_EOL.'  </tr>' . PHP_EOL;
      }
      else if ($list['graph'])
      {
        // Empty row for correct view class table-striped-two
        $string .= '  <tr class="'.$peer['html_row_class'].'"><td class="state-marker"></td><td colspan="10"></td></tr>' . PHP_EOL;
      }
    }

    $string .= '  </tbody>' . PHP_EOL;
    $string .= '</table>';

    $string .= generate_box_close();

    // Print pagination header
    if ($entries['pagination_html']) { $string = $entries['pagination_html'] . $string . $entries['pagination_html']; }

    // Print
    echo $string;
  }
}

/**
 * Params:
 *
 * pagination, pageno, pagesize
 * device, type, adminstatus, state
 */
function get_bgp_array($vars)
{
  $array = array();

  // With pagination? (display page numbers in header)
  $array['pagination'] = (isset($vars['pagination']) && $vars['pagination']);
  pagination($vars, 0, TRUE); // Get default pagesize/pageno
  $array['pageno']   = $vars['pageno'];
  $array['pagesize'] = $vars['pagesize'];
  $start    = $array['pagesize'] * $array['pageno'] - $array['pagesize'];
  $pagesize = $array['pagesize'];

  // Require cached IDs from html/includes/cache-data.inc.php
  $cache_bgp = &$GLOBALS['cache']['bgp'];

  // Begin query generate
  $param = array();
  $where = ' WHERE 1 ';
  foreach ($vars as $var => $value)
  {
    if ($value != '')
    {
      switch ($var)
      {
        case 'device':
        case 'device_id':
          $where .= generate_query_values($value, 'B.device_id');
          break;
        case 'type':
          if ($value == 'external' || $value == 'ebgp')
          {
            $where .= generate_query_values($cache_bgp['external'], 'B.bgpPeer_id');
          }
          else if ($value == 'internal' || $value == 'ibgp')
          {
            $where .= generate_query_values($cache_bgp['internal'], 'B.bgpPeer_id');
          }
          break;
        case 'adminstatus':
          if ($value == 'stop')
          {
            $where .= generate_query_values($cache_bgp['start'], 'B.bgpPeer_id', '!='); // NOT IN
          }
          else if ($value == 'start')
          {
            $where .= generate_query_values($cache_bgp['start'], 'B.bgpPeer_id');
          }
          break;
        case 'state':
          if ($value == 'down')
          {
            $where .= generate_query_values($cache_bgp['up'], 'B.bgpPeer_id', '!='); // NOT IN
          }
          else if ($value == 'up')
          {
            $where .= generate_query_values($cache_bgp['up'], 'B.bgpPeer_id');
          }
          break;
      }
    }
  }

  // Show peers only for permitted devices
  $query_permitted = generate_query_values($cache_bgp['permitted'], 'B.bgpPeer_id');

  $query  = 'FROM `bgpPeers` AS B';
  $query_count = 'SELECT COUNT(*) ' . $query . $where . $query_permitted; // Use only bgpPeer_id and device_id in query!

  $query .= ' LEFT JOIN `bgpPeers-state` AS S ON B.`bgpPeer_id` = S.`bgpPeer_id`';
  $query .= ' LEFT JOIN `devices` AS D ON B.`device_id` = D.`device_id`';
  $query .= $where . $query_permitted;

  $query = 'SELECT D.`hostname`, D.`bgpLocalAs`, B.*, S.* '.$query;
  $query .= ' ORDER BY D.`hostname`, B.`bgpPeerRemoteAs`, B.`bgpPeerRemoteAddr`';
  $query .= " LIMIT $start,$pagesize";

  $peer_devices = array();
  // Query BGP
  foreach (dbFetchRows($query, $param) as $entry)
  {
    humanize_bgp($entry);

    // Collect peer devices for AFI/SAFI
    $peer_devices[$entry['device_id']] = 1;

    $array['entries'][] = $entry;
  }

  // Query AFI/SAFI
  $query_afi = 'SELECT * FROM `bgpPeers_cbgp` WHERE 1'.generate_query_values(array_keys($peer_devices), 'device_id');
  foreach (dbFetchRows($query_afi) as $entry)
  {
    $array['afisafi'][$entry['device_id']][$entry['bgpPeerRemoteAddr']][] = $entry['afi'].'.'.$entry['safi'];
  }

  // Query BGP peers count
  if ($array['pagination'])
  {
    $array['count'] = dbFetchCell($query_count, $param);
    $array['pagination_html'] = pagination($vars, $array['count']);
  } else {
    $array['count'] = count($array['entries']);
  }

  return $array;
}

// EOF
