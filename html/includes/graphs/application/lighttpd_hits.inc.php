<?php

$scale_min = 0;

include("includes/graphs/common.inc.php");

$colour_area = "B0C4DE";
$colour_line = "191970";
#$colour_area_max = "FFEE99";
$colour_area_max = "B0C4DE";

$lighttpd_rrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-lighttpd-".$app['app_id'].".rrd";

if (is_file($lighttpd_rrd))
{
  $rrd_filename = $lighttpd_rrd;
}

$ds = "totalaccesses";

$graph_max = 1;

$unit_text = "Hits/Sec";

include("includes/graphs/generic_simplex.inc.php");

?>
