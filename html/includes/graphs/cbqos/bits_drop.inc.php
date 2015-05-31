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

include("includes/graphs/common.inc.php");

$ds = "DropByte";

$colour_area = "CDEB8B";
$colour_line = "006600";

$colour_area_max = "FFEE99";

$graph_max = 1;
$multiplier = 8;

$unit_text = "Bps";

include("includes/graphs/generic_simplex.inc.php");

?>
