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

if(device_permitted($device))
{

  // Only show aggregate graph if we have access to the entire device.

  echo generate_box_open();

  echo('<table class="table table-condensed table-striped table-hover ">');

 $graph_title = nicecase($vars['metric']);
  $graph_array['type'] = "device_".$vars['metric'];
  $graph_array['device'] = $device['device_id'];
  $graph_array['legend'] = no;

  echo('<tr><td>');
  echo('<h3>' . $graph_title . '</h3>');
  print_graph_row($graph_array);
  echo('</td></tr>');

  echo('</table>');

  echo generate_box_close();

}

echo generate_box_open();

echo('<table class="table table-striped table-condensed ">');

//echo("<thead><tr>
//        <th>Device</th>
//      </tr></thead>");

$row = 1;

foreach (dbFetchRows("SELECT * FROM `ucd_diskio` WHERE device_id = ? ORDER BY diskio_descr", array($device['device_id'])) as $drive)
{

  $fs_url   = "device/device=".$device['device_id']."/tab=health/metric=diskio/";

  $graph_array_zoom['id']     = $drive['diskio_id'];
  $graph_array_zoom['type']   = "diskio_ops";
  $graph_array_zoom['width']  = "400";
  $graph_array_zoom['height'] = "125";
  $graph_array_zoom['from']   = $config['time']['twoday'];
  $graph_array_zoom['to']     = $config['time']['now'];

  echo("<tr><td><h3>");
  echo(overlib_link($fs_url, $drive['diskio_descr'], generate_graph_tag($graph_array_zoom),  NULL));
  echo("</h3>");

  $types = array("diskio_bits", "diskio_ops");

  foreach ($types as $graph_type)
  {

    $graph_array           = array();
    $graph_array['id']     = $drive['diskio_id'];
    $graph_array['type']   = $graph_type;

    print_graph_row($graph_array);

  }

  $row++;
}

echo "</td></tr>";
echo "</table>";

echo generate_box_close();

// EOF
