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

$rrd_filename = get_rrd_path($device, "aruba-controller.rrd");

$ds = "NUMAPS";
$colour_line = "8C0000";
$colour_area = "EBCD8B";
$colour_area_max = "cc9999";
$unit_text = "APs";
$line_text = 'Active APs';
$scale_min = 0;

include("includes/graphs/generic_simplex.inc.php");

?>
