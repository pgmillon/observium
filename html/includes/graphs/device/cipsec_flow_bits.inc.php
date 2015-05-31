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

$rrd_filename = get_rrd_path($device, "cipsec_flow.rrd");

$ds_in = "InOctets";
$ds_out = "OutOctets";

include("includes/graphs/generic_data.inc.php");

?>
