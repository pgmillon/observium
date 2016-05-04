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


  $sql  = "SELECT *, `status`.`status_id` AS `status_id`";
  $sql .= " FROM  `status`";
  $sql .= " LEFT JOIN  `status-state` ON  `status`.status_id =  `status-state`.status_id";
  $sql .= " WHERE `device_id` = ? ORDER BY `status_descr`";

  $status = dbFetchRows($sql, array($device['device_id']));

  if (count($status))
  {
?>

  <div class="widget widget-table">
    <div class="widget-header">
      <a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'health', 'metric' => 'status'))); ?>">
        <i class="<?php echo($config['sensor_types']['state']['icon']); ?>"></i><h3>Status Indicators</h3>
      </a>
    </div>
    <div class="widget-content">


<?php

    echo('<table class="table table-condensed-more table-striped table-bordered">');
    foreach ($status as $status)
    {

      humanize_status($status);

      // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
      // FIXME - So now we need to clean this up and move it into a function. Isn't it just "print-graphrow"?
      // FIXME - DUPLICATED IN health/status

      $graph_colour = str_replace("#", "", $row_colour);

      $graph_array           = array();
      $graph_array['to']     = $config['time']['now'];
      $graph_array['id']     = $status['status_id'];
      $graph_array['type']   = "status_graph";
      $graph_array['from']   = $config['time']['day'];
      $graph_array['legend'] = "no";

      $link_array = $graph_array;
      $link_array['page'] = "graphs";
      unset($link_array['height'], $link_array['width'], $link_array['legend']);
      $link = generate_url($link_array);

      $overlib_content = generate_overlib_content($graph_array);

      $graph_array['width']  = 80;
      $graph_array['height'] = 20;
      $graph_array['bg']     = 'ffffff00'; # the 00 at the end makes the area transparent.
      $graph_array['from']   = $config['time']['day'];

      $status['status_descr'] = truncate($status['status_descr'], 48, '');

      $status_value = $status['status_name'];
      $status_minigraph = overlib_link($link, generate_graph_tag($graph_array), $overlib_content);


      echo('
      <tr class="'.$status['row_class'].'">
        <td class="state-marker"></td>
        <td><strong>'.generate_entity_link('status', $status).'</strong></td>
        <td style="width: 90px; text-align: right;">'.$status_minigraph.'</td>
        <td style="width: 80px; text-align: right;">'.overlib_link($link, '<span class="'.$status['state_class'].'">' . $status['status_name'] . '</span>', $overlib_content).'</td>
      </tr>'.PHP_EOL);
    }

    echo("</table>");
    echo("</div></div>");
  }

// EOF
