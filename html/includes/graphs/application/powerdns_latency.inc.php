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

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$scale_min       = 0;
$ds              = "latency";
$colour_area     = "F6F6F6";
$colour_line     = "B3D0DB";
$colour_area_max = "FFEE99";
$graph_max       = 100;
$unit_text       = "Latency";
$powerdns_rrd    = get_rrd_path($device, "app-powerdns-".$app['app_id'].".rrd");

if (is_file($powerdns_rrd))
{
  $rrd_filename = $powerdns_rrd;
}

include("includes/graphs/generic_simplex.inc.php");

// EOF
