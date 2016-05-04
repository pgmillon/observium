<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

global $sensor_type;

$sql  = "SELECT *, `sensors`.`sensor_id` AS `sensor_id` FROM `sensors`";
$sql .= " LEFT JOIN `sensors-state` ON `sensors`.`sensor_id` = `sensors-state`.`sensor_id`";
$sql .= " WHERE `sensors`.`sensor_class` = ?" . generate_query_permitted(array('device'));

// Groups
if (isset($vars['group']))
{
  $values = get_group_entities($vars['group']);
  $sql .= generate_query_values($values, 'sensors.sensor_id');
}

$sensors = array();
foreach (dbFetchRows($sql, array($sensor_type)) as $sensor)
{
  if (isset($cache['devices']['id'][$sensor['device_id']]))
  {
    $sensor['hostname']       = $cache['devices']['id'][$sensor['device_id']]['hostname'];
    $sensor['html_row_class'] = $cache['devices']['id'][$sensor['device_id']]['html_row_class'];
    $sensors[] = $sensor;
  }
}
$sensors = array_sort_by($sensors, 'hostname', SORT_ASC, SORT_STRING, 'sensor_descr', SORT_ASC, SORT_STRING);
$sensors_count = count($sensors);

// Pagination
$pagination_html = pagination($vars, $sensors_count);
echo $pagination_html;

if ($vars['pageno'])
{
  $sensors = array_chunk($sensors, $vars['pagesize']);
  $sensors = $sensors[$vars['pageno']-1];
}
// End Pagination

if ($vars['view'] == "graphs") { $stripe_class = "table-striped-two"; } else { $stripe_class = "table-striped"; }

echo('<table class="table '.$stripe_class.' table-condensed table-bordered">');
echo('  <thead>');
echo('    <tr>');
echo('      <th class="state-marker"></th>');
echo('      <th style="width: 250px;">Device</th>');
echo('      <th>Sensor</th>');
echo('      <th style="width: 40px;"></th>');
echo('      <th style="width: 100px;">Thresholds</th>');
echo('      <th style="width: 100px;"></th>');
echo('      <th style="width: 100px;">Value</th>');
echo('    </tr>');
echo('  </thead>');
echo('  <tbody>');

foreach ($sensors as $sensor)
{
  humanize_sensor($sensor);

  $alert = ($sensor['state_event'] == 'alert' ? 'oicon-exclamation-red' : '');

  // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
  // FIXME - DUPLICATED IN device/overview/sensors

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
        <td class="entity">' . generate_device_link($sensor) . '</td>
        <td class="entity">' . generate_entity_link("sensor", $sensor) . '</td>
        <td class="text-right"><i class="'.$alert.'"></i></td>
        <td><span class="label">' . $sensor['sensor_thresholds'] . '</span></td>
        <td>'.generate_entity_link("sensor", $sensor, generate_graph_tag($graph_array), FALSE, FALSE).'</td>
        <td style="text-align: right;"><span class="'.$sensor['state_class'].'">' . $sensor['human_value'] . $sensor['sensor_symbol'] . '</span></td>
        </tr>' . PHP_EOL);

  if ($vars['view'] == "graphs")
  {
    echo('
      <tr class="'.$sensor['row_class'].'">
        <td class="state-marker"></td>
        <td colspan=6>');

    $graph_array = array();
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $sensor['sensor_id'];
    $graph_array['type']   = 'sensor_graph';

    print_graph_row($graph_array, TRUE);

    echo('</td></tr>');
  } # endif graphs
}

echo("</tbody>");
echo("</table>");

echo $pagination_html;

// EOF
