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

$ds_in = "inpackets";
$ds_out = "outpackets";

$colour_area_in = "AA66AA";
$colour_line_in = "330033";
$colour_area_out = "FFDD88";
$colour_line_out = "FF6600";

$colour_area_in_max = "CC88CC";
$colour_area_out_max = "FFEFAA";

$graph_max = 1;
$unit_text = "Packets";

include("includes/graphs/generic_duplex.inc.php");

?>
