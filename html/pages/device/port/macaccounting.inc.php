<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

echo(generate_link($descr,$link_array,array('view'=>'macaccounting')));
$graphs = array('bits' => 'Bits', 'pkts' => 'Packets');

$navbar = array();

$navbar['class'] = "navbar-narrow";
$navbar['brand'] = 'Mac Accounting';

$subviews = array('details', 'graphs', 'minigraphs', 'top10');
foreach ($subviews as $type)
{
 $navbar['options'][$type]['text'] = nicecase($type);
 $navbar['options'][$type]['url']  = generate_url($vars,array('subview'=>$type));
  if ($vars['subview'] == $type) {$navbar['options'][$type]['class'] = "active"; }
}

foreach ($graphs as $type => $text)
{
 $navbar['options_right'][$type]['text'] = $text;
 $navbar['options_right'][$type]['url']  = generate_url($link_array,array('view' => 'macaccounting', 'subview' => 'graphs', 'graph'=>$type));
  if ($vars['graph'] == $type) {$navbar['options_right'][$type]['class'] = "active"; }
}

print_navbar($navbar);

// FIXME - REWRITE!

$hostname = $device['hostname'];
$hostid   = $device['port_id'];
$ifname   = $port['ifDescr'];
$ifIndex   = $port['ifIndex'];
$speed = humanspeed($port['ifSpeed']);

$ifalias = $port['name'];

if ($port['ifPhysAddress']) { $mac = $port['ifPhysAddress']; }

$color = "black";
if      ($port['ifAdminStatus'] == "down") { $status = "<span class='grey'>Disabled</span>"; }
else if ($port['ifAdminStatus'] == "up")
{
  if ($port['ifOperStatus'] == "down" || $port['ifOperStatus'] == "lowerLayerDown") { $status = "<span class='red'>Enabled / Disconnected</span>"; }
  else                                                                              { $status = "<span class='green'>Enabled / Connected</span>"; }
}

$i = 1;
$inf = rewrite_ifname($ifname);

echo("<div style='clear: both;'>");

if ($vars['subview'] == "top10")
{

  include("macaccounting_top10.inc.php");

}
else
{

  $query = "SELECT *, `mac_accounting`.`ma_id` as `ma_id` FROM `mac_accounting` LEFT JOIN `mac_accounting-state` ON  `mac_accounting`.`ma_id` =  `mac_accounting-state`.`ma_id` WHERE port_id = ?";
  $param = array($port['port_id']);

 if ($vars['subview'] != minigraphs) {

  if ($vars['subview'] == "graphs") { $table_class = "table-striped-two"; } else { $table_class = "table-striped"; }

  echo('<table class="table table-hover table-condensed   '.$table_class.'">');
  echo('  <thead>');

  echo('<tr>');
  $cols = array(
              'BLANK' => NULL,
              'mac' => 'MAC Address',
              'BLANK' => NULL,
              'ip' => 'IP Address',
              'graphs' => NULL,
              'bps_in' => 'Traffic In',
              'bps_out' => 'Traffic Out',
              'pkts_in' => 'Packets In',
              'pkts_out' => 'Packets Out',
              'BLANK' => NULL);

foreach ($cols as $sort => $col)
{
  if ($col == NULL)
  {
    echo('<th></th>');
  }
  elseif ($vars['sort'] == $sort)
  {
    echo('<th>'.$col.' *</th>');
  } else {
    echo('<th><a href="'. generate_url($vars, array('sort' => $sort)).'">'.$col.'</a></th>');
  }
}

  echo("      </tr>");
  echo('  </thead>');

  }

  $ma_array = dbFetchRows($query, $param);

  switch ($vars['sort'])
  {
    case 'bps_in':
      $ma_array = array_sort($ma_array, 'bytes_input_rate', 'SORT_DESC');
      break;
    case 'bps_out':
      $ma_array = array_sort($ma_array, 'bytes_output_rate', 'SORT_DESC');
      break;
    case 'pkts_in':
      $ma_array = array_sort($ma_array, 'bytes_input_rate', 'SORT_DESC');
      break;
    case 'pkts_out':
      $ma_array = array_sort($ma_array, 'bytes_output_rate', 'SORT_DESC');
      break;
  }

  foreach ($ma_array as $acc)
  {

    $ips = array();
    foreach (dbFetchRows("SELECT `ip_address` FROM `ip_mac` WHERE `mac_address` = ? AND `port_id` = ?", array($acc['mac'], $acc['port_id'])) AS $ip)
    {
      $ips[] = $ip['ip_address'];
    }

    unset($name);
    ///FIXME. Need rewrite, because $addy is array with multiple items.
    #$name = gethostbyaddr($addy['ipv4_address']); FIXME - Maybe some caching for this?

    $arp_host = dbFetchRow("SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D WHERE A.ipv4_address = ? AND I.port_id = A.port_id AND D.device_id = I.device_id", array($addy['ip_address']));
    if ($arp_host) { $arp_name = generate_device_link($arp_host); $arp_name .= " ".generate_port_link($arp_host); } else { unset($arp_if); }

    if ($name == $addy['ip_address']) { unset ($name); }
    if (dbFetchCell("SELECT COUNT(*) FROM bgpPeers WHERE device_id = ? AND bgpPeerIdentifier = ?", array($acc['device_id'], $addy['ip_address'])))
    {
      $peer_info = dbFetchRow("SELECT * FROM bgpPeers WHERE device_id = ? AND bgpPeerIdentifier = ?", array($acc['device_id'], $addy['ip_address']));
    } else { unset ($peer_info); }

    if ($peer_info)
    {
      $asn = "AS".$peer_info['bgpPeerRemoteAs']; $astext = $peer_info['astext'];
    } else {
      unset ($as); unset ($astext); unset($asn);
    }

    if (!isset($vars['graph'])) { $vars['graph'] = "bits"; }
    $graph_type = "macaccounting_" . $vars['graph'];

    if ($vars['subview'] == "minigraphs")
    {
      if (!$asn) { $asn = "No Session"; }

     echo("<div style='display: block; padding: 3px; margin: 3px; min-width: 221px; max-width:221px; min-height:90px; max-height:90px; text-align: center; float: left; background-color: #e5e5e5;'>
      ".$addy['ipv4_address']." - ".$asn."
          <a href='#' onmouseover=\"return overlib('\
     <div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #555555;\'>".$name." - ".$addy['ipv4_address']." - ".$asn."</div>\
     <img src=\'graph.php?id=" . $acc['ma_id'] . "&amp;type=$graph_type&amp;from=".$config['time']['twoday']."&amp;to=".$config['time']['now']."&amp;width=450&amp;height=150\'>\
     ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\" >
          <img src='graph.php?id=" . $acc['ma_id'] . "&amp;type=$graph_type&amp;from=".$config['time']['twoday']."&amp;to=".$config['time']['now']."&amp;width=213&amp;height=45'></a>

          <span style='font-size: 10px;'>".$name."</span>
         </div>");

   }
   else
   {
     echo("
        <tr>
          <td width=20></td>
          <td width=200><bold>".format_mac($acc['mac'])."</bold></td>
          <td width=200>".implode($ips, "<br />")."</td>
          <td width=500>".$name." ".$arp_name . "</td>
          <td width=100>".formatRates($acc['bytes_input_rate'] / 8)."</td>
          <td width=100>".formatRates($acc['bytes_output_rate'] / 8)."</td>
          <td width=100>".format_number($acc['pkts_input_rate'] / 8)."pps</td>
          <td width=100>".format_number($acc['pkts_output_rate'] / 8)."pps</td>
        </tr>
    ");

     $peer_info['astext'];

     if ($vars['subview'] == "graphs")
     {
       $graph_array['type']   = $graph_type;
       $graph_array['id']     = $acc['ma_id'];
       $graph_array['height'] = "100";
       $graph_array['to']     = $config['time']['now'];
       echo('<tr><td colspan="8">');

       print_graph_row($graph_array);

       echo("</td></tr>");
       $i++;
      }
    }
  }
  echo("</table>");
}

// EOF
