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

$scale_min = "0";
$scale_max = "100";

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_options .= " COMMENT:'                          Min     Last   Max\\n'";

$rrd_options .= " DEF:sensor=$rrd_filename:sensor:AVERAGE";
$rrd_options .= " DEF:sensor_max=$rrd_filename:sensor:MAX";
$rrd_options .= " DEF:sensor_min=$rrd_filename:sensor:MIN";
$rrd_options .= " CDEF:sensor_diff=sensor_max,sensor_min,-";
$rrd_options .= " AREA:sensor_min";
$rrd_options .= " AREA:sensor_diff#c5c5c5::STACK";

$rrd_options .= " LINE1.5:sensor#cc0000:'" . rrdtool_escape($sensor['sensor_descr'],20)."'";
$rrd_options .= " GPRINT:sensor_min:MIN:%4.1lf%%";
$rrd_options .= " GPRINT:sensor:LAST:%4.1lf%%";
$rrd_options .= " GPRINT:sensor_max:MAX:%4.1lf%%\\\\l";

if (is_numeric($sensor['sensor_limit'])) $rrd_options .= " HRULE:".$sensor['sensor_limit']."#999999::dashes";
if (is_numeric($sensor['sensor_limit_low'])) $rrd_options .= " HRULE:".$sensor['sensor_limit_low']."#999999::dashes";

$graph_return = array('rrds' => array($rrd_filename), 'descr' => nicecase($sensor['sensor_class']).' sensor measured in percent.', 'valid_options');

// EOF
