<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

humanize_device($device);

/// These should be summed at poller time
$port_count   = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `device_id` = ?", array($device['device_id']));
$sensor_count = dbFetchCell("SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ?", array($device['device_id']));

echo('  <tr class="'.$device['html_row_class'].'" onclick="location.href=\'device/device='.$device['device_id'].'/\'" style="cursor: pointer;">
          <td style="width: 1px; background-color: '.$device['html_tab_colour'].'; margin: 0px; padding: 0px"></td>
          <td style="width: 40px; padding: 10px; text-align: center; vertical-align: middle;">' . getImage($device) . '</td>
          <td style="width: 300px;"><span class="entity-title">' . generate_device_link($device) . '</span>
          <br />' . truncate($device['location'],32, '') . '</td>'
        );

echo('<td>');

  if (isset($config['os'][$device['os']]['over']))
{
  $graphs = $config['os'][$device['os']]['over'];
}
elseif (isset($device['os_group']) && isset($config['os'][$device['os_group']]['over']))
{
  $graphs = $config['os'][$device['os_group']]['over'];
}
else
{
  $graphs = $config['os']['default']['over'];
}

$graph_array = array();
$graph_array['height'] = "100";
$graph_array['width']  = "310";
$graph_array['to']     = $config['time']['now'];
$graph_array['device'] = $device['device_id'];
$graph_array['type']   = "device_bits";
$graph_array['from']   = $config['time']['day'];
$graph_array['legend'] = "no";

$graph_array['height'] = "45";
$graph_array['width']  = "175";
$graph_array['bg']     = "FFFFFF00";

foreach ($graphs as $entry)
{
  if ($entry['graph'])
  {
    $graph_array['type']   = $entry['graph'];
    $graph_array['popup_title'] = $entry['text'];

    echo('<div class="pull-right" style="height: 50px; padding: 2px; margin: 0;">');
    print_graph_popup($graph_array);
    echo("</div>");
  }
}

unset($graph_array);

echo('</td>');

echo(' </tr>');

// EOF
