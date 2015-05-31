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

echo('<table class="table table-striped-two table-condensed table-bordered">');

echo('<thead><tr>
        <th>'.nicecase($vars['metric']).'</th>
        <th>MIB</th>
        <th>Value</th>
        <th>High Limit</th>
        <th>Low Limit</th>
      </tr></thead>');

$row = 1;

$sql  = "SELECT *, `sensors`.`sensor_id` AS `sensor_id`";
$sql .= " FROM  `sensors`";
$sql .= " LEFT JOIN  `sensors-state` ON  `sensors`.sensor_id =  `sensors-state`.sensor_id";
$sql .= " WHERE `sensor_class` = ? AND `device_id` = ?";

foreach (dbFetchRows($sql, array($vars['metric'], $device['device_id'])) as $sensor)
{
  $graph_array['id'] = $sensor['sensor_id'];
  $graph_array['type'] = 'sensor_' . $vars['metric'];

  $sensor_url = "graphs/id=". $sensor['sensor_id'] . "/type=" . $graph_array['type'];

  echo("<tr>
          <td width=500 class='entity-title'><a class='entity-title' href='" . $sensor_url . "'>" . htmlentities($sensor['sensor_descr']) . "</a></td>
          <td>" . nicecase($sensor['sensor_type']) . "</td>
          <td width=75>" . format_si($sensor['sensor_value']) .$config['sensor_types'][$vars['metric']]['symbol']. "</td>
          <td width=75>" . format_si($sensor['sensor_limit']) . $config['sensor_types'][$vars['metric']]['symbol'] . "</td>
          <td width=75>" . format_si($sensor['sensor_limit_low']) . $config['sensor_types'][$vars['metric']]['symbol'] ."</td>
        </tr>\n");
  echo("<tr><td colspan='5'>");

  print_graph_row($graph_array);

  echo("</td></tr>");

  $row++;
}

echo("</table>");

// EOF
