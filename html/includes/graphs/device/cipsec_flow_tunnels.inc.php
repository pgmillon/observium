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

$rrd_filename = get_rrd_path($device, "cipsec_flow.rrd");
$ds = "Tunnels";
$colour_area = "9999cc";
$colour_line = "0000cc";
$colour_area_max = "aaaaacc";
$scale_min = 0;
$unit_text = "Active Tunnels";

include("includes/graphs/generic_simplex.inc.php");

// EOF
