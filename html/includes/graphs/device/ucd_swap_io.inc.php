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

$rrd_filename_in  = get_rrd_path($device, "ucd_ssRawSwapIn.rrd");
$rrd_filename_out = get_rrd_path($device, "ucd_ssRawSwapOut.rrd");
$ds_in = "value";
$ds_out = "value";

$multiplier = 512;

include("includes/graphs/generic_data.inc.php");

?>
