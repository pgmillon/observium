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

$rrd_filename = get_rrd_path($device, "netapp_stats.rrd");

$ds_in = "net_rx";
$ds_out = "net_tx";

include("includes/graphs/generic_data.inc.php");

?>
