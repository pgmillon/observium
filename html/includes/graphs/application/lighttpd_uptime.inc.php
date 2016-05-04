<?php

$scale_min = 0;
include("includes/graphs/common.inc.php");

$colour_area     = "EEEEEE";
$colour_line     = "36393D";
$colour_area_max = "FFEE99";
$graph_max       = 0;
$unit_text       = "Seconds";

$lighttpd_rrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-lighttpd-".$app['app_id'].".rrd";

if (is_file($lighttpd_rrd))
{
  $rrd_filename = $lighttpd_rrd;
}

$ds = "uptime";

$rrd_options   .= " DEF:uptime=".$rrd_filename.":uptime:AVERAGE";
$rrd_options   .= " CDEF:cuptime=uptime,86400,/";

if ($width<224)
{
  $rrd_options .= " 'COMMENT:Days         Cur      Min     Max     Avg\\n'";
} else {
  $rrd_options .= " 'COMMENT:Days      Current  Minimum  Maximum  Average\\n'";
}

$rrd_options   .= " AREA:cuptime#".$colour_area.":";
$rrd_options   .= " LINE1.25:cuptime#".$colour_line.":Uptime";
$rrd_options   .= " GPRINT:cuptime:LAST:%6.2lf";
$rrd_options   .= " GPRINT:cuptime:AVERAGE:%6.2lf";
$rrd_options   .= " GPRINT:cuptime:MAX:%6.2lf";
$rrd_options   .= " GPRINT:cuptime:AVERAGE:%6.2lf\\n";
?>
