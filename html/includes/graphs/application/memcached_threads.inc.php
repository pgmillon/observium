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

include("memcached.inc.php");
include_once($config['html_dir']."/includes/graphs/common.inc.php");

$scale_min       = 0;
$ds              = "threads";
$colour_area     = "F6F6F6";
$colour_line     = "555555";
$colour_area_max = "FFEE99";
#$graph_max       = 100;
$unit_text       = "Threads";

include("includes/graphs/generic_simplex.inc.php");

// EOF
