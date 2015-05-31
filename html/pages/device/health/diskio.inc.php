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

echo('<table class="table table-striped table-condensed table-bordered">');

echo("<thead><tr>
        <th>Device</th>
      </tr></thead>");

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

  echo("<tr bgcolor='$row_colour'><td><span class='entity-title'>");
  echo(overlib_link($fs_url, $drive['diskio_descr'], generate_graph_tag($graph_array_zoom),  NULL));
  echo("</span><br />");

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

echo("</td></tr>");
echo("</table>");

// EOF
