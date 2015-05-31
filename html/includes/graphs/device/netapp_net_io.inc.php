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

$rrd_filename = get_rrd_path($device, "netapp_stats.rrd");

$ds_in = "net_rx";
$ds_out = "net_tx";

include("includes/graphs/generic_data.inc.php");

?>
