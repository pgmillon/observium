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
$ds              = "frequency";
$colour_area     = "F6F6F6";
$colour_line     = "B3D0DB";
$colour_area_max = "FFEE99";
$graph_max       = 100;
$unit_text       = "Frequency";
$ntpclient_rrd   = get_rrd_path($device, "app-ntpclient-".$app['app_id'].".rrd");

if (is_file($ntpclient_rrd))
{
  $rrd_filename = $ntpclient_rrd;
}

include("includes/graphs/generic_simplex.inc.php");

// EOF
