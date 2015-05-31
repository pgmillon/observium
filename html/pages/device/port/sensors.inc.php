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

echo('<table class="table table-striped table-condensed">');

$sql  = "SELECT *, `sensors`.`sensor_id` AS `sensor_id`";
$sql .= " FROM  `sensors`";
$sql .= " LEFT JOIN  `sensors-state` ON  `sensors`.sensor_id =  `sensors-state`.sensor_id";
$sql .= " WHERE `measured_class` = 'port' AND `measured_entity` = ? AND `device_id` = ?";

$row=1;
foreach (dbFetchRows($sql, array($port['port_id'], $device['device_id'])) as $sensor)
{
  switch($sensor['sensor_class'])
  {
    case "current":
      $class = "current";
      $unit  = "A";
      $graph_type = "sensor_current";
      break;
    case "dbm";
      $class = "dbm";
      $unit  = "dBm";
      $graph_type = "sensor_dbm";
      break;
    case "temperature";
      $class = "temperature";
      $unit  = "&deg;C";
      $graph_type = "sensor_temperature";
      break;
  }

  if (!is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  echo("<tr class=entity-title style=\"background-color: $row_colour; padding: 5px;\">
          <td width=500>" . $sensor['sensor_descr'] . "</td>
          <td>" . $sensor['sensor_type'] . "</td>
          <td width=50>" . format_si($sensor['sensor_value']) .$unit. "</td>
          <td width=50>" . format_si($sensor['sensor_limit']) . $unit . "</td>
          <td width=50>" . format_si($sensor['sensor_limit_low']) . $unit ."</td>
        </tr>\n");
  echo("<tr  bgcolor=$row_colour><td colspan='5'>");

  $graph_array['id'] = $sensor['sensor_id'];
  $graph_array['type'] = $graph_type;
  $graph_array['shrink'] = "1";

  print_graph_row($graph_array);

  echo("</td></tr>");

  $row++;
}

echo("</table>");

// EOF
