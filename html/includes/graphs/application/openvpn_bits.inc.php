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

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$openvpn_rrd = get_rrd_path($device, "app-openvpn-" . $app['app_instance'] . ".rrd");

if (is_file($openvpn_rrd))
{
  $rrd_filename = $openvpn_rrd;
}

$multiplier = 8;

$ds_in = "bytesin";
$ds_out = "bytesout";

include("includes/graphs/generic_data.inc.php");

// EOF
