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

echo generate_box_open();

echo('<table class="table table-hover table-striped table-condensed">');
echo('<thead><tr><th>Device</th><th>Router Id</th><th>Status</th><th>ABR</th><th>ASBR</th><th>Areas</th><th>Ports</th><th>Neighbours</th></tr></thead>');

// Loop Instances

foreach (dbFetchRows("SELECT * FROM `ospf_instances` WHERE `ospfAdminStat` IN ('enabled', 'disabled')".$GLOBALS['cache']['where']['devices_permitted']) as $instance)
{
  $device = device_by_id_cache($instance['device_id']);

  $row_class = '';
  if ($instance['ospfAdminStat'] == "enabled")
  {
    $enabled = '<span style="color: #00aa00">enabled</span>';

    $area_count         = dbFetchCell('SELECT COUNT(*) FROM `ospf_areas` WHERE `device_id` = ?', array($device['device_id']));
    $port_count         = dbFetchCell('SELECT COUNT(*) FROM `ospf_ports` WHERE `device_id` = ?', array($device['device_id']));
    $port_count_enabled = dbFetchCell("SELECT COUNT(*) FROM `ospf_ports` WHERE `ospfIfAdminStat` = 'enabled' AND `device_id` = ?", array($device['device_id']));
    $neighbour_count    = dbFetchCell('SELECT COUNT(*) FROM `ospf_nbrs` WHERE `device_id` = ?', array($device['device_id']));

    if ($port_count_enabled == 0 || $neighbour_count == 0)
    {
      $row_class = 'warning';
    }
  } else {
    $enabled = '<span style="color: #aaaaaa">disabled</span>';
    $row_class = 'error';

    $area_count         = 0;
    $port_count         = 0;
    $port_count_enabled = 0;
    $neighbour_count    = 0;
  }

  /*
  $ip_query = "SELECT * FROM ipv4_addresses AS A, ports AS I WHERE ";
  $ip_query .= "(A.ipv4_address = ? AND I.port_id = A.port_id)";
  $ip_query .= " AND I.device_id = ?";
  $ipv4_host = dbFetchRow($ip_query, array($peer['bgpPeerIdentifier'], $device['device_id']));
  */

  if ($instance['ospfAreaBdrRtrStatus'] == "true") { $abr = '<span style="color: #00aa00">yes</span>'; } else { $abr = '<span style="color: #aaaaaa">no</span>'; }
  if ($instance['ospfASBdrRtrStatus'] == "true") { $asbr = '<span style="color: #00aa00">yes</span>'; } else { $asbr = '<span style="color: #aaaaaa">no</span>'; }

  echo('<tr class="'.$row_class.'">');
  echo('  <td class="entity-title">'.generate_device_link($device, 0, array('tab' => 'routing', 'proto' => 'ospf')). '</td>');
  echo('  <td class="entity-title">'.$instance['ospfRouterId'] . '</td>');
  echo('  <td>' . $enabled . '</td>');
  echo('  <td>' . $abr . '</td>');
  echo('  <td>' . $asbr . '</td>');
  echo('  <td>' . $area_count . '</td>');
  echo('  <td>' . $port_count . '('.$port_count_enabled.')</td>');
  echo('  <td>' . $neighbour_count . '</td>');
  echo('</tr>');

} // End loop instances

echo('</table>');

echo generate_box_close();

// EOF
