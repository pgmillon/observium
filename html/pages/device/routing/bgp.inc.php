<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

?>
<h2 style="padding: 0 10px;">Local AS : <?php echo($device['bgpLocalAs']); ?></h2>

<?php

/// FIXME -- output a table of statistics here to make the local AS thing look less weird.

/// FIXME - this whole page needs rewritte. Use view = graphs / graph = $graphtype.

if(!isset($vars['view'])) { $vars['view'] = "basic"; }
unset($navbar);
$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'routing',
                    'proto'   => 'bgp');

$types = array( 'internal' => 'iBGP',
                'external' => 'eBGP');

foreach ($types as $option => $text)
{
  $navbar['options'][$option]['text'] = $text;
  if ($vars['type'] == $option)
  {
    $navbar['options'][$option]['class'] .= " active";
    $bgp_options = array('type' => NULL);
  } else {
    $bgp_options = array('type' => $option);
  }
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

$navbar['options_right']['basic']['text'] = 'No Graphs';
if ($vars['view'] == 'basic') { $navbar['options_right']['basic']['class'] .= ' active'; }
$navbar['options_right']['basic']['url'] = generate_url($vars, array('view' => 'basic', 'graph' => 'NULL'));

$navbar['options_right']['updates']['text'] = 'Updates';
if ($vars['view'] == 'updates') { $navbar['options_right']['updates']['class'] .= ' active'; }
$navbar['options_right']['updates']['url'] = generate_url($vars, array('view' => 'updates'));

$bgp_graphs = array('unicast'   => array('text' => 'Unicast'),
                    //'multicast' => array('text' => 'Multicast'),
                    'mac'       => array('text' => 'MACaccounting'));
$bgp_graphs['unicast']['types'] = array('prefixes_ipv4unicast' => 'IPv4 Ucast Prefixes',
                                        'prefixes_ipv6unicast' => 'IPv6 Ucast Prefixes',
                                        'prefixes_ipv4vpn'     => 'VPNv4 Prefixes');
//$bgp_graphs['multicast']['types'] = array('prefixes_ipv4multicast' => 'IPv4 Mcast Prefixes',
//                                          'prefixes_ipv6multicast' => 'IPv6 Mcast Prefixes');
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
    $navbar['options_right'][$bgp_graph]['suboptions'][$option]['url'] = generate_url($vars, array('view' => $option));
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
  case 'updates':
    $table_class = 'table-striped-two'; $graphs = 1;
    break;
  default:
    $table_class = 'table-striped';
}

echo('<table class="table table-hover '.$table_class.' table-bordered table-condensed table-rounded">');
echo('<thead>');
echo('<tr><th></th><th></th><th>Peer address</th><th>Type</th><th>AFI.SAFI</th><th>Remote AS</th><th>State</th><th>Uptime</th></tr>');
echo('</thead>');

$where = "";

if ($vars['type'] == "external")
{
  $where .= " AND bgpPeerRemoteAs != '".$device['bgpLocalAs']."'";
} elseif ($vars['type'] == "internal") {
  $where .= " AND bgpPeerRemoteAs = '".$device['bgpLocalAs']."'";
}

if ($vars['adminstatus'] == "stop")
{
  $where .= " AND bgpPeerAdminStatus = 'stop'";
} elseif ($vars['adminstatus'] == "start") {
  $where .= " AND bgpPeerAdminStatus = 'start'";
}

if ($vars['state'] == "down")
{
  $where .= " AND bgpPeerState = 'down'";
}

$sql = 'SELECT * FROM `bgpPeers` AS B
        LEFT JOIN `bgpPeers-state` AS S ON B.bgpPeer_id = S.bgpPeer_id
        WHERE `device_id` = ? '.$where.'
        ORDER BY `bgpPeerRemoteAs`, `bgpPeerRemoteAddr`';

foreach (dbFetchRows($sql, array($device['device_id'])) as $peer)
{
  $peer['bgpLocalAs'] = $device['bgpLocalAs'];
  humanize_bgp($peer);

  $has_macaccounting = dbFetchCell("SELECT COUNT(*) FROM mac_accounting AS M
                                   LEFT JOIN `ip_mac` AS I ON M.mac = I.mac_address
                                   WHERE I.ip_address = ?", array($peer['bgpPeerRemoteAddr']));
  unset ($peerhost, $peername);

  $ip_version = (strstr($peer['bgpPeerRemoteAddr'], ':')) ? 'ipv6' : 'ipv4';
  $peerhost = dbFetchRow('SELECT * FROM '.$ip_version.'_addresses AS A
                         LEFT JOIN ports AS I ON A.port_id = I.port_id
                         LEFT JOIN devices AS D ON I.device_id = D.device_id
                         WHERE A.'.$ip_version.'_address = ?', array($peer['bgpPeerRemoteAddr']));

  if ($peerhost['device_id'])
  {
    $peername = generate_device_link($peerhost, short_hostname($peerhost['hostname']), array('tab' => 'routing', 'proto' => 'bgp'));
    $peer['remote_id'] = $peerhost['device_id'];
  } else {
    $peername = $peer['reverse_dns'];
  }

  unset($sep);
  foreach (dbFetchRows("SELECT * FROM `bgpPeers_cbgp` WHERE `device_id` = ? AND bgpPeerRemoteAddr = ?", array($device['device_id'], $peer['bgpPeerRemoteAddr'])) as $afisafi)
  {
    $afi = $afisafi['afi'];
    $safi = $afisafi['safi'];
    $this_afisafi = $afi.$safi;
    $peer['afi'] .= $sep . $afi .".".$safi;
    $sep = "<br />";
    $peer['afisafi'][$this_afisafi] = 1; // Build a list of valid AFI/SAFI for this peer
  }

  $graph_type       = "bgp_updates";
  $peer_daily_url   = "graph.php?id=" . $peer['bgpPeer_id'] . "&amp;type=" . $graph_type . "&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=500&amp;height=150";
  $peeraddresslink  = "<span class=entity-title><a onmouseover=\"return overlib('<img src=\'$peer_daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">" . $peer['human_remoteip'] . "</a></span>";

  echo('<tr class="'.$peer['html_row_class'].'">');
  echo('
         <td style="width: 1px; background-color: '.$peer['table_tab_colour'].'; margin: 0px; padding: 0px"></td>
         <td style="width: 1px;"></td>');

  echo("   <td>" . $peeraddresslink . "<br />" . $peername . "</td>
           <td><strong>".$peer['peer_type']."</strong></td>
           <td style='font-size: 10px; font-weight: bold; line-height: 10px;'>" . (isset($peer['afi']) ? $peer['afi'] : '') . "</td>
           <td><strong>AS" . $peer['bgpPeerRemoteAs'] . "</strong><br />" . $peer['astext'] . "</td>
           <td><strong><span class='".$peer['admin_class']."'>" . $peer['bgpPeerAdminStatus'] . "</span><br /><span class='".$peer['state_class']."'>" . $peer['bgpPeerState'] . "</span></strong></td>
           <td>" .formatUptime($peer['bgpPeerFsmEstablishedTime']). "<br />
               Updates <i class='oicon-arrow_down'></i> " . format_si($peer['bgpPeerInUpdates']) . "
                       <i class='oicon-arrow_up'></i> " . format_si($peer['bgpPeerOutUpdates']) . "</td>
          </tr>");

  unset($invalid);

  switch ($vars['view'])
  {
    case 'prefixes_ipv4unicast':
    case 'prefixes_ipv4multicast':
    case 'prefixes_vpnv4unicast':
    case 'prefixes_ipv6unicast':
    case 'prefixes_ipv6multicast':
      list(,$afisafi) = explode("_", $vars['view']);
      if (isset($peer['afisafi'][$afisafi])) { $peer['graph'] = 1; }
      // FIXME no break??
    case 'updates':
      $graph_array['type']   = "bgp_" . $vars['view'];
      $graph_array['id']     = $peer['bgpPeer_id'];
  }

  switch ($vars['view'])
  {
    case 'macaccounting_bits':
    case 'macaccounting_pkts':
      $acc = dbFetchRow("SELECT * FROM `mac_accounting` AS M
                        LEFT JOIN `ip_mac`   AS I ON M.mac = I.mac_address
                        LEFT JOIN `ports`    AS P ON P.port_id = M.port_id
                        LEFT JOIN `devices`  AS D ON D.device_id = P.device_id
                        WHERE I.ip_address = ?", array($peer['bgpPeerRemoteAddr']));
      $database = get_rrd_path($device, "cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd");
      if (is_array($acc) && is_file($database))
      {
        $peer['graph']       = 1;
        $graph_array['id']   = $acc['ma_id'];
        $graph_array['type'] = $vars['view'];
      }
  }
  if ($vars['view'] == 'updates') { $peer['graph'] = 1; }

  if ($graphs == 1)
  {
    echo('<tr><td colspan="8">');
    if ($peer['graph'])
    {
      $graph_array['to']     = $config['time']['now'];

      print_graph_row($graph_array);
    }
    echo("</td></tr>");
  }
  unset($valid_afi_safi);
}

echo("</table>");

// EOF
