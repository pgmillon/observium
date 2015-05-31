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

?>

<table class="table table-striped table-bordered">

<?php

$rrdfile = get_port_rrdfilename($port, NULL, TRUE);

if (is_file($rrdfile))
{
  $iid = $id;
  echo('<tr><td>');
  echo('<h4>Traffic</h4>');
  $graph_array['type'] = "port_bits";
  print_graph_row_port($graph_array, $port);
  echo('</td></tr>');

  echo('<tr><td>');
  echo("<h4>Unicast Packets</h4>");
  $graph_array['type'] = "port_upkts";

  print_graph_row_port($graph_array, $port);
  echo('</td></tr>');

  echo('<tr><td>');
  echo("<h4>Non Unicast Packets</h4>");
  $graph_array['type'] = "port_nupkts";

  print_graph_row_port($graph_array, $port);
  echo('</td></tr>');

  echo('<tr><td>');
  echo("<h4>Average Packet Size</h4>");
  $graph_array['type'] = "port_pktsize";

  print_graph_row_port($graph_array, $port);
  echo('</td></tr>');

  echo('<tr><td>');
  echo("<h4>Percent Utilisation</h4>");
  $graph_array['type'] = "port_percent";

  print_graph_row_port($graph_array, $port);
  echo('</td></tr>');

  echo('<tr><td>');
  echo("<h4>Errors</h4>");
  $graph_array['type'] = "port_errors";

  print_graph_row_port($graph_array, $port);
  echo('</td></tr>');

  if (is_file($device, get_port_rrdfilename($port, "dot3", TRUE)))
  {
    echo('<tr><td>');
    echo("<h4>Ethernet Errors</h4>");
    $graph_array['type'] = "port_etherlike";

    print_graph_row_port($graph_array, $port);
    echo('</td></tr>');

  }

  if (is_file(get_port_rrdfilename($port, "fdbcount", TRUE)))
  {
    echo('<tr><td>');
    echo("<h4>FDB Count</h4>");
    $graph_array['type'] = "port_fdb_count";

    print_graph_row_port($graph_array, $port);
    echo('</td></tr>');
  }
}

?>

</table>
<?php

// EOF
