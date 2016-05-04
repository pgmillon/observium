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

echo('<tr>');

echo('<td style="width: 100px;" class="entity-title"> Vlan ' . $vlan['vlan_vlan'] . '</td>');
echo('<td style="width: 200px;" class="small">' . $vlan['vlan_name'] . '</td>');
echo('<td class="strong">');

  $vlan_ports = array();
  $otherports = dbFetchRows("SELECT * FROM `ports_vlans` AS V, `ports` as P WHERE V.`device_id` = ? AND V.`vlan` = ? AND P.port_id = V.port_id", array($device['device_id'], $vlan['vlan_vlan']));
  foreach ($otherports as $otherport)
  {
   $vlan_ports[$otherport['ifIndex']] = $otherport;
  }
  $otherports = dbFetchRows("SELECT * FROM ports WHERE `device_id` = ? AND `ifVlan` = ?", array($device['device_id'], $vlan['vlan_vlan']));
  foreach ($otherports as $otherport)
  {
   $vlan_ports[$otherport['ifIndex']] = array_merge($otherport, array('untagged' => '1'));
  }
  ksort($vlan_ports);

foreach ($vlan_ports as $port)
{
  humanize_port($port);
  if ($vars['view'] == "graphs")
  {
    echo '<div class="box box-solid" style="display: block; padding: 2px; margin: 2px; min-width: 139px; max-width:139px; height:85px; height:85px; text-align: center; float: left;">
    <div style="font-weight: bold;"><h4>'.short_ifname($port['ifDescr'])."</h4></div>
    <a href='".generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'port', 'port' => $port['port_id']))."' onmouseover=\"return overlib('\
    <div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>".$device['hostname']." - ".$port['ifDescr']."</div>\
    ".$port['ifAlias']." \
    <img src=\'graph.php?type=$graph_type&amp;id=".$port['port_id']."&amp;from=" .$config['time']['twoday']."&amp;to=".$config['time']['now']."&amp;width=450&amp;height=150\'>\
    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<img src='graph.php?type=$graph_type&amp;id=".$port['port_id']."&amp;from=".$config['time']['twoday']."&amp;to=".$config['time']['now']."&amp;width=132&amp;height=40&amp;legend=no'>
    </a>
    <div style='font-size: 9px;'>".short_port_descr($port['ifAlias'])."</div>
   </div>";
  }
  else
  {
    echo($vlan['port_sep'] . generate_port_link($port, short_ifname($port['port_label'])));
    $vlan['port_sep'] = ", ";
    if ($port['untagged']) { echo("(U)"); }

  }
}

echo('</td></tr>');

// EOF
