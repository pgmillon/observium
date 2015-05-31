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

if ($_SESSION['userlevel'] < '5')
{
  include("includes/error-no-perm.inc.php");
}
else
{
  if (!isset($vars['view'])) { $vars['view'] = 'details'; }
  unset($navbar);
  $link_array = array('page' => 'routing',
                      'protocol' => 'bgp');

  $types = array('all'      => 'All',
                 'internal' => 'iBGP',
                 'external' => 'eBGP');
  foreach ($types as $option => $text)
  {
    $navbar['options'][$option]['text'] = $text;
    if ($vars['type'] == $option || (empty($vars['type']) && $option == 'all')) { $navbar['options'][$option]['class'] .= " active"; }
    $bgp_options = array('type' => $option);
    if ($vars['adminstatus']) { $bgp_options['adminstatus'] = $vars['adminstatus']; }
    elseif ($vars['state']) { $bgp_options['state'] = $vars['state']; }
    $navbar['options'][$option]['url'] = generate_url($link_array, $bgp_options);
  }

  $statuses = array('stop'  => 'Shutdown',
                    'start' => 'Enabled',
                    'down'  => 'Down');
  foreach ($statuses as $option => $text)
  {
    $status = ($option == 'down') ? 'state' : 'adminstatus';
    $navbar['options'][$option]['text'] = $text;
    if ($vars[$status] == $option)
    {
      $navbar['options'][$option]['class'] .= " active";
      $bgp_options = array($status => NULL);
    } else {
      $bgp_options = array($status => $option);
    }
    if ($vars['type']) { $bgp_options['type'] = $vars['type']; }
    $navbar['options'][$option]['url'] = generate_url($link_array, $bgp_options);
  }

  $navbar['options_right']['details']['text'] = 'No Graphs';
  if ($vars['view'] == 'details') { $navbar['options_right']['details']['class'] .= ' active'; }
  $navbar['options_right']['details']['url'] = generate_url($vars, array('view' => 'details', 'graph' => 'NULL'));

  $navbar['options_right']['updates']['text'] = 'Updates';
  if ($vars['graph'] == 'updates') { $navbar['options_right']['updates']['class'] .= ' active'; }
  $navbar['options_right']['updates']['url'] = generate_url($vars, array('view' => 'graphs', 'graph' => 'updates'));

  $bgp_graphs = array('unicast'   => array('text' => 'Unicast'),
                      'multicast' => array('text' => 'Multicast'),
                      'mac'       => array('text' => 'MACaccounting'));
  $bgp_graphs['unicast']['types'] = array('prefixes_ipv4unicast' => 'IPv4 Ucast Prefixes',
                                          'prefixes_ipv6unicast' => 'IPv6 Ucast Prefixes',
                                          'prefixes_ipv4vpn'     => 'VPNv4 Prefixes');
  $bgp_graphs['multicast']['types'] = array('prefixes_ipv4multicast' => 'IPv4 Mcast Prefixes',
                                            'prefixes_ipv6multicast' => 'IPv6 Mcast Prefixes');
  $bgp_graphs['mac']['types'] = array('macaccounting_bits' => 'MAC Bits',
                                      'macaccounting_pkts' => 'MAC Pkts');
  foreach ($bgp_graphs as $bgp_graph => $bgp_options)
  {
    $navbar['options_right'][$bgp_graph]['text'] = $bgp_options['text'];
    foreach ($bgp_options['types'] as $option => $text)
    {
      if ($vars['graph'] == $option)
      {
        $navbar['options_right'][$bgp_graph]['class'] .= ' active';
        $navbar['options_right'][$bgp_graph]['suboptions'][$option]['class'] = 'active';
      }
      $navbar['options_right'][$bgp_graph]['suboptions'][$option]['text'] = $text;
      $navbar['options_right'][$bgp_graph]['suboptions'][$option]['url'] = generate_url($vars, array('view' => 'graphs', 'graph' => $option));
    }
  }

  $navbar['class'] = "navbar-narrow";
  $navbar['brand'] = "BGP";
  print_navbar($navbar);

  switch ($vars['view'])
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
      break;
    default:
      $table_class = 'table-striped';
  }

  echo('<table class="table table-hover table-bordered '.$table_class.' table-condensed table-rounded">');
  echo('<thead>');

  echo('<tr><th></th><th></th><th>Local address</th><th></th><th>Peer address</th><th>Type</th><th>Family</th><th>Remote AS</th><th>State</th><th style="width: 200px;">Uptime / Updates</th></tr>');
  echo('</thead>');

  if ($vars['type'] == "external")
  {
    $where = " AND D.`bgpLocalAs` != B.`bgpPeerRemoteAs`";
  } elseif ($vars['type'] == "internal") {
    $where = " AND D.`bgpLocalAs` = B.`bgpPeerRemoteAs`";
  }

  if ($vars['adminstatus'] == "stop")
  {
    $where .= " AND (B.`bgpPeerAdminStatus` = 'stop' OR B.`bgpPeerAdminStatus` = 'halted')";
  } elseif ($vars['adminstatus'] == "start")
  {
    $where .= " AND (B.`bgpPeerAdminStatus` = 'start' OR B.`bgpPeerAdminStatus` = 'running')";
  }

  if ($vars['state'] == "down")
  {
    $where .= " AND (B.`bgpPeerState` != 'established')";
  }

  if (!$config['web_show_disabled']) { $where .= ' AND D.`disabled` = 0 '; }

  $peer_query = 'SELECT * FROM `bgpPeers` AS B
                 LEFT JOIN `bgpPeers-state` AS S ON B.`bgpPeer_id` = S.`bgpPeer_id`
                 LEFT JOIN `devices` AS D ON B.`device_id` = D.`device_id`
                 WHERE 1 ' . $where .
                 ' ORDER BY D.`hostname`, B.`bgpPeerRemoteAs`, B.`bgpPeerRemoteAddr`';
  foreach (dbFetchRows($peer_query) as $peer)
  {
    humanize_bgp($peer);

    $ip_version = (strstr($peer['bgpPeerRemoteAddr'], ':')) ? 'ipv6' : 'ipv4';
    $peerhost = dbFetchRow('SELECT * FROM `'.$ip_version.'_addresses` AS A
                           LEFT JOIN `ports` AS I ON A.`port_id` = I.`port_id`
                           LEFT JOIN `devices` AS D ON I.`device_id` = D.`device_id`
                           WHERE A.`'.$ip_version.'_address` = ?', array($peer['bgpPeerRemoteAddr']));

    if ($peerhost['device_id'])
    {
      $peername = generate_device_link($peerhost, short_hostname($peerhost['hostname']), array('tab' => 'routing', 'proto' => 'bgp'));
      $peer['remote_id'] = $peerhost['device_id'];
    } else {
      $peername = $peer['reverse_dns'];
    }

    // display overlib graphs

    $graph_type       = "bgp_updates";
    $local_daily_url  = "graph.php?id=" . $peer['bgpPeer_id'] . "&amp;type=" . $graph_type . "&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=500&amp;height=150&amp;afi=ipv4&amp;safi=unicast";
    $localaddresslink = "<span class=entity-title><a href='device/device=" . $peer['device_id'] . "/tab=routing/proto=bgp/' onmouseover=\"return overlib('<img src=\'$local_daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">" . $peer['human_localip'] . "</a></span>";

    if ($peer['remote_id'])
    {
      $graph_type       = "bgp_updates";
      $peer_daily_url   = "graph.php?id=" . $peer['bgpPeer_id'] . "&amp;type=" . $graph_type . "&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=500&amp;height=150";
      $peeraddresslink  = "<span class=entity-title><a href='device/device=" . $peer['remote_id'] . "/tab=routing/proto=bgp/' onmouseover=\"return overlib('<img src=\'$peer_daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">" . $peer['human_remoteip'] . "</a></span>";
      $peeraddresslink = generate_entity_link('bgp_peer', $peer);
    } else {
      $peeraddresslink  = "<span class=entity-title>" . $peer['human_remoteip'] . "</span>";
    }

    echo('<tr class="'.$peer['html_row_class'].'">');

    unset($sep);
    foreach (dbFetchRows("SELECT * FROM `bgpPeers_cbgp` WHERE `device_id` = ? AND bgpPeerRemoteAddr = ?", array($peer['device_id'], $peer['bgpPeerRemoteAddr'])) as $afisafi)
    {
      $afi = $afisafi['afi'];
      $safi = $afisafi['safi'];
      $this_afisafi = $afi.$safi;
      $peer['afi'] .= $sep . $afi .".".$safi;
      $sep = "<br />";
      $peer['afisafi'][$this_afisafi] = 1; // Build a list of valid AFI/SAFI for this peer
    }
    unset($sep);

    echo('
         <td style="width: 1px; background-color: '.$peer['table_tab_colour'].'; margin: 0px; padding: 0px"></td>
         <td style="width: 1px;"></td>');

    echo("
            <td style='width: 150px;'>" . $localaddresslink . "<br />".generate_device_link($peer, short_hostname($peer['hostname']), array('tab' => 'routing', 'proto' => 'bgp'))."</td>
            <td style='width: 30px;'><b>&#187;</b></td>
            <td style='width: 150px;'>" . $peeraddresslink . "<br />" . $peername . "</td>
            <td style='width: 50px;'><b>".$peer['peer_type']."</b></td>
            <td style='width: 50px;'><small>".$peer['afi']."</small></td>
            <td><strong>AS" . $peer['bgpPeerRemoteAs'] . "</strong><br />" . $peer['astext'] . "</td>
            <td><strong><span class='".$peer['admin_class']."'>" . $peer['bgpPeerAdminStatus'] . "</span><br /><span class='".$peer['state_class']."'>" . $peer['bgpPeerState'] . "</span></strong></td>
            <td>" .formatUptime($peer['bgpPeerFsmEstablishedTime']). "<br />
                Updates <i class='oicon-arrow_down'></i> " . format_si($peer['bgpPeerInUpdates']) . "
                        <i class='oicon-arrow_up'></i> " . format_si($peer['bgpPeerOutUpdates']) . "</td></tr>");

    unset($invalid);
    switch ($vars['graph'])
    {
      case 'prefixes_ipv4unicast':
      case 'prefixes_ipv4multicast':
      case 'prefixes_ipv4vpn':
      case 'prefixes_ipv6unicast':
      case 'prefixes_ipv6multicast':
        list(,$afisafi) = explode("_", $vars['graph']);
        if (isset($peer['afisafi'][$afisafi])) { $peer['graph'] = 1; }
      case 'updates':
        $graph_array['type']   = "bgp_" . $vars['graph'];
        $graph_array['id']     = $peer['bgpPeer_id'];
    }

    switch ($vars['graph'])
    {
      case 'macaccounting_bits':
      case 'macaccounting_pkts':
        ///FIXME. This is worked? -- mike
        $acc = dbFetchRow("SELECT * FROM `mac_accounting` AS M
                          LEFT JOIN `ip_mac` AS I ON M.mac = I.mac_address
                          LEFT JOIN `ports` AS P ON P.port_id = M.port_id
                          LEFT JOIN `devices` AS D ON D.device_id = P.device_id
                          WHERE I.ip_address = ?", array($peer['bgpPeerRemoteAddr']));
        $database = get_rrd_path($device, "cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd");
        if (is_array($acc) && is_file($database))
        {
          $peer['graph']       = 1;
          $graph_array['id']   = $acc['ma_id'];
          $graph_array['type'] = $vars['graph'];
        }
    }

    if ($vars['graph'] == 'updates') { $peer['graph'] = 1; }

    if ($peer['graph'])
    {
        $graph_array['to']     = $config['time']['now'];
    echo('<tr class="'.$peer['html_row_class'].'">
         <td colspan="11">');

        print_graph_row($graph_array);

        echo("</td></tr>");
    }
  }

  echo("</table>");
}

// EOF
