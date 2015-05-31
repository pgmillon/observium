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

foreach (array_keys($config['sensor_types']) as $sensor_type)
{
  $sql  = "SELECT *, `sensors`.`sensor_id` AS `sensor_id`";
  $sql .= " FROM  `sensors`";
  $sql .= " LEFT JOIN  `sensors-state` ON  `sensors`.sensor_id =  `sensors-state`.sensor_id";
  $sql .= " WHERE `sensor_class` = ? AND `device_id` = ? ORDER BY `sensor_type`, `sensor_descr`";

  $sensors = dbFetchRows($sql, array($sensor_type, $device['device_id']));

  if (count($sensors))
  {
?>

<div class="well info_box">
    <div class="title"><a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'health', 'metric' => $sensor_type))); ?>">
      <i class="<?php echo($config['sensor_types'][$sensor_type]['icon']); ?>"></i> <?php echo(nicecase($sensor_type)) ?></a></div>
    <div class="content">

<?php

    echo('<table class="table table-condensed-more table-striped table-bordered">');
    foreach ($sensors as $sensor)
    {

      humanize_sensor($sensor);

      // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
      // FIXME - So now we need to clean this up and move it into a function. Isn't it just "print-graphrow"?
      // FIXME - DUPLICATED IN health/sensors

      $graph_colour = str_replace("#", "", $row_colour);

      $graph_array           = array();
      $graph_array['to']     = $config['time']['now'];
      $graph_array['id']     = $sensor['sensor_id'];
      $graph_array['type']   = "sensor_" . $sensor_type;
      $graph_array['from']   = $config['time']['day'];
      $graph_array['legend'] = "no";

      $link_array = $graph_array;
      $link_array['page'] = "graphs";
      unset($link_array['height'], $link_array['width'], $link_array['legend']);
      $link = generate_url($link_array);

      $overlib_content = generate_overlib_content($graph_array);

      $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
      $graph_array['from'] = $config['time']['day'];
//      $graph_array['style'][] = 'margin-top: -6px';

      $sensor['sensor_descr'] = truncate($sensor['sensor_descr'], 48, '');
      if ($sensor['sensor_state'])
      {
        $sensor_value = $sensor['state_name'];
        $sensor_minigraph = overlib_link($link, generate_graph_tag($graph_array), $overlib_content);
      } else {
        $sensor_value = $sensor['human_value'];
        $sensor_minigraph = overlib_link($link, generate_graph_tag($graph_array), $overlib_content);
      }

      echo('<tr class="device-overview">
            <td><strong>'.overlib_link($link, htmlentities($sensor['sensor_descr']), $overlib_content).'</strong></td>
            <td style="width: 90px; align: right;">'.$sensor_minigraph.'</td>
            <td style="width: 80px; align: right;">'.overlib_link($link, '<span class="'.$sensor['state_class'].'">' . $sensor_value . $sensor['sensor_symbol'] . '</span>', $overlib_content).'</td>
            </tr>'.PHP_EOL);
    }

    echo("</table>");
    echo("</div></div>");
  }
}

// EOF
