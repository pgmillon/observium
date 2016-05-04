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
?>

<div class="box box-solid">
<table class="table table-condensed  table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th style="width: 450px;">Description</th>
      <th style="width: 100px;">Graphs</th>
      <th>Type</th>
      <th>Status</th>
      <th>Errors</th>
      <th>Load</th>
    </tr>
  </thead>
  <tbody>

<?php
foreach (dbFetchRows("SELECT * FROM `hrDevice` WHERE `device_id` = ? ORDER BY `hrDeviceIndex`", array($device['device_id'])) as $hrdevice)
{
  echo("    <tr>\n");
  echo("      <td>".$hrdevice['hrDeviceIndex']."</td>\n");

  if ($hrdevice['hrDeviceType'] == "hrDeviceProcessor")
  {
    $proc_id = dbFetchCell('SELECT processor_id FROM processors WHERE device_id = ? AND hrDeviceIndex = ?', array($device['device_id'], $hrdevice['hrDeviceIndex']));
    $proc_url   = "device/device=".$device['device_id']."/tab=health/metric=processor/";

    echo("      <td>" . generate_entity_link('processor', $proc_id) . "</td>\n");

    $graph_array['height'] = "20";
    $graph_array['width']  = "100";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $proc_id;
    $graph_array['type']   = 'processor_usage';
    $graph_array['from']     = $config['time']['day'];
    $graph_array_zoom   = $graph_array;
    $graph_array_zoom['height'] = "150";
    $graph_array_zoom['width'] = "400";

    $mini_graph = overlib_link($proc_url, generate_graph_tag($graph_array), generate_graph_tag($graph_array_zoom),  NULL);

    echo("      <td>".$mini_graph."</td>\n");
  }
  elseif ($hrdevice['hrDeviceType'] == "hrDeviceNetwork")
  {
    $int = str_replace("network interface ", "", $hrdevice['hrDeviceDescr']);
    $interface = dbFetchRow("SELECT * FROM ports WHERE device_id = ? AND ifDescr = ?", array($device['device_id'], $int));
    if ($interface['ifIndex'])
    {
      echo("      <td>".generate_port_link($interface)."</td>\n");

      $graph_array['height'] = "20";
      $graph_array['width']  = "100";
      $graph_array['to']     = $config['time']['now'];
      $graph_array['id']     = $interface['port_id'];
      $graph_array['type']   = 'port_bits';
      $graph_array['from']   = $config['time']['day'];
      $graph_array_zoom      = $graph_array;
      $graph_array_zoom['height'] = "150";
      $graph_array_zoom['width'] = "400";

      // FIXME click on graph should also link to port, but can't use generate_port_link here...
      $mini_graph = overlib_link(generate_port_url($interface), generate_graph_tag($graph_array), generate_graph_tag($graph_array_zoom),  NULL);

      echo("      <td>$mini_graph</td>");
    } else {
      echo("      <td>".$hrdevice['hrDeviceDescr']."</td>");
      echo("      <td></td>");
    }
  } else {
    echo("      <td>".$hrdevice['hrDeviceDescr']."</td>");
    echo("      <td></td>");
  }

  echo("      <td>".$hrdevice['hrDeviceType'].'</td><td>'.$hrdevice['hrDeviceStatus']."</td>");
  echo("      <td>".$hrdevice['hrDeviceErrors'].'</td><td>'.$hrdevice['hrProcessorLoad']."</td>");
  echo("    </tr>");
}

echo("  </tbody>\n");
echo("</table>\n");
echo '</div>';
$page_title[] = "Inventory";

// EOF
