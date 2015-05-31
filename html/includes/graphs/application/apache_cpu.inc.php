<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$scale_min = 0;

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$apache_rrd = get_rrd_path($device, "app-apache-".$app['app_id'].".rrd");

if (is_file($apache_rrd))
{
  $rrd_filename = $apache_rrd;
}

$ds = "cpu";

$colour_area = "F0E68C";
$colour_line = "FF4500";

$colour_area_max = "FFEE99";

$graph_max = 1;

$unit_text = "% Used";

include("includes/graphs/generic_simplex.inc.php");

// EOF
