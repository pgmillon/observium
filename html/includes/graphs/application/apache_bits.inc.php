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

$scale_min = 0;

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$apache_rrd = get_rrd_path($device, "app-apache-".$app['app_id'].".rrd");

if (is_file($apache_rrd))
{
  $rrd_filename = $apache_rrd;
}

$ds = "kbyte";

$colour_area = "CDEB8B";
$colour_line = "006600";

$colour_area_max = "FFEE99";

$graph_max = 1;
$multiplier = 8;

$unit_text = "Kbps";

include("includes/graphs/generic_simplex.inc.php");

// EOF
