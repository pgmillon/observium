<?php

$scale_min = 0;

$rrd_filename = get_rrd_path($device, "netstats-tcp.rrd");

$ds = "tcpCurrEstab";

$colour_area = "ef3b2c";
$colour_line = "67000d";

$colour_area_max = "dddddd";

$graph_max = 1;

$unit_text = "Established";

include("includes/graphs/generic_simplex.inc.php");

// EOF
