<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if($metric != sensors && device_permitted($device))
{

  // Don't show aggregate graphs to people without device permissions, or for "all sensors" view.

  echo '<div class="box box-solid">';


  echo('<table class="table table-condensed table-striped table-hover">');

  $graph_title = nicecase($vars['metric']);
  $graph_array['type'] = "device_".$vars['metric'];
  $graph_array['device'] = $device['device_id'];
  $graph_array['legend'] = no;

  echo('<tr><td>');
  echo('<h3>' . $graph_title . '</h3>');
  print_graph_row($graph_array);
  echo('</td></tr>');

  echo('</table>');

  echo '</div>';

}

print_sensor_table($vars);

// EOF
