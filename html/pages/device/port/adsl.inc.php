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

$rrdfile = get_port_rrdfilename($port, "adsl", TRUE);
if (is_file($rrdfile))
{

  echo('<table class="table table-striped  table-condensed">');

  $graph_array['to']     = $config['time']['now'];
  $graph_array['id']     = $port['port_id'];

  echo('<tr><td>');
  echo("<h3>ADSL Line Speed</h4>");
  $graph_array['type']   = "port_adsl_speed";
  print_graph_row($graph_array);
  echo('</td></tr>');

  echo('<tr><td>');
  echo("<h3>ADSL Line Attenuation</h4>");
  $graph_array['type']   =  "port_adsl_attenuation";
  print_graph_row($graph_array);
  echo('</td></tr>');

  echo('<tr><td>');
  echo("<h3>ADSL Line SNR Margin</h4>");
  $graph_array['type']   = "port_adsl_snr";
  print_graph_row($graph_array);
  echo('</td></tr>');

  echo('<tr><td>');
  echo('<h3>ADSL Output Powers</h4>');
  $graph_array['type']   = "port_adsl_power";
  print_graph_row($graph_array);
  echo('</td></tr>');

  echo('</table>');

}

// EOF
