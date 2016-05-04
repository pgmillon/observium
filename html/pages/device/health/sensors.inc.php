<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

if ($vars['view'] == "graphs") { $stripe_class = "table-striped-two"; } else { $stripe_class = "table-striped"; }

echo('<table class="table '.$stripe_class.' table-condensed table-bordered">');

echo('
  <thead>
    <tr>
      <th class="state-marker"></th>
      <th>'.nicecase($vars['metric']).'</th>
      <th>MIB</th>
      <th>Thresholds</th>
      <th width="100"></th>
      <th width="80">Value</th>
    </tr>
  </thead>');

$row = 1;

$sql  = "SELECT *, `sensors`.`sensor_id` AS `sensor_id`";
$sql .= " FROM  `sensors`";
$sql .= " LEFT JOIN  `sensors-state` ON  `sensors`.sensor_id =  `sensors-state`.sensor_id";
$sql .= " WHERE `sensor_class` = ? AND `device_id` = ?";

foreach (dbFetchRows($sql, array($vars['metric'], $device['device_id'])) as $sensor)
{

  humanize_sensor($sensor);

  $graph_array           = array();
  $graph_array['to']     = $config['time']['now'];
  $graph_array['id']     = $sensor['sensor_id'];
  $graph_array['type']   = "sensor_graph";
  $graph_array['width']  = 80;
  $graph_array['height'] = 20;
  $graph_array['bg']     = 'ffffff00'; # the 00 at the end makes the area transparent.
  $graph_array['from']   = $config['time']['day'];

  echo('
    <tr class="'.$sensor['row_class'].'">
      <td class="state-marker"></td>
      <td class="entity">' . generate_entity_link("sensor", $sensor) . '</td>
      <td class="text-right"><i>'.$sensor['sensor_type'].'</i></td>
      <td><span class="label">' . $sensor['sensor_thresholds'] . '</span></td>
      <td>'.generate_entity_link("sensor", $sensor, generate_graph_tag($graph_array), FALSE, FALSE).'</td>
      <td style="text-align: right;"><span class="'.$sensor['state_class'].'">' . $sensor['human_value'] . $sensor['sensor_symbol'] . '</span></td>
    </tr>');


  if ($vars['view'] == "graphs")
  {
    echo('<tr class="'.$sensor['row_class'].'"><td class="state-marker"></td><td colspan=5>');

    unset($graph_array['height'], $graph_array['width'], $graph_array['legend']);
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $sensor['sensor_id'];
    $graph_array['type']   = 'sensor_graph';

    print_graph_row($graph_array, TRUE);

    echo('</td></tr>');
  } # endif graphs

}

echo("</table>");

// EOF
