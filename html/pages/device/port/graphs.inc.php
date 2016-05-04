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

echo generate_box_open();

?>

<table class="table table-striped  table-condensed">

<?php

$rrdfile = get_port_rrdfilename($port, NULL, TRUE);

if (is_file($rrdfile))
{
  echo('<tr><td>');
  echo('<h3>Traffic</h3>');
  $graph_array['type'] = "port_bits";
  print_graph_row_port($graph_array, $port);
  echo('</td></tr>');

  echo('<tr><td>');
  echo("<h3>Unicast Packets</h3>");
  $graph_array['type'] = "port_upkts";

  print_graph_row_port($graph_array, $port);
  echo('</td></tr>');

  echo('<tr><td>');
  echo("<h3>Non Unicast Packets</h3>");
  $graph_array['type'] = "port_nupkts";

  print_graph_row_port($graph_array, $port);
  echo('</td></tr>');

  echo('<tr><td>');
  echo("<h3>Average Packet Size</h3>");
  $graph_array['type'] = "port_pktsize";

  print_graph_row_port($graph_array, $port);
  echo('</td></tr>');

  echo('<tr><td>');
  echo("<h3>Percent Utilisation</h3>");
  $graph_array['type'] = "port_percent";

  print_graph_row_port($graph_array, $port);
  echo('</td></tr>');

  echo('<tr><td>');
  echo("<h3>Errors</h3>");
  $graph_array['type'] = "port_errors";

  print_graph_row_port($graph_array, $port);
  echo('</td></tr>');

  if (is_file($device, get_port_rrdfilename($port, "dot3", TRUE)))
  {
    echo('<tr><td>');
    echo("<h3>Ethernet Errors</h3>");
    $graph_array['type'] = "port_etherlike";

    print_graph_row_port($graph_array, $port);
    echo('</td></tr>');

  }

  if (is_file(get_port_rrdfilename($port, "fdbcount", TRUE)))
  {
    echo('<tr><td>');
    echo("<h3>FDB Count</h3>");
    $graph_array['type'] = "port_fdb_count";

    print_graph_row_port($graph_array, $port);
    echo('</td></tr>');
  }
}

?>

</table>
<?php

  echo generate_box_close();

// EOF
