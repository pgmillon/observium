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

include("includes/graphs/common.inc.php");

$scale_min    = 0;
$colours      = "mixed";
$nototal      = 1;
$unit_text    = "Zones";

$thread = 0;

$i = 0;

$queries_filename = get_rrd_path($device, "app-nsd-zones.rrd");

$rrd_list[$i]['filename'] = $queries_filename;
$rrd_list[$i]['descr']    = 'Master zones';
$rrd_list[$i]['ds']       = 'zoneMaster';
$rrd_list[$i]['colour']   = $config['graph_colours'][$colours][$i % count($config['graph_colours'][$colours])];
$i++;
$rrd_list[$i]['filename'] = $queries_filename;
$rrd_list[$i]['descr']    = 'Slave Zones';
$rrd_list[$i]['ds']       = 'zoneSlave';
$rrd_list[$i]['colour']   = $config['graph_colours'][$colours][$i % count($config['graph_colours'][$colours])];

include("includes/graphs/generic_multi_line.inc.php");

// EOF
