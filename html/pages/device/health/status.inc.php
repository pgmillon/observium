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
      <thead><tr>
        <th class="state-marker"></th> 
        <th>'.nicecase($vars['metric']).'</th>
        <th width="200">Physical Class</th>
        <th width="100">Historical</th>
        <th width="100">State</th>
      </tr></thead>');

$row = 1;

$sql  = "SELECT *, `status`.`status_id` AS `status_id`";
$sql .= " FROM  `status`";
$sql .= " LEFT JOIN  `status-state` ON  `status`.status_id =  `status-state`.status_id";
$sql .= " WHERE `device_id` = ?";

foreach (dbFetchRows($sql, array($device['device_id'])) as $status)
{
  humanize_status($status);

  $alert = ($status['state_event'] == 'alert' ? 'oicon-exclamation-red' : '');

  // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
  // FIXME - DUPLICATED IN device/overview/status

  $graph_array           = array();
  $graph_array['to']     = $config['time']['now'];
  $graph_array['id']     = $status['status_id'];
  $graph_array['type']   = "status_graph";
  $graph_array['legend'] = "no";
  $graph_array['width'] = 80;
  $graph_array['height'] = 20;
  $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
  $graph_array['from'] = $config['time']['day'];

  $mini_graph = generate_graph_tag($graph_array);

  $status_misc = '<span class="label">'.$status['entPhysicalClass'].'</span>';

  echo('<tr class="'.$status['row_class'].'">
        <td class="state-marker"></td>
        <td class="entity">' . generate_entity_link('status', $status) . '</td>
        <td><span class="label">' . $status['entPhysicalClass'] . '</span></td>
        <td>'.generate_entity_link('status', $status, $mini_graph, NULL, FALSE).'</td>
        <td style="text-align: right;"><strong>'.overlib_link($link, '<span class="'.$status['state_class'].'">' . $status['status_name'] . '</span>', $overlib_content).'</strong></td>
        </tr>' . PHP_EOL);

  if ($vars['view'] == "graphs")
  {
    echo('<tr><td class="state-marker"></td><td colspan=5>');

    unset($graph_array['height'], $graph_array['width'], $graph_array['legend']);
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $status['status_id'];
    $graph_array['type']   = "status_graph";

    print_graph_row($graph_array, TRUE);

    echo('</td></tr>');
  } # endif graphs
}

echo("</table>");

// EOF
