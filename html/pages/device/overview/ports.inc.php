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

if ($ports['total'])
{
?>

<div class="well info_box">
    <div class="title"><a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'ports'))); ?>">
      <i class="oicon-network-ethernet"></i> Ports</a></div>
    <div class="content">

<?php
  $graph_array['height'] = "100";
  $graph_array['width']  = "512";
  $graph_array['to']     = $config['time']['now'];
  $graph_array['device']          = $device['device_id'];
  $graph_array['type']   = "device_bits";
  $graph_array['from']   = $config['time']['day'];
  $graph_array['legend'] = "no";
  $graph = generate_graph_tag($graph_array);

  $link_array = $graph_array;
  $link_array['page'] = "graphs";
  unset($link_array['height'], $link_array['width']);
  $link = generate_url($link_array);

  $graph_array['width']  = "210";
  $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . " - Device Traffic");

  echo(overlib_link($link, $graph, $overlib_content, NULL));

  echo('  <div style="height: 5px;"></div>');

  echo('<table class="table table-condensed table-striped table-bordered">
    <tr style="background-color: ' . $ports_colour . '; align: center;"><td></td>
      <td style="width: 25%"><img src="images/16/connect.png" alt="" /> ' . $ports['total'] . '</td>
      <td style="width: 25%" class="green"><img src="images/16/if-connect.png" alt="" /> ' . $ports['up'] . '</td>
      <td style="width: 25%" class="red"><img src="images/16/if-disconnect.png" alt="" /> ' . $ports['down'] . '</td>
      <td style="width: 25%" class="grey"><img src="images/16/if-disable.png" alt="" /> ' . $ports['disabled'] . '</td>
    </tr>
  </table>');

  echo('  <div style="margin: 8px; font-size: 11px; font-weight: bold;">');

  $ifsep = "";

  foreach (dbFetchRows("SELECT * FROM `ports` WHERE device_id = ? AND `deleted` != '1'", array($device['device_id'])) as $data)
  {
    humanize_port($data);
    $data = array_merge($data, $device);
    echo("$ifsep" . generate_port_link($data, short_ifname(strtolower($data['label']))));
    $ifsep = ", ";
  }

  unset($ifsep);
  echo("  </div>");
  echo("</div></div>");
}

// EOF
