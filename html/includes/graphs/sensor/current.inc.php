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

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_options .= " COMMENT:'                       Min     Last    Max\\n'";

$rrd_options .= " DEF:sensor=$rrd_filename:sensor:AVERAGE";
$rrd_options .= " LINE1.5:sensor#cc0000:'" . rrdtool_escape($sensor['sensor_descr'],20)."'";
$rrd_options .= " GPRINT:sensor$current_id:MIN:%5.2lfA";
$rrd_options .= " GPRINT:sensor:LAST:%5.2lfA";
$rrd_options .= " GPRINT:sensor:MAX:%5.2lfA\\\\l";

if (is_numeric($sensor['sensor_limit'])) $rrd_options .= " HRULE:".$sensor['sensor_limit']."#999999::dashes";
if (is_numeric($sensor['sensor_limit_low'])) $rrd_options .= " HRULE:".$sensor['sensor_limit_low']."#999999::dashes";

if ($_GET['previous'] == 'yes')
{
  $rrd_options .= " DEF:sensorX=$rrd_filename:sensor:AVERAGE:start=".$prev_from.":end=".$from;
  $rrd_options .= " LINE1.5:sensorX#0000cc:'Prev " . rrdtool_escape($sensor['sensor_descr'],18)."'";
  $rrd_options .= " SHIFT:sensorX:$period";
  $rrd_options .= " GPRINT:sensorX$current_id:MIN:%5.2lfA";
  $rrd_options .= " GPRINT:sensorX:LAST:%5.2lfA";
  $rrd_options .= " GPRINT:sensorX:MAX:%5.2lfA\\\\l";
}

$graph_return = array('rrds' => array($rrd_filename), 'descr' => 'Current sensor measured in Amperes.', 'valid_options' => array('previous'));

// EOF
