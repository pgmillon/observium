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

if(device_permitted($device))
{

  // Only show aggregate graph if we have access to the entire device.

  echo '<div class="box box-solid">';

  echo('<table class="table table-condensed table-striped table-hover ">');

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

print_storage_table($vars);

// EOF
