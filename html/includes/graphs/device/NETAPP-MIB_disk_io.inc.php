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

$rrd_filename = get_rrd_path($device, "netapp-mib_misc.rrd");

$ds_in = "DiskReadBytes";
$ds_out = "DiskWriteBytes";

$format = "octets";

include("includes/graphs/generic_data.inc.php");

?>
