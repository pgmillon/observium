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

$agent_rrd   = get_rrd_path($device, "agent.rrd");

if (is_file($agent_rrd))
{
  $rrd_filename = $agent_rrd;
}

$ds = "time";

$colour_area = "EEEEEE";
$colour_line = "36393D";

$colour_area_max = "FFEE99";

$graph_max = 1;

$unit_text = "msec";

include("includes/graphs/generic_simplex.inc.php");

// EOF
