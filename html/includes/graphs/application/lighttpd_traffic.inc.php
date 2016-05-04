<?php

$scale_min = 0;

include("includes/graphs/common.inc.php");

$lighttpd_rrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-lighttpd-".$app['app_id'].".rrd";

if (is_file($lighttpd_rrd))
{
  $rrd_filename = $lighttpd_rrd;
}

$ds = "totalkbytes";

$colour_area = "CDEB8B";
$colour_line = "006600";

$colour_area_max = "FFEE99";

$graph_max = 1;
$multiplier = 8;

$unit_text = "Kbps";

include("includes/graphs/generic_simplex.inc.php");

?>
