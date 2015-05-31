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

<table class="table table-condensed table-bordered table-striped">
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
    $proc_popup  = "onmouseover=\"return overlib('<div class=entity-title>".$device['hostname']." - ".$hrdevice['hrDeviceDescr'];
    $proc_popup .= "</div><img src=\'graph.php?id=" . $proc_id . "&amp;type=processor_usage&amp;from=".$config['time']['month']."&amp;to=".$config['time']['now']."&amp;width=400&amp;height=125\'>";
    $proc_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";
    echo("      <td><a href='$proc_url' $proc_popup>".$hrdevice['hrDeviceDescr']."</a></td>\n");

    $graph_array['height'] = "20";
    $graph_array['width']  = "100";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $proc_id;
    $graph_array['type']   = 'processor_usage';
    $graph_array['from']     = $config['time']['day'];
    $graph_array_zoom   = $graph_array; $graph_array_zoom['height'] = "150"; $graph_array_zoom['width'] = "400";

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
      $graph_array_zoom      = $graph_array; $graph_array_zoom['height'] = "150"; $graph_array_zoom['width'] = "400";

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

$pagetitle[] = "Inventory";

// EOF
