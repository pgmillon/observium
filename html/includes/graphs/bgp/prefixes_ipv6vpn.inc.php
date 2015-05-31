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

$rrd_filename = get_rrd_path($device, "cbgp-" . $data['bgpPeerRemoteAddr'] . ".ipv6.vpn.rrd");

include("includes/graphs/bgp/prefixes.inc.php");

?>
