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

$vlans = dbFetchRows('SELECT * FROM `ports_vlans` AS PV, vlans AS V WHERE PV.`port_id` = ? and PV.`device_id` = ? AND V.`vlan_vlan` = PV.vlan AND V.device_id = PV.device_id', array($port['port_id'], $device['device_id']));

echo('<table class="table  table-striped table-hover table-condensed">');

echo("<thead><tr><th>VLAN</th><th>Description</th><th>Cost</th><th>Priority</th><th>State</th><th>Other Ports</th></tr></thead>");

$row = 0;

foreach ($vlans as $vlan)
{
  $row++;
  if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }
  echo('<tr>');

  echo('<td style="width: 100px;" class="entity-title"> Vlan ' . $vlan['vlan'] . '</td>');
  echo('<td style="width: 200px;" class="small">' . $vlan['vlan_name'] . '</td>');

  if ($vlan['state'] == "blocking") { $class="red"; } elseif ($vlan['state'] == "forwarding" ) { $class="green"; } else { $class = "none"; }

  echo("<td>".$vlan['cost']."</td><td>".$vlan['priority']."</td><td class=$class>".$vlan['state']."</td>");

  $vlan_ports = array();
  $otherports = dbFetchRows("SELECT * FROM `ports_vlans` AS V, `ports` as P WHERE V.`device_id` = ? AND V.`vlan` = ? AND P.port_id = V.port_id", array($device['device_id'], $vlan['vlan']));
  foreach ($otherports as $otherport)
  {
   $vlan_ports[$otherport['ifIndex']] = $otherport;
  }
  $otherports = dbFetchRows("SELECT * FROM ports WHERE `device_id` = ? AND `ifVlan` = ?", array($device['device_id'], $vlan['vlan']));
  foreach ($otherports as $otherport)
  {
   $vlan_ports[$otherport['ifIndex']] = array_merge($otherport, array('untagged' => '1'));
  }
  ksort($vlan_ports);

  echo("<td>");
  $vsep='';
  foreach ($vlan_ports as $otherport)
  {
    echo($vsep.generate_port_link($otherport, short_ifname($otherport['ifDescr'])));
    if ($otherport['untagged']) { echo("(U)"); }
    $vsep=", ";
  }
  echo("</td>");
  echo("</tr>");
}

echo("</table>");

// EOF
