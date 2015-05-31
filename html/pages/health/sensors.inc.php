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
echo('      <th style="width: 250px;">Device</th>');
echo('      <th>Sensor</th>');
echo('      <th style="width: 40px;"></th>');
echo('      <th style="width: 100px;"></th>');
echo('      <th style="width: 100px;">Current</th>');
if ($sensor_type == 'state')
{
  echo('      <th style="width: 175px;">Physical Class</th>');
} else {
  echo('      <th style="width: 175px;">Thresholds</th>');
}
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
  $graph_array['type']   = "sensor_$sensor_type";
  $graph_array['legend'] = "no";

  $link_array = $graph_array;
  $link_array['page'] = "graphs";
  unset($link_array['height'], $link_array['width'], $link_array['legend']);
  $link_graph = generate_url($link_array);

  $link = generate_url(array("page" => "device", "device" => $sensor['device_id'], "tab" => "health", "metric" => $sensor['sensor_class']));

  $overlib_content = generate_overlib_content($graph_array, $sensor['hostname'] ." - " . htmlentities($sensor['sensor_descr']), NULL);

  $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
  $graph_array['from'] = $config['time']['day'];

  $sensor['sensor_descr'] = truncate($sensor['sensor_descr'], 48, '');
  if ($sensor['sensor_state'])
  {
    $sensor_value = $sensor['state_name'];
    $sensor_minigraph = overlib_link($link, generate_graph_tag($graph_array), $overlib_content);
    $sensor_misc = '<span class="label">'.$sensor['entPhysicalClass'].'</span>';
  } else {
    $sensor_value = $sensor['human_value'];
    $sensor_minigraph = overlib_link($link, generate_graph_tag($graph_array), $overlib_content);

    if ($sensor['sensor_limit_low'] != NULL)
    {
      switch ($sensor['sensor_class']) // Same set as in humanize_sensor()
      {
        case 'frequency':
        case 'voltage':
        case 'current':
        case 'apower':
        case 'power':
          $sensor_threshold_low = format_si($sensor['sensor_limit_low']) . $sensor['sensor_symbol'];
          break;
        default:
          $sensor_threshold_low = $sensor['sensor_limit_low'] . $sensor['sensor_symbol'];
      }
    } else {
      $sensor_threshold_low = "&infin;";
    }

    if ($sensor['sensor_limit'] != NULL)
    {
      switch ($sensor['sensor_class']) // Same set as in humanize_sensor()
      {
        case 'frequency':
        case 'voltage':
        case 'current':
        case 'apower':
        case 'power':
          $sensor_threshold_high = format_si($sensor['sensor_limit']) . $sensor['sensor_symbol'];
          break;
        default:
          $sensor_threshold_high = $sensor['sensor_limit'] . $sensor['sensor_symbol'];
      }
    } else {
      $sensor_threshold_high = "&infin;";
    }
    $sensor_misc = $sensor_threshold_low . ' - ' . $sensor_threshold_high;
  }

  echo('<tr class="'.$sensor['html_row_class'].'">
        <td class="entity">' . generate_device_link($sensor) . '</td>
        <td>' . overlib_link($link, htmlentities($sensor['sensor_descr']), $overlib_content) . '</td>
        <td class="text-right"><i class="'.$alert.'"></i></td>
        <td>'.$sensor_minigraph.'</td>
        <td><strong>'.overlib_link($link, '<span class="'.$sensor['state_class'].'">' . $sensor_value . $sensor['sensor_symbol'] . '</span>', $overlib_content).'</strong></td>
        <td>' . $sensor_misc . '</td>
        </tr>' . PHP_EOL);

  if ($vars['view'] == "graphs")
  {
    echo("<tr><td colspan=6>");

    unset($graph_array['height'], $graph_array['width'], $graph_array['legend']);
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $sensor['sensor_id'];
    $graph_array['type']   = "sensor_$sensor_type";

    print_graph_row($graph_array);

    echo("</td></tr>");
  } # endif graphs
}

echo("</tbody>");
echo("</table>");

echo $pagination_html;

// EOF
