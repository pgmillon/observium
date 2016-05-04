<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$units           = "b";
$total_units     = "B";
$colours_in      = "greens";
//$multiplier      = "0";
$colours_out     = "blues";

$nototal         = 1;

$ds_in           = "traf_in";
$ds_out          = "traf_out";

$graph_title    .= "::bits";

$colour_line_in  = "006600";
$colour_line_out = "000099";
$colour_area_in  = "CDEB8B";
$colour_area_out = "C3D9FF";

$hostname        = (isset($_GET['hostname']) ? $_GET['hostname'] : "unkown");
$rrd_filename    = get_rrd_path($device, "app-shoutcast-".$app['app_id']."-".$hostname.".rrd");

include("includes/graphs/generic_data.inc.php");

// EOF
