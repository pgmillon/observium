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

$rrd_filename = get_rrd_path($device, "arista-netstats-sw-ip6.rrd");
$ipv = "v6";

include("netstat_arista_sw_ip_frag.inc.php");

?>
