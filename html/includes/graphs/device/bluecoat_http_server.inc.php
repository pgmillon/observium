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

$file = get_rrd_path($device, "bluecoat-sg-proxy-mib_sgproxyhttpperf.rrd");

$rrd_list[0]['filename'] = $file;
$rrd_list[0]['descr'] = "ServerConn";
$rrd_list[0]['ds'] = "ServerConn";

$rrd_list[1]['filename'] = $file;
$rrd_list[1]['descr'] = "ServerConnAc";
$rrd_list[1]['ds'] = "ServerConnAc";

$rrd_list[2]['filename'] = $file;
$rrd_list[2]['descr'] = "ServerConnId";
$rrd_list[2]['ds'] = "ServerConnId";

if ($_GET['debug']) { print_vars($rrd_list); }

$colours   = "mixed";
$nototal   = 1;
$unit_text = "Connections/sec";
$scale_min = "0";

include("includes/graphs/generic_multi_line.inc.php");
