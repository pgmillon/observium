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

  $graph_title = nicecase($vars['metric']);
  $graph_array['type'] = "device_".$vars['metric'];
  $graph_array['device'] = $device['device_id'];
  $graph_array['legend'] = no;

  $box_args = array('title' => $graph_title,
                               'header-border' => TRUE,
                   );

   echo generate_box_open($box_args);


  $graph_title = nicecase($vars['metric']);
  $graph_array['type'] = "device_".$vars['metric'];
  $graph_array['device'] = $device['device_id'];
  $graph_array['legend'] = no;

  print_graph_row($graph_array);

  echo generate_box_close();

}

print_status_table($vars);

// EOF
