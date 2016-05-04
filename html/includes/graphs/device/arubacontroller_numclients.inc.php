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

$rrd_filename = get_rrd_path($device, "aruba-controller.rrd");

$ds = "NUMCLIENTS";
$colour_line = "008C00";
$colour_area = "CDEB8B";
$colour_area_max = "cc9999";
$unit_text = "Clients";
$line_text = 'Clients';
$scale_min = 0;

include("includes/graphs/generic_simplex.inc.php");

// EOF
